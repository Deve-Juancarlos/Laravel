<?php

namespace App\Http\Controllers\Farmacia;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display the pharmacy dashboard.
     */
    public function index()
    {
        $data = [
            'control_temperatura' => $this->getControlTemperatura(),
            'inventario_general' => $this->getInventarioGeneral(),
            'trazabilidad' => $this->getTrazabilidad(),
            'alertas_farmacia' => $this->getAlertasFarmacia(),
            'metricas_calidad' => $this->getMetricasCalidad(),
        ];

        return view('farmacia.dashboard', compact('data'));
    }

    /**
     * Get temperature control data.
     */
    private function getControlTemperatura()
    {
        return [
            'sensores_activos' => 8,
            'temperatura_promedio' => 22.5,
            'alertas_activas' => 2,
            'equipos_faltantes' => 0,
            'historial_24h' => [
                'temp_min' => 20.8,
                'temp_max' => 24.2,
                'horas_normales' => 23,
                'horas_alerta' => 1
            ]
        ];
    }

    /**
     * Get general inventory data.
     */
    private function getInventarioGeneral()
    {
        return [
            'total_productos' => 1847,
            'stock_valor' => 245680.50,
            'productos_vencidos' => 8,
            'stock_bajo' => 45,
            'productos_controlados' => 156,
            'proximos_vencer' => 23
        ];
    }

    /**
     * Get traceability data.
     */
    private function getTrazabilidad()
    {
        return [
            'lotes_activos' => 342,
            'movimientos_hoy' => 89,
            'productos_trazables' => 892,
            'alertas_fedefarma' => 1,
            'compliance' => 98.5
        ];
    }

    /**
     * Get pharmacy alerts.
     */
    private function getAlertasFarmacia()
    {
        return [
            'temperatura' => [
                'criticas' => 1,
                'warnings' => 2,
                'normales' => 5
            ],
            'inventario' => [
                'stock_bajo' => 45,
                'vencidos' => 8,
                'controlados_bajo' => 3
            ],
            'trazabilidad' => [
                'fedefarma' => 1,
                'lotes_vencer' => 5,
                'movimientos_faltantes' => 0
            ]
        ];
    }

    /**
     * Get quality metrics.
     */
    private function getMetricasCalidad()
    {
        return [
            'cumplimiento_temp' => 98.2,
            'precision_inventario' => 99.1,
            'trazabilidad_completa' => 97.8,
            'ordenes_sin_error' => 99.5,
            'tiempo_respuesta_promedio' => 2.3
        ];
    }
}