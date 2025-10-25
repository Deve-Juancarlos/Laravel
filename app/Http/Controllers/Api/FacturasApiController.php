<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class FacturasApiController extends Controller
{

    public function __construct()
    {
        // Middleware de autenticación API
        $this->middleware('auth:sanctum');
        
        // Rate limiting por defecto
        $this->middleware('throttle:80,1');
        
        // Validación de permisos API
        $this->middleware('api.permission:facturas');
    }

   
    public function index(Request $request): JsonResponse
    {
        try {
            // Validación de parámetros
            $validator = Validator::make($request->all(), [
                'page' => 'integer|min:1',
                'per_page' => 'integer|min:1|max:100',
                'search' => 'string|max:255',
                'estado' => 'in:Pagado,Parcial,Pendiente,Anulado',
                'serie' => 'string|max:20',
                'tipo_doc' => 'in:Factura,Boleta,NC,ND,ReciboHonorarios',
                'cliente_codigo' => 'string|max:50',
                'vendedor' => 'string|max:100',
                'fecha_desde' => 'date',
                'fecha_hasta' => 'date|after:fecha_desde',
                'monto_min' => 'numeric|min:0',
                'monto_max' => 'numeric|min:0',
                'order_by' => 'in:Fecha,Numero,Total,Saldo,Cliente',
                'order_dir' => 'in:asc,desc'
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
            $query = DB::table('Doccab')
                ->select([
                    'Doccab.Id',
                    'Doccab.Numero',
                    'Doccab.Serie',
                    'Doccab.Fecha',
                    'Doccab.FechaVenc',
                    'Doccab.FechaPago',
                    'Doccab.Total',
                    'Doccab.Saldo',
                    'Doccab.Descuento',
                    'Doccab.Estado',
                    'Doccab.TipoDoc',
                    'Doccab.Vendedor',
                    'Doccab.Observaciones',
                    'Clientes.Nombre as ClienteNombre',
                    'Clientes.Ruc as ClienteRuc',
                    'Clientes.Codigo as ClienteCodigo'
                ])
                ->leftJoin('Clientes', 'Doccab.Codcli', '=', 'Clientes.Codigo');

            // Aplicar filtros
            $this->applyFilters($query, $request);

            // Contar total para paginación
            $total = $query->count();

            // Ordenamiento
            $orderBy = $request->input('order_by', 'Fecha');
            $orderDir = $request->input('order_dir', 'desc');
            $query->orderBy($orderBy, $orderDir);

            // Paginación
            $documentos = $query->offset($offset)->limit($perPage)->get();

            // Calcular metadatos de paginación
            $totalPages = ceil($total / $perPage);
            $hasNextPage = $page < $totalPages;
            $hasPrevPage = $page > 1;

            // Calcular estadísticas
            $stats = $this->calcularEstadisticasFacturas($query);

            return response()->json([
                'success' => true,
                'data' => [
                    'documentos' => $documentos,
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
            Log::error('Error en FacturasApiController::index: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    public function show(Request $request, $id): JsonResponse
    {
        try {
            // Validar ID
            if (!is_numeric($id) || $id <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID de documento inválido'
                ], 400);
            }

            // Obtener documento con datos del cliente
            $documento = DB::table('Doccab')
                ->select([
                    'Doccab.*',
                    'Clientes.Nombre as ClienteNombre',
                    'Clientes.Ruc as ClienteRuc',
                    'Clientes.Direccion as ClienteDireccion',
                    'Clientes.Ciudad as ClienteCiudad',
                    'Clientes.Telefono as ClienteTelefono',
                    'Clientes.Email as ClienteEmail'
                ])
                ->leftJoin('Clientes', 'Doccab.Codcli', '=', 'Clientes.Codigo')
                ->where('Doccab.Id', $id)
                ->first();

            if (!$documento) {
                return response()->json([
                    'success' => false,
                    'message' => 'Documento no encontrado'
                ], 404);
            }

            // Obtener detalles del documento
            $detalles = DB::table('Docdet')
                ->select([
                    'Docdet.*',
                    'Saldos.Descripcion as ProductoDescripcion',
                    'Saldos.UnidadMedida',
                    'Saldos.PrecioCosto'
                ])
                ->leftJoin('Saldos', 'Docdet.Codpro', '=', 'Saldos.Codigo')
                ->where('Docdet.Numerod', $documento->Numero)
                ->get();

            // Calcular totales adicionales
            $totales = $this->calcularTotalesDocumento($documento, $detalles);

            // Obtener historial de pagos
            $historialPagos = $this->obtenerHistorialPagos($documento->Numero);

            // Obtener documentos relacionados (créditos/débito)
            $documentosRelacionados = $this->obtenerDocumentosRelacionados($documento->Numero);

            return response()->json([
                'success' => true,
                'data' => [
                    'documento' => $documento,
                    'detalles' => $detalles,
                    'totales' => $totales,
                    'historial_pagos' => $historialPagos,
                    'documentos_relacionados' => $documentosRelacionados
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error en FacturasApiController::show: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Crear nuevo documento
     * POST /api/facturas
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Validación de datos
            $validator = Validator::make($request->all(), [
                'TipoDoc' => 'required|in:Factura,Boleta,NC,ND,ReciboHonorarios',
                'Serie' => 'required|string|max:20',
                'Fecha' => 'required|date',
                'Codcli' => 'required|string|max:50',
                'Vendedor' => 'string|max:100',
                'Observaciones' => 'string',
                'Descuento' => 'numeric|min:0',
                'detalles' => 'required|array|min:1',
                'detalles.*.Codpro' => 'required|string|max:50',
                'detalles.*.Cantidad' => 'required|numeric|min:0.01',
                'detalles.*.Precio' => 'required|numeric|min:0'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            // Verificar que el cliente existe
            $cliente = DB::table('Clientes')->where('Codigo', $request->Codcli)->first();
            if (!$cliente) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cliente no encontrado'
                ], 404);
            }

            // Generar número de documento único
            $numeroDocumento = $this->generarNumeroDocumento($request->TipoDoc, $request->Serie);

            // Calcular totales
            $totales = $this->calcularTotalesNuevoDocumento($request->detalles, $request->Descuento ?? 0);

            // Preparar datos del documento
            $datosDocumento = [
                'Numero' => $numeroDocumento,
                'Serie' => $request->Serie,
                'TipoDoc' => $request->TipoDoc,
                'Fecha' => $request->Fecha,
                'FechaVenc' => Carbon::parse($request->Fecha)->addDays(30),
                'Codcli' => $request->Codcli,
                'Total' => $totales['total_con_igv'],
                'Subtotal' => $totales['subtotal'],
                'Igv' => $totales['igv'],
                'Descuento' => $request->Descuento ?? 0,
                'Saldo' => $totales['total_con_igv'],
                'Estado' => 'Pendiente',
                'Vendedor' => $request->Vendedor,
                'Observaciones' => $request->Observaciones,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ];

            // Iniciar transacción
            DB::beginTransaction();

            try {
                // Insertar documento
                $documentoId = DB::table('Doccab')->insertGetId($datosDocumento);

                // Insertar detalles
                foreach ($request->detalles as $detalle) {
                    $datosDetalle = [
                        'Numerod' => $numeroDocumento,
                        'Codpro' => $detalle['Codpro'],
                        'Cantidad' => $detalle['Cantidad'],
                        'Precio' => $detalle['Precio'],
                        'Total' => $detalle['Cantidad'] * $detalle['Precio'],
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ];

                    DB::table('Docdet')->insert($datosDetalle);

                    // Actualizar stock si es necesario
                    if (in_array($request->TipoDoc, ['Factura', 'Boleta'])) {
                        DB::table('Saldos')
                            ->where('Codigo', $detalle['Codpro'])
                            ->decrement('Stock', $detalle['Cantidad']);
                    }
                }

               // Actualizar estadísticas del cliente
                DB::table('Clientes')
                    ->where('Codigo', $request->Codcli)
                    ->increment('TotalCompras', $totales['total_con_igv']);

                DB::table('Clientes')
                    ->where('Codigo', $request->Codcli)
                    ->update(['UltimaCompra' => $request->Fecha]);

                DB::commit();

                // Registrar en auditoría
                $this->registrarAuditoria('CREATE', 'Doccab', $documentoId, $datosDocumento, $request);

                // Obtener documento creado
                $documentoCreado = DB::table('Doccab')
                    ->where('Id', $documentoId)
                    ->first();

                return response()->json([
                    'success' => true,
                    'message' => 'Documento creado exitosamente',
                    'data' => $documentoCreado
                ], 201);

            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Error en FacturasApiController::store: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }


    public function update(Request $request, $id): JsonResponse
    {
        try {
            // Validar ID
            if (!is_numeric($id) || $id <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID de documento inválido'
                ], 400);
            }

            // Verificar que el documento existe
            $documentoExistente = DB::table('Doccab')->where('Id', $id)->first();
            if (!$documentoExistente) {
                return response()->json([
                    'success' => false,
                    'message' => 'Documento no encontrado'
                ], 404);
            }

            // Verificar si el documento puede ser editado
            if ($documentoExistente->Estado === 'Pagado') {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede editar un documento pagado'
                ], 400);
            }

            // Validación de datos
            $validator = Validator::make($request->all(), [
                'Fecha' => 'date',
                'Vendedor' => 'string|max:100',
                'Observaciones' => 'string',
                'Estado' => 'in:Pagado,Parcial,Pendiente,Anulado'
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
                'Fecha', 'Vendedor', 'Observaciones', 'Estado'
            ]));

            $datosActualizacion['updated_at'] = Carbon::now();

            // Actualizar documento
            DB::table('Doccab')
                ->where('Id', $id)
                ->update($datosActualizacion);

            // Obtener documento actualizado
            $documentoActualizado = DB::table('Doccab')
                ->where('Id', $id)
                ->first();

            // Registrar en auditoría
            $this->registrarAuditoria('UPDATE', 'Doccab', $id, $documentoActualizado, $request);

            return response()->json([
                'success' => true,
                'message' => 'Documento actualizado exitosamente',
                'data' => $documentoActualizado
            ]);

        } catch (\Exception $e) {
            Log::error('Error en FacturasApiController::update: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        try {
            // Validar ID
            if (!is_numeric($id) || $id <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID de documento inválido'
                ], 400);
            }

            // Verificar que el documento existe
            $documento = DB::table('Doccab')->where('Id', $id)->first();
            if (!$documento) {
                return response()->json([
                    'success' => false,
                    'message' => 'Documento no encontrado'
                ], 404);
            }

            // Verificar si puede ser anulado
            if ($documento->Estado === 'Pagado' && $documento->Saldo == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede anular un documento completamente pagado'
                ], 400);
            }

            // Verificar si ya fue anulado
            if ($documento->Estado === 'Anulado') {
                return response()->json([
                    'success' => false,
                    'message' => 'El documento ya está anulado'
                ], 400);
            }

            // Iniciar transacción
            DB::beginTransaction();

            try {
                // Anular documento
                DB::table('Doccab')
                    ->where('Id', $id)
                    ->update([
                        'Estado' => 'Anulado',
                        'Observaciones' => ($documento->Observaciones ?? '') . ' [ANULADO: ' . Carbon::now() . ']',
                        'updated_at' => Carbon::now()
                    ]);

                // Devolver stock si es un documento de venta
                if (in_array($documento->TipoDoc, ['Factura', 'Boleta'])) {
                    $detalles = DB::table('Docdet')
                        ->where('Numerod', $documento->Numero)
                        ->get();

                    foreach ($detalles as $detalle) {
                        DB::table('Saldos')
                            ->where('Codigo', $detalle->Codpro)
                            ->increment('Stock', $detalle->Cantidad);
                    }
                }

                DB::commit();

                // Registrar en auditoría
                $this->registrarAuditoria('DELETE', 'Doccab', $id, $documento, $request);

                return response()->json([
                    'success' => true,
                    'message' => 'Documento anulado exitosamente'
                ]);

            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Error en FacturasApiController::destroy: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

  
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

            $documentos = DB::table('Doccab')
                ->select([
                    'Doccab.Id',
                    'Doccab.Numero',
                    'Doccab.Serie',
                    'Doccab.Fecha',
                    'Doccab.Total',
                    'Doccab.Estado',
                    'Doccab.TipoDoc',
                    'Clientes.Nombre as ClienteNombre',
                    'Clientes.Ruc as ClienteRuc'
                ])
                ->leftJoin('Clientes', 'Doccab.Codcli', '=', 'Clientes.Codigo')
                ->where(function($q) use ($query) {
                    $q->where('Doccab.Numero', 'LIKE', "%{$query}%")
                      ->orWhere('Clientes.Nombre', 'LIKE', "%{$query}%")
                      ->orWhere('Clientes.Ruc', 'LIKE', "%{$query}%")
                      ->orWhere(DB::raw("CONCAT(Doccab.Serie, '-', Doccab.Numero)"), 'LIKE', "%{$query}%");
                })
                ->orderBy('Doccab.Fecha', 'desc')
                ->limit($limit)
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'documentos' => $documentos,
                    'total' => $documentos->count(),
                    'query' => $query
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error en FacturasApiController::search: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    public function statistics(Request $request): JsonResponse
    {
        try {
            $fechaDesde = $request->input('fecha_desde', Carbon::now()->subMonth()->format('Y-m-d'));
            $fechaHasta = $request->input('fecha_hasta', Carbon::now()->format('Y-m-d'));

            // Estadísticas por período
            $estadisticas = [
                'resumen_periodo' => [
                    'total_documentos' => DB::table('Doccab')
                        ->whereBetween('Fecha', [$fechaDesde, $fechaHasta])
                        ->count(),
                    'monto_total' => DB::table('Doccab')
                        ->whereBetween('Fecha', [$fechaDesde, $fechaHasta])
                        ->sum('Total'),
                    'documentos_pagados' => DB::table('Doccab')
                        ->whereBetween('Fecha', [$fechaDesde, $fechaHasta])
                        ->where('Estado', 'Pagado')
                        ->count(),
                    'saldo_pendiente' => DB::table('Doccab')
                        ->whereBetween('Fecha', [$fechaDesde, $fechaHasta])
                        ->sum('Saldo')
                ],
                'distribucion_estado' => DB::table('Doccab')
                    ->whereBetween('Fecha', [$fechaDesde, $fechaHasta])
                    ->select('Estado', DB::raw('COUNT(*) as total'))
                    ->groupBy('Estado')
                    ->pluck('total', 'Estado'),
                'distribucion_tipo' => DB::table('Doccab')
                    ->whereBetween('Fecha', [$fechaDesde, $fechaHasta])
                    ->select('TipoDoc', DB::raw('COUNT(*) as total'))
                    ->groupBy('TipoDoc')
                    ->pluck('total', 'TipoDoc'),
                'top_vendedores' => $this->obtenerTopVendedores($fechaDesde, $fechaHasta),
                'top_clientes' => $this->obtenerTopClientes($fechaDesde, $fechaHasta),
                'documentos_vencidos' => $this->obtenerDocumentosVencidos(),
                'documentos_por_vencer' => $this->obtenerDocumentosPorVencer()
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
            Log::error('Error en FacturasApiController::statistics: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    public function detalle(Request $request, $id): JsonResponse
    {
        try {
            // Validar ID
            if (!is_numeric($id) || $id <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID de documento inválido'
                ], 400);
            }

            // Obtener documento
            $documento = DB::table('Doccab')->where('Id', $id)->first();
            if (!$documento) {
                return response()->json([
                    'success' => false,
                    'message' => 'Documento no encontrado'
                ], 404);
            }

            // Obtener detalles completos
            $detalles = DB::table('Docdet')
                ->select([
                    'Docdet.*',
                    'Saldos.Descripcion as ProductoDescripcion',
                    'Saldos.UnidadMedida',
                    'Saldos.CodigoBarras',
                    'Saldos.Categoria',
                    DB::raw('(Docdet.Cantidad * Docdet.Precio) as SubtotalDetalle')
                ])
                ->leftJoin('Saldos', 'Docdet.Codpro', '=', 'Saldos.Codigo')
                ->where('Docdet.Numerod', $documento->Numero)
                ->get();

            // Calcular totales
            $subtotal = $detalles->sum('SubtotalDetalle');
            $descuento = $documento->Descuento ?? 0;
            $baseImponible = ($subtotal - $descuento) / 1.18;
            $igv = ($subtotal - $descuento) - $baseImponible;
            $total = $subtotal - $descuento;

            return response()->json([
                'success' => true,
                'data' => [
                    'documento' => $documento,
                    'detalles' => $detalles,
                    'totales_detallados' => [
                        'subtotal' => round($subtotal, 2),
                        'descuento' => round($descuento, 2),
                        'base_imponible' => round($baseImponible, 2),
                        'igv' => round($igv, 2),
                        'total' => round($total, 2)
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error en FacturasApiController::detalle: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

  
    public function cuentasPorCobrar(Request $request): JsonResponse
    {
        try {
            // Filtros opcionales
            $clienteId = $request->input('cliente_id');
            $estado = $request->input('estado', 'Pendiente');
            $vencido = $request->boolean('vencido');

            // Query base
            $query = DB::table('Doccab')
                ->select([
                    'Doccab.Id',
                    'Doccab.Numero',
                    'Doccab.Serie',
                    'Doccab.Fecha',
                    'Doccab.FechaVenc',
                    'Doccab.Total',
                    'Doccab.Saldo',
                    'Doccab.Estado',
                    'Doccab.DiasVencimiento',
                    'Clientes.Nombre as ClienteNombre',
                    'Clientes.Codigo as ClienteCodigo',
                    'Clientes.Ruc as ClienteRuc'
                ])
                ->leftJoin('Clientes', 'Doccab.Codcli', '=', 'Clientes.Codigo')
                ->where('Doccab.Saldo', '>', 0);

            // Aplicar filtros
            if ($estado) {
                $query->where('Doccab.Estado', $estado);
            }

            if ($clienteId) {
                $query->where('Clientes.Codigo', $clienteId);
            }

            if ($vencido) {
                $query->where('Doccab.FechaVenc', '<', Carbon::now());
            }

            // Ordenar por fecha de vencimiento
            $query->orderBy('Doccab.FechaVenc');

            $cuentas = $query->get();

            // Calcular resumen
            $resumen = [
                'total_cuentas' => $cuentas->count(),
                'monto_total' => $cuentas->sum('Saldo'),
                'cuentas_vencidas' => $cuentas->where('DiasVencimiento', '>', 0)->count(),
                'monto_vencido' => $cuentas->where('DiasVencimiento', '>', 0)->sum('Saldo'),
                'cuentas_por_vencer' => $cuentas->where('DiasVencimiento', '<=', 0)->count(),
                'monto_por_vencer' => $cuentas->where('DiasVencimiento', '<=', 0)->sum('Saldo')
            ];

            // Agrupar por antigüedad de saldo
            $antiguedadSaldos = [
                '0_30_dias' => $cuentas->whereBetween('DiasVencimiento', [0, 30])->sum('Saldo'),
                '31_60_dias' => $cuentas->whereBetween('DiasVencimiento', [31, 60])->sum('Saldo'),
                '61_90_dias' => $cuentas->whereBetween('DiasVencimiento', [61, 90])->sum('Saldo'),
                'mas_90_dias' => $cuentas->where('DiasVencimiento', '>', 90)->sum('Saldo')
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'cuentas_por_cobrar' => $cuentas,
                    'resumen' => $resumen,
                    'antiguedad_saldos' => $antiguedadSaldos
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error en FacturasApiController::cuentasPorCobrar: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    public function registrarPago(Request $request, $id): JsonResponse
    {
        try {
            // Validar ID
            if (!is_numeric($id) || $id <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID de documento inválido'
                ], 400);
            }

            // Validación de datos
            $validator = Validator::make($request->all(), [
                'monto' => 'required|numeric|min:0.01',
                'fecha_pago' => 'required|date',
                'metodo_pago' => 'required|in:Efectivo,Transferencia,Cheque,Tarjeta,Descuento',
                'observaciones' => 'string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            // Obtener documento
            $documento = DB::table('Doccab')->where('Id', $id)->first();
            if (!$documento) {
                return response()->json([
                    'success' => false,
                    'message' => 'Documento no encontrado'
                ], 404);
            }

            // Verificar saldo
            if ($documento->Saldo < $request->monto) {
                return response()->json([
                    'success' => false,
                    'message' => 'El monto del pago excede el saldo pendiente'
                ], 400);
            }

            $nuevoSaldo = $documento->Saldo - $request->monto;
            $nuevoEstado = $nuevoSaldo <= 0 ? 'Pagado' : 'Parcial';

            // Actualizar documento
            DB::table('Doccab')
                ->where('Id', $id)
                ->update([
                    'Saldo' => $nuevoSaldo,
                    'Estado' => $nuevoEstado,
                    'FechaPago' => $nuevoEstado === 'Pagado' ? $request->fecha_pago : null,
                    'updated_at' => Carbon::now()
                ]);

            // Registrar movimiento de caja (opcional)
            $this->registrarMovimientoCaja($documento, $request);

            // Registrar en auditoría
            $this->registrarAuditoria('PAYMENT', 'Doccab', $id, [
                'monto_pago' => $request->monto,
                'nuevo_saldo' => $nuevoSaldo,
                'nuevo_estado' => $nuevoEstado
            ], $request);

            // Obtener documento actualizado
            $documentoActualizado = DB::table('Doccab')
                ->where('Id', $id)
                ->first();

            return response()->json([
                'success' => true,
                'message' => 'Pago registrado exitosamente',
                'data' => [
                    'documento' => $documentoActualizado,
                    'pago_registrado' => [
                        'monto' => $request->monto,
                        'saldo_anterior' => $documento->Saldo,
                        'saldo_nuevo' => $nuevoSaldo,
                        'estado_anterior' => $documento->Estado,
                        'estado_nuevo' => $nuevoEstado
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error en FacturasApiController::registrarPago: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

  
    private function applyFilters($query, Request $request)
    {
        // Filtro de búsqueda
        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->where(function($q) use ($search) {
                $q->where('Doccab.Numero', 'LIKE', $search)
                  ->orWhere('Clientes.Nombre', 'LIKE', $search)
                  ->orWhere(DB::raw("CONCAT(Doccab.Serie, '-', Doccab.Numero)"), 'LIKE', $search);
            });
        }

        // Filtro por estado
        if ($request->filled('estado')) {
            $query->where('Doccab.Estado', $request->estado);
        }

        // Filtro por serie
        if ($request->filled('serie')) {
            $query->where('Doccab.Serie', $request->serie);
        }

        // Filtro por tipo de documento
        if ($request->filled('tipo_doc')) {
            $query->where('Doccab.TipoDoc', $request->tipo_doc);
        }

        // Filtro por código de cliente
        if ($request->filled('cliente_codigo')) {
            $query->where('Doccab.Codcli', $request->cliente_codigo);
        }

        // Filtro por vendedor
        if ($request->filled('vendedor')) {
            $query->where('Doccab.Vendedor', 'LIKE', '%' . $request->vendedor . '%');
        }

        // Filtro por fechas
        if ($request->filled('fecha_desde')) {
            $query->whereDate('Doccab.Fecha', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('Doccab.Fecha', '<=', $request->fecha_hasta);
        }

        // Filtro por montos
        if ($request->filled('monto_min')) {
            $query->where('Doccab.Total', '>=', $request->monto_min);
        }

        if ($request->filled('monto_max')) {
            $query->where('Doccab.Total', '<=', $request->monto_max);
        }
    }

 
    private function calcularEstadisticasFacturas($baseQuery)
    {
        $hoy = Carbon::now();
        $mesActual = $hoy->month;
        $añoActual = $hoy->year;

        return [
            'total_hoy' => DB::table('Doccab')
                ->whereDate('Fecha', $hoy)
                ->sum('Total'),
            'total_mes' => DB::table('Doccab')
                ->whereMonth('Fecha', $mesActual)
                ->whereYear('Fecha', $añoActual)
                ->sum('Total'),
            'documentos_pendientes' => DB::table('Doccab')
                ->where('Estado', 'Pendiente')
                ->count(),
            'saldo_total_pendiente' => DB::table('Doccab')
                ->where('Saldo', '>', 0)
                ->sum('Saldo'),
            'promedio_diario' => DB::table('Doccab')
                ->whereDate('Fecha', '>=', $hoy->subDays(30))
                ->avg('Total')
        ];
    }

    private function generarNumeroDocumento($tipoDoc, $serie)
    {
        $prefijo = match($tipoDoc) {
            'Factura' => 'F',
            'Boleta' => 'B',
            'NC' => 'NC',
            'ND' => 'ND',
            'ReciboHonorarios' => 'RH',
            default => 'DOC'
        };

        $ultimoNumero = DB::table('Doccab')
            ->where('Serie', $serie)
            ->where('TipoDoc', $tipoDoc)
            ->orderBy('Numero', 'desc')
            ->value('Numero');

        if ($ultimoNumero) {
            $numero = (int)$ultimoNumero + 1;
        } else {
            $numero = 1;
        }

        return str_pad($numero, 8, '0', STR_PAD_LEFT);
    }

    private function calcularTotalesNuevoDocumento($detalles, $descuentoGlobal)
    {
        $subtotal = 0;
        
        foreach ($detalles as $detalle) {
            $subtotal += $detalle['Cantidad'] * $detalle['Precio'];
        }

        $descuentoTotal = $descuentoGlobal;
        $baseImponible = ($subtotal - $descuentoTotal) / 1.18;
        $igv = ($subtotal - $descuentoTotal) - $baseImponible;
        $totalConIgv = $subtotal - $descuentoTotal;

        return [
            'subtotal' => round($subtotal, 2),
            'descuento' => round($descuentoTotal, 2),
            'base_imponible' => round($baseImponible, 2),
            'igv' => round($igv, 2),
            'total_con_igv' => round($totalConIgv, 2)
        ];
    }

    private function calcularTotalesDocumento($documento, $detalles)
    {
        $subtotal = $detalles->sum('Total');
        
        return [
            'subtotal' => $subtotal,
            'descuento' => $documento->Descuento ?? 0,
            'base_imponible' => ($subtotal - ($documento->Descuento ?? 0)) / 1.18,
            'igv' => ($subtotal - ($documento->Descuento ?? 0)) - (($subtotal - ($documento->Descuento ?? 0)) / 1.18),
            'total' => $subtotal - ($documento->Descuento ?? 0)
        ];
    }

    private function obtenerHistorialPagos($numeroDocumento)
    {
        // Esta tabla podría estar en una tabla separada de movimientos de pago
        return DB::table('movimientos_caja')
            ->select(['*'])
            ->where('documento_numero', $numeroDocumento)
            ->orderBy('fecha', 'desc')
            ->get();
    }

    private function obtenerDocumentosRelacionados($numeroDocumento)
    {
        return DB::table('Doccab')
            ->select(['Id', 'Numero', 'TipoDoc', 'Total', 'Estado'])
            ->where(function($query) use ($numeroDocumento) {
                $query->where('NumeroReferencia', $numeroDocumento)
                      ->orWhereRaw("Observaciones LIKE '%{$numeroDocumento}%'");
            })
            ->get();
    }

    private function registrarMovimientoCaja($documento, $request)
    {
        try {
            DB::table('movimientos_caja')->insert([
                'fecha' => $request->fecha_pago,
                'tipo' => 'INGRESO',
                'concepto' => "Pago {$documento->TipoDoc} {$documento->Serie}-{$documento->Numero}",
                'monto' => $request->monto,
                'metodo_pago' => $request->metodo_pago,
                'documento_numero' => $documento->Numero,
                'cliente_codigo' => $documento->Codcli,
                'observaciones' => $request->observaciones,
                'created_at' => Carbon::now()
            ]);
        } catch (\Exception $e) {
            Log::warning('No se pudo registrar movimiento de caja: ' . $e->getMessage());
        }
    }

    private function registrarAuditoria($accion, $tabla, $registroId, $datos, Request $request)
    {
        try {
            DB::table('auditoria_log')->insert([
                'accion' => $accion,
                'tabla' => $tabla,
                'registro_id' => $registroId,
                'datos_anteriores' => is_string($datos) ? $datos : json_encode($datos),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'user_id' => auth('sanctum')->id() ?? null,
                'created_at' => Carbon::now()
            ]);
        } catch (\Exception $e) {
            Log::warning('No se pudo registrar auditoría: ' . $e->getMessage());
        }
    }

    // Métodos adicionales de estadísticas
    private function obtenerTopVendedores($fechaDesde, $fechaHasta)
    {
        return DB::table('Doccab')
            ->select('Vendedor', DB::raw('COUNT(*) as documentos'), DB::raw('SUM(Total) as monto_total'))
            ->whereBetween('Fecha', [$fechaDesde, $fechaHasta])
            ->whereNotNull('Vendedor')
            ->groupBy('Vendedor')
            ->orderBy('monto_total', 'desc')
            ->limit(10)
            ->get();
    }

    private function obtenerTopClientes($fechaDesde, $fechaHasta)
    {
        return DB::table('Doccab')
            ->join('Clientes', 'Doccab.Codcli', '=', 'Clientes.Codigo')
            ->select('Clientes.Codigo', 'Clientes.Nombre', DB::raw('COUNT(*) as documentos'), DB::raw('SUM(Doccab.Total) as monto_total'))
            ->whereBetween('Doccab.Fecha', [$fechaDesde, $fechaHasta])
            ->groupBy('Clientes.Codigo', 'Clientes.Nombre')
            ->orderBy('monto_total', 'desc')
            ->limit(10)
            ->get();
    }

    private function obtenerDocumentosVencidos()
    {
        return DB::table('Doccab')
            ->where('Saldo', '>', 0)
            ->where('FechaVenc', '<', Carbon::now())
            ->count();
    }

    private function obtenerDocumentosPorVencer()
    {
        return DB::table('Doccab')
            ->where('Saldo', '>', 0)
            ->where('FechaVenc', '>=', Carbon::now())
            ->where('FechaVenc', '<=', Carbon::now()->addDays(7))
            ->count();
    }
}