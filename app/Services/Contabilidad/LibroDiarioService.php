<?php

namespace App\Services\Contabilidad;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;
// --- ¡IMPORTAMOS TODOS LOS MODELOS! ---
use App\Models\LibroDiario;
use App\Models\LibroDiarioDetalle;
use App\Models\PlanCuentas;
use App\Models\AccesoWeb;
// --- IMPORTAMOS LOS EXPORTADORES ---
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LibroDiarioExport; // Asumimos que la crearás
use Barryvdh\DomPDF\Facade\Pdf as DomPDF;

class LibroDiarioService
{
    /**
     * Almacena un nuevo asiento contable.
     * (Esta función ya era perfecta y usaba Eloquent).
     */
    public function storeAsiento(array $validatedData, $observaciones, $userId)
    {
        $totalDebe = collect($validatedData['detalles'])->sum('debe');
        $totalHaber = collect($validatedData['detalles'])->sum('haber');

        if (abs($totalDebe - $totalHaber) > 0.01) {
            throw new \Exception('El asiento no cuadra. Debe: S/ ' . number_format($totalDebe, 2) .
                ', Haber: S/ ' . number_format($totalHaber, 2));
        }

        DB::beginTransaction();
        try {
            $numeroAsiento = $this->obtenerSiguienteNumeroAsiento();
            
            $asiento = LibroDiario::create([
                'numero' => $numeroAsiento,
                'fecha' => $validatedData['fecha'],
                'glosa' => $validatedData['glosa'],
                'total_debe' => $totalDebe,
                'total_haber' => $totalHaber,
                'balanceado' => true,
                'estado' => 'ACTIVO',
                'usuario_id' => $userId,
                'observaciones' => $observaciones,
            ]);
            
            $asiento->detalles()->createMany($validatedData['detalles']);

            DB::commit();
            Log::info('Asiento contable creado', ['numero' => $numeroAsiento, 'usuario_id' => $userId]);
            return $asiento->id;

        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Error al registrar el asiento: ' . $e->getMessage());
        }
    }

    /**
     * Actualiza la cabecera de un asiento.
     * (Esta función ya era perfecta y usaba Eloquent).
     */
    public function updateAsiento($id, array $validatedData)
    {
        $asiento = LibroDiario::findOrFail($id);
        $asiento->update($validatedData);
    }

    /**
     * Solicita la eliminación de un asiento (cambia estado).
     * (Esta función ya era perfecta y usaba Eloquent).
     */
    public function solicitarEliminacion($id, $usuario)
    {
        $asiento = LibroDiario::findOrFail($id);
        $asiento->estado = 'PENDIENTE_ELIMINACION';
        $asiento->save();

        $titulo = "Solicitud de Eliminación de Asiento";
        $mensaje = "El usuario {$usuario->usuario} ha solicitado eliminar el asiento N° {$asiento->numero}.";
        $url = route('admin.solicitudes.asiento.index');

        if (method_exists($asiento, 'notificarAdmin')) {
            $asiento->notificarAdmin($titulo, $mensaje, $url);
        } else {
            Log::warning("El método notificarAdmin() no existe en el modelo LibroDiario.");
        }
    }

    // ===================================================================
    // MÉTODOS DE LECTURA (R) - ¡AHORA OPTIMIZADOS CON ELOQUENT!
    // ===================================================================

    /**
     * Obtiene los datos para el dashboard del Libro Diario.
     */
    public function getDashboardData(Request $request)
    {
        $fechaInicio = $request->get('fecha_inicio') ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $fechaFin = $request->get('fecha_fin') ?? Carbon::now()->endOfMonth()->format('Y-m-d');
        
        $asientos = $this->obtenerAsientos(
            $fechaInicio, $fechaFin,
            $request->get('numero_asiento'),
            $request->get('cuenta_contable')
        );
        
        $totales = $this->calcularTotales($fechaInicio, $fechaFin);
        $cuentasContables = $this->obtenerCuentasContables(); // Optimizado
        $graficoAsientosPorMes = $this->obtenerAsientosPorMes();
        $graficoMovimientosPorCuenta = $this->obtenerMovimientosPorCuenta($fechaInicio, $fechaFin);
        $alertas = $this->generarAlertasContables();
        
        return compact(
            'asientos', 'totales', 'cuentasContables', 'fechaInicio',
            'fechaFin', 'graficoAsientosPorMes', 'graficoMovimientosPorCuenta', 'alertas'
        );
    }

