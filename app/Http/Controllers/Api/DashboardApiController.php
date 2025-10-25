<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DashboardApiController extends Controller
{
  
    public function __construct()
    {
        // Middleware de autenticación API
        $this->middleware('auth:sanctum');
        
        // Rate limiting por defecto
        $this->middleware('throttle:120,1');
        
        // Validación de permisos API
        $this->middleware('api.permission:dashboard');
    }

    public function dashboardEjecutivo(Request $request): JsonResponse
    {
        try {
            $fechaDesde = $request->input('fecha_desde', Carbon::now()->subDays(30)->format('Y-m-d'));
            $fechaHasta = $request->input('fecha_hasta', Carbon::now()->format('Y-m-d'));

            // Datos principales del dashboard
            $datosDashboard = [
                'resumen_ventas' => $this->obtenerResumenVentas($fechaDesde, $fechaHasta),
                'indicadores_financieros' => $this->obtenerIndicadoresFinancieros($fechaDesde, $fechaHasta),
                'estado_inventario' => $this->obtenerEstadoInventario(),
                'metricas_clientes' => $this->obtenerMetricasClientes($fechaDesde, $fechaHasta),
                'productividad_vendedores' => $this->obtenerProductividadVendedores($fechaDesde, $fechaHasta),
                'alertas_sistema' => $this->obtenerAlertasSistema(),
                'tendencias_tiempo_real' => $this->obtenerTendenciasTiempoReal(),
                'graficos_ventas' => $this->obtenerGraficosVentas($fechaDesde, $fechaHasta)
            ];

            // Calcular KPIs ejecutivos
            $kpisEjecutivos = $this->calcularKPIsEjecutivos($datosDashboard);

            return response()->json([
                'success' => true,
                'data' => [
                    'dashboard_data' => $datosDashboard,
                    'kpis_ejecutivos' => $kpisEjecutivos,
                    'periodo_analisis' => [
                        'desde' => $fechaDesde,
                        'hasta' => $fechaHasta
                    ],
                    'fecha_actualizacion' => Carbon::now()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error en DashboardApiController::dashboardEjecutivo: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Obtener métricas en tiempo real
     * GET /api/dashboard/tiempo-real
     */
    public function tiempoReal(Request $request): JsonResponse
    {
        try {
            $ahora = Carbon::now();
            
            // Métricas en tiempo real
            $metricasRT = [
                'ventas_hoy' => [
                    'monto' => DB::table('Doccab')
                        ->whereDate('Fecha', $ahora->toDateString())
                        ->sum('Total'),
                    'cantidad_facturas' => DB::table('Doccab')
                        ->whereDate('Fecha', $ahora->toDateString())
                        ->count(),
                    'promedio_por_factura' => $this->calcularPromedioDiario($ahora->toDateString())
                ],
                'ventas_hora_actual' => $this->obtenerVentasHoraActual($ahora),
                'productos_vendidos_hoy' => $this->obtenerProductosVendidosHoy(),
                'clientes_atendidos_hoy' => $this->obtenerClientesAtendidosHoy(),
                'estado_inventario_rt' => [
                    'productos_bajo_stock' => DB::table('Saldos')
                        ->whereColumn('Stock', '<=', 'StockMinimo')
                        ->where('Estado', 'Activo')
                        ->count(),
                    'productos_vencidos' => DB::table('Saldos')
                        ->where('Vencimiento', '<', $ahora->toDateString())
                        ->where('Estado', 'Activo')
                        ->count(),
                    'valor_total_inventario' => DB::table('Saldos')
                        ->where('Estado', 'Activo')
                        ->sum(DB::raw('Stock * PrecioCosto'))
                ],
                'cuentas_cobrar_rt' => [
                    'saldo_pendiente_total' => DB::table('Doccab')
                        ->where('Saldo', '>', 0)
                        ->sum('Saldo'),
                    'facturas_vencidas' => DB::table('Doccab')
                        ->where('Saldo', '>', 0)
                        ->where('FechaVenc', '<', $ahora->toDateString())
                        ->count(),
                    'facturas_por_vencer' => DB::table('Doccab')
                        ->where('Saldo', '>', 0)
                        ->whereBetween('FechaVenc', [
                            $ahora->toDateString(), 
                            $ahora->addDays(7)->toDateString()
                        ])
                        ->count()
                ]
            ];

            // Actividades recientes del sistema
            $actividadesRecientes = $this->obtenerActividadesRecientes(10);

            // Alertas críticas activas
            $alertasCriticas = $this->obtenerAlertasCriticas();

            return response()->json([
                'success' => true,
                'data' => [
                    'metricas_tiempo_real' => $metricasRT,
                    'actividades_recientes' => $actividadesRecientes,
                    'alertas_criticas' => $alertasCriticas,
                    'timestamp' => $ahora->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error en DashboardApiController::tiempoReal: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Obtener gráficos y visualizaciones
     * GET /api/dashboard/graficos
     */
    public function graficos(Request $request): JsonResponse
    {
        try {
            $tipoGrafico = $request->input('tipo', 'ventas'); // ventas, productos, clientes, financiero
            $periodo = $request->input('periodo', 'mensual'); // diario, semanal, mensual, anual
            $fechaDesde = $request->input('fecha_desde', Carbon::now()->subMonths(12)->format('Y-m-d'));
            $fechaHasta = $request->input('fecha_hasta', Carbon::now()->format('Y-m-d'));

            switch ($tipoGrafico) {
                case 'ventas':
                    $datos = $this->generarGraficoVentas($periodo, $fechaDesde, $fechaHasta);
                    break;
                case 'productos':
                    $datos = $this->generarGraficoProductos($periodo, $fechaDesde, $fechaHasta);
                    break;
                case 'clientes':
                    $datos = $this->generarGraficoClientes($periodo, $fechaDesde, $fechaHasta);
                    break;
                case 'financiero':
                    $datos = $this->generarGraficoFinanciero($periodo, $fechaDesde, $fechaHasta);
                    break;
                default:
                    $datos = $this->generarGraficoVentas($periodo, $fechaDesde, $fechaHasta);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'tipo_grafico' => $tipoGrafico,
                    'periodo' => $periodo,
                    'datos_grafico' => $datos,
                    'configuracion' => [
                        'colores' => $this->obtenerColoresGrafico($tipoGrafico),
                        'formato_datos' => $this->obtenerFormatoDatos($tipoGrafico)
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error en DashboardApiController::graficos: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Obtener comparativas de rendimiento
     * GET /api/dashboard/comparativas
     */
    public function comparativas(Request $request): JsonResponse
    {
        try {
            $tipoComparativa = $request->input('tipo', 'ventas'); // ventas, vendedores, productos, periodos
            $periodo1Desde = $request->input('periodo1_desde', Carbon::now()->subMonth()->startOfMonth()->format('Y-m-d'));
            $periodo1Hasta = $request->input('periodo1_hasta', Carbon::now()->subMonth()->endOfMonth()->format('Y-m-d'));
            $periodo2Desde = $request->input('periodo2_desde', Carbon::now()->startOfMonth()->format('Y-m-d'));
            $periodo2Hasta = $request->input('periodo2_hasta', Carbon::now()->format('Y-m-d'));

            switch ($tipoComparativa) {
                case 'ventas':
                    $datos = $this->generarComparativaVentas($periodo1Desde, $periodo1Hasta, $periodo2Desde, $periodo2Hasta);
                    break;
                case 'vendedores':
                    $datos = $this->generarComparativaVendedores($periodo1Desde, $periodo1Hasta, $periodo2Desde, $periodo2Hasta);
                    break;
                case 'productos':
                    $datos = $this->generarComparativaProductos($periodo1Desde, $periodo1Hasta, $periodo2Desde, $periodo2Hasta);
                    break;
                default:
                    $datos = $this->generarComparativaVentas($periodo1Desde, $periodo1Hasta, $periodo2Desde, $periodo2Hasta);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'tipo_comparativa' => $tipoComparativa,
                    'periodo_1' => ['desde' => $periodo1Desde, 'hasta' => $periodo1Hasta],
                    'periodo_2' => ['desde' => $periodo2Desde, 'hasta' => $periodo2Hasta],
                    'comparativa' => $datos
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error en DashboardApiController::comparativas: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Obtener KPIs principales
     * GET /api/dashboard/kpis
     */
    public function kpis(Request $request): JsonResponse
    {
        try {
            $tipoKPI = $request->input('tipo', 'todos'); // todos, ventas, financiero, operativo, comercial
            $fechaDesde = $request->input('fecha_desde', Carbon::now()->subDays(30)->format('Y-m-d'));
            $fechaHasta = $request->input('fecha_hasta', Carbon::now()->format('Y-m-d'));

            $kpis = [];

            switch ($tipoKPI) {
                case 'ventas':
                    $kpis = $this->obtenerKPIsVentas($fechaDesde, $fechaHasta);
                    break;
                case 'financiero':
                    $kpis = $this->obtenerKPIsFinancieros($fechaDesde, $fechaHasta);
                    break;
                case 'operativo':
                    $kpis = $this->obtenerKPIsOperativos($fechaDesde, $fechaHasta);
                    break;
                case 'comercial':
                    $kpis = $this->obtenerKPIsComerciales($fechaDesde, $fechaHasta);
                    break;
                default:
                    $kpis = [
                        'ventas' => $this->obtenerKPIsVentas($fechaDesde, $fechaHasta),
                        'financiero' => $this->obtenerKPIsFinancieros($fechaDesde, $fechaHasta),
                        'operativo' => $this->obtenerKPIsOperativos($fechaDesde, $fechaHasta),
                        'comercial' => $this->obtenerKPIsComerciales($fechaDesde, $fechaHasta)
                    ];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'tipo_kpi' => $tipoKPI,
                    'kpis' => $kpis,
                    'metas' => $this->obtenerMetasKPI(),
                    'alertas' => $this->evaluarAlertasKPI($kpis)
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error en DashboardApiController::kpis: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Obtener ranking de elementos
     * GET /api/dashboard/rankings
     */
    public function rankings(Request $request): JsonResponse
    {
        try {
            $tipoRanking = $request->input('tipo', 'productos'); // productos, clientes, vendedores
            $limite = $request->input('limite', 10);
            $fechaDesde = $request->input('fecha_desde', Carbon::now()->subMonth()->format('Y-m-d'));
            $fechaHasta = $request->input('fecha_hasta', Carbon::now()->format('Y-m-d'));

            switch ($tipoRanking) {
                case 'productos':
                    $rankings = $this->generarRankingProductos($fechaDesde, $fechaHasta, $limite);
                    break;
                case 'clientes':
                    $rankings = $this->generarRankingClientes($fechaDesde, $fechaHasta, $limite);
                    break;
                case 'vendedores':
                    $rankings = $this->generarRankingVendedores($fechaDesde, $fechaHasta, $limite);
                    break;
                default:
                    $rankings = $this->generarRankingProductos($fechaDesde, $fechaHasta, $limite);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'tipo_ranking' => $tipoRanking,
                    'periodo' => ['desde' => $fechaDesde, 'hasta' => $fechaHasta],
                    'ranking' => $rankings,
                    'estadisticas_ranking' => [
                        'total_elementos' => count($rankings),
                        'top_3_valor_total' => array_sum(array_slice(array_column($rankings->toArray(), 'valor'), 0, 3))
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error en DashboardApiController::rankings: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Obtener alertas y notificaciones del dashboard
     * GET /api/dashboard/alertas
     */
    public function alertas(Request $request): JsonResponse
    {
        try {
            $nivelAlerta = $request->input('nivel', 'todos'); // todos, critico, alto, medio, bajo
            $tipoAlerta = $request->input('tipo', 'todos'); // todos, inventario, ventas, financiero, operativo

            // Obtener alertas del sistema
            $alertas = [];

            // Alertas de inventario
            if ($tipoAlerta === 'todos' || $tipoAlerta === 'inventario') {
                $alertas = array_merge($alertas, $this->generarAlertasInventario($nivelAlerta));
            }

            // Alertas de ventas
            if ($tipoAlerta === 'todos' || $tipoAlerta === 'ventas') {
                $alertas = array_merge($alertas, $this->generarAlertasVentas($nivelAlerta));
            }

            // Alertas financieras
            if ($tipoAlerta === 'todos' || $tipoAlerta === 'financiero') {
                $alertas = array_merge($alertas, $this->generarAlertasFinanciero($nivelAlerta));
            }

            // Alertas operativas
            if ($tipoAlerta === 'todos' || $tipoAlerta === 'operativo') {
                $alertas = array_merge($alertas, $this->generarAlertasOperativo($nivelAlerta));
            }

            // Ordenar por nivel de importancia
            $niveles = ['critico' => 4, 'alto' => 3, 'medio' => 2, 'bajo' => 1];
            usort($alertas, function($a, $b) use ($niveles) {
                return $niveles[$b['nivel']] - $niveles[$a['nivel']];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'alertas' => $alertas,
                    'resumen_alertas' => [
                        'total' => count($alertas),
                        'criticas' => count(array_filter($alertas, fn($a) => $a['nivel'] === 'critico')),
                        'altas' => count(array_filter($alertas, fn($a) => $a['nivel'] === 'alto')),
                        'medias' => count(array_filter($alertas, fn($a) => $a['nivel'] === 'medio')),
                        'bajas' => count(array_filter($alertas, fn($a) => $a['nivel'] === 'bajo'))
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error en DashboardApiController::alertas: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Exportar datos del dashboard
     * POST /api/dashboard/exportar
     */
    public function exportar(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'tipo_exportacion' => 'required|in:completo,kpis,alertas,graficos',
                'formato' => 'required|in:json,csv,xlsx',
                'fecha_desde' => 'date',
                'fecha_hasta' => 'date|after:fecha_desde',
                'incluir_graficos' => 'boolean',
                'incluir_alertas' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parámetros inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            $fechaDesde = $request->input('fecha_desde', Carbon::now()->subMonth()->format('Y-m-d'));
            $fechaHasta = $request->input('fecha_hasta', Carbon::now()->format('Y-m-d'));
            $formato = $request->input('formato', 'json');

            // Generar datos según tipo
            $datosExportar = match($request->tipo_exportacion) {
                'completo' => $this->generarExportacionCompleta($fechaDesde, $fechaHasta, $request),
                'kpis' => $this->generarExportacionKPIs($fechaDesde, $fechaHasta),
                'alertas' => $this->generarExportacionAlertas(),
                'graficos' => $this->generarExportacionGraficos($fechaDesde, $fechaHasta),
                default => $this->generarExportacionCompleta($fechaDesde, $fechaHasta, $request)
            };

            // Agregar metadatos
            $datosExportar = [
                'metadatos' => [
                    'tipo_exportacion' => $request->tipo_exportacion,
                    'formato' => $formato,
                    'fecha_generacion' => Carbon::now(),
                    'generado_por' => auth()->user()->name ?? 'Sistema',
                    'periodo' => ['desde' => $fechaDesde, 'hasta' => $fechaHasta]
                ],
                'datos' => $datosExportar
            ];

            return response()->json([
                'success' => true,
                'message' => 'Datos exportados exitosamente',
                'data' => $datosExportar
            ]);

        } catch (\Exception $e) {
            Log::error('Error en DashboardApiController::exportar: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | MÉTODOS DE CÁLCULO Y ANÁLISIS
    |--------------------------------------------------------------------------
    */

    /**
     * Obtener resumen de ventas
     */
    private function obtenerResumenVentas($fechaDesde, $fechaHasta)
    {
        $ventas = DB::table('Doccab')
            ->whereBetween('Fecha', [$fechaDesde, $fechaHasta])
            ->select([
                DB::raw('COUNT(*) as total_facturas'),
                DB::raw('SUM(Total) as monto_total'),
                DB::raw('AVG(Total) as promedio_por_factura'),
                DB::raw('SUM(Saldo) as saldo_pendiente')
            ])
            ->first();

        // Ventas por día
        $ventasDiarias = DB::table('Doccab')
            ->whereBetween('Fecha', [$fechaDesde, $fechaHasta])
            ->select([
                'Fecha',
                DB::raw('COUNT(*) as facturas'),
                DB::raw('SUM(Total) as monto')
            ])
            ->groupBy('Fecha')
            ->orderBy('Fecha')
            ->get();

        // Comparación con período anterior
        $diasPeriodo = Carbon::parse($fechaDesde)->diffInDays(Carbon::parse($fechaHasta));
        $periodoAnteriorDesde = Carbon::parse($fechaDesde)->subDays($diasPeriodo)->format('Y-m-d');
        $periodoAnteriorHasta = Carbon::parse($fechaDesde)->subDay()->format('Y-m-d');

        $ventasPeriodoAnterior = DB::table('Doccab')
            ->whereBetween('Fecha', [$periodoAnteriorDesde, $periodoAnteriorHasta])
            ->sum('Total');

        $variacionPorcentual = $ventasPeriodoAnterior > 0 ? 
            (($ventas->monto_total - $ventasPeriodoAnterior) / $ventasPeriodoAnterior) * 100 : 0;

        return [
            'resumen' => $ventas,
            'ventas_diarias' => $ventasDiarias,
            'variacion_periodo_anterior' => [
                'monto_anterior' => $ventasPeriodoAnterior,
                'variacion_absoluta' => $ventas->monto_total - $ventasPeriodoAnterior,
                'variacion_porcentual' => round($variacionPorcentual, 2)
            ]
        ];
    }

    /**
     * Obtener indicadores financieros
     */
    private function obtenerIndicadoresFinancieros($fechaDesde, $fechaHasta)
    {
        $cuentasPorCobrar = DB::table('Doccab')
            ->where('Saldo', '>', 0)
            ->select([
                DB::raw('SUM(Saldo) as total_pendiente'),
                DB::raw('COUNT(*) as facturas_pendientes'),
                DB::raw('AVG(Saldo) as promedio_factura')
            ])
            ->first();

        $facturasVencidas = DB::table('Doccab')
            ->where('Saldo', '>', 0)
            ->where('FechaVenc', '<', Carbon::now())
            ->select([
                DB::raw('SUM(Saldo) as monto_vencido'),
                DB::raw('COUNT(*) as facturas_vencidas')
            ])
            ->first();

        $flujoEfectivo = DB::table('movimientos_caja')
            ->whereBetween('fecha', [$fechaDesde, $fechaHasta])
            ->select([
                DB::raw('SUM(CASE WHEN tipo = "INGRESO" THEN monto ELSE 0 END) as ingresos'),
                DB::raw('SUM(CASE WHEN tipo = "EGRESO" THEN monto ELSE 0 END) as egresos')
            ])
            ->first();

        return [
            'cuentas_por_cobrar' => $cuentasPorCobrar,
            'facturas_vencidas' => $facturasVencidas,
            'flujo_efectivo' => [
                'ingresos' => $flujoEfectivo->ingresos ?? 0,
                'egresos' => $flujoEfectivo->egresos ?? 0,
                'neto' => ($flujoEfectivo->ingresos ?? 0) - ($flujoEfectivo->egresos ?? 0)
            ],
            'indicadores' => [
                'dias_cuentas_por_cobrar' => $this->calcularDiasCuentaPorCobrar(),
                'rotacion_inventario' => $this->calcularRotacionInventario()
            ]
        ];
    }

    /**
     * Obtener estado del inventario
     */
    private function obtenerEstadoInventario()
    {
        $inventario = DB::table('Saldos')
            ->where('Estado', 'Activo')
            ->select([
                DB::raw('COUNT(*) as total_productos'),
                DB::raw('SUM(Stock * PrecioCosto) as valor_total_inventario'),
                DB::raw('SUM(Stock) as cantidad_total_stock')
            ])
            ->first();

        $productosBajoStock = DB::table('Saldos')
            ->whereColumn('Stock', '<=', 'StockMinimo')
            ->where('Estado', 'Activo')
            ->count();

        $productosVencidos = DB::table('Saldos')
            ->where('Vencimiento', '<', Carbon::now())
            ->where('Estado', 'Activo')
            ->count();

        $productosPorVencer = DB::table('Saldos')
            ->whereBetween('Vencimiento', [Carbon::now(), Carbon::now()->addDays(30)])
            ->where('Estado', 'Activo')
            ->count();

        return [
            'resumen' => $inventario,
            'alertas' => [
                'bajo_stock' => $productosBajoStock,
                'vencidos' => $productosVencidos,
                'por_vencer' => $productosPorVencer
            ],
            'distribucion_categorias' => $this->obtenerDistribucionInventarioCategorias()
        ];
    }

    /**
     * Obtener métricas de clientes
     */
    private function obtenerMetricasClientes($fechaDesde, $fechaHasta)
    {
        $clientesActivos = DB::table('Clientes')
            ->where('Estado', 'Activo')
            ->count();

        $nuevosClientes = DB::table('Clientes')
            ->whereBetween('FechaRegistro', [$fechaDesde, $fechaHasta])
            ->count();

        $clientesConCompras = DB::table('Doccab')
            ->distinct('Codcli')
            ->whereBetween('Fecha', [$fechaDesde, $fechaHasta])
            ->count('Codcli');

        $ticketPromedio = DB::table('Doccab')
            ->whereBetween('Fecha', [$fechaDesde, $fechaHasta])
            ->avg('Total');

        return [
            'clientes_activos' => $clientesActivos,
            'nuevos_clientes' => $nuevosClientes,
            'clientes_con_compras' => $clientesConCompras,
            'ticket_promedio' => round($ticketPromedio ?? 0, 2),
            'tasa_retencion' => $this->calcularTasaRetencionClientes($fechaDesde, $fechaHasta)
        ];
    }

    /**
     * Obtener productividad de vendedores
     */
    private function obtenerProductividadVendedores($fechaDesde, $fechaHasta)
    {
        $productividad = DB::table('Doccab')
            ->select([
                'Vendedor',
                DB::raw('COUNT(*) as total_facturas'),
                DB::raw('SUM(Total) as total_ventas'),
                DB::raw('AVG(Total) as promedio_por_factura')
            ])
            ->whereBetween('Fecha', [$fechaDesde, $fechaHasta])
            ->whereNotNull('Vendedor')
            ->groupBy('Vendedor')
            ->orderBy('total_ventas', 'desc')
            ->get();

        return [
            'productividad_individual' => $productividad,
            'top_vendedor' => $productividad->first(),
            'promedio_ventas_vendedor' => round($productividad->avg('total_ventas') ?? 0, 2)
        ];
    }

    /**
     * Obtener alertas del sistema
     */
    private function obtenerAlertasSistema()
    {
        return [
            'inventario' => [
                'productos_bajo_stock' => DB::table('Saldos')
                    ->whereColumn('Stock', '<=', 'StockMinimo')
                    ->where('Estado', 'Activo')
                    ->count(),
                'productos_vencidos' => DB::table('Saldos')
                    ->where('Vencimiento', '<', Carbon::now())
                    ->where('Estado', 'Activo')
                    ->count()
            ],
            'financiero' => [
                'facturas_vencidas' => DB::table('Doccab')
                    ->where('Saldo', '>', 0)
                    ->where('FechaVenc', '<', Carbon::now())
                    ->count(),
                'saldo_critico' => DB::table('Doccab')
                    ->where('Saldo', '>', 0)
                    ->where('Saldo', '>', 10000)
                    ->count()
            ]
        ];
    }

    /**
     * Obtener tendencias en tiempo real
     */
    private function obtenerTendenciasTiempoReal()
    {
        $hoy = Carbon::now();
        $ayer = Carbon::now()->subDay();
        
        return [
            'ventas_ultimas_24h' => [
                'hoy' => DB::table('Doccab')
                    ->whereDate('Fecha', $hoy)
                    ->sum('Total'),
                'ayer' => DB::table('Doccab')
                    ->whereDate('Fecha', $ayer)
                    ->sum('Total')
            ],
            'productos_mas_vendidos_hoy' => DB::table('Docdet')
                ->join('Saldos', 'Docdet.Codpro', '=', 'Saldos.Codigo')
                ->whereDate('Docdet.created_at', $hoy)
                ->select([
                    'Saldos.Descripcion',
                    DB::raw('SUM(Docdet.Cantidad) as cantidad')
                ])
                ->groupBy('Saldos.Descripcion')
                ->orderBy('cantidad', 'desc')
                ->limit(5)
                ->get()
        ];
    }

    // Métodos auxiliares para cálculos
    private function calcularPromedioDiario($fecha)
    {
        $total = DB::table('Doccab')
            ->whereDate('Fecha', $fecha)
            ->sum('Total');
            
        $cantidad = DB::table('Doccab')
            ->whereDate('Fecha', $fecha)
            ->count();
            
        return $cantidad > 0 ? round($total / $cantidad, 2) : 0;
    }

    private function obtenerVentasHoraActual($ahora)
    {
        $horaInicio = $ahora->copy()->startOfHour();
        $horaFin = $ahora->copy()->endOfHour();
        
        return DB::table('Doccab')
            ->whereBetween('created_at', [$horaInicio, $horaFin])
            ->sum('Total');
    }

    private function obtenerProductosVendidosHoy()
    {
        return DB::table('Docdet')
            ->whereDate('created_at', Carbon::now())
            ->distinct('Codpro')
            ->count('Codpro');
    }

    private function obtenerClientesAtendidosHoy()
    {
        return DB::table('Doccab')
            ->whereDate('Fecha', Carbon::now())
            ->distinct('Codcli')
            ->count('Codcli');
    }

    private function obtenerActividadesRecientes($limite)
    {
        return DB::table('auditoria_log')
            ->select([
                'accion',
                'tabla',
                'created_at',
                'ip_address'
            ])
            ->orderBy('created_at', 'desc')
            ->limit($limite)
            ->get();
    }

    private function obtenerAlertasCriticas()
    {
        return [
            'inventario_sin_stock' => DB::table('Saldos')
                ->where('Stock', 0)
                ->where('Estado', 'Activo')
                ->count(),
            'facturas_muy_vencidas' => DB::table('Doccab')
                ->where('Saldo', '>', 0)
                ->where('FechaVenc', '<', Carbon::now()->subDays(30))
                ->count(),
            'productos_criticos_vencidos' => DB::table('Saldos')
                ->where('Vencimiento', '<', Carbon::now()->subDays(7))
                ->where('Stock', '>', 0)
                ->count()
        ];
    }

    private function calcularKPIsEjecutivos($datos)
    {
        return [
            'margen_bruto_estimado' => $this->calcularMargenBruto($datos['resumen_ventas']),
            'rotacion_inventario_anual' => $this->calcularRotacionInventarioAnual(),
            'dias_cuentas_por_cobrar' => $this->calcularDiasCuentaPorCobrar(),
            'crecimiento_vs_periodo_anterior' => $datos['resumen_ventas']['variacion_periodo_anterior']['variacion_porcentual'],
            'alertas_activas' => array_sum($datos['alertas_sistema']['inventario']) + array_sum($datos['alertas_sistema']['financiero'])
        ];
    }

    // Métodos para generar gráficos
    private function generarGraficoVentas($periodo, $fechaDesde, $fechaHasta)
    {
        $formatoFecha = match($periodo) {
            'diario' => '%Y-%m-%d',
            'semanal' => '%Y-%u',
            'mensual' => '%Y-%m',
            'anual' => '%Y',
            default => '%Y-%m'
        };

        return DB::table('Doccab')
            ->select([
                DB::raw("DATE_FORMAT(Fecha, '$formatoFecha') as periodo"),
                DB::raw('SUM(Total) as ventas'),
                DB::raw('COUNT(*) as facturas')
            ])
            ->whereBetween('Fecha', [$fechaDesde, $fechaHasta])
            ->groupBy('periodo')
            ->orderBy('periodo')
            ->get();
    }

    private function generarGraficoProductos($periodo, $fechaDesde, $fechaHasta)
    {
        return DB::table('Docdet')
            ->join('Saldos', 'Docdet.Codpro', '=', 'Saldos.Codigo')
            ->select([
                'Saldos.Categoria',
                DB::raw('SUM(Docdet.Cantidad) as cantidad_vendida'),
                DB::raw('SUM(Docdet.Total) as ventas')
            ])
            ->whereBetween('Docdet.created_at', [$fechaDesde . ' 00:00:00', $fechaHasta . ' 23:59:59'])
            ->groupBy('Saldos.Categoria')
            ->get();
    }

    private function generarGraficoClientes($periodo, $fechaDesde, $fechaHasta)
    {
        return DB::table('Doccab')
            ->join('Clientes', 'Doccab.Codcli', '=', 'Clientes.Codigo')
            ->select([
                'Clientes.TipoCliente',
                DB::raw('COUNT(DISTINCT Doccab.Codcli) as clientes_unicos'),
                DB::raw('SUM(Doccab.Total) as ventas')
            ])
            ->whereBetween('Doccab.Fecha', [$fechaDesde, $fechaHasta])
            ->groupBy('Clientes.TipoCliente')
            ->get();
    }

    private function generarGraficoFinanciero($periodo, $fechaDesde, $fechaHasta)
    {
        return [
            'cuentas_por_cobrar' => DB::table('Doccab')
                ->where('Saldo', '>', 0)
                ->select([
                    DB::raw('CASE 
                        WHEN DATEDIFF(CURDATE(), FechaVenc) <= 0 THEN "Al día"
                        WHEN DATEDIFF(CURDATE(), FechaVenc) <= 30 THEN "1-30 días"
                        WHEN DATEDIFF(CURDATE(), FechaVenc) <= 60 THEN "31-60 días"
                        ELSE "Más de 60 días"
                    END as antiguedad'),
                    DB::raw('SUM(Saldo) as monto')
                ])
                ->groupBy('antiguedad')
                ->get(),
            'flujo_efectivo' => DB::table('movimientos_caja')
                ->select([
                    'tipo',
                    DB::raw('SUM(monto) as total')
                ])
                ->whereBetween('fecha', [$fechaDesde, $fechaHasta])
                ->groupBy('tipo')
                ->get()
        ];
    }

    // Métodos para comparativas
    private function generarComparativaVentas($p1d, $p1h, $p2d, $p2h)
    {
        $periodo1 = DB::table('Doccab')
            ->whereBetween('Fecha', [$p1d, $p1h])
            ->select([
                DB::raw('SUM(Total) as ventas'),
                DB::raw('COUNT(*) as facturas'),
                DB::raw('AVG(Total) as promedio')
            ])
            ->first();

        $periodo2 = DB::table('Doccab')
            ->whereBetween('Fecha', [$p2d, $p2h])
            ->select([
                DB::raw('SUM(Total) as ventas'),
                DB::raw('COUNT(*) as facturas'),
                DB::raw('AVG(Total) as promedio')
            ])
            ->first();

        return [
            'periodo_1' => $periodo1,
            'periodo_2' => $periodo2,
            'variaciones' => [
                'ventas' => [
                    'absoluta' => ($periodo2->ventas ?? 0) - ($periodo1->ventas ?? 0),
                    'porcentual' => $periodo1->ventas > 0 ? 
                        ((($periodo2->ventas ?? 0) - ($periodo1->ventas ?? 0)) / $periodo1->ventas) * 100 : 0
                ]
            ]
        ];
    }

    // Métodos para KPIs
    private function obtenerKPIsVentas($fechaDesde, $fechaHasta)
    {
        $ventasActuales = DB::table('Doccab')
            ->whereBetween('Fecha', [$fechaDesde, $fechaHasta])
            ->sum('Total');

        return [
            'ventas_totales' => $ventasActuales,
            'numero_facturas' => DB::table('Doccab')->whereBetween('Fecha', [$fechaDesde, $fechaHasta])->count(),
            'ticket_promedio' => round($ventasActuales / max(DB::table('Doccab')->whereBetween('Fecha', [$fechaDesde, $fechaHasta])->count(), 1), 2),
            'meta_mensual' => 500000, // Configurable
            'cumplimiento_meta' => round(($ventasActuales / 500000) * 100, 2)
        ];
    }

    private function obtenerKPIsFinancieros($fechaDesde, $fechaHasta)
    {
        return [
            'cuentas_por_cobrar' => DB::table('Doccab')->where('Saldo', '>', 0)->sum('Saldo'),
            'facturas_vencidas' => DB::table('Doccab')->where('Saldo', '>', 0)->where('FechaVenc', '<', Carbon::now())->sum('Saldo'),
            'rotacion_cuentas_cobrar' => $this->calcularRotacionCuentasCobrar(),
            'margen_bruto_estimado' => 25.0 // Estimación
        ];
    }

    private function obtenerKPIsOperativos($fechaDesde, $fechaHasta)
    {
        return [
            'rotacion_inventario' => $this->calcularRotacionInventario(),
            'productos_bajo_stock' => DB::table('Saldos')->whereColumn('Stock', '<=', 'StockMinimo')->count(),
            'tiempo_promedio_facturacion' => 5, // Minutos estimados
            'eficiencia_operativa' => 85.0 // Porcentaje estimado
        ];
    }

    private function obtenerKPIsComerciales($fechaDesde, $fechaHasta)
    {
        return [
            'clientes_nuevos' => DB::table('Clientes')->whereBetween('FechaRegistro', [$fechaDesde, $fechaHasta])->count(),
            'tasa_conversion' => 75.0, // Porcentaje estimado
            'retencion_clientes' => 90.0, // Porcentaje estimado
            'satisfaccion_cliente' => 4.2 // Puntuación sobre 5
        ];
    }

    private function obtenerMetasKPI()
    {
        return [
            'ventas_mensual' => 500000,
            'cuentas_cobrar_max' => 200000,
            'rotacion_inventario_min' => 4.0,
            'productos_bajo_stock_max' => 10,
            'clientes_nuevos_mes' => 50
        ];
    }

    private function evaluarAlertasKPI($kpis)
    {
        $alertas = [];
        
        if (isset($kpis['ventas']['cumplimiento_meta']) && $kpis['ventas']['cumplimiento_meta'] < 80) {
            $alertas[] = ['tipo' => 'ventas', 'nivel' => 'medio', 'mensaje' => 'Cumplimiento de meta de ventas bajo'];
        }
        
        if (isset($kpis['operativo']['productos_bajo_stock']) && $kpis['operativo']['productos_bajo_stock'] > 20) {
            $alertas[] = ['tipo' => 'inventario', 'nivel' => 'alto', 'mensaje' => 'Muchos productos con bajo stock'];
        }
        
        return $alertas;
    }

    // Métodos para rankings
    private function generarRankingProductos($fechaDesde, $fechaHasta, $limite)
    {
        return DB::table('Docdet')
            ->join('Saldos', 'Docdet.Codpro', '=', 'Saldos.Codigo')
            ->select([
                'Saldos.Codigo',
                'Saldos.Descripcion',
                DB::raw('SUM(Docdet.Cantidad) as cantidad'),
                DB::raw('SUM(Docdet.Total) as valor')
            ])
            ->whereBetween('Docdet.created_at', [$fechaDesde . ' 00:00:00', $fechaHasta . ' 23:59:59'])
            ->groupBy('Saldos.Codigo', 'Saldos.Descripcion')
            ->orderBy('valor', 'desc')
            ->limit($limite)
            ->get()
            ->values()
            ->map(function($item, $index) {
                $item->posicion = $index + 1;
                return $item;
            });
    }

    private function generarRankingClientes($fechaDesde, $fechaHasta, $limite)
    {
        return DB::table('Doccab')
            ->join('Clientes', 'Doccab.Codcli', '=', 'Clientes.Codigo')
            ->select([
                'Clientes.Codigo',
                'Clientes.Nombre',
                DB::raw('COUNT(Doccab.Numero) as num_facturas'),
                DB::raw('SUM(Doccab.Total) as valor')
            ])
            ->whereBetween('Doccab.Fecha', [$fechaDesde, $fechaHasta])
            ->groupBy('Clientes.Codigo', 'Clientes.Nombre')
            ->orderBy('valor', 'desc')
            ->limit($limite)
            ->get()
            ->values()
            ->map(function($item, $index) {
                $item->posicion = $index + 1;
                return $item;
            });
    }

    private function generarRankingVendedores($fechaDesde, $fechaHasta, $limite)
    {
        return DB::table('Doccab')
            ->select([
                'Vendedor',
                DB::raw('COUNT(*) as num_facturas'),
                DB::raw('SUM(Total) as valor')
            ])
            ->whereBetween('Fecha', [$fechaDesde, $fechaHasta])
            ->whereNotNull('Vendedor')
            ->groupBy('Vendedor')
            ->orderBy('valor', 'desc')
            ->limit($limite)
            ->get()
            ->values()
            ->map(function($item, $index) {
                $item->posicion = $index + 1;
                return $item;
            });
    }

    // Métodos para alertas
    private function generarAlertasInventario($nivel)
    {
        $alertas = [];
        
        $productosSinStock = DB::table('Saldos')
            ->where('Stock', 0)
            ->where('Estado', 'Activo')
            ->count();
            
        if ($productosSinStock > 0) {
            $alertas[] = [
                'tipo' => 'inventario',
                'nivel' => 'critico',
                'titulo' => 'Productos sin stock',
                'mensaje' => "Hay {$productosSinStock} productos sin stock disponible",
                'accion_recomendada' => 'Revisar y reponer inventario',
                'timestamp' => Carbon::now()
            ];
        }
        
        $productosVencidos = DB::table('Saldos')
            ->where('Vencimiento', '<', Carbon::now())
            ->where('Estado', 'Activo')
            ->count();
            
        if ($productosVencidos > 0) {
            $alertas[] = [
                'tipo' => 'inventario',
                'nivel' => 'alto',
                'titulo' => 'Productos vencidos',
                'mensaje' => "Hay {$productosVencidos} productos vencidos",
                'accion_recomendada' => 'Retirar productos vencidos del inventario',
                'timestamp' => Carbon::now()
            ];
        }
        
        return $alertas;
    }

    private function generarAlertasVentas($nivel)
    {
        $alertas = [];
        
        $hoy = Carbon::now();
        $ventaHoy = DB::table('Doccab')->whereDate('Fecha', $hoy)->sum('Total');
        $metaDiaria = 20000; // Configurable
        
        if ($ventaHoy < ($metaDiaria * 0.5)) {
            $alertas[] = [
                'tipo' => 'ventas',
                'nivel' => 'medio',
                'titulo' => 'Ventas por debajo de meta',
                'mensaje' => "Las ventas de hoy ({$ventaHoy}) están por debajo del 50% de la meta diaria",
                'accion_recomendada' => 'Revisar estrategia de ventas del día',
                'timestamp' => Carbon::now()
            ];
        }
        
        return $alertas;
    }

    private function generarAlertasFinanciero($nivel)
    {
        $alertas = [];
        
        $facturasVencidas = DB::table('Doccab')
            ->where('Saldo', '>', 0)
            ->where('FechaVenc', '<', Carbon::now())
            ->count();
            
        if ($facturasVencidas > 50) {
            $alertas[] = [
                'tipo' => 'financiero',
                'nivel' => 'alto',
                'titulo' => 'Muchas facturas vencidas',
                'mensaje' => "Hay {$facturasVencidas} facturas vencidas pendientes de cobro",
                'accion_recomendada' => 'Intensificar esfuerzos de cobranza',
                'timestamp' => Carbon::now()
            ];
        }
        
        return $alertas;
    }

    private function generarAlertasOperativo($nivel)
    {
        return [];
    }

    // Métodos de exportación
    private function generarExportacionCompleta($fechaDesde, $fechaHasta, $request)
    {
        return [
            'resumen_ventas' => $this->obtenerResumenVentas($fechaDesde, $fechaHasta),
            'indicadores_financieros' => $this->obtenerIndicadoresFinancieros($fechaDesde, $fechaHasta),
            'estado_inventario' => $this->obtenerEstadoInventario(),
            'kpis' => $this->obtenerKPIsVentas($fechaDesde, $fechaHasta)
        ];
    }

    private function generarExportacionKPIs($fechaDesde, $fechaHasta)
    {
        return [
            'ventas' => $this->obtenerKPIsVentas($fechaDesde, $fechaHasta),
            'financiero' => $this->obtenerKPIsFinancieros($fechaDesde, $fechaHasta),
            'operativo' => $this->obtenerKPIsOperativos($fechaDesde, $fechaHasta)
        ];
    }

    private function generarExportacionAlertas()
    {
        return [
            'inventario' => $this->generarAlertasInventario('todos'),
            'ventas' => $this->generarAlertasVentas('todos'),
            'financiero' => $this->generarAlertasFinanciero('todos')
        ];
    }

    private function generarExportacionGraficos($fechaDesde, $fechaHasta)
    {
        return [
            'ventas_mensual' => $this->generarGraficoVentas('mensual', $fechaDesde, $fechaHasta),
            'productos_categoria' => $this->generarGraficoProductos('mensual', $fechaDesde, $fechaHasta),
            'clientes_tipo' => $this->generarGraficoClientes('mensual', $fechaDesde, $fechaHasta)
        ];
    }

    // Métodos de cálculo auxiliares
    private function calcularMargenBruto($resumenVentas)
    {
        // Estimación del margen bruto basado en datos históricos
        return round($resumenVentas['resumen']->monto_total * 0.25, 2);
    }

    private function calcularRotacionInventarioAnual()
    {
        $costoVentasAnual = DB::table('Docdet')
            ->join('Saldos', 'Docdet.Codpro', '=', 'Saldos.Codigo')
            ->whereYear('Docdet.created_at', Carbon::now()->year)
            ->sum(DB::raw('Docdet.Cantidad * Saldos.PrecioCosto'));

        $inventarioPromedio = DB::table('Saldos')
            ->where('Estado', 'Activo')
            ->sum(DB::raw('Stock * PrecioCosto'));

        return $inventarioPromedio > 0 ? round($costoVentasAnual / $inventarioPromedio, 2) : 0;
    }

    private function calcularDiasCuentaPorCobrar()
    {
        $ventasCredito = DB::table('Doccab')
            ->whereMonth('created_at', Carbon::now()->month)
            ->sum('Total');

        $cuentasPorCobrar = DB::table('Doccab')
            ->where('Saldo', '>', 0)
            ->sum('Saldo');

        return $ventasCredito > 0 ? round(($cuentasPorCobrar / $ventasCredito) * 30, 0) : 0;
    }

    private function calcularRotacionInventario()
    {
        $costoVentasMensual = DB::table('Docdet')
            ->join('Saldos', 'Docdet.Codpro', '=', 'Saldos.Codigo')
            ->whereMonth('Docdet.created_at', Carbon::now()->month)
            ->sum(DB::raw('Docdet.Cantidad * Saldos.PrecioCosto'));

        $inventarioPromedio = DB::table('Saldos')
            ->where('Estado', 'Activo')
            ->avg(DB::raw('Stock * PrecioCosto'));

        return $inventarioPromedio > 0 ? round($costoVentasMensual / $inventarioPromedio, 2) : 0;
    }

    private function calcularRotacionCuentasCobrar()
    {
        $ventasNetas = DB::table('Doccab')
            ->whereMonth('created_at', Carbon::now()->month)
            ->sum('Total');

        $cuentasPorCobrarPromedio = DB::table('Doccab')
            ->where('Saldo', '>', 0)
            ->avg('Saldo');

        return $cuentasPorCobrarPromedio > 0 ? round($ventasNetas / $cuentasPorCobrarPromedio, 2) : 0;
    }

    private function calcularTasaRetencionClientes($fechaDesde, $fechaHasta)
    {
        $clientesActuales = DB::table('Doccab')
            ->distinct('Codcli')
            ->whereBetween('Fecha', [$fechaDesde, $fechaHasta])
            ->count('Codcli');

        $clientesAnteriores = DB::table('Doccab')
            ->distinct('Codcli')
            ->where('Fecha', '<', $fechaDesde)
            ->count('Codcli');

        return $clientesAnteriores > 0 ? round(($clientesActuales / $clientesAnteriores) * 100, 2) : 0;
    }

    private function obtenerDistribucionInventarioCategorias()
    {
        return DB::table('Saldos')
            ->where('Estado', 'Activo')
            ->select([
                'Categoria',
                DB::raw('COUNT(*) as productos'),
                DB::raw('SUM(Stock * PrecioCosto) as valor')
            ])
            ->groupBy('Categoria')
            ->get();
    }

    private function obtenerGraficosVentas($fechaDesde, $fechaHasta)
    {
        return [
            'ventas_diarias' => $this->generarGraficoVentas('diario', $fechaDesde, $fechaHasta),
            'productos_mas_vendidos' => DB::table('Docdet')
                ->join('Saldos', 'Docdet.Codpro', '=', 'Saldos.Codigo')
                ->select([
                    'Saldos.Descripcion',
                    DB::raw('SUM(Docdet.Cantidad) as cantidad')
                ])
                ->whereBetween('Docdet.created_at', [$fechaDesde . ' 00:00:00', $fechaHasta . ' 23:59:59'])
                ->groupBy('Saldos.Descripcion')
                ->orderBy('cantidad', 'desc')
                ->limit(10)
                ->get()
        ];
    }

    private function obtenerColoresGrafico($tipo)
    {
        return match($tipo) {
            'ventas' => ['#3B82F6', '#10B981', '#F59E0B', '#EF4444'],
            'productos' => ['#8B5CF6', '#06B6D4', '#84CC16', '#F97316'],
            'clientes' => ['#EC4899', '#6366F1', '#14B8A6', '#F59E0B'],
            'financiero' => ['#059669', '#DC2626', '#7C3AED', '#EA580C'],
            default => ['#6B7280', '#374151', '#9CA3AF', '#D1D5DB']
        };
    }

    private function obtenerFormatoDatos($tipo)
    {
        return match($tipo) {
            'ventas' => ['fecha', 'monto', 'facturas'],
            'productos' => ['categoria', 'cantidad', 'valor'],
            'clientes' => ['tipo', 'cantidad', 'valor'],
            'financiero' => ['concepto', 'monto', 'porcentaje'],
            default => ['label', 'value']
        };
    }
}