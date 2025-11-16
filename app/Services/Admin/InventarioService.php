<?php

namespace App\Services;

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
            ->leftJoin('Saldos', 'Productos.CodPro', '=', 'Saldos.codpro')
            ->leftJoin('Laboratorios', 'Productos.CodProv', '=', 'Laboratorios.CodLab')
            ->select(
                'Productos.*',
                DB::raw('ISNULL(SUM(Saldos.saldo), 0) as stock_total'),
                DB::raw('ISNULL(SUM(Saldos.saldo), 0) * Productos.Costo as valorizacion')
            )
            ->where('Productos.Eliminado', 0)
            ->groupBy(
                'Productos.CodPro',
                'Productos.Nombre',
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
            ->havingRaw('ISNULL(SUM(Saldos.saldo), 0) <= 10')
            ->orderBy('stock_total')
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
            ->first();
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
            ->get();
    }

   
    public function obtenerRotacionProductos()
    {
        return collect(); // Placeholder
    }

    public function obtenerLaboratorios()
    {
        return DB::table('Laboratorios')
            ->whereNotNull('Descripcion')
            ->orderBy('Descripcion')
            ->pluck('Descripcion');
    }
}