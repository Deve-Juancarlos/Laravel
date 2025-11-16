<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\ReporteService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


class ReporteController extends Controller
{
    protected $reporteService;

    public function __construct(ReporteService $reporteService)
    {
        $this->reporteService = $reporteService;
    }

    /**
     * Reporte de Ventas por Período
     */
    public function ventasPorPeriodo(Request $request)
    {
        $fechaInicio = $request->get('fecha_inicio', Carbon::now()->startOfMonth());
        $fechaFin = $request->get('fecha_fin', Carbon::now()->endOfMonth());
        
        $ventas = $this->reporteService->reporteVentasPeriodo($fechaInicio, $fechaFin);
        
        return view('admin.reportes.ventas-periodo', compact('ventas', 'fechaInicio', 'fechaFin'));
    }

    /**
     * Reporte de Ventas por Cliente
     */
    public function ventasPorCliente(Request $request)
    {
        $fechaInicio = $request->get('fecha_inicio', Carbon::now()->startOfMonth());
        $fechaFin = $request->get('fecha_fin', Carbon::now()->endOfMonth());
        
        $ventas = $this->reporteService->reporteVentasPorCliente($fechaInicio, $fechaFin);
        
        return view('admin.reportes.ventas-cliente', compact('ventas', 'fechaInicio', 'fechaFin'));
    }

    /**
     * Reporte de Ventas por Producto
     */
    public function ventasPorProducto(Request $request)
    {
        $fechaInicio = $request->get('fecha_inicio', Carbon::now()->startOfMonth());
        $fechaFin = $request->get('fecha_fin', Carbon::now()->endOfMonth());
        
        $productos = $this->reporteService->reporteVentasPorProducto($fechaInicio, $fechaFin);
        
        return view('admin.reportes.ventas-producto', compact('productos', 'fechaInicio', 'fechaFin'));
    }

    /**
     * Reporte de Ventas por Vendedor
     */
    public function ventasPorVendedor(Request $request)
    {
        $fechaInicio = $request->get('fecha_inicio', Carbon::now()->startOfMonth());
        $fechaFin = $request->get('fecha_fin', Carbon::now()->endOfMonth());
        
        $vendedores = $this->reporteService->reporteVentasPorVendedor($fechaInicio, $fechaFin);
        
        return view('admin.reportes.ventas-vendedor', compact('vendedores', 'fechaInicio', 'fechaFin'));
    }

    /**
     * Reporte de Cuentas por Cobrar (Aging)
     */
    public function cuentasPorCobrar(Request $request)
    {
        $aging = $this->reporteService->reporteAgingCuentasPorCobrar();
        $resumen = $this->reporteService->resumenCuentasPorCobrar();
        
        return view('admin.reportes.cuentas-cobrar', compact('aging', 'resumen'));
    }

  
    /**
     * Reporte de Inventario Valorizado
     */
    public function inventarioValorado(Request $request)
    {
        $tipoAgrupacion = $request->get('agrupar', 'laboratorio'); // laboratorio, producto, almacen
        
        $inventario = $this->reporteService->reporteInventarioValorado($tipoAgrupacion);
        
        return view('admin.reportes.inventario-valorado', compact('inventario', 'tipoAgrupacion'));
    }

    /**
     * Reporte de Productos por Vencer
     */
    public function productosVencer(Request $request)
    {
        $dias = $request->get('dias', 60);
        
        $productos = $this->reporteService->reporteProductosVencer($dias);
        
        return view('admin.reportes.productos-vencer', compact('productos', 'dias'));
    }


    /**
     * Registro de Compras SUNAT (Formato 8.1)
     */
    public function registroComprasSunat(Request $request)
    {
        $periodo = $request->get('periodo', Carbon::now()->format('Y-m'));
        
        $registros = $this->reporteService->reporteRegistroComprasSunat($periodo);
        
        return view('admin.reportes.sunat-compras', compact('registros', 'periodo'));
    }

    /**
     * Reporte de Rentabilidad por Producto
     */
    public function rentabilidadProductos(Request $request)
    {
        $fechaInicio = $request->get('fecha_inicio', Carbon::now()->startOfMonth());
        $fechaFin = $request->get('fecha_fin', Carbon::now()->endOfMonth());
        
        $rentabilidad = $this->reporteService->reporteRentabilidadProductos($fechaInicio, $fechaFin);
        
        return view('admin.reportes.rentabilidad-productos', compact('rentabilidad', 'fechaInicio', 'fechaFin'));
    }

    /**
     * Exportar a Excel
     */
    public function exportarExcel(Request $request)
    {
        $tipo = $request->get('tipo'); // ventas, inventario, cxc, etc.
        $fechaInicio = $request->get('fecha_inicio');
        $fechaFin = $request->get('fecha_fin');
        
        return $this->reporteService->exportarExcel($tipo, $fechaInicio, $fechaFin);
    }
    /**
 * Reporte de Facturas (Documento SUNAT 01 y 03)
 */
    public function facturas(Request $request)
    {
        $fechaInicio = $request->get('fecha_inicio', Carbon::now()->startOfMonth());
        $fechaFin = $request->get('fecha_fin', Carbon::now()->endOfMonth());

        $facturas = DB::table('Doccab')
            ->join('Clientes', 'Doccab.CodClie', '=', 'Clientes.Codclie')
            ->join('Empleados', 'Doccab.Vendedor', '=', 'Empleados.Codemp')
            ->join('TiposDocumentoSUNAT', 'Doccab.tipo_documento_sunat', '=', 'TiposDocumentoSUNAT.Codigo')
            ->select(
                'Doccab.Numero',
                'Doccab.Tipo',
                'Doccab.Fecha',
                'Clientes.Razon as cliente',
                'Empleados.Nombre as vendedor',
                'TiposDocumentoSUNAT.Descripcion as tipo_doc',
                'Doccab.Subtotal',
                'Doccab.Igv',
                'Doccab.Total',
                'Doccab.estado_sunat'
            )
            ->whereBetween('Doccab.Fecha', [$fechaInicio, $fechaFin])
            ->where('Doccab.Eliminado', 0)
            ->whereIn('Doccab.tipo_documento_sunat', ['01', '03']) // Facturas y Boletas
            ->orderBy('Doccab.Fecha', 'desc')
            ->get();

        return view('admin.reportes.facturas', compact('facturas', 'fechaInicio', 'fechaFin'));
    }

    

}
