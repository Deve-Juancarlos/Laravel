<?php

namespace App\Services\Contabilidad; // 👈 Namespace ajustado a la carpeta Contabilidad

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema; // Importado para verificar la tabla
use Carbon\Carbon;
use Log;
class BancoService
{
    /**
     * Obtiene todos los datos para la vista principal (index).
     */
    public function getDashboardData($fechaInicio, $fechaFin, $cuenta)
    {
        $query = DB::table('v_bancos_con_descripciones')
            ->whereBetween('Fecha', [$fechaInicio, $fechaFin]);

        if ($cuenta) {
            $query->where('Cuenta', $cuenta);
        }
        
        $movimientosBancarios = $query->clone()
            ->orderBy('Fecha', 'desc')
            ->orderBy('Numero', 'desc')
            ->paginate(50);

        // Resumen Consolidado
        $resumenCuentas = $query->clone()
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
            
        $totalesPeriodo = [
            'total_ingresos' => $resumenCuentas->sum('total_ingresos'),
            'total_egresos' => $resumenCuentas->sum('total_egresos'),
            'total_movimientos' => $resumenCuentas->sum('total_movimientos')
        ];

        $saldosActuales = DB::table('v_saldos_bancarios_actuales')->get();

        return [
            'movimientosBancarios' => $movimientosBancarios,
            'resumenCuentas' => $resumenCuentas,
            'saldosActuales' => $saldosActuales,
            'totalesPeriodo' => (object) $totalesPeriodo,
        ];
    }

    /**
     * Obtiene el detalle de movimientos y saldos para una cuenta específica.
     */
    public function getAccountDetail($cuenta, $fechaInicio, $fechaFin)
    {
        $infoCuenta = DB::table('v_saldos_bancarios_actuales')
            ->where('Cuenta', $cuenta)
            ->first();

        if (!$infoCuenta) {
            return null;
        }

        // Saldo anterior al período
        $saldoAnterior = DB::table('CtaBanco')
            ->where('Cuenta', $cuenta)
            ->where('Fecha', '<', $fechaInicio)
            ->selectRaw('SUM(CASE WHEN Tipo = 1 THEN Monto ELSE -Monto END) as saldo')
            ->value('saldo') ?? 0;
            
        // Movimientos de la cuenta (Query Base)
        $queryBase = DB::table('v_bancos_con_descripciones')
            ->where('Cuenta', $cuenta)
            ->whereBetween('Fecha', [$fechaInicio, $fechaFin]);
            
        // CORRECCIÓN: Calcular totales ANTES de paginar
        $totales = $queryBase->clone()
            ->selectRaw('SUM(ingreso) as total_ingresos, SUM(egreso) as total_egresos')
            ->first();
            
        $movimientos = $queryBase->orderBy('Fecha', 'desc')
            ->orderBy('Numero', 'desc')
            ->paginate(100);

        // Resumen mensual
        $resumenMensual = DB::table('v_resumen_mensual_bancos') // Tu vista SQL v_resumen_mensual_bancos
            ->where('Cuenta', $cuenta)
            ->whereRaw("DATEFROMPARTS(anio, mes, 1) BETWEEN ? AND ?", [$fechaInicio, $fechaFin])
            ->orderBy('anio', 'desc')
            ->orderBy('mes', 'desc')
            ->get();
            
        $totalesPeriodo = [
            'ingresos' => $totales->total_ingresos ?? 0,
            'egresos' => $totales->total_egresos ?? 0,
            'saldo_final' => $saldoAnterior + ($totales->total_ingresos ?? 0) - ($totales->total_egresos ?? 0)
        ];

        return [
            'infoCuenta' => $infoCuenta,
            'movimientos' => $movimientos,
            'saldoAnterior' => $saldoAnterior,
            'resumenMensual' => $resumenMensual,
            'totalesPeriodo' => (object) $totalesPeriodo,
        ];
    }

