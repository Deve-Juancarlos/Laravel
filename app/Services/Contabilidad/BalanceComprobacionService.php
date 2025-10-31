<?php

namespace App\Services\Contabilidad;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Collection;

class BalanceComprobacionService
{
    /**
     * Obtiene los datos para el Balance de Comprobación principal.
     */
    public function getBalanceData(array $filters): array
    {
        $fechaInicio = $filters['fecha_inicio'] ?? Carbon::now()->startOfYear()->format('Y-m-d');
        $fechaFin = $filters['fecha_fin'] ?? Carbon::now()->endOfYear()->format('Y-m-d');

        // Obtener saldos por cuenta contable en el período
        $balances = DB::table('libro_diario_detalles as d')
            ->join('libro_diario as c', 'd.asiento_id', '=', 'c.id')
            ->leftJoin('plan_cuentas as pc', 'd.cuenta_contable', '=', 'pc.codigo')
            ->whereBetween('c.fecha', [$fechaInicio, $fechaFin])
            ->where('c.estado', 'ACTIVO')
            ->select([
                'd.cuenta_contable as cuenta',
                'pc.nombre as nombre_cuenta', // Añadido para la vista
                DB::raw('SUM(d.debe) as saldo_deudor'),
                DB::raw('SUM(d.haber) as saldo_acredor'),
                DB::raw('COUNT(*) as movimientos')
            ])
            ->groupBy('d.cuenta_contable', 'pc.nombre') // Añadido pc.nombre
            ->orderBy('d.cuenta_contable')
            ->get();

        $cuentasDeudoras = collect();
        $cuentasAcreedoras = collect();
        $totalDeudor = 0;
        $totalAcreedor = 0;

        foreach ($balances as $balance) {
            $saldoNeto = $balance->saldo_deudor - $balance->saldo_acredor;
            $cuentaData = [
                'cuenta' => $balance->cuenta,
                'nombre_cuenta' => $balance->nombre_cuenta,
                'saldo_deudor_raw' => $balance->saldo_deudor,
                'saldo_acredor_raw' => $balance->saldo_acredor,
                'movimientos' => $balance->movimientos
            ];

            if ($saldoNeto > 0) {
                $cuentaData['saldo'] = $saldoNeto;
                $cuentasDeudoras->push($cuentaData);
                $totalDeudor += $saldoNeto;
            } else {
                $cuentaData['saldo'] = abs($saldoNeto);
                $cuentasAcreedoras->push($cuentaData);
                $totalAcreedor += abs($saldoNeto);
            }
        }

        $diferencia = abs($totalDeudor - $totalAcreedor);
        $cuadra = $diferencia < 0.01;
        
        $resumenClases = $this->obtenerResumenPorClases($fechaInicio, $fechaFin);
        $estadisticas = $this->obtenerEstadisticas($fechaInicio, $fechaFin);

        return compact(
            'cuentasDeudoras', 'cuentasAcreedoras', 'totalDeudor', 'totalAcreedor',
            'diferencia', 'cuadra', 'fechaInicio', 'fechaFin', 'resumenClases', 'estadisticas'
        );
    }

    /**
     * ¡ACTUALIZADO! Maneja la exportación de los diferentes reportes del balance.
     */
    public function exportar(array $filters): StreamedResponse
    {
        $fechaInicio = $filters['fecha_inicio'] ?? Carbon::now()->startOfYear()->format('Y-m-d');
        $fechaFin = $filters['fecha_fin'] ?? Carbon::now()->endOfYear()->format('Y-m-d');
        $formato = $filters['formato'] ?? 'balance'; // 'balance' o 'clases'

        if ($formato === 'clases') {
            return $this->exportarPorClases($fechaInicio, $fechaFin);
        }
        
        // Default: exportar balance de comprobación
        return $this->exportarBalance($fechaInicio, $fechaFin);
    }

    // ... (getDetalleCuentaData, getPorClasesData, getComparacionData, getVerificacionData se mantienen igual) ...

