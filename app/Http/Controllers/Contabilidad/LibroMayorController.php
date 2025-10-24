<?php

namespace App\Http\Controllers\Contabilidad;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LibroMayorController extends Controller
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

            // Obtener resumen por cuentas
            $query = DB::table('t_detalle_diario as a')
                ->select([
                    'a.Tipo as cuenta',
                    DB::raw('COUNT(*) as movimientos'),
                    DB::raw('SUM(CAST(a.Importe as MONEY)) as total_debe'),
                    DB::raw('SUM(CAST(a.Saldo as MONEY)) as total_haber'),
                    DB::raw('SUM(CAST(a.Importe as MONEY)) - SUM(CAST(a.Saldo as MONEY)) as saldo_nuevo')
                ])
                ->whereBetween('a.FechaF', [$fechaInicio, $fechaFin]);

            if ($cuenta) {
                $query->where('a.Tipo', 'like', "%$cuenta%");
            }

            $cuentas = $query->groupBy('a.Tipo')
                ->orderBy('a.Tipo')
                ->get();

            // Obtener movimientos detallados si se selecciona una cuenta específica
            $movimientosDetalle = [];
            $saldoAnterior = 0;

            if ($cuenta && $cuentas->where('cuenta', $cuenta)->isNotEmpty()) {
                // Saldo anterior a la fecha inicio
                $saldoAnterior = DB::table('t_detalle_diario')
                    ->where('Tipo', $cuenta)
                    ->where('FechaF', '<', $fechaInicio)
                    ->selectRaw('SUM(CAST(Importe as MONEY)) - SUM(CAST(Saldo as MONEY)) as saldo')
                    ->value('saldo') ?? 0;

                // Movimientos en el período
                $movimientosDetalle = DB::table('t_detalle_diario')
                    ->where('Tipo', $cuenta)
                    ->whereBetween('FechaF', [$fechaInicio, $fechaFin])
                    ->select([
                        'Numero',
                        'FechaF',
                        'Descripcion',
                        'Importe',
                        'Saldo',
                        'Nombre'
                    ])
                    ->orderBy('FechaF')
                    ->orderBy('Numero')
                    ->get();
            }

            // Totales del libro mayor
            $totales = DB::table('t_detalle_diario as a')
                ->whereBetween('a.FechaF', [$fechaInicio, $fechaFin])
                ->select([
                    DB::raw('COUNT(DISTINCT a.Tipo) as total_cuentas'),
                    DB::raw('SUM(CAST(a.Importe as MONEY)) as total_debe'),
                    DB::raw('SUM(CAST(a.Saldo as MONEY)) as total_haber')
                ])
                ->first();

            return view('contabilidad.libros.mayor.index', compact(
                'cuentas', 'fechaInicio', 'fechaFin', 'cuenta', 'movimientosDetalle', 'saldoAnterior', 'totales'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al cargar el libro mayor: ' . $e->getMessage());
        }
    }

    /**
     * Show detail for a specific account
     */
    public function cuentaDetalle($cuenta)
    {
        try {
            $fechaInicio = Carbon::now()->startOfMonth()->format('Y-m-d');
            $fechaFin = Carbon::now()->endOfMonth()->format('Y-m-d');

            // Información de la cuenta
            $infoCuenta = DB::table('t_detalle_diario')
                ->where('Tipo', $cuenta)
                ->selectRaw('MAX(Tipo) as cuenta, COUNT(*) as total_movimientos')
                ->first();

            // Saldo anterior al período
            $saldoAnterior = DB::table('t_detalle_diario')
                ->where('Tipo', $cuenta)
                ->where('FechaF', '<', $fechaInicio)
                ->selectRaw('SUM(CAST(Importe as MONEY)) - SUM(CAST(Saldo as MONEY)) as saldo_anterior')
                ->value('saldo_anterior') ?? 0;

            // Movimientos del período
            $movimientos = DB::table('t_detalle_diario')
                ->where('Tipo', $cuenta)
                ->whereBetween('FechaF', [$fechaInicio, $fechaFin])
                ->select([
                    'Numero as numero',
                    'FechaF as fecha',
                    'Descripcion as concepto',
                    'Importe as debe',
                    'Saldo as haber',
                    'Nombre as auxiliar',
                    DB::raw('CAST(Importe as MONEY) - CAST(Saldo as MONEY) as diferencia')
                ])
                ->orderBy('FechaF')
                ->orderBy('Numero')
                ->get();

            // Calcular saldos acumulados
            $saldoAcumulado = $saldoAnterior;
            foreach ($movimientos as $movimiento) {
                $saldoAcumulado += $movimiento->diferencia;
                $movimiento->saldo_acumulado = $saldoAcumulado;
            }

            // Totales del período
            $totalesPeriodo = [
                'debe' => $movimientos->sum('debe'),
                'haber' => $movimientos->sum('haber'),
                'saldo_final' => $saldoAnterior + $movimientos->sum('debe') - $movimientos->sum('haber')
            ];

            return view('contabilidad.libros.mayor.cuenta', compact(
                'infoCuenta', 'saldoAnterior', 'movimientos', 'totalesPeriodo', 'fechaInicio', 'fechaFin'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al cargar la cuenta: ' . $e->getMessage());
        }
    }

    /**
     * Get account movements for a date range
     */
    public function movimientos(Request $request)
    {
        try {
            $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfYear()->format('Y-m-d'));
            $fechaFin = $request->input('fecha_fin', Carbon::now()->endOfYear()->format('Y-m-d'));
            $cuenta = $request->input('cuenta');
            $mes = $request->input('mes');

            $query = DB::table('t_detalle_diario as a')
                ->select([
                    'a.Numero',
                    'a.FechaF',
                    'a.Tipo',
                    'a.Descripcion',
                    'a.Importe',
                    'a.Saldo',
                    'a.Nombre',
                    DB::raw('MONTH(a.FechaF) as mes'),
                    DB::raw('YEAR(a.FechaF) as anio')
                ])
                ->whereBetween('a.FechaF', [$fechaInicio, $fechaFin]);

            if ($cuenta) {
                $query->where('a.Tipo', 'like', "%$cuenta%");
            }

            if ($mes) {
                $query->whereMonth('a.FechaF', $mes);
            }

            $movimientos = $query->orderBy('a.FechaF', 'desc')
                ->orderBy('a.Numero', 'asc')
                ->paginate(100);

            // Resumen por meses
            $resumenMensual = DB::table('t_detalle_diario as a')
                ->whereBetween('a.FechaF', [$fechaInicio, $fechaFin])
                ->select([
                    DB::raw('MONTH(a.FechaF) as mes'),
                    DB::raw('YEAR(a.FechaF) as anio'),
                    DB::raw('DATENAME(month, a.FechaF) as mes_nombre'),
                    DB::raw('SUM(CAST(a.Importe as MONEY)) as total_debe'),
                    DB::raw('SUM(CAST(a.Saldo as MONEY)) as total_haber')
                ])
                ->groupBy('anio', 'mes', 'mes_nombre')
                ->orderBy('anio', 'desc')
                ->orderBy('mes', 'desc')
                ->get();

            return view('contabilidad.libros.mayor.movimientos', compact(
                'movimientos', 'fechaInicio', 'fechaFin', 'cuenta', 'mes', 'resumenMensual'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al cargar movimientos: ' . $e->getMessage());
        }
    }

    /**
     * Get balance comparison between periods
     */
    public function comparacionPeriodos()
    {
        try {
            $fechaActual = Carbon::now();
            $periodoActual = [
                'inicio' => $fechaActual->startOfMonth()->format('Y-m-d'),
                'fin' => $fechaActual->endOfMonth()->format('Y-m-d')
            ];
            $periodoAnterior = [
                'inicio' => $fechaActual->subMonth()->startOfMonth()->format('Y-m-d'),
                'fin' => $fechaActual->subMonth()->endOfMonth()->format('Y-m-d')
            ];

            // Período actual
            $actual = DB::table('t_detalle_diario')
                ->whereBetween('FechaF', [$periodoActual['inicio'], $periodoActual['fin']])
                ->select([
                    'Tipo as cuenta',
                    DB::raw('SUM(CAST(Importe as MONEY)) as debe'),
                    DB::raw('SUM(CAST(Saldo as MONEY)) as haber'),
                    DB::raw('SUM(CAST(Importe as MONEY)) - SUM(CAST(Saldo as MONEY)) as saldo')
                ])
                ->groupBy('Tipo')
                ->get();

            // Período anterior
            $anterior = DB::table('t_detalle_diario')
                ->whereBetween('FechaF', [$periodoAnterior['inicio'], $periodoAnterior['fin']])
                ->select([
                    'Tipo as cuenta',
                    DB::raw('SUM(CAST(Importe as MONEY)) as debe_anterior'),
                    DB::raw('SUM(CAST(Saldo as MONEY)) as haber_anterior'),
                    DB::raw('SUM(CAST(Importe as MONEY)) - SUM(CAST(Saldo as MONEY)) as saldo_anterior')
                ])
                ->groupBy('Tipo')
                ->get();

            // Combinar ambos períodos
            $comparacion = [];
            foreach ($actual as $cuentaActual) {
                $cuentaAnterior = $anterior->where('cuenta', $cuentaActual->cuenta)->first();
                
                $comparacion[] = [
                    'cuenta' => $cuentaActual->cuenta,
                    'debe_actual' => $cuentaActual->debe,
                    'haber_actual' => $cuentaActual->haber,
                    'saldo_actual' => $cuentaActual->saldo,
                    'debe_anterior' => $cuentaAnterior->debe_anterior ?? 0,
                    'haber_anterior' => $cuentaAnterior->haber_anterior ?? 0,
                    'saldo_anterior' => $cuentaAnterior->saldo_anterior ?? 0,
                    'variacion_debe' => ($cuentaActual->debe - ($cuentaAnterior->debe_anterior ?? 0)),
                    'variacion_haber' => ($cuentaActual->haber - ($cuentaAnterior->haber_anterior ?? 0))
                ];
            }

            return view('contabilidad.libros.mayor.comparacion', compact(
                'comparacion', 'periodoActual', 'periodoAnterior'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al generar comparación: ' . $e->getMessage());
        }
    }

    /**
     * Export book to Excel/CSV
     */
    public function exportar(Request $request)
    {
        try {
            $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
            $fechaFin = $request->input('fecha_fin', Carbon::now()->endOfMonth()->format('Y-m-d'));

            $datos = DB::table('t_detalle_diario as a')
                ->whereBetween('a.FechaF', [$fechaInicio, $fechaFin])
                ->select([
                    'a.Numero as Numero_Asiento',
                    'a.FechaF as Fecha',
                    'a.Tipo as Cuenta',
                    'a.Descripcion as Descripcion',
                    'a.Importe as Debito',
                    'a.Saldo as Credito',
                    'a.Nombre as Auxiliary'
                ])
                ->orderBy('a.FechaF')
                ->orderBy('a.Numero')
                ->get();

            // Aquí implementarías la exportación a Excel/CSV
            // Por ahora retorno la vista con los datos
            
            return view('contabilidad.libros.mayor.exportar', compact(
                'datos', 'fechaInicio', 'fechaFin'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al exportar: ' . $e->getMessage());
        }
    }
}