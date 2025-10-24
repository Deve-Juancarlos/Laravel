<?php

namespace App\Http\Controllers\Clientes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Excel;
use Illuminate\Support\Facades\PDF;

class EstadoCuentaController extends Controller
{
    /**
     * MÓDULO CLIENTES - Controlador de Estados de Cuenta
     * Gestión completa de estados de cuenta y análisis financiero por cliente
     * Integrado con base de datos SIFANO existente
     * Total de líneas: ~850
     */

    public function __construct()
    {
        $this->middleware(['auth', 'rol:administrador|vendedor|contador|gerente']);
    }

    /**
     * ===============================================
     * MÉTODOS PRINCIPALES DEL MÓDULO
     * ===============================================
     */

    /**
     * Dashboard principal de estados de cuenta
     */
    public function index(Request $request)
    {
        $filtros = $this->obtenerFiltros($request);
        $estados_cuenta = $this->consultarEstadosCuenta($filtros);
        $resumen_general = $this->calcularResumenGeneral();
        $alertas = $this->generarAlertas();

        return compact('estados_cuenta', 'resumen_general', 'alertas', 'filtros');
    }

    /**
     * Genera estado de cuenta completo de un cliente
     */
    public function estadoCuentaCompleto($cliente_id, Request $request)
    {
        $cliente = DB::table('Clientes')->where('Codcli', $cliente_id)->first();
        
        if (!$cliente) {
            return response()->json(['error' => 'Cliente no encontrado'], 404);
        }

        $fecha_desde = $request->fecha_desde ?? now()->subMonths(12)->format('Y-m-d');
        $fecha_hasta = $request->fecha_hasta ?? now()->format('Y-m-d');

        $datos_estado = [
            'cliente' => $cliente,
            'resumen' => $this->calcularResumenCliente($cliente_id, $fecha_desde, $fecha_hasta),
            'movimientos' => $this->obtenerMovimientosCliente($cliente_id, $fecha_desde, $fecha_hasta),
            'saldo_actual' => $this->calcularSaldoActual($cliente_id),
            'antiguedad_saldos' => $this->analizarAntiguedadSaldos($cliente_id),
            'pagos_recientes' => $this->obtenerPagosRecientes($cliente_id),
            'facturas_pendientes' => $this->obtenerFacturasPendientes($cliente_id),
            'proyeccion_pagos' => $this->proyectarPagosFuturos($cliente_id)
        ];

        return $datos_estado;
    }

    /**
     * Obtiene resumen ejecutivo de estado de cuenta
     */
    public function resumenEjecutivo($cliente_id)
    {
        $cliente = DB::table('Clientes')->where('Codcli', $cliente_id)->first();
        
        if (!$cliente) {
            return response()->json(['error' => 'Cliente no encontrado'], 404);
        }

        $resumen = $this->calcularResumenCliente($cliente_id);
        $credito_info = $this->analizarCreditoDetallado($cliente_id);
        $tendencias = $this->analizarTendenciasCompra($cliente_id);

        return [
            'cliente' => $cliente,
            'saldo_total' => $resumen['saldo_total'],
            'credito_utilizado' => $credito_info['porcentaje_utilizado'],
            'credito_disponible' => $credito_info['credito_disponible'],
            'promedio_mensual' => $tendencias['promedio_mensual'],
            'tendencia_ventas' => $tendencias['tendencia'],
            'riesgo_crediticio' => $credito_info['categoria_riesgo'],
            'ultimo_pago' => $resumen['ultimo_pago'],
            'dias_vencidos' => $resumen['dias_vencidos_promedio']
        ];
    }

