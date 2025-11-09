<?php

namespace App\Services\Contabilidad;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\LibroDiario; // <-- ¡¡IMPORTADO PARA USAR ELOQUENT!!

class LibroDiarioService
{
    // ===================================================================
    // MÉTODOS DE ESCRITURA (C-U-D) - ¡REFRACTORIZADOS CON ELOQUENT!
    // ===================================================================

    /**
     * Almacena un nuevo asiento contable.
     * ¡¡REFACTORIZADO CON ELOQUENT!!
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

            // ▼▼▼ CAMBIO DE DB::table A ELOQUENT ▼▼▼
            // Al llamar a "create", el Observador se disparará.
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
                // created_at y updated_at son automáticos con Eloquent
            ]);
            // ▲▲▲ FIN DEL CAMBIO ▲▲▲

            $detallesParaInsertar = [];
            foreach ($validatedData['detalles'] as $detalle) {
                $detallesParaInsertar[] = [
                    'cuenta_contable' => $detalle['cuenta_contable'],
                    'debe' => $detalle['debe'] ?? 0,
                    'haber' => $detalle['haber'] ?? 0,
                    'concepto' => $detalle['concepto'],
                    'documento_referencia' => $detalle['documento_referencia'] ?? null,
                    // created_at y updated_at son automáticos
                ];
            }
            
            // Usamos la relación (definida en el Modelo) para crear los detalles
            $asiento->detalles()->createMany($detallesParaInsertar);

            DB::commit();

            Log::info('Asiento contable creado', [
                'numero' => $numeroAsiento, 'total' => $totalDebe, 'usuario_id' => $userId
            ]);

            return $asiento->id; // Devolvemos el ID del asiento creado

        } catch (\Exception $e) {
            DB::rollBack();
            // Re-lanzamos la excepción para que el controlador la atrape
            throw new \Exception('Error al registrar el asiento en el servicio: ' . $e->getMessage());
        }
    }

    /**
     * Actualiza la cabecera de un asiento.
     * ¡¡REFACTORIZADO CON ELOQUENT!!
     */
    public function updateAsiento($id, array $validatedData)
    {
        // ▼▼▼ CAMBIO DE DB::table A ELOQUENT ▼▼▼
        // 1. Buscamos el asiento usando el Modelo
        $asiento = LibroDiario::findOrFail($id);
        
        // 2. Al llamar a "update" en el modelo, el Observador se disparará.
        $asiento->update([
            'fecha' => $validatedData['fecha'],
            'glosa' => $validatedData['glosa'],
            'observaciones' => $validatedData['observaciones'],
            // updated_at es automático
        ]);
        // ▲▲▲ FIN DEL CAMBIO ▲▲▲
    }

