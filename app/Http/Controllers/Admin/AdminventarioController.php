<?php

namespace App\Http\Controllers;

use App\Services\InventarioService;
use Illuminate\Http\Request;

class AdminventarioController extends Controller
{
    protected $inventarioService;

    public function __construct(InventarioService $inventarioService)
    {
        $this->inventarioService = $inventarioService;
    }

    public function index()
    {
        $estadisticas = $this->inventarioService->obtenerEstadisticasGenerales();
        $productos = $this->inventarioService->obtenerProductos();
        $laboratorios = $this->inventarioService->obtenerLaboratorios();

        return view('admin/inventario.index', compact('estadisticas', 'productos', 'laboratorios'));
    }

    public function buscar(Request $request)
    {
        $filtros = $request->only(['buscar', 'laboratorio', 'stock_minimo']);
        $productos = $this->inventarioService->obtenerProductos($filtros);
        return response()->json($productos);
    }

    public function porVencer()
    {
        $productos = $this->inventarioService->obtenerProductosPorVencerCompleto();
        return view('admin/inventario.por-vencer', compact('productos'));
    }

    public function stockCritico()
    {
        $productos = $this->inventarioService->obtenerProductosStockCritico();
        return view('admin/inventario.stock-critico', compact('productos'));
    }

    public function detalle($codpro)
    {
        $producto = $this->inventarioService->obtenerProducto($codpro);
        $saldos = $this->inventarioService->obtenerSaldosPorProducto($codpro);
        $valorizacion = $this->inventarioService->calcularValorizacion($codpro);

        if (!$producto) {
            abort(404);
        }

        return view('admin/inventario.detalle', compact('producto', 'saldos', 'valorizacion'));
    }
}