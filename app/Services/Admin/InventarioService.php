<?php

namespace App\Services\Admin;

use Illuminate\Support\Facades\DB;

class InventarioService
{
    public function obtenerEstadisticasGenerales()
    {
        return [
            'total_productos' => DB::table('Productos')
                ->where('Eliminado', 0)
                ->count(),

            'productos_con_stock' => DB::table('Saldos')
                ->where('saldo', '>', 0)
                ->distinct('codpro')
                ->count('codpro'),

            'productos_sin_stock' => DB::table('Productos')
                ->leftJoin('Saldos', 'Productos.CodPro', '=', 'Saldos.codpro')
                ->where('Productos.Eliminado', 0)
                ->where(function ($q) {
                    $q->whereNull('Saldos.saldo')
                      ->orWhere('Saldos.saldo', '<=', 0);
                })
                ->distinct('Productos.CodPro')
                ->count('Productos.CodPro'),

            'stock_total' => DB::table('Saldos')
                ->sum('saldo'),

            'productos_por_vencer' => DB::table('v_productos_por_vencer')
                ->where('DiasParaVencer', '<=', 30)
                ->count(),

            'valorizacion_total' => DB::table('Saldos')
                ->join('Productos', 'Saldos.codpro', '=', 'Productos.CodPro')
                ->where('Productos.Eliminado', 0)
                ->sum(DB::raw('Saldos.saldo * Productos.Costo')),
        ];
    }

    public function obtenerProductos($filtros = [])
    {
        $query = DB::table('Productos')
            ->leftJoin('Saldos', 'Productos.CodPro', '=', 'Saldos.codpro')
            ->leftJoin('Laboratorios', 'Productos.CodProv', '=', 'Laboratorios.CodLab')
            ->select(
                'Productos.CodPro',
                'Productos.Nombre',
                'Laboratorios.Descripcion as Laboratorio',
                'Productos.Costo',
                'Productos.PventaMa as Precio',
                'Productos.Eliminado',
                DB::raw('ISNULL(SUM(Saldos.saldo), 0) as stock_total')
            )
            ->where('Productos.Eliminado', 0)
            ->groupBy(
                'Productos.CodPro',
                'Productos.Nombre',
                'Laboratorios.Descripcion',
                'Productos.Costo',
                'Productos.PventaMa',
                'Productos.Eliminado'
            );

        if (!empty($filtros['buscar'])) {
            $query->where(function ($q) use ($filtros) {
                $q->where('Productos.Nombre', 'like', "%{$filtros['buscar']}%")
                  ->orWhere('Productos.CodPro', 'like', "%{$filtros['buscar']}%");
            });
        }

        if (!empty($filtros['laboratorio'])) {
            $query->where('Laboratorios.Descripcion', $filtros['laboratorio']);
        }

        if (!empty($filtros['stock_minimo'])) {
            $query->havingRaw('ISNULL(SUM(Saldos.saldo), 0) <= ?', [$filtros['stock_minimo']]);
        }

        return $query->orderBy('Productos.Nombre')->get();
    }

    public function obtenerProducto($codpro)
    {
        return DB::table('Productos')
            ->where('CodPro', $codpro)
            ->where('Eliminado', 0)
            ->first();
    }

    public function obtenerSaldosPorProducto($codpro)
    {
        return DB::table('Saldos')
            ->where('codpro', $codpro)
            ->where('saldo', '>', 0)
            ->orderBy('vencimiento')
            ->get();
    }

    public function calcularValorizacion($codpro)
    {
        $producto = $this->obtenerProducto($codpro);
        if (!$producto) return 0;

        $stockTotal = DB::table('Saldos')
            ->where('codpro', $codpro)
            ->sum('saldo');

        return $stockTotal * $producto->Costo;
    }


    public function obtenerProductosCriticos($limite = 20)
    {
        return DB::table('Productos')
            ->leftJoin('Saldos', 'Productos.CodPro', '=', 'Saldos.codpro')
            ->leftJoin('Laboratorios', 'Productos.CodProv', '=', 'Laboratorios.CodLab')
            ->select(
                'Productos.CodPro',
                'Productos.Nombre',
                'Laboratorios.Descripcion as Laboratorio',
                DB::raw('ISNULL(SUM(Saldos.saldo), 0) as stock_total')
            )
            ->where('Productos.Eliminado', 0)
            ->groupBy('Productos.CodPro', 'Productos.Nombre', 'Laboratorios.Descripcion')
            ->havingRaw('ISNULL(SUM(Saldos.saldo), 0) <= 10')
            ->orderBy('stock_total')
            ->limit($limite)
            ->get();
    }

    public function obtenerProductosPorVencer($limite = 20)
    {
        return DB::table('v_productos_por_vencer')
            ->select('*', 'DiasParaVencer as dias_para_vencer') // <-- alias
            ->where('DiasParaVencer', '<=', 30)
            ->orderBy('DiasParaVencer')
            ->limit($limite)
            ->get();
    }

    public function obtenerProductosPorVencerCompleto()
    {
        return DB::table('v_productos_por_vencer')
            ->orderBy('DiasParaVencer')
            ->get();
    }