    /**
     * Elimina un asiento y sus detalles.
     * ¡¡REFACTORIZADO CON ELOQUENT!!
     */
    public function deleteAsiento($id, $usuario)
    {
        DB::beginTransaction();
        try {
            // ▼▼▼ CAMBIO DE DB::table A ELOQUENT ▼▼▼
            // 1. Buscamos el asiento usando el Modelo
            $asiento = LibroDiario::findOrFail($id);
            if (!$asiento) {
                throw new \Exception('Asiento no encontrado');
            }
            
            // 2. Eliminamos los detalles primero (buena práctica, aunque CASCADE lo haría)
            $asiento->detalles()->delete();
            
            // 3. Eliminamos el asiento (esto disparará el Observador "deleted")
            $asiento->delete();
            // ▲▲▲ FIN DEL CAMBIO ▲▲▲
            
            DB::commit();
            
            Log::info('Asiento contable eliminado', [
                'numero' => $asiento->numero, 'total' => $asiento->total_debe, 'usuario' => $usuario
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Error al eliminar el asiento: ' . $e->getMessage());
        }
    }


    // ===================================================================
    // MÉTODOS DE LECTURA (R) - SIN CAMBIOS
    // (Estos métodos no necesitan disparar el Observador,
    // por lo que DB::table() es rápido y está perfecto)
    // ===================================================================

    /**
     * Obtiene los datos para el dashboard del Libro Diario.
     */
    public function getDashboardData(Request $request)
    {
        $fechaInicio = $request->get('fecha_inicio') ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $fechaFin = $request->get('fecha_fin') ?? Carbon::now()->endOfMonth()->format('Y-m-d');
        $numeroAsiento = $request->get('numero_asiento');
        $cuentaContable = $request->get('cuenta_contable');
        
        $asientos = $this->obtenerAsientos($fechaInicio, $fechaFin, $numeroAsiento, $cuentaContable);
        $totales = $this->calcularTotales($fechaInicio, $fechaFin);
        $cuentasContables = $this->obtenerCuentasContables();
        $graficoAsientosPorMes = $this->obtenerAsientosPorMes();
        $graficoMovimientosPorCuenta = $this->obtenerMovimientosPorCuenta();
        $alertas = $this->generarAlertasContables();
        
        return compact(
            'asientos', 
            'totales', 
            'cuentasContables',
            'fechaInicio',
            'fechaFin',
            'graficoAsientosPorMes',
            'graficoMovimientosPorCuenta',
            'alertas'
        );
    }

    /**
     * Obtiene los datos para el formulario de creación.
     */
    public function getCreateFormData()
    {
        $cuentasContables = DB::table('plan_cuentas')
            ->where('activo', 1)
            ->orderBy('codigo')
            ->get(['codigo', 'nombre']);
            
        $ultimosAsientos = $this->obtenerUltimosAsientos(5);
        
        return compact('cuentasContables', 'ultimosAsientos');
    }

    /**
     * Obtiene los detalles de un asiento para mostrarlo.
     */
    public function getAsientoDetails($id)
    {
        $query = DB::table('libro_diario as a');
        if (Schema::hasTable('accesoweb')) {
            $query->leftJoin('accesoweb as u', 'a.usuario_id', '=', 'u.idusuario')
                  ->select('a.*', DB::raw('u.usuario as usuario_nombre'), DB::raw('u.tipousuario as usuario_tipo'));
        } else {
            $query->select('a.*', DB::raw('NULL as usuario_nombre'), DB::raw('NULL as usuario_tipo'));
        }
        $asiento = $query->where('a.id', $id)->first();
                
        if (!$asiento) {
            return null; // El controlador manejará el not found
        }
        
        $detalles = DB::table('libro_diario_detalles as d')
            ->leftJoin('plan_cuentas as c', 'd.cuenta_contable', '=', 'c.codigo')
            ->select('d.*', 'c.nombre as cuenta_nombre', 'c.tipo as cuenta_tipo')
            ->where('d.asiento_id', $id)
            ->orderBy('d.id')
            ->get();
        
        $asientoAnterior = DB::table('libro_diario')
            ->where('fecha', '<=', $asiento->fecha)->where('id', '!=', $id)
            ->orderBy('fecha', 'desc')->orderBy('numero', 'desc')->first();
            
        $asientoSiguiente = DB::table('libro_diario')
            ->where('fecha', '>=', $asiento->fecha)->where('id', '!=', $id)
            ->orderBy('fecha', 'asc')->orderBy('numero', 'asc')->first();
        
        return compact('asiento', 'detalles', 'asientoAnterior', 'asientoSiguiente');
    }

    /**
     * Obtiene los datos para el formulario de edición.
     */
    public function getEditFormData($id)
    {
        $asiento = DB::table('libro_diario')->where('id', $id)->first();
        if (!$asiento) {
            return ['asiento' => null, 'detalles' => collect(), 'cuentasContables' => collect()];
        }

        $detalles = DB::table('libro_diario_detalles')->where('asiento_id', $id)->get();
        $cuentasContables = $this->obtenerCuentasContablesParaFormulario();

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

        // Obtenemos los asientos SIN paginar
        $asientos = $this->obtenerAsientos($fechaInicio, $fechaFin, null, null, false);
        
        $totales = $this->calcularTotales($fechaInicio, $fechaFin);
        
        if ($formato === 'pdf') {
            return $this->generarPDF($asientos, $totales, $fechaInicio, $fechaFin);
        } else {
            return $this->generarExcel($asientos, $totales, $fechaInicio, $fechaFin);
        }
    }


    // ===================================================================
    // MÉTODOS DE AYUDA (Sin Cambios)
    // ===================================================================

    public function obtenerAsientos($fechaInicio = null, $fechaFin = null, $numeroAsiento = null, $cuentaContable = null, $paginar = true)
    {
        $query = DB::table('libro_diario as a')->select('a.*');

        if (Schema::hasTable('accesoweb')) {
            $query->leftJoin('accesoweb as u', 'a.usuario_id', '=', 'u.idusuario')
                  ->addSelect(DB::raw('u.usuario as usuario_nombre'));
        } else {
            $query->addSelect(DB::raw('NULL as usuario_nombre'));
        }

        if ($fechaInicio && $fechaFin) {
            $query->whereBetween('a.fecha', [$fechaInicio, $fechaFin]);
        }
        
        if ($numeroAsiento) {
            $query->where('a.numero', 'like', '%' . $numeroAsiento . '%');
        }
        
        if ($cuentaContable) {
            $query->whereExists(function($q) use ($cuentaContable) {
                $q->select(DB::raw(1))
                  ->from('libro_diario_detalles as d')
                  ->whereRaw('d.asiento_id = a.id')
                  ->where('d.cuenta_contable', 'like', '%' . $cuentaContable . '%');
            });
        }
        
        $query->orderBy('a.fecha', 'desc')
              ->orderBy('a.numero', 'desc');
              
        return $paginar ? $query->paginate(20) : $query->get();
    }

    public function calcularTotales($fechaInicio = null, $fechaFin = null)
    {
        $query = DB::table('libro_diario');
        if ($fechaInicio && $fechaFin) {
            $query->whereBetween('fecha', [$fechaInicio, $fechaFin]);
        }
        $resultado = $query->selectRaw('
                    COUNT(*) as total_asientos,
                    SUM(total_debe) as total_debe,
                    SUM(total_haber) as total_haber,
                    AVG(total_debe) as promedio_asiento
                ')->first();
                
        return [
            'total_asientos' => $resultado->total_asientos ?? 0,
            'total_debe' => round($resultado->total_debe ?? 0, 2),
            'total_haber' => round($resultado->total_haber ?? 0, 2),
            'promedio_asiento' => round($resultado->promedio_asiento ?? 0, 2),
            'balance' => round(($resultado->total_debe ?? 0) - ($resultado->total_haber ?? 0), 2)
        ];
    }

    public function obtenerCuentasContables()
    {
        return DB::table('plan_cuentas')
            ->where('activo', 1)
            ->orderBy('codigo')
            ->get();
    }

    public function obtenerCuentasContablesParaFormulario()
    {
        return DB::table('plan_cuentas')
            ->where('activo', 1)
            ->whereIn('tipo', ['ACTIVO', 'PASIVO', 'PATRIMONIO', 'INGRESO', 'GASTO'])
            ->orderBy('codigo')
            ->get()
            ->groupBy('tipo');
    }

    /**
     * Esta es la función correcta para generar el número de asiento.
     */
    public function obtenerSiguienteNumeroAsiento()
    {
        $anio = now()->format('Y');
        $intentos = 0;
        $maxIntentos = 10;

        do {
            // 1. Busca el número MÁXIMO actual
            $ultimoNumero = DB::table('libro_diario')
                ->where('numero', 'like', $anio . '-%') // Aseguramos el formato AAAA-...
                ->max('numero');

            if (!$ultimoNumero) {
                $numero = 1;
            } else {
                // 2. Extrae la secuencia (ej: de "2025-0123" extrae "0123")
                $secuencia = (int) substr($ultimoNumero, 5); // Cambiado de 4 a 5 por el guion
                $numero = $secuencia + 1;
            }

            // 3. Formatea el nuevo número
            $nuevoNumero = $anio . '-' . str_pad($numero, 4, '0', STR_PAD_LEFT); // Añadido el guion

            // 4. Verifica si existe (para concurrencia)
            $existe = DB::table('libro_diario')->where('numero', $nuevoNumero)->exists();

            if (!$existe) {
                return $nuevoNumero;
            }
            $intentos++;
        } while ($intentos < $maxIntentos);

        throw new \Exception("No se pudo generar un número de asiento único después de $maxIntentos intentos.");
    }


    public function obtenerUltimosAsientos($cantidad = 5)
    {
        return DB::table('libro_diario')
            ->orderBy('fecha', 'desc')
            ->orderBy('numero', 'desc')
            ->limit($cantidad)
            ->get();
    }

    public function obtenerAsientosPorMes()
    {
        $resultado = DB::table('libro_diario')
            ->whereYear('fecha', now()->year)
            ->selectRaw('
                MONTH(fecha) as mes,
                COUNT(*) as cantidad,
                SUM(total_debe) as total
            ')
            ->groupBy(DB::raw('MONTH(fecha)'))
            ->orderBy(DB::raw('MONTH(fecha)'), 'asc')
            ->get();

        $meses = [];
        $datos = [];

        for ($i = 1; $i <= 12; $i++) {
            $mesNombre = Carbon::create(now()->year, $i)->locale('es')->isoFormat('MMM');
            $meses[] = ucfirst($mesNombre);
            $asientoMes = $resultado->firstWhere('mes', $i);
            $datos[] = $asientoMes ? $asientoMes->cantidad : 0;
        }
        return ['labels' => $meses, 'data' => $datos];
    }

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

        return $query
            ->select(
                'c.codigo', 'c.nombre',
                DB::raw('SUM(d.debe + d.haber) as total_movimientos')
            )
            ->groupBy('c.codigo', 'c.nombre')
            ->orderBy('total_movimientos', 'desc')
            ->limit(10)
            ->get();
    }


    public function generarAlertasContables()
    {
        $alertas = [];
        $asientosSinBalancear = DB::table('libro_diario')
            ->where('balanceado', false)
            ->count();
            
        if ($asientosSinBalancear > 0) {
            $alertas[] = [
                'tipo' => 'warning', 'titulo' => 'Asientos sin Balancear',
                'mensaje' => "{$asientosSinBalancear} asientos requieren revisión",
                'icono' => 'exclamation-triangle'
            ];
        }
        
        $asientosHoy = DB::table('libro_diario')->whereDate('fecha', today())->count();
        if ($asientosHoy == 0) {
            $alertas[] = [
                'tipo' => 'info', 'titulo' => 'Sin Asientos Hoy',
                'mensaje' => 'No se han registrado asientos contables hoy',
                'icono' => 'info-circle'
            ];
        }
        return $alertas;
    }

    public function generarPDF($asientos, $totales, $fechaInicio, $fechaFin)
    {
        $asientosCollection = $asientos instanceof \Illuminate\Support\Collection ? $asientos : collect($asientos);
        $filename = 'libro_diario_' . ($fechaInicio ?? 'inicio') . '_a_' . ($fechaFin ?? 'fin') . '.pdf';
        
        // Asumiendo que usas 'barryvdh/laravel-dompdf'
        $dompdfFacade = 'Barryvdh\\DomPDF\\Facade\\Pdf';
        if (class_exists($dompdfFacade)) {
            return $dompdfFacade::loadView('contabilidad.libros.diario.export_pdf', compact('asientosCollection', 'totales', 'fechaInicio', 'fechaFin'))
                                ->setPaper('a4', 'landscape')
                                ->download($filename);
        }

        // Fallback
        throw new \Exception('El generador de PDF (DomPDF) no está instalado.');
    }

    public function generarExcel($asientos, $totales, $fechaInicio, $fechaFin)
    {
        $asientosCollection = $asientos instanceof \Illuminate\Support\Collection ? $asientos : collect($asientos);


        $fechaInicioSafe = $fechaInicio ?? 'inicio';
        $fechaFinSafe = $fechaFin ?? 'fin';
        $filenameCsv = "libro_diario_{$fechaInicioSafe}_a_{$fechaFinSafe}.csv";

        // Fallback a CSV, que es más simple y no requiere paquetes
        $callback = function() use ($asientosCollection) {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Número', 'Fecha', 'Glosa', 'Total Debe', 'Total Haber', 'Balanceado', 'Usuario ID', 'Observaciones']);

            foreach ($asientosCollection as $a) {
                // ▼▼▼ ¡¡AQUÍ ESTÁ LA CORRECCIÓN DEL ERROR DE EXPORTACIÓN!! ▼▼▼
                fputcsv($output, [
                    (string)($a->numero ?? ''), (string)(isset($a->fecha) ? Carbon::parse($a->fecha)->format('Y-m-d') : ''),
                    (string)($a->glosa ?? ''), (float)($a->total_debe ?? 0), (float)($a->total_haber ?? 0),
                    ($a->balanceado ?? '') ? 'SI' : 'NO', (string)($a->usuario_id ?? ''), (string)($a->observaciones ?? ''),
                ]);
                // ▲▲▲ FIN DE LA CORRECCIÓN ▲▲▲
            }
            fclose($output);
        };
        return response()->streamDownload($callback, $filenameCsv, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filenameCsv\""
        ]);
    }
}