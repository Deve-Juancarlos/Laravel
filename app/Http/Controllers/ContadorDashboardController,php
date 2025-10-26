<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ContadorDashboardController extends Controller
{
    /**
     * Dashboard principal del contador
     */
    public function contadorDashboard(Request $request)
    {
        try {
            // Métricas principales
            $ventasMes = $this->calcularVentasMes();
            $cuentasPorCobrar = $this->calcularCuentasPorCobrar();
            $clientesActivos = $this->contarClientesActivos();
            $facturasPendientes = $this->contarFacturasPendientes();

            // Datos para gráficos
            $mesesLabels = $this->obtenerMesesLabels();
            $ventasData = $this->obtenerVentasPorMes();

            // Ventas recientes
            $ventasRecientes = $this->obtenerVentasRecientes();

            // Alertas
            $alertas = $this->generarAlertas();

            return view('contador.dashboard', compact(
                'ventasMes',
                'cuentasPorCobrar', 
                'clientesActivos',
                'facturasPendientes',
                'mesesLabels',
                'ventasData',
                'ventasRecientes',
                'alertas'
            ));

        } catch (\Exception $e) {
            \Log::error('Error en dashboard contador: ' . $e->getMessage());
            
            return view('contador.dashboard', [
                'ventasMes' => 0,
                'cuentasPorCobrar' => 0,
                'clientesActivos' => 0,
                'facturasPendientes' => 0,
                'mesesLabels' => [],
                'ventasData' => [],
                'ventasRecientes' => [],
                'alertas' => [[
                    'tipo' => 'danger',
                    'icono' => 'exclamation-triangle',
                    'mensaje' => 'Error al cargar los datos. Intente nuevamente.'
                ]]
            ]);
        }
    }

    /**
     * Calcular ventas del mes actual
     */
    private function calcularVentasMes()
    {
        $total = DB::table('Doccab')
            ->whereYear('Fecha', now()->year)
            ->whereMonth('Fecha', now()->month)
            ->where('Eliminado', 0)
            ->sum('Total');

        return $total ?? 0;
    }

    /**
     * Calcular cuentas por cobrar
     */
    private function calcularCuentasPorCobrar()
    {
        $total = DB::table('CtaCliente')
            ->where('Saldo', '>', 0)
            ->sum('Saldo');

        return $total ?? 0;
    }

    /**
     * Contar clientes activos
     */
    private function contarClientesActivos()
    {
        $total = DB::table('Clientes')
            ->where('Activo', 1)
            ->count();

        return $total ?? 0;
    }

    /**
     * Contar facturas pendientes
     */
    private function contarFacturasPendientes()
    {
        $total = DB::table('CtaCliente')
            ->where('Saldo', '>', 0)
            ->count();

        return $total ?? 0;
    }

    /**
     * Obtener labels de meses
     */
    private function obtenerMesesLabels()
    {
        $meses = [];
        for ($i = 5; $i >= 0; $i--) {
            $fecha = now()->subMonths($i);
            $meses[] = $fecha->format('M');
        }
        return $meses;
    }

    /**
     * Obtener datos de ventas por mes
     */
    private function obtenerVentasPorMes()
    {
        $ventas = [];
        for ($i = 5; $i >= 0; $i--) {
            $fecha = now()->subMonths($i);
            $total = DB::table('Doccab')
                ->whereYear('Fecha', $fecha->year)
                ->whereMonth('Fecha', $fecha->month)
                ->where('Eliminado', 0)
                ->sum('Total');
            $ventas[] = $total ?? 0;
        }
        return $ventas;
    }

    /**
     * Obtener ventas recientes
     */
    private function obtenerVentasRecientes()
    {
        $ventas = DB::table('Doccab as d')
            ->leftJoin('Clientes as c', 'd.CodClie', '=', 'c.Codclie')
            ->where('d.Eliminado', 0)
            ->select(
                'd.Numero',
                'd.Fecha',
                'd.Total',
                'c.Razon as cliente',
                DB::raw("CASE WHEN cc.Saldo > 0 THEN 'PENDIENTE' ELSE 'PAGADA' END as estado")
            )
            ->leftJoin('CtaCliente as cc', function($join) {
                $join->on('d.Numero', '=', 'cc.Documento')
                     ->on('d.Tipo', '=', 'cc.Tipo');
            })
            ->orderBy('d.Fecha', 'desc')
            ->limit(10)
            ->get();

        return $ventas->map(function($venta) {
            return [
                'numero' => $venta->Numero,
                'cliente' => $venta->cliente ?? 'Cliente no encontrado',
                'fecha' => $venta->Fecha,
                'total' => $venta->Total,
                'estado' => $venta->estado
            ];
        })->toArray();
    }

    /**
     * Generar alertas
     */
    private function generarAlertas()
    {
        $alertas = [];

        // Facturas vencidas
        $facturasVencidas = DB::table('CtaCliente')
            ->where('FechaV', '<', now())
            ->where('Saldo', '>', 0)
            ->count();

        if ($facturasVencidas > 0) {
            $alertas[] = [
                'tipo' => 'warning',
                'icono' => 'exclamation-triangle',
                'mensaje' => "Tienes {$facturasVencidas} facturas vencidas por cobrar"
            ];
        }

        // Clientes sin actividad
        $clientesSinActividad = DB::table('Clientes as c')
            ->leftJoin('Doccab as d', 'c.Codclie', '=', 'd.CodClie')
            ->where('c.Activo', 1)
            ->whereRaw('(d.Fecha IS NULL OR d.Fecha < DATE_SUB(NOW(), INTERVAL 3 MONTH))')
            ->count();

        if ($clientesSinActividad > 5) {
            $alertas[] = [
                'tipo' => 'info',
                'icono' => 'users',
                'mensaje' => "{$clientesSinActividad} clientes sin actividad reciente"
            ];
        }

        // Productos con stock bajo
        $productosStockBajo = DB::table('Productos')
            ->where('Stock', '<=', 'Minimo')
            ->where('Stock', '>', 0)
            ->count();

        if ($productosStockBajo > 0) {
            $alertas[] = [
                'tipo' => 'danger',
                'icono' => 'exclamation-circle',
                'mensaje' => "{$productosStockBajo} productos con stock bajo"
            ];
        }

        return $alertas;
    }

    /**
     * Estadísticas generales
     */
    public function getStats(Request $request)
    {
        try {
            $stats = [
                'ventas_hoy' => DB::table('Doccab')
                    ->whereDate('Fecha', today())
                    ->where('Eliminado', 0)
                    ->sum('Total'),
                'ventas_mes' => $this->calcularVentasMes(),
                'clientes_activos' => $this->contarClientesActivos(),
                'facturas_pendientes' => $this->contarFacturasPendientes(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas'
            ], 500);
        }
    }
}