    // ===================================================================
    // MÉTODOS PRIVADOS (HELPERS DE EXPORTACIÓN Y CÁLCULO)
    // ===================================================================

    /**
     * Exporta el Balance de Comprobación principal.
     */
    private function exportarBalance($fechaInicio, $fechaFin): StreamedResponse
    {
        $data = $this->getBalanceData(['fecha_inicio' => $fechaInicio, 'fecha_fin' => $fechaFin]);

        $filename = "balance_comprobacion_{$fechaInicio}_a_{$fechaFin}.csv";
        $headers = $this->getCsvHeaders($filename);

        $callback = function() use ($data, $fechaInicio, $fechaFin) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM para UTF-8

            fputcsv($file, ['BALANCE DE COMPROBACIÓN']);
            fputcsv($file, ['DISTRIBUIDORA SEIMCORP']);
            fputcsv($file, ['Período:', $fechaInicio, 'al', $fechaFin]);
            fputcsv($file, []); 

            fputcsv($file, [
                'Cuenta', 'Nombre Cuenta', 'Mov. Debe', 'Mov. Haber', 'Saldo Deudor', 'Saldo Acreedor'
            ]);

            foreach ($data['cuentasDeudoras'] as $cuenta) {
                fputcsv($file, [
                    $cuenta['cuenta'],
                    $cuenta['nombre_cuenta'] ?? 'N/A',
                    number_format($cuenta['saldo_deudor_raw'], 2, '.', ''),
                    number_format($cuenta['saldo_acredor_raw'], 2, '.', ''),
                    number_format($cuenta['saldo'], 2, '.', ''),
                    '0.00'
                ]);
            }

            foreach ($data['cuentasAcreedoras'] as $cuenta) {
                fputcsv($file, [
                    $cuenta['cuenta'],
                    $cuenta['nombre_cuenta'] ?? 'N/A',
                    number_format($cuenta['saldo_deudor_raw'], 2, '.', ''),
                    number_format($cuenta['saldo_acredor_raw'], 2, '.', ''),
                    '0.00',
                    number_format($cuenta['saldo'], 2, '.', '')
                ]);
            }

            fputcsv($file, []);
            fputcsv($file, [
                'TOTALES',
                '',
                number_format($data['cuentasDeudoras']->sum('saldo_deudor_raw') + $data['cuentasAcreedoras']->sum('saldo_deudor_raw'), 2, '.', ''),
                number_format($data['cuentasDeudoras']->sum('saldo_acredor_raw') + $data['cuentasAcreedoras']->sum('saldo_acredor_raw'), 2, '.', ''),
                number_format($data['totalDeudor'], 2, '.', ''),
                number_format($data['totalAcreedor'], 2, '.', '')
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * ¡NUEVO! Exporta el Resumen por Clases.
     */
    private function exportarPorClases($fechaInicio, $fechaFin): StreamedResponse
    {
        $cuentasPorClase = $this->obtenerCuentasPorClasesVista($fechaInicio, $fechaFin);

        $filename = "balance_por_clases_{$fechaInicio}_a_{$fechaFin}.csv";
        $headers = $this->getCsvHeaders($filename);

        $callback = function() use ($cuentasPorClase, $fechaInicio, $fechaFin) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM para UTF-8

            fputcsv($file, ['BALANCE POR CLASES DE CUENTAS']);
            fputcsv($file, ['DISTRIBUIDORA SEIMCORP']);
            fputcsv($file, ['Período:', $fechaInicio, 'al', $fechaFin]);
            fputcsv($file, []);

            $clases = ['ACTIVO', 'PASIVO', 'PATRIMONIO', 'INGRESOS', 'GASTOS'];

            foreach ($clases as $nombre) {
                if (isset($cuentasPorClase[$nombre]) && count($cuentasPorClase[$nombre]) > 0) {
                    $total = 0;
                    
                    fputcsv($file, [$nombre]); // Título de la Clase
                    fputcsv($file, ['Cuenta', 'Saldo (S/)']); // Cabecera de la tabla
                    
                    foreach ($cuentasPorClase[$nombre] as $cuenta) {
                        fputcsv($file, [
                            $cuenta->cuenta,
                            number_format($cuenta->saldo, 2, '.', '')
                        ]);
                        $total += $cuenta->saldo;
                    }
                    
                    // Total de la clase
                    fputcsv($file, [
                        'TOTAL ' . $nombre,
                        number_format($total, 2, '.', '')
                    ]);
                    fputcsv($file, []); // Línea vacía
                }
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
    
    /**
     * Retorna los headers estándar para una descarga CSV.
     */
    private function getCsvHeaders(string $filename): array
    {
        return [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];
    }
    
    // ... (El resto de métodos privados: getDetalleCuentaData, getPorClasesData, etc... se mantienen igual que en el archivo anterior) ...
// ... (Copiar el resto de métodos privados del servicio anterior: getDetalleCuentaData, getPorClasesData, getComparacionData, getVerificacionData, obtenerCuentasPorClasesVista, obtenerResumenPorClases, obtenerEstadisticas, calcularBalance) ...
// ... (Inicio de métodos privados copiados) ...

    public function getDetalleCuentaData(array $filters): array
    {
        $fechaInicio = $filters['fecha_inicio'] ?? Carbon::now()->startOfYear()->format('Y-m-d');
        $fechaFin = $filters['fecha_fin'] ?? Carbon::now()->endOfYear()->format('Y-m-d');

        $balances = DB::table('libro_diario_detalles as d')
            ->join('libro_diario as c', 'd.asiento_id', '=', 'c.id')
            ->whereBetween('c.fecha', [$fechaInicio, $fechaFin])
            ->where('c.estado', 'ACTIVO')
            ->select('d.cuenta_contable')
            ->groupBy('d.cuenta_contable')
            ->pluck('cuenta_contable');

        $movimientos = DB::table('libro_diario_detalles as d')
            ->join('libro_diario as c', 'd.asiento_id', '=', 'c.id')
            ->leftJoin('plan_cuentas as pc', 'd.cuenta_contable', '=', 'pc.codigo') // Unir con plan_cuentas
            ->whereIn('d.cuenta_contable', $balances)
            ->whereBetween('c.fecha', [$fechaInicio, $fechaFin])
            ->where('c.estado', 'ACTIVO')
            ->select([
                'd.cuenta_contable as cuenta',
                'pc.nombre as nombre_cuenta', // Obtener nombre de la cuenta
                'c.id as asiento_id', // ID para el enlace
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

        $saldos = [];
        foreach ($movimientos as $mov) {
            if (!isset($saldos[$mov->cuenta])) $saldos[$mov->cuenta] = 0;
            $saldos[$mov->cuenta] += $mov->debito - $mov->credito;
            $mov->saldo_acumulado = $saldos[$mov->cuenta];
        }

        foreach ($balances as $cuenta) {
            if (!isset($saldos[$cuenta])) {
                $cuentaInfo = DB::table('plan_cuentas')->where('codigo', $cuenta)->first(); // Info de la cuenta
                $mov = new \stdClass();
                $mov->cuenta = $cuenta;
                $mov->nombre_cuenta = $cuentaInfo->nombre ?? 'N/A';
                $mov->asiento_id = 0; // ID Cero para sin movimiento
                $mov->numero = '-';
                $mov->fecha = '-';
                $mov->concepto = 'SIN MOVIMIENTO';
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

        return compact(
            'movimientos', 'totales', 'fechaInicio', 'fechaFin'
        );
    }

    public function getPorClasesData(array $filters): array
    {
        $fechaInicio = $filters['fecha_inicio'] ?? Carbon::now()->startOfYear()->format('Y-m-d');
        $fechaFin = $filters['fecha_fin'] ?? Carbon::now()->endOfYear()->format('Y-m-d');

        $cuentasPorClase = $this->obtenerCuentasPorClasesVista($fechaInicio, $fechaFin);

        return compact(
            'cuentasPorClase', 'fechaInicio', 'fechaFin'
        );
    }

    public function getComparacionData(array $filters): array
    {
        $fechaActual = Carbon::now();
        $periodoActual = [
            'inicio' => $filters['fecha_inicio_actual'] ?? $fechaActual->startOfYear()->format('Y-m-d'), // Corregido
            'fin' => $filters['fecha_fin_actual'] ?? $fechaActual->endOfYear()->format('Y-m-d') // Corregido
        ];

        $periodoAnterior = [
            'inicio' => $filters['fecha_inicio_anterior'] ?? Carbon::parse($periodoActual['inicio'])->subYear()->format('Y-m-d'), // Corregido
            'fin' => $filters['fecha_fin_anterior'] ?? Carbon::parse($periodoActual['fin'])->subYear()->format('Y-m-d') // Corregido
        ];

        $balanceActual = $this->calcularBalance($periodoActual['inicio'], $periodoActual['fin']);
        $balanceAnterior = $this->calcularBalance($periodoAnterior['inicio'], $periodoAnterior['fin']);

        return compact(
            'balanceActual', 'balanceAnterior', 'periodoActual', 'periodoAnterior'
        );
    }

    public function getVerificacionData(array $filters): array
    {
        $fechaInicio = $filters['fecha_inicio'] ?? '2025-01-01';
        $fechaFin = $filters['fecha_fin'] ?? '2025-12-31';

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

        $topClientesSaldo = DB::table('CtaCliente as cc')
            ->join('Clientes as c', 'cc.CodClie', '=', 'c.Codclie')
            ->select('c.Razon', DB::raw('SUM(cc.Saldo) as saldo'))
            ->where('cc.Saldo', '>', 0)
            ->groupBy('c.Razon')
            ->orderByDesc('saldo')
            ->limit(5)
            ->get();

        return compact(
            'asientosDescuadrados',
            'totalDebe',
            'totalHaber',
            'diferencia',
            'topClientesSaldo',
            'ultimasFacturas',
            'fechaInicio', // Pasar fechas a la vista
            'fechaFin'
        );
    }

    private function obtenerCuentasPorClasesVista(string $fechaInicio, string $fechaFin): array
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
            ->orderBy('d.cuenta_contable')
            ->get();

        $cuentasPorClase = [
            'ACTIVO' => [], 'PASIVO' => [], 'PATRIMONIO' => [], 'INGRESOS' => [], 'GASTOS' => []
        ];

        foreach ($balances as $b) {
            $codigo = $b->cuenta;
            if (empty($codigo) || !ctype_digit(substr($codigo, 0, 1))) continue;
            $primera = substr($codigo, 0, 1);
            $saldo = $b->saldo;

            switch ($primera) {
                case '1': $cuentasPorClase['ACTIVO'][] = $b; break;
                case '2': $b->saldo = abs($saldo); $cuentasPorClase['PASIVO'][] = $b; break;
                case '3': $b->saldo = abs($saldo); $cuentasPorClase['PATRIMONIO'][] = $b; break;
                case '4': $b->saldo = abs($saldo); $cuentasPorClase['INGRESOS'][] = $b; break;
                case '5':
                case '6':
                case '9': $cuentasPorClase['GASTOS'][] = $b; break;
            }
        }
        return $cuentasPorClase;
    }

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
            $tipo = null;
            switch ($primera) {
                case '1': $tipo = 'ACTIVO'; break;
                case '2': $tipo = 'PASIVO'; break;
                case '3': $tipo = 'PATRIMONIO'; break;
                case '4': $tipo = 'INGRESOS'; break;
                case '5':
                case '6':
                case '9': $tipo = 'GASTOS'; break;
            }
            if ($tipo && isset($resumen[$tipo])) {
                $resumen[$tipo]['total_debe'] += $b->total_debe;
                $resumen[$tipo]['total_haber'] += $b->total_haber;
            }
        }

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
                ->count('d.cuenta_contable'),
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

