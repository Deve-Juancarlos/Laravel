<?php

namespace App\Http\Controllers\Contabilidad;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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

            // Normalize dates
            if (Carbon::parse($fechaInicio)->gt(Carbon::parse($fechaFin))) {
                [$fechaInicio, $fechaFin] = [$fechaFin, $fechaInicio];
            }

            // Obtener flujo de caja de actividades operativas
            $actividadesOperativas = $this->obtenerActividadesOperativas($fechaInicio, $fechaFin);

            // Obtener flujo de caja de actividades de inversión
            $actividadesInversion = $this->obtenerActividadesInversion($fechaInicio, $fechaFin);

            // Obtener flujo de caja de actividades de financiamiento
            $actividadesFinanciamiento = $this->obtenerActividadesFinanciamiento($fechaInicio, $fechaFin);

            // Calcular totales
            $totalOperativas = $actividadesOperativas['neto'] ?? 0;
            $totalInversion = $actividadesInversion['neto'] ?? 0;
            $totalFinanciamiento = $actividadesFinanciamiento['neto'] ?? 0;

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
            Log::error('Error en FlujoCajaController@index: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
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

            // Flujo de entrada (ingresos del día) - facturas (Tipo = 1)
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

            // Cobranzas del día - asumimos que los cobros registrados en caja tienen Tipo = 1 (ingresos)
            $cobranzas = DB::table('Caja')
                ->whereDate('Fecha', $fecha)
                ->where('Tipo', 1) // Ingresos / cobros
                ->select([
                    'Numero',
                    'Fecha',
                    'Documento',
                    'Monto',
                    'Moneda',
                    'Cambio'
                ])
                ->get();

            // Si prefieres usar PlanD_cobranza/PlanC_cobranza, reemplaza la consulta anterior por la correspondiente.

            // Flujo de salida (egresos del día)
            $egresos = $this->obtenerEgresosDiarios($fecha);

            // Resumen diario
            $ventasSum = (float) $ingresosDiarios->sum('Total');
            $cobranzasSum = (float) $cobranzas->sum('Monto');
            $totalIngresos = $ventasSum + $cobranzasSum;
            $totalEgresos = (float) $egresos['total'];

            $resumenDiario = [
                'fecha' => $fecha,
                'ventas_facturadas' => $ventasSum,
                'cobranzas' => $cobranzasSum,
                'total_ingresos' => $totalIngresos,
                'total_egresos' => $totalEgresos,
                'flujo_neto' => $totalIngresos - $totalEgresos
            ];

            return view('contabilidad.estados-financieros.flujo-diario', compact(
                'ingresosDiarios', 'cobranzas', 'egresos', 'resumenDiario', 'fecha'
            ));

        } catch (\Exception $e) {
            Log::error('Error en FlujoCajaController@flujoDiario: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return redirect()->back()->with('error', 'Error al cargar flujo diario: ' . $e->getMessage());
        }
    }

    /**
     * Get cash flow projections
     */
    public function proyecciones(Request $request)
    {
        try {
            $diasProyeccion = (int) $request->input('dias', 30);
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
            Log::error('Error en FlujoCajaController@proyecciones: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return redirect()->back()->with('error', 'Error al generar proyecciones: ' . $e->getMessage());
        }
    }

    /**
     * Get cash flow by periods
     */
    public function porPeriodos(Request $request)
    {
        try {
            $anio = (int) $request->input('anio', Carbon::now()->year);

            // Flujo de caja mensual del año
            $flujoMensual = [];
            for ($mes = 1; $mes <= 12; $mes++) {
                $fechaInicioMes = Carbon::create($anio, $mes, 1)->startOfMonth()->format('Y-m-d');
                $fechaFinMes = Carbon::create($anio, $mes, 1)->endOfMonth()->format('Y-m-d');

                $flujoMensual[$mes] = $this->calcularFlujoMensual($fechaInicioMes, $fechaFinMes);
                $flujoMensual[$mes]['mes'] = Carbon::create($anio, $mes, 1)->format('F');
            }

            // Análisis de tendencias
            $tendencias = $this->analizarTendenciasFlujo($flujoMensual);

            return view('contabilidad.estados-financieros.flujo-periodos', compact(
                'flujoMensual', 'tendencias', 'anio'
            ));

        } catch (\Exception $e) {
            Log::error('Error en FlujoCajaController@porPeriodos: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
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
            Log::error('Error en FlujoCajaController@farmacia: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
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
     * Get initial cash balance (sum ingresos - egresos before fechaInicio)
     */
    private function obtenerSaldoInicial($fechaInicio)
    {
        // Sumamos ingresos (Tipo = 1) y restamos egresos (Tipo = 2) en Caja antes de la fechaInicio
        $saldo = DB::table('Caja')
            ->whereDate('Fecha', '<', $fechaInicio)
            ->selectRaw('
                ISNULL(SUM(CASE WHEN Tipo = 1 THEN Monto ELSE -Monto END), 0) as saldo
            ')
            ->value('saldo');

        return (float) ($saldo ?? 0);
    }

    /**
     * Get cash flow projections (30 días por defecto)
     */
    private function obtenerProyecciones($fechaFin)
    {
        // Proyectar los próximos 30 días
        $proyecciones = [];
        for ($i = 1; $i <= 30; $i++) {
            $fechaProyeccion = Carbon::parse($fechaFin)->addDays($i);

            // Proyección basada en histórico (promedio de últimos 30 días comparables)
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
        // Obtener egresos del día desde caja (Tipo = 2)
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

        // Obtener pagos a proveedores del día (si están en otra tabla, implementar)
        $pagosProveedores = $this->obtenerPagosProveedoresFecha($fecha); // stub devuelve 0 por defecto

        $totalEgresos = (float)$egresosCaja->sum('Monto') + (float)$pagosProveedores;

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
        $ventas = (float) DB::table('Doccab')
            ->whereBetween('Fecha', [$fechaInicio, $fechaFin])
            ->where('Tipo', 1)
            ->where('Eliminado', 0)
            ->sum('Subtotal');

        // Join seguro Docdet con Doccab por Numero y Tipo (Docdet tiene campo Tipo)
        $costos = (float) DB::table('Docdet as dd')
            ->join('Doccab as dc', function($join) {
                $join->on('dd.Numero', '=', 'dc.Numero')
                     ->on('dd.Tipo', '=', 'dc.Tipo');
            })
            ->whereBetween('dc.Fecha', [$fechaInicio, $fechaFin])
            ->where('dc.Tipo', 1)
            ->where('dc.Eliminado', 0)
            ->selectRaw('ISNULL(SUM(CAST(dd.Costo AS MONEY) * dd.Cantidad),0) as suma')
            ->value('suma');

        // Gastos operativos desde t_detalle_diario (FechaF)
        $gastosOperativos = (float) DB::table('t_detalle_diario')
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
        // Depreciaciones, amortizaciones, etc. (buscamos en t_detalle_diario por descripción)
        $depreciaciones = (float) DB::table('t_detalle_diario')
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
     * Project daily income (average of last 30 days prior to $fecha)
     */
    private function proyectarIngresosUnDia($fecha)
    {
        $fecha = Carbon::parse($fecha)->startOfDay();
        $inicioWindow = $fecha->copy()->subDays(30);

        $promedioDiario = (float) DB::table('Doccab')
            ->where('Tipo', 1)
            ->where('Eliminado', 0)
            ->whereBetween('Fecha', [$inicioWindow->format('Y-m-d'), $fecha->subDay()->format('Y-m-d')])
            ->avg('Total') ?? 0;

        return $promedioDiario;
    }

    /**
     * Project daily expenses (average of last 30 days prior to $fecha)
     */
    private function proyectarEgresosUnDia($fecha)
    {
        $fecha = Carbon::parse($fecha)->startOfDay();
        $inicioWindow = $fecha->copy()->subDays(30);

        $promedioEgresos = (float) DB::table('Caja')
            ->where('Tipo', 2)
            ->whereBetween('Fecha', [$inicioWindow->format('Y-m-d'), $fecha->subDay()->format('Y-m-d')])
            ->avg('Monto') ?? 0;

        return $promedioEgresos;
    }

    /**
     * Get pharmaceutical inflows
     */
    private function obtenerIngresosFarmaceuticos($fechaInicio, $fechaFin)
    {
        return DB::table('Doccab as dc')
            ->join('Docdet as dd', function($join) {
                $join->on('dd.Numero', '=', 'dc.Numero')
                     ->on('dd.Tipo', '=', 'dc.Tipo');
            })
            ->join('Productos as p', 'dd.Codpro', '=', 'p.CodPro')
            ->whereBetween('dc.Fecha', [$fechaInicio, $fechaFin])
            ->where('dc.Tipo', 1)
            ->where('dc.Eliminado', 0)
            ->select([
                'p.Nombre as producto',
                DB::raw('SUM(CAST(dd.Subtotal as MONEY)) as total_ventas'),
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
        $fechaFinDt = Carbon::parse($fechaFin);
        $desde = $fechaFinDt->copy()->subDays(30)->format('Y-m-d');
        $hasta = $fechaFinDt->format('Y-m-d');

        $costoDiarioPromedio = 0;
        $sumaCosto = (float) DB::table('Docdet as dd')
            ->join('Doccab as dc', function($join) {
                $join->on('dd.Numero', '=', 'dc.Numero')
                     ->on('dd.Tipo', '=', 'dc.Tipo');
            })
            ->whereBetween('dc.Fecha', [$desde, $hasta])
            ->where('dc.Tipo', 1)
            ->where('dc.Eliminado', 0)
            ->selectRaw('ISNULL(SUM(CAST(dd.Costo AS MONEY) * dd.Cantidad),0) as suma')
            ->value('suma');

        $costoDiarioPromedio = $sumaCosto / 30;

        $inventarioActual = (float) DB::table('Saldos')
            ->selectRaw('ISNULL(SUM(CAST(saldo AS MONEY)), 0) as total_inventario')
            ->value('total_inventario');

        return $costoDiarioPromedio > 0 ? ($inventarioActual / $costoDiarioPromedio) : 0;
    }

    /**
     * Get monthly cash flow calculation
     */
    private function calcularFlujoMensual($fechaInicio, $fechaFin)
    {
        $actividadesOperativas = $this->obtenerActividadesOperativas($fechaInicio, $fechaFin);
        $actividadesInversion = $this->obtenerActividadesInversion($fechaInicio, $fechaFin);
        $actividadesFinanciamiento = $this->obtenerActividadesFinanciamiento($fechaInicio, $fechaFin);

        $oper = $actividadesOperativas['neto'] ?? 0;
        $inv = $actividadesInversion['neto'] ?? 0;
        $fin = $actividadesFinanciamiento['neto'] ?? 0;

        return [
            'operativas' => $oper,
            'inversion' => $inv,
            'financiamiento' => $fin,
            'neto' => $oper + $inv + $fin
        ];
    }

    /**
     * Analyze cash flow trends
     */
    private function analizarTendenciasFlujo($flujoMensual)
    {
        $flujos = array_map(function($m) { return (float)($m['neto'] ?? 0); }, $flujoMensual);
        $count = count($flujos);
        $promedio = $count > 0 ? array_sum($flujos) / $count : 0;
        $mesMayor = $count > 0 ? (int) array_search(max($flujos), $flujos) : null;
        $mesMenor = $count > 0 ? (int) array_search(min($flujos), $flujos) : null;

        return [
            'promedio_mensual' => $promedio,
            'mes_mayor_flujo' => $mesMayor,
            'mes_menor_flujo' => $mesMenor,
            'tendencia' => $this->calcularTendencia($flujos)
        ];
    }

    /**
     * Calculate trend
     */
    private function calcularTendencia($valores)
    {
        $n = count($valores);
        if ($n < 2) return 0;

        $primero = $valores[0];
        $ultimo = $valores[$n - 1];

        return $primero != 0 ? (($ultimo - $primero) / $primero) * 100 : 0;
    }

    /**
     * Generate cash flow alerts
     */
    private function generarAlertasFlujo($balanceProyectado)
    {
        $alertas = [];

        $proyectado = $balanceProyectado['proyectado'] ?? 0;
        $promedioDiario = $balanceProyectado['promedio_diario'] ?? 1;

        // Alerta de flujo negativo
        if ($proyectado < 0) {
            $alertas[] = [
                'tipo' => 'danger',
                'mensaje' => 'Flujo de caja proyectado en negativo',
                'valor' => $proyectado
            ];
        }

        // Alerta de flujo crítico (menos de 7 días de operación)
        $diasOperacion = $promedioDiario != 0 ? ($proyectado / $promedioDiario) : PHP_INT_MAX;
        if ($diasOperacion < 7) {
            $alertas[] = [
                'tipo' => 'warning',
                'mensaje' => 'Flujo de caja suficiente para menos de 7 días',
                'valor' => round($diasOperacion, 1)
            ];
        }

        return $alertas;
    }

    // Métodos auxiliares adicionales (stubs - implementar según tu lógica de negocio)
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
    private function calcularBalanceProyectado($fechaInicio, $ingresos, $egresos, $cobranzas) { 
        // Ejemplo simple: suma ingresos - egresos + cobranzas, y promedio diario base
        $totalIngresos = array_sum($ingresos ?: []);
        $totalEgresos = array_sum($egresos ?: []);
        $totalCobranzas = array_sum($cobranzas ?: []);
        $proyectado = $totalIngresos - $totalEgresos + $totalCobranzas;
        $dias = max(1, count($ingresos));
        $promedio_diario = $dias > 0 ? $proyectado / $dias : 0;
        return ['proyectado' => $proyectado, 'promedio_diario' => $promedio_diario];
    }
}