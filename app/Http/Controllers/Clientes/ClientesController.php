<?php

namespace App\Http\Controllers\Clientes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ClientesController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    /**
     * Lista todos los clientes con filtros, estadísticas y segmentación
     */
    public function index(Request $request)
    {
        $filtros = $this->obtenerFiltros($request);
        $clientes = $this->consultarClientes($filtros);
        $estadisticas = $this->calcularEstadisticas();
        $segmentacion = $this->analizarSegmentacionClientes();

        return view('clientes.index', compact('clientes', 'estadisticas', 'segmentacion', 'filtros'));
    }

    /**
     * Obtiene detalles completos de un cliente
     */
    public function show($id)
    {
        $cliente = DB::table('Clientes')->where('Codclie', $id)->first();
        if (!$cliente) {
            return response()->json(['error' => 'Cliente no encontrado'], 404);
        }

        // Si la solicitud es AJAX o tiene header Accept: application/json, devuelve JSON
        if (request()->expectsJson()) {
            $datos_completos = [
                'cliente' => $cliente,
                'compras' => $this->obtenerHistorialCompras($id),
                'estadisticas' => $this->calcularEstadisticasCliente($id),
                'credito' => $this->analizarCredito($id),
                'recomendaciones' => $this->generarRecomendaciones($id),
                'actividad' => $this->obtenerActividadReciente($id)
            ];
            return response()->json($datos_completos);
        }

        // De lo contrario, muestra la vista
        return view('clientes.show', [
            'cliente' => $cliente,
            'compras' => $this->obtenerHistorialCompras($id),
            'estadisticas' => $this->calcularEstadisticasCliente($id),
            'credito' => $this->analizarCredito($id),
            'recomendaciones' => $this->generarRecomendaciones($id),
            'actividad' => $this->obtenerActividadReciente($id)
        ]);
    }

    /**
     * Crea un nuevo cliente
     */
    public function store(Request $request)
    {
        $request->validate([
            'Razon' => 'required|string|max:80',
            'Documento' => 'required|string|max:12|unique:Clientes,Documento',
            'Direccion' => 'nullable|string|max:60',
            'Telefono1' => 'nullable|string|max:10',
            'Email' => 'nullable|email|max:100|unique:Clientes,Email',
            'TipoClie' => 'nullable|integer',
            'Vendedor' => 'nullable|integer',
            'Limite' => 'nullable|numeric|min:0'
        ]);

        try {
            DB::beginTransaction();

            $cliente_id = DB::table('Clientes')->insertGetId([
                'Razon' => $request->Razon,
                'Documento' => $request->Documento,
                'Direccion' => $request->Direccion,
                'Telefono1' => $request->Telefono1,
                'Email' => $request->Email,
                'TipoClie' => $request->TipoClie ?? 1,
                'Vendedor' => $request->Vendedor,
                'Limite' => $request->Limite ?? 0,
                'Activo' => 1,
                'Fecha' => now(),
                'Maymin' => 0,
                'Zona' => '001'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'mensaje' => 'Cliente creado exitosamente',
                'cliente_id' => $cliente_id
            ], 201);

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
        $cliente = DB::table('Clientes')->where('Codclie', $id)->first();
        if (!$cliente) {
            return response()->json(['error' => 'Cliente no encontrado'], 404);
        }

        $request->validate([
            'Razon' => 'required|string|max:80',
            'Documento' => 'required|string|max:12|unique:Clientes,Documento,' . $id . ',Codclie',
            'Direccion' => 'nullable|string|max:60',
            'Telefono1' => 'nullable|string|max:10',
            'Email' => 'nullable|email|max:100|unique:Clientes,Email,' . $id . ',Codclie',
            'TipoClie' => 'nullable|integer',
            'Vendedor' => 'nullable|integer',
            'Limite' => 'nullable|numeric|min:0'
        ]);

        try {
            DB::beginTransaction();

            DB::table('Clientes')->where('Codclie', $id)->update([
                'Razon' => $request->Razon,
                'Documento' => $request->Documento,
                'Direccion' => $request->Direccion,
                'Telefono1' => $request->Telefono1,
                'Email' => $request->Email,
                'TipoClie' => $request->TipoClie ?? 1,
                'Vendedor' => $request->Vendedor,
                'Limite' => $request->Limite ?? 0
            ]);

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
     * Desactiva un cliente (marca como inactivo)
     */
    public function destroy($id)
    {
        $cliente = DB::table('Clientes')->where('Codclie', $id)->first();
        if (!$cliente) {
            return response()->json(['error' => 'Cliente no encontrado'], 404);
        }

        $facturas_pendientes = DB::table('Doccab')
            ->where('CodClie', $id)
            ->where('Eliminado', 0)
            ->count();

        if ($facturas_pendientes > 0) {
            return response()->json([
                'error' => 'No se puede desactivar cliente. Tiene facturas activas'
            ], 400);
        }

        DB::table('Clientes')->where('Codclie', $id)->update([
            'Activo' => 0
        ]);

        return response()->json([
            'success' => true,
            'mensaje' => 'Cliente desactivado exitosamente'
        ]);
    }

    /**
     * Calcula estadísticas generales de clientes
     */
    public function calcularEstadisticas()
    {
        $total_clientes = DB::table('Clientes')->count();
        $clientes_activos = DB::table('Clientes')->where('Activo', 1)->count();
        $clientes_nuevos_mes = DB::table('Clientes')
            ->whereRaw("MONTH(Fecha) = ?", [now()->month])
            ->whereRaw("YEAR(Fecha) = ?", [now()->year])
            ->count();
        
        $ventas_totales = DB::table('Doccab')
            ->join('Clientes', 'Doccab.CodClie', '=', 'Clientes.Codclie')
            ->where('Clientes.Activo', 1)
            ->sum('Doccab.Total');

        $ticket_promedio = DB::table('Doccab')
            ->join('Clientes', 'Doccab.CodClie', '=', 'Clientes.Codclie')
            ->where('Clientes.Activo', 1)
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
        $por_tipo = DB::table('Clientes')
            ->select('TipoClie', DB::raw('COUNT(*) as cantidad'))
            ->where('Activo', 1)
            ->groupBy('TipoClie')
            ->get();

        $por_valor = DB::table('Doccab')
            ->join('Clientes', 'Doccab.CodClie', '=', 'Clientes.Codclie')
            ->select(
                'Clientes.Codclie',
                'Clientes.Razon',
                DB::raw('SUM(Doccab.Total) as total_compras')
            )
            ->where('Clientes.Activo', 1)
            ->where('Doccab.Fecha', '>=', now()->subYear())
            ->groupBy('Clientes.Codclie', 'Clientes.Razon')
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

        $por_frecuencia = DB::table('Doccab')
            ->join('Clientes', 'Doccab.CodClie', '=', 'Clientes.Codclie')
            ->select(
                'Clientes.Codclie',
                'Clientes.Razon',
                DB::raw('COUNT(Doccab.Numero) as frecuencia')
            )
            ->where('Clientes.Activo', 1)
            ->where('Doccab.Fecha', '>=', now()->subMonths(6))
            ->groupBy('Clientes.Codclie', 'Clientes.Razon')
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

        return compact('por_tipo', 'por_valor', 'por_frecuencia');
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
            ->whereRaw("MONTH(Fecha) = ?", [now()->month])
            ->whereRaw("YEAR(Fecha) = ?", [now()->year])
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
     * Analiza situación crediticia de un cliente
     */
    public function analizarCredito($cliente_id)
    {
        $cliente = DB::table('Clientes')->where('Codclie', $cliente_id)->first();
        
        $saldo_actual = DB::table('Doccab')
            ->where('CodClie', $cliente_id)
            ->where('Eliminado', 0)
            ->sum('Total');

        $limite = $cliente->Limite ?? 0;
        $credito_disponible = max(0, $limite - $saldo_actual);
        $porcentaje_utilizado = $limite > 0 ? ($saldo_actual / $limite) * 100 : 0;

        $total_operaciones = DB::table('Doccab')->where('CodClie', $cliente_id)->count();
        $tasa_cumplimiento = $total_operaciones > 0 ? 100 : 0;

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
            'saldo_actual' => round($saldo_actual, 2),
            'credito_disponible' => round($credito_disponible, 2),
            'limite_credito' => round($limite, 2),
            'porcentaje_utilizado' => round($porcentaje_utilizado, 2),
            'tasa_cumplimiento' => round($tasa_cumplimiento, 2),
            'categoria_riesgo' => $categoria_riesgo,
            'dias_credito' => $cliente->Dias ?? 0
        ];
    }

    /**
     * Busca clientes por diferentes criterios
     */
    public function buscar(Request $request)
    {
        $query = DB::table('Clientes')
            ->leftJoin('Doccab', 'Clientes.Codclie', '=', 'Doccab.CodClie')
            ->where('Clientes.Activo', 1);

        if ($request->busqueda) {
            $query->where(function($q) use ($request) {
                $q->where('Clientes.Razon', 'like', '%' . $request->busqueda . '%')
                  ->orWhere('Clientes.Documento', 'like', '%' . $request->busqueda . '%')
                  ->orWhere('Clientes.Codclie', 'like', '%' . $request->busqueda . '%');
            });
        }

        if ($request->tipo_clie) {
            $query->where('Clientes.TipoClie', $request->tipo_clie);
        }

        if ($request->ventas_min || $request->ventas_max) {
            $query->selectRaw('Clientes.*, SUM(Doccab.Total) as total_ventas')
                  ->groupBy('Clientes.Codclie');
                  
            if ($request->ventas_min) {
                $query->having('total_ventas', '>=', $request->ventas_min);
            }
            if ($request->ventas_max) {
                $query->having('total_ventas', '<=', $request->ventas_max);
            }
        }

        $clientes = $query->orderBy('Clientes.Razon')->paginate($request->per_page ?? 20);

        return response()->json($clientes);
    }

    /**
     * Genera recomendaciones personalizadas para un cliente
     */
    public function generarRecomendaciones($cliente_id)
    {
        $estadisticas = $this->calcularEstadisticasCliente($cliente_id);
        $credito = $this->analizarCredito($cliente_id);
        $compras_recientes = DB::table('Doccab')
            ->join('Docdet', 'Doccab.Numero', '=', 'Docdet.Numero')
            ->join('Productos', 'Docdet.Codpro', '=', 'Productos.CodPro')
            ->where('Doccab.CodClie', $cliente_id)
            ->where('Doccab.Fecha', '>=', now()->subMonths(3))
            ->select('Productos.Codpro', 'Productos.Nombre', DB::raw('SUM(Docdet.Cantidad) as cantidad'))
            ->groupBy('Productos.Codpro', 'Productos.Nombre')
            ->orderBy('cantidad', 'desc')
            ->take(5)
            ->get();

        $recomendaciones = [];

        if ($estadisticas['dias_desde_ultima_compra'] > 30) {
            $recomendaciones[] = [
                'tipo' => 'SEGUIMIENTO',
                'mensaje' => 'Cliente no ha comprado en los últimos ' . $estadisticas['dias_desde_ultima_compra'] . ' días',
                'accion' => 'Contactar para seguimiento de satisfacción',
                'prioridad' => 'ALTA'
            ];
        }

        if ($compras_recientes->isNotEmpty()) {
            $productos_favoritos = $compras_recientes->pluck('Nombre')->toArray();
            $recomendaciones[] = [
                'tipo' => 'PRODUCTOS',
                'mensaje' => 'Cliente muestra preferencia por: ' . implode(', ', $productos_favoritos),
                'accion' => 'Ofrecer promociones en productos similares',
                'prioridad' => 'MEDIA'
            ];
        }

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
        $compras = DB::table('Doccab')
            ->select('Numero', 'Fecha', 'Total')
            ->where('CodClie', $cliente_id)
            ->where('Fecha', '>=', now()->subMonths(6))
            ->orderBy('Fecha', 'desc')
            ->take(10)
            ->get()
            ->map(function($compra) {
                return [
                    'tipo' => 'COMPRA',
                    'descripcion' => "Compra #{$compra->Numero} - S/ " . number_format($compra->Total, 2),
                    'fecha' => $compra->Fecha,
                    'icono' => 'shopping-cart'
                ];
            });

        return $compras->values();
    }

    /**
     * Obtiene filtros disponibles para búsqueda
     */
    public function obtenerFiltros(Request $request)
    {
        return [
            'tipo_clie' => $request->tipo_clie,
            'busqueda' => $request->busqueda,
            'ventas_min' => $request->ventas_min,
            'ventas_max' => $request->ventas_max
        ];
    }

    /**
     * Consulta principal de clientes con filtros
     */
    public function consultarClientes($filtros)
    {
        $columns = [
            'Clientes.Codclie',
            'Clientes.tipoDoc',
            'Clientes.Documento',
            'Clientes.Razon',
            'Clientes.Direccion',
            'Clientes.Telefono1',
            'Clientes.Telefono2',
            'Clientes.Fax',
            'Clientes.Celular',
            'Clientes.Nextel',
            'Clientes.Maymin',
            'Clientes.Fecha',
            'Clientes.Zona',
            'Clientes.TipoNeg',
            'Clientes.TipoClie',
            'Clientes.Vendedor',
            'Clientes.Email',
            'Clientes.Limite',
            'Clientes.Activo'
        ];

        $query = DB::table('Clientes')
            ->leftJoin('Doccab', 'Clientes.Codclie', '=', 'Doccab.CodClie')
            ->where('Clientes.Activo', 1)
            ->select(array_merge($columns, [DB::raw('SUM(Doccab.Total) as total_ventas')]))
            ->groupBy($columns);

        // Aplicar filtros
        if (!empty($filtros['tipo_clie'])) {
            $query->where('Clientes.TipoClie', $filtros['tipo_clie']);
        }

        if (!empty($filtros['busqueda'])) {
            $query->where(function($q) use ($filtros) {
                $q->where('Clientes.Razon', 'like', '%' . $filtros['busqueda'] . '%')
                ->orWhere('Clientes.Documento', 'like', '%' . $filtros['busqueda'] . '%');
            });
        }

        if (!empty($filtros['ventas_min'])) {
            $query->having('total_ventas', '>=', $filtros['ventas_min']);
        }

        if (!empty($filtros['ventas_max'])) {
            $query->having('total_ventas', '<=', $filtros['ventas_max']);
        }

        return $query->orderBy('Clientes.Razon')->paginate(25);
    }

    /**
     * Obtiene historial de compras de un cliente
     */
    public function obtenerHistorialCompras($cliente_id)
    {
        return DB::table('Doccab')
            ->join('Docdet', 'Doccab.Numero', '=', 'Docdet.Numero')
            ->join('Productos', 'Docdet.Codpro', '=', 'Productos.CodPro')
            ->where('Doccab.CodClie', $cliente_id)
            ->select(
                'Doccab.Numero',
                'Doccab.Fecha',
                'Doccab.Total',
                'Productos.Nombre as Producto',
                'Docdet.Cantidad',
                'Docdet.Precio'
            )
            ->orderBy('Doccab.Fecha', 'desc')
            ->take(50)
            ->get();
    }

    /**
     * Exporta datos de clientes
     */
    public function exportar(Request $request)
    {
        $filtros = $this->obtenerFiltros($request);
        $clientes = $this->consultarClientes($filtros);

        return response()->json([
            'clientes' => $clientes->items(),
            'total' => $clientes->total(),
            'filtros_aplicados' => $filtros
        ]);
    }

    /**
     * Genera reporte de clientes VIP
     */
    public function reporteClientesVip()
    {
        return DB::table('Doccab')
            ->join('Clientes', 'Doccab.CodClie', '=', 'Clientes.Codclie')
            ->where('Clientes.TipoClie', 4)
            ->select(
                'Clientes.Codclie',
                'Clientes.Razon',
                'Clientes.Documento',
                'Clientes.Telefono1',
                'Clientes.Email',
                DB::raw('SUM(Doccab.Total) as total_compras'),
                DB::raw('COUNT(Doccab.Numero) as cantidad_compras'),
                DB::raw('AVG(Doccab.Total) as ticket_promedio')
            )
            ->where('Doccab.Fecha', '>=', now()->subYear())
            ->groupBy('Clientes.Codclie', 'Clientes.Razon', 'Clientes.Documento', 'Clientes.Telefono1', 'Clientes.Email')
            ->orderBy('total_compras', 'desc')
            ->get();
    }

    /**
     * API: Busca cliente por RUC/DNI
     */
    public function buscarPorRuc($ruc)
    {
        $cliente = DB::table('Clientes')
            ->where('Documento', $ruc)
            ->first();

        if (!$cliente) {
            return response()->json(['error' => 'Cliente no encontrado'], 404);
        }

        return response()->json($cliente);
    }

    /**
     * API: Obtiene resumen de cliente para facturación
     */
    public function resumenParaFacturacion($cliente_id)
    {
        $cliente = DB::table('Clientes')
            ->where('Codclie', $cliente_id)
            ->first();

        if (!$cliente) {
            return response()->json(['error' => 'Cliente no encontrado'], 404);
        }

        $credito = $this->analizarCredito($cliente_id);

        return response()->json([
            'cliente' => $cliente,
            'credito_disponible' => $credito['credito_disponible'],
            'puede_credito' => $credito['porcentaje_utilizado'] < 90,
            'dias_credito' => $credito['dias_credito'],
            'ultima_compra' => $this->obtenerActividadReciente($cliente_id)->first()
        ]);
    }

    /**
     * API: Obtiene sugerencias de clientes para autocomplete
     */
    public function sugerencias(Request $request)
    {
        $term = $request->term;
        
        $clientes = DB::table('Clientes')
            ->where('Activo', 1)
            ->where(function($q) use ($term) {
                $q->where('Razon', 'like', '%' . $term . '%')
                  ->orWhere('Documento', 'like', '%' . $term . '%');
            })
            ->select('Codclie', 'Razon', 'Documento')
            ->limit(10)
            ->get();

        return response()->json($clientes);
    }
    /**
 * Muestra la vista para crear un nuevo cliente
 */
    public function crearVista()
    {
        return view('clientes.crear');
    }

    /**
     * Muestra la vista de búsqueda avanzada
     */
    public function vistaBusqueda()
    {
        return view('clientes.buscar');
    }

    /**
     * Muestra la vista para editar un cliente
     */
    public function editarVista($id)
    {
        $cliente = DB::table('Clientes')->where('Codclie', $id)->first();
        if (!$cliente) {
            abort(404);
        }
        return view('clientes.editar', compact('cliente'));
    }
}