    /**
     * Obtiene datos para el formulario de creación. (OPTIMIZADO)
     */
    public function getCreateFormData()
    {
        // ¡AHORA USA EL MODELO!
        $cuentasContables = PlanCuentas::where('activo', 1)
            ->orderBy('codigo')
            ->get(['codigo', 'nombre']);
            
        // ¡AHORA USA EL MODELO!
        $ultimosAsientos = $this->obtenerUltimosAsientos(5);
        
        return compact('cuentasContables', 'ultimosAsientos');
    }

    /**
     * Obtiene detalles de un asiento. (OPTIMIZADO)
     */
    public function getAsientoDetails($id)
    {
        // ¡MÁGIA DE ELOQUENT!
        // Carga el asiento Y TAMBIÉN carga las relaciones
        // 'usuario' y 'detalles' (y la relación anidada 'detalles.cuenta')
        // todo en consultas súper optimizadas (Eager Loading).
        $asiento = LibroDiario::with(['usuario', 'detalles.cuenta'])->find($id);
            
        if (!$asiento) {
            return null; // El controlador manejará el not found
        }
        
        // Los detalles ya vienen cargados gracias a 'with()'
        $detalles = $asiento->detalles;
        
        // ¡AHORA USA EL MODELO!
        $asientoAnterior = LibroDiario::where('fecha', '<=', $asiento->fecha)
            ->where('id', '!=', $id)
            ->orderBy('fecha', 'desc')->orderBy('numero', 'desc')->first();
            
        $asientoSiguiente = LibroDiario::where('fecha', '>=', $asiento->fecha)
            ->where('id', '!=', $id)
            ->orderBy('fecha', 'asc')->orderBy('numero', 'asc')->first();
        
        return compact('asiento', 'detalles', 'asientoAnterior', 'asientoSiguiente');
    }

    /**
     * Obtiene datos para el formulario de edición. (OPTIMIZADO)
     */
    public function getEditFormData($id)
    {
        // ¡AHORA USA EL MODELO!
        $asiento = LibroDiario::find($id);
        if (!$asiento) {
            return ['asiento' => null, 'detalles' => collect(), 'cuentasContables' => collect()];
        }

        $detalles = $asiento->detalles; // Carga la relación
        $cuentasContables = $this->obtenerCuentasContablesParaFormulario(); // Optimizado

        return compact('asiento', 'detalles', 'cuentasContables');
    }

    /**
     * Maneja la lógica de exportación.
     */
    public function export(Request $request)
    {
        $formato = $request->get('formato', 'excel');
        $fechaInicio = $request->get('fecha_inicio');
        $fechaFin = $request->get('fecha_fin');

        $asientos = $this->obtenerAsientos($fechaInicio, $fechaFin, null, null, false); // Ya usa Eloquent
        $totales = $this->calcularTotales($fechaInicio, $fechaFin); // DB::table está bien
        
        if ($formato === 'pdf') {
            return $this->generarPDF($asientos, $totales, $fechaInicio, $fechaFin);
        } else {
            return $this->generarExcel($asientos, $totales, $fechaInicio, $fechaFin);
        }
    }


    // ===================================================================
    // MÉTODOS DE AYUDA (¡HÍBRIDOS!)
    // ===================================================================

    /**
     * Obtiene los asientos filtrados. (OPTIMIZADO)
     */
    public function obtenerAsientos($fechaInicio = null, $fechaFin = null, $numeroAsiento = null, $cuentaContable = null, $paginar = true)
    {
        // ¡AHORA USA EL MODELO!
        // 'with('usuario')' precarga la relación de usuario para evitar
        // consultas N+1 en la vista. Es mucho más rápido.
        
        // ▼▼▼ ¡¡AQUÍ ESTÁ LA SOLUCIÓN!! ▼▼▼
        //
        // Le decimos a Eloquent que cargue no solo el 'usuario',
        // sino también los 'detalles' de cada asiento, y la 'cuenta'
        // relacionada a cada uno de esos detalles.
        // Esto reduce cientos de consultas a solo 3 o 4.
        $query = LibroDiario::with(['usuario', 'detalles.cuenta']) 
            ->when($fechaInicio, function ($q) use ($fechaInicio, $fechaFin) {
                $q->whereBetween('fecha', [$fechaInicio, $fechaFin]);
            })
            ->when($numeroAsiento, function ($q) use ($numeroAsiento) {
                $q->where('numero', 'like', '%' . $numeroAsiento . '%');
            })
            ->when($cuentaContable, function ($q) use ($cuentaContable) {
                // 'whereHas' es la forma Eloquent de tu 'whereExists'.
                $q->whereHas('detalles', function ($detalleQuery) use ($cuentaContable) {
                    $detalleQuery->where('cuenta_contable', 'like', '%' . $cuentaContable . '%');
                });
            })
            ->orderBy('fecha', 'desc')
            ->orderBy('numero', 'desc');
            
        return $paginar ? $query->paginate(20) : $query->get();
    }

