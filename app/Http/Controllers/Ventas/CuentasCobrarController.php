<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\AccesoWeb¿;

class CuentasCobrarController extends Controller
{
    /**
     * Constructor con middleware de autenticación y autorización
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('rol:vendedor|administrador|contador');
    }

    /**
     * Dashboard principal de cuentas por cobrar
     */
    public function index(Request $request)
    {
        $fechaCorte = $request->get('fecha_corte', now()->toDateString());
        $estado = $request->get('estado', 'todas');
        
        // Consulta base con join para obtener información del cliente
        $query = DB::table('Doccab')
            ->leftJoin('Clientes', 'Doccab.CodCli', '=', 'Clientes.CodCli')
            ->leftJoin('Usuarios', 'Doccab.Vendedor', '=', 'Usuarios.Usuario')
            ->where('Doccab.Tipodoc', '!=', 'AN');

        // Filtros
        if ($estado !== 'todas') {
            $query->where('Doccab.Estado', $estado);
        }

        if ($request->filled('cliente')) {
            $query->where(function($q) use ($request) {
                $q->where('Clientes.CodCli', 'like', '%' . $request->cliente . '%')
                  ->orWhere('Clientes.Razon', 'like', '%' . $request->cliente . '%');
            });
        }

        if ($request->filled('vendedor')) {
            $query->where('Doccab.Vendedor', 'like', '%' . $request->vendedor . '%');
        }

        $cuentas = $query->select([
            'Doccab.*',
            'Clientes.Razon as Cliente',
            'Clientes.Ruc as RucCli',
            'Clientes.Telefono as TelefonoCli',
            'Clientes.Direccion as DireccionCli',
            'Usuarios.Nombre as NombreVendedor'
        ])->orderBy('Doccab.Fecha', 'asc')->paginate(20);

        // Estadísticas generales
        $estadisticas = $this->calcularEstadisticasGenerales($fechaCorte);
        
        // Análisis por antigüedad
        $antiguedad = $this->analizarPorAntiguedad($fechaCorte);
        
        // Top clientes con más deuda
        $topDeudores = $this->topClientesDeudores($fechaCorte);

        return response()->json([
            'cuentas' => $cuentas,
            'estadisticas' => $estadisticas,
            'antiguedad' => $antiguedad,
            'top_deudores' => $topDeudores,
            'fecha_corte' => $fechaCorte
        ]);
    }

