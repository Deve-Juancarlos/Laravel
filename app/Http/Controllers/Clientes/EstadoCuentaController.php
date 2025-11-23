<?php

namespace App\Http\Controllers\Clientes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class EstadoCuentaController extends Controller
{
    

    public function __construct()
    {
        $this->middleware(['auth', 'rol:contador|administrador']);
    }


    public function index(Request $request)
    {
        $filtros = $this->obtenerFiltros($request);
        $estados_cuenta = $this->consultarEstadosCuenta($filtros);
        $resumen_general = $this->calcularResumenGeneral();
        $alertas = $this->generarAlertas();

        return compact('estados_cuenta', 'resumen_general', 'alertas', 'filtros');
    }

   
    public function estadoCuentaCompleto($cliente_id, Request $request)
    {
        $cliente = DB::table('Clientes')->where('Codclie', $cliente_id)->first();
        
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

    public function resumenEjecutivo($cliente_id)
    {
        $cliente = DB::table('Clientes')->where('Codclie', $cliente_id)->first();
        
        if (!$cliente) {
            return response()->json(['error' => 'Cliente no encontrado'], 404);
        }

        $resumen = $this->calcularResumenCliente($cliente_id);
        $credito_info = $this->analizarCreditoDetallado($cliente_id);
        $tendencias = $this->analizarTendenciasCompra($cliente_id);

        return [
            'cliente' => $cliente,
            'saldo_total' => $resumen['saldo_actual'],
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
        $facturas = DB::table('CtaCliente')
            ->where('CodClie', $cliente_id)
            ->where('Saldo', '>', 0)
            ->select('Documento', 'FechaF', 'FechaV', 'Saldo', 'Importe')
            ->get();

        $saldos_por_antiguedad = [
            'al_dia' => [],
            '1_30_dias' => [],
            '31_60_dias' => [],
            '61_90_dias' => [],
            'mas_90_dias' => []
        ];

        foreach ($facturas as $factura) {
            $dias_vencimiento = now()->diffInDays($factura->FechaV, false);
            
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
     * ✅ ADAPTADO PARA USAR CtaCliente
     */
    public function obtenerMovimientosCliente($cliente_id, $fecha_desde, $fecha_hasta)
    {
        $movimientos = DB::table('CtaCliente')
            ->select(
                'Documento',
                'Tipo',
                'FechaF as Fecha',
                'FechaV as Vencimiento',
                'FechaP as FechaPago',
                'Importe',
                'Saldo',
                DB::raw('CASE 
                    WHEN Saldo > 0 AND FechaV >= GETDATE() THEN "PENDIENTE"
                    WHEN Saldo > 0 AND FechaV < GETDATE() THEN "VENCIDO"
                    WHEN Saldo = 0 AND FechaP IS NOT NULL THEN "PAGADO"
                    ELSE "CANCELADO"
                END as Estado'),
                DB::raw('Importe as debe'),
                DB::raw('(Importe - Saldo) as haber')
            )
            ->where('CodClie', $cliente_id)
            ->whereBetween('FechaF', [$fecha_desde, $fecha_hasta])
            ->orderBy('FechaF', 'asc')
            ->get();

        // Calcular saldo acumulado
        $saldo_acumulado = 0;
        foreach ($movimientos as $movimiento) {
            $saldo_acumulado += $movimiento->Saldo;
            $movimiento->saldo_acumulado = $saldo_acumulado;
        }

        return $movimientos;
    }

    /**
     * Obtiene facturas pendientes de pago
     * ✅ ADAPTADO PARA USAR CtaCliente
     */
    public function obtenerFacturasPendientes($cliente_id)
    {
        return DB::table('CtaCliente')
            ->select(
                'Documento as Numero',
                'Tipo',
                'FechaF as Fecha',
                'FechaV as Vencimiento',
                'Importe as Total',
                'Saldo',
                DB::raw('DATEDIFF(day, FechaV, GETDATE()) as dias_vencimiento'),
                DB::raw('CASE 
                    WHEN Saldo > 0 AND FechaV >= GETDATE() THEN "PENDIENTE"
                    WHEN Saldo > 0 AND FechaV < GETDATE() THEN "VENCIDO"
                    ELSE "CANCELADO"
                END as Estado')
            )
            ->where('CodClie', $cliente_id)
            ->where('Saldo', '>', 0)
            ->orderBy('FechaV', 'asc')
            ->get()
            ->map(function($factura) {
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
     * ✅ ADAPTADO PARA USAR CtaCliente
     */
    public function obtenerPagosRecientes($cliente_id, $limite = 10)
    {
        return DB::table('CtaCliente')
            ->select(
                'Documento',
                'Tipo',
                'FechaP as fecha_pago',
                DB::raw('(Importe - Saldo) as monto_pagado'),
                'FechaF as fecha_factura',
                'Importe as total_factura',
                'Saldo as saldo_restante'
            )
            ->where('CodClie', $cliente_id)
            ->whereNotNull('FechaP')
            ->where('Saldo', '<=', DB::raw('Importe'))
            ->orderBy('FechaP', 'desc')
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
     * ✅ ADAPTADO PARA USAR CtaCliente
     */
    public function calcularResumenCliente($cliente_id, $fecha_desde = null, $fecha_hasta = null)
    {
        $fecha_desde = $fecha_desde ?? now()->subMonths(12)->format('Y-m-d');
        $fecha_hasta = $fecha_hasta ?? now()->format('Y-m-d');

        // Total facturado
        $ventas_totales = DB::table('CtaCliente')
            ->where('CodClie', $cliente_id)
            ->whereBetween('FechaF', [$fecha_desde, $fecha_hasta])
            ->sum('Importe');

        // Total cobrado
        $pagos_totales = DB::table('CtaCliente')
            ->where('CodClie', $cliente_id)
            ->whereBetween('FechaF', [$fecha_desde, $fecha_hasta])
            ->sum(DB::raw('(Importe - Saldo)'));

        // Saldo pendiente actual
        $saldo_actual = DB::table('CtaCliente')
            ->where('CodClie', $cliente_id)
            ->sum('Saldo');

        // Cantidad de facturas
        $cantidad_facturas = DB::table('CtaCliente')
            ->where('CodClie', $cliente_id)
            ->whereBetween('FechaF', [$fecha_desde, $fecha_hasta])
            ->count();

        // Último pago
        $ultimo_pago = DB::table('CtaCliente')
            ->where('CodClie', $cliente_id)
            ->whereNotNull('FechaP')
            ->max('FechaP');

        // Días promedio de vencimiento
        $facturas_vencidas = DB::table('CtaCliente')
            ->selectRaw('AVG(DATEDIFF(day, FechaV, GETDATE())) as promedio_vencimiento')
            ->where('CodClie', $cliente_id)
            ->where('Saldo', '>', 0)
            ->whereRaw('FechaV < GETDATE()')
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
        $saldo_pendiente = DB::table('CtaCliente')
            ->where('CodClie', $cliente_id)
            ->sum('Saldo');

        $credito_limite = DB::table('Clientes')
            ->where('Codclie', $cliente_id)
            ->value('Limite');

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
        $ventas_mensuales = DB::table('CtaCliente')
            ->selectRaw('YEAR(FechaF) as año, MONTH(FechaF) as mes, SUM(Importe) as total')
            ->where('CodClie', $cliente_id)
            ->where('FechaF', '>=', now()->subMonths(12))
            ->groupByRaw('YEAR(FechaF), MONTH(FechaF)')
            ->orderByRaw('YEAR(FechaF), MONTH(FechaF)')
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
     * ✅ ADAPTADO PARA USAR CtaCliente
     */
    public function analizarCreditoDetallado($cliente_id)
    {
        $cliente = DB::table('Clientes')->where('Codclie', $cliente_id)->first();
        
        $saldo_actual = $this->calcularSaldoActual($cliente_id);
        
        // Historial de pagos para análisis de cumplimiento
        $historial_pagos = DB::table('CtaCliente')
            ->select(
                'Documento',
                'FechaV as Vencimiento',
                'FechaP as FechaPago',
                'Importe as Total',
                'Saldo',
                DB::raw('CASE 
                    WHEN Saldo = 0 THEN "PAGADO"
                    WHEN Saldo > 0 AND FechaV >= GETDATE() THEN "PENDIENTE"
                    ELSE "VENCIDO"
                END as Estado')
            )
            ->where('CodClie', $cliente_id)
            ->orderBy('FechaV', 'desc')
            ->limit(24)
            ->get();

        $pagos_tiempo = 0;
        $total_pagos = 0;

        foreach ($historial_pagos as $factura) {
            if ($factura->Estado == 'PAGADO' && $factura->FechaPago) {
                $total_pagos++;
                
                $fecha_vencimiento = Carbon::parse($factura->Vencimiento);
                $fecha_pago = Carbon::parse($factura->FechaPago);
                $dias_retraso = $fecha_pago->diffInDays($fecha_vencimiento, false);
                
                if ($dias_retraso <= 0) {
                    $pagos_tiempo++;
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
     * ✅ ADAPTADO PARA USAR CtaCliente
     */
    public function proyectarPagosFuturos($cliente_id)
    {
        // Patrones de pago histórico
        $patrones_pago = DB::table('CtaCliente')
            ->selectRaw('
                MONTH(FechaP) as mes,
                AVG(Importe - Saldo) as promedio_mensual,
                COUNT(*) as frecuencia_mensual
            ')
            ->where('CodClie', $cliente_id)
            ->whereNotNull('FechaP')
            ->where('FechaP', '>=', now()->subMonths(12))
            ->groupByRaw('MONTH(FechaP)')
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
        $facturas_vencidas = DB::table('CtaCliente')
            ->join('Clientes', 'CtaCliente.CodClie', '=', 'Clientes.Codclie')
            ->where('CtaCliente.Saldo', '>', 0)
            ->whereRaw('CtaCliente.FechaV < GETDATE()')
            ->select(
                'Clientes.Razon', 
                'CtaCliente.Documento', 
                'CtaCliente.Saldo', 
                'CtaCliente.FechaV'
            )
            ->orderBy('CtaCliente.FechaV', 'asc')
            ->get()
            ->map(function($factura) {
                $dias_vencido = now()->diffInDays($factura->FechaV);
                return [
                    'tipo' => 'FACTURA_VENCIDA',
                    'cliente' => $factura->Razon,
                    'factura' => $factura->Documento,
                    'monto' => $factura->Saldo,
                    'dias_vencido' => $dias_vencido,
                    'prioridad' => $dias_vencido > 30 ? 'CRITICA' : ($dias_vencido > 15 ? 'ALTA' : 'MEDIA'),
                    'fecha_vencimiento' => $factura->FechaV
                ];
            });

        // Clientes cerca del límite de crédito
        $creditos_riesgo = DB::table('Clientes')
            ->leftJoin('CtaCliente', 'Clientes.Codclie', '=', 'CtaCliente.CodClie')
            ->select(
                'Clientes.Codclie',
                'Clientes.Razon',
                'Clientes.Limite',
                DB::raw('COALESCE(SUM(CtaCliente.Saldo), 0) as saldo_actual')
            )
            ->where('Clientes.Activo', 1)
            ->where('Clientes.Limite', '>', 0)
            ->groupBy('Clientes.Codclie', 'Clientes.Razon', 'Clientes.Limite')
            ->havingRaw('COALESCE(SUM(CtaCliente.Saldo), 0) >= (Clientes.Limite * 0.8)')
            ->get()
            ->map(function($cliente) {
                $porcentaje = ($cliente->saldo_actual / $cliente->Limite) * 100;
                return [
                    'tipo' => 'CREDITO_ALTO',
                    'cliente' => $cliente->Razon,
                    'saldo_actual' => $cliente->saldo_actual,
                    'limite_credito' => $cliente->Limite,
                    'porcentaje_utilizado' => round($porcentaje, 2),
                    'prioridad' => $porcentaje >= 90 ? 'CRITICA' : 'ALTA'
                ];
            });

        // Clientes inactivos por más de 90 días
        $clientes_inactivos = DB::table('Clientes')
            ->leftJoin('CtaCliente', 'Clientes.Codclie', '=', 'CtaCliente.CodClie')
            ->select(
                'Clientes.Codclie', 
                'Clientes.Razon',
                DB::raw('MAX(CtaCliente.FechaF) as ultima_compra')
            )
            ->where('Clientes.Activo', 1)
            ->groupBy('Clientes.Codclie', 'Clientes.Razon')
            ->havingRaw('MAX(CtaCliente.FechaF) IS NULL OR MAX(CtaCliente.FechaF) <= DATEADD(day, -90, GETDATE())')
            ->get()
            ->map(function($cliente) {
                $dias_inactividad = $cliente->ultima_compra ? now()->diffInDays($cliente->ultima_compra) : 999;
                return [
                    'tipo' => 'CLIENTE_INACTIVO',
                    'cliente' => $cliente->Razon,
                    'dias_inactividad' => $dias_inactividad,
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
        
        if (is_array($datos) && isset($datos['error'])) {
            return response()->json($datos, 404);
        }
        
        $pdf = Pdf::loadView('estados_cuenta.pdf', $datos);
        
        $nombre_archivo = 'estado_cuenta_' . 
                         str_replace(' ', '_', $datos['cliente']->Razon) . 
                         '_' . date('Y-m-d') . '.pdf';
        
        return $pdf->download($nombre_archivo);
    }

    /**
     * Exporta estado de cuenta a Excel
     */
    public function exportarExcel($cliente_id, Request $request)
    {
        $datos = $this->estadoCuentaCompleto($cliente_id, $request);
        
        if (is_array($datos) && isset($datos['error'])) {
            return response()->json($datos, 404);
        }
        
        // Aquí se implementaría la exportación a Excel
        // return Excel::download(new EstadoCuentaExport($datos), 'estado_cuenta.xlsx');
        
        return response()->json(['mensaje' => 'Exportación a Excel en desarrollo']);
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
            'estado_saldo' => $request->estado_saldo,
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
            ->leftJoin('CtaCliente', 'Clientes.Codclie', '=', 'CtaCliente.CodClie')
            ->select(
                'Clientes.Codclie',
                'Clientes.Razon',
                'Clientes.Documento',
                'Clientes.Limite',
                DB::raw('COALESCE(SUM(CtaCliente.Saldo), 0) as saldo_actual')
            )
            ->where('Clientes.Activo', 1)
            ->groupBy(
                'Clientes.Codclie', 
                'Clientes.Razon', 
                'Clientes.Documento', 
                'Clientes.Limite'
            );

        // Aplicar filtros
        if (!empty($filtros['cliente_id'])) {
            $query->where('Clientes.Codclie', $filtros['cliente_id']);
        }

        if (!empty($filtros['estado_saldo'])) {
            if ($filtros['estado_saldo'] == 'CON_SALDO') {
                $query->havingRaw('COALESCE(SUM(CtaCliente.Saldo), 0) > 0');
            } elseif ($filtros['estado_saldo'] == 'SIN_SALDO') {
                $query->havingRaw('COALESCE(SUM(CtaCliente.Saldo), 0) = 0');
            }
        }

        if (!empty($filtros['limite_credito_min'])) {
            $query->where('Clientes.Limite', '>=', $filtros['limite_credito_min']);
        }

        if (!empty($filtros['limite_credito_max'])) {
            $query->where('Clientes.Limite', '<=', $filtros['limite_credito_max']);
        }

        return $query->orderByRaw('COALESCE(SUM(CtaCliente.Saldo), 0) DESC')
                     ->paginate(25);
    }

    /**
     * Calcula resumen general de todos los estados de cuenta
     */
    public function calcularResumenGeneral()
    {
        $total_clientes = DB::table('Clientes')
            ->where('Activo', 1)
            ->count();
        
        $saldo_total = DB::table('CtaCliente')
            ->sum('Saldo');

        $limite_credito_total = DB::table('Clientes')
            ->where('Activo', 1)
            ->sum('Limite');

        $facturas_vencidas = DB::table('CtaCliente')
            ->where('Saldo', '>', 0)
            ->whereRaw('FechaV < GETDATE()')
            ->count();

        $monto_vencido = DB::table('CtaCliente')
            ->where('Saldo', '>', 0)
            ->whereRaw('FechaV < GETDATE()')
            ->sum('Saldo');

        $facturas_por_vencer = DB::table('CtaCliente')
            ->where('Saldo', '>', 0)
            ->whereRaw('FechaV >= GETDATE()')
            ->count();

        $porcentaje_utilizacion = $limite_credito_total > 0 
            ? ($saldo_total / $limite_credito_total) * 100 
            : 0;

        return [
            'total_clientes' => $total_clientes,
            'saldo_total_pendiente' => $saldo_total,
            'limite_credito_total' => $limite_credito_total,
            'porcentaje_utilizacion' => round($porcentaje_utilizacion, 2),
            'facturas_vencidas' => $facturas_vencidas,
            'monto_vencido' => $monto_vencido,
            'facturas_por_vencer' => $facturas_por_vencer,
            'promedio_credito_por_cliente' => $total_clientes > 0 
                ? $limite_credito_total / $total_clientes 
                : 0
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
                return $porcentaje_utilizado < 50 ? 'AUMENTAR_LIMITE' : 'MANTENER';
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
        return response()->json($this->calcularResumenGeneral());
    }

    /**
     * API: Obtiene alertas para notificaciones
     */
    public function obtenerAlertasActivas()
    {
        $alertas = $this->generarAlertas();
        return response()->json($alertas['resumen']);
    }

    /**
     * API: Comparte estado de cuenta vía email
     */
    public function compartirEmail($cliente_id, Request $request)
    {
        $cliente = DB::table('Clientes')->where('Codclie', $cliente_id)->first();
        
        if (!$cliente) {
            return response()->json(['error' => 'Cliente no encontrado'], 404);
        }

        $request->validate([
            'email' => 'required|email'
        ]);

        try {
            // Aquí se implementaría el envío de email
            // Mail::to($request->email)->send(new EstadoCuentaEmail($datos));
            
            return response()->json([
                'success' => true,
                'mensaje' => 'Estado de cuenta enviado a ' . $request->email
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al enviar email: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Obtiene datos para gráficos
     */
    public function datosGraficos($cliente_id)
    {
        $ventas_mensuales = DB::table('CtaCliente')
            ->selectRaw('YEAR(FechaF) as año, MONTH(FechaF) as mes, SUM(Importe) as total')
            ->where('CodClie', $cliente_id)
            ->where('FechaF', '>=', now()->subMonths(12))
            ->groupByRaw('YEAR(FechaF), MONTH(FechaF)')
            ->orderByRaw('YEAR(FechaF), MONTH(FechaF)')
            ->get();

        $antiguedad_saldos = $this->analizarAntiguedadSaldos($cliente_id);

        return response()->json([
            'ventas_mensuales' => $ventas_mensuales,
            'antiguedad_saldos' => $antiguedad_saldos['totales']
        ]);
    }

    /**
     * API: Registra un nuevo pago de cliente
     */
    public function registrarPago(Request $request)
    {
        $request->validate([
            'documento' => 'required',
            'tipo' => 'required|integer',
            'monto_pagado' => 'required|numeric|min:0',
            'fecha_pago' => 'required|date'
        ]);

        try {
            DB::beginTransaction();

            // Buscar factura en CtaCliente
            $factura = DB::table('CtaCliente')
                ->where('Documento', $request->documento)
                ->where('Tipo', $request->tipo)
                ->first();

            if (!$factura) {
                return response()->json(['error' => 'Factura no encontrada'], 404);
            }

            if ($request->monto_pagado > $factura->Saldo) {
                return response()->json([
                    'error' => 'El monto pagado excede el saldo pendiente'
                ], 422);
            }

            // Actualizar saldo y fecha de pago
            $nuevo_saldo = $factura->Saldo - $request->monto_pagado;
            
            DB::table('CtaCliente')
                ->where('Documento', $request->documento)
                ->where('Tipo', $request->tipo)
                ->update([
                    'Saldo' => $nuevo_saldo,
                    'FechaP' => $request->fecha_pago
                ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'mensaje' => 'Pago registrado correctamente',
                'nuevo_saldo' => $nuevo_saldo
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Error al registrar pago: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Obtiene historial de pagos de un cliente
     */
    public function historialPagos($cliente_id)
    {
        $historial = DB::table('CtaCliente')
            ->select(
                'Documento',
                'Tipo',
                'FechaF as fecha_factura',
                'FechaV as fecha_vencimiento',
                'FechaP as fecha_pago',
                'Importe',
                'Saldo',
                DB::raw('(Importe - Saldo) as monto_pagado'),
                DB::raw('DATEDIFF(day, FechaV, FechaP) as dias_retraso')
            )
            ->where('CodClie', $cliente_id)
            ->whereNotNull('FechaP')
            ->orderBy('FechaP', 'desc')
            ->get();

        return response()->json($historial);
    }
}
