<?php

namespace App\Http\Controllers\Contabilidad;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class FlujoCajaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
            $fechaFin = $request->input('fecha_fin', Carbon::now()->endOfMonth()->format('Y-m-d'));

            // Obtener flujo de caja de actividades operativas
            $actividadesOperativas = $this->obtenerActividadesOperativas($fechaInicio, $fechaFin);

            // Obtener flujo de caja de actividades de inversión
            $actividadesInversion = $this->obtenerActividadesInversion($fechaInicio, $fechaFin);

            // Obtener flujo de caja de actividades de financiamiento
            $actividadesFinanciamiento = $this->obtenerActividadesFinanciamiento($fechaInicio, $fechaFin);

            // Calcular totales
            $totalOperativas = $actividadesOperativas['neto'];
            $totalInversion = $actividadesInversion['neto'];
            $totalFinanciamiento = $actividadesFinanciamiento['neto'];

            $flujoNeto = $totalOperativas + $totalInversion + $totalFinanciamiento;

            // Obtener saldo inicial y final
            $saldoInicial = $this->obtenerSaldoInicial($fechaInicio);
            $saldoFinal = $saldoInicial + $flujoNeto;

            // Obtener proyecciones
            $proyecciones = $this->obtenerProyecciones($fechaFin);

            return view('contabilidad.estados-financieros.flujo-caja', compact(
                'actividadesOperativas', 'actividadesInversion', 'actividadesFinanciamiento',
                'totalOperativas', 'totalInversion', 'totalFinanciamiento', 'flujoNeto',
                'saldoInicial', 'saldoFinal', 'fechaInicio', 'fechaFin', 'proyecciones'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al cargar el flujo de caja: ' . $e->getMessage());
        }
    }

    /**
     * Get daily cash flow
     */
    public function flujoDiario(Request $request)
    {
        try {
            $fecha = $request->input('fecha', Carbon::now()->format('Y-m-d'));

            // Flujo de entrada (ingresos del día)
            $ingresosDiarios = DB::table('Doccab')
                ->whereDate('Fecha', $fecha)
                ->where('Tipo', 1) // Facturas
                ->where('Eliminado', 0)
                ->select([
                    'Numero',
                    'Fecha',
                    'Subtotal',
                    'Total',
                    'Moneda',
                    'Cambio'
                ])
                ->get();

            // Cobranzas del día
            $cobranzas = DB::table('CtaCliente')
                ->whereDate('FechaF', $fecha)
                ->where('Saldo', '>', 0)
                ->select([
                    'Documento',
                    'CodClie',
                    'FechaF',
                    'Importe',
                    'Saldo'
                ])
                ->get();

            // Flujo de salida (egresos del día)
            $egresos = $this->obtenerEgresosDiarios($fecha);

            // Resumen diario
            $resumenDiario = [
                'fecha' => $fecha,
                'ventas_facturadas' => $ingresosDiarios->sum('Total'),
                'cobranzas' => $cobranzas->sum('Importe'),
                'total_ingresos' => $ingresosDiarios->sum('Total') + $cobranzas->sum('Importe'),
                'total_egresos' => $egresos['total'],
                'flujo_neto' => ($ingresosDiarios->sum('Total') + $cobranzas->sum('Importe')) - $egresos['total']
            ];

            return view('contabilidad.estados-financieros.flujo-diario', compact(
                'ingresosDiarios', 'cobranzas', 'egresos', 'resumenDiario', 'fecha'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al cargar flujo diario: ' . $e->getMessage());
        }
    }

    /**
     * Get cash flow projections
     */
    public function proyecciones(Request $request)
    {
        try {
            $diasProyeccion = $request->input('dias', 30);
            $fechaInicio = Carbon::now()->startOfDay();
            $fechaFin = $fechaInicio->copy()->addDays($diasProyeccion);

            // Proyección de ingresos
            $proyeccionIngresos = $this->proyectarIngresos($fechaInicio, $fechaFin);

            // Proyección de egresos
            $proyeccionEgresos = $this->proyectarEgresos($fechaInicio, $fechaFin);

            // Proyección de cobranzas
            $proyeccionCobranzas = $this->proyectarCobranzas($fechaInicio, $fechaFin);

            // Balance proyectado
            $balanceProyectado = $this->calcularBalanceProyectado($fechaInicio, $proyeccionIngresos, $proyeccionEgresos, $proyeccionCobranzas);

            // Alertas de flujo
            $alertas = $this->generarAlertasFlujo($balanceProyectado);

            return view('contabilidad.estados-financieros.flujo-proyecciones', compact(
                'proyeccionIngresos', 'proyeccionEgresos', 'proyeccionCobranzas', 
                'balanceProyectado', 'alertas', 'fechaInicio', 'fechaFin'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al generar proyecciones: ' . $e->getMessage());
        }
    }

    /**
     * Get cash flow by periods
     */
    public function porPeriodos(Request $request)
    {
        try {
            $anio = $request->input('anio', Carbon::now()->year);

            // Flujo de caja mensual del año
            $flujoMensual = [];
            for ($mes = 1; $mes <= 12; $mes++) {
                $fechaInicio = Carbon::create($anio, $mes, 1)->startOfMonth()->format('Y-m-d');
                $fechaFin = Carbon::create($anio, $mes, 1)->endOfMonth()->format('Y-m-d');

                $flujoMensual[$mes] = $this->calcularFlujoMensual($fechaInicio, $fechaFin);
                $flujoMensual[$mes]['mes'] = Carbon::create($anio, $mes, 1)->format('F');
            }

            // Análisis de tendencias
            $tendencias = $this->analizarTendenciasFlujo($flujoMensual);

            return view('contabilidad.estados-financieros.flujo-periodos', compact(
                'flujoMensual', 'tendencias', 'anio'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al analizar períodos: ' . $e->getMessage());
        }
    }

    /**
     * Get cash flow for pharmacy specifically
     */
    public function farmacia(Request $request)
    {
        try {
            $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
            $fechaFin = $request->input('fecha_fin', Carbon::now()->endOfMonth()->format('Y-m-d'));

            // Flujo específico de farmacia
            $flujoFarmaceutico = [
                'ingresos' => $this->obtenerIngresosFarmaceuticos($fechaInicio, $fechaFin),
                'egresos' => $this->obtenerEgresosFarmaceuticos($fechaInicio, $fechaFin),
                'inventario' => $this->obtenerMovimientosInventario($fechaInicio, $fechaFin),
                'mermas' => $this->obtenerCostoMermas($fechaInicio, $fechaFin)
            ];

            // Análisis de rotación de inventario
            $rotacionInventario = $this->calcularRotacionInventario($fechaInicio, $fechaFin);

            // Días de inventario disponible
            $diasInventario = $this->calcularDiasInventario($fechaFin);

            return view('contabilidad.estados-financieros.flujo-farmacia', compact(
                'flujoFarmaceutico', 'rotacionInventario', 'diasInventario', 'fechaInicio', 'fechaFin'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error en flujo farmacéutico: ' . $e->getMessage());
        }
    }

    /**
     * Get operational activities (indirect method)
     */
    private function obtenerActividadesOperativas($fechaInicio, $fechaFin)
    {
        // Utilidad neta (Estado de Resultados)
        $utilidadNeta = $this->obtenerUtilidadNeta($fechaInicio, $fechaFin);

        // Ajustes por partidas que no representan movimientos de efectivo
        $ajustes = $this->obtenerAjustesNoEfectivo($fechaInicio, $fechaFin);

        // Cambios en activos y pasivos de operación
        $cambiosCapitalTrabajo = $this->obtenerCambiosCapitalTrabajo($fechaInicio, $fechaFin);

        $flujoOperativo = $utilidadNeta + $ajustes + $cambiosCapitalTrabajo;

        return [
            'utilidad_neta' => $utilidadNeta,
            'ajustes' => $ajustes,
            'cambios_capital_trabajo' => $cambiosCapitalTrabajo,
            'neto' => $flujoOperativo,
            'detalle' => $this->obtenerDetalleActividadesOperativas($fechaInicio, $fechaFin)
        ];
    }

    /**
     * Get investment activities
     */
    private function obtenerActividadesInversion($fechaInicio, $fechaFin)
    {
        // Compras de activos fijos
        $comprasActivos = $this->obtenerComprasActivosFijos($fechaInicio, $fechaFin);

        // Ventas de activos fijos
        $ventasActivos = $this->obtenerVentasActivosFijos($fechaInicio, $fechaFin);

        $flujoInversion = $ventasActivos - $comprasActivos;

        return [
            'compras_activos' => $comprasActivos,
            'ventas_activos' => $ventasActivos,
            'neto' => $flujoInversion
        ];
    }

    /**
     * Get financing activities
     */
    private function obtenerActividadesFinanciamiento($fechaInicio, $fechaFin)
    {
        // Préstamos recibidos
        $prestamosRecibidos = $this->obtenerPrestamosRecibidos($fechaInicio, $fechaFin);

        // Pagos de préstamos
        $pagosPrestamos = $this->obtenerPagosPrestamos($fechaInicio, $fechaFin);

        // Dividendos pagados
        $dividendosPagados = $this->obtenerDividendosPagados($fechaInicio, $fechaFin);

        $flujoFinanciamiento = $prestamosRecibidos - $pagosPrestamos - $dividendosPagados;

        return [
            'prestamos_recibidos' => $prestamosRecibidos,
            'pagos_prestamos' => $pagosPrestamos,
            'dividendos_pagados' => $dividendosPagados,
            'neto' => $flujoFinanciamiento
        ];
    }

    /**
     * Get initial cash balance
     */
    private function obtenerSaldoInicial($fechaInicio)
    {
        // Buscar último movimiento de caja antes de la fecha inicio
        $saldoInicial = DB::table('Caja')
            ->where('Fecha', '<', $fechaInicio)
            ->orderBy('Fecha', 'desc')
            ->orderBy('Numero', 'desc')
            ->value('Monto') ?? 0;

        return $saldoInicial;
    }

    /**
     * Get cash flow projections
     */
    private function obtenerProyecciones($fechaFin)
    {
        // Proyectar los próximos 30 días
        $proyecciones = [];
        for ($i = 1; $i <= 30; $i++) {
            $fechaProyeccion = Carbon::parse($fechaFin)->addDays($i);
            
            // Proyección basada en histórico
            $proyeccionIngresos = $this->proyectarIngresosUnDia($fechaProyeccion);
            $proyeccionEgresos = $this->proyectarEgresosUnDia($fechaProyeccion);
            
            $proyecciones[] = [
                'fecha' => $fechaProyeccion->format('Y-m-d'),
                'ingresos_proyectados' => $proyeccionIngresos,
                'egresos_proyectados' => $proyeccionEgresos,
                'flujo_proyectado' => $proyeccionIngresos - $proyeccionEgresos
            ];
        }

        return $proyecciones;
    }

    /**
     * Get daily expenses
     */
    private function obtenerEgresosDiarios($fecha)
    {
        // Obtener egresos del día desde caja
        $egresosCaja = DB::table('Caja')
            ->whereDate('Fecha', $fecha)
            ->where('Tipo', 2) // Egresos
            ->select([
                'Numero',
                'Fecha',
                'Monto',
                'Moneda',
                'Cambio'
            ])
            ->get();

        // Obtener pagos a proveedores del día
        $pagosProveedores = $this->obtenerPagosProveedoresFecha($fecha);

        $totalEgresos = $egresosCaja->sum('Monto') + $pagosProveedores;

        return [
            'egresos_caja' => $egresosCaja,
            'pagos_proveedores' => $pagosProveedores,
            'total' => $totalEgresos
        ];
    }

    /**
     * Get net income from Estado de Resultados
     */
    private function obtenerUtilidadNeta($fechaInicio, $fechaFin)
    {
        // Calcular utilidad neta basada en el estado de resultados
        $ventas = DB::table('Doccab')
            ->whereBetween('Fecha', [$fechaInicio, $fechaFin])
            ->where('Tipo', 1)
            ->where('Eliminado', 0)
            ->sum('Subtotal');

        $costos = DB::table('Docdet as dd')
            ->join('Doccab as dc', 'dd.Numero', '=', 'dc.Numero')
            ->whereBetween('dc.Fecha', [$fechaInicio, $fechaFin])
            ->where('dc.Tipo', 1)
            ->where('dc.Eliminado', 0)
            ->sum(DB::raw('CAST(dd.Costo as MONEY) * dd.Cantidad'));

        $gastosOperativos = DB::table('t_detalle_diario')
            ->whereBetween('FechaF', [$fechaInicio, $fechaFin])
            ->where('Tipo', 'like', '5%')
            ->sum('Importe');

        return $ventas - $costos - $gastosOperativos;
    }

    /**
     * Get adjustments for non-cash items
     */
    private function obtenerAjustesNoEfectivo($fechaInicio, $fechaFin)
    {
        // Depreciaciones, amortizaciones, etc.
        $depreciaciones = DB::table('t_detalle_diario')
            ->whereBetween('FechaF', [$fechaInicio, $fechaFin])
            ->where('Descripcion', 'like', '%depreciaci%')
            ->sum('Importe');

        return $depreciaciones;
    }

    /**
     * Get working capital changes
     */
    private function obtenerCambiosCapitalTrabajo($fechaInicio, $fechaFin)
    {
        // Cambios en cuentas por cobrar
        $cambioCuentasCobrar = $this->obtenerCambioCuentasCobrar($fechaInicio, $fechaFin);

        // Cambios en inventarios
        $cambioInventarios = $this->obtenerCambioInventarios($fechaInicio, $fechaFin);

        // Cambios en cuentas por pagar
        $cambioCuentasPagar = $this->obtenerCambioCuentasPagar($fechaInicio, $fechaFin);

        return $cambioCuentasCobrar + $cambioInventarios + $cambioCuentasPagar;
    }

    /**
     * Get detailed operational activities
     */
    private function obtenerDetalleActividadesOperativas($fechaInicio, $fechaFin)
    {
        return [
            'utilidad_operativa' => $this->obtenerUtilidadOperativa($fechaInicio, $fechaFin),
            'depreciacion' => $this->obtenerAjustesNoEfectivo($fechaInicio, $fechaFin),
            'variacion_clientes' => $this->obtenerCambioCuentasCobrar($fechaInicio, $fechaFin),
            'variacion_inventarios' => $this->obtenerCambioInventarios($fechaInicio, $fechaFin),
            'variacion_proveedores' => $this->obtenerCambioCuentasPagar($fechaInicio, $fechaFin)
        ];
    }

    /**
     * Project daily income
     */
    private function proyectarIngresosUnDia($fecha)
    {
        // Promedio de ingresos de los últimos 30 días similares
        $promedioDiario = DB::table('Doccab')
            ->where('Tipo', 1)
            ->where('Eliminado', 0)
            ->whereDate('Fecha', '<', $fecha)
            ->orderBy('Fecha', 'desc')
            ->limit(30)
            ->avg('Total') ?? 0;

        return $promedioDiario;
    }

    /**
     * Project daily expenses
     */
    private function proyectarEgresosUnDia($fecha)
    {
        // Promedio de egresos de los últimos 30 días
        $promedioEgresos = DB::table('Caja')
            ->where('Tipo', 2)
            ->where('Fecha', '<', $fecha)
            ->orderBy('Fecha', 'desc')
            ->limit(30)
            ->avg('Monto') ?? 0;

        return $promedioEgresos;
    }

    /**
     * Get pharmaceutical inflows
     */
    private function obtenerIngresosFarmaceuticos($fechaInicio, $fechaFin)
    {
        return DB::table('Doccab as dc')
            ->join('Docdet as dd', 'dc.Numero', '=', 'dd.Numero')
            ->join('Productos as p', 'dd.Codpro', '=', 'p.CodPro')
            ->whereBetween('dc.Fecha', [$fechaInicio, $fechaFin])
            ->where('dc.Tipo', 1)
            ->where('dc.Eliminado', 0)
            ->select([
                'p.Nombre as producto',
                DB::raw('SUM(CAST(dc.Subtotal as MONEY)) as total_ventas'),
                DB::raw('COUNT(dd.Numero) as cantidad_transacciones')
            ])
            ->groupBy('p.CodPro', 'p.Nombre')
            ->orderBy('total_ventas', 'desc')
            ->get();
    }

    /**
     * Get pharmaceutical outflows
     */
    private function obtenerEgresosFarmaceuticos($fechaInicio, $fechaFin)
    {
        return [
            'compras_inventario' => $this->obtenerComprasInventario($fechaInicio, $fechaFin),
            'gastos_operativos' => $this->obtenerGastosOperativosFarmacia($fechaInicio, $fechaFin),
            'mermas' => $this->obtenerCostoMermas($fechaInicio, $fechaFin)
        ];
    }

    /**
     * Calculate inventory turnover
     */
    private function calcularRotacionInventario($fechaInicio, $fechaFin)
    {
        $costoVentas = $this->obtenerCostoVentas($fechaInicio, $fechaFin);
        $inventarioPromedio = $this->obtenerInventarioPromedio($fechaInicio, $fechaFin);

        return $inventarioPromedio > 0 ? $costoVentas / $inventarioPromedio : 0;
    }

    /**
     * Calculate inventory days
     */
    private function calcularDiasInventario($fechaFin)
    {
        $costoDiarioPromedio = DB::table('Docdet as dd')
            ->join('Doccab as dc', 'dd.Numero', '=', 'dc.Numero')
            ->where('dc.Fecha', '>=', Carbon::parse($fechaFin)->subDays(30))
            ->where('dc.Fecha', '<=', $fechaFin)
            ->where('dc.Tipo', 1)
            ->where('dc.Eliminado', 0)
            ->sum(DB::raw('CAST(dd.Costo as MONEY) * dd.Cantidad')) / 30;

        $inventarioActual = DB::table('Saldos')
            ->selectRaw('SUM(CAST(saldo as MONEY)) as total_inventario')
            ->value('total_inventario') ?? 0;

        return $costoDiarioPromedio > 0 ? $inventarioActual / $costoDiarioPromedio : 0;
    }

    /**
     * Get monthly cash flow calculation
     */
    private function calcularFlujoMensual($fechaInicio, $fechaFin)
    {
        $actividadesOperativas = $this->obtenerActividadesOperativas($fechaInicio, $fechaFin);
        $actividadesInversion = $this->obtenerActividadesInversion($fechaInicio, $fechaFin);
        $actividadesFinanciamiento = $this->obtenerActividadesFinanciamiento($fechaInicio, $fechaFin);

        return [
            'operativas' => $actividadesOperativas['neto'],
            'inversion' => $actividadesInversion['neto'],
            'financiamiento' => $actividadesFinanciamiento['neto'],
            'neto' => $actividadesOperativas['neto'] + $actividadesInversion['neto'] + $actividadesFinanciamiento['neto']
        ];
    }

    /**
     * Analyze cash flow trends
     */
    private function analizarTendenciasFlujo($flujoMensual)
    {
        $flujos = array_column($flujoMensual, 'neto');
        
        return [
            'promedio_mensual' => array_sum($flujos) / count($flujos),
            'mes_mayor_flujo' => array_keys($flujos, max($flujos))[0] ?? null,
            'mes_menor_flujo' => array_keys($flujos, min($flujos))[0] ?? null,
            'tendencia' => $this->calcularTendencia($flujos)
        ];
    }

    /**
     * Calculate trend
     */
    private function calcularTendencia($valores)
    {
        if (count($valores) < 2) return 0;
        
        $primero = $valores[0];
        $ultimo = $valores[array_key_last($valores)];
        
        return $primero > 0 ? (($ultimo - $primero) / $primero) * 100 : 0;
    }

    /**
     * Generate cash flow alerts
     */
    private function generarAlertasFlujo($balanceProyectado)
    {
        $alertas = [];
        
        // Alerta de flujo negativo
        if ($balanceProyectado['proyectado'] < 0) {
            $alertas[] = [
                'tipo' => 'danger',
                'mensaje' => 'Flujo de caja proyectado en negativo',
                'valor' => $balanceProyectado['proyectado']
            ];
        }
        
        // Alerta de flujo crítico (menos de 7 días de operación)
        $diasOperacion = $balanceProyectado['proyectado'] / ($balanceProyectado['promedio_diario'] ?? 1);
        if ($diasOperacion < 7) {
            $alertas[] = [
                'tipo' => 'warning',
                'mensaje' => 'Flujo de caja suficiente para menos de 7 días',
                'valor' => round($diasOperacion, 1)
            ];
        }
        
        return $alertas;
    }

    // Métodos auxiliares adicionales que usarías en un proyecto real
    private function obtenerComprasActivosFijos($fechaInicio, $fechaFin) { return 0; }
    private function obtenerVentasActivosFijos($fechaInicio, $fechaFin) { return 0; }
    private function obtenerPrestamosRecibidos($fechaInicio, $fechaFin) { return 0; }
    private function obtenerPagosPrestamos($fechaInicio, $fechaFin) { return 0; }
    private function obtenerDividendosPagados($fechaInicio, $fechaFin) { return 0; }
    private function obtenerPagosProveedoresFecha($fecha) { return 0; }
    private function obtenerUtilidadOperativa($fechaInicio, $fechaFin) { return 0; }
    private function obtenerCambioCuentasCobrar($fechaInicio, $fechaFin) { return 0; }
    private function obtenerCambioInventarios($fechaInicio, $fechaFin) { return 0; }
    private function obtenerCambioCuentasPagar($fechaInicio, $fechaFin) { return 0; }
    private function obtenerMovimientosInventario($fechaInicio, $fechaFin) { return 0; }
    private function obtenerCostoMermas($fechaInicio, $fechaFin) { return 0; }
    private function obtenerComprasInventario($fechaInicio, $fechaFin) { return 0; }
    private function obtenerGastosOperativosFarmacia($fechaInicio, $fechaFin) { return 0; }
    private function obtenerInventarioPromedio($fechaInicio, $fechaFin) { return 0; }
    private function proyectarIngresos($fechaInicio, $fechaFin) { return []; }
    private function proyectarEgresos($fechaInicio, $fechaFin) { return []; }
    private function proyectarCobranzas($fechaInicio, $fechaFin) { return []; }
    private function calcularBalanceProyectado($fechaInicio, $ingresos, $egresos, $cobranzas) { return []; }
}