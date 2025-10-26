<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display the main dashboard.
     */
    public function index()
    {
        $user = Auth::user();
        
        $data = [
            'user' => $user,
            'rol' => $user->rol ?? 'Usuario',
            'estadisticas_generales' => $this->getEstadisticasGenerales(),
            'alertas_activas' => $this->getAlertasActivas(),
            'actividad_reciente' => $this->getActividadReciente(),
            'metricas_tiempo_real' => $this->getMetricasTiempoReal(),
        ];

        return view('dashboard.index', compact('data'));
    }

    /**
     * Get dashboard statistics via API.
     */
    public function getStats()
    {
        $estadisticas = $this->getEstadisticasGenerales();

        return response()->json($estadisticas);
    }

    /**
     * Get dashboard alerts via API.
     */
    public function getAlerts()
    {
        $alertas = $this->getAlertasActivas();

        return response()->json($alertas);
    }

    /**
     * Get general system statistics.
     */
    private function getEstadisticasGenerales()
    {
        return [
            'ventas_hoy' => [
                'monto' => 8940.50,
                'transacciones' => 25,
                'cambio' => 12.5,
                'cambio_tipo' => 'incremento'
            ],
            'ventas_mes' => [
                'monto' => 156780.90,
                'transacciones' => 425,
                'cambio' => 8.3,
                'cambio_tipo' => 'incremento'
            ],
            'productos_stock' => [
                'total' => 1847,
                'stock_bajo' => 45,
                'vencidos' => 8,
                'alerta_critica' => 3
            ],
            'clientes_activos' => [
                'total' => 234,
                'nuevos_mes' => 18,
                'credito_utilizado' => 89450.30,
                'dias_promedio_pago' => 15
            ],
            'temperatura_promedio' => [
                'valor' => 22.5,
                'estado' => 'normal',
                'equipos_monitoreo' => 8,
                'alertas_pendientes' => 2
            ]
        ];
    }

    /**
     * Get active system alerts.
     */
    private function getAlertasActivas()
    {
        return collect([
            [
                'id' => 1,
                'tipo' => 'warning',
                'titulo' => 'Stock Bajo',
                'mensaje' => 'El producto Paracetamol 500mg tiene stock bajo (5 unidades)',
                'modulo' => 'inventario',
                'fecha' => '2025-10-26 09:15:00',
                'prioridad' => 'media',
                'enlace' => '/farmacia/inventario/alertas-stock'
            ],
            [
                'id' => 2,
                'tipo' => 'danger',
                'titulo' => 'Temperatura Fuera de Rango',
                'mensaje' => 'Sensor T-003 registró 28°C (máximo 25°C)',
                'modulo' => 'temperatura',
                'fecha' => '2025-10-26 08:45:00',
                'prioridad' => 'alta',
                'enlace' => '/farmacia/temperatura/alertas'
            ],
            [
                'id' => 3,
                'tipo' => 'info',
                'titulo' => 'Producto Próximo a Vencer',
                'mensaje' => '15 productos vencerán en los próximos 30 días',
                'modulo' => 'inventario',
                'fecha' => '2025-10-26 07:30:00',
                'prioridad' => 'baja',
                'enlace' => '/farmacia/inventario'
            ],
            [
                'id' => 4,
                'tipo' => 'warning',
                'titulo' => 'Factura Vencida',
                'mensaje' => 'La factura F001-085 de Botica Central está vencida (8 días)',
                'modulo' => 'cuentas-cobrar',
                'fecha' => '2025-10-25 16:20:00',
                'prioridad' => 'media',
                'enlace' => '/ventas/cuentas-cobrar'
            ]
        ]);
    }

    /**
     * Get recent system activity.
     */
    private function getActividadReciente()
    {
        return collect([
            [
                'accion' => 'Venta procesada',
                'usuario' => 'María González',
                'descripcion' => 'Venta de $1,250.80 - Farmacia San Rafael',
                'fecha' => '2025-10-26 09:45:00',
                'tipo' => 'success'
            ],
            [
                'accion' => 'Inventario actualizado',
                'usuario' => 'Carlos López',
                'descripcion' => 'Entrada de 50 unidades - Amoxicilina 500mg',
                'fecha' => '2025-10-26 09:30:00',
                'tipo' => 'info'
            ],
            [
                'accion' => 'Temperatura verificada',
                'usuario' => 'Sistema',
                'descripcion' => 'Sensores de refrigeración funcionando correctamente',
                'fecha' => '2025-10-26 09:00:00',
                'tipo' => 'info'
            ],
            [
                'accion' => 'Factura generada',
                'usuario' => 'Ana Martínez',
                'descripcion' => 'Factura F001-156 - $890.50',
                'fecha' => '2025-10-26 08:45:00',
                'tipo' => 'success'
            ],
            [
                'accion' => 'Alerta resuelta',
                'usuario' => 'Roberto Silva',
                'descripcion' => 'Alerta de temperatura sensor T-001 resuelta',
                'fecha' => '2025-10-26 08:15:00',
                'tipo' => 'warning'
            ]
        ]);
    }

    /**
     * Get real-time system metrics.
     */
    private function getMetricasTiempoReal()
    {
        return [
            'ventas_hora_actual' => [
                'monto' => 2450.80,
                'transacciones' => 8,
                'meta_hora' => 3000.00,
                'progreso' => 81.7
            ],
            'ocupacion_sistema' => [
                'cpu' => 45.2,
                'memoria' => 68.5,
                'disco' => 34.1,
                'estado' => 'normal'
            ],
            'conexiones_activas' => [
                'usuarios' => 12,
                'sesiones' => 15,
                'max_conexiones' => 100,
                'porcentaje' => 15
            ],
            'operaciones_segundo' => [
                'transacciones' => 2.3,
                'requests_api' => 8.7,
                'consultas_db' => 15.2,
                'estado' => 'optimo'
            ]
        ];
    }
}