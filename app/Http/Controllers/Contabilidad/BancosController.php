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
     */
    public function index(Request $request)
    {
        try {
            $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
            $fechaFin = $request->input('fecha_fin', Carbon::now()->endOfMonth()->format('Y-m-d'));
            $cuenta = $request->input('cuenta');

            // Obtener libros de bancos usando la tabla CtaBanco
            $movimientosBancarios = DB::table('CtaBanco as cb')
                ->leftJoin('Bancos as b', 'cb.Cuenta', '=', 'b.Cuenta')
                ->whereBetween('cb.Fecha', [$fechaInicio, $fechaFin])
                ->select([
                    'cb.Numero',
                    'cb.Fecha',
                    'cb.Tipo',
                    'cb.Clase',
                    'cb.Cuenta',
                    'b.Banco',
                    'b.Moneda as moneda_banco',
                    'cb.Documento',
                    'cb.Monto',
                    DB::raw('CASE 
                        WHEN cb.Tipo = 1 THEN "INGRESO"
                        WHEN cb.Tipo = 2 THEN "EGRESO"
                        ELSE "OTRO"
                    END as tipo_movimiento')
                ])
                ->orderBy('cb.Fecha', 'desc')
                ->orderBy('cb.Numero', 'desc')
                ->paginate(50);

            // Resumen por cuenta bancaria
            $resumenCuentas = DB::table('CtaBanco as cb')
                ->leftJoin('Bancos as b', 'cb.Cuenta', '=', 'b.Cuenta')
                ->whereBetween('cb.Fecha', [$fechaInicio, $fechaFin])
                ->select([
                    'cb.Cuenta',
                    'b.Banco',
                    'b.Moneda as moneda_banco',
                    DB::raw('SUM(CASE WHEN cb.Tipo = 1 THEN cb.Monto ELSE 0 END) as total_ingresos'),
                    DB::raw('SUM(CASE WHEN cb.Tipo = 2 THEN cb.Monto ELSE 0 END) as total_egresos'),
                    DB::raw('COUNT(*) as total_movimientos')
                ])
                ->groupBy('cb.Cuenta', 'b.Banco', 'b.Moneda')
                ->orderBy('b.Banco')
                ->get();

            // Saldo actual por cuenta
            $saldosActuales = DB::table('Bancos as b')
                ->leftJoin('CtaBanco as cb', function($join) {
                    $join->on('b.Cuenta', '=', 'cb.Cuenta')
                         ->where('cb.Fecha', '<=', Carbon::now()->format('Y-m-d'));
                })
                ->select([
                    'b.Cuenta',
                    'b.Banco',
                    'b.Moneda',
                    DB::raw('SUM(CASE WHEN cb.Tipo = 1 THEN cb.Monto ELSE 0 END) - 
                            SUM(CASE WHEN cb.Tipo = 2 THEN cb.Monto ELSE 0 END) as saldo_actual')
                ])
                ->groupBy('b.Cuenta', 'b.Banco', 'b.Moneda')
                ->get();

            // Totales del período
            $totalesPeriodo = DB::table('CtaBanco')
                ->whereBetween('Fecha', [$fechaInicio, $fechaFin])
                ->select([
                    DB::raw('SUM(CASE WHEN Tipo = 1 THEN Monto ELSE 0 END) as total_ingresos'),
                    DB::raw('SUM(CASE WHEN Tipo = 2 THEN Monto ELSE 0 END) as total_egresos'),
                    DB::raw('COUNT(*) as total_movimientos')
                ])
                ->first();

            return view('contabilidad.registros.bancos', compact(
                'movimientosBancarios', 'resumenCuentas', 'saldosActuales', 
                'totalesPeriodo', 'fechaInicio', 'fechaFin', 'cuenta'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al cargar el libro de bancos: ' . $e->getMessage());
        }
    }

    /**
     * Show bank account detail
     */
    public function detalleCuenta($cuenta, Request $request)
    {
        try {
            $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfYear()->format('Y-m-d'));
            $fechaFin = $request->input('fecha_fin', Carbon::now()->endOfYear()->format('Y-m-d'));

            // Información de la cuenta
            $infoCuenta = DB::table('Bancos')
                ->where('Cuenta', $cuenta)
                ->first();

            if (!$infoCuenta) {
                return redirect()->route('libro-bancos')->with('error', 'Cuenta bancaria no encontrada');
            }

            // Movimientos de la cuenta
            $movimientos = DB::table('CtaBanco')
                ->where('Cuenta', $cuenta)
                ->whereBetween('Fecha', [$fechaInicio, $fechaFin])
                ->select([
                    'Numero',
                    'Fecha',
                    'Tipo',
                    'Clase',
                    'Documento',
                    'Monto'
                ])
                ->orderBy('Fecha', 'desc')
                ->orderBy('Numero', 'desc')
                ->paginate(100);

            // Saldo anterior al período
            $saldoAnterior = DB::table('CtaBanco')
                ->where('Cuenta', $cuenta)
                ->where('Fecha', '<', $fechaInicio)
                ->selectRaw('SUM(CASE WHEN Tipo = 1 THEN Monto ELSE -Monto END) as saldo')
                ->value('saldo') ?? 0;

            // Saldo final
            $saldoFinal = DB::table('CtaBanco')
                ->where('Cuenta', $cuenta)
                ->where('Fecha', '<=', $fechaFin)
                ->selectRaw('SUM(CASE WHEN Tipo = 1 THEN Monto ELSE -Monto END) as saldo')
                ->value('saldo') ?? 0;

            // Resumen mensual
            $resumenMensual = DB::table('CtaBanco')
                ->where('Cuenta', $cuenta)
                ->whereBetween('Fecha', [$fechaInicio, $fechaFin])
                ->select([
                    DB::raw('YEAR(Fecha) as anio'),
                    DB::raw('MONTH(Fecha) as mes'),
                    DB::raw('DATENAME(month, Fecha) as mes_nombre'),
                    DB::raw('SUM(CASE WHEN Tipo = 1 THEN Monto ELSE 0 END) as ingresos'),
                    DB::raw('SUM(CASE WHEN Tipo = 2 THEN Monto ELSE 0 END) as egresos'),
                    DB::raw('COUNT(*) as movimientos')
                ])
                ->groupBy('anio', 'mes', 'mes_nombre')
                ->orderBy('anio', 'desc')
                ->orderBy('mes', 'desc')
                ->get();

            return view('contabilidad.registros.bancos-detalle', compact(
                'infoCuenta', 'movimientos', 'saldoAnterior', 'saldoFinal', 
                'resumenMensual', 'fechaInicio', 'fechaFin'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al cargar detalle de cuenta: ' . $e->getMessage());
        }
    }

    /**
     * Get daily bank movements
     */
    public function movimientosDiarios(Request $request)
    {
        try {
            $fecha = $request->input('fecha', Carbon::now()->format('Y-m-d'));

            // Movimientos del día
            $movimientosDiarios = DB::table('CtaBanco as cb')
                ->leftJoin('Bancos as b', 'cb.Cuenta', '=', 'b.Cuenta')
                ->whereDate('cb.Fecha', $fecha)
                ->select([
                    'cb.Numero',
                    'cb.Fecha',
                    'cb.Tipo',
                    'cb.Cuenta',
                    'b.Banco',
                    'cb.Documento',
                    'cb.Monto',
                    'cb.Clase'
                ])
                ->orderBy('cb.Cuenta')
                ->orderBy('cb.Numero')
                ->get();

            // Resumen por banco
            $resumenPorBanco = $movimientosDiarios->groupBy('Banco')->map(function($grupo) {
                return [
                    'ingresos' => $grupo->where('Tipo', 1)->sum('Monto'),
                    'egresos' => $grupo->where('Tipo', 2)->sum('Monto'),
                    'movimientos' => $grupo->count()
                ];
            });

            // Totales
            $totalesDiarios = [
                'fecha' => $fecha,
                'total_ingresos' => $movimientosDiarios->where('Tipo', 1)->sum('Monto'),
                'total_egresos' => $movimientosDiarios->where('Tipo', 2)->sum('Monto'),
                'total_movimientos' => $movimientosDiarios->count()
            ];

            return view('contabilidad.registros.bancos-diarios', compact(
                'movimientosDiarios', 'resumenPorBanco', 'totalesDiarios', 'fecha'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al cargar movimientos diarios: ' . $e->getMessage());
        }
    }

    /**
     * Get reconciliation analysis
     */
    public function conciliacion(Request $request)
    {
        try {
            $cuenta = $request->input('cuenta');
            $fecha = $request->input('fecha', Carbon::now()->endOfMonth()->format('Y-m-d'));

            if (!$cuenta) {
                return redirect()->route('libro-bancos')->with('error', 'Debe seleccionar una cuenta');
            }

            // Saldo según libros
            $saldoLibros = DB::table('CtaBanco')
                ->where('Cuenta', $cuenta)
                ->where('Fecha', '<=', $fecha)
                ->selectRaw('SUM(CASE WHEN Tipo = 1 THEN Monto ELSE -Monto END) as saldo')
                ->value('saldo') ?? 0;

            // Obtener último saldo según estado de cuenta bancario
            // (En un proyecto real, esto vendría de un archivo de estado de cuenta)
            $saldoBancario = $this->obtenerSaldoBancario($cuenta, $fecha);

            // Movimientos no conciliar (cheques girados no cobrados, depósitos en tránsito, etc.)
            $movimientosNoConciliados = $this->obtenerMovimientosNoConciliados($cuenta, $fecha);

            // Diferencias encontradas
            $diferencias = [
                'diferencia_total' => $saldoBancario - $saldoLibros,
                'movimientos_no_conciliados' => $movimientosNoConciliados,
                'conciliado' => abs($saldoBancario - $saldoLibros) < 0.01
            ];

            // Información de la cuenta
            $infoCuenta = DB::table('Bancos')
                ->where('Cuenta', $cuenta)
                ->first();

            return view('contabilidad.registros.bancos-conciliacion', compact(
                'infoCuenta', 'saldoLibros', 'saldoBancario', 'diferencias', 'fecha'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error en conciliación: ' . $e->getMessage());
        }
    }

    /**
     * Get bank transfers
     */
    public function transferencias(Request $request)
    {
        try {
            $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
            $fechaFin = $request->input('fecha_fin', Carbon::now()->endOfMonth()->format('Y-m-d'));

            // Transferencias entre cuentas (Clase = 3)
            $transferencias = DB::table('CtaBanco as cb')
                ->leftJoin('Bancos as b', 'cb.Cuenta', '=', 'b.Cuenta')
                ->whereBetween('cb.Fecha', [$fechaInicio, $fechaFin])
                ->where('cb.Clase', 3) // Transferencias
                ->select([
                    'cb.Numero',
                    'cb.Fecha',
                    'cb.Cuenta',
                    'b.Banco',
                    'cb.Documento',
                    'cb.Monto'
                ])
                ->orderBy('cb.Fecha', 'desc')
                ->get();

            // Agrupar transferencias (entrada y salida)
            $transferenciasAgrupadas = [];
            foreach ($transferencias as $transferencia) {
                $clave = $transferencia->Numero . '-' . $transferencia->Documento;
                if (!isset($transferenciasAgrupadas[$clave])) {
                    $transferenciasAgrupadas[$clave] = [
                        'numero' => $transferencia->Numero,
                        'fecha' => $transferencia->Fecha,
                        'documento' => $transferencia->Documento,
                        'cuentas' => []
                    ];
                }
                $transferenciasAgrupadas[$clave]['cuentas'][] = $transferencia;
            }

            // Resumen de transferencias
            $resumenTransferencias = [
                'total_transferencias' => count($transferenciasAgrupadas),
                'monto_total' => $transferencias->sum('Monto'),
                'cuentas_involucradas' => $transferencias->unique('Cuenta')->count()
            ];

            return view('contabilidad.registros.bancos-transferencias', compact(
                'transferenciasAgrupadas', 'resumenTransferencias', 'fechaInicio', 'fechaFin'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al cargar transferencias: ' . $e->getMessage());
        }
    }

    /**
     * Get monthly bank summary
     */
    public function resumenMensual(Request $request)
    {
        try {
            $anio = $request->input('anio', Carbon::now()->year);
            $mes = $request->input('mes', Carbon::now()->month);

            $fechaInicio = Carbon::create($anio, $mes, 1)->startOfMonth()->format('Y-m-d');
            $fechaFin = Carbon::create($anio, $mes, 1)->endOfMonth()->format('Y-m-d');

            // Resumen diario del mes
            $resumenDiario = DB::table('CtaBanco as cb')
                ->leftJoin('Bancos as b', 'cb.Cuenta', '=', 'b.Cuenta')
                ->whereBetween('cb.Fecha', [$fechaInicio, $fechaFin])
                ->select([
                    DB::raw('DAY(cb.Fecha) as dia'),
                    'b.Banco',
                    DB::raw('SUM(CASE WHEN cb.Tipo = 1 THEN cb.Monto ELSE 0 END) as ingresos'),
                    DB::raw('SUM(CASE WHEN cb.Tipo = 2 THEN cb.Monto ELSE 0 END) as egresos'),
                    DB::raw('COUNT(*) as movimientos')
                ])
                ->groupBy('dia', 'b.Banco')
                ->orderBy('dia')
                ->get();

            // Saldo inicial y final del mes por banco
            $saldosMensuales = DB::table('Bancos as b')
                ->leftJoin('CtaBanco as cb', function($join) use ($fechaInicio, $fechaFin) {
                    $join->on('b.Cuenta', '=', 'cb.Cuenta')
                         ->whereBetween('cb.Fecha', [$fechaInicio, $fechaFin]);
                })
                ->select([
                    'b.Cuenta',
                    'b.Banco',
                    'b.Moneda',
                    DB::raw('SUM(CASE WHEN cb.Fecha < "' . $fechaInicio . '" AND cb.Tipo = 1 THEN cb.Monto 
                            WHEN cb.Fecha < "' . $fechaInicio . '" AND cb.Tipo = 2 THEN -cb.Monto 
                            ELSE 0 END) as saldo_inicial'),
                    DB::raw('SUM(CASE WHEN cb.Tipo = 1 THEN cb.Monto ELSE 0 END) as ingresos_mes'),
                    DB::raw('SUM(CASE WHEN cb.Tipo = 2 THEN cb.Monto ELSE 0 END) as egresos_mes'),
                    DB::raw('SUM(CASE WHEN cb.Tipo = 1 THEN cb.Monto WHEN cb.Tipo = 2 THEN -cb.Monto ELSE 0 END) as movimiento_neto')
                ])
                ->groupBy('b.Cuenta', 'b.Banco', 'b.Moneda')
                ->get()
                ->map(function($item) {
                    $item->saldo_final = $item->saldo_inicial + $item->movimiento_neto;
                    return $item;
                });

            // Totales del mes
            $totalesMes = DB::table('CtaBanco')
                ->whereBetween('Fecha', [$fechaInicio, $fechaFin])
                ->select([
                    DB::raw('SUM(CASE WHEN Tipo = 1 THEN Monto ELSE 0 END) as total_ingresos'),
                    DB::raw('SUM(CASE WHEN Tipo = 2 THEN Monto ELSE 0 END) as total_egresos'),
                    DB::raw('COUNT(*) as total_movimientos')
                ])
                ->first();

            return view('contabilidad.registros.bancos-mensual', compact(
                'resumenDiario', 'saldosMensuales', 'totalesMes', 'anio', 'mes'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al cargar resumen mensual: ' . $e->getMessage());
        }
    }

    /**
     * Generate bank report
     */
    public function generarReporte(Request $request)
    {
        try {
            $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
            $fechaFin = $request->input('fecha_fin', Carbon::now()->endOfMonth()->format('Y-m-d'));
            $cuenta = $request->input('cuenta');

            // Filtros aplicados
            $filtros = [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'cuenta' => $cuenta
            ];

            // Movimientos filtrados
            $query = DB::table('CtaBanco as cb')
                ->leftJoin('Bancos as b', 'cb.Cuenta', '=', 'b.Cuenta')
                ->whereBetween('cb.Fecha', [$fechaInicio, $fechaFin]);

            if ($cuenta) {
                $query->where('cb.Cuenta', $cuenta);
            }

            $movimientosReporte = $query->select([
                'cb.Fecha',
                'cb.Cuenta',
                'b.Banco',
                'cb.Documento',
                'cb.Tipo',
                'cb.Monto',
                'cb.Clase'
            ])
            ->orderBy('cb.Fecha')
            ->orderBy('cb.Cuenta')
            ->orderBy('cb.Numero')
            ->get();

            // Resumen ejecutivo
            $resumenEjecutivo = [
                'periodo' => $fechaInicio . ' al ' . $fechaFin,
                'total_movimientos' => $movimientosReporte->count(),
                'total_ingresos' => $movimientosReporte->where('Tipo', 1)->sum('Monto'),
                'total_egresos' => $movimientosReporte->where('Tipo', 2)->sum('Monto'),
                'saldo_neto' => $movimientosReporte->where('Tipo', 1)->sum('Monto') - 
                               $movimientosReporte->where('Tipo', 2)->sum('Monto'),
                'cuentas_involucradas' => $movimientosReporte->unique('Cuenta')->count()
            ];

            return view('contabilidad.registros.bancos-reporte', compact(
                'movimientosReporte', 'resumenEjecutivo', 'filtros'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al generar reporte: ' . $e->getMessage());
        }
    }

    /**
     * Get bank balance from external source (simulation)
     */
    private function obtenerSaldoBancario($cuenta, $fecha)
    {
        // En un proyecto real, esto vendría de una API del banco o archivo de estado de cuenta
        // Por ahora simulamos con un valor aleatorio basado en la cuenta
        
        $saldoSimulado = [
            '001' => 45000.00,
            '002' => 125000.50,
            '003' => 78000.25
        ];

        $codigoCuenta = substr($cuenta, 0, 3);
        return $saldoSimulado[$codigoCuenta] ?? 0;
    }

    /**
     * Get unconciliated movements
     */
    private function obtenerMovimientosNoConciliados($cuenta, $fecha)
    {
        // Cheques girados no cobrados (clase = 1)
        $chequesNoCobrados = DB::table('CtaBanco')
            ->where('Cuenta', $cuenta)
            ->where('Fecha', '<=', $fecha)
            ->where('Clase', 1)
            ->where('Tipo', 2) // Egresos (cheques girados)
            ->where('Documento', 'NOT LIKE', '%COBRADO%')
            ->select([
                'Numero',
                'Fecha',
                'Documento',
                'Monto'
            ])
            ->get();

        // Depósitos en tránsito (clase = 2)
        $depositosTransito = DB::table('CtaBanco')
            ->where('Cuenta', $cuenta)
            ->where('Fecha', '<=', $fecha)
            ->where('Clase', 2)
            ->where('Tipo', 1) // Ingresos (depósitos)
            ->where('Documento', 'NOT LIKE', '%CONCILIADO%')
            ->select([
                'Numero',
                'Fecha',
                'Documento',
                'Monto'
            ])
            ->get();

        return [
            'cheques_no_cobrados' => $chequesNoCobrados,
            'depositos_transito' => $depositosTransito
        ];
    }

    /**
     * Show daily cash flow from banks
     */
    public function flujoDiario(Request $request)
    {
        try {
            $fecha = $request->input('fecha', Carbon::now()->format('Y-m-d'));

            // Movimientos bancarios del día
            $movimientos = DB::table('CtaBanco as cb')
                ->leftJoin('Bancos as b', 'cb.Cuenta', '=', 'b.Cuenta')
                ->whereDate('cb.Fecha', $fecha)
                ->select([
                    'cb.Cuenta',
                    'b.Banco',
                    'cb.Tipo',
                    'cb.Documento',
                    'cb.Monto',
                    DB::raw('CASE 
                        WHEN cb.Clase = 1 THEN "Cheque"
                        WHEN cb.Clase = 2 THEN "Depósito"
                        WHEN cb.Clase = 3 THEN "Transferencia"
                        ELSE "Otros"
                    END as tipo_operacion')
                ])
                ->orderBy('cb.Cuenta')
                ->get();

            // Agrupar por cuenta y tipo
            $flujoPorCuenta = $movimientos->groupBy('Cuenta')->map(function($cuentaMovimientos) {
                return [
                    'banco' => $cuentaMovimientos->first()->Banco,
                    'ingresos' => $cuentaMovimientos->where('Tipo', 1)->sum('Monto'),
                    'egresos' => $cuentaMovimientos->where('Tipo', 2)->sum('Monto'),
                    'neto' => $cuentaMovimientos->where('Tipo', 1)->sum('Monto') - 
                             $cuentaMovimientos->where('Tipo', 2)->sum('Monto'),
                    'movimientos' => $cuentaMovimientos->count()
                ];
            });

            return view('contabilidad.registros.bancos-flujo-diario', compact(
                'movimientos', 'flujoPorCuenta', 'fecha'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al cargar flujo diario: ' . $e->getMessage());
        }
    }
}