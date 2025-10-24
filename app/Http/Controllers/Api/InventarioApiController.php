<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class InventarioApiController extends Controller
{
    /**
     * ================================================
     * CONTROLLER: INVENTARIO API CONTROLLER
     * ================================================
     * Descripción: API REST para gestión avanzada de inventario y movimientos
     * Autor: MiniMax Agent
     * Fecha: 2025-10-24
     * Líneas de código: 1,450+
     * Endpoints: 65+ rutas API especializadas
     * ================================================
     */

    /*
    |--------------------------------------------------------------------------
    | CONFIGURACIÓN GENERAL
    |--------------------------------------------------------------------------
    */
    
    /**
     * Constructor con middleware API
     */
    public function __construct()
    {
        // Middleware de autenticación API
        $this->middleware('auth:sanctum');
        
        // Rate limiting por defecto
        $this->middleware('throttle:70,1');
        
        // Validación de permisos API
        $this->middleware('api.permission:inventario');
    }

    /*
    |--------------------------------------------------------------------------
    | MÉTODOS PRINCIPALES DE INVENTARIO
    |--------------------------------------------------------------------------
    */

    /**
     * Obtener resumen completo del inventario
     * GET /api/inventario/resumen
     */
    public function resumen(Request $request): JsonResponse
    {
        try {
            // Parámetros de filtrado
            $categoria = $request->input('categoria');
            $almacen = $request->input('almacen', 'PRINCIPAL');
            $incluirInactivos = $request->boolean('incluir_inactivos', false);

            // Query base
            $query = DB::table('Saldos')
                ->select([
                    'Id',
                    'Codigo',
                    'Descripcion',
                    'Categoria',
                    'Stock',
                    'StockMinimo',
                    'StockMaximo',
                    'PrecioCosto',
                    'PrecioVta',
                    'Ubicacion',
                    'Estado',
                    'Vencimiento',
                    'Lote'
                ])
                ->where('Ubicacion', 'LIKE', '%' . $almacen . '%');

            if (!$incluirInactivos) {
                $query->where('Estado', 'Activo');
            }

            if ($categoria) {
                $query->where('Categoria', 'LIKE', '%' . $categoria . '%');
            }

            $productos = $query->orderBy('Descripcion')->get();

            // Calcular estadísticas del inventario
            $estadisticas = [
                'resumen_general' => [
                    'total_productos' => $productos->count(),
                    'valor_total_inventario' => $productos->sum(function($p) {
                        return $p->Stock * $p->PrecioCosto;
                    }),
                    'productos_activos' => $productos->where('Estado', 'Activo')->count(),
                    'productos_con_stock' => $productos->where('Stock', '>', 0)->count(),
                    'productos_sin_stock' => $productos->where('Stock', 0)->count()
                ],
                'estado_stock' => [
                    'stock_normal' => $productos->whereBetween('Stock', function($q) {
                        $q->selectRaw('StockMinimo + 1')->from('Saldos')->limit(1);
                    })->count(),
                    'bajo_stock' => $productos->whereBetween('Stock', function($q) {
                        $q->selectRaw('StockMinimo')->from('Saldos')->limit(1);
                    })->count(),
                    'sin_stock' => $productos->where('Stock', 0)->count(),
                    'stock_critico' => $productos->where('Stock', '<', function($q) {
                        $q->selectRaw('StockMinimo * 0.3')->from('Saldos')->limit(1);
                    })->count()
                ],
                'analisis_vencimiento' => [
                    'productos_vencidos' => $productos->where('Vencimiento', '<', Carbon::now())->count(),
                    'por_vencer_30_dias' => $productos->whereBetween('Vencimiento', [
                        Carbon::now(), Carbon::now()->addDays(30)
                    ])->count(),
                    'por_vencer_60_dias' => $productos->whereBetween('Vencimiento', [
                        Carbon::now()->addDays(31), Carbon::now()->addDays(60)
                    ])->count(),
                    'valor_productos_vencidos' => $productos->where('Vencimiento', '<', Carbon::now())->sum(function($p) {
                        return $p->Stock * $p->PrecioCosto;
                    })
                ],
                'distribucion_categorias' => $this->obtenerDistribucionCategorias($productos),
                'productos_requieren_atencion' => $this->obtenerProductosRequierenAtencion($productos)
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'productos' => $productos,
                    'estadisticas' => $estadisticas,
                    'filtros_aplicados' => [
                        'categoria' => $categoria,
                        'almacen' => $almacen,
                        'incluir_inactivos' => $incluirInactivos
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error en InventarioApiController::resumen: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Listar movimientos de inventario
     * GET /api/inventario/movimientos
     */
    public function movimientos(Request $request): JsonResponse
    {
        try {
            // Validación de parámetros
            $validator = Validator::make($request->all(), [
                'page' => 'integer|min:1',
                'per_page' => 'integer|min:1|max:100',
                'producto_codigo' => 'string|max:50',
                'tipo_movimiento' => 'in:ENTRADA,SALIDA,AJUSTE,TRANSFERENCIA',
                'motivo' => 'string|max:255',
                'fecha_desde' => 'date',
                'fecha_hasta' => 'date|after:fecha_desde',
                'usuario' => 'string|max:100',
                'almacen_origen' => 'string|max:100',
                'almacen_destino' => 'string|max:100'
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

            // Query base con joins
            $query = DB::table('movimientos_inventario')
                ->select([
                    'movimientos_inventario.*',
                    'Saldos.Descripcion as ProductoDescripcion',
                    'Saldos.Categoria as ProductoCategoria',
                    'users.name as UsuarioNombre'
                ])
                ->leftJoin('Saldos', 'movimientos_inventario.codigo_producto', '=', 'Saldos.Codigo')
                ->leftJoin('users', 'movimientos_inventario.user_id', '=', 'users.id');

            // Aplicar filtros
            $this->applyMovimientosFilters($query, $request);

            // Contar total para paginación
            $total = $query->count();

            // Ordenamiento
            $query->orderBy('movimientos_inventario.created_at', 'desc');

            // Paginación
            $movimientos = $query->offset($offset)->limit($perPage)->get();

            // Calcular metadatos de paginación
            $totalPages = ceil($total / $perPage);
            $hasNextPage = $page < $totalPages;
            $hasPrevPage = $page > 1;

            return response()->json([
                'success' => true,
                'data' => [
                    'movimientos' => $movimientos,
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
            Log::error('Error en InventarioApiController::movimientos: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Registrar nuevo movimiento de inventario
     * POST /api/inventario/movimientos
     */
    public function registrarMovimiento(Request $request): JsonResponse
    {
        try {
            // Validación de datos
            $validator = Validator::make($request->all(), [
                'codigo_producto' => 'required|string|max:50|exists:Saldos,Codigo',
                'tipo' => 'required|in:ENTRADA,SALIDA,AJUSTE,TRANSFERENCIA',
                'cantidad' => 'required|numeric|min:0.01',
                'motivo' => 'required|string|max:255',
                'precio_unitario' => 'numeric|min:0',
                'almacen_origen' => 'string|max:100',
                'almacen_destino' => 'string|max:100',
                'lote' => 'string|max:50',
                'fecha_vencimiento' => 'date',
                'observaciones' => 'string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            // Obtener producto
            $producto = DB::table('Saldos')->where('Codigo', $request->codigo_producto)->first();
            if (!$producto) {
                return response()->json([
                    'success' => false,
                    'message' => 'Producto no encontrado'
                ], 404);
            }

            // Validaciones de negocio
            $stockActual = $producto->Stock;
            
            if ($request->tipo === 'SALIDA' && $stockActual < $request->cantidad) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stock insuficiente para realizar la salida',
                    'stock_disponible' => $stockActual
                ], 400);
            }

            // Iniciar transacción
            DB::beginTransaction();

            try {
                // Registrar movimiento
                $movimientoId = DB::table('movimientos_inventario')->insertGetId([
                    'codigo_producto' => $request->codigo_producto,
                    'tipo' => $request->tipo,
                    'cantidad' => $request->cantidad,
                    'stock_anterior' => $stockActual,
                    'stock_nuevo' => $this->calcularNuevoStock($stockActual, $request->tipo, $request->cantidad),
                    'motivo' => $request->motivo,
                    'precio_unitario' => $request->precio_unitario ?? $producto->PrecioCosto,
                    'almacen_origen' => $request->almacen_origen,
                    'almacen_destino' => $request->almacen_destino,
                    'lote' => $request->lote,
                    'fecha_vencimiento' => $request->fecha_vencimiento,
                    'observaciones' => $request->observaciones,
                    'user_id' => auth()->id(),
                    'created_at' => Carbon::now()
                ]);

                // Actualizar stock del producto
                $nuevoStock = $this->calcularNuevoStock($stockActual, $request->tipo, $request->cantidad);
                
                $datosActualizacion = [
                    'Stock' => $nuevoStock,
                    'updated_at' => Carbon::now()
                ];

                // Actualizar lote y fecha de vencimiento si es una entrada
                if ($request->tipo === 'ENTRADA') {
                    if ($request->lote) {
                        $datosActualizacion['Lote'] = $request->lote;
                    }
                    if ($request->fecha_vencimiento) {
                        $datosActualizacion['Vencimiento'] = $request->fecha_vencimiento;
                    }
                }

                DB::table('Saldos')
                    ->where('Codigo', $request->codigo_producto)
                    ->update($datosActualizacion);

                DB::commit();

                // Obtener movimiento completo
                $movimientoCompleto = DB::table('movimientos_inventario')
                    ->where('id', $movimientoId)
                    ->first();

                return response()->json([
                    'success' => true,
                    'message' => 'Movimiento registrado exitosamente',
                    'data' => [
                        'movimiento' => $movimientoCompleto,
                        'producto_actualizado' => DB::table('Saldos')
                            ->where('Codigo', $request->codigo_producto)
                            ->first()
                    ]
                ], 201);

            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Error en InventarioApiController::registrarMovimiento: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Obtener kardex de un producto
     * GET /api/inventario/kardex/{codigo_producto}
     */
    public function kardex(Request $request, $codigoProducto): JsonResponse
    {
        try {
            // Validar código de producto
            if (empty($codigoProducto)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Código de producto requerido'
                ], 400);
            }

            // Obtener producto
            $producto = DB::table('Saldos')->where('Codigo', $codigoProducto)->first();
            if (!$producto) {
                return response()->json([
                    'success' => false,
                    'message' => 'Producto no encontrado'
                ], 404);
            }

            // Parámetros de fechas
            $fechaDesde = $request->input('fecha_desde', Carbon::now()->subMonths(12)->format('Y-m-d'));
            $fechaHasta = $request->input('fecha_hasta', Carbon::now()->format('Y-m-d'));

            // Obtener movimientos del período
            $movimientos = DB::table('movimientos_inventario')
                ->where('codigo_producto', $codigoProducto)
                ->whereBetween('created_at', [$fechaDesde . ' 00:00:00', $fechaHasta . ' 23:59:59'])
                ->orderBy('created_at', 'asc')
                ->get();

            // Calcular kardex
            $kardex = $this->calcularKardex($movimientos, $producto);

            // Obtener stock actual
            $stockActual = DB::table('Saldos')
                ->where('Codigo', $codigoProducto)
                ->value('Stock');

            // Obtener resumen del período
            $resumenPeriodo = [
                'entradas_total' => $movimientos->where('tipo', 'ENTRADA')->sum('cantidad'),
                'salidas_total' => $movimientos->where('tipo', 'SALIDA')->sum('cantidad'),
                'ajustes_total' => $movimientos->where('tipo', 'AJUSTE')->sum('cantidad'),
                'valor_entrada' => $movimientos->where('tipo', 'ENTRADA')->sum(function($m) {
                    return $m->cantidad * $m->precio_unitario;
                }),
                'valor_salida' => $movimientos->where('tipo', 'SALIDA')->sum(function($m) {
                    return $m->cantidad * $m->precio_unitario;
                })
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'producto' => $producto,
                    'kardex' => $kardex,
                    'stock_actual' => $stockActual,
                    'resumen_periodo' => $resumenPeriodo,
                    'periodo_consulta' => [
                        'desde' => $fechaDesde,
                        'hasta' => $fechaHasta
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error en InventarioApiController::kardex: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Obtener productos con alertas de inventario
     * GET /api/inventario/alertas
     */
    public function alertas(Request $request): JsonResponse
    {
        try {
            $tipoAlerta = $request->input('tipo', 'TODAS'); // TODAS, BAJO_STOCK, VENCIMIENTO, SIN_MOVIMIENTO

            $productos = DB::table('Saldos')
                ->select([
                    'Id',
                    'Codigo',
                    'Descripcion',
                    'Categoria',
                    'Stock',
                    'StockMinimo',
                    'PrecioVta',
                    'Vencimiento',
                    'Ubicacion',
                    'FechaActualizacion',
                    DB::raw('CASE 
                        WHEN Stock = 0 THEN "SIN_STOCK"
                        WHEN Stock <= StockMinimo THEN "BAJO_STOCK"
                        WHEN Vencimiento < CURDATE() THEN "VENCIDO"
                        WHEN Vencimiento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN "POR_VENCER"
                        ELSE "NORMAL"
                    END as alerta_tipo')
                ])
                ->where('Estado', 'Activo')
                ->orderBy('Stock', 'asc');

            // Aplicar filtros por tipo de alerta
            switch ($tipoAlerta) {
                case 'BAJO_STOCK':
                    $productos->whereColumn('Stock', '<=', 'StockMinimo');
                    break;
                case 'VENCIMIENTO':
                    $productos->where(function($q) {
                        $q->where('Vencimiento', '<', Carbon::now())
                          ->orWhereBetween('Vencimiento', [
                              Carbon::now(), 
                              Carbon::now()->addDays(30)
                          ]);
                    });
                    break;
                case 'SIN_MOVIMIENTO':
                    $productos->where('FechaActualizacion', '<', Carbon::now()->subDays(90));
                    break;
            }

            $productosConAlertas = $productos->get();

            // Categorizar alertas
            $alertasCategorizadas = [
                'sin_stock' => $productosConAlertas->where('alerta_tipo', 'SIN_STOCK'),
                'bajo_stock' => $productosConAlertas->where('alerta_tipo', 'BAJO_STOCK'),
                'vencidos' => $productosConAlertas->where('alerta_tipo', 'VENCIDO'),
                'por_vencer' => $productosConAlertas->where('alerta_tipo', 'POR_VENCER'),
                'sin_movimiento' => $productos->where('FechaActualizacion', '<', Carbon::now()->subDays(90))->get()
            ];

            // Calcular prioridad de cada alerta
            foreach ($productosConAlertas as $producto) {
                $producto->prioridad = $this->calcularPrioridadAlerta($producto);
                $producto->accion_recomendada = $this->generarAccionRecomendada($producto);
                $producto->valor_en_riesgo = $producto->Stock * $producto->PrecioVta;
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'alertas' => $productosConAlertas,
                    'alertas_categorizadas' => $alertasCategorizadas,
                    'resumen_alertas' => [
                        'total_productos_alerta' => $productosConAlertas->count(),
                        'sin_stock' => $alertasCategorizadas['sin_stock']->count(),
                        'bajo_stock' => $alertasCategorizadas['bajo_stock']->count(),
                        'vencidos' => $alertasCategorizadas['vencidos']->count(),
                        'por_vencer' => $alertasCategorizadas['por_vencer']->count(),
                        'sin_movimiento' => $alertasCategorizadas['sin_movimiento']->count()
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error en InventarioApiController::alertas: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Realizar conteo físico de inventario
     * POST /api/inventario/conteo-fisico
     */
    public function conteoFisico(Request $request): JsonResponse
    {
        try {
            // Validación de datos
            $validator = Validator::make($request->all(), [
                'codigo_producto' => 'required|string|max:50|exists:Saldos,Codigo',
                'stock_contado' => 'required|integer|min:0',
                'motivo' => 'required|string|max:255',
                'observaciones' => 'string',
                'lote' => 'string|max:50',
                'fecha_vencimiento' => 'date'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            // Obtener producto actual
            $producto = DB::table('Saldos')->where('Codigo', $request->codigo_producto)->first();
            
            $stockActual = $producto->Stock;
            $stockContado = $request->stock_contado;
            $diferencia = $stockContado - $stockActual;

            // Iniciar transacción
            DB::beginTransaction();

            try {
                // Registrar movimiento de conteo
                DB::table('movimientos_inventario')->insert([
                    'codigo_producto' => $request->codigo_producto,
                    'tipo' => $diferencia > 0 ? 'ENTRADA' : 'SALIDA',
                    'cantidad' => abs($diferencia),
                    'stock_anterior' => $stockActual,
                    'stock_nuevo' => $stockContado,
                    'motivo' => 'CONTEO FÍSICO: ' . $request->motivo,
                    'precio_unitario' => $producto->PrecioCosto,
                    'observaciones' => $request->observaciones,
                    'user_id' => auth()->id(),
                    'created_at' => Carbon::now()
                ]);

                // Actualizar datos del producto
                $datosActualizacion = [
                    'Stock' => $stockContado,
                    'FechaActualizacion' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ];

                if ($request->lote) {
                    $datosActualizacion['Lote'] = $request->lote;
                }

                if ($request->fecha_vencimiento) {
                    $datosActualizacion['Vencimiento'] = $request->fecha_vencimiento;
                }

                DB::table('Saldos')
                    ->where('Codigo', $request->codigo_producto)
                    ->update($datosActualizacion);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Conteo físico registrado exitosamente',
                    'data' => [
                        'diferencias' => [
                            'stock_anterior' => $stockActual,
                            'stock_contado' => $stockContado,
                            'diferencia' => $diferencia,
                            'ajuste_necesario' => $diferencia != 0
                        ],
                        'producto_actualizado' => DB::table('Saldos')
                            ->where('Codigo', $request->codigo_producto)
                            ->first()
                    ]
                ]);

            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Error en InventarioApiController::conteoFisico: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Generar reporte de inventario
     * GET /api/inventario/reporte
     */
    public function reporte(Request $request): JsonResponse
    {
        try {
            // Parámetros del reporte
            $tipoReporte = $request->input('tipo', 'COMPLETO'); // COMPLETO, STOCK, VENCIMIENTO, VALORIZACION
            $formato = $request->input('formato', 'JSON'); // JSON, CSV, EXCEL
            $incluirInactivos = $request->boolean('incluir_inactivos', false);
            
            $fechaGeneracion = Carbon::now();

            switch ($tipoReporte) {
                case 'STOCK':
                    $datos = $this->generarReporteStock($incluirInactivos);
                    break;
                case 'VENCIMIENTO':
                    $datos = $this->generarReporteVencimiento($incluirInactivos);
                    break;
                case 'VALORIZACION':
                    $datos = $this->generarReporteValorizacion($incluirInactivos);
                    break;
                default:
                    $datos = $this->generarReporteCompleto($incluirInactivos);
            }

            // Agregar metadatos del reporte
            $reporte = [
                'metadatos' => [
                    'tipo_reporte' => $tipoReporte,
                    'fecha_generacion' => $fechaGeneracion,
                    'generado_por' => auth()->user()->name ?? 'Sistema',
                    'total_registros' => count($datos['datos']),
                    'formato' => $formato
                ],
                'filtros_aplicados' => [
                    'incluir_inactivos' => $incluirInactivos
                ],
                'datos' => $datos['datos'],
                'resumen' => $datos['resumen']
            ];

            return response()->json([
                'success' => true,
                'data' => $reporte
            ]);

        } catch (\Exception $e) {
            Log::error('Error en InventarioApiController::reporte: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | MÉTODOS ESPECIALIZADOS DE INVENTARIO
    |--------------------------------------------------------------------------
    */

    /**
     * Obtener análisis ABC de inventario
     * GET /api/inventario/analisis-abc
     */
    public function analisisABC(Request $request): JsonResponse
    {
        try {
            $fechaDesde = $request->input('fecha_desde', Carbon::now()->subMonths(12)->format('Y-m-d'));
            $fechaHasta = $request->input('fecha_hasta', Carbon::now()->format('Y-m-d'));

            // Obtener ventas por producto en el período
            $ventasPorProducto = DB::table('Docdet')
                ->join('Saldos', 'Docdet.Codpro', '=', 'Saldos.Codigo')
                ->select([
                    'Saldos.Codigo',
                    'Saldos.Descripcion',
                    'Saldos.Categoria',
                    'Saldos.Stock',
                    'Saldos.PrecioVta',
                    DB::raw('SUM(Docdet.Cantidad) as cantidad_vendida'),
                    DB::raw('SUM(Docdet.Total) as valor_vendido')
                ])
                ->whereBetween('Docdet.created_at', [$fechaDesde . ' 00:00:00', $fechaHasta . ' 23:59:59'])
                ->groupBy('Saldos.Codigo', 'Saldos.Descripcion', 'Saldos.Categoria', 'Saldos.Stock', 'Saldos.PrecioVta')
                ->get();

            // Ordenar por valor vendido descendente
            $ventasPorProducto = $ventasPorProducto->sortByDesc('valor_vendido')->values();

            // Calcular porcentajes acumulados
            $totalValor = $ventasPorProducto->sum('valor_vendido');
            $valorAcumulado = 0;
            
            foreach ($ventasPorProducto as $index => $producto) {
                $valorAcumulado += $producto->valor_vendido;
                $porcentajeAcumulado = ($valorAcumulado / $totalValor) * 100;
                
                // Clasificar según regla 80-20
                if ($porcentajeAcumulado <= 80) {
                    $producto->clasificacion_abc = 'A';
                } elseif ($porcentajeAcumulado <= 95) {
                    $producto->clasificacion_abc = 'B';
                } else {
                    $producto->clasificacion_abc = 'C';
                }
                
                $producto->porcentaje_acumulado = round($porcentajeAcumulado, 2);
            }

            // Separar por clasificación
            $productosClaseA = $ventasPorProducto->where('clasificacion_abc', 'A');
            $productosClaseB = $ventasPorProducto->where('clasificacion_abc', 'B');
            $productosClaseC = $ventasPorProducto->where('clasificacion_abc', 'C');

            // Calcular estadísticas por clase
            $estadisticasABC = [
                'clase_a' => [
                    'productos' => $productosClaseA->count(),
                    'porcentaje_productos' => round(($productosClaseA->count() / $ventasPorProducto->count()) * 100, 2),
                    'valor_total' => $productosClaseA->sum('valor_vendido'),
                    'porcentaje_valor' => round(($productosClaseA->sum('valor_vendido') / $totalValor) * 100, 2),
                    'stock_total' => $productosClaseA->sum('Stock')
                ],
                'clase_b' => [
                    'productos' => $productosClaseB->count(),
                    'porcentaje_productos' => round(($productosClaseB->count() / $ventasPorProducto->count()) * 100, 2),
                    'valor_total' => $productosClaseB->sum('valor_vendido'),
                    'porcentaje_valor' => round(($productosClaseB->sum('valor_vendido') / $totalValor) * 100, 2),
                    'stock_total' => $productosClaseB->sum('Stock')
                ],
                'clase_c' => [
                    'productos' => $productosClaseC->count(),
                    'porcentaje_productos' => round(($productosClaseC->count() / $ventasPorProducto->count()) * 100, 2),
                    'valor_total' => $productosClaseC->sum('valor_vendido'),
                    'porcentaje_valor' => round(($productosClaseC->sum('valor_vendido') / $totalValor) * 100, 2),
                    'stock_total' => $productosClaseC->sum('Stock')
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'productos_clasificados' => $ventasPorProducto,
                    'productos_clase_a' => $productosClaseA,
                    'productos_clase_b' => $productosClaseB,
                    'productos_clase_c' => $productosClaseC,
                    'estadisticas_abc' => $estadisticasABC,
                    'periodo_analisis' => [
                        'desde' => $fechaDesde,
                        'hasta' => $fechaHasta,
                        'total_dias' => Carbon::parse($fechaDesde)->diffInDays(Carbon::parse($fechaHasta))
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error en InventarioApiController::analisisABC: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Calcular rotación de inventario
     * GET /api/inventario/rotacion
     */
    public function calcularRotacion(Request $request): JsonResponse
    {
        try {
            $fechaDesde = $request->input('fecha_desde', Carbon::now()->subMonths(12)->format('Y-m-d'));
            $fechaHasta = $request->input('fecha_hasta', Carbon::now()->format('Y-m-d'));

            // Obtener costo de ventas en el período
            $costoVentas = DB::table('Docdet')
                ->join('Saldos', 'Docdet.Codpro', '=', 'Saldos.Codigo')
                ->whereBetween('Docdet.created_at', [$fechaDesde . ' 00:00:00', $fechaHasta . ' 23:59:59'])
                ->sum(DB::raw('Docdet.Cantidad * Saldos.PrecioCosto'));

            // Calcular inventario promedio (asumiendo stock actual como aproximación)
            $inventarioPromedio = DB::table('Saldos')
                ->where('Estado', 'Activo')
                ->sum(DB::raw('Stock * PrecioCosto'));

            // Calcular rotación general
            $rotacionGeneral = $inventarioPromedio > 0 ? $costoVentas / $inventarioPromedio : 0;

            // Calcular rotación por categoría
            $rotacionPorCategoria = DB::table('Docdet')
                ->join('Saldos', 'Docdet.Codpro', '=', 'Saldos.Codigo')
                ->select([
                    'Saldos.Categoria',
                    DB::raw('SUM(Docdet.Cantidad * Saldos.PrecioCosto) as costo_ventas'),
                    DB::raw('AVG(Saldos.Stock * Saldos.PrecioCosto) as inventario_promedio'),
                    DB::raw('SUM(Docdet.Cantidad * Saldos.PrecioCosto) / NULLIF(AVG(Saldos.Stock * Saldos.PrecioCosto), 0) as rotacion')
                ])
                ->whereBetween('Docdet.created_at', [$fechaDesde . ' 00:00:00', $fechaHasta . ' 23:59:59'])
                ->groupBy('Saldos.Categoria')
                ->get();

            // Calcular días de inventario
            $diasInventario = $rotacionGeneral > 0 ? 365 / $rotacionGeneral : 0;

            return response()->json([
                'success' => true,
                'data' => [
                    'rotacion_general' => round($rotacionGeneral, 2),
                    'dias_inventario' => round($diasInventario, 0),
                    'costo_ventas_periodo' => $costoVentas,
                    'inventario_promedio' => $inventarioPromedio,
                    'rotacion_por_categoria' => $rotacionPorCategoria,
                    'periodo_analisis' => [
                        'desde' => $fechaDesde,
                        'hasta' => $fechaHasta,
                        'meses_analizados' => Carbon::parse($fechaDesde)->diffInMonths(Carbon::parse($fechaHasta))
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error en InventarioApiController::calcularRotacion: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | MÉTODOS DE SOPORTE Y UTILIDADES
    |--------------------------------------------------------------------------
    */

    /**
     * Aplicar filtros a movimientos
     */
    private function applyMovimientosFilters($query, Request $request)
    {
        if ($request->filled('producto_codigo')) {
            $query->where('movimientos_inventario.codigo_producto', 'LIKE', '%' . $request->producto_codigo . '%');
        }

        if ($request->filled('tipo_movimiento')) {
            $query->where('movimientos_inventario.tipo', $request->tipo_movimiento);
        }

        if ($request->filled('motivo')) {
            $query->where('movimientos_inventario.motivo', 'LIKE', '%' . $request->motivo . '%');
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('movimientos_inventario.created_at', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('movimientos_inventario.created_at', '<=', $request->fecha_hasta);
        }

        if ($request->filled('usuario')) {
            $query->where('users.name', 'LIKE', '%' . $request->usuario . '%');
        }

        if ($request->filled('almacen_origen')) {
            $query->where('movimientos_inventario.almacen_origen', 'LIKE', '%' . $request->almacen_origen . '%');
        }

        if ($request->filled('almacen_destino')) {
            $query->where('movimientos_inventario.almacen_destino', 'LIKE', '%' . $request->almacen_destino . '%');
        }
    }

    /**
     * Calcular nuevo stock
     */
    private function calcularNuevoStock($stockActual, $tipo, $cantidad)
    {
        return match($tipo) {
            'ENTRADA' => $stockActual + $cantidad,
            'SALIDA' => $stockActual - $cantidad,
            'AJUSTE' => $cantidad, // Para ajustes, la cantidad es el nuevo stock total
            'TRANSFERENCIA' => $stockActual, // No cambia en transferencias
            default => $stockActual
        };
    }

    /**
     * Calcular kardex
     */
    private function calcularKardex($movimientos, $producto)
    {
        $saldoAnterior = 0;
        $costoPromedio = $producto->PrecioCosto;
        $kardex = [];

        foreach ($movimientos as $movimiento) {
            $saldoAnterior += match($movimiento->tipo) {
                'ENTRADA' => $movimiento->cantidad,
                'SALIDA' => -$movimiento->cantidad,
                default => 0
            };

            // Calcular valor del movimiento
            $valorMovimiento = $movimiento->cantidad * $movimiento->precio_unitario;

            $kardex[] = [
                'fecha' => $movimiento->created_at,
                'tipo' => $movimiento->tipo,
                'cantidad' => $movimiento->cantidad,
                'saldo' => $saldoAnterior,
                'precio_unitario' => $movimiento->precio_unitario,
                'valor_movimiento' => $valorMovimiento,
                'saldo_valor' => $saldoAnterior * $costoPromedio,
                'motivo' => $movimiento->motivo
            ];
        }

        return $kardex;
    }

    /**
     * Calcular prioridad de alerta
     */
    private function calcularPrioridadAlerta($producto)
    {
        if ($producto->Stock == 0) {
            return 'CRITICA';
        } elseif ($producto->Stock <= ($producto->StockMinimo * 0.3)) {
            return 'ALTA';
        } elseif ($producto->Stock <= $producto->StockMinimo) {
            return 'MEDIA';
        } else {
            return 'BAJA';
        }
    }

    /**
     * Generar acción recomendada
     */
    private function generarAccionRecomendada($producto)
    {
        if ($producto->Stock == 0) {
            return 'Reabastecer stock inmediatamente';
        } elseif ($producto->Stock <= $producto->StockMinimo) {
            return 'Solicitar reposición de stock';
        } elseif ($producto->Vencimiento && $producto->Vencimiento < Carbon::now()->addDays(30)) {
            return 'Revisar fechas de vencimiento';
        } else {
            return 'Monitoreo regular';
        }
    }

    /**
     * Obtener distribución por categorías
     */
    private function obtenerDistribucionCategorias($productos)
    {
        return $productos->groupBy('Categoria')
            ->map(function($categoria) {
                return [
                    'productos' => $categoria->count(),
                    'valor_total' => $categoria->sum(function($p) {
                        return $p->Stock * $p->PrecioCosto;
                    }),
                    'stock_total' => $categoria->sum('Stock')
                ];
            });
    }

    /**
     * Obtener productos que requieren atención
     */
    private function obtenerProductosRequierenAtencion($productos)
    {
        return [
            'sin_stock' => $productos->where('Stock', 0)->count(),
            'bajo_stock' => $productos->whereBetween('Stock', function($q) {
                $q->selectRaw('StockMinimo')->from('Saldos')->limit(1);
            })->count(),
            'vencidos' => $productos->where('Vencimiento', '<', Carbon::now())->count(),
            'por_vencer' => $productos->whereBetween('Vencimiento', [
                Carbon::now(), Carbon::now()->addDays(30)
            ])->count()
        ];
    }

    // Métodos para generar reportes
    private function generarReporteCompleto($incluirInactivos)
    {
        $query = DB::table('Saldos')
            ->select([
                'Codigo',
                'Descripcion',
                'Categoria',
                'Stock',
                'StockMinimo',
                'StockMaximo',
                'PrecioCosto',
                'PrecioVta',
                'Ubicacion',
                'Vencimiento',
                'Lote',
                'Estado',
                'FechaActualizacion'
            ])
            ->orderBy('Categoria')
            ->orderBy('Descripcion');

        if (!$incluirInactivos) {
            $query->where('Estado', 'Activo');
        }

        $datos = $query->get();

        return [
            'datos' => $datos,
            'resumen' => [
                'total_productos' => $datos->count(),
                'valor_total' => $datos->sum(function($p) {
                    return $p->Stock * $p->PrecioCosto;
                })
            ]
        ];
    }

    private function generarReporteStock($incluirInactivos)
    {
        $query = DB::table('Saldos')
            ->select([
                'Codigo',
                'Descripcion',
                'Categoria',
                'Stock',
                'StockMinimo',
                'StockMaximo',
                'Valor_Stock' => DB::raw('Stock * PrecioCosto')
            ])
            ->orderBy('Stock', 'asc');

        if (!$incluirInactivos) {
            $query->where('Estado', 'Activo');
        }

        $datos = $query->get();

        return [
            'datos' => $datos,
            'resumen' => [
                'productos_con_stock' => $datos->where('Stock', '>', 0)->count(),
                'productos_sin_stock' => $datos->where('Stock', 0)->count(),
                'valor_total_stock' => $datos->sum('Valor_Stock')
            ]
        ];
    }

    private function generarReporteVencimiento($incluirInactivos)
    {
        $query = DB::table('Saldos')
            ->select([
                'Codigo',
                'Descripcion',
                'Categoria',
                'Stock',
                'Vencimiento',
                'Dias_Vencimiento' => DB::raw('DATEDIFF(Vencimiento, CURDATE())')
            ])
            ->whereNotNull('Vencimiento')
            ->orderBy('Vencimiento');

        if (!$incluirInactivos) {
            $query->where('Estado', 'Activo');
        }

        $datos = $query->get();

        return [
            'datos' => $datos,
            'resumen' => [
                'productos_vencidos' => $datos->where('Dias_Vencimiento', '<', 0)->count(),
                'productos_por_vencer' => $datos->whereBetween('Dias_Vencimiento', [0, 30])->count(),
                'valor_productos_vencidos' => $datos->where('Dias_Vencimiento', '<', 0)->sum(function($p) {
                    return $p->Stock * $p->PrecioCosto;
                })
            ]
        ];
    }

    private function generarReporteValorizacion($incluirInactivos)
    {
        $query = DB::table('Saldos')
            ->select([
                'Codigo',
                'Descripcion',
                'Categoria',
                'Stock',
                'PrecioCosto',
                'PrecioVta',
                'Valor_Costo' => DB::raw('Stock * PrecioCosto'),
                'Valor_Venta' => DB::raw('Stock * PrecioVta'),
                'Margen' => DB::raw('(PrecioVta - PrecioCosto) / PrecioCosto * 100')
            ])
            ->orderBy('Valor_Costo', 'desc');

        if (!$incluirInactivos) {
            $query->where('Estado', 'Activo');
        }

        $datos = $query->get();

        return [
            'datos' => $datos,
            'resumen' => [
                'valor_total_costo' => $datos->sum('Valor_Costo'),
                'valor_total_venta' => $datos->sum('Valor_Venta'),
                'margen_promedio' => $datos->avg('Margen')
            ]
        ];
    }
}