    /**
     * Registrar pago de cuenta por cobrar
     */
    public function registrarPago(Request $request, $numero)
    {
        $request->validate([
            'monto_pago' => 'required|numeric|min:0.01',
            'fecha_pago' => 'required|date',
            'metodo_pago' => 'required|in:efectivo,transferencia,cheque,tarjeta,deposito',
            'banco' => 'nullable|string|max:100',
            'numero_operacion' => 'nullable|string|max:50',
            'observaciones' => 'nullable|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            $factura = DB::table('Doccab')->where('Numero', $numero)->first();
            
            if (!$factura) {
                return response()->json(['error' => 'Factura no encontrada'], 404);
            }

            if ($factura->Estado === 'PAGADO') {
                return response()->json(['error' => 'La factura ya está pagada'], 400);
            }

            // Calcular saldo pendiente
            $saldoPendiente = $factura->Total - ($this->calcularTotalPagado($numero) ?? 0);

            if ($request->monto_pago > $saldoPendiente) {
                return response()->json([
                    'error' => 'El monto del pago excede el saldo pendiente',
                    'saldo_pendiente' => $saldoPendiente
                ], 400);
            }

            // Registrar pago
            $pagoId = DB::table('Cobros')->insertGetId([
                'Numero_Fac' => $numero,
                'Fecha' => $request->fecha_pago,
                'Monto' => $request->monto_pago,
                'Metodo_Pago' => $request->metodo_pago,
                'Banco' => $request->banco,
                'Numero_Operacion' => $request->numero_operacion,
                'Observaciones' => $request->observaciones,
                'Usuario' => auth()->user()->Usuario ?? 'admin',
                'created_at' => now()
            ]);

            // Actualizar estado de factura si se paga completamente
            $nuevoTotalPagado = ($this->calcularTotalPagado($numero) ?? 0) + $request->monto_pago;
            
            if ($nuevoTotalPagado >= $factura->Total) {
                DB::table('Doccab')
                    ->where('Numero', $numero)
                    ->update([
                        'Estado' => 'PAGADO',
                        'FechaPago' => $request->fecha_pago,
                        'MetodoPago' => $request->metodo_pago
                    ]);
            }

            // Crear movimiento contable
            $this->crearAsientoCobro($numero, $request->monto_pago, $request->fecha_pago, $request->metodo_pago);

            // Actualizar saldo del cliente
            $this->actualizarSaldoCliente($factura->CodCli);

            DB::commit();

            return response()->json([
                'mensaje' => 'Pago registrado exitosamente',
                'pago_id' => $pagoId,
                'saldo_restante' => $factura->Total - $nuevoTotalPagado
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al registrar el pago'], 500);
        }
    }

    /**
     * Mostrar historial de pagos de una factura
     */
    public function historialPagos($numero)
    {
        $pagos = DB::table('Cobros')
            ->leftJoin('Usuarios', 'Cobros.Usuario', '=', 'Usuarios.Usuario')
            ->where('Cobros.Numero_Fac', $numero)
            ->select([
                'Cobros.*',
                'Usuarios.Nombre as UsuarioRegistro'
            ])
            ->orderBy('Cobros.Fecha', 'desc')
            ->get();

        $factura = DB::table('Doccab')
            ->leftJoin('Clientes', 'Doccab.CodCli', '=', 'Clientes.CodCli')
            ->where('Doccab.Numero', $numero)
            ->select(['Doccab.*', 'Clientes.Razon as Cliente'])
            ->first();

        if (!$factura) {
            return response()->json(['error' => 'Factura no encontrada'], 404);
        }

        $totalFactura = $factura->Total;
        $totalPagado = $pagos->sum('Monto');
        $saldoPendiente = $totalFactura - $totalPagado;

        return response()->json([
            'factura' => $factura,
            'pagos' => $pagos,
            'resumen' => [
                'total_factura' => $totalFactura,
                'total_pagado' => $totalPagado,
                'saldo_pendiente' => $saldoPendiente,
                'porcentaje_pagado' => $totalFactura > 0 ? ($totalPagado / $totalFactura) * 100 : 0
            ]
        ]);
    }

    /**
     * Análisis de antigüedad de deuda
     */
    public function analizarAntiguedad(Request $request)
    {
        $fechaCorte = $request->get('fecha_corte', now()->toDateString());
        $resultado = $this->analizarPorAntiguedad($fechaCorte);

        return response()->json($resultado);
    }

    /**
     * Reporte de cuentas por cobrar por cliente
     */
    public function reportePorCliente(Request $request)
    {
        $clienteId = $request->get('cliente_id');
        $fechaDesde = $request->get('fecha_desde', now()->startOfYear()->toDateString());
        $fechaHasta = $request->get('fecha_hasta', now()->toDateString());

        if (!$clienteId) {
            return response()->json(['error' => 'Cliente ID es requerido'], 400);
        }

        $cliente = DB::table('Clientes')->where('CodCli', $clienteId)->first();
        
        if (!$cliente) {
            return response()->json(['error' => 'Cliente no encontrado'], 404);
        }

        // Facturas del cliente
        $facturas = DB::table('Doccab')
            ->leftJoin('Cobros', 'Doccab.Numero', '=', 'Cobros.Numero_Fac')
            ->where('Doccab.CodCli', $clienteId)
            ->whereBetween('Doccab.Fecha', [$fechaDesde, $fechaHasta])
            ->where('Doccab.Tipodoc', '!=', 'AN')
            ->select([
                'Doccab.*',
                DB::raw('SUM(Cobros.Monto) as total_pagado'),
                DB::raw('COUNT(Cobros.id) as numero_pagos')
            ])
            ->groupBy('Doccab.Numero')
            ->orderBy('Doccab.Fecha', 'desc')
            ->get();

        // Calcular saldos
        $facturas = $facturas->map(function ($factura) {
            $factura->saldo_pendiente = $factura->Total - ($factura->total_pagado ?? 0);
            $factura->dias_vencido = max(0, now()->diffInDays($factura->Fecha));
            $factura->estado_pago = $factura->saldo_pendiente <= 0 ? 'PAGADO' : 'PENDIENTE';
            return $factura;
        });

        // Resumen del cliente
        $resumen = [
            'cliente' => $cliente,
            'total_facturado' => $facturas->sum('Total'),
            'total_cobrado' => $facturas->sum('total_pagado'),
            'saldo_total' => $facturas->sum('saldo_pendiente'),
            'facturas_pendientes' => $facturas->where('saldo_pendiente', '>', 0)->count(),
            'facturas_pagadas' => $facturas->where('saldo_pendiente', '<=', 0)->count()
        ];

        return response()->json([
            'cliente' => $resumen['cliente'],
            'facturas' => $facturas,
            'resumen' => $resumen,
            'periodo' => [
                'desde' => $fechaDesde,
                'hasta' => $fechaHasta
            ]
        ]);
    }

    /**
     * Gestión de cobranza - Lista de tareas
     */
    public function gestionCobranza()
    {
        $fechaActual = now();
        
        // Cuentas vencidas (más de 30 días)
        $cuentasVencidas = DB::table('Doccab')
            ->leftJoin('Clientes', 'Doccab.CodCli', '=', 'Clientes.CodCli')
            ->leftJoin('Cobros', 'Doccab.Numero', '=', 'Cobros.Numero_Fac')
            ->where('Doccab.Tipodoc', '!=', 'AN')
            ->where('Doccab.Estado', '!=', 'PAGADO')
            ->where('Doccab.Fecha', '<', $fechaActual->copy()->subDays(30)->toDateString())
            ->select([
                'Doccab.*',
                'Clientes.Razon as Cliente',
                'Clientes.Telefono as TelefonoCli',
                'Clientes.Email as EmailCli',
                DB::raw("DATEDIFF('{$fechaActual->toDateString()}', Doccab.Fecha) as dias_vencido"),
                DB::raw('(Doccab.Total - COALESCE(SUM(Cobros.Monto), 0)) as saldo_pendiente')
            ])
            ->groupBy('Doccab.Numero')
            ->having('saldo_pendiente', '>', 0)
            ->orderBy('dias_vencido', 'desc')
            ->limit(50)
            ->get();

        // Próximas a vencer (próximos 7 días)
        $proximasVencer = DB::table('Doccab')
            ->leftJoin('Clientes', 'Doccab.CodCli', '=', 'Clientes.CodCli')
            ->leftJoin('Cobros', 'Doccab.Numero', '=', 'Cobros.Numero_Fac')
            ->where('Doccab.Tipodoc', '!=', 'AN')
            ->where('Doccab.Estado', '!=', 'PAGADO')
            ->whereBetween('Doccab.Fecha', [
                $fechaActual->toDateString(),
                $fechaActual->copy()->addDays(7)->toDateString()
            ])
            ->select([
                'Doccab.*',
                'Clientes.Razon as Cliente',
                'Clientes.Telefono as TelefonoCli',
                DB::raw('(Doccab.Total - COALESCE(SUM(Cobros.Monto), 0)) as saldo_pendiente')
            ])
            ->groupBy('Doccab.Numero')
            ->having('saldo_pendiente', '>', 0)
            ->orderBy('Doccab.Fecha', 'asc')
            ->get();

        return response()->json([
            'cuentas_vencidas' => $cuentasVencidas,
            'proximas_vencer' => $proximasVencer,
            'fecha_actual' => $fechaActual->toDateString(),
            'resumen' => [
                'total_vencidas' => $cuentasVencidas->count(),
                'total_proximas' => $proximasVencer->count(),
                'monto_vencido' => $cuentasVencidas->sum('saldo_pendiente'),
                'monto_proximo' => $proximasVencer->sum('saldo_pendiente')
            ]
        ]);
    }

    /**
     * Notificación de cobranza
     */
    public function enviarNotificacion(Request $request, $numero)
    {
        $request->validate([
            'tipo' => 'required|in:email,sms,llamada',
            'mensaje' => 'required|string|max:500',
            'contacto' => 'required|string|max:100'
        ]);

        $factura = DB::table('Doccab')
            ->leftJoin('Clientes', 'Doccab.CodCli', '=', 'Clientes.CodCli')
            ->where('Doccab.Numero', $numero)
            ->select(['Doccab.*', 'Clientes.Razon as Cliente'])
            ->first();

        if (!$factura) {
            return response()->json(['error' => 'Factura no encontrada'], 404);
        }

        // Registrar notificación
        DB::table('Notificaciones_Cobranza')->insert([
            'Numero_Fac' => $numero,
            'Tipo' => $request->tipo,
            'Contacto' => $request->contacto,
            'Mensaje' => $request->mensaje,
            'Fecha_Envio' => now(),
            'Usuario' => auth()->user()->Usuario ?? 'admin', 'contador',
            'Estado' => 'ENVIADO'
        ]);

        return response()->json([
            'mensaje' => 'Notificación registrada exitosamente',
            'factura' => $numero,
            'cliente' => $factura->Cliente
        ]);
    }

    /**
     * Conciliación de pagos
     */
    public function conciliacion(Request $request)
    {
        $fechaDesde = $request->get('fecha_desde', now()->startOfMonth()->toDateString());
        $fechaHasta = $request->get('fecha_hasta', now()->endOfMonth()->toDateString());

        // Pagos registrados en el sistema
        $pagosSistema = DB::table('Cobros')
            ->whereBetween('Fecha', [$fechaDesde, $fechaHasta])
            ->select([
                'Fecha',
                DB::raw('SUM(Monto) as total_pagos')
            ])
            ->groupBy('Fecha')
            ->orderBy('Fecha')
            ->get();

        // Movimientos bancarios (si existe tabla de bancos)
        $pagosBanco = DB::table('Movimientos_Bancarios')
            ->whereBetween('Fecha', [$fechaDesde, $fechaHasta])
            ->where('Tipo', 'INGRESO')
            ->select([
                'Fecha',
                DB::raw('SUM(Monto) as total_bancario')
            ])
            ->groupBy('Fecha')
            ->orderBy('Fecha')
            ->get();

        // Unir datos para conciliación
        $conciliacion = [];
        $fechas = collect($pagosSistema)->pluck('Fecha')->merge(
            collect($pagosBanco)->pluck('Fecha')
        )->unique()->sort();

        foreach ($fechas as $fecha) {
            $pagoSistema = $pagosSistema->firstWhere('Fecha', $fecha);
            $pagoBanco = $pagosBanco->firstWhere('Fecha', $fecha);
            
            $conciliacion[] = [
                'fecha' => $fecha,
                'pagos_sistema' => $pagoSistema ? $pagoSistema->total_pagos : 0,
                'pagos_banco' => $pagoBanco ? $pagoBanco->total_bancario : 0,
                'diferencia' => ($pagoSistema ? $pagoSistema->total_pagos : 0) - 
                              ($pagoBanco ? $pagoBanco->total_bancario : 0)
            ];
        }

        return response()->json([
            'conciliacion' => $conciliacion,
            'periodo' => [
                'desde' => $fechaDesde,
                'hasta' => $fechaHasta
            ]
        ]);
    }

    /**
     * Estadísticas de cobranza
     */
    public function estadisticas(Request $request)
    {
        $periodo = $request->get('periodo', 'mes'); // dia, semana, mes, trimestre, año
        $fechaInicio = match($periodo) {
            'dia' => now()->startOfDay(),
            'semana' => now()->startOfWeek(),
            'mes' => now()->startOfMonth(),
            'trimestre' => now()->startOfQuarter(),
            'año' => now()->startOfYear(),
            default => now()->startOfMonth()
        };

        // Recaudación por período
        $recaudacion = DB::table('Cobros')
            ->where('Fecha', '>=', $fechaInicio)
            ->select([
                DB::raw('DATE(Fecha) as fecha'),
                DB::raw('SUM(Monto) as total_recaudado'),
                DB::raw('COUNT(*) as numero_cobros')
            ])
            ->groupBy(DB::raw('DATE(Fecha)'))
            ->orderBy('fecha')
            ->get();

        // Efectividad de cobranza
        $totalFacturas = DB::table('Doccab')
            ->where('Fecha', '>=', $fechaInicio)
            ->where('Tipodoc', '!=', 'AN')
            ->count();

        $facturasCobradas = DB::table('Doccab')
            ->where('Fecha', '>=', $fechaInicio)
            ->where('Tipodoc', '!=', 'AN')
            ->where('Estado', 'PAGADO')
            ->count();

        $tasaCobranza = $totalFacturas > 0 ? ($facturasCobradas / $totalFacturas) * 100 : 0;

        // Días promedio de cobro
        $diasPromedioCobro = DB::table('Doccab')
            ->leftJoin('Cobros', 'Doccab.Numero', '=', 'Cobros.Numero_Fac')
            ->where('Doccab.Fecha', '>=', $fechaInicio)
            ->where('Doccab.Estado', 'PAGADO')
            ->select([
                'Doccab.Fecha',
                'Cobros.Fecha as FechaPago'
            ])
            ->get()
            ->filter(function ($item) {
                return $item->FechaPago;
            })
            ->map(function ($item) {
                $item->dias_cobro = Carbon::parse($item->Fecha)->diffInDays(Carbon::parse($item->FechaPago));
                return $item;
            });

        $promedioDiasCobro = $diasPromedioCobro->count() > 0 ? 
            $diasPromedioCobro->avg('dias_cobro') : 0;

        return response()->json([
            'recaudacion' => $recaudacion,
            'efectividad' => [
                'total_facturas' => $totalFacturas,
                'facturas_cobradas' => $facturasCobradas,
                'tasa_cobranza' => number_format($tasaCobranza, 2),
                'promedio_dias_cobro' => number_format($promedioDiasCobro, 1)
            ],
            'periodo' => [
                'tipo' => $periodo,
                'inicio' => $fechaInicio->toDateString(),
                'fin' => now()->toDateString()
            ]
        ]);
    }

    // ===== MÉTODOS PRIVADOS DE SOPORTE =====

    /**
     * Calcular estadísticas generales
     */
    private function calcularEstadisticasGenerales($fechaCorte)
    {
        $cuentas = DB::table('Doccab')
            ->leftJoin('Cobros', 'Doccab.Numero', '=', 'Cobros.Numero_Fac')
            ->where('Doccab.Tipodoc', '!=', 'AN')
            ->where('Doccab.Fecha', '<=', $fechaCorte)
            ->select([
                'Doccab.Total',
                'Doccab.Estado',
                DB::raw('SUM(Cobros.Monto) as total_pagado')
            ])
            ->groupBy('Doccab.Numero')
            ->get();

        $totalFacturas = $cuentas->count();
        $montoTotal = $cuentas->sum('Total');
        $montoPagado = $cuentas->sum('total_pagado');
        $montoPendiente = $montoTotal - $montoPagado;

        return [
            'total_facturas' => $totalFacturas,
            'monto_total' => number_format($montoTotal, 2),
            'monto_pagado' => number_format($montoPagado, 2),
            'monto_pendiente' => number_format($montoPendiente, 2),
            'tasa_cobranza' => $montoTotal > 0 ? 
                number_format(($montoPagado / $montoTotal) * 100, 2) : '0.00'
        ];
    }

    /**
     * Analizar por antigüedad
     */
    private function analizarPorAntiguedad($fechaCorte)
    {
        $cuentas = DB::table('Doccab')
            ->leftJoin('Cobros', 'Doccab.Numero', '=', 'Cobros.Numero_Fac')
            ->where('Doccab.Tipodoc', '!=', 'AN')
            ->where('Doccab.Fecha', '<=', $fechaCorte)
            ->select([
                'Doccab.Numero',
                'Doccab.Fecha',
                'Doccab.Total',
                DB::raw('SUM(Cobros.Monto) as total_pagado'),
                DB::raw("DATEDIFF('{$fechaCorte}', Doccab.Fecha) as dias_antiguedad")
            ])
            ->groupBy('Doccab.Numero')
            ->get();

        $saldoPendiente = $cuentas->map(function ($cuenta) {
            $cuenta->saldo_pendiente = $cuenta->Total - ($cuenta->total_pagado ?? 0);
            return $cuenta;
        })->filter(function ($cuenta) {
            return $cuenta->saldo_pendiente > 0;
        });

        $resultado = [
            '0_30_dias' => $saldoPendiente->where('dias_antiguedad', '<=', 30)->count(),
            '31_60_dias' => $saldoPendiente->whereBetween('dias_antiguedad', [31, 60])->count(),
            '61_90_dias' => $saldoPendiente->whereBetween('dias_antiguedad', [61, 90])->count(),
            'mas_90_dias' => $saldoPendiente->where('dias_antiguedad', '>', 90)->count(),
            'monto_0_30' => $saldoPendiente->where('dias_antiguedad', '<=', 30)->sum('saldo_pendiente'),
            'monto_31_60' => $saldoPendiente->whereBetween('dias_antiguedad', [31, 60])->sum('saldo_pendiente'),
            'monto_61_90' => $saldoPendiente->whereBetween('dias_antiguedad', [61, 90])->sum('saldo_pendiente'),
            'monto_mas_90' => $saldoPendiente->where('dias_antiguedad', '>', 90)->sum('saldo_pendiente')
        ];

        $resultado['total_cuentas'] = $saldoPendiente->count();
        $resultado['total_monto'] = $saldoPendiente->sum('saldo_pendiente');

        return $resultado;
    }

    /**
     * Top clientes deudores
     */
    private function topClientesDeudores($fechaCorte)
    {
        return DB::table('Doccab')
            ->leftJoin('Clientes', 'Doccab.CodCli', '=', 'Clientes.CodCli')
            ->leftJoin('Cobros', 'Doccab.Numero', '=', 'Cobros.Numero_Fac')
            ->where('Doccab.Tipodoc', '!=', 'AN')
            ->where('Doccab.Fecha', '<=', $fechaCorte)
            ->select([
                'Clientes.CodCli',
                'Clientes.Razon',
                DB::raw('COUNT(DISTINCT Doccab.Numero) as facturas_pendientes'),
                DB::raw('SUM(Doccab.Total) as total_facturas'),
                DB::raw('SUM(COALESCE(Cobros.Monto, 0)) as total_pagado'),
                DB::raw('(SUM(Doccab.Total) - SUM(COALESCE(Cobros.Monto, 0))) as saldo_pendiente')
            ])
            ->groupBy('Clientes.CodCli', 'Clientes.Razon')
            ->having('saldo_pendiente', '>', 0)
            ->orderBy('saldo_pendiente', 'desc')
            ->limit(10)
            ->get();
    }

    /**
     * Calcular total pagado de una factura
     */
    private function calcularTotalPagado($numero)
    {
        return DB::table('Cobros')
            ->where('Numero_Fac', $numero)
            ->sum('Monto');
    }

    /**
     * Crear asiento contable por cobro
     */
    private function crearAsientoCobro($numero, $monto, $fechaPago, $metodoPago)
    {
        $cuentaBanco = $this->obtenerCuentaBanco($metodoPago);
        
        DB::table('asientos_diario')->insert([
            [
                'fecha' => $fechaPago,
                'glosa' => "COBRO {$numero}",
                'cuenta_debe' => $cuentaBanco,
                'cuenta_haber' => '1211', // Cuentas por cobrar
                'monto' => $monto,
                'fecha_creacion' => now()
            ]
        ]);
    }

    /**
     * Obtener cuenta bancaria según método de pago
     */
    private function obtenerCuentaBanco($metodoPago)
    {
        $cuentas = [
            'efectivo' => '1011',
            'transferencia' => '1041',
            'cheque' => '1042',
            'tarjeta' => '1043',
            'deposito' => '1041'
        ];

        return $cuentas[$metodoPago] ?? '1011';
    }

    /**
     * Actualizar saldo del cliente
     */
    private function actualizarSaldoCliente($codCli)
    {
        $saldo = DB::table('Doccab')
            ->leftJoin('Cobros', 'Doccab.Numero', '=', 'Cobros.Numero_Fac')
            ->where('Doccab.CodCli', $codCli)
            ->where('Doccab.Tipodoc', '!=', 'AN')
            ->select([
                DB::raw('SUM(Doccab.Total) as total_facturado'),
                DB::raw('SUM(COALESCE(Cobros.Monto, 0)) as total_cobrado')
            ])
            ->first();

        $nuevoSaldo = ($saldo->total_facturado ?? 0) - ($saldo->total_cobrado ?? 0);

        DB::table('Clientes')
            ->where('CodCli', $codCli)
            ->update(['Saldo' => $nuevoSaldo]);
    }

    /**
     * Exportar cuentas por cobrar
     */
    public function exportar(Request $request)
    {
        $fechaCorte = $request->get('fecha_corte', now()->toDateString());
        $estado = $request->get('estado', 'todas');
        $tipo = $request->get('tipo', 'excel');

        // Obtener datos usando lógica similar al index
        $cuentas = DB::table('Doccab')
            ->leftJoin('Clientes', 'Doccab.CodCli', '=', 'Clientes.CodCli')
            ->leftJoin('Cobros', 'Doccab.Numero', '=', 'Cobros.Numero_Fac')
            ->where('Doccab.Tipodoc', '!=', 'AN')
            ->where('Doccab.Fecha', '<=', $fechaCorte)
            ->select([
                'Doccab.Numero',
                'Doccab.Fecha',
                'Clientes.Razon as Cliente',
                'Clientes.Ruc as Ruc',
                'Doccab.Total',
                DB::raw('SUM(COALESCE(Cobros.Monto, 0)) as total_pagado'),
                DB::raw('(Doccab.Total - SUM(COALESCE(Cobros.Monto, 0))) as saldo_pendiente'),
                DB::raw("DATEDIFF('{$fechaCorte}', Doccab.Fecha) as dias_antiguedad"),
                'Doccab.Estado'
            ])
            ->groupBy('Doccab.Numero')
            ->orderBy('dias_antiguedad', 'desc')
            ->get();

        if ($tipo === 'csv') {
            return $this->exportarCSV($cuentas, $fechaCorte);
        }

        return response()->json([
            'mensaje' => 'Exportación iniciada',
            'total_registros' => $cuentas->count(),
            'fecha_corte' => $fechaCorte
        ]);
    }

    /**
     * Exportar a CSV
     */
    private function exportarCSV($cuentas, $fechaCorte)
    {
        $filename = 'cuentas_cobrar_' . $fechaCorte . '_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Numero', 'Fecha', 'Cliente', 'RUC', 'Total', 'Pagado', 
            'Saldo_Pendiente', 'Dias_Antiguedad', 'Estado'
        ];

        $csvData = [];
        $csvData[] = implode(',', $headers);

        foreach ($cuentas as $cuenta) {
            $row = [
                $cuenta->Numero,
                $cuenta->Fecha,
                '"' . str_replace('"', '""', $cuenta->Cliente) . '"',
                $cuenta->Ruc,
                number_format($cuenta->Total, 2),
                number_format($cuenta->total_pagado, 2),
                number_format($cuenta->saldo_pendiente, 2),
                $cuenta->dias_antiguedad,
                $cuenta->Estado
            ];
            $csvData[] = implode(',', $row);
        }

        return response(implode("\n", $csvData))
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}
