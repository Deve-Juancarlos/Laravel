<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Dashboard principal del administrador
     * Vista general del negocio
     */
    public function index()
    {
        $data = [
            'kpis' => $this->dashboardService->obtenerKPIsGenerales(),
            'ventasHoy' => $this->dashboardService->ventasDelDia(),
            'ventasMes' => $this->dashboardService->ventasDelMes(),
            'alertasCriticas' => $this->dashboardService->alertasCriticas(),
            'topProductos' => $this->dashboardService->productosTop(10),
            'topClientes' => $this->dashboardService->clientesTop(10),
            'cuentasPorCobrar' => $this->dashboardService->resumenCuentasPorCobrar(),
            'cuentasPorPagar' => $this->dashboardService->resumenCuentasPorPagar(),
            'stockCritico' => $this->dashboardService->productosStockMinimo(),
            'productosVencer' => $this->dashboardService->productosProximosVencer(30),
        ];

        return view('admin.dashboard.index', compact('data'));
    }

    /**
     * Obtener datos para gráficos del dashboard
     * Se llama vía AJAX para actualizar gráficos
     */
    public function graficos(Request $request)
    {
        $tipo = $request->get('tipo', 'ventas_mes');
        
        $graficos = [
            'ventas_mes' => $this->dashboardService->graficoVentasPorMes(12),
            'ventas_laboratorio' => $this->dashboardService->graficoVentasPorLaboratorio(),
            'ventas_vendedor' => $this->dashboardService->graficoVentasPorVendedor(),
            'flujo_efectivo' => $this->dashboardService->graficoFlujoCaja(30),
            'aging_cartera' => $this->dashboardService->graficoAgingCartera(),
        ];

        return response()->json($graficos[$tipo] ?? []);
    }

    /**
     * Resumen ejecutivo para imprimir o exportar
     */
    public function resumenEjecutivo(Request $request)
    {
        $periodo = $request->get('periodo', 'mes_actual');
        
        $resumen = $this->dashboardService->generarResumenEjecutivo($periodo);
        
        return view('admin.dashboard.resumen-ejecutivo', compact('resumen', 'periodo'));
    }
}
