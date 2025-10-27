<?php

namespace App\Http\Controllers\Contabilidad;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class LibroDiarioController extends Controller
{
    /**
     * Display a listing of the resource.
     * Dashboard del Libro Diario - RUTA: contador.libro-diario.index
     */
    public function index(Request $request)
    {
        try {
            // Obtener parámetros de filtros
            $fechaInicio = $request->get('fecha_inicio') ?? Carbon::now()->startOfMonth()->format('Y-m-d');
            $fechaFin = $request->get('fecha_fin') ?? Carbon::now()->endOfMonth()->format('Y-m-d');
            $numeroAsiento = $request->get('numero_asiento');
            $cuentaContable = $request->get('cuenta_contable');
            
            // Obtener asientos del libro diario (tabla correcta: libro_diario)
            $asientos = $this->obtenerAsientos($fechaInicio, $fechaFin, $numeroAsiento, $cuentaContable);
            
            // Calcular totales
            $totales = $this->calcularTotales($fechaInicio, $fechaFin);
            
            // Obtener cuentas contables principales (tabla correcta: plan_cuentas)
            $cuentasContables = $this->obtenerCuentasContables();
            
            // Obtener datos para gráficos
            $graficoAsientosPorMes = $this->obtenerAsientosPorMes();
            $graficoMovimientosPorCuenta = $this->obtenerMovimientosPorCuenta();
            
            // Obtener alertas contables
            $alertas = $this->generarAlertasContables();
            
            return view('contabilidad.libros.diario.index', compact(
                'asientos', 
                'totales', 
                'cuentasContables',
                'fechaInicio',
                'fechaFin',
                'graficoAsientosPorMes',
                'graficoMovimientosPorCuenta',
                'alertas'
            ));

        } catch (\Exception $e) {
            Log::error('Error en libro diario: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return redirect()->back()->with('error', 'Error al cargar el libro diario: ' . $e->getMessage());
        }
    }

    /**
     * Crear nuevo asiento contable
     */
    public function create()
    {
        try {
            // Obtener siguiente número de asiento
            $siguienteNumero = $this->obtenerSiguienteNumeroAsiento();
            
            // Obtener cuentas contables para el formulario
            $cuentasContables = $this->obtenerCuentasContablesParaFormulario();
            
            // Obtener últimos 5 asientos para referencia
            $ultimosAsientos = $this->obtenerUltimosAsientos(5);
            
            return view('contabilidad.libros.diario.create', compact(
                'siguienteNumero', 
                'cuentasContables', 
                'ultimosAsientos'
            ));
            
        } catch (\Exception $e) {
            Log::error('Error al crear asiento: ' . $e->getMessage());
            return redirect()->route('contador.libro-diario.index')
                ->with('error', 'Error al cargar formulario de asiento');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Validar datos
            $validatedData = $request->validate([
                'numero' => 'required|string|unique:libro_diario,numero',
                'fecha' => 'required|date',
                'glosa' => 'required|string|max:255',
                'detalles' => 'required|array|min:2',
                'detalles.*.cuenta_contable' => 'required|string',
                'detalles.*.debe' => 'nullable|numeric|min:0',
                'detalles.*.haber' => 'nullable|numeric|min:0',
                'detalles.*.concepto' => 'required|string|max:255',
            ]);

            // Verificar que el balance cuadre
            $totalDebe = collect($validatedData['detalles'])->sum('debe');
            $totalHaber = collect($validatedData['detalles'])->sum('haber');

            if (abs($totalDebe - $totalHaber) > 0.01) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'El asiento no cuadra. Debe: S/ ' . number_format($totalDebe, 2) . 
                           ', Haber: S/ ' . number_format($totalHaber, 2));
            }

            DB::beginTransaction();
            
            // Crear asiento principal (tabla correcta: libro_diario)
            $asiento = [
                'numero' => $validatedData['numero'],
                'fecha' => $validatedData['fecha'],
                'glosa' => $validatedData['glosa'],
                'total_debe' => $totalDebe,
                'total_haber' => $totalHaber,
                'balanceado' => true,
                'usuario_id' => auth()->id(),
                'observaciones' => $request->observaciones,
                'created_at' => now(),
                'updated_at' => now()
            ];
            
            $asientoId = DB::table('libro_diario')->insertGetId($asiento);
            
            // Crear detalles del asiento (tabla correcta: libro_diario_detalles)
            foreach ($validatedData['detalles'] as $detalle) {
                DB::table('libro_diario_detalles')->insert([
                    'asiento_id' => $asientoId,
                    'cuenta_contable' => $detalle['cuenta_contable'],
                    'debe' => $detalle['debe'] ?? 0,
                    'haber' => $detalle['haber'] ?? 0,
                    'concepto' => $detalle['concepto'],
                    'documento_referencia' => $detalle['documento_referencia'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
            
            DB::commit();
            
            // Log para auditoría
            Log::info('Asiento contable creado', [
                'numero' => $validatedData['numero'],
                'total' => $totalDebe,
                'usuario' => auth()->user()->usuario ?? 'Sistema'
            ]);
            
            return redirect()->route('contador.libro-diario.show', $asientoId)
                ->with('success', 'Asiento contable registrado correctamente');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear asiento contable: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al registrar el asiento: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            // Obtener asiento con información del usuario (tabla correcta: libro_diario)
            $asiento = DB::table('libro_diario as a')
                ->leftJoin('accesoweb as u', 'a.usuario_id', '=', 'u.id') // Usar tabla accesoweb
                ->select(
                    'a.*',
                    'u.nombre as usuario_nombre',
                    'u.email as usuario_email'
                )
                ->where('a.id', $id)
                ->first();
                
            if (!$asiento) {
                return redirect()->route('contador.libro-diario.index')
                    ->with('error', 'Asiento no encontrado');
            }
            
            // Obtener detalles del asiento
            $detalles = DB::table('libro_diario_detalles as d')
                ->leftJoin('plan_cuentas as c', 'd.cuenta_contable', '=', 'c.codigo')
                ->select(
                    'd.*',
                    'c.nombre as cuenta_nombre',
                    'c.tipo as cuenta_tipo'
                )
                ->where('d.asiento_id', $id)
                ->orderBy('d.id')
                ->get();
            
            // Obtener asientos relacionados (anterior y siguiente)
            $asientoAnterior = DB::table('libro_diario')
                ->where('fecha', '<=', $asiento->fecha)
                ->where('id', '!=', $id)
                ->orderBy('fecha', 'desc')
                ->orderBy('numero', 'desc')
                ->first();
                
            $asientoSiguiente = DB::table('libro_diario')
                ->where('fecha', '>=', $asiento->fecha)
                ->where('id', '!=', $id)
                ->orderBy('fecha', 'asc')
                ->orderBy('numero', 'asc')
                ->first();
            
              return view('contabilidad.libros.diario.show', compact(
                'asiento', 
                'detalles', 
                'asientoAnterior', 
                'asientoSiguiente'
            ));
            
        } catch (\Exception $e) {
            Log::error('Error al mostrar asiento: ' . $e->getMessage());
            return redirect()->route('contador.libro-diario.index')
                ->with('error', 'Error al cargar el asiento');
        }
    }

    /**
     * Edit the specified resource.
     */
    public function edit($id)
    {
        try {
            $asiento = DB::table('libro_diario')->where('id', $id)->first();
                
            if (!$asiento) {
                return redirect()->route('contador.libro-diario.index')
                    ->with('error', 'Asiento no encontrado');
            }
            
            $detalles = DB::table('libro_diario_detalles')->where('asiento_id', $id)->get();
            $cuentasContables = $this->obtenerCuentasContablesParaFormulario();
            
            return view('contabilidad.libros.diario.create', compact(
                'asiento', 
                'detalles', 
                'cuentasContables'
            ));
            
        } catch (\Exception $e) {
            Log::error('Error al editar asiento: ' . $e->getMessage());
            return redirect()->route('contador.libro-diario.index')
                ->with('error', 'Error al cargar el asiento para edición');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Implementar lógica de actualización
        return redirect()->route('contador.libro-diario.index')
            ->with('success', 'Asiento actualizado correctamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            
            $asiento = DB::table('libro_diario')->where('id', $id)->first();
            if (!$asiento) {
                return redirect()->route('contador.libro-diario.index')
                    ->with('error', 'Asiento no encontrado');
            }
            
            // Eliminar detalles primero
            DB::table('libro_diario_detalles')->where('asiento_id', $id)->delete();
            
            // Eliminar asiento principal
            DB::table('libro_diario')->where('id', $id)->delete();
            
            DB::commit();
            
            Log::info('Asiento contable eliminado', [
                'numero' => $asiento->numero,
                'total' => $asiento->total_debe,
                'usuario' => auth()->user()->usuario ?? 'Sistema'
            ]);
            
            return redirect()->route('contador.libro-diario.index')
                ->with('success', 'Asiento eliminado correctamente');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar asiento: ' . $e->getMessage());
            
            return redirect()->route('contador.libro-diario.index')
                ->with('error', 'Error al eliminar el asiento');
        }
    }

    /**
     * Exportar libro diario a Excel/PDF
     */
    public function exportar(Request $request)
    {
        try {
            $formato = $request->get('formato', 'excel');
            $fechaInicio = $request->get('fecha_inicio');
            $fechaFin = $request->get('fecha_fin');
            
            $asientos = $this->obtenerAsientos($fechaInicio, $fechaFin);
            $totales = $this->calcularTotales($fechaInicio, $fechaFin);
            
            if ($formato === 'pdf') {
                return $this->generarPDF($asientos, $totales, $fechaInicio, $fechaFin);
            } else {
                return $this->generarExcel($asientos, $totales, $fechaInicio, $fechaFin);
            }
            
        } catch (\Exception $e) {
            Log::error('Error al exportar libro diario: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al generar el reporte');
        }
    }

    // ==================== MÉTODOS PRIVADOS ====================
    
    private function obtenerAsientos($fechaInicio, $fechaFin, $numeroAsiento = null, $cuentaContable = null)
    {
        $query = DB::table('libro_diario as a') // tabla correcta
            ->leftJoin('accesoweb as u', 'a.usuario_id', '=', 'u.id') // tabla accesoweb
            ->select(
                'a.*',
                'u.nombre as usuario_nombre'
            )
            ->whereBetween('a.fecha', [$fechaInicio, $fechaFin])
            ->orderBy('a.fecha', 'desc')
            ->orderBy('a.numero', 'desc');
            
        if ($numeroAsiento) {
            $query->where('a.numero', 'like', '%' . $numeroAsiento . '%');
        }
        
        if ($cuentaContable) {
            $query->whereExists(function($q) use ($cuentaContable) {
                $q->select(DB::raw(1))
                  ->from('libro_diario_detalles as d') // tabla correcta
                  ->whereRaw('d.asiento_id = a.id')
                  ->where('d.cuenta_contable', 'like', '%' . $cuentaContable . '%');
            });
        }
        
        return $query->paginate(20);
    }

    private function calcularTotales($fechaInicio, $fechaFin)
    {
        $resultado = DB::table('libro_diario') // tabla correcta
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->selectRaw('
                COUNT(*) as total_asientos,
                SUM(total_debe) as total_debe,
                SUM(total_haber) as total_haber,
                AVG(total_debe) as promedio_asiento
            ')
            ->first();
            
        return [
            'total_asientos' => $resultado->total_asientos ?? 0,
            'total_debe' => round($resultado->total_debe ?? 0, 2),
            'total_haber' => round($resultado->total_haber ?? 0, 2),
            'promedio_asiento' => round($resultado->promedio_asiento ?? 0, 2),
            'balance' => round(($resultado->total_debe ?? 0) - ($resultado->total_haber ?? 0), 2)
        ];
    }

    private function obtenerCuentasContables()
    {
        return DB::table('plan_cuentas') // tabla correcta
            ->where('activo', 1)
            ->orderBy('codigo')
            ->get();
    }

    private function obtenerCuentasContablesParaFormulario()
    {
        return DB::table('plan_cuentas') // tabla correcta
            ->where('activo', 1)
            ->whereIn('tipo', ['ACTIVO', 'PASIVO', 'PATRIMONIO', 'INGRESO', 'GASTO'])
            ->orderBy('codigo')
            ->get()
            ->groupBy('tipo');
    }

    private function obtenerSiguienteNumeroAsiento()
    {
        $ultimoNumero = DB::table('libro_diario') // tabla correcta
            ->whereYear('fecha', now()->year)
            ->max('numero');
            
        if (!$ultimoNumero) {
            return now()->format('Y') . '0001';
        }
        
        $numero = (int) substr($ultimoNumero, 4) + 1;
        return now()->format('Y') . str_pad($numero, 4, '0', STR_PAD_LEFT);
    }

    private function obtenerUltimosAsientos($cantidad = 5)
    {
        return DB::table('libro_diario') // tabla correcta
            ->orderBy('fecha', 'desc')
            ->orderBy('numero', 'desc')
            ->limit($cantidad)
            ->get();
    }

    private function obtenerAsientosPorMes()
    {
        $resultado = DB::table('libro_diario') // tabla correcta
            ->whereYear('fecha', now()->year)
            ->selectRaw('
                MONTH(fecha) as mes,
                COUNT(*) as cantidad,
                SUM(total_debe) as total
            ')
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();
            
        $meses = [];
        $datos = [];
        
        for ($i = 1; $i <= 12; $i++) {
            $mesNombre = Carbon::create(now()->year, $i)->locale('es')->isoFormat('MMM');
            $meses[] = ucfirst($mesNombre);
            
            $asientoMes = $resultado->where('mes', $i)->first();
            $datos[] = $asientoMes ? $asientoMes->cantidad : 0;
        }
        
        return [
            'labels' => $meses,
            'data' => $datos
        ];
    }

    private function obtenerMovimientosPorCuenta()
    {
        $resultado = DB::table('libro_diario_detalles as d') // tabla correcta
            ->join('plan_cuentas as c', 'd.cuenta_contable', '=', 'c.codigo') // tabla correcta
            ->whereYear('a.fecha', now()->year)
            ->join('libro_diario as a', function($join) { // tabla correcta
                $join->on('d.asiento_id', '=', 'a.id');
            })
            ->select(
                'c.codigo',
                'c.nombre',
                DB::raw('SUM(d.debe + d.haber) as total_movimientos')
            )
            ->groupBy('c.codigo', 'c.nombre')
            ->orderBy('total_movimientos', 'desc')
            ->limit(10)
            ->get();
            
        return $resultado;
    }

    private function generarAlertasContables()
    {
        $alertas = [];
        
        // Asientos sin balancear
        $asientosSinBalancear = DB::table('libro_diario') // tabla correcta
            ->where('balanceado', false)
            ->count();
            
        if ($asientosSinBalancear > 0) {
            $alertas[] = [
                'tipo' => 'warning',
                'titulo' => 'Asientos sin Balancear',
                'mensaje' => "{$asientosSinBalancear} asientos requieren revisión",
                'icono' => 'exclamation-triangle'
            ];
        }
        
        // Asientos del día de hoy
        $asientosHoy = DB::table('libro_diario') // tabla correcta
            ->whereDate('fecha', today())
            ->count();
            
        if ($asientosHoy == 0) {
            $alertas[] = [
                'tipo' => 'info',
                'titulo' => 'Sin Asientos Hoy',
                'mensaje' => 'No se han registrado asientos contables hoy',
                'icono' => 'info-circle'
            ];
        }
        
        return $alertas;
    }

    private function generarPDF($asientos, $totales, $fechaInicio, $fechaFin)
    {
        // Implementar generación de PDF
    }

    private function generarExcel($asientos, $totales, $fechaInicio, $fechaFin)
    {
        // Implementar generación de Excel
    }
}