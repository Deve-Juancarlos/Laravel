<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request; // Importamos Request

class InventarioService
{
    protected $connection = 'sqlsrv';

    /**
     * Obtiene una lista paginada de todos los productos.
     */
    public function getProductosPaginados(Request $request)
    {
        $query = DB::connection($this->connection)->table('Productos as p')
            ->leftJoin('Laboratorios as l', DB::raw('RTRIM(l.CodLab)'), '=', DB::raw('LEFT(p.CodPro, 2)'))
            ->where('p.Eliminado', 0)
            ->select('p.CodPro', 'p.Nombre', 'p.Costo', 'p.PventaMa', 'p.PventaMi', 'p.Stock', 'l.Descripcion as Laboratorio');

        // Filtro de búsqueda
        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(function($subQuery) use ($q) {
                $subQuery->where('p.Nombre', 'LIKE', "%{$q}%")
                         ->orWhere('p.CodPro', 'LIKE', "%{$q}%")
                         ->orWhere('l.Descripcion', 'LIKE', "%{$q}%");
            });
        }
        
        return $query->orderBy('p.Nombre')->paginate(50);
    }

    /**
     * Obtiene el stock detallado (lotes y vencimientos) de todos los productos.
     * Usa la tabla Saldos.
     */
    public function getStockLotesPaginado(Request $request)
    {
        $query = DB::connection($this->connection)->table('Saldos as s')
            ->join('Productos as p', 's.codpro', '=', 'p.CodPro')
            ->where('s.saldo', '>', 0)
            ->where('p.Eliminado', 0)
            ->select('s.codpro', 'p.Nombre', 's.almacen', 's.lote', 's.vencimiento', 's.saldo');

        // Filtro de búsqueda
        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(function($subQuery) use ($q) {
                $subQuery->where('p.Nombre', 'LIKE', "%{$q}%")
                         ->orWhere('s.codpro', 'LIKE', "%{$q}%")
                         ->orWhere('s.lote', 'LIKE', "%{$q}%");
            });
        }
        
        return $query->orderBy('s.vencimiento', 'asc')->paginate(50);
    }

    /**
     * Obtiene la lista de todos los laboratorios.
     */
    public function getLaboratoriosPaginados(Request $request)
    {
        $query = DB::connection($this->connection)->table('Laboratorios');

        if ($request->filled('q')) {
            $query->where('Descripcion', 'LIKE', '%' . $request->input('q') . '%');
        }

        return $query->orderBy('Descripcion')->paginate(50);
    }

    /**
     * Obtiene el reporte de productos por vencer.
     * Usa la vista v_productos_por_vencer que ya tienes en tu BD.
     */
    public function getProductosPorVencer(Request $request)
    {
        $query = DB::connection($this->connection)->table('v_productos_por_vencer');

        // Filtro de estado (VENCIDO, CRÍTICO, ALERTA)
        if ($request->filled('estado')) {
            $query->where('EstadoVencimiento', $request->input('estado'));
        }
        
        // Filtro de búsqueda
        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(function($subQuery) use ($q) {
                $subQuery->where('Nombre', 'LIKE', "%{$q}%")
                         ->orWhere('CodPro', 'LIKE', "%{$q}%")
                         ->orWhere('Lote', 'LIKE', "%{$q}%");
            });
        }

        return $query->orderBy('DiasParaVencer', 'asc')->paginate(50);
    }

    /**
     * Obtiene un solo producto por su código.
     */
    public function getProductoPorCodigo($codPro)
    {
        return DB::connection($this->connection)->table('Productos as p')
            ->leftJoin('Laboratorios as l', DB::raw('RTRIM(l.CodLab)'), '=', DB::raw('LEFT(p.CodPro, 2)'))
            ->where('p.Eliminado', 0)
            ->where('p.CodPro', $codPro)
            ->select('p.*', 'l.Descripcion as Laboratorio')
            ->first();
    }

    /**
     * Obtiene el stock detallado (lotes y vencimientos) de UN producto.
     */
    public function getStockPorProducto($codPro)
    {
        return DB::connection($this->connection)->table('Saldos')
            ->where('codpro', $codPro)
            ->where('saldo', '>', 0)
            ->select('almacen', 'lote', 'vencimiento', 'saldo')
            ->orderBy('vencimiento', 'asc')
            ->get();
    }

    
}