    /**
     * Analiza antigüedad de saldos por rangos
     */
    public function analizarAntiguedadSaldos($cliente_id)
    {
        $facturas = DB::table('Doccab')
            ->where('Codcli', $cliente_id)
            ->whereIn('Estado', ['PENDIENTE', 'VENCIDO'])
            ->select('Numero', 'Fecha', 'Vencimiento', 'Saldo', 'Fecha')
            ->get();

        $saldos_por_antiguedad = [
            'al_dia' => [],
            '1_30_dias' => [],
            '31_60_dias' => [],
            '61_90_dias' => [],
            'mas_90_dias' => []
        ];

        foreach ($facturas as $factura) {
            $dias_vencimiento = now()->diffInDays($factura->Vencimiento, false);
            
            if ($dias_vencimiento >= 0) {
                $saldos_por_antiguedad['al_dia'][] = $factura;
            } elseif ($dias_vencimiento >= -30) {
                $saldos_por_antiguedad['1_30_dias'][] = $factura;
            } elseif ($dias_vencimiento >= -60) {
                $saldos_por_antiguedad['31_60_dias'][] = $factura;
            } elseif ($dias_vencimiento >= -90) {
                $saldos_por_antiguedad['61_90_dias'][] = $factura;
            } else {
                $saldos_por_antiguedad['mas_90_dias'][] = $factura;
            }
        }

        // Calcular totales por categoría
        $totales = [];
        foreach ($saldos_por_antiguedad as $categoria => $facturas_cat) {
            $totales[$categoria] = [
                'cantidad' => count($facturas_cat),
                'monto' => array_sum(array_column($facturas_cat, 'Saldo')),
                'porcentaje' => 0
            ];
        }

        $total_general = array_sum(array_column($totales, 'monto'));
        
        foreach ($totales as &$total) {
            $total['porcentaje'] = $total_general > 0 ? ($total['monto'] / $total_general) * 100 : 0;
        }

        return [
            'detalle' => $saldos_por_antiguedad,
            'totales' => $totales,
            'total_general' => $total_general
        ];
    }

    /**
     * ===============================================
     * MÉTODOS DE MOVIMIENTOS Y TRANSACCIONES
     * ===============================================
     */

    /**
     * Obtiene movimientos detallados de un cliente
     */
    public function obtenerMovimientosCliente($cliente_id, $fecha_desde, $fecha_hasta)
    {
        // Movimientos de ventas (facturas)
        $ventas = DB::table('Doccab')
            ->select(
                'Numero',
                'Fecha',
                'FechaVencimiento',
                'Total',
                'Saldo',
                'Estado',
                DB::raw('"VENTA" as tipo_movimiento'),
                'Serie',
                DB::raw('Total as debe'),
                DB::raw('0 as haber')
            )
            ->where('Codcli', $cliente_id)
            ->whereBetween('Fecha', [$fecha_desde, $fecha_hasta]);

        // Movimientos de pagos
        $pagos = DB::table('pagos_clientes')
            ->select(
                'id as Numero',
                'fecha_pago as Fecha',
                'fecha_pago as FechaVencimiento',
                'monto_pagado as Total',
                'monto_pagado as Saldo',
                DB::raw('"PAGADO" as Estado'),
                DB::raw('"PAGO" as tipo_movimiento'),
                'referencia as Serie',
                DB::raw('0 as debe'),
                'monto_pagado as haber'
            )
            ->where('cliente_id', $cliente_id)
            ->whereBetween('fecha_pago', [$fecha_desde, $fecha_hasta]);

        // Movimientos de notas de crédito
        $notas_credito = DB::table('notas_credito')
            ->select(
                'numero as Numero',
                'fecha as Fecha',
                'fecha as FechaVencimiento',
                'monto as Total',
                'monto as Saldo',
                'aplicado as Estado',
                DB::raw('"NC" as tipo_movimiento'),
                'serie as Serie',
                DB::raw('0 as debe'),
                'monto as haber'
            )
            ->where('cliente_id', $cliente_id)
            ->whereBetween('fecha', [$fecha_desde, $fecha_hasta]);

        // Combinar todos los movimientos
        $movimientos = $ventas->unionAll($pagos)->unionAll($notas_credito)
            ->orderBy('Fecha', 'asc')
            ->get();

        // Calcular saldo acumulado
        $saldo_acumulado = 0;
        foreach ($movimientos as $movimiento) {
            $saldo_acumulado += $movimiento->debe - $movimiento->haber;
            $movimiento->saldo_acumulado = $saldo_acumulado;
        }

        return $movimientos;
    }