    /**
     * Obtiene todos los movimientos bancarios para una fecha específica.
     */
    public function getDailyMovements($fecha)
    {
        $movimientosDiarios = DB::table('v_bancos_con_descripciones')
            ->whereDate('Fecha', $fecha)
            ->orderBy('Cuenta')
            ->orderBy('Numero')
            ->get();

        // CORRECCIÓN (Error 1): La query de tu log es mejor. La usamos.
        $resumenPorBanco = DB::table('v_bancos_con_descripciones')
            ->whereDate('Fecha', $fecha)
            ->select('Banco', 'Moneda',
                DB::raw('SUM(ingreso) as total_ingresos'),
                DB::raw('SUM(egreso) as total_egresos'),
                DB::raw('COUNT(*) as total_movimientos')
            )
            ->groupBy('Banco', 'Moneda')
            ->get();

        $totalesDiarios = [
            'fecha' => $fecha,
            'total_ingresos' => $movimientosDiarios->sum('ingreso'),
            'total_egresos' => $movimientosDiarios->sum('egreso'),
            'total_movimientos' => $movimientosDiarios->count()
        ];

        return compact('movimientosDiarios', 'resumenPorBanco', 'totalesDiarios');
    }

    /**
     * Calcula la conciliación bancaria para una cuenta a una fecha de corte.
     */
    public function getReconciliationData($cuenta, $fecha)
    {
        $infoCuenta = DB::table('v_saldos_bancarios_actuales')
            ->where('Cuenta', $cuenta)
            ->first();

        if (!$infoCuenta) {
            return null;
        }

        // Saldo según libros a la fecha de corte
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

        // CORRECCIÓN (Error 3): Verificar si la tabla existe antes de consultarla
        $ultimaConciliacion = null;
        if (Schema::hasTable('BancosConciliacion')) {
            $ultimaConciliacion = DB::table('BancosConciliacion')
                ->where('cuenta', $cuenta)
                ->orderBy('fecha_conciliacion', 'desc')
                ->first();
        }

        $diferencias = [
            'saldo_libros' => $saldoLibros,
            'cheques_pendientes' => $chequesPendientes->sum('Monto'),
            'depositos_transito' => $depositosTransito->sum('Monto'),
            'saldo_bancario_estimado' => $saldoLibros - $chequesPendientes->sum('Monto') + $depositosTransito->sum('Monto')
        ];

        return compact('infoCuenta', 'saldoLibros', 'chequesPendientes', 'depositosTransito', 'diferencias', 'ultimaConciliacion');
    }

    /**
     * Registra una nueva conciliación bancaria.
     */
    public function saveReconciliation($validatedData)
    {
        // CORRECCIÓN (Error 3): Verificar si la tabla existe
        if (!Schema::hasTable('BancosConciliacion')) {
            // Si la tabla no existe, intentamos usar el SP (que la creará si se ejecutó el SQL)
            // Si el SP tampoco existe, esto fallará y el usuario sabrá que falta el SP.
            Log::warning('La tabla BancosConciliacion no existe, intentando llamar a sp_registrar_conciliacion.');
        }

        // Llamamos al Stored Procedure que creamos en el script SQL
         DB::select('EXEC sp_registrar_conciliacion ?, ?, ?, ?, ?', [
            $validatedData['cuenta'],
            $validatedData['fecha_conciliacion'],
            $validatedData['saldo_bancario'],
            $validatedData['observaciones'] ?? null,
            Auth::user()->name ?? 'SYSTEM'
        ]);
    }

    /**
     * Obtiene el listado y resumen de transferencias.
     */
    public function getTransfersData($fechaInicio, $fechaFin)
    {
        $transferencias = DB::table('v_transferencias_bancarias')
            ->whereBetween('Fecha', [$fechaInicio, $fechaFin])
            ->orderBy('Fecha', 'desc')
            ->paginate(50);

        $resumenTransferencias = [
            'total_transferencias' => $transferencias->total(),
            'monto_total' => $transferencias->sum('Monto'),
            'cuentas_origen' => $transferencias->unique('cuenta_origen')->count(),
            'cuentas_destino' => $transferencias->unique('cuenta_destino')->count()
        ];
        
        return compact('transferencias', 'resumenTransferencias');
    }

    /**
     * Obtiene el resumen consolidado mensual por banco.
     */
    public function getMonthlySummary($anio, $mes)
    {
        $fechaInicio = Carbon::create($anio, $mes, 1)->startOfMonth()->format('Y-m-d');
        $fechaFin = Carbon::create($anio, $mes, 1)->endOfMonth()->format('Y-m-d');
        
        $resumenMensual = DB::table('v_resumen_mensual_bancos') // Tu vista v_resumen_mensual_bancos
            ->where('anio', $anio)
            ->where('mes', $mes)
            ->get();

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

        $totalesMes = [
            'total_ingresos' => $resumenMensual->sum('ingresos_mes'),
            'total_egresos' => $resumenMensual->sum('egresos_mes'),
            'total_movimientos' => $resumenMensual->sum('total_movimientos')
        ];

        return compact('resumenMensual', 'detalleDiario', 'totalesMes');
    }

