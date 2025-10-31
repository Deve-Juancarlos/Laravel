<?php

namespace App\Http\Controllers\Contabilidad;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class BancosController extends Controller
{
    /**
     * Display a listing of the resource.
     * Route: /contabilidad/bancos
     */
    public function index(Request $request)
    {
        try {
            $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
            $fechaFin = $request->input('fecha_fin', Carbon::now()->endOfMonth()->format('Y-m-d'));
            $cuenta = $request->input('cuenta');

            // Usar la vista SQL v_movimientos_bancarios
            $query = DB::table('v_bancos_con_descripciones')
                ->whereBetween('Fecha', [$fechaInicio, $fechaFin]);

            if ($cuenta) {
                $query->where('Cuenta', $cuenta);
            }

            $movimientosBancarios = $query
                ->orderBy('Fecha', 'desc')
                ->orderBy('Numero', 'desc')
                ->paginate(50);

            // Usar vista v_saldos_bancarios_actuales
            $saldosActuales = DB::table('v_saldos_bancarios_actuales')->get();

            // Resumen del período
            $resumenCuentas = DB::table('v_bancos_con_descripciones')
                ->whereBetween('Fecha', [$fechaInicio, $fechaFin])
                ->select([
                    'Cuenta',
                    'Banco',
                    'Moneda',
                    DB::raw('SUM(ingreso) as total_ingresos'),
                    DB::raw('SUM(egreso) as total_egresos'),
                    DB::raw('COUNT(*) as total_movimientos')
                ])
                ->groupBy('Cuenta', 'Banco', 'Moneda')
                ->get();

            // Totales generales
            $totalesPeriodo = DB::table('v_bancos_con_descripciones')
                ->whereBetween('Fecha', [$fechaInicio, $fechaFin])
                ->select([
                    DB::raw('SUM(ingreso) as total_ingresos'),
                    DB::raw('SUM(egreso) as total_egresos'),
                    DB::raw('COUNT(*) as total_movimientos')
                ])
                ->first();

            // Lista de bancos para el filtro
            $listaBancos = DB::table('Bancos')->orderBy('Banco')->get();

            return view('contabilidad.registros.bancos', compact(
                'movimientosBancarios',
                'resumenCuentas',
                'saldosActuales',
                'totalesPeriodo',
                'fechaInicio',
                'fechaFin',
                'cuenta',
                'listaBancos'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al cargar el libro de bancos: ' . $e->getMessage());
        }
    }

    /**
     * Show bank account detail
     * Route: /contabilidad/bancos/detalle/{cuenta}
     */
    public function detalleCuenta($cuenta, Request $request)
    {
        try {
            $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfYear()->format('Y-m-d'));
            $fechaFin = $request->input('fecha_fin', Carbon::now()->endOfYear()->format('Y-m-d'));

            // Información de la cuenta
            $infoCuenta = DB::table('v_saldos_bancarios_actuales')
                ->where('Cuenta', $cuenta)
                ->first();

            if (!$infoCuenta) {
                return redirect()->route('contador.bancos.index')->with('error', 'Cuenta bancaria no encontrada');
            }

            // Movimientos de la cuenta
            $movimientos = DB::table('v_bancos_con_descripciones')
                ->where('Cuenta', $cuenta)
                ->whereBetween('Fecha', [$fechaInicio, $fechaFin])
                ->orderBy('Fecha', 'desc')
                ->orderBy('Numero', 'desc')
                ->paginate(100);

            // Saldo anterior al período
            $saldoAnterior = DB::table('CtaBanco')
                ->where('Cuenta', $cuenta)
                ->where('Fecha', '<', $fechaInicio)
                ->selectRaw('SUM(CASE WHEN Tipo = 1 THEN Monto ELSE -Monto END) as saldo')
                ->value('saldo') ?? 0;

            // Resumen mensual usando vista
            $resumenMensual = DB::table('v_resumen_mensual_bancario')
                ->where('Cuenta', $cuenta)
                ->whereBetween(DB::raw("DATEFROMPARTS(anio, mes, 1)"), [$fechaInicio, $fechaFin])
                ->orderBy('anio', 'desc')
                ->orderBy('mes', 'desc')
                ->get();

            // Totales del período
            $totalesPeriodo = [
                'ingresos' => $movimientos->sum('ingreso'),
                'egresos' => $movimientos->sum('egreso'),
                'saldo_final' => $saldoAnterior + $movimientos->sum('ingreso') - $movimientos->sum('egreso')
            ];

            return view('contabilidad.registros.bancos-detalle', compact(
                'infoCuenta',
                'movimientos',
                'saldoAnterior',
                'resumenMensual',
                'totalesPeriodo',
                'fechaInicio',
                'fechaFin'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al cargar detalle: ' . $e->getMessage());
        }
    }

    /**
     * Get daily bank movements
     * Route: /contabilidad/bancos/diarios
     */
    public function movimientosDiarios(Request $request)
    {
        try {
            $fecha = $request->input('fecha', Carbon::now()->format('Y-m-d'));

            // Movimientos del día
            $movimientosDiarios = DB::table('v_bancos_con_descripciones')
                ->whereDate('Fecha', $fecha)
                ->orderBy('Cuenta')
                ->orderBy('Numero')
                ->get();

            // Resumen por banco
            $resumenPorBanco = DB::table('v_bancos_con_descripciones')
                ->whereDate('Fecha', $fecha)
                ->select([
                    'Banco',
                    'Moneda',
                    DB::raw('SUM(ingreso) as total_ingresos'),
                    DB::raw('SUM(egreso) as total_egresos'),
                    DB::raw('COUNT(*) as total_movimientos')
                ])
                ->groupBy('Banco', 'Moneda')
                ->get();

            // Totales del día
            $totalesDiarios = [
                'fecha' => $fecha,
                'total_ingresos' => $movimientosDiarios->sum('ingreso'),
                'total_egresos' => $movimientosDiarios->sum('egreso'),
                'total_movimientos' => $movimientosDiarios->count()
            ];

            return view('contabilidad.registros.bancos-diario', compact(
                'movimientosDiarios',
                'resumenPorBanco',
                'totalesDiarios',
                'fecha'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al cargar movimientos: ' . $e->getMessage());
        }
    }

    /**
     * Get reconciliation analysis
     * Route: /contabilidad/bancos/conciliacion
     */
    public function conciliacion(Request $request)
    {
        try {
            $cuenta = $request->input('cuenta');
            $fecha = $request->input('fecha', Carbon::now()->endOfMonth()->format('Y-m-d'));

            if (!$cuenta) {
                $listaBancos = DB::table('Bancos')->get();
                return view('contabilidad.registros.bancos-conciliacion', compact('listaBancos', 'fecha'));
            }

            // Información de la cuenta
            $infoCuenta = DB::table('v_saldos_bancarios_actuales')
                ->where('Cuenta', $cuenta)
                ->first();

            if (!$infoCuenta) {
                return redirect()->back()->with('error', 'Cuenta no encontrada');
            }

            // Saldo según libros
            $saldoLibros = DB::table('CtaBanco')
                ->where('Cuenta', $cuenta)
                ->where('Fecha', '<=', $fecha)
                ->selectRaw('SUM(CASE WHEN Tipo = 1 THEN Monto ELSE -Monto END) as saldo')
                ->value('saldo') ?? 0;

            // Cheques pendientes
            $chequesPendientes = DB::table('v_cheques_pendientes')
                ->where('Cuenta', $cuenta)
                ->where('fecha_emision', '<=', $fecha)
                ->get();

            // Depósitos en tránsito
            $depositosTransito = DB::table('CtaBanco')
                ->where('Cuenta', $cuenta)
                ->where('Fecha', '<=', $fecha)
                ->where('Tipo', 1)
                ->where('Clase', 2)
                ->whereRaw("Documento NOT LIKE '%CONCILIADO%'")
                ->get();

            // Última conciliación
            $ultimaConciliacion = DB::table('BancosConciliacion')
                ->where('cuenta', $cuenta)
                ->orderBy('fecha_conciliacion', 'desc')
                ->first();

            $diferencias = [
                'saldo_libros' => $saldoLibros,
                'cheques_pendientes' => $chequesPendientes->sum('Monto'),
                'depositos_transito' => $depositosTransito->sum('Monto'),
                'saldo_bancario_estimado' => $saldoLibros - $chequesPendientes->sum('Monto') + $depositosTransito->sum('Monto')
            ];

            return view('contabilidad.registros.bancos-conciliacion', compact(
                'infoCuenta',
                'saldoLibros',
                'chequesPendientes',
                'depositosTransito',
                'diferencias',
                'ultimaConciliacion',
                'fecha'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error en conciliación: ' . $e->getMessage());
        }
    }

    /**
     * Save bank reconciliation
     * Route: POST /contabilidad/bancos/conciliacion/guardar
     */
    public function guardarConciliacion(Request $request)
    {
        try {
            $validated = $request->validate([
                'cuenta' => 'required|exists:Bancos,Cuenta',
                'fecha_conciliacion' => 'required|date',
                'saldo_bancario' => 'required|numeric',
                'observaciones' => 'nullable|string|max:500'
            ]);

            $resultado = DB::select('EXEC sp_registrar_conciliacion ?, ?, ?, ?, ?', [
                $validated['cuenta'],
                $validated['fecha_conciliacion'],
                $validated['saldo_bancario'],
                $validated['observaciones'] ?? null,
                Auth::user()->name ?? 'SYSTEM'
            ]);

            return redirect()->back()->with('success', 'Conciliación registrada exitosamente');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al guardar conciliación: ' . $e->getMessage());
        }
    }

    /**
     * Get bank transfers
     * Route: /contabilidad/bancos/transferencias
     */
    public function transferencias(Request $request)
    {
        try {
            $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
            $fechaFin = $request->input('fecha_fin', Carbon::now()->endOfMonth()->format('Y-m-d'));

            // Usar vista de transferencias
            $transferencias = DB::table('v_transferencias_bancarias')
                ->whereBetween('Fecha', [$fechaInicio, $fechaFin])
                ->orderBy('Fecha', 'desc')
                ->paginate(50);

            // Resumen de transferencias
            $resumenTransferencias = [
                'total_transferencias' => $transferencias->total(),
                'monto_total' => $transferencias->sum('Monto'),
                'cuentas_origen' => $transferencias->unique('cuenta_origen')->count(),
                'cuentas_destino' => $transferencias->unique('cuenta_destino')->count()
            ];

            return view('contabilidad.registros.bancos-transferencias', compact(
                'transferencias',
                'resumenTransferencias',
                'fechaInicio',
                'fechaFin'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al cargar transferencias: ' . $e->getMessage());
        }
    }

    /**
     * Get monthly bank summary
     * Route: /contabilidad/bancos/mensual
     */
    /**
 * Get monthly bank summary
 * Route: /contabilidad/bancos/mensual
 */
    public function resumenMensual(Request $request)
    {
        try {
            $anio = (int) $request->input('anio', Carbon::now()->year);
            
            // ✅ Extraer SOLO el número del mes, incluso si llega como '2025-10'
            $mesInput = $request->input('mes', Carbon::now()->month);
            if (is_string($mesInput) && preg_match('/^(\d{4})-(\d{1,2})$/', $mesInput, $matches)) {
                $mes = (int) $matches[2]; // Ej: de '2025-10' → 10
            } else {
                $mes = (int) $mesInput;
            }

            // Validar rango del mes
            if ($mes < 1 || $mes > 12) {
                $mes = Carbon::now()->month;
            }

            // ✅ Nombre CORRECTO de la vista: v_resumen_mensual_bancos
            $resumenMensual = DB::table('v_resumen_mensual_bancos')
                ->where('anio', $anio)
                ->where('mes', $mes)
                ->get();

            // Fechas del mes para el detalle diario
            $fechaInicio = Carbon::create($anio, $mes, 1)->startOfMonth()->format('Y-m-d');
            $fechaFin = Carbon::create($anio, $mes, 1)->endOfMonth()->format('Y-m-d');

            // Detalle diario usando la vista correcta
            $detalleDiario = DB::table('v_bancos_con_descripciones')
                ->whereBetween('Fecha', [$fechaInicio, $fechaFin])
                ->select([
                    DB::raw('DAY(Fecha) as dia'),
                    'Banco',
                    DB::raw('SUM(ingreso) as ingresos'),
                    DB::raw('SUM(egreso) as egresos'),
                    DB::raw('COUNT(*) as movimientos')
                ])
                ->groupBy(DB::raw('DAY(Fecha)'), 'Banco')
                ->orderBy('dia')
                ->get();

            // Totales del mes
            $totalesMes = [
                'total_ingresos' => $resumenMensual->sum('ingresos_mes'),
                'total_egresos' => $resumenMensual->sum('egresos_mes'),
                'total_movimientos' => $resumenMensual->sum('total_movimientos')
            ];

            // Lista de meses para el selector
            $meses = [
                1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
            ];

            return view('contabilidad.registros.bancos-mensual', compact(
                'resumenMensual',
                'detalleDiario',
                'totalesMes',
                'anio',
                'mes',
                'meses'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al cargar resumen mensual: ' . $e->getMessage());
        }
    }

    /**
     * Generate bank report
     * Route: /contabilidad/bancos/reporte
     */
    /**
 * Generate bank report
 * Route: /contabilidad/bancos/reporte
 */
    public function generarReporte(Request $request)
    {
        try {
            $tipoReporte = $request->input('tipo_reporte', 'general');
            $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
            $fechaFin = $request->input('fecha_fin', Carbon::now()->endOfMonth()->format('Y-m-d'));
            $cuenta = $request->input('cuenta');

            // Validar fechas
            $fechaInicio = Carbon::parse($fechaInicio)->format('Y-m-d');
            $fechaFin = Carbon::parse($fechaFin)->format('Y-m-d');

            // Obtener movimientos base
            $movimientos = DB::table('v_bancos_con_descripciones')
                ->whereBetween('Fecha', [$fechaInicio, $fechaFin]);

            if ($cuenta) {
                $movimientos->where('Cuenta', $cuenta);
            }

            $movimientos = $movimientos->get();

            // Obtener lista de bancos (solo para el filtro)
            $listaBancos = DB::table('Bancos')->select('Cuenta', 'Banco')->orderBy('Banco')->get();

            // === Construir $datosReporte según el tipo de reporte ===
            $datosReporte = [];

            switch ($tipoReporte) {
                case 'flujo':
                    // Top 10 ingresos
                    $topIngresos = $movimientos
                        ->where('Tipo', 1)
                        ->sortByDesc('Monto')
                        ->take(10)
                        ->values();

                    // Top 10 egresos
                    $topEgresos = $movimientos
                        ->where('Tipo', 2)
                        ->sortByDesc('Monto')
                        ->take(10)
                        ->values();

                    $datosReporte = [
                        'top_ingresos' => $topIngresos,
                        'top_egresos' => $topEgresos,
                    ];
                    break;

                case 'general':
                case 'comparativo':
                    // Agrupar por banco
                    $porBanco = DB::table('v_resumen_mensual_bancos')
                        ->whereRaw("DATEFROMPARTS(anio, mes, 1) BETWEEN ? AND ?", [$fechaInicio, $fechaFin])
                        ->when($cuenta, fn($q) => $q->where('Cuenta', $cuenta))
                        ->get()
                        ->groupBy('Cuenta')
                        ->map(function ($grupo, $cuenta) use ($listaBancos) {
                            $bancoInfo = $listaBancos->firstWhere('Cuenta', $cuenta);
                            $totalIngresos = $grupo->sum('ingresos_mes');
                            $totalEgresos = $grupo->sum('egresos_mes');
                            return [
                                'nombre' => $bancoInfo ? $bancoInfo->Banco : "Cuenta: $cuenta",
                                'tipo' => 'Corriente',
                                'numero_cuenta' => $cuenta,
                                'saldo_inicial' => 0, // No se calcula sin vista de saldos iniciales
                                'ingresos' => $totalIngresos,
                                'egresos' => $totalEgresos,
                                'saldo_actual' => $totalIngresos - $totalEgresos,
                                'estado' => 'activo',
                            ];
                        })
                        ->values();

                    $totalIngresos = $porBanco->sum('ingresos');
                    $totalEgresos = $porBanco->sum('egresos');

                    $datosReporte = [
                        'saldo_total' => $totalIngresos - $totalEgresos,
                        'total_ingresos' => $totalIngresos,
                        'total_egresos' => $totalEgresos,
                        'flujo_neto' => $totalIngresos - $totalEgresos,
                        'por_banco' => $porBanco,
                    ];
                    break;

                case 'conciliacion':
                    $totalMovs = $movimientos->count();
                    $conciliados = $movimientos->where('Documento', 'like', '%CONCILIADO%')->count();
                    $pendientes = $totalMovs - $conciliados;
                    $porcentaje = $totalMovs > 0 ? ($conciliados / $totalMovs) * 100 : 0;

                    $porBancoConc = $movimientos->groupBy('Cuenta')->map(function ($grupo, $cuenta) use ($listaBancos) {
                        $total = $grupo->count();
                        $conc = $grupo->where('Documento', 'like', '%CONCILIADO%')->count();
                        $pend = $total - $conc;
                        $porc = $total > 0 ? ($conc / $total) * 100 : 0;
                        $bancoNombre = $listaBancos->firstWhere('Cuenta', $cuenta)?->Banco ?? "Cuenta: $cuenta";
                        return [
                            'banco' => $bancoNombre,
                            'total' => $total,
                            'conciliados' => $conc,
                            'pendientes' => $pend,
                            'porcentaje' => $porc,
                            'diferencia' => $grupo->sum('ingreso') - $grupo->sum('egreso'),
                        ];
                    })->values();

                    $datosReporte = [
                        'conciliados' => $conciliados,
                        'pendientes' => $pendientes,
                        'porcentaje' => $porcentaje,
                        'por_banco_conc' => $porBancoConc,
                    ];
                    break;

                default:
                    // Movimientos detallados (tipo 'movimientos')
                    $datosReporte = [
                        'movimientos' => $movimientos,
                    ];
                    break;
            }

            // Variables para la vista
            return view('contabilidad.registros.bancos-reporte', compact(
                'datosReporte',
                'listaBancos',
                'fechaInicio',
                'fechaFin',
                'cuenta',
                'tipoReporte'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al generar reporte: ' . $e->getMessage());
        }
    }
    /**
     * Show daily cash flow from banks
     * Route: /contabilidad/bancos/flujo-diario
     */
    public function flujoDiario(Request $request)
    {
        try {
            $fecha = $request->input('fecha', Carbon::now()->format('Y-m-d'));
            $bancoId = $request->input('banco_id');

            // Lista de bancos para el filtro
            $bancos = DB::table('Bancos')->orderBy('Banco')->get();

            // Ejecuta SP que devuelve los saldos
            $flujoCaja = DB::select('EXEC sp_flujo_caja_bancario ?', [$fecha]);

            // Movimientos del día
            $query = DB::table('v_bancos_con_descripciones')
                ->whereDate('Fecha', $fecha)
                ->orderBy('Cuenta')
                ->orderBy('Numero');

            if ($bancoId) {
                $query->where('BancoID', $bancoId);
            }

            $movimientosDia = $query->get();
            $movimientos = $movimientosDia;

            // Totales generales (usa valores del SP)
            $saldoInicial = collect($flujoCaja)->sum('saldo_inicial');
            $totalIngresos = collect($flujoCaja)->sum('ingresos_dia');
            $totalEgresos = collect($flujoCaja)->sum('egresos_dia');
            $saldoFinal = collect($flujoCaja)->sum('saldo_final');

            // Si la vista también muestra un resumen general:
            $totalesGenerales = [
                'saldo_inicial_total' => $saldoInicial,
                'ingresos_total' => $totalIngresos,
                'egresos_total' => $totalEgresos,
                'saldo_final_total' => $saldoFinal,
            ];

            return view('contabilidad.registros.bancos-flujo-diario', compact(
                'bancos',
                'flujoCaja',
                'movimientosDia',
                'movimientos', 
                'totalesGenerales',
                'fecha',
                'bancoId',
                'saldoInicial',   // 👈 agregado
                'totalIngresos',  // 👈 agregado
                'totalEgresos',   // 👈 agregado
                'saldoFinal'      // 👈 agregado
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al cargar flujo diario: ' . $e->getMessage());
        }
    }


    /**
     * Export to Excel
     */
    public function exportarExcel(Request $request)
    {
        // Implementar exportación si es necesario
        return response()->json(['message' => 'Función de exportación en desarrollo']);
    }
}