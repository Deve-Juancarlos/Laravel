<?php

namespace App\Http\Controllers\Contabilidad;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class BalanceComprobacionController extends Controller
{
    public function index(Request $request)
    {
        try {
            $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfYear()->format('Y-m-d'));
            $fechaFin = $request->input('fecha_fin', Carbon::now()->endOfYear()->format('Y-m-d'));

            // Obtener saldos por cuenta contable en el período
            $balances = DB::table('libro_diario_detalles as d')
                ->join('libro_diario as c', 'd.asiento_id', '=', 'c.id')
                ->whereBetween('c.fecha', [$fechaInicio, $fechaFin])
                ->where('c.estado', 'ACTIVO')
                ->select([
                    'd.cuenta_contable as cuenta',
                    DB::raw('SUM(d.debe) as saldo_deudor'),
                    DB::raw('SUM(d.haber) as saldo_acredor'),
                    DB::raw('COUNT(*) as movimientos')
                ])
                ->groupBy('d.cuenta_contable')
                ->orderBy('d.cuenta_contable')
                ->get();

            // Clasificar en deudoras y acreedoras
            $cuentasDeudoras = [];
            $cuentasAcreedoras = [];
            $totalDeudor = 0;
            $totalAcreedor = 0;

            foreach ($balances as $balance) {
                $saldoNeto = $balance->saldo_deudor - $balance->saldo_acredor;

                if ($saldoNeto > 0) {
                    $cuentasDeudoras[] = [
                        'cuenta' => $balance->cuenta,
                        'saldo' => $saldoNeto,
                        'movimientos' => $balance->movimientos
                    ];
                    $totalDeudor += $saldoNeto;
                } else {
                    $cuentasAcreedoras[] = [
                        'cuenta' => $balance->cuenta,
                        'saldo' => abs($saldoNeto),
                        'movimientos' => $balance->movimientos
                    ];
                    $totalAcreedor += abs($saldoNeto);
                }
            }

            $diferencia = abs($totalDeudor - $totalAcreedor);
            $cuadra = $diferencia < 0.01;

            // Resumen por clases (usando primer dígito del código)
            $resumenClases = $this->obtenerResumenPorClases($fechaInicio, $fechaFin);

            // Estadísticas del período
            $estadisticas = $this->obtenerEstadisticas($fechaInicio, $fechaFin);

            return view('contabilidad.libros.balance-comprobacion.index', compact(
                'cuentasDeudoras', 'cuentasAcreedoras', 'totalDeudor', 'totalAcreedor',
                'diferencia', 'cuadra', 'fechaInicio', 'fechaFin', 'resumenClases', 'estadisticas'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al cargar el balance de comprobación: ' . $e->getMessage());
        }
    }

    public function detalleCuenta($cuenta, Request $request)
    {
        try {
            $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfYear()->format('Y-m-d'));
            $fechaFin = $request->input('fecha_fin', Carbon::now()->endOfYear()->format('Y-m-d'));

            $movimientos = DB::table('libro_diario_detalles as d')
                ->join('libro_diario as c', 'd.asiento_id', '=', 'c.id')
                ->where('d.cuenta_contable', $cuenta)
                ->whereBetween('c.fecha', [$fechaInicio, $fechaFin])
                ->where('c.estado', 'ACTIVO')
                ->select([
                    'c.numero',
                    'c.fecha',
                    'd.concepto',
                    'd.debe as debito',
                    'd.haber as credito',
                    DB::raw("'' as auxiliar") // opcional: podrías enlazar con terceros si aplica
                ])
                ->orderBy('c.fecha')
                ->orderBy('c.numero')
                ->get();

            $saldo = 0;
            foreach ($movimientos as $mov) {
                $saldo += $mov->debito - $mov->credito;
                $mov->saldo_acumulado = $saldo;
            }

            $totales = [
                'total_debito' => $movimientos->sum('debito'),
                'total_credito' => $movimientos->sum('credito'),
                'saldo_final' => $saldo
            ];

            return view('contabilidad.libros.balance-comprobacion.detalle', compact(
                'cuenta', 'movimientos', 'totales', 'fechaInicio', 'fechaFin'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al cargar el detalle de la cuenta: ' . $e->getMessage());
        }
    }

    public function porClases(Request $request)
    {
        try {
            $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfYear()->format('Y-m-d'));
            $fechaFin = $request->input('fecha_fin', Carbon::now()->endOfYear()->format('Y-m-d'));

            $cuentasPorClase = $this->obtenerCuentasPorClases($fechaInicio, $fechaFin);

            return view('contabilidad.libros.balance-comprobacion.clases', compact(
                'cuentasPorClase', 'fechaInicio', 'fechaFin'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al cargar balance por clases: ' . $e->getMessage());
        }
    }

    public function comparacion(Request $request)
    {
        try {
            $fechaActual = Carbon::now();
            $periodoActual = [
                'inicio' => $request->input('fecha_inicio', $fechaActual->startOfYear()->format('Y-m-d')),
                'fin' => $request->input('fecha_fin', $fechaActual->endOfYear()->format('Y-m-d'))
            ];

            $periodoAnterior = [
                'inicio' => Carbon::parse($periodoActual['inicio'])->subYear()->format('Y-m-d'),
                'fin' => Carbon::parse($periodoActual['fin'])->subYear()->format('Y-m-d')
            ];

            $balanceActual = $this->calcularBalance($periodoActual['inicio'], $periodoActual['fin']);
            $balanceAnterior = $this->calcularBalance($periodoAnterior['inicio'], $periodoAnterior['fin']);

            return view('contabilidad.libros.balance-comprobacion.comparacion', compact(
                'balanceActual', 'balanceAnterior', 'periodoActual', 'periodoAnterior'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al generar comparación: ' . $e->getMessage());
        }
    }

    public function verificar(Request $request)
    {
        try {
            $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfYear()->format('Y-m-d'));
            $fechaFin = $request->input('fecha_fin', Carbon::now()->endOfYear()->format('Y-m-d'));

            // Asientos desequilibrados
            $asientosDesequilibrados = DB::table('libro_diario')
                ->select(
                    'id',
                    'numero',
                    'total_debe',
                    'total_haber',
                    DB::raw('ABS(total_debe - total_haber) as diferencia')
                )
                ->whereBetween('fecha', [$fechaInicio, $fechaFin])
                ->where('estado', 'ACTIVO')
                ->whereRaw('ABS(total_debe - total_haber) > 0.01')
                ->get();

            // Totales generales
            $totalDebe = DB::table('libro_diario_detalles as d')
                ->join('libro_diario as c', 'd.asiento_id', '=', 'c.id')
                ->whereBetween('c.fecha', [$fechaInicio, $fechaFin])
                ->where('c.estado', 'ACTIVO')
                ->sum('d.debe');

            $totalHaber = DB::table('libro_diario_detalles as d')
                ->join('libro_diario as c', 'd.asiento_id', '=', 'c.id')
                ->whereBetween('c.fecha', [$fechaInicio, $fechaFin])
                ->where('c.estado', 'ACTIVO')
                ->sum('d.haber');

            $diferencia = abs($totalDebe - $totalHaber);

            // Cuentas sin movimientos (opcional: puedes ajustar según necesidad)
            $cuentasConMov = DB::table('libro_diario_detalles as d')
                ->join('libro_diario as c', 'd.asiento_id', '=', 'c.id')
                ->whereBetween('c.fecha', [$fechaInicio, $fechaFin])
                ->where('c.estado', 'ACTIVO')
                ->pluck('d.cuenta_contable')
                ->toArray();

            $cuentasSinMovimientos = DB::table('plan_cuentas')
                ->where('activo', 1)
                ->whereNotIn('codigo', $cuentasConMov)
                ->limit(20)
                ->get(['codigo as cuenta']);

            $estadisticas = [
                'total_asientos' => DB::table('libro_diario')
                    ->whereBetween('fecha', [$fechaInicio, $fechaFin])
                    ->where('estado', 'ACTIVO')
                    ->count(),
                'asientos_desequilibrados' => $asientosDesequilibrados->count(),
                'cuentas_con_movimientos' => count($cuentasConMov),
                'total_debe' => $totalDebe,
                'total_haber' => $totalHaber,
                'diferencia' => $diferencia,
                'cuadra' => $diferencia < 0.01
            ];

            return view('contabilidad.libros.balance-comprobacion.verificacion', compact(
                'asientosDesequilibrados', 'cuentasSinMovimientos', 'estadisticas', 'fechaInicio', 'fechaFin'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al verificar balance: ' . $e->getMessage());
        }
    }

    // Métodos privados actualizados

    private function obtenerResumenPorClases($fechaInicio, $fechaFin)
    {
        $balances = DB::table('libro_diario_detalles as d')
            ->join('libro_diario as c', 'd.asiento_id', '=', 'c.id')
            ->whereBetween('c.fecha', [$fechaInicio, $fechaFin])
            ->where('c.estado', 'ACTIVO')
            ->select([
                'd.cuenta_contable as cuenta',
                DB::raw('SUM(d.debe) as total_debe'),
                DB::raw('SUM(d.haber) as total_haber')
            ])
            ->groupBy('d.cuenta_contable')
            ->get();

        $resumen = [
            'ACTIVO' => ['total_debe' => 0, 'total_haber' => 0],
            'PASIVO' => ['total_debe' => 0, 'total_haber' => 0],
            'PATRIMONIO' => ['total_debe' => 0, 'total_haber' => 0],
            'INGRESOS' => ['total_debe' => 0, 'total_haber' => 0],
            'GASTOS' => ['total_debe' => 0, 'total_haber' => 0]
        ];

        foreach ($balances as $b) {
            $codigo = $b->cuenta;
            $primera = substr($codigo, 0, 1);
            $neto = $b->total_debe - $b->total_haber;

            switch ($primera) {
                case '1': // Activo
                    $resumen['ACTIVO']['total_debe'] += max(0, $neto);
                    break;
                case '2': // Pasivo
                    $resumen['PASIVO']['total_haber'] += max(0, -$neto);
                    break;
                case '3': // Patrimonio
                    $resumen['PATRIMONIO']['total_haber'] += max(0, -$neto);
                    break;
                case '4': // Ingresos
                    $resumen['INGRESOS']['total_haber'] += max(0, -$neto);
                    break;
                case '5':
                case '6':
                case '9': // Gastos
                    $resumen['GASTOS']['total_debe'] += max(0, $neto);
                    break;
            }
        }

        return $resumen;
    }

    private function obtenerEstadisticas($fechaInicio, $fechaFin)
    {
        return [
            'total_asientos' => DB::table('libro_diario')
                ->whereBetween('fecha', [$fechaInicio, $fechaFin])
                ->where('estado', 'ACTIVO')
                ->count(),
            'total_movimientos' => DB::table('libro_diario_detalles as d')
                ->join('libro_diario as c', 'd.asiento_id', '=', 'c.id')
                ->whereBetween('c.fecha', [$fechaInicio, $fechaFin])
                ->where('c.estado', 'ACTIVO')
                ->count(),
            'cuentas_utilizadas' => DB::table('libro_diario_detalles as d')
                ->join('libro_diario as c', 'd.asiento_id', '=', 'c.id')
                ->whereBetween('c.fecha', [$fechaInicio, $fechaFin])
                ->where('c.estado', 'ACTIVO')
                ->distinct('d.cuenta_contable')
                ->count(),
            'primer_asiento' => DB::table('libro_diario')
                ->whereBetween('fecha', [$fechaInicio, $fechaFin])
                ->where('estado', 'ACTIVO')
                ->min('fecha'),
            'ultimo_asiento' => DB::table('libro_diario')
                ->whereBetween('fecha', [$fechaInicio, $fechaFin])
                ->where('estado', 'ACTIVO')
                ->max('fecha')
        ];
    }

    private function obtenerCuentasPorClases($fechaInicio, $fechaFin)
    {
        $balances = DB::table('libro_diario_detalles as d')
            ->join('libro_diario as c', 'd.asiento_id', '=', 'c.id')
            ->whereBetween('c.fecha', [$fechaInicio, $fechaFin])
            ->where('c.estado', 'ACTIVO')
            ->select([
                'd.cuenta_contable as cuenta',
                DB::raw('SUM(d.debe) - SUM(d.haber) as saldo')
            ])
            ->groupBy('d.cuenta_contable')
            ->get();

        $cuentasPorClase = [
            'ACTIVO' => [],
            'PASIVO' => [],
            'PATRIMONIO' => [],
            'INGRESOS' => [],
            'GASTOS' => []
        ];

        foreach ($balances as $b) {
            $codigo = $b->cuenta;
            $primera = substr($codigo, 0, 1);
            $saldo = $b->saldo;

            switch ($primera) {
                case '1':
                    if ($saldo > 0) $cuentasPorClase['ACTIVO'][] = $b;
                    break;
                case '2':
                    if ($saldo < 0) { $b->saldo = abs($saldo); $cuentasPorClase['PASIVO'][] = $b; }
                    break;
                case '3':
                    if ($saldo < 0) { $b->saldo = abs($saldo); $cuentasPorClase['PATRIMONIO'][] = $b; }
                    break;
                case '4':
                    if ($saldo < 0) { $b->saldo = abs($saldo); $cuentasPorClase['INGRESOS'][] = $b; }
                    break;
                case '5':
                case '6':
                case '9':
                    if ($saldo > 0) $cuentasPorClase['GASTOS'][] = $b;
                    break;
            }
        }

        return $cuentasPorClase;
    }

    private function calcularBalance($fechaInicio, $fechaFin)
    {
        $deudor = 0;
        $acreedor = 0;

        $balances = DB::table('libro_diario_detalles as d')
            ->join('libro_diario as c', 'd.asiento_id', '=', 'c.id')
            ->whereBetween('c.fecha', [$fechaInicio, $fechaFin])
            ->where('c.estado', 'ACTIVO')
            ->selectRaw('cuenta_contable, SUM(debe) as debe, SUM(haber) as haber')
            ->groupBy('cuenta_contable')
            ->get();

        foreach ($balances as $b) {
            $neto = $b->debe - $b->haber;
            if ($neto > 0) $deudor += $neto;
            else $acreedor += abs($neto);
        }

        return [
            'total_deudor' => $deudor,
            'total_acreedor' => $acreedor,
            'diferencia' => abs($deudor - $acreedor),
            'cuadra' => abs($deudor - $acreedor) < 0.01
        ];
    }
}