    /**
     * Obtiene el flujo de caja diario usando el SP.
     */
    public function getDailyCashFlow($fecha, $bancoId)
    {
        // Usamos el Stored Procedure de la BD
        $flujoCaja = DB::select('EXEC sp_flujo_caja_bancario ?', [$fecha]);

        $query = DB::table('v_bancos_con_descripciones')
            ->whereDate('Fecha', $fecha)
            ->orderBy('Cuenta')
            ->orderBy('Numero');

        if ($bancoId) {
            $query->where('Cuenta', $bancoId); // Filtramos por 'Cuenta'
        }

        $movimientos = $query->get(); // Renombramos a 'movimientos'

        // Totales generales (usa valores del SP)
        $totalesGenerales = [
            'saldo_inicial_total' => collect($flujoCaja)->sum('saldo_inicial'),
            'ingresos_total' => collect($flujoCaja)->sum('ingresos_dia'),
            'egresos_total' => collect($flujoCaja)->sum('egresos_dia'),
            'saldo_final_total' => collect($flujoCaja)->sum('saldo_final'),
        ];
        
        return compact('flujoCaja', 'movimientos', 'totalesGenerales');
    }

    /**
     * Obtiene los datos de movimientos para generar un reporte específico.
     */
    public function getReportData($tipoReporte, $fechaInicio, $fechaFin, $cuenta, $listaBancos)
    {
        $movimientos = DB::table('v_bancos_con_descripciones')
            ->whereBetween('Fecha', [$fechaInicio, $fechaFin]);

        if ($cuenta) {
            $movimientos->where('Cuenta', $cuenta);
        }

        $movimientos = $movimientos->get();
        $datosReporte = [];

        switch ($tipoReporte) {
            case 'flujo':
                $topIngresos = $movimientos->where('Tipo', 1)->sortByDesc('Monto')->take(10)->values();
                $topEgresos = $movimientos->where('Tipo', 2)->sortByDesc('Monto')->take(10)->values();
                $datosReporte = compact('topIngresos', 'topEgresos');
                break;

            case 'general':
            case 'comparativo':
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
                            'ingresos' => $totalIngresos,
                            'egresos' => $totalEgresos,
                            'saldo_neto_periodo' => $totalIngresos - $totalEgresos,
                        ];
                    })->values();

                $totalIngresos = $porBanco->sum('ingresos');
                $totalEgresos = $porBanco->sum('egresos');

                $datosReporte = compact('totalIngresos', 'totalEgresos', 'porBanco');
                break;

            case 'conciliacion':
                $totalMovs = $movimientos->count();
                $conciliados = $movimientos->where('Documento', 'like', '%CONCILIADO%')->count();
                $pendientes = $totalMovs - $conciliados;
                $porcentaje = $totalMovs > 0 ? ($conciliados / $totalMovs) * 100 : 0;

                $porBancoConc = $movimientos->groupBy('Cuenta')->map(function ($grupo, $cuenta) use ($listaBancos) {
                    $total = $grupo->count();
                    $conc = $grupo->where('Documento', 'like', '%CONCILIADO%')->count();
                    $bancoNombre = $listaBancos->firstWhere('Cuenta', $cuenta)?->Banco ?? "Cuenta: $cuenta";
                    return [
                        'banco' => $bancoNombre,
                        'total' => $total,
                        'conciliados' => $conc,
                        'pendientes' => $total - $conc,
                        'porcentaje' => $total > 0 ? ($conc / $total) * 100 : 0,
                        'diferencia' => $grupo->sum('ingreso') - $grupo->sum('egreso'),
                    ];
                })->values();

                $datosReporte = compact('conciliados', 'pendientes', 'porcentaje', 'porBancoConc');
                break;

            default:
                $datosReporte = ['movimientos' => $movimientos];
                break;
        }

        // Devolvemos $datosReporte como un array
        return $datosReporte;
    }
}