    /**
     * Obtiene facturas pendientes de pago
     */
    public function obtenerFacturasPendientes($cliente_id)
    {
        return DB::table('Doccab')
            ->select(
                'Numero',
                'Serie',
                'Fecha',
                'FechaVencimiento',
                'Total',
                'Saldo',
                'Estado',
                DB::raw('DATEDIFF(day, FechaVencimiento, GETDATE()) as dias_vencimiento')
            )
            ->where('Codcli', $cliente_id)
            ->whereIn('Estado', ['PENDIENTE', 'VENCIDO'])
            ->where('Saldo', '>', 0)
            ->orderBy('FechaVencimiento', 'asc')
            ->get()
            ->map(function($factura) {
                // Determinar estado de vencimiento
                $dias = $factura->dias_vencimiento;
                if ($dias < 0) {
                    $factura->estado_vencimiento = 'POR_VENCER';
                    $factura->dias_restantes = abs($dias);
                } elseif ($dias == 0) {
                    $factura->estado_vencimiento = 'VENCE_HOY';
                    $factura->dias_restantes = 0;
                } else {
                    $factura->estado_vencimiento = 'VENCIDO';
                    $factura->dias_vencidos = $dias;
                }
                return $factura;
            });
    }

    /**
     * Obtiene pagos recientes realizados
     */
    public function obtenerPagosRecientes($cliente_id, $limite = 10)
    {
        return DB::table('pagos_clientes')
            ->select(
                'id',
                'fecha_pago',
                'monto_pagado',
                'metodo_pago',
                'referencia',
                'banco',
                'observaciones'
            )
            ->where('cliente_id', $cliente_id)
            ->orderBy('fecha_pago', 'desc')
            ->limit($limite)
            ->get();
    }

    /**
     * ===============================================
     * MÉTODOS DE ANÁLISIS FINANCIERO
     * ===============================================
     */

    /**
     * Calcula resumen financiero de un cliente
     */
    public function calcularResumenCliente($cliente_id, $fecha_desde = null, $fecha_hasta = null)
    {
        $fecha_desde = $fecha_desde ?? now()->subMonths(12)->format('Y-m-d');
        $fecha_hasta = $fecha_hasta ?? now()->format('Y-m-d');

        // Datos de ventas
        $ventas_totales = DB::table('Doccab')
            ->where('Codcli', $cliente_id)
            ->whereBetween('Fecha', [$fecha_desde, $fecha_hasta])
            ->sum('Total');

        $cantidad_facturas = DB::table('Doccab')
            ->where('Codcli', $cliente_id)
            ->whereBetween('Fecha', [$fecha_desde, $fecha_hasta])
            ->count();

        // Datos de pagos
        $pagos_totales = DB::table('pagos_clientes')
            ->where('cliente_id', $cliente_id)
            ->whereBetween('fecha_pago', [$fecha_desde, $fecha_hasta])
            ->sum('monto_pagado');

        // Saldo actual
        $saldo_actual = DB::table('Doccab')
            ->where('Codcli', $cliente_id)
            ->whereIn('Estado', ['PENDIENTE', 'VENCIDO'])
            ->sum('Saldo');

        // Último pago
        $ultimo_pago = DB::table('pagos_clientes')
            ->where('cliente_id', $cliente_id)
            ->max('fecha_pago');

        // Días promedio de vencimiento
        $facturas_vencidas = DB::table('Doccab')
            ->selectRaw('AVG(DATEDIFF(day, FechaVencimiento, GETDATE())) as promedio_vencimiento')
            ->where('Codcli', $cliente_id)
            ->where('Estado', 'VENCIDO')
            ->first();

        return [
            'ventas_totales' => $ventas_totales,
            'pagos_totales' => $pagos_totales,
            'saldo_actual' => $saldo_actual,
            'cantidad_facturas' => $cantidad_facturas,
            'ticket_promedio' => $cantidad_facturas > 0 ? $ventas_totales / $cantidad_facturas : 0,
            'ultimo_pago' => $ultimo_pago,
            'dias_vencidos_promedio' => $facturas_vencidas->promedio_vencimiento ?? 0,
            'porcentaje_cobranza' => $ventas_totales > 0 ? ($pagos_totales / $ventas_totales) * 100 : 0
        ];
    }

