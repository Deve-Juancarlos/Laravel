    <?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ReportesApiController extends Controller
{
    
    public function __construct()
    {
        // Middleware de autenticación API
        $this->middleware('auth:sanctum');
        
        // Rate limiting por defecto
        $this->middleware('throttle:60,1');
        
        // Validación de permisos API
        $this->middleware('api.permission:reportes');
    }

 
    public function reportePersonalizado(Request $request): JsonResponse
    {
        try {
            // Validación de parámetros
            $validator = Validator::make($request->all(), [
                'tipo_reporte' => 'required|in:ventas,inventario,financiero,clientes,productos,completo',
                'formato_salida' => 'required|in:json,pdf,excel,csv',
                'periodo_desde' => 'required|date',
                'periodo_hasta' => 'required|date|after:periodo_desde',
                'filtros' => 'array',
                'filtros.categoria' => 'string|max:100',
                'filtros.cliente_codigo' => 'string|max:50',
                'filtros.vendedor' => 'string|max:100',
                'filtros.estado_documento' => 'in:Pagado,Parcial,Pendiente,Anulado',
                'incluir_graficos' => 'boolean',
                'incluir_resumen' => 'boolean',
                'incluir_detalle' => 'boolean',
                'agrupar_por' => 'in:fecha,categoria,cliente,vendedor,producto'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parámetros inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            // Generar reporte según tipo
            $datosReporte = match($request->tipo_reporte) {
                'ventas' => $this->generarReporteVentasPersonalizado($request),
                'inventario' => $this->generarReporteInventarioPersonalizado($request),
                'financiero' => $this->generarReporteFinancieroPersonalizado($request),
                'clientes' => $this->generarReporteClientesPersonalizado($request),
                'productos' => $this->generarReporteProductosPersonalizado($request),
                'completo' => $this->generarReporteCompletoPersonalizado($request),
                default => $this->generarReporteVentasPersonalizado($request)
            };

            // Aplicar agrupación si se especifica
            if ($request->filled('agrupar_por') && $request->incluir_detalle) {
                $datosReporte = $this->aplicarAgrupacion($datosReporte, $request->agrupar_por);
            }

            // Estructurar reporte final
            $reporteFinal = [
                'metadatos' => [
                    'tipo_reporte' => $request->tipo_reporte,
                    'formato_salida' => $request->formato_salida,
                    'fecha_generacion' => Carbon::now(),
                    'periodo' => [
                        'desde' => $request->periodo_desde,
                        'hasta' => $request->periodo_hasta
                    ],
                    'generado_por' => auth()->user()->nombre ?? 'Sistema',
                    'parametros_filtro' => $request->filtros ?? []
                ],
                'datos' => $datosReporte['datos'] ?? [],
                'resumen' => $datosReporte['resumen'] ?? [],
                'graficos' => $request->incluir_graficos ? ($datosReporte['graficos'] ?? []) : null
            ];

            return response()->json([
                'success' => true,
                'message' => 'Reporte generado exitosamente',
                'data' => $reporteFinal
            ]);

        } catch (\Exception $e) {
            Log::error('Error en ReportesApiController::reportePersonalizado: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Obtener reportes predefinidos
     * GET /api/reportes/predefinidos
     */
    public function reportesPredefinidos(Request $request): JsonResponse
    {
        try {
            $categoria = $request->input('categoria', 'todos'); // todos, ventas, inventario, financiero, comercial

            $reportesPredefinidos = [];

            switch ($categoria) {
                case 'ventas':
                    $reportesPredefinidos = [
                        'reporte_ventas_diarias' => [
                            'nombre' => 'Ventas Diarias',
                            'descripcion' => 'Resumen de ventas diarias con comparativa',
                            'parametros' => ['fecha' => 'date'],
                            'tiempo_generacion' => '30s'
                        ],
                        'reporte_ventas_mensual' => [
                            'nombre' => 'Ventas Mensual',
                            'descripcion' => 'Análisis completo de ventas mensuales',
                            'parametros' => ['mes' => 'string', 'año' => 'integer'],
                            'tiempo_generacion' => '45s'
                        ],
                        'reporte_top_productos' => [
                            'nombre' => 'Top Productos Vendidos',
                            'descripcion' => 'Ranking de productos más vendidos',
                            'parametros' => ['periodo' => 'string'],
                            'tiempo_generacion' => '20s'
                        ],
                        'reporte_vendedores' => [
                            'nombre' => 'Performance Vendedores',
                            'descripcion' => 'Análisis de productividad de vendedores',
                            'parametros' => ['periodo' => 'string'],
                            'tiempo_generacion' => '25s'
                        ]
                    ];
                    break;

                case 'inventario':
                    $reportesPredefinidos = [
                        'reporte_stock_actual' => [
                            'nombre' => 'Stock Actual',
                            'descripcion' => 'Estado actual del inventario',
                            'parametros' => ['categoria' => 'string'],
                            'tiempo_generacion' => '15s'
                        ],
                        'reporte_productos_bajo_stock' => [
                            'nombre' => 'Productos Bajo Stock',
                            'descripcion' => 'Productos que requieren reposición',
                            'parametros' => ['umbral' => 'integer'],
                            'tiempo_generacion' => '10s'
                        ],
                        'reporte_vencimientos' => [
                            'nombre' => 'Reporte de Vencimientos',
                            'descripcion' => 'Productos próximos a vencer',
                            'parametros' => ['dias_anticipacion' => 'integer'],
                            'tiempo_generacion' => '12s'
                        ],
                        'reporte_kardex' => [
                            'nombre' => 'Kardex por Producto',
                            'descripcion' => 'Movimientos de inventario por producto',
                            'parametros' => ['codigo_producto' => 'string'],
                            'tiempo_generacion' => '35s'
                        ]
                    ];
                    break;

                case 'financiero':
                    $reportesPredefinidos = [
                        'reporte_cuentas_cobrar' => [
                            'nombre' => 'Cuentas por Cobrar',
                            'descripcion' => 'Estado de cuentas por cobrar',
                            'parametros' => ['incluir_vencidas' => 'boolean'],
                            'tiempo_generacion' => '40s'
                        ],
                        'reporte_flujo_caja' => [
                            'nombre' => 'Flujo de Caja',
                            'descripcion' => 'Ingresos y egresos de efectivo',
                            'parametros' => ['periodo' => 'string'],
                            'tiempo_generacion' => '30s'
                        ],
                        'reporte_pyg' => [
                            'nombre' => 'Estado de Resultados',
                            'descripcion' => 'Estado de pérdidas y ganancias',
                            'parametros' => ['periodo' => 'string'],
                            'tiempo_generacion' => '50s'
                        ]
                    ];
                    break;

                case 'comercial':
                    $reportesPredefinidos = [
                        'reporte_clientes_activos' => [
                            'nombre' => 'Clientes Activos',
                            'descripcion' => 'Análisis de base de clientes',
                            'parametros' => ['incluir_estadisticas' => 'boolean'],
                            'tiempo_generacion' => '25s'
                        ],
                        'reporte_clientes_nuevos' => [
                            'nombre' => 'Clientes Nuevos',
                            'descripcion' => 'Nuevos clientes registrados',
                            'parametros' => ['periodo' => 'string'],
                            'tiempo_generacion' => '20s'
                        ]
                    ];
                    break;

                default:
                    // Todos los reportes
                    $reportesPredefinidos = array_merge_recursive(
                        $this->getReportesVentas(),
                        $this->getReportesInventario(),
                        $this->getReportesFinanciero(),
                        $this->getReportesComercial()
                    );
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'categoria' => $categoria,
                    'reportes_disponibles' => $reportesPredefinidos,
                    'total_reportes' => count($reportesPredefinidos)
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error en ReportesApiController::reportesPredefinidos: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Generar reporte predefinido
     * POST /api/reportes/predefinido/{codigo}
     */
    public function generarReportePredefinido(Request $request, $codigo): JsonResponse
    {
        try {
            $parametros = $request->all();

            // Ejecutar reporte según código
            $resultado = match($codigo) {
                'reporte_ventas_diarias' => $this->ejecutarReporteVentasDiarias($parametros),
                'reporte_ventas_mensual' => $this->ejecutarReporteVentasMensual($parametros),
                'reporte_top_productos' => $this->ejecutarReporteTopProductos($parametros),
                'reporte_vendedores' => $this->ejecutarReporteVendedores($parametros),
                'reporte_stock_actual' => $this->ejecutarReporteStockActual($parametros),
                'reporte_productos_bajo_stock' => $this->ejecutarReporteProductosBajoStock($parametros),
                'reporte_vencimientos' => $this->ejecutarReporteVencimientos($parametros),
                'reporte_kardex' => $this->ejecutarReporteKardex($parametros),
                'reporte_cuentas_cobrar' => $this->ejecutarReporteCuentasCobrar($parametros),
                'reporte_flujo_caja' => $this->ejecutarReporteFlujoCaja($parametros),
                'reporte_pyg' => $this->ejecutarReportePYG($parametros),
                'reporte_clientes_activos' => $this->ejecutarReporteClientesActivos($parametros),
                'reporte_clientes_nuevos' => $this->ejecutarReporteClientesNuevos($parametros),
                default => throw new \Exception('Código de reporte no encontrado')
            };

            return response()->json([
                'success' => true,
                'message' => 'Reporte generado exitosamente',
                'data' => $resultado
            ]);

        } catch (\Exception $e) {
            Log::error('Error en ReportesApiController::generarReportePredefinido: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Obtener métricas de reportes
     * GET /api/reportes/metricas
     */
    public function metricasReportes(Request $request): JsonResponse
    {
        try {
            $fechaDesde = $request->input('fecha_desde', Carbon::now()->subMonth()->format('Y-m-d'));
            $fechaHasta = $request->input('fecha_hasta', Carbon::now()->format('Y-m-d'));

            // Métricas de uso de reportes
            $metricasUso = DB::table('reportes_generados')
                ->select([
                    DB::raw('COUNT(*) as total_reportes'),
                    DB::raw('COUNT(DISTINCT usuario_id) as usuarios_unicos'),
                    DB::raw('tipo_reporte'),
                    DB::raw('COUNT(*) as cantidad_por_tipo')
                ])
                ->whereBetween('created_at', [$fechaDesde . ' 00:00:00', $fechaHasta . ' 23:59:59'])
                ->groupBy('tipo_reporte')
                ->get();

            // Reportes más solicitados
            $reportesPopulares = DB::table('reportes_generados')
                ->select([
                    'tipo_reporte',
                    DB::raw('COUNT(*) as solicitudes')
                ])
                ->whereBetween('created_at', [$fechaDesde . ' 00:00:00', $fechaHasta . ' 23:59:59'])
                ->groupBy('tipo_reporte')
                ->orderBy('solicitudes', 'desc')
                ->limit(10)
                ->get();

            // Tiempo promedio de generación
            $tiempoPromedioGeneracion = DB::table('reportes_generados')
                ->whereBetween('created_at', [$fechaDesde . ' 00:00:00', $fechaHasta . ' 23:59:59'])
                ->whereNotNull('tiempo_generacion')
                ->avg('tiempo_generacion');

            // Formatos más utilizados
            $formatosUtilizados = DB::table('reportes_generados')
                ->select([
                    'formato_salida',
                    DB::raw('COUNT(*) as cantidad')
                ])
                ->whereBetween('created_at', [$fechaDesde . ' 00:00:00', $fechaHasta . ' 23:59:59'])
                ->groupBy('formato_salida')
                ->pluck('cantidad', 'formato_salida');

            return response()->json([
                'success' => true,
                'data' => [
                    'metricas_uso' => $metricasUso,
                    'reportes_populares' => $reportesPopulares,
                    'tiempo_promedio_generacion' => round($tiempoPromedioGeneracion ?? 0, 2),
                    'formatos_mas_utilizados' => $formatosUtilizados,
                    'periodo_analisis' => [
                        'desde' => $fechaDesde,
                        'hasta' => $fechaHasta
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error en ReportesApiController::metricasReportes: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Programar reporte automático
     * POST /api/reportes/programar
     */
    public function programarReporte(Request $request): JsonResponse
    {
        try {
            // Validación de parámetros
            $validator = Validator::make($request->all(), [
                'nombre' => 'required|string|max:255',
                'tipo_reporte' => 'required|in:ventas,inventario,financiero,clientes,productos',
                'frecuencia' => 'required|in:diaria,semanal,mensual,trimestral',
                'dia_semana' => 'integer|between:1,7', // Para reportes semanales
                'dia_mes' => 'integer|between:1,31', // Para reportes mensuales
                'formato_salida' => 'required|in:json,pdf,excel,csv',
                'email_destinatarios' => 'required|array',
                'email_destinatarios.*' => 'email',
                'parametros' => 'array',
                'activo' => 'boolean|default:true'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parámetros inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            // Preparar datos para programación
            $datosProgramacion = [
                'nombre' => $request->nombre,
                'tipo_reporte' => $request->tipo_reporte,
                'frecuencia' => $request->frecuencia,
                'dia_semana' => $request->dia_semana,
                'dia_mes' => $request->dia_mes,
                'formato_salida' => $request->formato_salida,
                'email_destinatarios' => json_encode($request->email_destinatarios),
                'parametros' => json_encode($request->parametros ?? []),
                'activo' => $request->activo ?? true,
                'user_id' => auth()->id(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ];

            // Insertar programación
            $programacionId = DB::table('reportes_programados')->insertGetId($datosProgramacion);

            // Calcular próxima fecha de ejecución
            $proximaEjecucion = $this->calcularProximaEjecucion($request);

            DB::table('reportes_programados')
                ->where('id', $programacionId)
                ->update(['proxima_ejecucion' => $proximaEjecucion]);

            return response()->json([
                'success' => true,
                'message' => 'Reporte programado exitosamente',
                'data' => [
                    'id' => $programacionId,
                    'nombre' => $request->nombre,
                    'proxima_ejecucion' => $proximaEjecucion
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error en ReportesApiController::programarReporte: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Obtener reportes programados
     * GET /api/reportes/programados
     */
    public function reportesProgramados(Request $request): JsonResponse
    {
        try {
            $estado = $request->input('estado', 'todos'); // todos, activos, inactivos

            $query = DB::table('reportes_programados')
                ->select([
                    'id',
                    'nombre',
                    'tipo_reporte',
                    'frecuencia',
                    'formato_salida',
                    'activo',
                    'proxima_ejecucion',
                    'created_at',
                    'user_id',
                    'users.name as usuario_nombre'
                ])
                ->leftJoin('users', 'reportes_programados.user_id', '=', 'users.id');

            if ($estado === 'activos') {
                $query->where('activo', true);
            } elseif ($estado === 'inactivos') {
                $query->where('activo', false);
            }

            $reportesProgramados = $query->orderBy('proxima_ejecucion')->get();

            // Procesar datos para respuesta
            foreach ($reportesProgramados as $reporte) {
                $reporte->email_destinatarios = json_decode($reporte->email_destinatarios, true);
                $reporte->parametros = json_decode($reporte->parametros, true) ?? [];
                $reporte->estado_ejecucion = $this->evaluarEstadoEjecucion($reporte);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'reportes_programados' => $reportesProgramados,
                    'total_activos' => $reportesProgramados->where('activo', true)->count(),
                    'total_inactivos' => $reportesProgramados->where('activo', false)->count()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error en ReportesApiController::reportesProgramados: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Obtener historial de reportes generados
     * GET /api/reportes/historial
     */
    public function historialReportes(Request $request): JsonResponse
    {
        try {
            // Validación de parámetros
            $validator = Validator::make($request->all(), [
                'page' => 'integer|min:1',
                'per_page' => 'integer|min:1|max:100',
                'tipo_reporte' => 'string',
                'usuario_id' => 'integer',
                'fecha_desde' => 'date',
                'fecha_hasta' => 'date|after:fecha_desde',
                'formato_salida' => 'in:json,pdf,excel,csv'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parámetros inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            // Parámetros de paginación
            $page = $request->input('page', 1);
            $perPage = $request->input('per_page', 20);
            $offset = ($page - 1) * $perPage;

            // Query base
            $query = DB::table('reportes_generados')
                ->select([
                    'id',
                    'tipo_reporte',
                    'formato_salida',
                    'parametros_filtro',
                    'tiempo_generacion',
                    'archivo_generado',
                    'estado',
                    'error_mensaje',
                    'created_at',
                    'user_id',
                    'users.name as usuario_nombre'
                ])
                ->leftJoin('users', 'reportes_generados.user_id', '=', 'users.id');

            // Aplicar filtros
            if ($request->filled('tipo_reporte')) {
                $query->where('tipo_reporte', 'LIKE', '%' . $request->tipo_reporte . '%');
            }

            if ($request->filled('usuario_id')) {
                $query->where('user_id', $request->usuario_id);
            }

            if ($request->filled('fecha_desde')) {
                $query->whereDate('created_at', '>=', $request->fecha_desde);
            }

            if ($request->filled('fecha_hasta')) {
                $query->whereDate('created_at', '<=', $request->fecha_hasta);
            }

            if ($request->filled('formato_salida')) {
                $query->where('formato_salida', $request->formato_salida);
            }

            // Contar total para paginación
            $total = $query->count();

            // Ordenamiento
            $query->orderBy('created_at', 'desc');

            // Paginación
            $historial = $query->offset($offset)->limit($perPage)->get();

            // Procesar datos
            foreach ($historial as $reporte) {
                $reporte->parametros_filtro = json_decode($reporte->parametros_filtro, true) ?? [];
                $reporte->tamaño_archivo = $this->obtenerTamañoArchivo($reporte->archivo_generado);
            }

            // Calcular metadatos de paginación
            $totalPages = ceil($total / $perPage);
            $hasNextPage = $page < $totalPages;
            $hasPrevPage = $page > 1;

            return response()->json([
                'success' => true,
                'data' => [
                    'historial' => $historial,
                    'pagination' => [
                        'current_page' => $page,
                        'per_page' => $perPage,
                        'total' => $total,
                        'total_pages' => $totalPages,
                        'has_next_page' => $hasNextPage,
                        'has_prev_page' => $hasPrevPage,
                        'next_page_url' => $hasNextPage ? $request->url() . '?page=' . ($page + 1) : null,
                        'prev_page_url' => $hasPrevPage ? $request->url() . '?page=' . ($page - 1) : null
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error en ReportesApiController::historialReportes: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Descargar reporte generado
     * GET /api/reportes/descargar/{id}
     */
    public function descargarReporte(Request $request, $id): JsonResponse
    {
        try {
            // Validar ID
            if (!is_numeric($id) || $id <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID de reporte inválido'
                ], 400);
            }

            // Obtener información del reporte
            $reporte = DB::table('reportes_generados')
                ->where('id', $id)
                ->first();

            if (!$reporte) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reporte no encontrado'
                ], 404);
            }

            if ($reporte->estado !== 'completado') {
                return response()->json([
                    'success' => false,
                    'message' => 'El reporte aún no ha sido completado'
                ], 400);
            }

            if (!$reporte->archivo_generado || !file_exists($reporte->archivo_generado)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Archivo de reporte no encontrado'
                ], 404);
            }

            // Registrar descarga
            DB::table('reportes_generados')
                ->where('id', $id)
                ->increment('descargas', 1);

            // Retornar información del archivo para descarga
            return response()->json([
                'success' => true,
                'data' => [
                    'archivo' => $reporte->archivo_generado,
                    'nombre_original' => basename($reporte->archivo_generado),
                    'formato' => $reporte->formato_salida,
                    'tamaño' => $this->obtenerTamañoArchivo($reporte->archivo_generado),
                    'fecha_generacion' => $reporte->created_at,
                    'url_descarga' => route('reportes.download', $id) // Esta ruta debería manejarse por un controlador de archivos
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error en ReportesApiController::descargarReporte: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | MÉTODOS DE GENERACIÓN DE REPORTES
    |--------------------------------------------------------------------------
    */

    /**
     * Generar reporte de ventas personalizado
     */
    private function generarReporteVentasPersonalizado(Request $request)
    {
        $filtros = $request->filtros ?? [];
        
        // Query base para ventas
        $query = DB::table('Doccab')
            ->select([
                'Doccab.*',
                'Clientes.Nombre as ClienteNombre',
                'Clientes.Ruc as ClienteRuc'
            ])
            ->leftJoin('Clientes', 'Doccab.Codcli', '=', 'Clientes.Codigo')
            ->whereBetween('Doccab.Fecha', [$request->periodo_desde, $request->periodo_hasta]);

        // Aplicar filtros
        if (!empty($filtros['cliente_codigo'])) {
            $query->where('Doccab.Codcli', $filtros['cliente_codigo']);
        }

        if (!empty($filtros['vendedor'])) {
            $query->where('Doccab.Vendedor', 'LIKE', '%' . $filtros['vendedor'] . '%');
        }

        if (!empty($filtros['estado_documento'])) {
            $query->where('Doccab.Estado', $filtros['estado_documento']);
        }

        $datos = $query->orderBy('Doccab.Fecha')->get();

        // Calcular resumen
        $resumen = [
            'total_facturas' => $datos->count(),
            'monto_total' => $datos->sum('Total'),
            'saldo_pendiente' => $datos->sum('Saldo'),
            'promedio_factura' => $datos->count() > 0 ? round($datos->sum('Total') / $datos->count(), 2) : 0,
            'facturas_pagadas' => $datos->where('Estado', 'Pagado')->count(),
            'facturas_pendientes' => $datos->where('Estado', 'Pendiente')->count()
        ];

        return [
            'datos' => $request->incluir_detalle ? $datos : [],
            'resumen' => $resumen,
            'graficos' => $request->incluir_graficos ? $this->generarGraficosVentas($datos) : null
        ];
    }

    /**
     * Generar reporte de inventario personalizado
     */
    private function generarReporteInventarioPersonalizado(Request $request)
    {
        $filtros = $request->filtros ?? [];
        
        $query = DB::table('Saldos')
            ->where('Estado', 'Activo');

        // Aplicar filtros
        if (!empty($filtros['categoria'])) {
            $query->where('Categoria', 'LIKE', '%' . $filtros['categoria'] . '%');
        }

        $datos = $query->get();

        // Enriquecer datos
        foreach ($datos as $producto) {
            $producto->valor_inventario = $producto->Stock * $producto->PrecioCosto;
            $producto->necesita_reorden = $producto->Stock <= $producto->StockMinimo;
            $producto->dias_hasta_vencimiento = $producto->Vencimiento ? 
                Carbon::now()->diffInDays(Carbon::parse($producto->Vencimiento), false) : null;
        }

        $resumen = [
            'total_productos' => $datos->count(),
            'valor_total_inventario' => $datos->sum('valor_inventario'),
            'productos_con_stock' => $datos->where('Stock', '>', 0)->count(),
            'productos_sin_stock' => $datos->where('Stock', 0)->count(),
            'productos_bajo_stock' => $datos->where('necesita_reorden', true)->count(),
            'productos_vencidos' => $datos->where('dias_hasta_vencimiento', '<', 0)->count(),
            'productos_por_vencer' => $datos->whereBetween('dias_hasta_vencimiento', [0, 30])->count()
        ];

        return [
            'datos' => $request->incluir_detalle ? $datos : [],
            'resumen' => $resumen,
            'graficos' => $request->incluir_graficos ? $this->generarGraficosInventario($datos) : null
        ];
    }

    /**
     * Generar reporte financiero personalizado
     */
    private function generarReporteFinancieroPersonalizado(Request $request)
    {
        $cuentasPorCobrar = DB::table('Doccab')
            ->where('Saldo', '>', 0)
            ->select([
                '*',
                DB::raw('DATEDIFF(CURDATE(), FechaVenc) as dias_vencido')
            ])
            ->get();

        // Categorizar por antigüedad
        $antiguedadSaldos = [
            'al_dia' => $cuentasPorCobrar->where('dias_vencido', '<=', 0)->sum('Saldo'),
            '1_30_dias' => $cuentasPorCobrar->whereBetween('dias_vencido', [1, 30])->sum('Saldo'),
            '31_60_dias' => $cuentasPorCobrar->whereBetween('dias_vencido', [31, 60])->sum('Saldo'),
            '61_90_dias' => $cuentasPorCobrar->whereBetween('dias_vencido', [61, 90])->sum('Saldo'),
            'mas_90_dias' => $cuentasPorCobrar->where('dias_vencido', '>', 90)->sum('Saldo')
        ];

        $resumen = [
            'total_cuentas_cobrar' => $cuentasPorCobrar->sum('Saldo'),
            'facturas_vencidas' => $cuentasPorCobrar->where('dias_vencido', '>', 0)->count(),
            'monto_vencido' => $cuentasPorCobrar->where('dias_vencido', '>', 0)->sum('Saldo'),
            'facturas_por_vencer' => $cuentasPorCobrar->whereBetween('dias_vencido', [1, 7])->count(),
            'promedio_dias_vencido' => $cuentasPorCobrar->where('dias_vencido', '>', 0)->avg('dias_vencido') ?? 0
        ];

        return [
            'datos' => $request->incluir_detalle ? $cuentasPorCobrar : [],
            'resumen' => array_merge($resumen, ['antiguedad_saldos' => $antiguedadSaldos]),
            'graficos' => $request->incluir_graficos ? $this->generarGraficosFinanciero($cuentasPorCobrar) : null
        ];
    }

    /**
     * Generar reporte de clientes personalizado
     */
    private function generarReporteClientesPersonalizado(Request $request)
    {
        $filtros = $request->filtros ?? [];
        
        $query = DB::table('Clientes')
            ->where('Estado', 'Activo');

        $clientes = $query->get();

        // Enriquecer con estadísticas de compras
        foreach ($clientes as $cliente) {
            $compras = DB::table('Doccab')
                ->where('Codcli', $cliente->Codigo)
                ->whereBetween('Fecha', [$request->periodo_desde, $request->periodo_hasta])
                ->select([
                    DB::raw('COUNT(*) as num_facturas'),
                    DB::raw('SUM(Total) as total_compras'),
                    DB::raw('AVG(Total) as ticket_promedio'),
                    DB::raw('MAX(Fecha) as ultima_compra')
                ])
                ->first();

            $cliente->estadisticas_compras = $compras;
        }

        $resumen = [
            'total_clientes' => $clientes->count(),
            'clientes_con_compras' => $clientes->where('estadisticas_compras->num_facturas', '>', 0)->count(),
            'clientes_sin_compras' => $clientes->where('estadisticas_compras->num_facturas', 0)->count(),
            'ticket_promedio_general' => $clientes->where('estadisticas_compras->num_facturas', '>', 0)->avg('estadisticas_compras->ticket_promedio') ?? 0,
            'total_facturas_generadas' => $clientes->sum('estadisticas_compras->num_facturas'),
            'monto_total_compras' => $clientes->sum('estadisticas_compras->total_compras')
        ];

        return [
            'datos' => $request->incluir_detalle ? $clientes : [],
            'resumen' => $resumen,
            'graficos' => $request->incluir_graficos ? $this->generarGraficosClientes($clientes) : null
        ];
    }

    /**
     * Generar reporte de productos personalizado
     */
    private function generarReporteProductosPersonalizado(Request $request)
    {
        $filtros = $request->filtros ?? [];
        
        $query = DB::table('Saldos')
            ->leftJoin('Docdet', 'Saldos.Codigo', '=', 'Docdet.Codpro')
            ->leftJoin('Doccab', 'Docdet.Numerod', '=', 'Doccab.Numero')
            ->select([
                'Saldos.*',
                DB::raw('SUM(Docdet.Cantidad) as cantidad_vendida'),
                DB::raw('SUM(Docdet.Total) as ingresos_generados'),
                DB::raw('COUNT(DISTINCT Docdet.Numerod) as num_facturas')
            ])
            ->whereBetween('Doccab.Fecha', [$request->periodo_desde, $request->periodo_hasta])
            ->where('Doccab.Estado', '!=', 'Anulado')
            ->groupBy('Saldos.Id');

        // Aplicar filtros
        if (!empty($filtros['categoria'])) {
            $query->where('Saldos.Categoria', 'LIKE', '%' . $filtros['categoria'] . '%');
        }

        $productos = $query->get();

        $resumen = [
            'total_productos' => $productos->count(),
            'productos_vendidos' => $productos->where('cantidad_vendida', '>', 0)->count(),
            'productos_sin_ventas' => $productos->where('cantidad_vendida', 0)->count(),
            'cantidad_total_vendida' => $productos->sum('cantidad_vendida'),
            'ingresos_totales' => $productos->sum('ingresos_generados'),
            'precio_promedio_venta' => $productos->avg('PrecioVta') ?? 0
        ];

        return [
            'datos' => $request->incluir_detalle ? $productos : [],
            'resumen' => $resumen,
            'graficos' => $request->incluir_graficos ? $this->generarGraficosProductos($productos) : null
        ];
    }

    /**
     * Generar reporte completo personalizado
     */
    private function generarReporteCompletoPersonalizado(Request $request)
    {
        return [
            'ventas' => $this->generarReporteVentasPersonalizado($request),
            'inventario' => $this->generarReporteInventarioPersonalizado($request),
            'financiero' => $this->generarReporteFinancieroPersonalizado($request),
            'resumen_general' => [
                'periodo' => ['desde' => $request->periodo_desde, 'hasta' => $request->periodo_hasta],
                'fecha_generacion' => Carbon::now(),
                'componentes_incluidos' => ['ventas', 'inventario', 'financiero']
            ]
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | MÉTODOS AUXILIARES
    |--------------------------------------------------------------------------
    */

    /**
     * Aplicar agrupación a los datos
     */
    private function aplicarAgrupacion($datos, $campo)
    {
        return $datos->groupBy($campo)->map(function($grupo) {
            return [
                'total' => $grupo->count(),
                'suma' => $grupo->sum('Total') ?? $grupo->sum('valor') ?? 0,
                'promedio' => $grupo->avg('Total') ?? $grupo->avg('valor') ?? 0
            ];
        });
    }

    /**
     * Calcular próxima fecha de ejecución
     */
    private function calcularProximaEjecucion($request)
    {
        $ahora = Carbon::now();
        
        return match($request->frecuencia) {
            'diaria' => $ahora->addDay(),
            'semanal' => $ahora->next($request->dia_semana ?? 1),
            'mensual' => $ahora->addMonth()->day($request->dia_mes ?? 1),
            'trimestral' => $ahora->addMonths(3)->day($request->dia_mes ?? 1),
            default => $ahora->addDay()
        };
    }

    /**
     * Evaluar estado de ejecución de reporte programado
     */
    private function evaluarEstadoEjecucion($reporte)
    {
        if (!$reporte->activo) {
            return 'inactivo';
        }

        $proximaEjecucion = Carbon::parse($reporte->proxima_ejecucion);
        
        if ($proximaEjecucion->isPast()) {
            return 'pendiente';
        }

        return 'programado';
    }

    /**
     * Obtener tamaño de archivo
     */
    private function obtenerTamañoArchivo($archivo)
    {
        if (!$archivo || !file_exists($archivo)) {
            return 0;
        }
        
        return filesize($archivo);
    }

    /**
     * Generar gráficos de ventas
     */
    private function generarGraficosVentas($datos)
    {
        return [
            'ventas_por_dia' => $datos->groupBy('Fecha')->map(function($ventas) {
                return [
                    'fecha' => $ventas->first()->Fecha,
                    'total' => $ventas->sum('Total'),
                    'facturas' => $ventas->count()
                ];
            })->values()
        ];
    }

    /**
     * Generar gráficos de inventario
     */
    private function generarGraficosInventario($datos)
    {
        return [
            'distribucion_categorias' => $datos->groupBy('Categoria')->map(function($categoria) {
                return [
                    'categoria' => $categoria->first()->Categoria,
                    'productos' => $categoria->count(),
                    'valor' => $categoria->sum('valor_inventario')
                ];
            })->values(),
            'estado_stock' => [
                'con_stock' => $datos->where('Stock', '>', 0)->count(),
                'sin_stock' => $datos->where('Stock', 0)->count(),
                'bajo_stock' => $datos->where('necesita_reorden', true)->count()
            ]
        ];
    }

    /**
     * Generar gráficos financieros
     */
    private function generarGraficosFinanciero($datos)
    {
        return [
            'antiguedad_saldos' => $datos->groupBy(function($item) {
                $dias = $item->dias_vencido ?? 0;
                if ($dias <= 0) return 'al_dia';
                if ($dias <= 30) return '1_30_dias';
                if ($dias <= 60) return '31_60_dias';
                if ($dias <= 90) return '61_90_dias';
                return 'mas_90_dias';
            })->map(function($grupo) {
                return [
                    'cantidad' => $grupo->count(),
                    'monto' => $grupo->sum('Saldo')
                ];
            })
        ];
    }

    /**
     * Generar gráficos de clientes
     */
    private function generarGraficosClientes($datos)
    {
        return [
            'distribucion_tipo' => $datos->groupBy('TipoCliente')->map(function($grupo) {
                return [
                    'tipo' => $grupo->first()->TipoCliente,
                    'cantidad' => $grupo->count()
                ];
            })->values()
        ];
    }

    /**
     * Generar gráficos de productos
     */
    private function generarGraficosProductos($datos)
    {
        return [
            'top_productos' => $datos->sortByDesc('cantidad_vendida')->take(10)->values(),
            'ventas_por_categoria' => $datos->groupBy('Categoria')->map(function($categoria) {
                return [
                    'categoria' => $categoria->first()->Categoria,
                    'cantidad_vendida' => $categoria->sum('cantidad_vendida'),
                    'ingresos' => $categoria->sum('ingresos_generados')
                ];
            })->values()
        ];
    }

    // Métodos para obtener listas de reportes predefinidos
    private function getReportesVentas()
    {
        return [
            'reporte_ventas_diarias' => [
                'nombre' => 'Ventas Diarias',
                'descripcion' => 'Resumen de ventas diarias con comparativa',
                'categoria' => 'ventas',
                'parametros' => ['fecha' => 'date'],
                'tiempo_generacion' => '30s'
            ],
            'reporte_ventas_mensual' => [
                'nombre' => 'Ventas Mensual',
                'descripcion' => 'Análisis completo de ventas mensuales',
                'categoria' => 'ventas',
                'parametros' => ['mes' => 'string', 'año' => 'integer'],
                'tiempo_generacion' => '45s'
            ]
        ];
    }

    private function getReportesInventario()
    {
        return [
            'reporte_stock_actual' => [
                'nombre' => 'Stock Actual',
                'descripcion' => 'Estado actual del inventario',
                'categoria' => 'inventario',
                'parametros' => ['categoria' => 'string'],
                'tiempo_generacion' => '15s'
            ],
            'reporte_productos_bajo_stock' => [
                'nombre' => 'Productos Bajo Stock',
                'descripcion' => 'Productos que requieren reposición',
                'categoria' => 'inventario',
                'parametros' => ['umbral' => 'integer'],
                'tiempo_generacion' => '10s'
            ]
        ];
    }

    private function getReportesFinanciero()
    {
        return [
            'reporte_cuentas_cobrar' => [
                'nombre' => 'Cuentas por Cobrar',
                'descripcion' => 'Estado de cuentas por cobrar',
                'categoria' => 'financiero',
                'parametros' => ['incluir_vencidas' => 'boolean'],
                'tiempo_generacion' => '40s'
            ],
            'reporte_flujo_caja' => [
                'nombre' => 'Flujo de Caja',
                'descripcion' => 'Ingresos y egresos de efectivo',
                'categoria' => 'financiero',
                'parametros' => ['periodo' => 'string'],
                'tiempo_generacion' => '30s'
            ]
        ];
    }

    private function getReportesComercial()
    {
        return [
            'reporte_clientes_activos' => [
                'nombre' => 'Clientes Activos',
                'descripcion' => 'Análisis de base de clientes',
                'categoria' => 'comercial',
                'parametros' => ['incluir_estadisticas' => 'boolean'],
                'tiempo_generacion' => '25s'
            ],
            'reporte_clientes_nuevos' => [
                'nombre' => 'Clientes Nuevos',
                'descripcion' => 'Nuevos clientes registrados',
                'categoria' => 'comercial',
                'parametros' => ['periodo' => 'string'],
                'tiempo_generacion' => '20s'
            ]
        ];
    }

    // Métodos para ejecutar reportes predefinidos
    private function ejecutarReporteVentasDiarias($parametros)
    {
        $fecha = $parametros['fecha'] ?? Carbon::now()->format('Y-m-d');
        
        $ventas = DB::table('Doccab')
            ->whereDate('Fecha', $fecha)
            ->select([
                'Numero',
                'Fecha',
                'Total',
                'Estado',
                'Codcli',
                'Vendedor'
            ])
            ->get();

        return [
            'reporte' => 'ventas_diarias',
            'fecha' => $fecha,
            'datos' => $ventas,
            'resumen' => [
                'total_facturas' => $ventas->count(),
                'monto_total' => $ventas->sum('Total'),
                'promedio' => $ventas->count() > 0 ? round($ventas->sum('Total') / $ventas->count(), 2) : 0
            ]
        ];
    }

    private function ejecutarReporteVentasMensual($parametros)
    {
        $mes = $parametros['mes'] ?? Carbon::now()->month;
        $año = $parametros['año'] ?? Carbon::now()->year;
        
        $ventas = DB::table('Doccab')
            ->whereMonth('Fecha', $mes)
            ->whereYear('Fecha', $año)
            ->select([
                'Fecha',
                'Numero',
                'Total',
                'Estado',
                'Vendedor'
            ])
            ->get();

        return [
            'reporte' => 'ventas_mensual',
            'periodo' => "$año-$mes",
            'datos' => $ventas,
            'resumen' => [
                'total_facturas' => $ventas->count(),
                'monto_total' => $ventas->sum('Total'),
                'facturas_pagadas' => $ventas->where('Estado', 'Pagado')->count(),
                'promedio_diario' => $ventas->groupBy('Fecha')->map(fn($dia) => $dia->sum('Total'))->avg() ?? 0
            ]
        ];
    }

    private function ejecutarReporteTopProductos($parametros)
    {
        $periodo = $parametros['periodo'] ?? 'mensual';
        $fechaDesde = match($periodo) {
            'diario' => Carbon::now()->subDay(),
            'semanal' => Carbon::now()->subWeek(),
            'mensual' => Carbon::now()->subMonth(),
            default => Carbon::now()->subMonth()
        };

        $productos = DB::table('Docdet')
            ->join('Saldos', 'Docdet.Codpro', '=', 'Saldos.Codigo')
            ->where('Docdet.created_at', '>=', $fechaDesde)
            ->select([
                'Saldos.Descripcion',
                DB::raw('SUM(Docdet.Cantidad) as cantidad'),
                DB::raw('SUM(Docdet.Total) as ingresos')
            ])
            ->groupBy('Saldos.Descripcion')
            ->orderBy('cantidad', 'desc')
            ->limit(20)
            ->get();

        return [
            'reporte' => 'top_productos',
            'periodo' => $periodo,
            'datos' => $productos
        ];
    }

    private function ejecutarReporteVendedores($parametros)
    {
        $periodo = $parametros['periodo'] ?? 'mensual';
        $fechaDesde = match($periodo) {
            'diario' => Carbon::now()->subDay(),
            'semanal' => Carbon::now()->subWeek(),
            'mensual' => Carbon::now()->subMonth(),
            default => Carbon::now()->subMonth()
        };

        $vendedores = DB::table('Doccab')
            ->where('created_at', '>=', $fechaDesde)
            ->whereNotNull('Vendedor')
            ->select([
                'Vendedor',
                DB::raw('COUNT(*) as facturas'),
                DB::raw('SUM(Total) as ventas'),
                DB::raw('AVG(Total) as promedio')
            ])
            ->groupBy('Vendedor')
            ->orderBy('ventas', 'desc')
            ->get();

        return [
            'reporte' => 'vendedores',
            'periodo' => $periodo,
            'datos' => $vendedores
        ];
    }

    // Métodos stub para otros reportes predefinidos
    private function ejecutarReporteStockActual($parametros) { return ['reporte' => 'stock_actual', 'datos' => []]; }
    private function ejecutarReporteProductosBajoStock($parametros) { return ['reporte' => 'productos_bajo_stock', 'datos' => []]; }
    private function ejecutarReporteVencimientos($parametros) { return ['reporte' => 'vencimientos', 'datos' => []]; }
    private function ejecutarReporteKardex($parametros) { return ['reporte' => 'kardex', 'datos' => []]; }
    private function ejecutarReporteCuentasCobrar($parametros) { return ['reporte' => 'cuentas_cobrar', 'datos' => []]; }
    private function ejecutarReporteFlujoCaja($parametros) { return ['reporte' => 'flujo_caja', 'datos' => []]; }
    private function ejecutarReportePYG($parametros) { return ['reporte' => 'pyg', 'datos' => []]; }
    private function ejecutarReporteClientesActivos($parametros) { return ['reporte' => 'clientes_activos', 'datos' => []]; }
    private function ejecutarReporteClientesNuevos($parametros) { return ['reporte' => 'clientes_nuevos', 'datos' => []]; }
}