<?php

namespace App\Services\Contabilidad;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Servicio para toda la lógica de negocio del Libro Mayor.
 * Creado para "adelgazar" el LibroMayorController.
 */
class LibroMayorService
{
    /**
     * Obtener datos para la vista principal del Libro Mayor (Resumen por Cuentas)
     */
    public function getMayorIndexData(array $filters)
    {
        $fechaInicio = $filters['fecha_inicio'] ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $fechaFin = $filters['fecha_fin'] ?? Carbon::now()->endOfMonth()->format('Y-m-d');
        $cuenta = $filters['cuenta'] ?? null;

        // Si las fechas vienen invertidas, las intercambiamos
        if (Carbon::parse($fechaInicio)->gt(Carbon::parse($fechaFin))) {
            [$fechaInicio, $fechaFin] = [$fechaFin, $fechaInicio];
        }

        // 1. Query base (reutilizable)
        $baseQuery = $this->buildBaseMayorQuery($fechaInicio, $fechaFin, $cuenta);

        // 2. Query agrupada para la tabla principal (paginada)
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

        $cuentas = $groupQuery->paginate(50)->withQueryString();

        // 3. Calcular saldo y naturaleza para la página actual
        foreach ($cuentas as $item) {
            $item->saldo = (float) (($item->total_debe ?? 0) - ($item->total_haber ?? 0));
            $item->naturaleza = $this->determinarNaturaleza($item->cuenta);
        }

        // 4. Totales globales (usando la query base sin agrupar/paginar)
        // Usamos ->get() en lugar de ->paginate(null) para obtener todos los resultados
        $allResults = (clone $groupQuery)->get();
        
        $totales = (object)[
            'total_cuentas' => $allResults->count(),
            'total_debe' => round($allResults->sum('total_debe'), 2),
            'total_haber' => round($allResults->sum('total_haber'), 2),
            'diferencia' => round($allResults->sum('total_debe') - $allResults->sum('total_haber'), 2)
        ];

        return [
            'cuentas' => $cuentas,
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
            'cuenta' => $cuenta,
            'totales' => $totales
        ];
    }

