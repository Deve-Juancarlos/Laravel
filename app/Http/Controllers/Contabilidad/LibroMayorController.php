<?php

namespace App\Http\Controllers\Contabilidad;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class LibroMayorController extends Controller
{
    /**
     * Vista principal del Libro Mayor
     */
    public function index(Request $request)
    {
        try {
            $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
            $fechaFin = $request->input('fecha_fin', Carbon::now()->endOfMonth()->format('Y-m-d'));
            $cuenta = $request->input('cuenta');

            // Si las fechas vienen invertidas, las intercambiamos para evitar consultas vacías
            if (Carbon::parse($fechaInicio)->gt(Carbon::parse($fechaFin))) {
                [$fechaInicio, $fechaFin] = [$fechaFin, $fechaInicio];
            }

            // Construyo la query base (reutilizable para totals y para el group/paginate)
            $baseQuery = DB::table('libro_diario_detalles as dld')
                ->join('libro_diario as ld', 'dld.asiento_id', '=', 'ld.id')
                ->leftJoin('plan_cuentas as pc', 'dld.cuenta_contable', '=', 'pc.codigo')
                ->whereBetween('ld.fecha', [$fechaInicio, $fechaFin])
                ->where('ld.estado', 'ACTIVO');

            if ($cuenta) {
                $baseQuery->where('dld.cuenta_contable', 'like', "%$cuenta%");
            }

            // Query agrupada por cuenta (para paginar)
            $groupQuery = (clone $baseQuery)
                ->select([
                    'dld.cuenta_contable as cuenta',
                    'pc.nombre as cuenta_nombre',
                    'pc.tipo as cuenta_tipo',
                    DB::raw('COUNT(dld.id) as movimientos'),
                    DB::raw('SUM(CAST(dld.debe AS DECIMAL(15,2))) as total_debe'),
                    DB::raw('SUM(CAST(dld.haber AS DECIMAL(15,2))) as total_haber')
                ])
                ->groupBy('dld.cuenta_contable', 'pc.nombre', 'pc.tipo')
                ->orderBy('dld.cuenta_contable');

            $cuentas = $groupQuery->paginate(50);

            // Calcular saldo y naturaleza para cada registro de la página
            foreach ($cuentas as $item) {
                $item->saldo = (float) (($item->total_debe ?? 0) - ($item->total_haber ?? 0));
                $item->naturaleza = $this->determinarNaturaleza($item->cuenta);
            }

            // Totales globales: calculados con una consulta agregada que respeta los mismos filtros
            $globalTotals = (clone $baseQuery)
                ->selectRaw('
                    COUNT(DISTINCT dld.cuenta_contable) as total_cuentas,
                    SUM(CAST(dld.debe AS DECIMAL(25,2))) as total_debe,
                    SUM(CAST(dld.haber AS DECIMAL(25,2))) as total_haber
                ')
                ->first();

            $totales = (object)[
                'total_cuentas' => (int)($globalTotals->total_cuentas ?? 0),
                // Los siguientes totales son los globales (no sólo de la página)
                'total_debe' => round((float)($globalTotals->total_debe ?? 0), 2),
                'total_haber' => round((float)($globalTotals->total_haber ?? 0), 2),
                'diferencia' => round(((float)($globalTotals->total_debe ?? 0) - (float)($globalTotals->total_haber ?? 0)), 2)
            ];

            return view('contabilidad.libros.mayor.index', compact(
                'cuentas',
                'fechaInicio',
                'fechaFin',
                'cuenta',
                'totales'
            ));

        } catch (\Exception $e) {
            Log::error('Error en Libro Mayor: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return redirect()->back()->with('error', 'Error al cargar el libro mayor: ' . $e->getMessage());
        }
    }

    /**
     * Detalle de movimientos de una cuenta específica
     */
    public function cuenta(Request $request, $codigoCuenta)
    {
        try {
            $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfYear()->format('Y-m-d'));
            $fechaFin = $request->input('fecha_fin', Carbon::now()->format('Y-m-d'));

            if (Carbon::parse($fechaInicio)->gt(Carbon::parse($fechaFin))) {
                [$fechaInicio, $fechaFin] = [$fechaFin, $fechaInicio];
            }

            // Información de la cuenta
            $infoCuenta = DB::table('plan_cuentas')
                ->where('codigo', $codigoCuenta)
                ->first();

            if (!$infoCuenta) {
                return redirect()->route('contador.libro-mayor.index')
                    ->with('error', 'Cuenta no encontrada');
            }

            // Saldo inicial (antes del período)
            $saldoInicial = DB::table('libro_diario_detalles as dld')
                ->join('libro_diario as ld', 'dld.asiento_id', '=', 'ld.id')
                ->where('dld.cuenta_contable', $codigoCuenta)
                ->where('ld.fecha', '<', $fechaInicio)
                ->where('ld.estado', 'ACTIVO')
                ->selectRaw('
                    SUM(CAST(dld.debe AS DECIMAL(15,2))) as debe_inicial,
                    SUM(CAST(dld.haber AS DECIMAL(15,2))) as haber_inicial
                ')
                ->first();

            $saldoAnterior = (float)(($saldoInicial->debe_inicial ?? 0) - ($saldoInicial->haber_inicial ?? 0));

            // Movimientos del período
            $movimientos = DB::table('libro_diario_detalles as dld')
                ->join('libro_diario as ld', 'dld.asiento_id', '=', 'ld.id')
                ->where('dld.cuenta_contable', $codigoCuenta)
                ->whereBetween('ld.fecha', [$fechaInicio, $fechaFin])
                ->where('ld.estado', 'ACTIVO')
                ->select([
                    'ld.numero',
                    'ld.fecha',
                    'ld.glosa as glosa_general',
                    'dld.concepto',
                    DB::raw('CAST(dld.debe AS DECIMAL(25,2)) as debe'),
                    DB::raw('CAST(dld.haber AS DECIMAL(25,2)) as haber'),
                    'dld.documento_referencia'
                ])
                ->orderBy('ld.fecha')
                ->orderBy('ld.numero')
                ->get();

            // Calcular saldos acumulados
            $saldoAcumulado = $saldoAnterior;
            foreach ($movimientos as $mov) {
                $saldoAcumulado += ((float)($mov->debe ?? 0) - (float)($mov->haber ?? 0));
                $mov->saldo_acumulado = round($saldoAcumulado, 2);
            }

            // Totales del período
            $totalesPeriodo = [
                'debe' => round($movimientos->sum(fn($m) => (float)($m->debe ?? 0)), 2),
                'haber' => round($movimientos->sum(fn($m) => (float)($m->haber ?? 0)), 2),
                'saldo_final' => round($saldoAcumulado, 2)
            ];

            $cuenta = $codigoCuenta; // Pasamos $cuenta a la vista

            return view('contabilidad.libros.mayor.cuenta', compact(
                'cuenta',
                'infoCuenta',
                'saldoAnterior',
                'movimientos',
                'totalesPeriodo',
                'fechaInicio',
                'fechaFin'
            ));

        } catch (\Exception $e) {
            Log::error('Error al cargar cuenta: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return redirect()->back()->with('error', 'Error al cargar la cuenta: ' . $e->getMessage());
        }
    }

    



    /**
     * Exportar a Excel
     */
    public function exportar(Request $request)
    {
        try {
            $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
            $fechaFin = $request->input('fecha_fin', Carbon::now()->endOfMonth()->format('Y-m-d'));
            $tipo = $request->input('tipo', 'resumen'); // resumen o detallado

            if (Carbon::parse($fechaInicio)->gt(Carbon::parse($fechaFin))) {
                [$fechaInicio, $fechaFin] = [$fechaFin, $fechaInicio];
            }

            if ($tipo === 'resumen') {
                return $this->exportarResumen($fechaInicio, $fechaFin);
            } else {
                return $this->exportarDetallado($fechaInicio, $fechaFin);
            }

        } catch (\Exception $e) {
            Log::error('Error al exportar: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return redirect()->back()->with('error', 'Error al exportar: ' . $e->getMessage());
        }
    }

    /**
     * Exportar resumen por cuentas
     */
    private function exportarResumen($fechaInicio, $fechaFin)
    {
        $datos = DB::table('libro_diario_detalles as dld')
            ->join('libro_diario as ld', 'dld.asiento_id', '=', 'ld.id')
            ->leftJoin('plan_cuentas as pc', 'dld.cuenta_contable', '=', 'pc.codigo')
            ->whereBetween('ld.fecha', [$fechaInicio, $fechaFin])
            ->where('ld.estado', 'ACTIVO')
            ->select([
                'dld.cuenta_contable',
                'pc.nombre as cuenta_nombre',
                'pc.tipo as cuenta_tipo',
                DB::raw('COUNT(dld.id) as movimientos'),
                DB::raw('SUM(CAST(dld.debe AS DECIMAL(15,2))) as total_debe'),
                DB::raw('SUM(CAST(dld.haber AS DECIMAL(15,2))) as total_haber')
            ])
            ->groupBy('dld.cuenta_contable', 'pc.nombre', 'pc.tipo')
            ->orderBy('dld.cuenta_contable')
            ->get();

        $filename = 'libro_mayor_resumen_' . $fechaInicio . '_' . $fechaFin . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() use ($datos, $fechaInicio, $fechaFin) {
            $file = fopen('php://output', 'w');
            // BOM para UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Encabezado del reporte
            fputcsv($file, ['LIBRO MAYOR - RESUMEN POR CUENTAS']);
            fputcsv($file, ['DISTRIBUIDORA SIFANO']);
            fputcsv($file, ['Período: ' . Carbon::parse($fechaInicio)->format('d/m/Y') . ' - ' . Carbon::parse($fechaFin)->format('d/m/Y')]);
            fputcsv($file, []);

            // Encabezados de columnas
            fputcsv($file, [
                'Cuenta',
                'Nombre de la Cuenta',
                'Tipo',
                'Movimientos',
                'Debe',
                'Haber',
                'Saldo',
                'Naturaleza'
            ]);

            $totalDebe = 0;
            $totalHaber = 0;

            foreach ($datos as $row) {
                $saldo = (float)(($row->total_debe ?? 0) - ($row->total_haber ?? 0));
                $naturaleza = $this->determinarNaturaleza($row->cuenta_contable);

                fputcsv($file, [
                    $row->cuenta_contable,
                    $row->cuenta_nombre ?? 'Sin nombre',
                    $row->cuenta_tipo ?? '',
                    $row->movimientos,
                    number_format($row->total_debe ?? 0, 2, '.', ''),
                    number_format($row->total_haber ?? 0, 2, '.', ''),
                    number_format($saldo, 2, '.', ''),
                    $naturaleza
                ]);

                $totalDebe += (float)($row->total_debe ?? 0);
                $totalHaber += (float)($row->total_haber ?? 0);
            }

            // Totales
            fputcsv($file, []);
            fputcsv($file, [
                'TOTALES',
                '',
                '',
                $datos->sum('movimientos'),
                number_format($totalDebe, 2, '.', ''),
                number_format($totalHaber, 2, '.', ''),
                number_format($totalDebe - $totalHaber, 2, '.', ''),
                ''
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Exportar detallado (todos los movimientos)
     */
    private function exportarDetallado($fechaInicio, $fechaFin)
    {
        $datos = DB::table('libro_diario_detalles as dld')
            ->join('libro_diario as ld', 'dld.asiento_id', '=', 'ld.id')
            ->leftJoin('plan_cuentas as pc', 'dld.cuenta_contable', '=', 'pc.codigo')
            ->whereBetween('ld.fecha', [$fechaInicio, $fechaFin])
            ->where('ld.estado', 'ACTIVO')
            ->select([
                'ld.numero',
                'ld.fecha',
                'ld.glosa',
                'dld.cuenta_contable',
                'pc.nombre as cuenta_nombre',
                'dld.concepto',
                'dld.debe',
                'dld.haber',
                'dld.documento_referencia'
            ])
            ->orderBy('ld.fecha')
            ->orderBy('ld.numero')
            ->orderBy('dld.cuenta_contable')
            ->get();

        $filename = 'libro_mayor_detallado_' . $fechaInicio . '_' . $fechaFin . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() use ($datos, $fechaInicio, $fechaFin) {
            $file = fopen('php://output', 'w');

            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, ['LIBRO MAYOR - DETALLADO']);
            fputcsv($file, ['DISTRIBUIDORA SIFANO']);
            fputcsv($file, ['Período: ' . Carbon::parse($fechaInicio)->format('d/m/Y') . ' - ' . Carbon::parse($fechaFin)->format('d/m/Y')]);
            fputcsv($file, []);

            fputcsv($file, [
                'Asiento',
                'Fecha',
                'Glosa',
                'Cuenta',
                'Nombre Cuenta',
                'Concepto',
                'Debe',
                'Haber',
                'Documento Ref.'
            ]);

            $totalDebe = 0;
            $totalHaber = 0;

            foreach ($datos as $row) {
                fputcsv($file, [
                    $row->numero,
                    Carbon::parse($row->fecha)->format('d/m/Y'),
                    $row->glosa,
                    $row->cuenta_contable,
                    $row->cuenta_nombre ?? 'Sin nombre',
                    $row->concepto,
                    number_format($row->debe ?? 0, 2, '.', ''),
                    number_format($row->haber ?? 0, 2, '.', ''),
                    $row->documento_referencia ?? ''
                ]);

                $totalDebe += $row->debe ?? 0;
                $totalHaber += $row->haber ?? 0;
            }

            fputcsv($file, []);
            fputcsv($file, [
                'TOTALES',
                '',
                '',
                '',
                '',
                '',
                number_format($totalDebe, 2, '.', ''),
                number_format($totalHaber, 2, '.', ''),
                ''
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Exportar cuenta específica
     */
    public function exportarCuenta(Request $request, $codigoCuenta)
    {
        try {
            $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfYear()->format('Y-m-d'));
            $fechaFin = $request->input('fecha_fin', Carbon::now()->format('Y-m-d'));

            if (Carbon::parse($fechaInicio)->gt(Carbon::parse($fechaFin))) {
                [$fechaInicio, $fechaFin] = [$fechaFin, $fechaInicio];
            }

            $infoCuenta = DB::table('plan_cuentas')
                ->where('codigo', $codigoCuenta)
                ->first();

            // Saldo inicial
            $saldoInicial = DB::table('libro_diario_detalles as dld')
                ->join('libro_diario as ld', 'dld.asiento_id', '=', 'ld.id')
                ->where('dld.cuenta_contable', $codigoCuenta)
                ->where('ld.fecha', '<', $fechaInicio)
                ->where('ld.estado', 'ACTIVO')
                ->selectRaw('
                    SUM(CAST(dld.debe AS DECIMAL(15,2))) as debe_inicial,
                    SUM(CAST(dld.haber AS DECIMAL(15,2))) as haber_inicial
                ')
                ->first();

            $saldoAnterior = (float)(($saldoInicial->debe_inicial ?? 0) - ($saldoInicial->haber_inicial ?? 0));

            // Movimientos
            $movimientos = DB::table('libro_diario_detalles as dld')
                ->join('libro_diario as ld', 'dld.asiento_id', '=', 'ld.id')
                ->where('dld.cuenta_contable', $codigoCuenta)
                ->whereBetween('ld.fecha', [$fechaInicio, $fechaFin])
                ->where('ld.estado', 'ACTIVO')
                ->select([
                    'ld.numero',
                    'ld.fecha',
                    'ld.glosa',
                    'dld.concepto',
                    'dld.debe',
                    'dld.haber',
                    'dld.documento_referencia'
                ])
                ->orderBy('ld.fecha')
                ->orderBy('ld.numero')
                ->get();

            $filename = 'libro_mayor_cuenta_' . $codigoCuenta . '_' . $fechaInicio . '_' . $fechaFin . '.csv';

            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"$filename\"",
            ];

            $callback = function() use ($infoCuenta, $saldoAnterior, $movimientos, $fechaInicio, $fechaFin, $codigoCuenta) {
                $file = fopen('php://output', 'w');
                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

                fputcsv($file, ['LIBRO MAYOR - DETALLE DE CUENTA']);
                fputcsv($file, ['Cuenta: ' . $codigoCuenta . ' - ' . ($infoCuenta->nombre ?? 'Sin nombre')]);
                fputcsv($file, ['Período: ' . Carbon::parse($fechaInicio)->format('d/m/Y') . ' - ' . Carbon::parse($fechaFin)->format('d/m/Y')]);
                fputcsv($file, ['Saldo Anterior: S/ ' . number_format($saldoAnterior, 2)]);
                fputcsv($file, []);

                fputcsv($file, ['Asiento', 'Fecha', 'Glosa', 'Concepto', 'Debe', 'Haber', 'Saldo Acumulado', 'Doc. Ref.']);

                $saldoAcumulado = $saldoAnterior;
                $totalDebe = 0;
                $totalHaber = 0;

                foreach ($movimientos as $mov) {
                    $saldoAcumulado += ($mov->debe ?? 0) - ($mov->haber ?? 0);

                    fputcsv($file, [
                        $mov->numero,
                        Carbon::parse($mov->fecha)->format('d/m/Y'),
                        $mov->glosa,
                        $mov->concepto,
                        number_format($mov->debe ?? 0, 2, '.', ''),
                        number_format($mov->haber ?? 0, 2, '.', ''),
                        number_format($saldoAcumulado, 2, '.', ''),
                        $mov->documento_referencia ?? ''
                    ]);

                    $totalDebe += $mov->debe ?? 0;
                    $totalHaber += $mov->haber ?? 0;
                }

                fputcsv($file, []);
                fputcsv($file, ['TOTALES', '', '', '',
                    number_format($totalDebe, 2, '.', ''),
                    number_format($totalHaber, 2, '.', ''),
                    number_format($saldoAcumulado, 2, '.', ''),
                    ''
                ]);

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);

        } catch (\Exception $e) {
            Log::error('Error al exportar cuenta: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return redirect()->back()->with('error', 'Error al exportar: ' . $e->getMessage());
        }
    }


    /**
 * Comparación entre períodos
 */
    public function comparacionPeriodos(Request $request)
    {
        try {
            // Fechas de los períodos (si no vienen por request, se toma el mes actual y el mes anterior)
            $fechaInicioActual = $request->input('fecha_inicio_actual', Carbon::now()->startOfMonth()->format('Y-m-d'));
            $fechaFinActual = $request->input('fecha_fin_actual', Carbon::now()->endOfMonth()->format('Y-m-d'));

            $fechaInicioAnterior = $request->input('fecha_inicio_anterior', Carbon::parse($fechaInicioActual)->subMonth()->startOfMonth()->format('Y-m-d'));
            $fechaFinAnterior = $request->input('fecha_fin_anterior', Carbon::parse($fechaFinActual)->subMonth()->endOfMonth()->format('Y-m-d'));

            // Intercambiar fechas si vienen invertidas
            if (Carbon::parse($fechaInicioActual)->gt(Carbon::parse($fechaFinActual))) {
                [$fechaInicioActual, $fechaFinActual] = [$fechaFinActual, $fechaInicioActual];
            }
            if (Carbon::parse($fechaInicioAnterior)->gt(Carbon::parse($fechaFinAnterior))) {
                [$fechaInicioAnterior, $fechaFinAnterior] = [$fechaFinAnterior, $fechaInicioAnterior];
            }

            // Obtener totales por cuenta para el período actual
            $actual = DB::table('libro_diario_detalles as dld')
                ->join('libro_diario as ld', 'dld.asiento_id', '=', 'ld.id')
                ->leftJoin('plan_cuentas as pc', 'dld.cuenta_contable', '=', 'pc.codigo')
                ->whereBetween('ld.fecha', [$fechaInicioActual, $fechaFinActual])
                ->where('ld.estado', 'ACTIVO')
                ->select(
                    'dld.cuenta_contable as cuenta',
                    'pc.nombre as nombre_cuenta',
                    DB::raw('SUM(CAST(dld.debe AS DECIMAL(25,2))) as debe_actual'),
                    DB::raw('SUM(CAST(dld.haber AS DECIMAL(25,2))) as haber_actual')
                )
                ->groupBy('dld.cuenta_contable', 'pc.nombre')
                ->get();

            // Obtener totales por cuenta para el período anterior
            $anterior = DB::table('libro_diario_detalles as dld')
                ->join('libro_diario as ld', 'dld.asiento_id', '=', 'ld.id')
                ->leftJoin('plan_cuentas as pc', 'dld.cuenta_contable', '=', 'pc.codigo')
                ->whereBetween('ld.fecha', [$fechaInicioAnterior, $fechaFinAnterior])
                ->where('ld.estado', 'ACTIVO')
                ->select(
                    'dld.cuenta_contable as cuenta',
                    'pc.nombre as nombre_cuenta',
                    DB::raw('SUM(CAST(dld.debe AS DECIMAL(25,2))) as debe_anterior'),
                    DB::raw('SUM(CAST(dld.haber AS DECIMAL(25,2))) as haber_anterior')
                )
                ->groupBy('dld.cuenta_contable', 'pc.nombre')
                ->get();

            // Unir períodos por cuenta
            $comparacion = collect();

            $cuentas = $actual->pluck('cuenta')->merge($anterior->pluck('cuenta'))->unique();

            foreach ($cuentas as $cuenta) {
                $actualRow = $actual->firstWhere('cuenta', $cuenta);
                $anteriorRow = $anterior->firstWhere('cuenta', $cuenta);

                $comparacion->push([
                    'cuenta' => $cuenta,
                    'nombre_cuenta' => $actualRow->nombre_cuenta ?? $anteriorRow->nombre_cuenta ?? 'Sin nombre',
                    'debe_actual' => (float)($actualRow->debe_actual ?? 0),
                    'haber_actual' => (float)($actualRow->haber_actual ?? 0),
                    'debe_anterior' => (float)($anteriorRow->debe_anterior ?? 0),
                    'haber_anterior' => (float)($anteriorRow->haber_anterior ?? 0),
                ]);
            }

            // Ordenar por cuenta
            $comparacion = $comparacion->sortBy('cuenta')->values();

            // Preparar períodos para la vista
            $periodoActual = ['inicio' => $fechaInicioActual, 'fin' => $fechaFinActual];
            $periodoAnterior = ['inicio' => $fechaInicioAnterior, 'fin' => $fechaFinAnterior];

            return view('contabilidad.libros.mayor.comparacion', compact(
                'comparacion',
                'periodoActual',
                'periodoAnterior'
            ));

        } catch (\Exception $e) {
            Log::error('Error en comparación de períodos: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return redirect()->back()->with('error', 'Error al generar comparación de períodos: ' . $e->getMessage());
        }
    }

    /**
     * Balance de Comprobación
     */
    public function balanceComprobacion(Request $request)
    {
        try {
            $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfYear()->format('Y-m-d'));
            $fechaFin = $request->input('fecha_fin', Carbon::now()->format('Y-m-d'));

            if (Carbon::parse($fechaInicio)->gt(Carbon::parse($fechaFin))) {
                [$fechaInicio, $fechaFin] = [$fechaFin, $fechaInicio];
            }

            $balance = DB::table('libro_diario_detalles as dld')
                ->join('libro_diario as ld', 'dld.asiento_id', '=', 'ld.id')
                ->leftJoin('plan_cuentas as pc', 'dld.cuenta_contable', '=', 'pc.codigo')
                ->whereBetween('ld.fecha', [$fechaInicio, $fechaFin])
                ->where('ld.estado', 'ACTIVO')
                ->select([
                    'dld.cuenta_contable',
                    'pc.nombre as cuenta_nombre',
                    'pc.tipo',
                    DB::raw('SUM(CAST(dld.debe AS DECIMAL(15,2))) as debe'),
                    DB::raw('SUM(CAST(dld.haber AS DECIMAL(15,2))) as haber')
                ])
                ->groupBy('dld.cuenta_contable', 'pc.nombre', 'pc.tipo')
                ->orderBy('dld.cuenta_contable')
                ->get();

            foreach ($balance as $item) {
                $item->saldo = (float)(($item->debe ?? 0) - ($item->haber ?? 0));
                $item->deudor = $item->saldo > 0 ? $item->saldo : 0;
                $item->acreedor = $item->saldo < 0 ? abs($item->saldo) : 0;
            }

            $totales = [
                'debe' => round($balance->sum('debe'), 2),
                'haber' => round($balance->sum('haber'), 2),
                'deudor' => round($balance->sum('deudor'), 2),
                'acreedor' => round($balance->sum('acreedor'), 2)
            ];

            return view('contabilidad.libros.mayor.balance-comprobacion', compact(
                'balance',
                'fechaInicio',
                'fechaFin',
                'totales'
            ));

        } catch (\Exception $e) {
            Log::error('Error en Balance de Comprobación: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return redirect()->back()->with('error', 'Error al generar balance: ' . $e->getMessage());
        }
    }

    /**
     * Determinar naturaleza de la cuenta
     */
    private function determinarNaturaleza($codigoCuenta)
    {
        $primerDigito = substr((string)$codigoCuenta, 0, 1);

        switch ($primerDigito) {
            case '1': // Activo
            case '6': // Gastos
                return 'Deudor';
            case '2': // Pasivo
            case '3': // Patrimonio
            case '4': // Ingresos
            case '7': // Ingresos
                return 'Acreedor';
            default:
                return 'Mixta';
        }
    }
}