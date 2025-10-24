<?php

namespace App\Http\Controllers\Contabilidad;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LibroDiarioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
            $fechaFin = $request->input('fecha_fin', Carbon::now()->endOfMonth()->format('Y-m-d'));
            
            // Obtener asientos del libro diario usando vista o tabla de asientos
            $asientos = DB::table('t_detalle_diario as a')
                ->select([
                    'a.Numero',
                    'a.FechaF as fecha',
                    'a.Descripcion as concepto',
                    'a.Tipo as cuenta',
                    'a.Importe as debe',
                    'a.Saldo as haber',
                    'a.Nombre as auxiliar'
                ])
                ->whereBetween('a.FechaF', [$fechaInicio, $fechaFin])
                ->orderBy('a.FechaF', 'desc')
                ->orderBy('a.Numero', 'asc')
                ->paginate(50);

            // Obtener resumen por cuenta
            $resumenCuentas = DB::table('t_detalle_diario as a')
                ->select([
                    'a.Tipo as cuenta',
                    DB::raw('SUM(CAST(a.Importe as MONEY)) as total_debe'),
                    DB::raw('SUM(CAST(a.Saldo as MONEY)) as total_haber')
                ])
                ->whereBetween('a.FechaF', [$fechaInicio, $fechaFin])
                ->groupBy('a.Tipo')
                ->get();

            // Totales del período
            $totales = DB::table('t_detalle_diario as a')
                ->select([
                    DB::raw('SUM(CAST(a.Importe as MONEY)) as total_debe'),
                    DB::raw('SUM(CAST(a.Saldo as MONEY)) as total_haber'),
                    DB::raw('COUNT(DISTINCT a.Numero) as total_asientos')
                ])
                ->whereBetween('a.FechaF', [$fechaInicio, $fechaFin])
                ->first();

            return view('contabilidad.libros.diario.index', compact(
                'asientos', 'fechaInicio', 'fechaFin', 'resumenCuentas', 'totales'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al cargar el libro diario: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Obtener último número de asiento
        $ultimoNumero = DB::table('t_detalle_diario')
            ->max('Numero') ?? 'AS-0001';

        return view('contabilidad.libros.diario.create', compact('ultimoNumero'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'numero' => 'required|string|max:20|unique:t_detalle_diario,Numero',
            'fecha' => 'required|date',
            'concepto' => 'required|string|max:255',
            'debe.*' => 'required|numeric|min:0',
            'haber.*' => 'required|numeric|min:0'
        ]);

        try {
            DB::beginTransaction();

            // Validar que la suma de debe = suma de haber
            $totalDebe = array_sum($request->debe);
            $totalHaber = array_sum($request->haber);

            if (abs($totalDebe - $totalHaber) > 0.01) {
                throw new \Exception('El total de debe debe ser igual al total de haber');
            }

            // Insertar líneas del asiento
            foreach ($request->cuentas as $index => $cuenta) {
                if ($request->debe[$index] > 0 || $request->haber[$index] > 0) {
                    DB::table('t_detalle_diario')->insert([
                        'Numero' => $request->numero,
                        'FechaF' => $request->fecha,
                        'Descripcion' => $request->concepto,
                        'Tipo' => $cuenta,
                        'Importe' => $request->debe[$index],
                        'Saldo' => $request->haber[$index],
                        'Nombre' => $request->auxiliar[$index] ?? '',
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }

            // Registrar en auditoría
            DB::table('Auditoria_Sistema')->insert([
                'usuario' => Auth::user()->name,
                'accion' => 'ASIENTO_CONTABLE',
                'tabla' => 't_detalle_diario',
                'detalle' => "Asiento {$request->numero} - {$request->concepto}",
                'fecha' => now()
            ]);

            DB::commit();

            return redirect()->route('libro-diario')
                ->with('success', 'Asiento contable registrado exitosamente');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Error al registrar el asiento: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $asiento = DB::table('t_detalle_diario')
                ->where('Numero', $id)
                ->get();

            if ($asiento->isEmpty()) {
                return redirect()->route('libro-diario')->with('error', 'Asiento no encontrado');
            }

            return view('contabilidad.libros.diario.show', compact('asiento'));

        } catch (\Exception $e) {
            return redirect()->route('libro-diario')->with('error', 'Error al mostrar el asiento');
        }
    }

    /**
     * Obtener resumen mensual del libro diario
     */
    public function resumenMensual()
    {
        try {
            $resumen = DB::select("
                SELECT 
                    YEAR(FechaF) as anio,
                    MONTH(FechaF) as mes,
                    DATENAME(month, FechaF) as mes_nombre,
                    COUNT(DISTINCT Numero) as total_asientos,
                    SUM(CAST(Importe as MONEY)) as total_debe,
                    SUM(CAST(Saldo as MONEY)) as total_haber
                FROM t_detalle_diario 
                WHERE FechaF >= DATEADD(month, -12, GETDATE())
                GROUP BY YEAR(FechaF), MONTH(FechaF), DATENAME(month, FechaF)
                ORDER BY anio DESC, mes DESC
            ");

            return view('contabilidad.libros.diario.resumen', compact('resumen'));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al cargar el resumen: ' . $e->getMessage());
        }
    }

    /**
     * Obtener cuentas más utilizadas
     */
    public function cuentasMasUtilizadas()
    {
        try {
            $cuentas = DB::select("
                SELECT 
                    Tipo as cuenta,
                    COUNT(*) as movimientos,
                    SUM(CAST(Importe as MONEY)) as total_debe,
                    SUM(CAST(Saldo as MONEY)) as total_haber
                FROM t_detalle_diario
                WHERE FechaF >= DATEADD(month, -6, GETDATE())
                GROUP BY Tipo
                ORDER BY movimientos DESC
            ");

            return view('contabilidad.libros.diario.cuentas', compact('cuentas'));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al cargar cuentas: ' . $e->getMessage());
        }
    }
}