    /**
     * Obtener datos para el detalle de una cuenta específica
     */
    public function getMayorCuentaData(array $filters, string $codigoCuenta)
    {
        $fechaInicio = $filters['fecha_inicio'] ?? Carbon::now()->startOfYear()->format('Y-m-d');
        $fechaFin = $filters['fecha_fin'] ?? Carbon::now()->format('Y-m-d');

        if (Carbon::parse($fechaInicio)->gt(Carbon::parse($fechaFin))) {
            [$fechaInicio, $fechaFin] = [$fechaFin, $fechaInicio];
        }

        // 1. Información de la cuenta
        $infoCuenta = DB::table('plan_cuentas')
            ->where('codigo', $codigoCuenta)
            ->first();

        if (!$infoCuenta) {
            return ['error' => 'Cuenta no encontrada'];
        }

        // 2. Saldo inicial (antes del período)
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

        // 3. Movimientos del período
        $movimientos = DB::table('libro_diario_detalles as dld')
            ->join('libro_diario as ld', 'dld.asiento_id', '=', 'ld.id')
            ->where('dld.cuenta_contable', $codigoCuenta)
            ->whereBetween('ld.fecha', [$fechaInicio, $fechaFin])
            ->where('ld.estado', 'ACTIVO')
            ->select([
                'ld.id as asiento_id', // ID para el enlace
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

        // 4. Calcular saldos acumulados
        $saldoAcumulado = $saldoAnterior;
        foreach ($movimientos as $mov) {
            $saldoAcumulado += ((float)($mov->debe ?? 0) - (float)($mov->haber ?? 0));
            $mov->saldo_acumulado = round($saldoAcumulado, 2);
        }

        // 5. Totales del período
        $totalesPeriodo = [
            'debe' => round($movimientos->sum(fn($m) => (float)($m->debe ?? 0)), 2),
            'haber' => round($movimientos->sum(fn($m) => (float)($m->haber ?? 0)), 2),
            'saldo_final' => round($saldoAcumulado, 2)
        ];

        return [
            'cuenta' => $codigoCuenta,
            'infoCuenta' => $infoCuenta,
            'saldoAnterior' => $saldoAnterior,
            'movimientos' => $movimientos,
            'totalesPeriodo' => $totalesPeriodo,
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin
        ];
    }

    /**
     * Lógica de exportación (CSV/Excel)
     */
    public function exportar(array $filters)
    {
        $fechaInicio = $filters['fecha_inicio'] ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $fechaFin = $filters['fecha_fin'] ?? Carbon::now()->endOfMonth()->format('Y-m-d');
        $tipo = $filters['tipo'] ?? 'resumen';
        $cuenta = $filters['cuenta'] ?? null;

        if (Carbon::parse($fechaInicio)->gt(Carbon::parse($fechaFin))) {
            [$fechaInicio, $fechaFin] = [$fechaFin, $fechaInicio];
        }

        if ($tipo === 'resumen') {
            return $this->exportarResumen($fechaInicio, $fechaFin, $cuenta);
        } else {
            return $this->exportarDetallado($fechaInicio, $fechaFin, $cuenta);
        }
    }

    /**
     * Lógica de exportación para una cuenta específica
     */
    public function exportarCuenta(array $filters, string $codigoCuenta)
    {
        $data = $this->getMayorCuentaData($filters, $codigoCuenta);

        if (isset($data['error'])) {
            throw new \Exception($data['error']);
        }

        $filename = 'libro_mayor_cuenta_' . $codigoCuenta . '_' . $data['fechaInicio'] . '_' . $data['fechaFin'] . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM para UTF-8

            fputcsv($file, ['LIBRO MAYOR - DETALLE DE CUENTA']);
            fputcsv($file, ['DISTRIBUIDORA SEIMCORP']); // Nombre Corregido
            fputcsv($file, ['Cuenta: ' . $data['cuenta'] . ' - ' . ($data['infoCuenta']->nombre ?? 'Sin nombre')]);
            fputcsv($file, ['Período: ' . Carbon::parse($data['fechaInicio'])->format('d/m/Y') . ' - ' . Carbon::parse($data['fechaFin'])->format('d/m/Y')]);
            fputcsv($file, ['Saldo Anterior (al ' . Carbon::parse($data['fechaInicio'])->format('d/m/Y') . '): S/ ' . number_format($data['saldoAnterior'], 2)]);
            fputcsv($file, []);

            fputcsv($file, ['Asiento', 'Fecha', 'Glosa', 'Concepto', 'Debe', 'Haber', 'Saldo Acumulado', 'Doc. Ref.']);

            $totalDebe = 0;
            $totalHaber = 0;

            foreach ($data['movimientos'] as $mov) {
                fputcsv($file, [
                    $mov->numero,
                    Carbon::parse($mov->fecha)->format('d/m/Y'),
                    $mov->glosa_general,
                    $mov->concepto,
                    number_format($mov->debe ?? 0, 2, '.', ''),
                    number_format($mov->haber ?? 0, 2, '.', ''),
                    number_format($mov->saldo_acumulado, 2, '.', ''),
                    $mov->documento_referencia ?? ''
                ]);
                $totalDebe += $mov->debe ?? 0;
                $totalHaber += $mov->haber ?? 0;
            }

            fputcsv($file, []);
            fputcsv($file, ['TOTAL MOVIMIENTOS', '', '', '',
                number_format($totalDebe, 2, '.', ''),
                number_format($totalHaber, 2, '.', ''),
                '', ''
            ]);
            fputcsv($file, ['SALDO FINAL (al ' . Carbon::parse($data['fechaFin'])->format('d/m/Y') . ')', '', '', '', '', '',
                number_format($data['totalesPeriodo']['saldo_final'], 2, '.', ''),
                ''
            ]);

            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }

    /**
     * Obtener datos para la vista de Comparación de Períodos
     */
    public function getComparacionPeriodosData(array $filters)
    {
        // Fechas
        $fechaInicioActual = $filters['fecha_inicio_actual'] ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $fechaFinActual = $filters['fecha_fin_actual'] ?? Carbon::now()->endOfMonth()->format('Y-m-d');
        $fechaInicioAnterior = $filters['fecha_inicio_anterior'] ?? Carbon::parse($fechaInicioActual)->subMonth()->startOfMonth()->format('Y-m-d');
        $fechaFinAnterior = $filters['fecha_fin_anterior'] ?? Carbon::parse($fechaFinActual)->subMonth()->endOfMonth()->format('Y-m-d');

        // Normalizar fechas
        if (Carbon::parse($fechaInicioActual)->gt(Carbon::parse($fechaFinActual))) {
            [$fechaInicioActual, $fechaFinActual] = [$fechaFinActual, $fechaInicioActual];
        }
        if (Carbon::parse($fechaInicioAnterior)->gt(Carbon::parse($fechaFinAnterior))) {
            [$fechaInicioAnterior, $fechaFinAnterior] = [$fechaFinAnterior, $fechaInicioAnterior];
        }

        // Query para Período Actual
        $actual = DB::table('libro_diario_detalles as dld')
            ->join('libro_diario as ld', 'dld.asiento_id', '=', 'ld.id')
            ->leftJoin('plan_cuentas as pc', 'dld.cuenta_contable', '=', 'pc.codigo')
            ->whereBetween('ld.fecha', [$fechaInicioActual, $fechaFinActual])
            ->where('ld.estado', 'ACTIVO')
            ->select(
                'dld.cuenta_contable as cuenta',
                'pc.nombre as nombre_cuenta',
                DB::raw('SUM(CAST(dld.debe AS DECIMAL(25,2))) as debe_actual'),
                DB::raw('SUM(CAST(dld.haber AS DECIMAL(25,2))) as haber_actual') // <-- CORRECCIÓN AQUÍ
            )
            ->groupBy('dld.cuenta_contable', 'pc.nombre')
            ->get();

        // Query para Período Anterior
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

        return [
            'comparacion' => $comparacion->sortBy('cuenta')->values(),
            'periodoActual' => ['inicio' => $fechaInicioActual, 'fin' => $fechaFinActual],
            'periodoAnterior' => ['inicio' => $fechaInicioAnterior, 'fin' => $fechaFinAnterior]
        ];
    }

    /**
     * Obtener datos para la vista de Movimientos (detalle de todo el libro mayor)
     */
    public function getMovimientosData(array $filters)
    {
        $fechaInicio = $filters['fecha_inicio'] ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $fechaFin = $filters['fecha_fin'] ?? Carbon::now()->endOfMonth()->format('Y-m-d');
        $cuenta = $filters['cuenta'] ?? null;
        $mes = $filters['mes'] ?? null;

        if (Carbon::parse($fechaInicio)->gt(Carbon::parse($fechaFin))) {
            [$fechaInicio, $fechaFin] = [$fechaFin, $fechaInicio];
        }

        // Query base
        $query = DB::table('libro_diario_detalles as dld')
            ->join('libro_diario as ld', 'dld.asiento_id', '=', 'ld.id')
            ->leftJoin('plan_cuentas as pc', 'dld.cuenta_contable', '=', 'pc.codigo')
            ->select(
                'ld.id as asiento_id', // ID para el enlace
                'ld.numero',
                'ld.fecha',
                'dld.cuenta_contable',
                'pc.nombre as nombre_cuenta',
                'dld.concepto',
                DB::raw('CAST(dld.debe AS DECIMAL(25,2)) as debe'),
                DB::raw('CAST(dld.haber AS DECIMAL(25,2)) as haber')
            )
            ->where('ld.estado', 'ACTIVO')
            ->whereBetween('ld.fecha', [$fechaInicio, $fechaFin])
            ->orderBy('ld.fecha', 'asc')
            ->orderBy('ld.numero', 'asc')
            ->orderBy('dld.cuenta_contable', 'asc');

        if ($cuenta) {
            $query->where('dld.cuenta_contable', 'like', "%{$cuenta}%");
        }
        if ($mes) {
            $query->whereMonth('ld.fecha', $mes);
        }

        // Paginación
        $movimientos = $query->paginate(50)->withQueryString();

        // Totales (calculados sobre todos los resultados, no solo la página)
        $allResults = (clone $query)->get(); // Clonar antes de paginar
        $totales = [
            'debe' => round($allResults->sum('debe'), 2),
            'haber' => round($allResults->sum('haber'), 2),
            'count' => $allResults->count()
        ];
        
        return [
            'movimientos' => $movimientos,
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
            'cuenta' => $cuenta,
            'mes' => $mes,
            'totales' => $totales
        ];
    }


    // ==================== MÉTODOS PRIVADOS DE EXPORTACIÓN ====================

    private function exportarResumen($fechaInicio, $fechaFin, $cuenta)
    {
        $query = $this->buildBaseMayorQuery($fechaInicio, $fechaFin, $cuenta);
        
        $datos = $query->select([
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
        $headers = $this->getCsvHeaders($filename);

        $callback = function() use ($datos, $fechaInicio, $fechaFin) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8

            fputcsv($file, ['LIBRO MAYOR - RESUMEN POR CUENTAS']);
            fputcsv($file, ['DISTRIBUIDORA SEIMCORP']); // Nombre Corregido
            fputcsv($file, ['Período: ' . Carbon::parse($fechaInicio)->format('d/m/Y') . ' - ' . Carbon::parse($fechaFin)->format('d/m/Y')]);
            fputcsv($file, []);
            fputcsv($file, ['Cuenta', 'Nombre de la Cuenta', 'Tipo', 'Movimientos', 'Debe', 'Haber', 'Saldo', 'Naturaleza']);

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

            fputcsv($file, []);
            fputcsv($file, ['TOTALES', '', '', $datos->sum('movimientos'),
                number_format($totalDebe, 2, '.', ''),
                number_format($totalHaber, 2, '.', ''),
                number_format($totalDebe - $totalHaber, 2, '.', ''),
                ''
            ]);

            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }

    private function exportarDetallado($fechaInicio, $fechaFin, $cuenta)
    {
        $query = $this->buildBaseMayorQuery($fechaInicio, $fechaFin, $cuenta);

        $datos = $query->select([
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
        $headers = $this->getCsvHeaders($filename);

        $callback = function() use ($datos, $fechaInicio, $fechaFin) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8

            fputcsv($file, ['LIBRO MAYOR - DETALLADO']);
            fputcsv($file, ['DISTRIBUIDORA SEIMCORP']); // Nombre Corregido
            fputcsv($file, ['Período: ' . Carbon::parse($fechaInicio)->format('d/m/Y') . ' - ' . Carbon::parse($fechaFin)->format('d/m/Y')]);
            fputcsv($file, []);
            fputcsv($file, ['Asiento', 'Fecha', 'Glosa', 'Cuenta', 'Nombre Cuenta', 'Concepto', 'Debe', 'Haber', 'Doc. Ref.']);

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
            fputcsv($file, ['TOTALES', '', '', '', '', '',
                number_format($totalDebe, 2, '.', ''),
                number_format($totalHaber, 2, '.', ''),
                ''
            ]);
            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }


    // ==================== MÉTODOS HELPERS ====================

    /**
     * Construye la query base para el Libro Mayor
     */
    private function buildBaseMayorQuery($fechaInicio, $fechaFin, $cuenta = null)
    {
        $query = DB::table('libro_diario_detalles as dld')
            ->join('libro_diario as ld', 'dld.asiento_id', '=', 'ld.id')
            ->leftJoin('plan_cuentas as pc', 'dld.cuenta_contable', '=', 'pc.codigo')
            ->whereBetween('ld.fecha', [$fechaInicio, $fechaFin])
            ->where('ld.estado', 'ACTIVO');

        if ($cuenta) {
            // Buscar tanto por código como por nombre
            $query->where(function($q) use ($cuenta) {
                $q->where('dld.cuenta_contable', 'like', "%$cuenta%")
                  ->orWhere('pc.nombre', 'like', "%$cuenta%");
            });
        }

        return $query;
    }

    /**
     * Determinar naturaleza de la cuenta (Deudor/Acreedor)
     */
    private function determinarNaturaleza($codigoCuenta)
    {
        $primerDigito = substr((string)$codigoCuenta, 0, 1);

        switch ($primerDigito) {
            case '1': // Activo
            case '5': // Gastos (Elemento 5 PCGE)
            case '6': // Gastos (Elemento 6 PCGE)
            case '9': // Cuentas analíticas de costo/gasto
                return 'Deudor';
            case '2': // Pasivo
            case '3': // Patrimonio
            case '4': // Ingresos (Elemento 4 PCGE)
            case '7': // Ingresos (Elemento 7 PCGE)
            case '8': // Saldos intermediarios
                return 'Acreedor';
            default:
                return 'Mixta';
        }
    }

    /**
     * Headers estándar para descarga CSV
     */
    private function getCsvHeaders($filename)
    {
        return [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];
    }
}