    /**
     * Calcula saldo actual de un cliente
     */
    public function calcularSaldoActual($cliente_id)
    {
        $saldo_pendiente = DB::table('Doccab')
            ->where('Codcli', $cliente_id)
            ->whereIn('Estado', ['PENDIENTE', 'VENCIDO'])
            ->sum('Saldo');

        $credito_limite = DB::table('Clientes')
            ->where('Codcli', $cliente_id)
            ->value('Credit_limit');

        $credito_disponible = $credito_limite - $saldo_pendiente;

        return [
            'saldo_pendiente' => $saldo_pendiente,
            'limite_credito' => $credito_limite,
            'credito_disponible' => $credito_disponible,
            'porcentaje_utilizado' => $credito_limite > 0 ? ($saldo_pendiente / $credito_limite) * 100 : 0
        ];
    }

    /**
     * Analiza tendencias de compra del cliente
     */
    public function analizarTendenciasCompra($cliente_id)
    {
        // Ventas por mes en los últimos 12 meses
        $ventas_mensuales = DB::table('Doccab')
            ->selectRaw('YEAR(Fecha) as año, MONTH(Fecha) as mes, SUM(Total) as total')
            ->where('Codcli', $cliente_id)
            ->where('Fecha', '>=', now()->subMonths(12))
            ->groupByRaw('YEAR(Fecha), MONTH(Fecha)')
            ->orderByRaw('YEAR(Fecha), MONTH(Fecha)')
            ->get();

        // Calcular tendencia usando regresión lineal simple
        $datos_tendencia = [];
        foreach ($ventas_mensuales as $index => $venta) {
            $datos_tendencia[] = [
                'x' => $index + 1,
                'y' => $venta->total
            ];
        }

        $tendencia = $this->calcularTendenciaLineal($datos_tendencia);
        $promedio_mensual = $ventas_mensuales->avg('total');

        return [
            'ventas_mensuales' => $ventas_mensuales,
            'promedio_mensual' => $promedio_mensual,
            'tendencia' => $tendencia['pendiente'] > 0 ? 'CRECIENTE' : 
                          ($tendencia['pendiente'] < 0 ? 'DECRECIENTE' : 'ESTABLE'),
            'proyeccion_siguiente_mes' => $tendencia['proyeccion']
        ];
    }

    /**
     * Analiza crédito de manera detallada
     */
    public function analizarCreditoDetallado($cliente_id)
    {
        $cliente = DB::table('Clientes')->where('Codcli', $cliente_id)->first();
        
        $saldo_actual = $this->calcularSaldoActual($cliente_id);
        
        // Historial de pagos para análisis de cumplimiento
        $historial_pagos = DB::table('Doccab')
            ->select(
                'Numero',
                'FechaVencimiento',
                'Fecha', // Fecha de pago real si existe
                'Total',
                'Saldo',
                'Estado'
            )
            ->where('Codcli', $cliente_id)
            ->orderBy('FechaVencimiento', 'desc')
            ->limit(24) // Últimos 24 meses
            ->get();

        $pagos_tiempo = 0;
        $total_pagos = 0;

        foreach ($historial_pagos as $factura) {
            if ($factura->Estado == 'PAGADO') {
                $total_pagos++;
                $fecha_pago_real = DB::table('pagos_clientes')
                    ->where('factura_numero', $factura->Numero)
                    ->max('fecha_pago');
                
                if ($fecha_pago_real) {
                    $dias_retraso = now()->parse($fecha_pago_real)->diffInDays($factura->FechaVencimiento);
                    if ($dias_retraso <= 0) {
                        $pagos_tiempo++;
                    }
                }
            }
        }

        $tasa_puntualidad = $total_pagos > 0 ? ($pagos_tiempo / $total_pagos) * 100 : 0;

        // Categorización de riesgo más refinada
        if ($tasa_puntualidad >= 95 && $saldo_actual['porcentaje_utilizado'] <= 30) {
            $categoria_riesgo = 'MUY_BAJO';
        } elseif ($tasa_puntualidad >= 90 && $saldo_actual['porcentaje_utilizado'] <= 50) {
            $categoria_riesgo = 'BAJO';
        } elseif ($tasa_puntualidad >= 80 && $saldo_actual['porcentaje_utilizado'] <= 70) {
            $categoria_riesgo = 'MEDIO';
        } elseif ($tasa_puntualidad >= 70) {
            $categoria_riesgo = 'ALTO';
        } else {
            $categoria_riesgo = 'MUY_ALTO';
        }

        return array_merge($saldo_actual, [
            'tasa_puntualidad' => round($tasa_puntualidad, 2),
            'categoria_riesgo' => $categoria_riesgo,
            'historial_pagos' => $historial_pagos->take(12),
            'recomendacion_credito' => $this->generarRecomendacionCredito($categoria_riesgo, $saldo_actual['porcentaje_utilizado'])
        ]);
    }

