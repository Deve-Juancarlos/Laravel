<?php

namespace App\Http\Controllers\Clientes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class ClientesController extends Controller
{
    /**
     * MÓDULO CLIENTES - Controlador principal de gestión de clientes
     * Integrado con base de datos SIFANO existente
     * Total de líneas: ~650
     */

    public function __construct()
    {
        $this->middleware(['auth',]);
    }

    /**
     * ===============================================
     * MÉTODOS PRINCIPALES DEL MÓDULO
     * ===============================================
     */

    /**
     * Muestra el dashboard principal de clientes
     */
    public function index(Request $request)
    {
        $filtros = $this->obtenerFiltros($request);
        $clientes = $this->consultarClientes($filtros);
        $estadisticas = $this->calcularEstadisticas();
        $segmentacion = $this->analizarSegmentacionClientes();

        return compact('clientes', 'estadisticas', 'segmentacion', 'filtros');
    }

    /**
     * Obtiene detalles completos de un cliente
     */
    public function show($id)
    {
        $cliente = DB::table('Clientes')->where('CodClie', $id)->first();
        
        if (!$cliente) {
            return response()->json(['error' => 'Cliente no encontrado'], 404);
        }

        $datos_completos = [
            'cliente' => $cliente,
            'compras' => $this->obtenerHistorialCompras($id),
            'estadisticas' => $this->calcularEstadisticasCliente($id),
            'credito' => $this->analizarCredito($id),
            'recomendaciones' => $this->generarRecomendaciones($id),
            'actividad' => $this->obtenerActividadReciente($id)
        ];

        return $datos_completos;
    }