    public function obtenerProductosStockCritico()
{
    return DB::table('Productos')
        ->leftJoin('Laboratorios', 'Productos.CodProv', '=', 'Laboratorios.CodLab')
        ->leftJoin('Saldos', 'Productos.CodPro', '=', 'Saldos.codpro')
        ->select(
            'Productos.*',
            'Laboratorios.Descripcion as Laboratorio',
            DB::raw('ISNULL(SUM(Saldos.saldo),0) as stock_total'),
            DB::raw('ISNULL(SUM(Saldos.saldo),0) * Productos.Costo as valorizacion')
        )
        ->where('Productos.Eliminado', 0)
        ->groupBy(
            'Productos.CodPro',
            'Productos.Nombre',
            'Productos.CodBar',
            'Productos.Clinea',
            'Productos.Clase',
            'Productos.CodProv',
            'Productos.Peso',
            'Productos.Minimo',
            'Productos.Stock',
            'Productos.Afecto',
            'Productos.Tipo',
            'Productos.Costo',
            'Productos.PventaMa',
            'Productos.PventaMi',
            'Productos.ComisionH',
            'Productos.ComisionV',
            'Productos.ComisionR',
            'Productos.Eliminado',
            'Productos.AfecFle',
            'Productos.CosReal',
            'Productos.RegSanit',
            'Productos.TemMax',
            'Productos.TemMin',
            'Productos.FecSant',
            'Productos.Coddigemin',
            'Productos.CodLab',
            'Productos.Codlab1',
            'Productos.Principio',
            'Productos.SujetoADetraccion',
            'Laboratorios.Descripcion'
        )
        ->havingRaw('ISNULL(SUM(Saldos.saldo),0) <= ?', [10])
        ->orderByRaw('ISNULL(SUM(Saldos.saldo),0) ASC')
        ->get();
}



    public function obtenerValorizacionTotal()
{
    return DB::table('Saldos')
        ->join('Productos', 'Saldos.codpro', '=', 'Productos.CodPro')
        ->where('Productos.Eliminado', 0)
        ->select(
            DB::raw('SUM(Saldos.saldo) as unidades_totales'),
            DB::raw('SUM(Saldos.saldo * Productos.Costo) as costo_total'),
            DB::raw('SUM(Saldos.saldo * Productos.PventaMa) as precio_venta_total')
        )
        ->first(); // ← Devuelve un objeto stdClass
}

    public function obtenerValorizacionPorLaboratorio()
    {
        return DB::table('Productos')
            ->leftJoin('Saldos', 'Productos.CodPro', '=', 'Saldos.codpro')
            ->leftJoin('Laboratorios', 'Productos.CodProv', '=', 'Laboratorios.CodLab')
            ->select(
                'Laboratorios.Descripcion as Laboratorio',
                DB::raw('COUNT(DISTINCT Productos.CodPro) as cantidad_productos'),
                DB::raw('ISNULL(SUM(Saldos.saldo), 0) as stock_total'),
                DB::raw('ISNULL(SUM(Saldos.saldo * Productos.Costo), 0) as valorizacion')
            )
            ->where('Productos.Eliminado', 0)
            ->groupBy('Laboratorios.Descripcion')
            ->orderByDesc('valorizacion')
            ->get(); // ← Devuelve una colección
    }

    /**
 * Obtener rotación de productos (ventas y compras del año actual)
 */
public function obtenerRotacionProductos()
{
    $anio = date('Y');

    // Ventas (salidas)
    $ventas = DB::table('Docdet as dd')
        ->join('Doccab as dc', function ($join) {
            $join->on('dd.Numero', '=', 'dc.Numero')
                 ->on('dd.Tipo', '=', 'dc.Tipo');
        })
        ->select(
            'dd.Codpro',
            DB::raw('SUM(dd.Cantidad) as total_salidas')
        )
        ->whereYear('dc.Fecha', $anio)
        ->where('dc.Eliminado', 0)
        ->groupBy('dd.Codpro');

    // Compras (entradas)
    $compras = DB::table('CompraDet as cd')
        ->join('CompraCab as cc', 'cd.CompraId', '=', 'cc.Id')
        ->select(
            'cd.CodPro',
            DB::raw('SUM(cd.Cantidad) as total_entradas')
        )
        ->whereYear('cc.FechaEmision', $anio)
        ->where('cc.Estado', 'REGISTRADA')
        ->groupBy('cd.CodPro');

    // Unión de ambos con Productos y Laboratorios
    $rotacion = DB::table('Productos as p')
        ->leftJoinSub($ventas, 'ventas', function ($join) {
            $join->on('p.CodPro', '=', 'ventas.Codpro');
        })
        ->leftJoinSub($compras, 'compras', function ($join) {
            $join->on('p.CodPro', '=', 'compras.CodPro');
        })
        ->leftJoin('Laboratorios as l', 'p.CodProv', '=', 'l.CodLab')
        ->select(
            'p.CodPro',
            'p.Nombre',
            'l.Descripcion as Laboratorio',
            DB::raw('ISNULL(ventas.total_salidas, 0) as total_salidas'),
            DB::raw('ISNULL(compras.total_entradas, 0) as total_entradas')
        )
        ->where('p.Eliminado', 0)
        ->where(function ($q) {
            $q->whereRaw('ISNULL(ventas.total_salidas, 0) > 0')
              ->orWhereRaw('ISNULL(compras.total_entradas, 0) > 0');
        })
        ->orderByDesc('total_salidas')
        ->limit(50)
        ->get();

    return $rotacion;
}

    public function obtenerLaboratorios()
    {
        return DB::table('Laboratorios')
            ->whereNotNull('Descripcion')
            ->orderBy('Descripcion')
            ->pluck('Descripcion');
    }

    

}