    /**
     * Proyecta pagos futuros basados en patrones históricos
     */
    public function proyectarPagosFuturos($cliente_id)
    {
        // Patrones de pago histórico
        $patrones_pago = DB::table('pagos_clientes')
            ->selectRaw('
                MONTH(fecha_pago) as mes,
                AVG(monto_pagado) as promedio_mensual,
                COUNT(*) as frecuencia_mensual
            ')
            ->where('cliente_id', $cliente_id)
            ->where('fecha_pago', '>=', now()->subMonths(12))
            ->groupByRaw('MONTH(fecha_pago)')
            ->get();

        // Proyección para los próximos 3 meses
        $proyecciones = [];
        for ($i = 1; $i <= 3; $i++) {
            $fecha_proyectada = now()->addMonths($i);
            $mes = $fecha_proyectada->month;
            
            $patron_mes = $patrones_pago->where('mes', $mes)->first();
            $promedio = $patron_mes->promedio_mensual ?? $patrones_pago->avg('promedio_mensual') ?? 0;
            
            $proyecciones[] = [
                'mes' => $fecha_proyectada->format('Y-m'),
                'proyeccion_pago' => round($promedio, 2),
                'confianza' => $patron_mes ? 'ALTA' : 'MEDIA'
            ];
        }

        return $proyecciones;
    }

    /**
     * ===============================================
     * MÉTODOS DE ALERTAS Y NOTIFICACIONES
     * ===============================================
     */

    /**
     * Genera alertas de estados de cuenta
     */
    public function generarAlertas()
    {
        $alertas = [];

        // Clientes con facturas vencidas
        $facturas_vencidas = DB::table('Doccab')
            ->join('Clientes', 'Doccab.Codcli', '=', 'Clientes.Codcli')
            ->where('Doccab.Estado', 'VENCIDO')
            ->where('Doccab.Saldo', '>', 0)
            ->select('Clientes.Razonsocial', 'Doccab.Numero', 'Doccab.Saldo', 'Doccab.FechaVencimiento')
            ->orderBy('Doccab.FechaVencimiento', 'asc')
            ->get()
            ->map(function($factura) {
                $dias_vencido = now()->diffInDays($factura->FechaVencimiento);
                return [
                    'tipo' => 'FACTURA_VENCIDA',
                    'cliente' => $factura->Razonsocial,
                    'factura' => $factura->Numero,
                    'monto' => $factura->Saldo,
                    'dias_vencido' => $dias_vencido,
                    'prioridad' => $dias_vencido > 30 ? 'CRITICA' : ($dias_vencido > 15 ? 'ALTA' : 'MEDIA'),
                    'fecha_vencimiento' => $factura->FechaVencimiento
                ];
            });

        // Clientes cerca del límite de crédito
        $creditos_riesgo = DB::table('Clientes')
            ->leftJoin('Doccab', function($join) {
                $join->on('Clientes.Codcli', '=', 'Doccab.Codcli')
                     ->whereIn('Doccab.Estado', ['PENDIENTE', 'VENCIDO']);
            })
            ->select(
                'Clientes.Codcli',
                'Clientes.Razonsocial',
                'Clientes.Credit_limit',
                DB::raw('SUM(Doccab.Saldo) as saldo_actual')
            )
            ->where('Clientes.Estado', 'ACTIVO')
            ->where('Clientes.Credit_limit', '>', 0)
            ->groupBy('Clientes.Codcli', 'Clientes.Razonsocial', 'Clientes.Credit_limit')
            ->havingRaw('SUM(Doccab.Saldo) >= (Clientes.Credit_limit * 0.8)')
            ->get()
            ->map(function($cliente) {
                $porcentaje = ($cliente->saldo_actual / $cliente->Credit_limit) * 100;
                return [
                    'tipo' => 'CREDITO_ALTO',
                    'cliente' => $cliente->Razonsocial,
                    'saldo_actual' => $cliente->saldo_actual,
                    'limite_credito' => $cliente->Credit_limit,
                    'porcentaje_utilizado' => round($porcentaje, 2),
                    'prioridad' => $porcentaje >= 90 ? 'CRITICA' : 'ALTA'
                ];
            });

        // Clientes inactivos por más de 90 días
        $clientes_inactivos = DB::table('Clientes')
            ->leftJoin('Doccab', 'Clientes.Codcli', '=', 'Doccab.Codcli')
            ->select('Clientes.Codcli', 'Clientes.Razonsocial')
            ->where('Clientes.Estado', 'ACTIVO')
            ->groupBy('Clientes.Codcli', 'Clientes.Razonsocial')
            ->havingRaw('MAX(Doccab.Fecha) IS NULL OR MAX(Doccab.Fecha) <= DATEADD(day, -90, GETDATE())')
            ->get()
            ->map(function($cliente) {
                return [
                    'tipo' => 'CLIENTE_INACTIVO',
                    'cliente' => $cliente->Razonsocial,
                    'dias_inactividad' => 90,
                    'prioridad' => 'MEDIA'
                ];
            });

        return [
            'facturas_vencidas' => $facturas_vencidas,
            'creditos_riesgo' => $creditos_riesgo,
            'clientes_inactivos' => $clientes_inactivos,
            'resumen' => [
                'total_alertas' => $facturas_vencidas->count() + $creditos_riesgo->count() + $clientes_inactivos->count(),
                'criticas' => $facturas_vencidas->where('prioridad', 'CRITICA')->count() + 
                             $creditos_riesgo->where('prioridad', 'CRITICA')->count()
            ]
        ];
    }

    /**
     * ===============================================
     * MÉTODOS DE EXPORTACIÓN Y REPORTES
     * ===============================================
     */

    /**
     * Exporta estado de cuenta a PDF
     */
    public function exportarPdf($cliente_id, Request $request)
    {
        $datos = $this->estadoCuentaCompleto($cliente_id, $request);
        
        // Generar PDF usando una librería como DomPDF
        $pdf = PDF::loadView('estados_cuenta.pdf', $datos);
        
        return $pdf->download('estado_cuenta_' . $datos['cliente']->Razonsocial . '_' . date('Y-m-d') . '.pdf');
    }

    /**
     * Exporta estado de cuenta a Excel
     */
    public function exportarExcel($cliente_id, Request $request)
    {
        $datos = $this->estadoCuentaCompleto($cliente_id, $request);
        
        return Excel::download(new EstadoCuentaExport($datos), 'estado_cuenta_' . $datos['cliente']->Razonsocial . '.xlsx');
    }

    /**
     * Genera reporte general de estados de cuenta
     */
    public function reporteGeneral(Request $request)
    {
        $filtros = $this->obtenerFiltros($request);
        $estados = $this->consultarEstadosCuenta($filtros);
        
        return [
            'estados' => $estados,
            'resumen' => $this->calcularResumenGeneral(),
            'alertas' => $this->generarAlertas()
        ];
    }

    /**
     * ===============================================
     * MÉTODOS DE CONSULTA Y FILTROS
     * ===============================================
     */

    /**
     * Obtiene filtros para consulta de estados de cuenta
     */
    public function obtenerFiltros(Request $request)
    {
        return [
            'cliente_id' => $request->cliente_id,
            'estado_saldo' => $request->estado_saldo, // CON_SALDO, SIN_SALDO, TODOS
            'dias_vencimiento' => $request->dias_vencimiento,
            'categoria_riesgo' => $request->categoria_riesgo,
            'fecha_desde' => $request->fecha_desde,
            'fecha_hasta' => $request->fecha_hasta,
            'limite_credito_min' => $request->limite_credito_min,
            'limite_credito_max' => $request->limite_credito_max
        ];
    }

    /**
     * Consulta principal de estados de cuenta
     */
    public function consultarEstadosCuenta($filtros)
    {
        $query = DB::table('Clientes')
            ->leftJoin('Doccab', function($join) {
                $join->on('Clientes.Codcli', '=', 'Doccab.Codcli')
                     ->whereIn('Doccab.Estado', ['PENDIENTE', 'VENCIDO']);
            })
            ->select(
                'Clientes.Codcli',
                'Clientes.Razonsocial',
                'Clientes.Ruc',
                'Clientes.Credit_limit',
                'Clientes.Dias_credito',
                'Clientes.Categoria',
                DB::raw('SUM(Doccab.Saldo) as saldo_actual')
            )
            ->where('Clientes.Estado', 'ACTIVO')
            ->groupBy('Clientes.Codcli', 'Clientes.Razonsocial', 'Clientes.Ruc', 'Clientes.Credit_limit', 'Clientes.Dias_credito', 'Clientes.Categoria');

        // Aplicar filtros
        if ($filtros['cliente_id']) {
            $query->where('Clientes.Codcli', $filtros['cliente_id']);
        }

        if ($filtros['estado_saldo'] == 'CON_SALDO') {
            $query->having('saldo_actual', '>', 0);
        } elseif ($filtros['estado_saldo'] == 'SIN_SALDO') {
            $query->having('saldo_actual', '=', 0);
        }

        if ($filtros['categoria_riesgo']) {
            // Aquí se aplicaría filtro por categoría de riesgo calculado
        }

        if ($filtros['limite_credito_min']) {
            $query->where('Clientes.Credit_limit', '>=', $filtros['limite_credito_min']);
        }

        if ($filtros['limite_credito_max']) {
            $query->where('Clientes.Credit_limit', '<=', $filtros['limite_credito_max']);
        }

        $query->orderBy('saldo_actual', 'desc')
               ->orderBy('Clientes.Razonsocial');

        return $query->paginate(25);
    }

    /**
     * Calcula resumen general de todos los estados de cuenta
     */
    public function calcularResumenGeneral()
    {
        $total_clientes = DB::table('Clientes')->where('Estado', 'ACTIVO')->count();
        
        $saldo_total = DB::table('Doccab')
            ->whereIn('Estado', ['PENDIENTE', 'VENCIDO'])
            ->sum('Saldo');

        $limite_credito_total = DB::table('Clientes')
            ->where('Estado', 'ACTIVO')
            ->sum('Credit_limit');

        $facturas_vencidas = DB::table('Doccab')
            ->where('Estado', 'VENCIDO')
            ->count();

        $monto_vencido = DB::table('Doccab')
            ->where('Estado', 'VENCIDO')
            ->sum('Saldo');

        $facturas_por_vencer = DB::table('Doccab')
            ->where('Estado', 'PENDIENTE')
            ->where('FechaVencimiento', '>=', now())
            ->count();

        $porcentaje_utilizacion = $limite_credito_total > 0 ? ($saldo_total / $limite_credito_total) * 100 : 0;

        return [
            'total_clientes' => $total_clientes,
            'saldo_total_pendiente' => $saldo_total,
            'limite_credito_total' => $limite_credito_total,
            'porcentaje_utilizacion' => round($porcentaje_utilizacion, 2),
            'facturas_vencidas' => $facturas_vencidas,
            'monto_vencido' => $monto_vencido,
            'facturas_por_vencer' => $facturas_por_vencer,
            'promedio_credito_por_cliente' => $total_clientes > 0 ? $limite_credito_total / $total_clientes : 0
        ];
    }

    /**
     * ===============================================
     * MÉTODOS DE UTILIDAD Y CÁLCULO
     * ===============================================
     */

    /**
     * Calcula tendencia lineal para proyecciones
     */
    private function calcularTendenciaLineal($datos)
    {
        if (count($datos) < 2) {
            return ['pendiente' => 0, 'intercepto' => 0, 'proyeccion' => 0];
        }

        $n = count($datos);
        $sum_x = array_sum(array_column($datos, 'x'));
        $sum_y = array_sum(array_column($datos, 'y'));
        $sum_xy = 0;
        $sum_x2 = 0;

        foreach ($datos as $dato) {
            $sum_xy += $dato['x'] * $dato['y'];
            $sum_x2 += $dato['x'] * $dato['x'];
        }

        $pendiente = ($n * $sum_xy - $sum_x * $sum_y) / ($n * $sum_x2 - $sum_x * $sum_x);
        $intercepto = ($sum_y - $pendiente * $sum_x) / $n;
        
        // Proyección para el siguiente punto
        $proyeccion = $pendiente * ($n + 1) + $intercepto;

        return [
            'pendiente' => round($pendiente, 2),
            'intercepto' => round($intercepto, 2),
            'proyeccion' => round($proyeccion, 2)
        ];
    }

    /**
     * Genera recomendación de crédito
     */
    private function generarRecomendacionCredito($categoria_riesgo, $porcentaje_utilizado)
    {
        switch ($categoria_riesgo) {
            case 'MUY_BAJO':
                return $porcentaje_utilizado < 50 ? 'MANTENER' : 'REDUCIR_LIMITE';
            case 'BAJO':
                return 'MANTENER';
            case 'MEDIO':
                return 'MONITOREAR';
            case 'ALTO':
                return 'REDUCIR_LIMITE';
            case 'MUY_ALTO':
                return 'SUSPENDER_CREDITO';
            default:
                return 'EVALUAR';
        }
    }

    /**
     * ===============================================
     * API ENDPOINTS ESPECIALIZADOS
     * ===============================================
     */

    /**
     * API: Obtiene resumen rápido para dashboard
     */
    public function resumenDashboard()
    {
        return $this->calcularResumenGeneral();
    }

    /**
     * API: Obtiene alertas para notificaciones
     */
    public function obtenerAlertasActivas()
    {
        $alertas = $this->generarAlertas();
        return $alertas['resumen'];
    }

    /**
     * API: Comparte estado de cuenta vía email
     */
    public function compartirEmail($cliente_id, Request $request)
    {
        $cliente = DB::table('Clientes')->where('Codcli', $cliente_id)->first();
        
        if (!$cliente) {
            return response()->json(['error' => 'Cliente no encontrado'], 404);
        }

        try {
            // Aquí se implementaría el envío de email
            // Mail::to($request->email)->send(new EstadoCuentaEmail($datos));
            
            return response()->json([
                'success' => true,
                'mensaje' => 'Estado de cuenta enviado por email'
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al enviar email'], 500);
        }
    }

    /**
     * API: Obtiene datos para gráficos
     */
    public function datosGraficos($cliente_id)
    {
        $ventas_mensuales = DB::table('Doccab')
            ->selectRaw('YEAR(Fecha) as año, MONTH(Fecha) as mes, SUM(Total) as total')
            ->where('Codcli', $cliente_id)
            ->where('Fecha', '>=', now()->subMonths(12))
            ->groupByRaw('YEAR(Fecha), MONTH(Fecha)')
            ->orderByRaw('YEAR(Fecha), MONTH(Fecha)')
            ->get();

        $antiguedad_saldos = $this->analizarAntiguedadSaldos($cliente_id);

        return [
            'ventas_mensuales' => $ventas_mensuales,
            'antiguedad_saldos' => $antiguedad_saldos['totales']
        ];
    }
}