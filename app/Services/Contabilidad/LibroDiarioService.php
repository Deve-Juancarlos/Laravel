<?php

namespace App\Services\Contabilidad;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\LibroDiario;
use App\Models\LibroDiarioDetalle;
use App\Models\PlanCuentas;
use App\Models\AccesoWeb;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LibroDiarioExport;
use Barryvdh\DomPDF\Facade\Pdf as DomPDF;

class LibroDiarioService
{
    /**
     * ================================================================
     * CREAR ASIENTO (CON AUDITORÍA + NOTIFICACIÓN)
     * ================================================================
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

            // ✅ REGISTRAR EN AUDITORÍA
            $this->registrarAuditoria(
                $asiento->id,
                'CREAR',
                null,
                [
                    'numero' => $numeroAsiento,
                    'fecha' => $validatedData['fecha'],
                    'glosa' => $validatedData['glosa'],
                    'total_debe' => $totalDebe,
                    'total_haber' => $totalHaber,
                    'estado' => 'ACTIVO',
                    'observaciones' => $observaciones,
                    'cantidad_detalles' => count($validatedData['detalles']),
                ]
            );

            // ✅ NOTIFICAR A SUPERVISORES (si el monto es alto)
            if ($totalDebe >= 10000) {
                $this->notificarAsientoAlto($asiento, $userId);
            }

            DB::commit();
            Log::info('Asiento contable creado', ['numero' => $numeroAsiento, 'usuario_id' => $userId]);
            return $asiento->id;

        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Error al registrar el asiento: ' . $e->getMessage());
        }
    }

    /**
     * ================================================================
     * ACTUALIZAR ASIENTO (CON AUDITORÍA + NOTIFICACIÓN)
     * ================================================================
     */
    public function updateAsiento($id, array $validatedData)
    {
        DB::beginTransaction();
        try {
            $asiento = LibroDiario::findOrFail($id);

            // ✅ GUARDAR DATOS ANTERIORES
            $datosAnteriores = [
                'numero' => $asiento->numero,
                'fecha' => $asiento->fecha,
                'glosa' => $asiento->glosa,
                'observaciones' => $asiento->observaciones,
                'total_debe' => $asiento->total_debe,
                'total_haber' => $asiento->total_haber,
                'estado' => $asiento->estado,
            ];

            // Actualizar asiento
            $asiento->update($validatedData);

            // ✅ REGISTRAR EN AUDITORÍA
            $this->registrarAuditoria(
                $id,
                'MODIFICAR',
                $datosAnteriores,
                [
                    'numero' => $asiento->numero,
                    'fecha' => $validatedData['fecha'],
                    'glosa' => $validatedData['glosa'],
                    'observaciones' => $validatedData['observaciones'] ?? null,
                    'total_debe' => $asiento->total_debe,
                    'total_haber' => $asiento->total_haber,
                    'estado' => $asiento->estado,
                ]
            );

            // ✅ NOTIFICAR CAMBIOS IMPORTANTES
            $this->notificarModificacionAsiento($asiento, $datosAnteriores);

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * ================================================================
     * SOLICITAR ELIMINACIÓN (CON AUDITORÍA + NOTIFICACIÓN)
     * ================================================================
     */
    public function solicitarEliminacion($id, $usuario)
    {
        DB::beginTransaction();
        try {
            $asiento = LibroDiario::findOrFail($id);

            // ✅ GUARDAR DATOS ANTERIORES
            $datosAnteriores = [
                'numero' => $asiento->numero,
                'fecha' => $asiento->fecha,
                'glosa' => $asiento->glosa,
                'estado' => $asiento->estado,
                'total_debe' => $asiento->total_debe,
                'total_haber' => $asiento->total_haber,
            ];

            $asiento->estado = 'PENDIENTE_ELIMINACION';
            $asiento->save();

            // ✅ REGISTRAR EN AUDITORÍA
            $this->registrarAuditoria(
                $id,
                'SOLICITAR_ELIMINACION',
                $datosAnteriores,
                [
                    'numero' => $asiento->numero,
                    'fecha' => $asiento->fecha,
                    'glosa' => $asiento->glosa,
                    'estado' => 'PENDIENTE_ELIMINACION',
                    'solicitante' => $usuario->usuario,
                ]
            );

            // ✅ NOTIFICAR A ADMINISTRADORES
            $this->notificarSolicitudEliminacion($asiento, $usuario);

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * ================================================================
     * ELIMINAR ASIENTO (CON AUDITORÍA + NOTIFICACIÓN)
     * ================================================================
     */
    public function deleteAsiento($id, $usuario)
    {
        DB::beginTransaction();
        try {
            $asiento = LibroDiario::findOrFail($id);
            
            // ✅ GUARDAR DATOS PARA AUDITORÍA
            $datosAnteriores = [
                'numero' => $asiento->numero,
                'fecha' => $asiento->fecha,
                'glosa' => $asiento->glosa,
                'estado' => $asiento->estado,
                'total_debe' => $asiento->total_debe,
                'total_haber' => $asiento->total_haber,
            ];

            // ✅ REGISTRAR EN AUDITORÍA
            $this->registrarAuditoria(
                $id,
                'ELIMINAR',
                $datosAnteriores,
                [
                    'numero' => $asiento->numero,
                    'glosa' => '[ELIMINADO] ' . $asiento->glosa,
                    'estado' => 'ELIMINADO',
                    'eliminado_por' => $usuario,
                ]
            );

            // ✅ NOTIFICAR ELIMINACIÓN
            $this->notificarEliminacionAsiento($asiento, $usuario);

            // Ejecutar eliminación
            $asiento->delete();

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar asiento: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * ================================================================
     * ✅ REGISTRAR AUDITORÍA
     * ================================================================
     */
    private function registrarAuditoria($asientoId, $accion, $datosAnteriores = null, $datosNuevos = null)
    {
        try {
            DB::table('libro_diario_auditoria')->insert([
                'asiento_id' => $asientoId,
                'accion' => $accion,
                'datos_anteriores' => $datosAnteriores ? json_encode($datosAnteriores, JSON_UNESCAPED_UNICODE) : null,
                'datos_nuevos' => $datosNuevos ? json_encode($datosNuevos, JSON_UNESCAPED_UNICODE) : null,
                'usuario' => Auth::user()->usuario ?? 'Sistema',
                'fecha_hora' => now(),
                'ip_address' => request()->ip(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error al registrar auditoría: ' . $e->getMessage());
        }
    }

    /**
     * ================================================================
     * ✅ SISTEMA DE NOTIFICACIONES
     * ================================================================
     */

    /**
     * Notifica cuando se crea un asiento de monto alto
     */
    private function notificarAsientoAlto($asiento, $usuarioId)
    {
        try {
            // Obtener administradores y supervisores
            $destinatarios = DB::table('accesoweb')
                ->where('nivel', 1) // Administradores
                ->pluck('idusuario');

            foreach ($destinatarios as $destinatarioId) {
                DB::table('notificaciones')->insert([
                    'usuario_id' => $destinatarioId,
                    'tipo' => 'ASIENTO_MONTO_ALTO',
                    'titulo' => 'Asiento de Monto Elevado Registrado',
                    'mensaje' => "Se ha registrado el asiento {$asiento->numero} con un monto de S/ " . number_format($asiento->total_debe, 2),
                    'icono' => 'exclamation-triangle',
                    'color' => 'warning',
                    'url' => route('contador.libro-diario.show', $asiento->id),
                    'leida' => 0,
                    'metadata' => json_encode([
                        'asiento_id' => $asiento->id,
                        'numero' => $asiento->numero,
                        'monto' => $asiento->total_debe,
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error al notificar asiento alto: ' . $e->getMessage());
        }
    }

    /**
     * Notifica cuando se modifica un asiento
     */
    private function notificarModificacionAsiento($asiento, $datosAnteriores)
    {
        try {
            // Solo notificar si hubo cambios significativos
            if ($datosAnteriores['glosa'] !== $asiento->glosa || 
                $datosAnteriores['fecha'] !== $asiento->fecha) {

                // Obtener supervisores
                $destinatarios = DB::table('accesoweb')
                    ->where('nivel', 1)
                    ->pluck('idusuario');

                foreach ($destinatarios as $destinatarioId) {
                    DB::table('notificaciones')->insert([
                        'usuario_id' => $destinatarioId,
                        'tipo' => 'ASIENTO_MODIFICADO',
                        'titulo' => 'Asiento Contable Modificado',
                        'mensaje' => "El asiento {$asiento->numero} ha sido modificado por " . (Auth::user()->usuario ?? 'Sistema'),
                        'icono' => 'edit',
                        'color' => 'info',
                        'url' => route('contador.libro-diario.show', $asiento->id),
                        'leida' => 0,
                        'metadata' => json_encode([
                            'asiento_id' => $asiento->id,
                            'numero' => $asiento->numero,
                            'cambios' => [
                                'glosa_anterior' => $datosAnteriores['glosa'],
                                'glosa_nueva' => $asiento->glosa,
                            ],
                        ]),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error al notificar modificación: ' . $e->getMessage());
        }
    }

    /**
     * Notifica solicitud de eliminación a administradores
     */
    private function notificarSolicitudEliminacion($asiento, $usuario)
    {
        try {
            // Obtener administradores
            $administradores = DB::table('accesoweb')
                ->where('nivel', 1)
                ->pluck('idusuario');

            foreach ($administradores as $adminId) {
                DB::table('notificaciones')->insert([
                    'usuario_id' => $adminId,
                    'tipo' => 'SOLICITUD_ELIMINACION',
                    'titulo' => 'Solicitud de Eliminación de Asiento',
                    'mensaje' => "El usuario {$usuario->usuario} solicita eliminar el asiento {$asiento->numero}",
                    'icono' => 'trash',
                    'color' => 'danger',
                    'url' => route('admin.solicitudes.asiento.index'),
                    'leida' => 0,
                    'metadata' => json_encode([
                        'asiento_id' => $asiento->id,
                        'numero' => $asiento->numero,
                        'solicitante_id' => $usuario->idusuario,
                        'solicitante' => $usuario->usuario,
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error al notificar solicitud de eliminación: ' . $e->getMessage());
        }
    }

    /**
     * Notifica cuando se elimina un asiento
     */
    private function notificarEliminacionAsiento($asiento, $usuario)
    {
        try {
            // Notificar al creador del asiento (si existe)
            if ($asiento->usuario_id) {
                DB::table('notificaciones')->insert([
                    'usuario_id' => $asiento->usuario_id,
                    'tipo' => 'ASIENTO_ELIMINADO',
                    'titulo' => 'Asiento Eliminado',
                    'mensaje' => "El asiento {$asiento->numero} ha sido eliminado por un administrador",
                    'icono' => 'trash',
                    'color' => 'danger',
                    'url' => null,
                    'leida' => 0,
                    'metadata' => json_encode([
                        'numero' => $asiento->numero,
                        'glosa' => $asiento->glosa,
                        'eliminado_por' => $usuario,
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error al notificar eliminación: ' . $e->getMessage());
        }
    }

    // ===================================================================
    // MÉTODOS DE LECTURA (OPTIMIZADOS CON ELOQUENT)
    // ===================================================================

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
        $cuentasContables = $this->obtenerCuentasContables();
        $graficoAsientosPorMes = $this->obtenerAsientosPorMes();
        $graficoMovimientosPorCuenta = $this->obtenerMovimientosPorCuenta($fechaInicio, $fechaFin);
        $alertas = $this->generarAlertasContables();
        
        return compact(
            'asientos', 'totales', 'cuentasContables', 'fechaInicio',
            'fechaFin', 'graficoAsientosPorMes', 'graficoMovimientosPorCuenta', 'alertas'
        );
    }

    public function getCreateFormData()
    {
        $cuentasContables = PlanCuentas::where('activo', 1)
            ->orderBy('codigo')
            ->get(['codigo', 'nombre']);
            
        $ultimosAsientos = $this->obtenerUltimosAsientos(5);
        
        return compact('cuentasContables', 'ultimosAsientos');
    }

    public function getAsientoDetails($id)
    {
        $asiento = LibroDiario::with(['usuario', 'detalles.cuenta'])->find($id);
            
        if (!$asiento) {
            return null;
        }
        
        $detalles = $asiento->detalles;
        
        $asientoAnterior = LibroDiario::where('fecha', '<=', $asiento->fecha)
            ->where('id', '!=', $id)
            ->orderBy('fecha', 'desc')->orderBy('numero', 'desc')->first();
            
        $asientoSiguiente = LibroDiario::where('fecha', '>=', $asiento->fecha)
            ->where('id', '!=', $id)
            ->orderBy('fecha', 'asc')->orderBy('numero', 'asc')->first();
        
        return compact('asiento', 'detalles', 'asientoAnterior', 'asientoSiguiente');
    }

    public function getEditFormData($id)
    {
        $asiento = LibroDiario::find($id);
        if (!$asiento) {
            return ['asiento' => null, 'detalles' => collect(), 'cuentasContables' => collect()];
        }

        $detalles = $asiento->detalles;
        $cuentasContables = $this->obtenerCuentasContablesParaFormulario();

        return compact('asiento', 'detalles', 'cuentasContables');
    }

    public function export(Request $request)
    {
        $formato = $request->get('formato', 'excel');
        $fechaInicio = $request->get('fecha_inicio');
        $fechaFin = $request->get('fecha_fin');

        $asientos = $this->obtenerAsientos($fechaInicio, $fechaFin, null, null, false);
        $totales = $this->calcularTotales($fechaInicio, $fechaFin);
        
        if ($formato === 'pdf') {
            return $this->generarPDF($asientos, $totales, $fechaInicio, $fechaFin);
        } else {
            return $this->generarExcel($asientos, $totales, $fechaInicio, $fechaFin);
        }
    }

    // ===================================================================
    // MÉTODOS DE AYUDA
    // ===================================================================

    public function obtenerAsientos($fechaInicio = null, $fechaFin = null, $numeroAsiento = null, $cuentaContable = null, $paginar = true)
    {
        $query = LibroDiario::with(['usuario', 'detalles.cuenta']) 
            ->when($fechaInicio, function ($q) use ($fechaInicio, $fechaFin) {
                $q->whereBetween('fecha', [$fechaInicio, $fechaFin]);
            })
            ->when($numeroAsiento, function ($q) use ($numeroAsiento) {
                $q->where('numero', 'like', '%' . $numeroAsiento . '%');
            })
            ->when($cuentaContable, function ($q) use ($cuentaContable) {
                $q->whereHas('detalles', function ($detalleQuery) use ($cuentaContable) {
                    $detalleQuery->where('cuenta_contable', 'like', '%' . $cuentaContable . '%');
                });
            })
            ->orderBy('fecha', 'desc')
            ->orderBy('numero', 'desc');
            
        return $paginar ? $query->paginate(20) : $query->get();
    }

    public function obtenerCuentasContables()
    {
        return PlanCuentas::where('activo', 1)->orderBy('codigo')->get();
    }

    public function obtenerCuentasContablesParaFormulario()
    {
        return PlanCuentas::where('activo', 1)
            ->whereIn('tipo', ['ACTIVO', 'PASIVO', 'PATRIMONIO', 'INGRESO', 'GASTO'])
            ->orderBy('codigo')
            ->get()
            ->groupBy('tipo');
    }

    public function obtenerUltimosAsientos($cantidad = 5)
    {
        return LibroDiario::orderBy('fecha', 'desc')
            ->orderBy('numero', 'desc')
            ->limit($cantidad)
            ->get();
    }

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

    public function obtenerAsientosPorMes()
    {
        $resultado = DB::table('libro_diario')
            ->whereYear('fecha', now()->year)
            ->selectRaw('MONTH(fecha) as mes, COUNT(*) as cantidad, SUM(total_debe) as total')
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
        return $query->select('c.codigo', 'c.nombre', DB::raw('SUM(d.debe + d.haber) as total_movimientos'))
            ->groupBy('c.codigo', 'c.nombre')
            ->orderBy('total_movimientos', 'desc')
            ->limit(10)
            ->get();
    }

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

    public function generarPDF($asientos, $totales, $fechaInicio, $fechaFin)
    {
        $asientosCollection = $asientos instanceof \Illuminate\Support\Collection ? $asientos : collect($asientos);
        $filename = 'libro_diario_' . ($fechaInicio ?? 'inicio') . '_a_' . ($fechaFin ?? 'fin') . '.pdf';
        
        return DomPDF::loadView('contabilidad.libros.diario.export_pdf', compact('asientosCollection', 'totales', 'fechaInicio', 'fechaFin'))
                    ->setPaper('a4', 'landscape')
                    ->download($filename);
    }

    public function generarExcel($asientos, $totales, $fechaInicio, $fechaFin)
    {
        $asientosCollection = $asientos instanceof \Illuminate\Support\Collection ? $asientos : collect($asientos);
        $fechaInicioSafe = $fechaInicio ?? 'inicio';
        $fechaFinSafe = $fechaFin ?? 'fin';
        $filename = "Libro_Diario_{$fechaInicioSafe}_a_{$fechaFinSafe}.xlsx";

        return Excel::download(
            new LibroDiarioExport($asientosCollection, $totales, $fechaInicioSafe, $fechaFinSafe), 
            $filename
        );
    }
}
