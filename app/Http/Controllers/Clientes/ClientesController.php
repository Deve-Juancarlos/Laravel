<?php

namespace App\Http\Controllers\Clientes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Services\ReniecService;
use Illuminate\Support\Facades\Log;

class ClientesController extends Controller
{
    protected $connection = 'sqlsrv';
    protected $reniecService;
    
    public function __construct(ReniecService $reniecService)
    {
        $this->middleware(['auth']);
        $this->reniecService = $reniecService;
    }

    public function search(Request $request)
    {
        // El JS del Paso 1 envía 'query'
        $query = trim($request->input('query', '')); 

        if (strlen($query) < 3) {
            return response()->json(['clientes' => [], 'fuente' => 'ninguno', 'mensaje' => 'Debe ingresar al menos 3 caracteres.']);
        }

        try {
            // 1. Buscar en la base de datos local
            $local = $this->reniecService->buscarEnBaseLocal($query);

            if ($local['encontrados'] > 0) {
                
                // Mapeamos los clientes para añadir la deuda
                $clientesConDeuda = $local['clientes']->map(function ($cliente) {
                    $cliente->deuda_total = $this->getDeudaCliente($cliente->Codclie);
                    return $cliente;
                });

                return response()->json([
                    'clientes' => $clientesConDeuda,
                    'fuente' => 'local'
                ]);
            }

            // 2. Si no se encuentra, buscar en API
            $esDniValido = $this->reniecService->validarDNI($query)['valido'];
            $esRucValido = $this->reniecService->validarRUC($query)['valido'];

            if ($esDniValido) {
                $apiData = $this->reniecService->consultarDNI($query);
                if ($apiData) {
                    return response()->json([
                        'clientes' => [$apiData], 
                        'fuente' => 'reniec'
                    ]);
                }
            }

            if ($esRucValido) {
                $apiData = $this->reniecService->consultarRUC($query);
                if ($apiData) {
                    return response()->json([
                        'clientes' => [$apiData],
                        'fuente' => 'reniec'
                    ]);
                }
            }

            // 3. Si no se encuentra en ningún lado
            return response()->json([
                'clientes' => [],
                'fuente' => 'ninguno',
                'mensaje' => 'No se encontraron resultados en BD ni en RENIEC/SUNAT.'
            ]);

        } catch (\Exception $e) {
            Log::error('Error fatal en ClientesController@search: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json(['clientes' => [], 'fuente' => 'ninguno', 'mensaje' => 'Error del servidor: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Helper privado para calcular la deuda de un cliente.
     */
    private function getDeudaCliente($cliente_id)
    {
        $saldo_actual = DB::connection($this->connection)->table('CtaCliente')
            ->where('CodClie', $cliente_id)
            ->sum('Saldo');

        return round($saldo_actual, 2) ?? 0.00;
    }


    // -------------------------------------------------------------------
    // --- AQUÍ VA EL RESTO DE TU CONTROLADOR (Copiado de tu archivo) ---
    // -------------------------------------------------------------------

    public function index(Request $request)
    {
        $filtros = $this->obtenerFiltros($request);
        $clientes = $this->consultarClientes($filtros);
        $estadisticas = $this->calcularEstadisticas(); // <-- Esta variable SÍ existe
        $segmentacion = $this->analizarSegmentacionClientes();

        
        $resumenGeneral = [
             'total_clientes' => $estadisticas['total_clientes'],
             'clientes_activos' => $estadisticas['clientes_activos'],
             'total_cartera' => $this->obtenerTotalCartera(),
             'mayor_deudor' => $this->obtenerMayorDeudor()
        ];
        
        // Esta variable NO existía y causaba el error
        $saldosPorCliente = [];
        $clienteIds = $clientes->pluck('Codclie')->filter()->unique()->values()->all();
        if (!empty($clienteIds)) {
             $saldos = DB::connection($this->connection)->table('CtaCliente')
                 ->whereIn('CodClie', $clienteIds)
                 ->select('CodClie', DB::raw('SUM(Saldo) as saldo_pendiente'))
                 ->groupBy('CodClie')
                 ->get()
                 ->keyBy('CodClie');

             foreach ($clientes as $clienteItem) {
                 $saldosPorCliente[$clienteItem->Codclie] = $saldos->get($clienteItem->Codclie) ?: (object)['saldo_pendiente' => 0];
             }
        }

        return view('clientes.index', compact(
            'clientes', 
            'estadisticas', // Pasamos esta también
            'segmentacion', 
            'filtros',
            'resumenGeneral', 
            'saldosPorCliente' 
        ));
    }

    private function obtenerMayorDeudor()
    {
        return DB::connection($this->connection)->table('CtaCliente as cc')
            ->leftJoin('Clientes as c', 'cc.CodClie', '=', 'c.Codclie')
            ->where('cc.Saldo', '>', 0)
            ->select('c.Razon', DB::raw('SUM(cc.Saldo) as SaldoTotal'))
            ->groupBy('c.Razon')
            ->orderBy('SaldoTotal', 'desc')
            ->first();
    }

    public function show($id)
    {
        $cliente = DB::connection($this->connection)->table('Clientes')->where('Codclie', $id)->first();
        if (!$cliente) {
             return redirect()->route('contador.clientes.index')->with('error', 'Cliente no encontrado');
        }

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

        return view('clientes.show', [
            'cliente' => $cliente,
            'compras' => $this->obtenerHistorialCompras($id),
            'estadisticas' => $this->calcularEstadisticasCliente($id),
            'credito' => $this->analizarCredito($id),
            'recomendaciones' => $this->generarRecomendaciones($id),
            'actividad' => $this->obtenerActividadReciente($id)
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'Razon' => 'required|string|max:80',
            'Documento' => 'required|string|max:12|unique:sqlsrv.Clientes,Documento',
            'Direccion' => 'nullable|string|max:60',
            'Telefono1' => 'nullable|string|max:10',
            'Email' => 'nullable|email|max:100|unique:sqlsrv.Clientes,Email',
            'TipoClie' => 'nullable|integer',
            'Vendedor' => 'nullable|integer',
            'Limite' => 'nullable|numeric|min:0'
        ]);

        try {
            DB::connection($this->connection)->beginTransaction();

            $cliente_id = DB::connection($this->connection)
                ->table('Clientes')
                ->insertGetId([
                    'Razon' => strtoupper($request->Razon),
                    'Documento' => $request->Documento,
                    'Direccion' => strtoupper($request->Direccion),
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

            DB::connection($this->connection)->commit();

            return redirect()
                ->route('contador.clientes.index')
                ->with('success', 'Cliente ' . $request->Razon . ' creado exitosamente.');

        } catch (\Exception $e) {
            DB::connection($this->connection)->rollback();
            \Log::error("Error al crear cliente: " . $e->getMessage());
            
            return redirect()
                ->back()
                ->with('error', 'Error al crear cliente: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function update(Request $request, $id)
    {
        $cliente = DB::connection($this->connection)
            ->table('Clientes')
            ->where('Codclie', $id)
            ->first();
        
        if (!$cliente) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Cliente no encontrado'
            ], 404);
        }

        $request->validate([
            'Razon' => 'required|string|max:80',
            'Documento' => 'required|string|max:12|unique:sqlsrv.Clientes,Documento,' . $id . ',Codclie',
            'Direccion' => 'nullable|string|max:60',
            'Telefono1' => 'nullable|string|max:10',
            'Celular' => 'nullable|string|max:10',
            'Email' => 'nullable|email|max:100',
            'TipoClie' => 'nullable|integer',
            'Vendedor' => 'nullable|integer', // ✅ Se guardará en Clientes.Vendedor
            'Limite' => 'nullable|numeric|min:0'
        ]);

        try {
            DB::connection($this->connection)->beginTransaction();

            // ✅ Actualizar cliente
            DB::connection($this->connection)
                ->table('Clientes')
                ->where('Codclie', $id)
                ->update([
                    'Razon' => strtoupper($request->Razon),
                    'Documento' => $request->Documento,
                    'Direccion' => strtoupper($request->Direccion),
                    'Telefono1' => $request->Telefono1,
                    'Celular' => $request->Celular,
                    'Email' => $request->Email,
                    'TipoClie' => $request->TipoClie ?? 1,
                    'Vendedor' => $request->Vendedor, // Campo correcto en Clientes
                    'Limite' => $request->Limite ?? 0,
                ]);

            DB::connection($this->connection)->commit();
            
            // Respuesta exitosa
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'mensaje' => 'Cliente actualizado exitosamente'
                ]);
            }
            
            return redirect()
                ->route('contador.clientes.show', $id)
                ->with('success', 'Cliente actualizado exitosamente.');

        } catch (\Exception $e) {
            DB::connection($this->connection)->rollback();
            \Log::error("Error al actualizar cliente: " . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Error al actualizar: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()
                ->back()
                ->with('error', 'Error al actualizar cliente: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy($id)
    {
        $cliente = DB::connection($this->connection)->table('Clientes')->where('Codclie', $id)->first();
        if (!$cliente) {
            return response()->json(['error' => 'Cliente no encontrado'], 404);
        }

        $facturas_pendientes = DB::connection($this->connection)->table('CtaCliente') // Corregido a CtaCliente
            ->where('CodClie', $id)
            ->where('Saldo', '>', 0)
            ->count();

        if ($facturas_pendientes > 0) {
            return response()->json([
                'error' => 'No se puede desactivar cliente. Tiene saldos pendientes.'
            ], 400);
        }

        DB::connection($this->connection)->table('Clientes')->where('Codclie', $id)->update([
            'Activo' => 0
        ]);

        return response()->json([
            'success' => true,
            'mensaje' => 'Cliente desactivado exitosamente'
        ]);
    }

    public function calcularEstadisticas()
    {
        $total_clientes = DB::connection($this->connection)->table('Clientes')->count();
        $clientes_activos = DB::connection($this->connection)->table('Clientes')->where('Activo', 1)->count();
        $clientes_nuevos_mes = DB::connection($this->connection)->table('Clientes')
            ->whereRaw("MONTH(Fecha) = ?", [now()->month])
            ->whereRaw("YEAR(Fecha) = ?", [now()->year])
            ->count();
        
        $ventas_totales = DB::connection($this->connection)->table('Doccab')
            ->join('Clientes', 'Doccab.CodClie', '=', 'Clientes.Codclie')
            ->where('Clientes.Activo', 1)
            ->sum('Doccab.Total');

        $ticket_promedio = DB::connection($this->connection)->table('Doccab')
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

    public function analizarSegmentacionClientes()
    {
        $por_tipo = DB::connection($this->connection)->table('Clientes')
            ->select('TipoClie', DB::raw('COUNT(*) as cantidad'))
            ->where('Activo', 1)
            ->groupBy('TipoClie')
            ->get();

        $por_valor = DB::connection($this->connection)->table('Doccab')
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

        $por_frecuencia = DB::connection($this->connection)->table('Doccab')
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

    public function calcularEstadisticasCliente($cliente_id)
    {
        $total_compras = DB::connection($this->connection)->table('Doccab')
            ->where('CodClie', $cliente_id)
            ->sum('Total');

        $cantidad_compras = DB::connection($this->connection)->table('Doccab')
            ->where('CodClie', $cliente_id)
            ->count();

        $fecha_primera_compra = DB::connection($this->connection)->table('Doccab')
            ->where('CodClie', $cliente_id)
            ->min('Fecha');

        $fecha_ultima_compra = DB::connection($this->connection)->table('Doccab')
            ->where('CodClie', $cliente_id)
            ->max('Fecha');

        $ticket_promedio = $cantidad_compras > 0 ? $total_compras / $cantidad_compras : 0;

        $compras_mes_actual = DB::connection($this->connection)->table('Doccab')
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

    public function analizarCredito($cliente_id)
    {
        $cliente = DB::connection($this->connection)->table('Clientes')->where('Codclie', $cliente_id)->first();
        
        $saldo_actual = DB::connection($this->connection)->table('CtaCliente')
            ->where('CodClie', $cliente_id)
            ->sum('Saldo');

        $limite = $cliente->Limite ?? 0;
        $credito_disponible = max(0, $limite - $saldo_actual);
        $porcentaje_utilizado = $limite > 0 ? ($saldo_actual / $limite) * 100 : 0;

        $total_operaciones = DB::connection($this->connection)->table('CtaCliente')->where('CodClie', $cliente_id)->count();
        $tasa_cumplimiento = $total_operaciones > 0 ? 100 : 0; 

        if ($tasa_cumplimiento >= 95 && $porcentaje_utilizado <= 50) {
            $categoria_riesgo = 'BAJO';
        } elseif ($tasa_cumplimiento >= 85 && $porcentaje_utilizado <= 75) {
            $categoria_riesgo = 'MEDIO';
        } else {
            $categoria_riesgo = 'ALTO';
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

    public function generarRecomendaciones($cliente_id)
    {
        $estadisticas = $this->calcularEstadisticasCliente($cliente_id);
        $credito = $this->analizarCredito($cliente_id);
        $compras_recientes = DB::connection($this->connection)->table('Doccab')
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

    public function obtenerActividadReciente($cliente_id)
    {
        $compras = DB::connection($this->connection)->table('Doccab')
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

    public function obtenerFiltros(Request $request)
    {
        return [
            'tipo_clie' => $request->tipo_clie,
            'busqueda' => $request->busqueda,
            'ventas_min' => $request->ventas_min,
            'ventas_max' => $request->ventas_max
        ];
    }

    public function consultarClientes($filtros)
    {
        $columns = [
            'Clientes.Codclie', 'Clientes.tipoDoc', 'Clientes.Documento', 'Clientes.Razon',
            'Clientes.Direccion', 'Clientes.Telefono1', 'Clientes.Telefono2', 'Clientes.Fax',
            'Clientes.Celular', 'Clientes.Nextel', 'Clientes.Maymin', 'Clientes.Fecha',
            'Clientes.Zona', 'Clientes.TipoNeg', 'Clientes.TipoClie', 'Clientes.Vendedor',
            'Clientes.Email', 'Clientes.Limite', 'Clientes.Activo'
        ];

        // Usamos la conexión sqlsrv
        $query = DB::connection($this->connection)->table('Clientes')
            ->leftJoin('Doccab', 'Clientes.Codclie', '=', 'Doccab.CodClie')
            ->where('Clientes.Activo', 1)
            ->select(array_merge($columns, [DB::raw('SUM(Doccab.Total) as total_ventas')]))
            ->groupBy($columns);

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

    public function obtenerHistorialCompras($cliente_id)
    {
        return DB::connection($this->connection)->table('Doccab')
            ->join('Docdet', 'Doccab.Numero', '=', 'Docdet.Numero')
            ->join('Productos', 'Docdet.Codpro', '=', 'Productos.CodPro')
            ->where('Doccab.CodClie', $cliente_id)
            ->select(
                'Doccab.Numero', 'Doccab.Fecha', 'Doccab.Total',
                'Productos.Nombre as Producto', 'Docdet.Cantidad', 'Docdet.Precio'
            )
            ->orderBy('Doccab.Fecha', 'desc')
            ->take(50)
            ->get();
    }

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

    public function reporteClientesVip()
    {
        return DB::connection($this->connection)->table('Doccab')
            ->join('Clientes', 'Doccab.CodClie', '=', 'Clientes.Codclie')
            ->where('Clientes.TipoClie', 4)
            ->select(
                'Clientes.Codclie', 'Clientes.Razon', 'Clientes.Documento',
                'Clientes.Telefono1', 'Clientes.Email',
                DB::raw('SUM(Doccab.Total) as total_compras'),
                DB::raw('COUNT(Doccab.Numero) as cantidad_compras'),
                DB::raw('AVG(Doccab.Total) as ticket_promedio')
            )
            ->where('Doccab.Fecha', '>=', now()->subYear())
            ->groupBy('Clientes.Codclie', 'Clientes.Razon', 'Clientes.Documento', 'Clientes.Telefono1', 'Clientes.Email')
            ->orderBy('total_compras', 'desc')
            ->get();
    }

    public function buscarPorRuc($ruc)
    {
        $cliente = DB::connection($this->connection)->table('Clientes')
            ->where('Documento', $ruc)
            ->first();

        if (!$cliente) {
            return response()->json(['error' => 'Cliente no encontrado'], 404);
        }

        return response()->json($cliente);
    }

    public function resumenParaFacturacion($cliente_id)
    {
        $cliente = DB::connection($this->connection)->table('Clientes')
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

    private function obtenerTotalCartera()
    {
        return DB::connection($this->connection)->table('CtaCliente')
            ->where('Saldo', '>', 0)
            ->sum('Saldo') ?? 0;
    }

    public function sugerencias(Request $request)
    {
        $term = $request->term;
        
        $clientes = DB::connection($this->connection)->table('Clientes')
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
    
    public function crearVista()
    {
        // ✅ CORRECCIÓN: Misma estructura
        $vendedores = DB::connection($this->connection)
            ->table('Empleados')
            ->select('Codemp', 'Nombre', 'Tipo')
            ->where('Tipo', 1)
            ->orderBy('Nombre', 'asc')
            ->get();
                        
        return view('clientes.crear', compact('vendedores'));
    }

    public function vistaBusqueda()
    {
        return view('clientes.buscar');
    }

    public function editarVista($id)
    {
        // Obtener el cliente
        $cliente = DB::connection($this->connection)
            ->table('Clientes')
            ->where('Codclie', $id)
            ->first();
        
        if (!$cliente) {
            return redirect()
                ->route('contador.clientes.index')
                ->with('error', 'Cliente no encontrado');
        }
        
        // ✅ CORRECCIÓN: Seleccionar Codemp y Nombre correctamente
        $vendedores = DB::connection($this->connection)
            ->table('Empleados')
            ->select('Codemp', 'Nombre', 'Tipo', 'Direccion', 'Telefono1')
            ->where('Tipo', 1) // Tipo 1 = Vendedor
            ->orderBy('Nombre', 'asc')
            ->get();
        
        // Log para debugging (opcional)
        \Log::info('Vendedores cargados:', [
            'total' => $vendedores->count(),
            'primer_vendedor' => $vendedores->first()
        ]);
        
        return view('clientes.editar', compact('cliente', 'vendedores'));
    }

    public function apiConsultaDocumento($documento)
    {
        $documento = trim($documento);
        
        $esDniValido = $this->reniecService->validarDNI($documento)['valido'];
        $esRucValido = $this->reniecService->validarRUC($documento)['valido'];

        if (!$esDniValido && !$esRucValido) {
            return response()->json(['success' => false, 'message' => 'El documento no es un RUC o DNI válido.'], 400);
        }

        try {
            // 1. Revisar si ya existe en la BD local
            $local = DB::connection($this->connection)->table('Clientes')
                       ->where('Documento', $documento)->first();
            
            if($local) {
                return response()->json(['success' => false, 'message' => 'Este documento ya está registrado a nombre de: ' . $local->Razon], 409); // 409 Conflicto
            }
            
            // 2. No existe, consultar API
            $apiData = $esRucValido ? $this->reniecService->consultarRUC($documento) : $this->reniecService->consultarDNI($documento);

            if ($apiData) {
                // El ReniecService ya formatea los campos (razon_social, address, etc.)
                return response()->json([
                    'success' => true,
                    'data' => $apiData
                ]);
            } else {
                return response()->json(['success' => false, 'message' => 'No se encontraron datos en RENIEC/SUNAT. Puede registrarlo manualmente.'], 404);
            }

        } catch (\Exception $e) {
            Log::error('Error en apiConsultaDocumento: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()], 500);
        }
    }

}