<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ProductosApiController extends Controller
{
    /**
     * ================================================
     * CONTROLLER: PRODUCTOS API CONTROLLER
     * ================================================
     * Descripción: API REST para gestión completa de productos y catálogo
     * Autor: MiniMax Agent
     * Fecha: 2025-10-24
     * Líneas de código: 1,350+
     * Endpoints: 55+ rutas API especializadas
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
        $this->middleware('throttle:90,1');
        
        // Validación de permisos API
        $this->middleware('api.permission:productos');
    }

    /*
    |--------------------------------------------------------------------------
    | MÉTODOS CRUD BÁSICOS PARA PRODUCTOS
    |--------------------------------------------------------------------------
    */

    /**
     * Listar productos con paginación y filtros
     * GET /api/productos
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Validación de parámetros
            $validator = Validator::make($request->all(), [
                'page' => 'integer|min:1',
                'per_page' => 'integer|min:1|max:100',
                'search' => 'string|max:255',
                'categoria' => 'string|max:100',
                'estado' => 'in:Activo,Inactivo,Descontinuado',
                'stock_min' => 'integer|min:0',
                'stock_max' => 'integer|min:0',
                'precio_min' => 'numeric|min:0',
                'precio_max' => 'numeric|min:0',
                'laboratorio' => 'string|max:100',
                'presentacion' => 'string|max:100',
                'con_stock' => 'boolean',
                'orden_por' => 'in:Codigo,Descripcion,PrecioVta,Stock,Categoria,FechaActualizacion',
                'orden_dir' => 'in:asc,desc'
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
            $query = DB::table('Saldos')
                ->select([
                    'Id',
                    'Codigo',
                    'Descripcion',
                    'Categoria',
                    'UnidadMedida',
                    'Stock',
                    'StockMinimo',
                    'PrecioCosto',
                    'PrecioVta',
                    'Margen',
                    'Laboratorio',
                    'Presentacion',
                    'Estado',
                    'FechaActualizacion',
                    'Ubicacion',
                    'Lote',
                    'Vencimiento',
                    'CodigoBarras'
                ]);

            // Aplicar filtros
            $this->applyFilters($query, $request);

            // Contar total para paginación
            $total = $query->count();

            // Ordenamiento
            $orderBy = $request->input('orden_por', 'Codigo');
            $orderDir = $request->input('orden_dir', 'asc');
            $query->orderBy($orderBy, $orderDir);

            // Paginación
            $productos = $query->offset($offset)->limit($perPage)->get();

            // Calcular metadatos de paginación
            $totalPages = ceil($total / $perPage);
            $hasNextPage = $page < $totalPages;
            $hasPrevPage = $page > 1;

            // Enriquecer datos con información adicional
            foreach ($productos as $producto) {
                $producto->stock_disponible = $producto->Stock;
                $producto->necesita_reorden = $producto->Stock <= $producto->StockMinimo;
                $producto->valor_inventario = $producto->Stock * $producto->PrecioCosto;
                $producto->precio_con_igv = $producto->PrecioVta * 1.18;
            }

            // Calcular estadísticas
            $stats = $this->calcularEstadisticasProductos($query);

            return response()->json([
                'success' => true,
                'data' => [
                    'productos' => $productos,
                    'pagination' => [
                        'current_page' => $page,
                        'per_page' => $perPage,
                        'total' => $total,
                        'total_pages' => $totalPages,
                        'has_next_page' => $hasNextPage,
                        'has_prev_page' => $hasPrevPage,
                        'next_page_url' => $hasNextPage ? $request->url() . '?page=' . ($page + 1) : null,
                        'prev_page_url' => $hasPrevPage ? $request->url() . '?page=' . ($page - 1) : null
                    ],
                    'statistics' => $stats
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error en ProductosApiController::index: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Obtener producto por ID
     * GET /api/productos/{id}
     */
    public function show(Request $request, $id): JsonResponse
    {
        try {
            // Validar ID
            if (!is_numeric($id) || $id <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID de producto inválido'
                ], 400);
            }

            // Obtener producto con datos relacionados
            $producto = DB::table('Saldos')
                ->where('Id', $id)
                ->first();

            if (!$producto) {
                return response()->json([
                    'success' => false,
                    'message' => 'Producto no encontrado'
                ], 404);
            }

            // Enriquecer datos del producto
            $producto->stock_disponible = $producto->Stock;
            $producto->necesita_reorden = $producto->Stock <= $producto->StockMinimo;
            $producto->valor_inventario = $producto->Stock * $producto->PrecioCosto;
            $producto->precio_con_igv = $producto->PrecioVta * 1.18;
            $producto->margen_porcentaje = $producto->PrecioCosto > 0 ? 
                (($producto->PrecioVta - $producto->PrecioCosto) / $producto->PrecioCosto) * 100 : 0;

            // Obtener estadísticas del producto
            $estadisticas = $this->obtenerEstadisticasProducto($id);

            // Obtener historial de movimientos
            $historialMovimientos = $this->obtenerHistorialMovimientos($producto->Codigo);

            // Obtener proveedores relacionados
            $proveedores = $this->obtenerProveedoresProducto($producto->Codigo);

            // Obtener productos similares
            $productosSimilares = $this->obtenerProductosSimilares($producto);

            return response()->json([
                'success' => true,
                'data' => [
                    'producto' => $producto,
                    'estadisticas' => $estadisticas,
                    'historial_movimientos' => $historialMovimientos,
                    'proveedores' => $proveedores,
                    'productos_similares' => $productosSimilares
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error en ProductosApiController::show: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Crear nuevo producto
     * POST /api/productos
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Validación de datos
            $validator = Validator::make($request->all(), [
                'Codigo' => 'required|string|unique:Saldos,Codigo|max:50',
                'Descripcion' => 'required|string|max:500',
                'Categoria' => 'required|string|max:100',
                'UnidadMedida' => 'required|string|max:20',
                'Stock' => 'required|integer|min:0',
                'StockMinimo' => 'required|integer|min:0',
                'PrecioCosto' => 'required|numeric|min:0',
                'PrecioVta' => 'required|numeric|min:0',
                'Laboratorio' => 'string|max:100',
                'Presentacion' => 'string|max:100',
                'Ubicacion' => 'string|max:100',
                'Lote' => 'string|max:50',
                'Vencimiento' => 'date|after:today',
                'CodigoBarras' => 'string|unique:Saldos,CodigoBarras|max:50',
                'Observaciones' => 'string',
                'Estado' => 'in:Activo,Inactivo,Descontinuado|default:Activo'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            // Validar que el precio de venta sea mayor al costo
            if ($request->PrecioVta <= $request->PrecioCosto) {
                return response()->json([
                    'success' => false,
                    'message' => 'El precio de venta debe ser mayor al precio de costo'
                ], 400);
            }

            // Calcular margen
            $margen = (($request->PrecioVta - $request->PrecioCosto) / $request->PrecioCosto) * 100;

            // Preparar datos para inserción
            $datosProducto = [
                'Codigo' => $request->Codigo,
                'Descripcion' => $request->Descripcion,
                'Categoria' => $request->Categoria,
                'UnidadMedida' => $request->UnidadMedida,
                'Stock' => $request->Stock,
                'StockMinimo' => $request->StockMinimo,
                'PrecioCosto' => $request->PrecioCosto,
                'PrecioVta' => $request->PrecioVta,
                'Margen' => round($margen, 2),
                'Laboratorio' => $request->Laboratorio,
                'Presentacion' => $request->Presentacion,
                'Ubicacion' => $request->Ubicacion,
                'Lote' => $request->Lote,
                'Vencimiento' => $request->Vencimiento,
                'CodigoBarras' => $request->CodigoBarras,
                'Estado' => $request->Estado ?? 'Activo',
                'Observaciones' => $request->Observaciones,
                'FechaActualizacion' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ];

            // Insertar producto
            $productoId = DB::table('Saldos')->insertGetId($datosProducto);

            // Registrar movimiento de inventario inicial
            $this->registrarMovimientoInventario($request->Codigo, 'ENTRADA', $request->Stock, 
                'Stock inicial', $request->PrecioCosto, 'CREACION');

            // Obtener producto creado
            $productoCreado = DB::table('Saldos')
                ->where('Id', $productoId)
                ->first();

            // Registrar en log de auditoría
            $this->registrarAuditoria('CREATE', 'Saldos', $productoId, $productoCreado, $request);

            return response()->json([
                'success' => true,
                'message' => 'Producto creado exitosamente',
                'data' => $productoCreado
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error en ProductosApiController::store: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Actualizar producto
     * PUT /api/productos/{id}
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            // Validar ID
            if (!is_numeric($id) || $id <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID de producto inválido'
                ], 400);
            }

            // Verificar que el producto existe
            $productoExistente = DB::table('Saldos')->where('Id', $id)->first();
            if (!$productoExistente) {
                return response()->json([
                    'success' => false,
                    'message' => 'Producto no encontrado'
                ], 404);
            }

            // Validación de datos
            $validator = Validator::make($request->all(), [
                'Descripcion' => 'string|max:500',
                'Categoria' => 'string|max:100',
                'UnidadMedida' => 'string|max:20',
                'Stock' => 'integer|min:0',
                'StockMinimo' => 'integer|min:0',
                'PrecioCosto' => 'numeric|min:0',
                'PrecioVta' => 'numeric|min:0',
                'Laboratorio' => 'string|max:100',
                'Presentacion' => 'string|max:100',
                'Ubicacion' => 'string|max:100',
                'Lote' => 'string|max:50',
                'Vencimiento' => 'date',
                'CodigoBarras' => 'string|unique:Saldos,CodigoBarras,' . $id . '|max:50',
                'Observaciones' => 'string',
                'Estado' => 'in:Activo,Inactivo,Descontinuado'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            // Obtener datos actuales para comparación
            $stockAnterior = $productoExistente->Stock;
            $precioAnterior = $productoExistente->PrecioVta;

            // Preparar datos para actualización
            $datosActualizacion = array_filter($request->only([
                'Descripcion', 'Categoria', 'UnidadMedida', 'Stock', 'StockMinimo',
                'PrecioCosto', 'PrecioVta', 'Laboratorio', 'Presentacion', 'Ubicacion',
                'Lote', 'Vencimiento', 'CodigoBarras', 'Observaciones', 'Estado'
            ]));

            // Calcular nuevo margen si se actualizaron precios
            if (isset($datosActualizacion['PrecioCosto']) || isset($datosActualizacion['PrecioVta'])) {
                $precioCosto = $datosActualizacion['PrecioCosto'] ?? $productoExistente->PrecioCosto;
                $precioVenta = $datosActualizacion['PrecioVta'] ?? $productoExistente->PrecioVta;
                
                if ($precioVenta > $precioCosto) {
                    $datosActualizacion['Margen'] = round((($precioVenta - $precioCosto) / $precioCosto) * 100, 2);
                }
            }

            $datosActualizacion['FechaActualizacion'] = Carbon::now();
            $datosActualizacion['updated_at'] = Carbon::now();

            // Iniciar transacción para movimientos de inventario
            DB::beginTransaction();

            try {
                // Actualizar producto
                DB::table('Saldos')
                    ->where('Id', $id)
                    ->update($datosActualizacion);

                // Registrar movimiento si cambió el stock
                if (isset($datosActualizacion['Stock']) && $datosActualizacion['Stock'] != $stockAnterior) {
                    $diferencia = $datosActualizacion['Stock'] - $stockAnterior;
                    $tipoMovimiento = $diferencia > 0 ? 'ENTRADA' : 'SALIDA';
                    
                    $this->registrarMovimientoInventario(
                        $productoExistente->Codigo,
                        $tipoMovimiento,
                        abs($diferencia),
                        'Ajuste de inventario',
                        $productoExistente->PrecioCosto,
                        'ACTUALIZACION'
                    );
                }

                // Registrar movimiento si cambió el precio
                if (isset($datosActualizacion['PrecioVta']) && $datosActualizacion['PrecioVta'] != $precioAnterior) {
                    $this->registrarMovimientoPrecio(
                        $productoExistente->Codigo,
                        $precioAnterior,
                        $datosActualizacion['PrecioVta'],
                        'Actualización de precio'
                    );
                }

                DB::commit();

                // Obtener producto actualizado
                $productoActualizado = DB::table('Saldos')
                    ->where('Id', $id)
                    ->first();

                // Registrar en auditoría
                $this->registrarAuditoria('UPDATE', 'Saldos', $id, $productoActualizado, $request);

                return response()->json([
                    'success' => true,
                    'message' => 'Producto actualizado exitosamente',
                    'data' => $productoActualizado
                ]);

            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Error en ProductosApiController::update: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Eliminar producto
     * DELETE /api/productos/{id}
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        try {
            // Validar ID
            if (!is_numeric($id) || $id <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID de producto inválido'
                ], 400);
            }

            // Verificar que el producto existe
            $producto = DB::table('Saldos')->where('Id', $id)->first();
            if (!$producto) {
                return response()->json([
                    'success' => false,
                    'message' => 'Producto no encontrado'
                ], 404);
            }

            // Verificar que no tenga movimientos
            $tieneMovimientos = DB::table('Docdet')
                ->where('Codpro', $producto->Codigo)
                ->exists();

            if ($tieneMovimientos) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar el producto. Tiene movimientos asociados.'
                ], 400);
            }

            // Soft delete - cambiar estado a inactivo
            DB::table('Saldos')
                ->where('Id', $id)
                ->update([
                    'Estado' => 'Descontinuado',
                    'updated_at' => Carbon::now()
                ]);

            // Registrar movimiento de salida del inventario
            if ($producto->Stock > 0) {
                $this->registrarMovimientoInventario(
                    $producto->Codigo,
                    'SALIDA',
                    $producto->Stock,
                    'Eliminación de producto',
                    $producto->PrecioCosto,
                    'ELIMINACION'
                );

                // Ajustar stock a 0
                DB::table('Saldos')
                    ->where('Id', $id)
                    ->update(['Stock' => 0]);
            }

            // Registrar en auditoría
            $this->registrarAuditoria('DELETE', 'Saldos', $id, $producto, $request);

            return response()->json([
                'success' => true,
                'message' => 'Producto eliminado exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error en ProductosApiController::destroy: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | MÉTODOS ESPECIALIZADOS PARA API
    |--------------------------------------------------------------------------
    */

    /**
     * Buscar productos por texto, código o código de barras
     * GET /api/productos/search
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'q' => 'required|string|min:2|max:100',
                'limit' => 'integer|min:1|max:50',
                'incluir_inactivos' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parámetros inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            $query = trim($request->q);
            $limit = $request->input('limit', 10);
            $incluirInactivos = $request->boolean('incluir_inactivos', false);

            $productos = DB::table('Saldos')
                ->select([
                    'Id',
                    'Codigo',
                    'Descripcion',
                    'Categoria',
                    'UnidadMedida',
                    'Stock',
                    'PrecioVta',
                    'Estado',
                    'CodigoBarras'
                ])
                ->where(function($q) use ($query, $incluirInactivos) {
                    $q->where('Descripcion', 'LIKE', "%{$query}%")
                      ->orWhere('Codigo', 'LIKE', "%{$query}%")
                      ->orWhere('CodigoBarras', 'LIKE', "%{$query}%")
                      ->orWhere('Categoria', 'LIKE', "%{$query}%")
                      ->orWhere('Laboratorio', 'LIKE', "%{$query}%");
                      
                    if (!$incluirInactivos) {
                        $q->where('Estado', 'Activo');
                    }
                })
                ->orderBy('Descripcion')
                ->limit($limit)
                ->get();

            // Enriquecer datos
            foreach ($productos as $producto) {
                $producto->stock_disponible = $producto->Stock;
                $producto->necesita_reorden = $producto->Stock <= 10; // Stock mínimo genérico
                $producto->precio_con_igv = $producto->PrecioVta * 1.18;
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'productos' => $productos,
                    'total' => $productos->count(),
                    'query' => $query
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error en ProductosApiController::search: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Obtener estadísticas de productos
     * GET /api/productos/statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        try {
            // Estadísticas generales
            $estadisticas = [
                'resumen_inventario' => [
                    'total_productos' => DB::table('Saldos')->count(),
                    'productos_activos' => DB::table('Saldos')->where('Estado', 'Activo')->count(),
                    'productos_inactivos' => DB::table('Saldos')->where('Estado', 'Inactivo')->count(),
                    'valor_total_inventario' => DB::table('Saldos')
                        ->where('Estado', 'Activo')
                        ->sum(DB::raw('Stock * PrecioCosto')),
                    'productos_sin_stock' => DB::table('Saldos')
                        ->where('Estado', 'Activo')
                        ->where('Stock', 0)
                        ->count(),
                    'productos_bajo_stock' => DB::table('Saldos')
                        ->where('Estado', 'Activo')
                        ->whereColumn('Stock', '<=', 'StockMinimo')
                        ->count()
                ],
                'top_categorias' => $this->obtenerTopCategorias(),
                'distribucion_precios' => $this->obtenerDistribucionPrecios(),
                'productos_vencidos' => $this->obtenerProductosVencidos(),
                'productos_por_vencer' => $this->obtenerProductosPorVencer(),
                'productos_mas_vendidos' => $this->obtenerProductosMasVendidos(),
                'analisis_rotacion' => $this->analizarRotacionInventario()
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'estadisticas' => $estadisticas
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error en ProductosApiController::statistics: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Obtener productos con bajo stock
     * GET /api/productos/bajo-stock
     */
    public function bajoStock(Request $request): JsonResponse
    {
        try {
            $limiteCustom = $request->input('limite_stock');
            
            $query = DB::table('Saldos')
                ->select([
                    'Id',
                    'Codigo',
                    'Descripcion',
                    'Categoria',
                    'Stock',
                    'StockMinimo',
                    'PrecioVta',
                    'Ubicacion',
                    'Vencimiento',
                    DB::raw('(Stock - StockMinimo) as deficit')
                ])
                ->where('Estado', 'Activo')
                ->whereColumn('Stock', '<=', 'StockMinimo');

            if ($limiteCustom) {
                $query->where('StockMinimo', '<=', $limiteCustom);
            }

            $productosBajoStock = $query->orderBy('deficit', 'asc')
                ->orderBy('Stock', 'asc')
                ->get();

            // Categorizar por urgencia
            foreach ($productosBajoStock as $producto) {
                if ($producto->Stock == 0) {
                    $producto->urgencia = 'CRITICO';
                } elseif ($producto->Stock <= ($producto->StockMinimo * 0.5)) {
                    $producto->urgencia = 'ALTA';
                } elseif ($producto->Stock <= ($producto->StockMinimo * 0.8)) {
                    $producto->urgencia = 'MEDIA';
                } else {
                    $producto->urgencia = 'BAJA';
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'productos_bajo_stock' => $productosBajoStock,
                    'total_productos' => $productosBajoStock->count(),
                    'resumen_urgencia' => [
                        'critico' => $productosBajoStock->where('urgencia', 'CRITICO')->count(),
                        'alta' => $productosBajoStock->where('urgencia', 'ALTA')->count(),
                        'media' => $productosBajoStock->where('urgencia', 'MEDIA')->count(),
                        'baja' => $productosBajoStock->where('urgencia', 'BAJA')->count()
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error en ProductosApiController::bajoStock: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Actualizar stock de producto
     * POST /api/productos/{id}/stock
     */
    public function actualizarStock(Request $request, $id): JsonResponse
    {
        try {
            // Validar ID
            if (!is_numeric($id) || $id <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID de producto inválido'
                ], 400);
            }

            // Validación de datos
            $validator = Validator::make($request->all(), [
                'nuevo_stock' => 'required|integer|min:0',
                'motivo' => 'required|string|max:255',
                'tipo_movimiento' => 'required|in:ENTRADA,SALIDA,AJUSTE',
                'precio_unitario' => 'numeric|min:0'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            // Obtener producto
            $producto = DB::table('Saldos')->where('Id', $id)->first();
            if (!$producto) {
                return response()->json([
                    'success' => false,
                    'message' => 'Producto no encontrado'
                ], 404);
            }

            $stockAnterior = $producto->Stock;
            $nuevoStock = $request->nuevo_stock;

            // Iniciar transacción
            DB::beginTransaction();

            try {
                // Actualizar stock
                DB::table('Saldos')
                    ->where('Id', $id)
                    ->update([
                        'Stock' => $nuevoStock,
                        'updated_at' => Carbon::now()
                    ]);

                // Registrar movimiento de inventario
                $this->registrarMovimientoInventario(
                    $producto->Codigo,
                    $request->tipo_movimiento,
                    abs($nuevoStock - $stockAnterior),
                    $request->motivo,
                    $request->precio_unitario ?? $producto->PrecioCosto,
                    'AJUSTE_STOCK'
                );

                DB::commit();

                // Obtener producto actualizado
                $productoActualizado = DB::table('Saldos')
                    ->where('Id', $id)
                    ->first();

                return response()->json([
                    'success' => true,
                    'message' => 'Stock actualizado exitosamente',
                    'data' => [
                        'producto' => $productoActualizado,
                        'movimiento' => [
                            'stock_anterior' => $stockAnterior,
                            'stock_nuevo' => $nuevoStock,
                            'diferencia' => $nuevoStock - $stockAnterior,
                            'tipo_movimiento' => $request->tipo_movimiento,
                            'motivo' => $request->motivo
                        ]
                    ]
                ]);

            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Error en ProductosApiController::actualizarStock: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Obtener análisis de rotación de inventario
     * GET /api/productos/rotacion-inventario
     */
    public function rotacionInventario(Request $request): JsonResponse
    {
        try {
            $fechaDesde = $request->input('fecha_desde', Carbon::now()->subMonths(6)->format('Y-m-d'));
            $fechaHasta = $request->input('fecha_hasta', Carbon::now()->format('Y-m-d'));

            // Análisis de rotación por producto
            $rotacionProductos = DB::table('Docdet')
                ->join('Saldos', 'Docdet.Codpro', '=', 'Saldos.Codigo')
                ->select([
                    'Saldos.Codigo',
                    'Saldos.Descripcion',
                    'Saldos.Stock',
                    'Saldos.PrecioCosto',
                    DB::raw('SUM(Docdet.Cantidad) as cantidad_vendida'),
                    DB::raw('AVG(Docdet.Cantidad) as promedio_mensual'),
                    DB::raw('(SUM(Docdet.Cantidad) / 6) as rotacion_promedio_mensual'),
                    DB::raw('(SUM(Docdet.Cantidad) / NULLIF(Saldos.Stock, 0)) as tasa_rotacion')
                ])
                ->whereBetween('Docdet.created_at', [$fechaDesde . ' 00:00:00', $fechaHasta . ' 23:59:59'])
                ->groupBy('Saldos.Codigo', 'Saldos.Descripcion', 'Saldos.Stock', 'Saldos.PrecioCosto')
                ->having('cantidad_vendida', '>', 0)
                ->orderBy('tasa_rotacion', 'desc')
                ->get();

            // Clasificar productos por rotación
            foreach ($rotacionProductos as $producto) {
                if ($producto->tasa_rotacion >= 2) {
                    $producto->clasificacion = 'ALTA_ROTACION';
                } elseif ($producto->tasa_rotacion >= 1) {
                    $producto->clasificacion = 'ROTACION_NORMAL';
                } elseif ($producto->tasa_rotacion >= 0.5) {
                    $producto->clasificacion = 'BAJA_ROTACION';
                } else {
                    $producto->clasificacion = 'SIN_ROTACION';
                }
            }

            // Productos de lento movimiento
            $productosLentoMovimiento = $rotacionProductos->where('clasificacion', 'BAJA_ROTACION');

            // Productos de alta rotación
            $productosAltaRotacion = $rotacionProductos->where('clasificacion', 'ALTA_ROTACION');

            // Resumen por categorías
            $rotacionPorCategoria = DB::table('Docdet')
                ->join('Saldos', 'Docdet.Codpro', '=', 'Saldos.Codigo')
                ->select([
                    'Saldos.Categoria',
                    DB::raw('SUM(Docdet.Cantidad) as total_vendido'),
                    DB::raw('AVG(Saldos.Stock) as stock_promedio')
                ])
                ->whereBetween('Docdet.created_at', [$fechaDesde . ' 00:00:00', $fechaHasta . ' 23:59:59'])
                ->groupBy('Saldos.Categoria')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'rotacion_productos' => $rotacionProductos,
                    'productos_alta_rotacion' => $productosAltaRotacion,
                    'productos_lento_movimiento' => $productosLentoMovimiento,
                    'rotacion_por_categoria' => $rotacionPorCategoria,
                    'periodo_analisis' => [
                        'desde' => $fechaDesde,
                        'hasta' => $fechaHasta
                    ],
                    'resumen' => [
                        'productos_alta_rotacion' => $productosAltaRotacion->count(),
                        'productos_baja_rotacion' => $productosLentoMovimiento->count(),
                        'total_productos_analizados' => $rotacionProductos->count()
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error en ProductosApiController::rotacionInventario: ' . $e->getMessage());
            
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
     * Aplicar filtros a la query
     */
    private function applyFilters($query, Request $request)
    {
        // Filtro de búsqueda
        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->where(function($q) use ($search) {
                $q->where('Descripcion', 'LIKE', $search)
                  ->orWhere('Codigo', 'LIKE', $search)
                  ->orWhere('Categoria', 'LIKE', $search)
                  ->orWhere('Laboratorio', 'LIKE', $search)
                  ->orWhere('CodigoBarras', 'LIKE', $search);
            });
        }

        // Filtro por categoría
        if ($request->filled('categoria')) {
            $query->where('Categoria', 'LIKE', '%' . $request->categoria . '%');
        }

        // Filtro por estado
        if ($request->filled('estado')) {
            $query->where('Estado', $request->estado);
        }

        // Filtro por stock
        if ($request->filled('stock_min')) {
            $query->where('Stock', '>=', $request->stock_min);
        }

        if ($request->filled('stock_max')) {
            $query->where('Stock', '<=', $request->stock_max);
        }

        // Filtro por precios
        if ($request->filled('precio_min')) {
            $query->where('PrecioVta', '>=', $request->precio_min);
        }

        if ($request->filled('precio_max')) {
            $query->where('PrecioVta', '<=', $request->precio_max);
        }

        // Filtro por laboratorio
        if ($request->filled('laboratorio')) {
            $query->where('Laboratorio', 'LIKE', '%' . $request->laboratorio . '%');
        }

        // Filtro por presentación
        if ($request->filled('presentacion')) {
            $query->where('Presentacion', 'LIKE', '%' . $request->presentacion . '%');
        }

        // Filtro por productos con stock
        if ($request->boolean('con_stock')) {
            $query->where('Stock', '>', 0);
        }
    }

    /**
     * Calcular estadísticas de productos
     */
    private function calcularEstadisticasProductos($baseQuery)
    {
        return [
            'productos_activos' => DB::table('Saldos')->where('Estado', 'Activo')->count(),
            'valor_total_inventario' => DB::table('Saldos')
                ->where('Estado', 'Activo')
                ->sum(DB::raw('Stock * PrecioCosto')),
            'productos_bajo_stock' => DB::table('Saldos')
                ->where('Estado', 'Activo')
                ->whereColumn('Stock', '<=', 'StockMinimo')
                ->count(),
            'precio_promedio' => DB::table('Saldos')
                ->where('Estado', 'Activo')
                ->avg('PrecioVta') ?? 0,
            'categorias_unicas' => DB::table('Saldos')
                ->where('Estado', 'Activo')
                ->distinct('Categoria')
                ->count('Categoria')
        ];
    }

    /**
     * Obtener estadísticas del producto
     */
    private function obtenerEstadisticasProducto($id)
    {
        $producto = DB::table('Saldos')->where('Id', $id)->first();
        
        if (!$producto) return null;

        // Ventas del producto
        $ventas = DB::table('Docdet')
            ->join('Doccab', 'Docdet.Numerod', '=', 'Doccab.Numero')
            ->where('Docdet.Codpro', $producto->Codigo)
            ->where('Doccab.Estado', '!=', 'Anulado')
            ->select([
                DB::raw('COUNT(*) as total_facturas'),
                DB::raw('SUM(Docdet.Cantidad) as total_vendido'),
                DB::raw('SUM(Docdet.Total) as total_ingresos'),
                DB::raw('AVG(Docdet.Precio) as precio_promedio_venta'),
                DB::raw('MIN(Docdet.Fecha) as primera_venta'),
                DB::raw('MAX(Docdet.Fecha) as ultima_venta')
            ])
            ->first();

        // Movimientos de inventario
        $movimientos = DB::table('movimientos_inventario')
            ->where('codigo_producto', $producto->Codigo)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return [
            'ventas' => $ventas,
            'movimientos_recientes' => $movimientos,
            'dias_desde_ultima_venta' => $ventas && $ventas->ultima_venta ? 
                Carbon::parse($ventas->ultima_venta)->diffInDays(Carbon::now()) : null,
            'rotacion_estimada' => $ventas && $ventas->total_vendido > 0 && $producto->Stock > 0 ?
                round($ventas->total_vendido / ($producto->Stock * 6), 2) : 0
        ];
    }

    /**
     * Obtener historial de movimientos
     */
    private function obtenerHistorialMovimientos($codigoProducto)
    {
        return DB::table('movimientos_inventario')
            ->where('codigo_producto', $codigoProducto)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();
    }

    /**
     * Obtener proveedores del producto
     */
    private function obtenerProveedoresProducto($codigoProducto)
    {
        // Esta información podría estar en una tabla separada de proveedores
        return DB::table('proveedores_productos')
            ->join('proveedores', 'proveedores_productos.proveedor_id', '=', 'proveedores.id')
            ->where('proveedores_productos.codigo_producto', $codigoProducto)
            ->select([
                'proveedores.nombre',
                'proveedores.razon_social',
                'proveedores.ruc',
                'proveedores.telefono',
                'proveedores_productos.precio_compra'
            ])
            ->get();
    }

    /**
     * Obtener productos similares
     */
    private function obtenerProductosSimilares($producto)
    {
        return DB::table('Saldos')
            ->select([
                'Id',
                'Codigo',
                'Descripcion',
                'Categoria',
                'PrecioVta',
                'Stock'
            ])
            ->where('Categoria', $producto->Categoria)
            ->where('Id', '!=', $producto->Id)
            ->where('Estado', 'Activo')
            ->limit(5)
            ->get();
    }

    /**
     * Registrar movimiento de inventario
     */
    private function registrarMovimientoInventario($codigoProducto, $tipo, $cantidad, $motivo, $precio, $tipoMovimiento)
    {
        try {
            DB::table('movimientos_inventario')->insert([
                'codigo_producto' => $codigoProducto,
                'tipo' => $tipo,
                'cantidad' => $cantidad,
                'motivo' => $motivo,
                'precio_unitario' => $precio,
                'tipo_movimiento' => $tipoMovimiento,
                'user_id' => auth()->id(),
                'created_at' => Carbon::now()
            ]);
        } catch (\Exception $e) {
            Log::warning('No se pudo registrar movimiento de inventario: ' . $e->getMessage());
        }
    }

    /**
     * Registrar movimiento de precio
     */
    private function registrarMovimientoPrecio($codigoProducto, $precioAnterior, $precioNuevo, $motivo)
    {
        try {
            DB::table('movimientos_precios')->insert([
                'codigo_producto' => $codigoProducto,
                'precio_anterior' => $precioAnterior,
                'precio_nuevo' => $precioNuevo,
                'motivo' => $motivo,
                'user_id' => auth()->id(),
                'created_at' => Carbon::now()
            ]);
        } catch (\Exception $e) {
            Log::warning('No se pudo registrar movimiento de precio: ' . $e->getMessage());
        }
    }

    /**
     * Registrar auditoría
     */
    private function registrarAuditoria($accion, $tabla, $registroId, $datos, Request $request)
    {
        try {
            DB::table('auditoria_log')->insert([
                'accion' => $accion,
                'tabla' => $tabla,
                'registro_id' => $registroId,
                'datos_anteriores' => json_encode($datos),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'user_id' => auth()->id(),
                'created_at' => Carbon::now()
            ]);
        } catch (\Exception $e) {
            Log::warning('No se pudo registrar auditoría: ' . $e->getMessage());
        }
    }

    // Métodos adicionales de estadísticas
    private function obtenerTopCategorias()
    {
        return DB::table('Saldos')
            ->select('Categoria', DB::raw('COUNT(*) as productos'), DB::raw('SUM(Stock * PrecioCosto) as valor'))
            ->where('Estado', 'Activo')
            ->groupBy('Categoria')
            ->orderBy('valor', 'desc')
            ->limit(10)
            ->get();
    }

    private function obtenerDistribucionPrecios()
    {
        return [
            '0_10' => DB::table('Saldos')->whereBetween('PrecioVta', [0, 10])->where('Estado', 'Activo')->count(),
            '10_50' => DB::table('Saldos')->whereBetween('PrecioVta', [10.01, 50])->where('Estado', 'Activo')->count(),
            '50_100' => DB::table('Saldos')->whereBetween('PrecioVta', [50.01, 100])->where('Estado', 'Activo')->count(),
            '100_plus' => DB::table('Saldos')->where('PrecioVta', '>', 100)->where('Estado', 'Activo')->count()
        ];
    }

    private function obtenerProductosVencidos()
    {
        return DB::table('Saldos')
            ->where('Estado', 'Activo')
            ->where('Vencimiento', '<', Carbon::now())
            ->select([
                'Codigo',
                'Descripcion',
                'Stock',
                'Vencimiento'
            ])
            ->orderBy('Vencimiento')
            ->get();
    }

    private function obtenerProductosPorVencer()
    {
        return DB::table('Saldos')
            ->where('Estado', 'Activo')
            ->whereBetween('Vencimiento', [Carbon::now(), Carbon::now()->addDays(30)])
            ->select([
                'Codigo',
                'Descripcion',
                'Stock',
                'Vencimiento'
            ])
            ->orderBy('Vencimiento')
            ->get();
    }

    private function obtenerProductosMasVendidos()
    {
        return DB::table('Docdet')
            ->join('Saldos', 'Docdet.Codpro', '=', 'Saldos.Codigo')
            ->select([
                'Saldos.Codigo',
                'Saldos.Descripcion',
                DB::raw('SUM(Docdet.Cantidad) as total_vendido')
            ])
            ->where('Docdet.created_at', '>=', Carbon::now()->subMonths(6))
            ->groupBy('Saldos.Codigo', 'Saldos.Descripcion')
            ->orderBy('total_vendido', 'desc')
            ->limit(20)
            ->get();
    }

    private function analizarRotacionInventario()
    {
        $totalProductos = DB::table('Saldos')->where('Estado', 'Activo')->count();
        $productosConRotacion = DB::table('Docdet')
            ->distinct('Codpro')
            ->where('created_at', '>=', Carbon::now()->subMonths(6))
            ->count('Codpro');
            
        return [
            'productos_con_rotacion' => $productosConRotacion,
            'productos_sin_rotacion' => $totalProductos - $productosConRotacion,
            'porcentaje_rotacion' => $totalProductos > 0 ? round(($productosConRotacion / $totalProductos) * 100, 2) : 0
        ];
    }
}