    /**
     * Crea un nuevo cliente
     */
    public function store(Request $request)
    {
        $validacion = $this->validarDatosCliente($request);
        if ($validacion['errors']) {
            return response()->json($validacion, 400);
        }

        try {
            DB::beginTransaction();

            // Crear cliente
            $cliente_id = DB::table('Clientes')->insertGetId([
                'CodClie' => $this->generarCodigoCliente(),
                'Razon' => $request->razon_social,
                'Ruc' => $request->ruc,
                'Direccion' => $request->direccion,
                'Distrito' => $request->distrito,
                'Provincia' => $request->provincia,
                'Departamento' => $request->departamento,
                'Telefono' => $request->telefono,
                'Email' => $request->email,
                'Contacto' => $request->contacto,
                'Fecha_inicio' => now(),
                'Categoria' => $request->categoria ?? 'REGULAR',
                'CreditLimit' => $request->limite_credito ?? 0,
                'DiasCredito' => $request->dias_credito ?? 0,
                'Estado' => 'ACTIVO'
            ]);

            // Registrar actividad
            $this->registrarActividad($cliente_id, 'CLIENTE_CREADO', 'Cliente registrado en el sistema');

            // Cliente asignado al contador
            // No se requiere tabla intermedia

            DB::commit();

            return response()->json([
                'success' => true,
                'mensaje' => 'Cliente creado exitosamente',
                'cliente_id' => $cliente_id
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'Error al crear cliente: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Actualiza datos de un cliente
     */
    public function update(Request $request, $id)
    {
        $cliente = DB::table('Clientes')->where('CodClie', $id)->first();
        if (!$cliente) {
            return response()->json(['error' => 'Cliente no encontrado'], 404);
        }

        $validacion = $this->validarDatosCliente($request, $id);
        if ($validacion['errors']) {
            return response()->json($validacion, 400);
        }

        try {
            DB::beginTransaction();

            // Actualizar cliente
            DB::table('Clientes')->where('CodClie', $id)->update([
                'Razon' => $request->razon_social,
                'Ruc' => $request->ruc,
                'Direccion' => $request->direccion,
                'Distrito' => $request->distrito,
                'Provincia' => $request->provincia,
                'Departamento' => $request->departamento,
                'Telefono' => $request->telefono,
                'Email' => $request->email,
                'Contacto' => $request->contacto,
                'Categoria' => $request->categoria,
                'CreditLimit' => $request->limite_credito,
                'DiasCredito' => $request->dias_credito,
                'Estado' => $request->estado ?? 'ACTIVO'
            ]);

            // Registrar actividad
            $this->registrarActividad($id, 'CLIENTE_ACTUALIZADO', 'Datos del cliente actualizados');

            // No se requiere actualización de asignación

            DB::commit();

            return response()->json([
                'success' => true,
                'mensaje' => 'Cliente actualizado exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'Error al actualizar cliente'], 500);
        }
    }

    /**
     * Desactiva un cliente
     */
    public function destroy($id)
    {
        $cliente = DB::table('Clientes')->where('CodClie', $id)->first();
        if (!$cliente) {
            return response()->json(['error' => 'Cliente no encontrado'], 404);
        }

        // Verificar que no tenga facturas pendientes
        $facturas_pendientes = DB::table('Doccab')
            ->where('CodClie', $id)
            ->whereIn('Estado', ['PENDIENTE', 'VENCIDO'])
            ->count();

        if ($facturas_pendientes > 0) {
            return response()->json([
                'error' => 'No se puede desactivar cliente. Tiene facturas pendientes'
            ], 400);
        }

        DB::table('Clientes')->where('CodClie', $id)->update([
            'Estado' => 'INACTIVO',
            'updated_at' => now()
        ]);

        $this->registrarActividad($id, 'CLIENTE_DESACTIVADO', 'Cliente desactivado del sistema');

        return response()->json([
            'success' => true,
            'mensaje' => 'Cliente desactivado exitosamente'
        ]);
    }

    /**
     * ===============================================
     * MÉTODOS DE ANÁLISIS Y ESTADÍSTICAS
     * ===============================================
     */

    /**
     * Calcula estadísticas generales de clientes
     */
    public function calcularEstadisticas()
    {
        $total_clientes = DB::table('Clientes')->count();
        $clientes_activos = DB::table('Clientes')->where('Estado', 'ACTIVO')->count();
        $clientes_nuevos_mes = DB::table('Clientes')
            ->whereMonth('created_at', now()->month)
            ->count();
        
        $ventas_totales = DB::table('Doccab')
            ->join('Clientes', 'Doccab.CodClie', '=', 'Clientes.CodClie')
            ->where('Clientes.Estado', 'ACTIVO')
            ->sum('Doccab.Total');

        $ticket_promedio = DB::table('Doccab')
            ->join('Clientes', 'Doccab.CodClie', '=', 'Clientes.CodClie')
            ->where('Clientes.Estado', 'ACTIVO')
            ->where('Doccab.Fecha', '>=', now()->subDays(30))
            ->avg('Doccab.Total');

        return [
            'total_clientes' => $total_clientes,
            'clientes_activos' => $clientes_activos,
            'clientes_nuevos_mes' => $clientes_nuevos_mes,
            'ventas_totales' => $ventas_totales,
            'ticket_promedio' => $ticket_promedio,
            'porcentaje_activos' => $total_clientes > 0 ? ($clientes_activos / $total_clientes) * 100 : 0
        ];
    }

    /**
     * Analiza segmentación de clientes
     */
    public function analizarSegmentacionClientes()
    {
        // Segmentación por categoría
        $por_categoria = DB::table('Clientes')
            ->select('Categoria', DB::raw('COUNT(*) as cantidad'))
            ->where('Estado', 'ACTIVO')
            ->groupBy('Categoria')
            ->get();

        // Segmentación por valor de compras
        $por_valor = DB::table('Doccab')
            ->join('Clientes', 'Doccab.CodClie', '=', 'Clientes.CodClie')
            ->select(
                'Clientes.CodClie',
                'Clientes.Razon',
                DB::raw('SUM(Doccab.Total) as total_compras')
            )
            ->where('Clientes.Estado', 'ACTIVO')
            ->where('Doccab.Fecha', '>=', now()->subYear())
            ->groupBy('Clientes.CodClie', 'Clientes.Razon')
            ->orderBy('total_compras', 'desc')
            ->get()
            ->map(function ($cliente) {
                if ($cliente->total_compras >= 100000) {
                    $cliente->segmento = 'VIP';
                } elseif ($cliente->total_compras >= 50000) {
                    $cliente->segmento = 'PREMIUM';
                } elseif ($cliente->total_compras >= 20000) {
                    $cliente->segmento = 'REGULAR';
                } else {
                    $cliente->segmento = 'BASICO';
                }
                return $cliente;
            });

        // Clasificación por frecuencia de compra
        $por_frecuencia = DB::table('Doccab')
            ->join('Clientes', 'Doccab.CodClie', '=', 'Clientes.CodClie')
            ->select(
                'Clientes.CodClie',
                'Clientes.Razon',
                DB::raw('COUNT(Doccab.Numero) as frecuencia')
            )
            ->where('Clientes.Estado', 'ACTIVO')
            ->where('Doccab.Fecha', '>=', now()->subMonths(6))
            ->groupBy('Clientes.CodClie', 'Clientes.Razon')
            ->orderBy('frecuencia', 'desc')
            ->get()
            ->map(function ($cliente) {
                if ($cliente->frecuencia >= 20) {
                    $cliente->categoria_frecuencia = 'MUY_FRECUENTE';
                } elseif ($cliente->frecuencia >= 10) {
                    $cliente->categoria_frecuencia = 'FRECUENTE';
                } elseif ($cliente->frecuencia >= 5) {
                    $cliente->categoria_frecuencia = 'OCASIONAL';
                } else {
                    $cliente->categoria_frecuencia = 'ESPORADICO';
                }
                return $cliente;
            });

        return compact('por_categoria', 'por_valor', 'por_frecuencia');
    }

    /**
     * Calcula estadísticas de un cliente específico
     */
    public function calcularEstadisticasCliente($cliente_id)
    {
        $total_compras = DB::table('Doccab')
            ->where('CodClie', $cliente_id)
            ->sum('Total');

        $cantidad_compras = DB::table('Doccab')
            ->where('CodClie', $cliente_id)
            ->count();

        $fecha_primera_compra = DB::table('Doccab')
            ->where('CodClie', $cliente_id)
            ->min('Fecha');

        $fecha_ultima_compra = DB::table('Doccab')
            ->where('CodClie', $cliente_id)
            ->max('Fecha');

        $ticket_promedio = $cantidad_compras > 0 ? $total_compras / $cantidad_compras : 0;

        $compras_mes_actual = DB::table('Doccab')
            ->where('CodClie', $cliente_id)
            ->whereMonth('Fecha', now()->month)
            ->whereYear('Fecha', now()->year)
            ->sum('Total');

        return [
            'total_compras' => $total_compras,
            'cantidad_compras' => $cantidad_compras,
            'ticket_promedio' => $ticket_promedio,
            'fecha_primera_compra' => $fecha_primera_compra,
            'fecha_ultima_compra' => $fecha_ultima_compra,
            'compras_mes_actual' => $compras_mes_actual,
            'dias_desde_ultima_compra' => $fecha_ultima_compra ? now()->diffInDays($fecha_ultima_compra) : null
        ];
    }

    /**
     * ===============================================
     * MÉTODOS DE ANÁLISIS DE CRÉDITO
     * ===============================================
     */

    /**
     * Analiza situación crediticia de un cliente
     */
    public function analizarCredito($cliente_id)
    {
        $cliente = DB::table('Clientes')->where('CodClie', $cliente_id)->first();
        
        $saldo_actual = DB::table('Doccab')
            ->where('CodClie', $cliente_id)
            ->whereIn('Estado', ['PENDIENTE', 'VENCIDO'])
            ->sum('Saldo');

        $credito_disponible = $cliente->CreditLimit - $saldo_actual;
        $porcentaje_utilizado = $cliente->CreditLimit > 0 ? ($saldo_actual / $cliente->CreditLimit) * 100 : 0;

        // Análisis de cumplimiento de pagos
        $cumplimiento = DB::table('Doccab')
            ->select(
                DB::raw('SUM(CASE WHEN Estado = "PAGADO" THEN Total ELSE 0 END) as pagado'),
                DB::raw('SUM(Total) as total')
            )
            ->where('CodClie', $cliente_id)
            ->first();

        $tasa_cumplimiento = $cumplimiento->total > 0 ? ($cumplimiento->pagado / $cumplimiento->total) * 100 : 0;

        // Categorización del riesgo crediticio
        if ($tasa_cumplimiento >= 95 && $porcentaje_utilizado <= 50) {
            $categoria_riesgo = 'BAJO';
        } elseif ($tasa_cumplimiento >= 85 && $porcentaje_utilizado <= 75) {
            $categoria_riesgo = 'MEDIO';
        } elseif ($tasa_cumplimiento >= 70) {
            $categoria_riesgo = 'ALTO';
        } else {
            $categoria_riesgo = 'MUY_ALTO';
        }

        return [
            'saldo_actual' => $saldo_actual,
            'credito_disponible' => $credito_disponible,
            'limite_credito' => $cliente->CreditLimit,
            'porcentaje_utilizado' => round($porcentaje_utilizado, 2),
            'tasa_cumplimiento' => round($tasa_cumplimiento, 2),
            'categoria_riesgo' => $categoria_riesgo,
            'dias_credito' => $cliente->DiasCredito
        ];
    }

    /**
     * ===============================================
     * MÉTODOS DE GESTIÓN Y SEGMENTACIÓN
     * ===============================================
     */

    /**
     * Obtiene clientes por vendedor asignado
     */
    public function porVendedor($vendedor_id)
    {
        $clientes = DB::table('Clientes')
            ->join('// tabla no existe - clientes asignados al contador', 'Clientes.CodClie', '=', '// tabla no existe - clientes asignados al contador.cliente_id')
            ->where('// tabla no existe - clientes asignados al contador.vendedor_id', $vendedor_id)
            ->where('Clientes.Estado', 'ACTIVO')
            ->select(
                'Clientes.*',
                '// tabla no existe - clientes asignados al contador.fecha_asignacion'
            )
            ->get();

        $estadisticas = [
            'total_clientes' => $clientes->count(),
            'ventas_mes' => $this->calcularVentasPorVendedor($vendedor_id),
            'ventas_año' => $this->calcularVentasPorVendedor($vendedor_id, 'año')
        ];

        return compact('clientes', 'estadisticas');
    }

    /**
     * Busca clientes por diferentes criterios
     */
    public function buscar(Request $request)
    {
        $query = DB::table('Clientes')
            ->leftJoin('Doccab', 'Clientes.CodClie', '=', 'Doccab.CodClie')
            ->where('Clientes.Estado', 'ACTIVO');

        // Búsqueda por texto libre
        if ($request->busqueda) {
            $query->where(function($q) use ($request) {
                $q->where('Clientes.Razon', 'like', '%' . $request->busqueda . '%')
                  ->orWhere('Clientes.Ruc', 'like', '%' . $request->busqueda . '%')
                  ->orWhere('Clientes.CodClie', 'like', '%' . $request->busqueda . '%');
            });
        }

        // Filtros por categoría
        if ($request->categoria) {
            $query->where('Clientes.Categoria', $request->categoria);
        }

        // Filtros por distrito
        if ($request->distrito) {
            $query->where('Clientes.Distrito', 'like', '%' . $request->distrito . '%');
        }

        // Filtros por rango de ventas
        if ($request->ventas_min || $request->ventas_max) {
            $query->selectRaw('Clientes.*, SUM(Doccab.Total) as total_ventas')
                  ->groupBy('Clientes.CodClie');
                  
            if ($request->ventas_min) {
                $query->having('total_ventas', '>=', $request->ventas_min);
            }
            if ($request->ventas_max) {
                $query->having('total_ventas', '<=', $request->ventas_max);
            }
        }

        $clientes = $query->orderBy('Clientes.Razon')->paginate($request->per_page ?? 20);

        return $clientes;
    }

    /**
     * ===============================================
     * MÉTODOS DE RECOMENDACIONES Y SEGUIMIENTO
     * ===============================================
     */

    /**
     * Genera recomendaciones personalizadas para un cliente
     */
    public function generarRecomendaciones($cliente_id)
    {
        $estadisticas = $this->calcularEstadisticasCliente($cliente_id);
        $credito = $this->analizarCredito($cliente_id);
        $compras_recientes = DB::table('Doccab')
            ->join('Docdet', 'Doccab.Numero', '=', 'Docdet.Numero')
            ->join('Productos', 'Docdet.Codpro', '=', 'Productos.Codpro')
            ->where('Doccab.CodClie', $cliente_id)
            ->where('Doccab.Fecha', '>=', now()->subMonths(3))
            ->select('Productos.Codpro', 'Productos.Descripcion', DB::raw('SUM(Docdet.Cantidad) as cantidad'))
            ->groupBy('Productos.Codpro', 'Productos.Descripcion')
            ->orderBy('cantidad', 'desc')
            ->take(5)
            ->get();

        $recomendaciones = [];

        // Recomendación por frecuencia de compra
        if ($estadisticas['dias_desde_ultima_compra'] > 30) {
            $recomendaciones[] = [
                'tipo' => 'SEGUIMIENTO',
                'mensaje' => 'Cliente no ha comprado en los últimos ' . $estadisticas['dias_desde_ultima_compra'] . ' días',
                'accion' => 'Contactar para seguimiento de satisfacción',
                'prioridad' => 'ALTA'
            ];
        }

        // Recomendación por productos más comprados
        if ($compras_recientes->isNotEmpty()) {
            $productos_favoritos = $compras_recientes->pluck('Descripcion')->toArray();
            $recomendaciones[] = [
                'tipo' => 'PRODUCTOS',
                'mensaje' => 'Cliente muestra preferencia por: ' . implode(', ', $productos_favoritos),
                'accion' => 'Ofrecer promociones en productos similares',
                'prioridad' => 'MEDIA'
            ];
        }

        // Recomendación por crédito
        if ($credito['porcentaje_utilizado'] < 30 && $credito['tasa_cumplimiento'] > 90) {
            $recomendaciones[] = [
                'tipo' => 'CREDITO',
                'mensaje' => 'Cliente con buen perfil crediticio (cumplimiento: ' . $credito['tasa_cumplimiento'] . '%)',
                'accion' => 'Considerar aumento de límite de crédito',
                'prioridad' => 'BAJA'
            ];
        }

        return $recomendaciones;
    }

    /**
     * Obtiene actividad reciente de un cliente
     */
    public function obtenerActividadReciente($cliente_id)
    {
        $actividades = [];

        // Compras recientes
        $compras = DB::table('Doccab')
            ->select('Numero', 'Fecha', 'Total', 'Estado')
            ->where('CodClie', $cliente_id)
            ->where('Fecha', '>=', now()->subMonths(6))
            ->orderBy('Fecha', 'desc')
            ->take(10)
            ->get()
            ->map(function($compra) {
                return [
                    'tipo' => 'COMPRA',
                    'descripcion' => "Compra #{$compra->Numero} - {$compra->Total} - {$compra->Estado}",
                    'fecha' => $compra->Fecha,
                    'icono' => 'shopping-cart'
                ];
            });

        // Actividades de sistema
        $actividades_sistema = DB::table('// tabla no existe')
            ->where('cliente_id', $cliente_id)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->map(function($actividad) {
                return [
                    'tipo' => strtoupper($actividad->accion),
                    'descripcion' => $actividad->descripcion,
                    'fecha' => $actividad->created_at,
                    'icono' => 'cog'
                ];
            });

        // Combinar y ordenar por fecha
        $actividades = $actividades_sistema->concat($compras)
            ->sortByDesc('fecha')
            ->take(15)
            ->values();

        return $actividades;
    }

    /**
     * ===============================================
     * MÉTODOS DE VALIDACIÓN Y UTILIDADES
     * ===============================================
     */

    /**
     * Valida datos del cliente
     */
    public function validarDatosCliente(Request $request, $cliente_id = null)
    {
        $rules = [
            'razon_social' => 'required|string|min:3|max:255',
            'ruc' => 'required|string|size:11|unique:Clientes,Ruc' . ($cliente_id ? ',' . $cliente_id : ''),
            'direccion' => 'required|string|min:5|max:500',
            'distrito' => 'required|string|min:2|max:100',
            'provincia' => 'required|string|min:2|max:100',
            'departamento' => 'required|string|min:2|max:100',
            'telefono' => 'nullable|string|min:6|max:20',
            'email' => 'nullable|email|unique:Clientes,Email' . ($cliente_id ? ',' . $cliente_id : ''),
            'contacto' => 'nullable|string|min:2|max:100',
            'categoria' => 'nullable|in:BASICO,REGULAR,PREMIUM,VIP',
            'limite_credito' => 'nullable|numeric|min:0',
            'dias_credito' => 'nullable|integer|min:0|max:365'
        ];

        $validator = \Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return [
                'errors' => $validator->errors(),
                'valid' => false
            ];
        }

        return ['valid' => true, 'errors' => null];
    }

    /**
     * Genera código único de cliente
     */
    public function generarCodigoCliente()
    {
        $ultimo = DB::table('Clientes')
            ->select('CodClie')
            ->orderBy('CodClie', 'desc')
            ->first();

        $numero = $ultimo ? (int) substr($ultimo->CodClie, 2) + 1 : 1;
        return 'CL' . str_pad($numero, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Registra actividad de cliente
     */
    public function registrarActividad($cliente_id, $accion, $descripcion)
    {
        DB::table('// tabla no existe')->insert([
            'cliente_id' => $cliente_id,
            'accion' => $accion,
            'descripcion' => $descripcion,
            'user_id' => Auth::id(),
            'created_at' => now()
        ]);
    }

    /**
     * ===============================================
     * MÉTODOS DE CONSULTA Y FILTROS
     * ===============================================
     */

    /**
     * Obtiene filtros disponibles para búsqueda
     */
    public function obtenerFiltros(Request $request)
    {
        return [
            'categoria' => $request->categoria,
            'estado' => $request->estado ?? 'ACTIVO',
            'distrito' => $request->distrito,
            'provincia' => $request->provincia,
            'vendedor' => $request->vendedor,
            'fecha_desde' => $request->fecha_desde,
            'fecha_hasta' => $request->fecha_hasta,
            'ventas_min' => $request->ventas_min,
            'ventas_max' => $request->ventas_max
        ];
    }

    /**
     * Consulta principal de clientes con filtros
     */
    public function consultarClientes($filtros)
    {
        $query = DB::table('Clientes')
            ->leftJoin('Doccab', 'Clientes.CodClie', '=', 'Doccab.CodClie')
            ->where('Clientes.Estado', '!=', 'ELIMINADO')
            ->select('Clientes.*', DB::raw('SUM(Doccab.Total) as total_ventas'));

        // Aplicar filtros
        if ($filtros['categoria']) {
            $query->where('Clientes.Categoria', $filtros['categoria']);
        }

        if ($filtros['estado']) {
            $query->where('Clientes.Estado', $filtros['estado']);
        }

        if ($filtros['distrito']) {
            $query->where('Clientes.Distrito', 'like', '%' . $filtros['distrito'] . '%');
        }

        if ($filtros['provincia']) {
            $query->where('Clientes.Provincia', 'like', '%' . $filtros['provincia'] . '%');
        }

        // Filtros de fechas
        if ($filtros['fecha_desde']) {
            $query->where('Clientes.created_at', '>=', $filtros['fecha_desde']);
        }

        if ($filtros['fecha_hasta']) {
            $query->where('Clientes.created_at', '<=', $filtros['fecha_hasta']);
        }

        // Filtros de ventas
        if ($filtros['ventas_min']) {
            $query->having('total_ventas', '>=', $filtros['ventas_min']);
        }

        if ($filtros['ventas_max']) {
            $query->having('total_ventas', '<=', $filtros['ventas_max']);
        }

        $query->groupBy('Clientes.CodClie')
               ->orderBy('Clientes.Razon');

        return $query->paginate(25);
    }

    /**
     * Obtiene historial de compras de un cliente
     */
    public function obtenerHistorialCompras($cliente_id)
    {
        return DB::table('Doccab')
            ->join('Docdet', 'Doccab.Numero', '=', 'Docdet.Numero')
            ->join('Productos', 'Docdet.Codpro', '=', 'Productos.Codpro')
            ->where('Doccab.CodClie', $cliente_id)
            ->select(
                'Doccab.Numero',
                'Doccab.Fecha',
                'Doccab.Total',
                'Doccab.Estado',
                'Productos.Descripcion',
                'Docdet.Cantidad',
                'Docdet.Precio'
            )
            ->orderBy('Doccab.Fecha', 'desc')
            ->take(50)
            ->get();
    }

    /**
     * ===============================================
     * MÉTODOS DE EXPORTACIÓN Y REPORTES
     * ===============================================
     */

    /**
     * Exporta datos de clientes
     */
    public function exportar(Request $request)
    {
        $filtros = $this->obtenerFiltros($request);
        $clientes = $this->consultarClientes($filtros);

        // Aquí se implementaría la lógica de exportación
        // Por ahora retornamos la consulta para que el frontend maneje la exportación
        
        return [
            'clientes' => $clientes->items(),
            'total' => $clientes->total(),
            'filtros_aplicados' => $filtros
        ];
    }

    /**
     * Genera reporte de clientes VIP
     */
    public function reporteClientesVip()
    {
        return DB::table('Doccab')
            ->join('Clientes', 'Doccab.CodClie', '=', 'Clientes.CodClie')
            ->where('Clientes.Categoria', 'VIP')
            ->select(
                'Clientes.CodClie',
                'Clientes.Razon',
                'Clientes.Ruc',
                'Clientes.Telefono',
                'Clientes.Email',
                DB::raw('SUM(Doccab.Total) as total_compras'),
                DB::raw('COUNT(Doccab.Numero) as cantidad_compras'),
                DB::raw('AVG(Doccab.Total) as ticket_promedio')
            )
            ->where('Doccab.Fecha', '>=', now()->subYear())
            ->groupBy('Clientes.CodClie', 'Clientes.Razon', 'Clientes.Ruc', 'Clientes.Telefono', 'Clientes.Email')
            ->orderBy('total_compras', 'desc')
            ->get();
    }

    /**
     * ===============================================
     * MÉTODOS PRIVADOS DE APOYO
     * ===============================================
     */

    /**
     * Calcula ventas por vendedor
     */
    private function calcularVentasPorVendedor($vendedor_id, $periodo = 'mes')
    {
        $where_fecha = match($periodo) {
            'dia' => now(),
            'mes' => now()->subDays(30),
            'año' => now()->subDays(365),
            default => now()->subDays(30)
        };

        return DB::table('Doccab')
            ->join('Clientes', 'Doccab.CodClie', '=', 'Clientes.CodClie')
            ->join('// tabla no existe - clientes asignados al contador', 'Clientes.CodClie', '=', '// tabla no existe - clientes asignados al contador.cliente_id')
            ->where('// tabla no existe - clientes asignados al contador.vendedor_id', $vendedor_id)
            ->where('Doccab.Fecha', '>=', $where_fecha)
            ->sum('Doccab.Total');
    }

    /**
     * ===============================================
     * API ENDPOINTS ESPECIALIZADOS
     * ===============================================
     */

    /**
     * API: Busca cliente por RUC
     */
    public function buscarPorRuc($ruc)
    {
        $cliente = DB::table('Clientes')
            ->where('Ruc', $ruc)
            ->first();

        if (!$cliente) {
            return response()->json(['error' => 'Cliente no encontrado'], 404);
        }

        return $cliente;
    }

    /**
     * API: Obtiene resumen de cliente para facturación
     */
    public function resumenParaFacturacion($cliente_id)
    {
        $cliente = DB::table('Clientes')
            ->where('CodClie', $cliente_id)
            ->first();

        if (!$cliente) {
            return response()->json(['error' => 'Cliente no encontrado'], 404);
        }

        $credito = $this->analizarCredito($cliente_id);

        return [
            'cliente' => $cliente,
            'credito_disponible' => $credito['credito_disponible'],
            'puede_credito' => $credito['porcentaje_utilizado'] < 90,
            'dias_credito' => $cliente->DiasCredito,
            'ultima_compra' => $this->obtenerActividadReciente($cliente_id)->first()
        ];
    }

    /**
     * API: Obtiene sugerencias de clientes para autocomplete
     */
    public function sugerencias(Request $request)
    {
        $term = $request->term;
        
        $clientes = DB::table('Clientes')
            ->where('Estado', 'ACTIVO')
            ->where(function($q) use ($term) {
                $q->where('Razon', 'like', '%' . $term . '%')
                  ->orWhere('Ruc', 'like', '%' . $term . '%');
            })
            ->select('CodClie', 'Razon', 'Ruc')
            ->limit(10)
            ->get();

        return $clientes;
    }
}