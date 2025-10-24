<?php

namespace App\Http\Controllers\Contabilidad;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class BalanceComprobacionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfYear()->format('Y-m-d'));
            $fechaFin = $request->input('fecha_fin', Carbon::now()->endOfYear()->format('Y-m-d'));

            // Obtener movimientos por cuenta desde el inicio hasta la fecha fin
            $balances = DB::table('t_detalle_diario as a')
                ->whereBetween('a.FechaF', [$fechaInicio, $fechaFin])
                ->select([
                    'a.Tipo as cuenta',
                    DB::raw('SUM(CAST(a.Importe as MONEY)) as saldo_deudor'),
                    DB::raw('SUM(CAST(a.Saldo as MONEY)) as saldo_acredor'),
                    DB::raw('COUNT(*) as movimientos')
                ])
                ->groupBy('a.Tipo')
                ->orderBy('a.Tipo')
                ->get();

            // Clasificar cuentas en deudoras o acreedoras
            $cuentasDeudoras = [];
            $cuentasAcreedoras = [];
            $totalDeudor = 0;
            $totalAcreedor = 0;

            foreach ($balances as $balance) {
                $saldoNeto = $balance->saldo_deudor - $balance->saldo_acredor;
                
                if ($saldoNeto > 0) {
                    // Cuenta deudora (Activo, Gastos)
                    $cuentasDeudoras[] = [
                        'cuenta' => $balance->cuenta,
                        'saldo' => $saldoNeto,
                        'movimientos' => $balance->movimientos
                    ];
                    $totalDeudor += $saldoNeto;
                } else {
                    // Cuenta acreedora (Pasivo, Patrimonio, Ingresos)
                    $cuentasAcreedoras[] = [
                        'cuenta' => $balance->cuenta,
                        'saldo' => abs($saldoNeto),
                        'movimientos' => $balance->movimientos
                    ];
                    $totalAcreedor += abs($saldoNeto);
                }
            }

            // Verificar que cuadre
            $diferencia = abs($totalDeudor - $totalAcreedor);
            $cuadra = $diferencia < 0.01;

            // Obtener resúmenes por clases de cuentas
            $resumenClases = $this->obtenerResumenPorClases($fechaInicio, $fechaFin);

            // Obtener estadísticas del período
            $estadisticas = $this->obtenerEstadisticas($fechaInicio, $fechaFin);

            return view('contabilidad.libros.balance-comprobacion.index', compact(
                'cuentasDeudoras', 'cuentasAcreedoras', 'totalDeudor', 'totalAcreedor', 
                'diferencia', 'cuadra', 'fechaInicio', 'fechaFin', 'resumenClases', 'estadisticas'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al cargar el balance de comprobación: ' . $e->getMessage());
        }
    }

    /**
     * Show detailed balance for a specific account
     */
    public function detalleCuenta($cuenta, Request $request)
    {
        try {
            $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfYear()->format('Y-m-d'));
            $fechaFin = $request->input('fecha_fin', Carbon::now()->endOfYear()->format('Y-m-d'));

            // Obtener todos los movimientos de la cuenta
            $movimientos = DB::table('t_detalle_diario')
                ->where('Tipo', $cuenta)
                ->whereBetween('FechaF', [$fechaInicio, $fechaFin])
                ->select([
                    'Numero as numero',
                    'FechaF as fecha',
                    'Descripcion as concepto',
                    'Importe as debito',
                    'Saldo as credito',
                    'Nombre as auxiliar'
                ])
                ->orderBy('FechaF')
                ->orderBy('Numero')
                ->get();

            // Calcular saldos acumulados
            $saldo = 0;
            foreach ($movimientos as $movimiento) {
                $saldo += $movimiento->debito - $movimiento->credito;
                $movimiento->saldo_acumulado = $saldo;
            }

            // Totales
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

    /**
     * Get balance by account classes (Activo, Pasivo, Patrimonio, Ingresos, Gastos)
     */
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

    /**
     * Compare balance with previous period
     */
    public function comparacion(Request $request)
    {
        try {
            $fechaActual = Carbon::now();
            
            // Período actual
            $periodoActual = [
                'inicio' => $request->input('fecha_inicio', $fechaActual->startOfYear()->format('Y-m-d')),
                'fin' => $request->input('fecha_fin', $fechaActual->endOfYear()->format('Y-m-d'))
            ];
            
            // Período anterior (año anterior)
            $periodoAnterior = [
                'inicio' => Carbon::parse($periodoActual['inicio'])->subYear()->format('Y-m-d'),
                'fin' => Carbon::parse($periodoActual['fin'])->subYear()->format('Y-m-d')
            ];

            // Balances comparativos
            $balanceActual = $this->calcularBalance($periodoActual['inicio'], $periodoActual['fin']);
            $balanceAnterior = $this->calcularBalance($periodoAnterior['inicio'], $periodoAnterior['fin']);

            return view('contabilidad.libros.balance-comprobacion.comparacion', compact(
                'balanceActual', 'balanceAnterior', 'periodoActual', 'periodoAnterior'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al generar comparación: ' . $e->getMessage());
        }
    }

    /**
     * Verify balance integrity
     */
    public function verificar(Request $request)
    {
        try {
            $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfYear()->format('Y-m-d'));
            $fechaFin = $request->input('fecha_fin', Carbon::now()->endOfYear()->format('Y-m-d'));

            // Verificar asientos desequilibrados
            $asientosDesequilibrados = DB::table('t_detalle_diario')
                ->select('Numero', DB::raw('SUM(CAST(Importe as MONEY)) as total_debe'), DB::raw('SUM(CAST(Saldo as MONEY)) as total_haber'))
                ->whereBetween('FechaF', [$fechaInicio, $fechaFin])
                ->groupBy('Numero')
                ->havingRaw('ABS(SUM(CAST(Importe as MONEY)) - SUM(CAST(Saldo as MONEY))) > 0.01')
                ->get();

            // Verificar totales generales
            $totalDebe = DB::table('t_detalle_diario')
                ->whereBetween('FechaF', [$fechaInicio, $fechaFin])
                ->selectRaw('SUM(CAST(Importe as MONEY)) as total')
                ->value('total');

            $totalHaber = DB::table('t_detalle_diario')
                ->whereBetween('FechaF', [$fechaInicio, $fechaFin])
                ->selectRaw('SUM(CAST(Saldo as MONEY)) as total')
                ->value('total');

            $diferencia = abs($totalDebe - $totalHaber);

            // Verificar cuentas sin movimientos
            $cuentasSinMovimientos = $this->verificarCuentasSinMovimientos($fechaInicio, $fechaFin);

            // Estadísticas de verificación
            $estadisticas = [
                'total_asientos' => DB::table('t_detalle_diario')
                    ->whereBetween('FechaF', [$fechaInicio, $fechaFin])
                    ->distinct('Numero')
                    ->count('Numero'),
                'asientos_desequilibrados' => $asientosDesequilibrados->count(),
                'cuentas_con_movimientos' => DB::table('t_detalle_diario')
                    ->whereBetween('FechaF', [$fechaInicio, $fechaFin])
                    ->distinct('Tipo')
                    ->count('Tipo'),
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

    /**
     * Get summary by account classes
     */
    private function obtenerResumenPorClases($fechaInicio, $fechaFin)
    {
        $balances = DB::table('t_detalle_diario as a')
            ->whereBetween('a.FechaF', [$fechaInicio, $fechaFin])
            ->select([
                'a.Tipo as cuenta',
                DB::raw('SUM(CAST(a.Importe as MONEY)) as total_debe'),
                DB::raw('SUM(CAST(a.Saldo as MONEY)) as total_haber')
            ])
            ->groupBy('a.Tipo')
            ->get();

        $resumen = [
            'ACTIVO' => ['total_debe' => 0, 'total_haber' => 0],
            'PASIVO' => ['total_debe' => 0, 'total_haber' => 0],
            'PATRIMONIO' => ['total_debe' => 0, 'total_haber' => 0],
            'INGRESOS' => ['total_debe' => 0, 'total_haber' => 0],
            'GASTOS' => ['total_debe' => 0, 'total_haber' => 0]
        ];

        foreach ($balances as $balance) {
            $primeraLetra = substr($balance->cuenta, 0, 1);
            
            switch ($primeraLetra) {
                case '1':
                    $resumen['ACTIVO']['total_debe'] += max(0, $balance->total_debe - $balance->total_haber);
                    break;
                case '2':
                    $resumen['PASIVO']['total_haber'] += max(0, $balance->total_haber - $balance->total_debe);
                    break;
                case '3':
                    $resumen['PATRIMONIO']['total_haber'] += max(0, $balance->total_haber - $balance->total_debe);
                    break;
                case '4':
                    $resumen['INGRESOS']['total_haber'] += max(0, $balance->total_haber - $balance->total_debe);
                    break;
                case '5':
                    $resumen['GASTOS']['total_debe'] += max(0, $balance->total_debe - $balance->total_haber);
                    break;
            }
        }

        return $resumen;
    }

    /**
     * Get statistics for the period
     */
    private function obtenerEstadisticas($fechaInicio, $fechaFin)
    {
        return [
            'total_asientos' => DB::table('t_detalle_diario')
                ->whereBetween('FechaF', [$fechaInicio, $fechaFin])
                ->distinct('Numero')
                ->count('Numero'),
            'total_movimientos' => DB::table('t_detalle_diario')
                ->whereBetween('FechaF', [$fechaInicio, $fechaFin])
                ->count(),
            'cuentas_utilizadas' => DB::table('t_detalle_diario')
                ->whereBetween('FechaF', [$fechaInicio, $fechaFin])
                ->distinct('Tipo')
                ->count('Tipo'),
            'primer_asiento' => DB::table('t_detalle_diario')
                ->where('FechaF', '>=', $fechaInicio)
                ->min('FechaF'),
            'ultimo_asiento' => DB::table('t_detalle_diario')
                ->where('FechaF', '<=', $fechaFin)
                ->max('FechaF')
        ];
    }

    /**
     * Get accounts organized by classes
     */
    private function obtenerCuentasPorClases($fechaInicio, $fechaFin)
    {
        $balances = DB::table('t_detalle_diario as a')
            ->whereBetween('a.FechaF', [$fechaInicio, $fechaFin])
            ->select([
                'a.Tipo as cuenta',
                DB::raw('SUM(CAST(a.Importe as MONEY)) - SUM(CAST(a.Saldo as MONEY)) as saldo')
            ])
            ->groupBy('a.Tipo')
            ->get();

        $cuentasPorClase = [
            'ACTIVO' => [],
            'PASIVO' => [],
            'PATRIMONIO' => [],
            'INGRESOS' => [],
            'GASTOS' => []
        ];

        foreach ($balances as $balance) {
            $primeraLetra = substr($balance->cuenta, 0, 1);
            
            switch ($primeraLetra) {
                case '1':
                    if ($balance->saldo > 0) {
                        $cuentasPorClase['ACTIVO'][] = $balance;
                    }
                    break;
                case '2':
                    if ($balance->saldo < 0) {
                        $balance->saldo = abs($balance->saldo);
                        $cuentasPorClase['PASIVO'][] = $balance;
                    }
                    break;
                case '3':
                    if ($balance->saldo < 0) {
                        $balance->saldo = abs($balance->saldo);
                        $cuentasPorClase['PATRIMONIO'][] = $balance;
                    }
                    break;
                case '4':
                    if ($balance->saldo < 0) {
                        $balance->saldo = abs($balance->saldo);
                        $cuentasPorClase['INGRESOS'][] = $balance;
                    }
                    break;
                case '5':
                    if ($balance->saldo > 0) {
                        $cuentasPorClase['GASTOS'][] = $balance;
                    }
                    break;
            }
        }

        return $cuentasPorClase;
    }

    /**
     * Calculate balance for a specific period
     */
    private function calcularBalance($fechaInicio, $fechaFin)
    {
        $balances = DB::table('t_detalle_diario as a')
            ->whereBetween('a.FechaF', [$fechaInicio, $fechaFin])
            ->select([
                'a.Tipo as cuenta',
                DB::raw('SUM(CAST(a.Importe as MONEY)) as saldo_deudor'),
                DB::raw('SUM(CAST(a.Saldo as MONEY)) as saldo_acredor')
            ])
            ->groupBy('a.Tipo')
            ->get();

        $deudor = 0;
        $acreedor = 0;

        foreach ($balances as $balance) {
            $saldoNeto = $balance->saldo_deudor - $balance->saldo_acredor;
            
            if ($saldoNeto > 0) {
                $deudor += $saldoNeto;
            } else {
                $acreedor += abs($saldoNeto);
            }
        }

        return [
            'total_deudor' => $deudor,
            'total_acreedor' => $acreedor,
            'diferencia' => abs($deudor - $acreedor),
            'cuadra' => abs($deudor - $acreedor) < 0.01
        ];
    }

    /**
     * Verify accounts without movements
     */
    private function verificarCuentasSinMovimientos($fechaInicio, $fechaFin)
    {
        // Obtener todas las cuentas que aparecen en el período
        $cuentasConMovimientos = DB::table('t_detalle_diario')
            ->whereBetween('FechaF', [$fechaInicio, $fechaFin])
            ->distinct('Tipo')
            ->pluck('Tipo')
            ->toArray();

        // Buscar cuentas que no aparezcan en movimientos recientes pero sí en históricos
        $cuentasSinMovimientos = DB::table('t_detalle_diario')
            ->where('FechaF', '<', $fechaInicio)
            ->whereNotIn('Tipo', $cuentasConMovimientos)
            ->distinct('Tipo')
            ->limit(20)
            ->get();

        return $cuentasSinMovimientos;
    }
}