    /**
     * Obtiene cuentas contables. (OPTIMIZADO)
     */
    public function obtenerCuentasContables()
    {
        // ¡AHORA USA EL MODELO!
        return PlanCuentas::where('activo', 1)
            ->orderBy('codigo')
            ->get();
    }

    /**
     * Obtiene cuentas para el formulario. (OPTIMIZADO)
     */
    public function obtenerCuentasContablesParaFormulario()
    {
        // ¡AHORA USA EL MODELO!
        return PlanCuentas::where('activo', 1)
            ->whereIn('tipo', ['ACTIVO', 'PASIVO', 'PATRIMONIO', 'INGRESO', 'GASTO'])
            ->orderBy('codigo')
            ->get()
            ->groupBy('tipo');
    }

    /**
     * Obtiene los últimos N asientos. (OPTIMIZADO)
     */
    public function obtenerUltimosAsientos($cantidad = 5)
    {
        // ¡AHORA USA EL MODELO!
        return LibroDiario::orderBy('fecha', 'desc')
            ->orderBy('numero', 'desc')
            ->limit($cantidad)
            ->get();
    }

    // ===================================================================
    // MÉTODOS DE REPORTE (DB::table es el rey aquí)
    //
    // Para reportes pesados, agregaciones (SUM, COUNT, AVG) y
    // generación de números, usar DB::table es a menudo MÁS RÁPIDO
    // y más limpio que Eloquent. Tu lógica original es perfecta.
    // ===================================================================

    /**
     * Calcula los totales. (Tu lógica original es óptima).
     */
    public function calcularTotales($fechaInicio = null, $fechaFin = null)
    {
        $query = DB::table('libro_diario');
        if ($fechaInicio && $fechaFin) {
            $query->whereBetween('fecha', [$fechaInicio, $fechaFin]);
        }
        $resultado = $query->selectRaw('COUNT(*) as total_asientos, SUM(total_debe) as total_debe, SUM(total_haber) as total_haber, AVG(total_debe) as promedio_asiento')
                           ->first();
        return [
            'total_asientos' => $resultado->total_asientos ?? 0,
            'total_debe' => round($resultado->total_debe ?? 0, 2),
            'total_haber' => round($resultado->total_haber ?? 0, 2),
            'promedio_asiento' => round($resultado->promedio_asiento ?? 0, 2),
            'balance' => round(($resultado->total_debe ?? 0) - ($resultado->total_haber ?? 0), 2)
        ];
    }

    /**
     * Genera el siguiente número de asiento. (Tu lógica original es óptima).
     */
    public function obtenerSiguienteNumeroAsiento()
    {
        $anio = now()->format('Y');
        $intentos = 0;
        $maxIntentos = 10;

        do {
            $ultimoNumero = DB::table('libro_diario')
                ->where('numero', 'like', $anio . '-%')
                ->max('numero');
            $numero = !$ultimoNumero ? 1 : ((int) substr($ultimoNumero, 5) + 1);
            $nuevoNumero = $anio . '-' . str_pad($numero, 4, '0', STR_PAD_LEFT);
            $existe = DB::table('libro_diario')->where('numero', $nuevoNumero)->exists();
            if (!$existe) return $nuevoNumero;
            $intentos++;
        } while ($intentos < $maxIntentos);

        throw new \Exception("No se pudo generar un número de asiento único.");
    }

