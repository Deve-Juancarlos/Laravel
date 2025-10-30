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
            $cuentasPorClase = $this->obtenerCuentasPorClasesVista($fechaInicio, $fechaFin);


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

    public function detalleCuenta(Request $request)
    {
        try {
            $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfYear()->format('Y-m-d'));
            $fechaFin = $request->input('fecha_fin', Carbon::now()->endOfYear()->format('Y-m-d'));

            // Obtener todas las cuentas utilizadas en el período
            $balances = DB::table('libro_diario_detalles as d')
                ->join('libro_diario as c', 'd.asiento_id', '=', 'c.id')
                ->whereBetween('c.fecha', [$fechaInicio, $fechaFin])
                ->where('c.estado', 'ACTIVO')
                ->select('d.cuenta_contable')
                ->groupBy('d.cuenta_contable')
                ->pluck('cuenta_contable');

            // Obtener movimientos de todas las cuentas utilizadas
            $movimientos = DB::table('libro_diario_detalles as d')
                ->join('libro_diario as c', 'd.asiento_id', '=', 'c.id')
                ->whereIn('d.cuenta_contable', $balances)
                ->whereBetween('c.fecha', [$fechaInicio, $fechaFin])
                ->where('c.estado', 'ACTIVO')
                ->select([
                    'd.cuenta_contable as cuenta',
                    'c.numero',
                    'c.fecha',
                    'd.concepto',
                    'd.debe as debito',
                    'd.haber as credito',
                    DB::raw("'' as auxiliar")
                ])
                ->orderBy('d.cuenta_contable')
                ->orderBy('c.fecha')
                ->orderBy('c.numero')
                ->get();

            // Calcular saldo acumulado por cuenta
            $saldos = [];
            foreach ($movimientos as $mov) {
                if (!isset($saldos[$mov->cuenta])) $saldos[$mov->cuenta] = 0;
                $saldos[$mov->cuenta] += $mov->debito - $mov->credito;
                $mov->saldo_acumulado = $saldos[$mov->cuenta];
            }

            // Agregar cuentas con saldo cero que no tengan movimientos
            foreach ($balances as $cuenta) {
                if (!isset($saldos[$cuenta])) {
                    $mov = new \stdClass();
                    $mov->cuenta = $cuenta;
                    $mov->numero = '-';
                    $mov->fecha = '-';
                    $mov->concepto = '-';
                    $mov->debito = 0;
                    $mov->credito = 0;
                    $mov->saldo_acumulado = 0;
                    $mov->auxiliar = '-';
                    $movimientos->push($mov);
                }
            }

            $totales = [
                'total_debito' => $movimientos->sum('debito'),
                'total_credito' => $movimientos->sum('credito'),
                'saldo_final' => $movimientos->sum('debito') - $movimientos->sum('credito')
            ];

            return view('contabilidad.libros.balance-comprobacion.detalle', compact(
                'movimientos', 'totales', 'fechaInicio', 'fechaFin'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al cargar el detalle de las cuentas: ' . $e->getMessage());
        }
    }    
    private function obtenerCuentasPorClasesVista(string $fechaInicio, string $fechaFin): array
    {
        // Consulta todos los saldos netos por cuenta en el período
        $balances = DB::table('libro_diario_detalles as d')
            ->join('libro_diario as c', 'd.asiento_id', '=', 'c.id')
            ->whereBetween('c.fecha', [$fechaInicio, $fechaFin])
            ->where('c.estado', 'ACTIVO')
            ->select([
                'd.cuenta_contable as cuenta',
                DB::raw('SUM(d.debe) - SUM(d.haber) as saldo')
            ])
            ->groupBy('d.cuenta_contable')
            ->orderBy('d.cuenta_contable')
            ->get();

        // Inicializa el array de clases
        $cuentasPorClase = [
            'ACTIVO' => [],
            'PASIVO' => [],
            'PATRIMONIO' => [],
            'INGRESOS' => [],
            'GASTOS' => []
        ];

        // Clasifica cada cuenta según el primer dígito de su código
        foreach ($balances as $b) {
            $codigo = $b->cuenta;
            $primera = substr($codigo, 0, 1);
            $saldo = $b->saldo;

            switch ($primera) {
                case '1': // ACTIVO
                    $cuentasPorClase['ACTIVO'][] = $b;
                    break;
                case '2': // PASIVO
                    $b->saldo = abs($saldo);
                    $cuentasPorClase['PASIVO'][] = $b;
                    break;
                case '3': // PATRIMONIO
                    $b->saldo = abs($saldo);
                    $cuentasPorClase['PATRIMONIO'][] = $b;
                    break;
                case '4': // INGRESOS
                    $b->saldo = abs($saldo);
                    $cuentasPorClase['INGRESOS'][] = $b;
                    break;
                case '5':
                case '6':
                case '9': // GASTOS
                    $cuentasPorClase['GASTOS'][] = $b;
                    break;
                default:
                    // Ignorar códigos que no correspondan a clases conocidas
                    break;
            }
        }

        return $cuentasPorClase;
    }

    public function porClases(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfYear()->format('Y-m-d'));
        $fechaFin = $request->input('fecha_fin', Carbon::now()->endOfYear()->format('Y-m-d'));

        $cuentasPorClase = $this->obtenerCuentasPorClasesVista($fechaInicio, $fechaFin);

        return view('contabilidad.libros.balance-comprobacion.clases', compact(
            'cuentasPorClase', 'fechaInicio', 'fechaFin'
        ));
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
            $fechaInicio = $request->input('fecha_inicio', '2025-01-01');
            $fechaFin = $request->input('fecha_fin', '2025-12-31');

            // ==============================
            // 1️⃣ Verificar asientos descuadrados
            // ==============================
            $asientosDescuadrados = DB::table('libro_diario as c')
                ->join('libro_diario_detalles as d', 'd.asiento_id', '=', 'c.id')
                ->select('c.id', 'c.numero', 
                    DB::raw('SUM(d.debe) as total_debe'), 
                    DB::raw('SUM(d.haber) as total_haber'),
                    DB::raw('ABS(SUM(d.debe) - SUM(d.haber)) as diferencia'))
                ->whereBetween('c.fecha', [$fechaInicio, $fechaFin])
                ->where('c.estado', 'ACTIVO')
                ->groupBy('c.id', 'c.numero')
                ->havingRaw('ABS(SUM(d.debe) - SUM(d.haber)) > 0.01')
                ->get();

            // ==============================
            // 2️⃣ Totales generales
            // ==============================
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

            $diferencia = $totalDebe - $totalHaber;

            // ==============================
            // 3️⃣ Últimas facturas emitidas
            // ==============================
            $ultimasFacturas = DB::table('Doccab')
                ->select(['Numero', 'Tipo', 'CodClie', 'Fecha', 'FechaV', 'Total', 'Impreso'])
                ->where('Eliminado', 0)
                ->orderByDesc('Fecha')
                ->limit(5)
                ->get()
                ->map(function ($f) {
                    $f->Estado = $f->Impreso ? 'Emitida' : 'Pendiente';
                    return $f;
                });

            // ==============================
            // 4️⃣ Top clientes (placeholder)
            // ==============================
            $topClientesSaldo = DB::table('clientes')
                ->select('Razon', DB::raw('0 as saldo'))
                ->limit(5)
                ->get();

            // ==============================
            // 5️⃣ Retornar vista con todos los datos
            // ==============================
            return view('contabilidad.libros.balance-comprobacion.verificar', compact(
                'asientosDescuadrados',
                'totalDebe',
                'totalHaber',
                'diferencia',
                'topClientesSaldo',
                'ultimasFacturas'
            ));


        } catch (\Exception $e) {
            return back()->with('error', 'Error en verificación: ' . $e->getMessage());
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
            'GASTOS' => ['total_debe' => 0, 'total_haber' => 0],
        ];

        foreach ($balances as $b) {
            $codigo = $b->cuenta;
            if (empty($codigo) || !ctype_digit(substr($codigo, 0, 1))) continue;

            $primera = substr($codigo, 0, 1);

            switch ($primera) {
                case '1': $tipo = 'ACTIVO'; break;
                case '2': $tipo = 'PASIVO'; break;
                case '3': $tipo = 'PATRIMONIO'; break;
                case '4': $tipo = 'INGRESOS'; break;
                case '5':
                case '6':
                case '9': $tipo = 'GASTOS'; break;
                default: $tipo = null;
            }

            if ($tipo) {
                $resumen[$tipo]['total_debe'] += $b->total_debe;
                $resumen[$tipo]['total_haber'] += $b->total_haber;
            }
        }

        // Calcular saldos netos por clase
        foreach ($resumen as $clase => &$valores) {
            $valores['saldo'] = $valores['total_debe'] - $valores['total_haber'];
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