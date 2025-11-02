<?php

namespace App\Http\Controllers\Contabilidad;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\InventarioService; // <-- Importamos el servicio

class InventarioController extends Controller
{
    protected $inventarioService;

    // Inyectamos el servicio
    public function __construct(InventarioService $inventarioService)
    {
        $this->inventarioService = $inventarioService;
    }

    /**
     * Muestra la lista principal de productos (para el link "Productos").
     */
    public function index(Request $request)
    {
        $productos = $this->inventarioService->getProductosPaginados($request);
        $filtros = $request->only(['q']);

        return view('inventario.index', compact('productos', 'filtros'));
    }

    /**
     * Muestra el detalle de un producto (lotes, stock).
     */
    public function show($codPro)
    {
        $producto = $this->inventarioService->getProductoPorCodigo($codPro);
        if (!$producto) {
            abort(404, 'Producto no encontrado');
        }
        
        $stockDetallado = $this->inventarioService->getStockPorProducto($codPro);

        return view('inventario.show', compact('producto', 'stockDetallado'));
    }
    
    /**
     * Muestra la lista de "Stock y Lotes" (tabla Saldos).
     */
    public function stockLotes(Request $request)
    {
        $lotes = $this->inventarioService->getStockLotesPaginado($request);
        $filtros = $request->only(['q']);

        return view('inventario.stock', compact('lotes', 'filtros'));
    }


    /**
     * Muestra la lista de laboratorios.
     */
    public function laboratorios(Request $request)
    {
        $laboratorios = $this->inventarioService->getLaboratoriosPaginados($request);
        $filtros = $request->only(['q']);

        return view('inventario.laboratorios', compact('laboratorios', 'filtros'));
    }

    /**
     * (Recomendado) Reporte de Vencimientos
     */
    public function vencimientos(Request $request)
    {
        $productos = $this->inventarioService->getProductosPorVencer($request);
        $filtros = $request->only(['q', 'estado']);

        return view('inventario.vencimientos', compact('productos', 'filtros'));
    }
}