    /**
     * Obtiene datos para el gráfico de asientos. (Tu lógica original es óptima).
     */
    public function obtenerAsientosPorMes()
    {
        $resultado = DB::table('libro_diario')
            ->whereYear('fecha', now()->year)
            ->selectRaw('MONTH(fecha) as mes, COUNT(*) as cantidad, SUM(total_debe) as total')
            ->groupBy(DB::raw('MONTH(fecha)'))
            ->orderBy(DB::raw('MONTH(fecha)'), 'asc')
            ->get();
        // ... (el resto de tu lógica de formateo de meses es perfecta)
        $meses = []; $datos = [];
        for ($i = 1; $i <= 12; $i++) {
            $mesNombre = Carbon::create(now()->year, $i)->locale('es')->isoFormat('MMM');
            $meses[] = ucfirst($mesNombre);
            $asientoMes = $resultado->firstWhere('mes', $i);
            $datos[] = $asientoMes ? $asientoMes->cantidad : 0;
        }
        return ['labels' => $meses, 'data' => $datos];
    }

    /**
     * Obtiene el top 10 de movimientos por cuenta. (Tu lógica original es óptima).
     */
    public function obtenerMovimientosPorCuenta($fechaInicio = null, $fechaFin = null)
    {
        $query = DB::table('libro_diario_detalles as d')
            ->join('libro_diario as a', 'd.asiento_id', '=', 'a.id')
            ->join('plan_cuentas as c', 'd.cuenta_contable', '=', 'c.codigo');
        if ($fechaInicio && $fechaFin) {
            $query->whereBetween('a.fecha', [$fechaInicio, $fechaFin]);
        } else {
            $query->whereYear('a.fecha', now()->year);
        }
        return $query->select('c.codigo', 'c.nombre', DB::raw('SUM(d.debe + d.haber) as total_movimientos'))
            ->groupBy('c.codigo', 'c.nombre')
            ->orderBy('total_movimientos', 'desc')
            ->limit(10)
            ->get();
    }

    /**
     * Genera alertas contables. (Tu lógica original es óptima).
     */
    public function generarAlertasContables()
    {
        $alertas = [];
        $asientosSinBalancear = DB::table('libro_diario')->where('balanceado', false)->count();
        if ($asientosSinBalancear > 0) {
            $alertas[] = ['tipo' => 'warning', 'titulo' => 'Asientos sin Balancear', 'mensaje' => "{$asientosSinBalancear} asientos requieren revisión", 'icono' => 'exclamation-triangle'];
        }
        $asientosHoy = DB::table('libro_diario')->whereDate('fecha', today())->count();
        if ($asientosHoy == 0) {
            $alertas[] = ['tipo' => 'info', 'titulo' => 'Sin Asientos Hoy', 'mensaje' => 'No se han registrado asientos contables hoy', 'icono' => 'info-circle'];
        }
        return $alertas;
    }

    /**
     * Genera la descarga de PDF. (Tu lógica original es correcta).
     */
    public function generarPDF($asientos, $totales, $fechaInicio, $fechaFin)
    {
        $asientosCollection = $asientos instanceof \Illuminate\Support\Collection ? $asientos : collect($asientos);
        $filename = 'libro_diario_' . ($fechaInicio ?? 'inicio') . '_a_'."_".$fechaFin ?? 'fin' . '.pdf';
        
        return DomPDF::loadView('contabilidad.libros.diario.export_pdf', compact('asientosCollection', 'totales', 'fechaInicio', 'fechaFin'))
                    ->setPaper('a4', 'landscape')
                    ->download($filename);
    }

    /**
     * Genera la descarga de Excel. (Corregido para usar tu fallback de CSV).
     */
    public function generarExcel($asientos, $totales, $fechaInicio, $fechaFin)
    {
        // ---
        // ¡ACTUALIZADO!
        // Ahora usamos la nueva clase de exportación profesional
        // en lugar del fallback de CSV.
        // ---
        $asientosCollection = $asientos instanceof \Illuminate\Support\Collection ? $asientos : collect($asientos);
        $fechaInicioSafe = $fechaInicio ?? 'inicio';
        $fechaFinSafe = $fechaFin ?? 'fin';
        $filename = "Libro_Diario_{$fechaInicioSafe}_a_{$fechaFinSafe}.xlsx";

        // ¡Usamos la nueva clase!
        return Excel::download(
            new LibroDiarioExport($asientosCollection, $totales, $fechaInicioSafe, $fechaFinSafe), 
            $filename
        );
    }
}