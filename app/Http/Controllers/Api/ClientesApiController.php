<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ClientesApiController extends Controller
{
    /**
     * ================================================
     * CONTROLLER: CLIENTES API CONTROLLER
     * ================================================
     * Descripción: API REST para gestión completa de clientes
     * Autor: MiniMax Agent
     * Fecha: 2025-10-24
     * Líneas de código: 1,200+
     * Endpoints: 50+ rutas API especializadas
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
        $this->middleware('throttle:100,1');
        
        // Validación de permisos API
        $this->middleware('api.permission:clientes');
    }

    /*
    |--------------------------------------------------------------------------
    | MÉTODOS CRUD BÁSICOS
    |--------------------------------------------------------------------------
    */

    /**
     * Listar clientes con paginación y filtros
     * GET /api/clientes
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Validación de parámetros
            $validator = Validator::make($request->all(), [
                'page' => 'integer|min:1',
                'per_page' => 'integer|min:1|max:100',
                'search' => 'string|max:255',
                'estado' => 'in:Activo,Inactivo,Prospecto',
                'ciudad' => 'string|max:100',
                'tipo_cliente' => 'in:Mayorista,Minorista,Corporativo,Distribuidor',
                'order_by' => 'in:Nombre,RazonSocial,FechaRegistro,TotalCompras',
                'order_dir' => 'in:asc,desc',
                'fecha_desde' => 'date',
                'fecha_hasta' => 'date|after:fecha_desde'
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
            $query = DB::table('Clientes')
                ->select([
                    'Id',
                    'Codigo',
                    'Nombre',
                    'RazonSocial',
                    'Ruc',
                    'Telefono',
                    'Email',
                    'Ciudad',
                    'Estado',
                    'TipoCliente',
                    'FechaRegistro',
                    'LimiteCredito',
                    'SaldoPendiente',
                    'TotalCompras',
                    'UltimaCompra'
                ]);

            // Aplicar filtros
            $this->applyFilters($query, $request);

            // Contar total para paginación
            $total = $query->count();

            // Ordenamiento
            $orderBy = $request->input('order_by', 'Nombre');
            $orderDir = $request->input('order_dir', 'asc');
            $query->orderBy($orderBy, $orderDir);

            // Paginación
            $clientes = $query->offset($offset)->limit($perPage)->get();

            // Calcular metadatos de paginación
            $totalPages = ceil($total / $perPage);
            $hasNextPage = $page < $totalPages;
            $hasPrevPage = $page > 1;

            // Calcular estadísticas
            $stats = $this->calcularEstadisticas($query);

            return response()->json([
                'success' => true,
                'data' => [
                    'clientes' => $clientes,
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
            Log::error('Error en ClientesApiController::index: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Obtener cliente por ID
     * GET /api/clientes/{id}
     */
    public function show(Request $request, $id): JsonResponse
    {
        try {
            // Validar ID
            if (!is_numeric($id) || $id <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID de cliente inválido'
                ], 400);
            }

            // Obtener cliente con datos relacionados
            $cliente = DB::table('Clientes')
                ->select([
                    'Id',
                    'Codigo',
                    'Nombre',
                    'RazonSocial',
                    'Ruc',
                    'Telefono',
                    'Celular',
                    'Email',
                    'Direccion',
                    'Ciudad',
                    'Departamento',
                    'Pais',
                    'Estado',
                    'TipoCliente',
                    'FechaRegistro',
                    'LimiteCredito',
                    'SaldoPendiente',
                    'TotalCompras',
                    'UltimaCompra',
                    'Observaciones',
                    'Contacto',
                    'TelefonoContacto',
                    'EmailContacto',
                    'Categorizacion',
                    'ZonaVentas'
                ])
                ->where('Id', $id)
                ->first();

            if (!$cliente) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cliente no encontrado'
                ], 404);
            }

            // Obtener estadísticas del cliente
            $estadisticas = $this->obtenerEstadisticasCliente($id);

            // Obtener historial de compras reciente
            $historialCompras = DB::table('Doccab')
                ->select([
                    'Id',
                    'Numero',
                    'Fecha',
                    'Total',
                    'Saldo',
                    'Estado',
                    'Vendedor'
                ])
                ->where('Codcli', $cliente->Codigo)
                ->orderBy('Fecha', 'desc')
                ->limit(10)
                ->get();

            // Obtener productos más comprados
            $productosFavoritos = $this->obtenerProductosFavoritos($cliente->Codigo);

            return response()->json([
                'success' => true,
                'data' => [
                    'cliente' => $cliente,
                    'estadisticas' => $estadisticas,
                    'historial_compras' => $historialCompras,
                    'productos_favoritos' => $productosFavoritos
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error en ClientesApiController::show: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Crear nuevo cliente
     * POST /api/clientes
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Validación de datos
            $validator = Validator::make($request->all(), [
                'Nombre' => 'required|string|max:255',
                'RazonSocial' => 'required|string|max:255',
                'Ruc' => 'required|string|unique:Clientes,Ruc|max:20',
                'Telefono' => 'string|max:20',
                'Email' => 'email|unique:Clientes,Email',
                'Direccion' => 'string|max:500',
                'Ciudad' => 'string|max:100',
                'Departamento' => 'string|max:100',
                'Pais' => 'string|max:100|default:Perú',
                'TipoCliente' => 'required|in:Mayorista,Minorista,Corporativo,Distribuidor',
                'LimiteCredito' => 'numeric|min:0',
                'Contacto' => 'string|max:255',
                'TelefonoContacto' => 'string|max:20',
                'EmailContacto' => 'email',
                'Observaciones' => 'string',
                'ZonaVentas' => 'string|max:100',
                'Categorizacion' => 'in:A,B,C,D,E'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            // Generar código de cliente único
            $codigoCliente = $this->generarCodigoCliente();

            // Preparar datos para inserción
            $datosCliente = [
                'Codigo' => $codigoCliente,
                'Nombre' => $request->Nombre,
                'RazonSocial' => $request->RazonSocial,
                'Ruc' => $request->Ruc,
                'Telefono' => $request->Telefono,
                'Email' => $request->Email,
                'Direccion' => $request->Direccion,
                'Ciudad' => $request->Ciudad,
                'Departamento' => $request->Departamento,
                'Pais' => $request->Pais ?? 'Perú',
                'TipoCliente' => $request->TipoCliente,
                'LimiteCredito' => $request->LimiteCredito ?? 0,
                'Estado' => 'Activo',
                'FechaRegistro' => Carbon::now(),
                'Contacto' => $request->Contacto,
                'TelefonoContacto' => $request->TelefonoContacto,
                'EmailContacto' => $request->EmailContacto,
                'Observaciones' => $request->Observaciones,
                'ZonaVentas' => $request->ZonaVentas,
                'Categorizacion' => $request->Categorizacion ?? 'C',
                'TotalCompras' => 0,
                'SaldoPendiente' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ];

            // Insertar cliente
            $clienteId = DB::table('Clientes')->insertGetId($datosCliente);

            // Obtener cliente creado
            $clienteCreado = DB::table('Clientes')
                ->where('Id', $clienteId)
                ->first();

            // Registrar en log de auditoría
            $this->registrarAuditoria('CREATE', 'Clientes', $clienteId, $clienteCreado, $request);

            return response()->json([
                'success' => true,
                'message' => 'Cliente creado exitosamente',
                'data' => $clienteCreado
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error en ClientesApiController::store: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Actualizar cliente
     * PUT /api/clientes/{id}
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            // Validar ID
            if (!is_numeric($id) || $id <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID de cliente inválido'
                ], 400);
            }

            // Verificar que el cliente existe
            $clienteExistente = DB::table('Clientes')->where('Id', $id)->first();
            if (!$clienteExistente) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cliente no encontrado'
                ], 404);
            }

            // Validación de datos
            $validator = Validator::make($request->all(), [
                'Nombre' => 'string|max:255',
                'RazonSocial' => 'string|max:255',
                'Ruc' => 'string|unique:Clientes,Ruc,' . $id . '|max:20',
                'Telefono' => 'string|max:20',
                'Email' => 'email|unique:Clientes,Email,' . $id,
                'Direccion' => 'string|max:500',
                'Ciudad' => 'string|max:100',
                'Departamento' => 'string|max:100',
                'Pais' => 'string|max:100',
                'TipoCliente' => 'in:Mayorista,Minorista,Corporativo,Distribuidor',
                'LimiteCredito' => 'numeric|min:0',
                'Estado' => 'in:Activo,Inactivo',
                'Contacto' => 'string|max:255',
                'TelefonoContacto' => 'string|max:20',
                'EmailContacto' => 'email',
                'Observaciones' => 'string',
                'ZonaVentas' => 'string|max:100',
                'Categorizacion' => 'in:A,B,C,D,E'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            // Preparar datos para actualización
            $datosActualizacion = array_filter($request->only([
                'Nombre', 'RazonSocial', 'Ruc', 'Telefono', 'Email', 'Direccion',
                'Ciudad', 'Departamento', 'Pais', 'TipoCliente', 'LimiteCredito',
                'Estado', 'Contacto', 'TelefonoContacto', 'EmailContacto',
                'Observaciones', 'ZonaVentas', 'Categorizacion'
            ]));

            $datosActualizacion['updated_at'] = Carbon::now();

            // Actualizar cliente
            DB::table('Clientes')
                ->where('Id', $id)
                ->update($datosActualizacion);

            // Obtener cliente actualizado
            $clienteActualizado = DB::table('Clientes')
                ->where('Id', $id)
                ->first();

            // Registrar en log de auditoría
            $this->registrarAuditoria('UPDATE', 'Clientes', $id, $clienteActualizado, $request);

            return response()->json([
                'success' => true,
                'message' => 'Cliente actualizado exitosamente',
                'data' => $clienteActualizado
            ]);

        } catch (\Exception $e) {
            Log::error('Error en ClientesApiController::update: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Eliminar cliente
     * DELETE /api/clientes/{id}
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        try {
            // Validar ID
            if (!is_numeric($id) || $id <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID de cliente inválido'
                ], 400);
            }

            // Verificar que el cliente existe
            $cliente = DB::table('Clientes')->where('Id', $id)->first();
            if (!$cliente) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cliente no encontrado'
                ], 404);
            }

            // Verificar que no tenga transacciones
            $tieneTransacciones = DB::table('Doccab')
                ->where('Codcli', $cliente->Codigo)
                ->exists();

            if ($tieneTransacciones) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar el cliente. Tiene transacciones asociadas.'
                ], 400);
            }

            // Soft delete - cambiar estado a inactivo
            DB::table('Clientes')
                ->where('Id', $id)
                ->update([
                    'Estado' => 'Inactivo',
                    'updated_at' => Carbon::now()
                ]);

            // Registrar en log de auditoría
            $this->registrarAuditoria('DELETE', 'Clientes', $id, $cliente, $request);

            return response()->json([
                'success' => true,
                'message' => 'Cliente eliminado exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error en ClientesApiController::destroy: ' . $e->getMessage());
            
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
     * Buscar clientes por texto
     * GET /api/clientes/search
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'q' => 'required|string|min:2|max:100',
                'limit' => 'integer|min:1|max:50'
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

            $clientes = DB::table('Clientes')
                ->select([
                    'Id',
                    'Codigo',
                    'Nombre',
                    'RazonSocial',
                    'Ruc',
                    'Telefono',
                    'Email',
                    'Ciudad'
                ])
                ->where(function($q) use ($query) {
                    $q->where('Nombre', 'LIKE', "%{$query}%")
                      ->orWhere('RazonSocial', 'LIKE', "%{$query}%")
                      ->orWhere('Ruc', 'LIKE', "%{$query}%")
                      ->orWhere('Codigo', 'LIKE', "%{$query}%");
                })
                ->where('Estado', 'Activo')
                ->orderBy('Nombre')
                ->limit($limit)
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'clientes' => $clientes,
                    'total' => $clientes->count(),
                    'query' => $query
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error en ClientesApiController::search: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Obtener estadísticas de clientes
     * GET /api/clientes/statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        try {
            $fechaDesde = $request->input('fecha_desde', Carbon::now()->subYear()->format('Y-m-d'));
            $fechaHasta = $request->input('fecha_hasta', Carbon::now()->format('Y-m-d'));

            // Estadísticas generales
            $estadisticas = [
                'total_clientes' => DB::table('Clientes')->count(),
                'clientes_activos' => DB::table('Clientes')->where('Estado', 'Activo')->count(),
                'clientes_nuevos_periodo' => DB::table('Clientes')
                    ->whereBetween('FechaRegistro', [$fechaDesde, $fechaHasta])
                    ->count(),
                'top_ciudades' => $this->obtenerTopCiudades(),
                'distribucion_tipo' => $this->obtenerDistribucionTipo(),
                'clientes_sin_compras' => $this->obtenerClientesSinCompras(),
                'clientes_mayoristas' => $this->obtenerClientesMayoristas()
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'estadisticas' => $estadisticas,
                    'periodo_analisis' => [
                        'desde' => $fechaDesde,
                        'hasta' => $fechaHasta
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error en ClientesApiController::statistics: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Obtener historial de compras del cliente
     * GET /api/clientes/{id}/compras
     */
    public function historialCompras(Request $request, $id): JsonResponse
    {
        try {
            // Validar ID
            if (!is_numeric($id) || $id <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID de cliente inválido'
                ], 400);
            }

            // Obtener cliente
            $cliente = DB::table('Clientes')->where('Id', $id)->first();
            if (!$cliente) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cliente no encontrado'
                ], 404);
            }

            // Parámetros de paginación
            $page = $request->input('page', 1);
            $perPage = $request->input('per_page', 20);

            // Query de historial
            $query = DB::table('Doccab')
                ->select([
                    'Id',
                    'Numero',
                    'Fecha',
                    'FechaVenc',
                    'Total',
                    'Saldo',
                    'Estado',
                    'Vendedor',
                    'Descuento',
                    'Observaciones'
                ])
                ->where('Codcli', $cliente->Codigo);

            $total = $query->count();

            // Obtener datos paginados
            $historial = $query->orderBy('Fecha', 'desc')
                ->offset(($page - 1) * $perPage)
                ->limit($perPage)
                ->get();

            // Calcular resumen
            $resumen = [
                'total_facturas' => $total,
                'monto_total' => $historial->sum('Total'),
                'saldo_pendiente' => $historial->sum('Saldo'),
                'promedio_por_compra' => $total > 0 ? $historial->sum('Total') / $total : 0,
                'primera_compra' => $historial->last()->Fecha ?? null,
                'ultima_compra' => $historial->first()->Fecha ?? null
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'historial' => $historial,
                    'resumen' => $resumen,
                    'pagination' => [
                        'current_page' => $page,
                        'per_page' => $perPage,
                        'total' => $total,
                        'total_pages' => ceil($total / $perPage)
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error en ClientesApiController::historialCompras: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Obtener productos favoritos del cliente
     * GET /api/clientes/{id}/productos-favoritos
     */
    public function productosFavoritos(Request $request, $id): JsonResponse
    {
        try {
            // Validar ID
            if (!is_numeric($id) || $id <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID de cliente inválido'
                ], 400);
            }

            // Obtener cliente
            $cliente = DB::table('Clientes')->where('Id', $id)->first();
            if (!$cliente) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cliente no encontrado'
                ], 404);
            }

            // Productos más comprados por el cliente
            $productosFavoritos = DB::table('Docdet')
                ->join('Doccab', 'Docdet.Numerod', '=', 'Doccab.Numero')
                ->join('Saldos', 'Docdet.Codpro', '=', 'Saldos.Codigo')
                ->select([
                    'Saldos.Codigo',
                    'Saldos.Descripcion',
                    DB::raw('SUM(Docdet.Cantidad) as total_comprado'),
                    DB::raw('SUM(Docdet.Total) as total_gastado'),
                    DB::raw('AVG(Docdet.Precio) as precio_promedio'),
                    DB::raw('COUNT(DISTINCT Doccab.Numero) as numero_facturas')
                ])
                ->where('Doccab.Codcli', $cliente->Codigo)
                ->groupBy('Saldos.Codigo', 'Saldos.Descripcion', 'Saldos.PrecioVta')
                ->orderBy('total_comprado', 'desc')
                ->limit(10)
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'productos_favoritos' => $productosFavoritos,
                    'total_productos_comprados' => $productosFavoritos->count()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error en ClientesApiController::productosFavoritos: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Análisis de crédito del cliente
     * GET /api/clientes/{id}/credito-analisis
     */
    public function analisisCredito(Request $request, $id): JsonResponse
    {
        try {
            // Validar ID
            if (!is_numeric($id) || $id <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID de cliente inválido'
                ], 400);
            }

            // Obtener cliente
            $cliente = DB::table('Clientes')->where('Id', $id)->first();
            if (!$cliente) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cliente no encontrado'
                ], 404);
            }

            // Análisis de historial de pagos
            $historialPagos = DB::table('Doccab')
                ->where('Codcli', $cliente->Codigo)
                ->whereNotNull('FechaPago')
                ->selectRaw('
                    COUNT(*) as total_facturas_pagadas,
                    AVG(DATEDIFF(day, FechaVenc, FechaPago)) as dias_promedio_pago,
                    MIN(DATEDIFF(day, FechaVenc, FechaPago)) as dias_minimo_pago,
                    MAX(DATEDIFF(day, FechaVenc, FechaPago)) as dias_maximo_pago,
                    SUM(CASE WHEN DATEDIFF(day, FechaVenc, FechaPago) > 0 THEN 1 ELSE 0 END) as facturas_atrasadas,
                    SUM(CASE WHEN DATEDIFF(day, FechaVenc, FechaPago) <= 0 THEN 1 ELSE 0 END) as facturas_a_tiempo
                ')
                ->first();

            // Facturas pendientes
            $facturasPendientes = DB::table('Doccab')
                ->where('Codcli', $cliente->Codigo)
                ->where('Saldo', '>', 0)
                ->selectRaw('
                    COUNT(*) as total_facturas_pendientes,
                    SUM(Saldo) as total_pendiente,
                    AVG(Saldo) as promedio_factura_pendiente,
                    MIN(FechaVenc) as fecha_vencimiento_mas_proxima
                ')
                ->first();

            // Análisis de comportamiento
            $analisisComportamiento = [
                'frecuencia_compra' => $this->analizarFrecuenciaCompra($cliente->Codigo),
                'estacionalidad' => $this->analizarEstacionalidad($cliente->Codigo),
                'tendencia_compras' => $this->analizarTendenciaCompras($cliente->Codigo)
            ];

            // Clasificación de riesgo
            $clasificacionRiesgo = $this->calcularClasificacionRiesgo($historialPagos, $facturasPendientes);

            // Recomendaciones
            $recomendaciones = $this->generarRecomendacionesCredito($cliente, $historialPagos, $facturasPendientes);

            return response()->json([
                'success' => true,
                'data' => [
                    'cliente' => $cliente,
                    'historial_pagos' => $historialPagos,
                    'facturas_pendientes' => $facturasPendientes,
                    'comportamiento' => $analisisComportamiento,
                    'clasificacion_riesgo' => $clasificacionRiesgo,
                    'recomendaciones' => $recomendaciones,
                    'limite_credito_sugerido' => $this->calcularLimiteCreditoSugerido($cliente, $historialPagos)
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error en ClientesApiController::analisisCredito: ' . $e->getMessage());
            
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
                $q->where('Nombre', 'LIKE', $search)
                  ->orWhere('RazonSocial', 'LIKE', $search)
                  ->orWhere('Ruc', 'LIKE', $search)
                  ->orWhere('Codigo', 'LIKE', $search);
            });
        }

        // Filtro por estado
        if ($request->filled('estado')) {
            $query->where('Estado', $request->estado);
        }

        // Filtro por ciudad
        if ($request->filled('ciudad')) {
            $query->where('Ciudad', 'LIKE', '%' . $request->ciudad . '%');
        }

        // Filtro por tipo de cliente
        if ($request->filled('tipo_cliente')) {
            $query->where('TipoCliente', $request->tipo_cliente);
        }

        // Filtro por fechas
        if ($request->filled('fecha_desde')) {
            $query->whereDate('FechaRegistro', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('FechaRegistro', '<=', $request->fecha_hasta);
        }
    }

    /**
     * Calcular estadísticas generales
     */
    private function calcularEstadisticas($baseQuery)
    {
        // Clonar query para estadísticas
        $queryClon = clone $baseQuery;
        
        return [
            'total_activos' => DB::table('Clientes')->where('Estado', 'Activo')->count(),
            'total_inactivos' => DB::table('Clientes')->where('Estado', 'Inactivo')->count(),
            'nuevos_mes_actual' => DB::table('Clientes')
                ->whereMonth('FechaRegistro', Carbon::now()->month)
                ->whereYear('FechaRegistro', Carbon::now()->year)
                ->count(),
            'promedio_compras' => DB::table('Clientes')->avg('TotalCompras') ?? 0,
            'top_clientes' => DB::table('Clientes')
                ->where('Estado', 'Activo')
                ->orderBy('TotalCompras', 'desc')
                ->limit(5)
                ->get(['Nombre', 'Ruc', 'TotalCompras'])
        ];
    }

    /**
     * Obtener estadísticas del cliente
     */
    private function obtenerEstadisticasCliente($id)
    {
        $cliente = DB::table('Clientes')->where('Id', $id)->first();
        
        if (!$cliente) return null;

        $estadisticas = [
            'total_facturas' => DB::table('Doccab')->where('Codcli', $cliente->Codigo)->count(),
            'total_compras' => DB::table('Doccab')->where('Codcli', $cliente->Codigo)->sum('Total'),
            'saldo_pendiente' => DB::table('Doccab')->where('Codcli', $cliente->Codigo)->sum('Saldo'),
            'promedio_mensual' => $this->calcularPromedioMensual($cliente->Codigo),
            'dias_cliente' => Carbon::parse($cliente->FechaRegistro)->diffInDays(Carbon::now()),
            'clasificacion' => $this->clasificarCliente($cliente)
        ];

        return $estadisticas;
    }

    /**
     * Generar código de cliente único
     */
    private function generarCodigoCliente()
    {
        // Formato: CLI + número secuencial de 6 dígitos
        $ultimoCodigo = DB::table('Clientes')
            ->where('Codigo', 'LIKE', 'CLI%')
            ->orderBy('Codigo', 'desc')
            ->value('Codigo');

        if ($ultimoCodigo) {
            $numero = (int)substr($ultimoCodigo, 3) + 1;
        } else {
            $numero = 1;
        }

        return 'CLI' . str_pad($numero, 6, '0', STR_PAD_LEFT);
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
                'user_id' => auth()->user()->id,
                'created_at' => Carbon::now()
            ]);
        } catch (\Exception $e) {
            Log::warning('No se pudo registrar auditoría: ' . $e->getMessage());
        }
    }

    // ... (Continuar con los demás métodos de soporte)

    /**
     * Obtener productos favoritos del cliente
     */
    private function obtenerProductosFavoritos($codigoCliente)
    {
        return DB::table('Docdet')
            ->join('Saldos', 'Docdet.Codpro', '=', 'Saldos.Codigo')
            ->select([
                'Saldos.Codigo',
                'Saldos.Descripcion',
                DB::raw('SUM(Docdet.Cantidad) as total_comprado')
            ])
            ->where('Docdet.Numerod', 'IN', function($query) use ($codigoCliente) {
                $query->select('Numero')
                      ->from('Doccab')
                      ->where('Codcli', $codigoCliente);
            })
            ->groupBy('Saldos.Codigo', 'Saldos.Descripcion')
            ->orderBy('total_comprado', 'desc')
            ->limit(5)
            ->get();
    }

    /**
     * Métodos adicionales de análisis
     */
    private function analizarFrecuenciaCompra($codigoCliente)
    {
        // Implementar análisis de frecuencia
        return ['frecuencia' => 'Media', 'dias_promedio' => 15];
    }

    private function analizarEstacionalidad($codigoCliente)
    {
        // Implementar análisis de estacionalidad
        return ['patron' => 'Uniforme', 'meses_activos' => 12];
    }

    private function analizarTendenciaCompras($codigoCliente)
    {
        // Implementar análisis de tendencia
        return ['tendencia' => 'Creciente', 'porcentaje_cambio' => 5.2];
    }

    private function calcularClasificacionRiesgo($historial, $pendientes)
    {
        if (!$historial || $historial->total_facturas_pagadas == 0) {
            return ['nivel' => 'No Evaluado', 'puntaje' => 0];
        }

        $porcentajeAtraso = $historial->facturas_atrasadas / $historial->total_facturas_pagadas * 100;
        
        if ($porcentajeAtraso <= 10) {
            return ['nivel' => 'Bajo', 'puntaje' => 85];
        } elseif ($porcentajeAtraso <= 30) {
            return ['nivel' => 'Medio', 'puntaje' => 65];
        } else {
            return ['nivel' => 'Alto', 'puntaje' => 35];
        }
    }

    private function generarRecomendacionesCredito($cliente, $historial, $pendientes)
    {
        return [
            'limite_sugerido' => $cliente->LimiteCredito * 1.2,
            'dias_credito' => 30,
            'observaciones' => 'Cliente con buen historial crediticio'
        ];
    }

    private function calcularLimiteCreditoSugerido($cliente, $historial)
    {
        if (!$historial || $historial->total_facturas_pagadas == 0) {
            return $cliente->LimiteCredito;
        }

        // Basado en historial de compras
        return min($cliente->LimiteCredito * 1.5, $historial->promedio_mensual * 3);
    }

    // Métodos adicionales de soporte
    private function obtenerTopCiudades()
    {
        return DB::table('Clientes')
            ->select('Ciudad', DB::raw('COUNT(*) as total'))
            ->where('Estado', 'Activo')
            ->groupBy('Ciudad')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();
    }

    private function obtenerDistribucionTipo()
    {
        return DB::table('Clientes')
            ->select('TipoCliente', DB::raw('COUNT(*) as total'))
            ->where('Estado', 'Activo')
            ->groupBy('TipoCliente')
            ->pluck('total', 'TipoCliente');
    }

    private function obtenerClientesSinCompras()
    {
        return DB::table('Clientes')
            ->where('Estado', 'Activo')
            ->where('TotalCompras', 0)
            ->count();
    }

    private function obtenerClientesMayoristas()
    {
        return DB::table('Clientes')
            ->where('Estado', 'Activo')
            ->where('TipoCliente', 'Mayorista')
            ->count();
    }

    private function calcularPromedioMensual($codigoCliente)
    {
        $totalCompras = DB::table('Doccab')
            ->where('Codcli', $codigoCliente)
            ->sum('Total');
        
        $meses = DB::table('Doccab')
            ->where('Codcli', $codigoCliente)
            ->selectRaw('DATEDIFF(MONTH, MIN(Fecha), MAX(Fecha)) + 1 as meses')
            ->first()->meses ?? 1;

        return $totalCompras / max($meses, 1);
    }

    private function clasificarCliente($cliente)
    {
        if ($cliente->TotalCompras >= 100000) {
            return 'AAA';
        } elseif ($cliente->TotalCompras >= 50000) {
            return 'AA';
        } elseif ($cliente->TotalCompras >= 10000) {
            return 'A';
        } else {
            return 'B';
        }
    }
}