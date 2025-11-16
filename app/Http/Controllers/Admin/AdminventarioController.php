<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\InventarioService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $productosCriticos = $this->inventarioService->obtenerProductosCriticos();
        $productosVencer = $this->inventarioService->obtenerProductosPorVencer(); // <-- Esta línea faltaba

        return view('admin.inventario.index', compact(
            'estadisticas',
            'productos',
            'laboratorios',
            'productosCriticos',
            'productosVencer'
        ));
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
        return view('admin.inventario.por-vencer', compact('productos'));
    }

    public function stockCritico()
    {
        $productos = $this->inventarioService->obtenerProductosStockCritico();
        return view('admin.inventario.stock-critico', compact('productos'));
    }

    public function detalle($codpro)
    {
        $producto = $this->inventarioService->obtenerProducto($codpro);
        $saldos = $this->inventarioService->obtenerSaldosPorProducto($codpro);
        $valorizacion = $this->inventarioService->calcularValorizacion($codpro);

        if (!$producto) {
            abort(404);
        }

        return view('admin.inventario.detalle', compact('producto', 'saldos', 'valorizacion'));
    }

    public function productos(Request $request)
    {
        $filtros = [
            'buscar' => $request->get('buscar', ''),
            'laboratorio' => $request->get('laboratorio', ''),
            'stock_minimo' => $request->get('stock_minimo', ''), // <--- nueva línea
        ];

        $productos = DB::table('Productos')
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
            ->groupBy('Productos.CodPro', 'Productos.Nombre', 'Laboratorios.Descripcion', 'Productos.Costo', 'Productos.PventaMa', 'Productos.Eliminado')
            ->orderBy('Productos.Nombre', 'asc')
            ->get();

        $laboratorios = DB::table('Laboratorios')
            ->whereNotNull('Descripcion')
            ->orderBy('Descripcion', 'asc')
            ->pluck('Descripcion');

        return view('admin.inventario.productos', compact('productos', 'laboratorios', 'filtros'));
    }

    public function valorizacion()
    {
        $totales = $this->inventarioService->obtenerValorizacionTotal();
        $porLaboratorio = $this->inventarioService->obtenerValorizacionPorLaboratorio();

        return view('admin.inventario.valorizacion', compact('totales', 'porLaboratorio'));
    }

    public function rotacion()
{
    $rotacion = $this->inventarioService->obtenerRotacionProductos();
    return view('admin.inventario.rotacion', compact('rotacion'));
}

}