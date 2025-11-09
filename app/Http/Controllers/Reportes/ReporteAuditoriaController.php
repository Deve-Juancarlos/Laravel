<?php

namespace App\Http\Controllers\Reportes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReporteAuditoriaController extends Controller
{
    /**
     * Muestra el reporte de auditoría AVANZADO
     * basado en la tabla [libro_diario_auditoria]
     */
    public function libroDiario(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'nullable|date',
            'fecha_fin'    => 'nullable|date|after_or_equal:fecha_inicio',
            'usuario'      => 'nullable|string',
            'asiento_id'   => 'nullable|integer',
            'accion'       => 'nullable|string',
        ]);

        $query = DB::table('libro_diario_auditoria as aud')
            ->join('libro_diario as ld', 'aud.asiento_id', '=', 'ld.id')
            ->select(
                'aud.id',
                'aud.fecha_hora',
                'aud.usuario',
                'aud.accion',
                'aud.asiento_id',
                'ld.numero as AsientoNumero',
                'aud.ip_address',
                'aud.datos_anteriores', // ¡Importante!
                'aud.datos_nuevos'      // ¡Importante!
            );

        // --- Aplicar Filtros ---
        if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
            $query->whereBetween(DB::raw('CAST(aud.fecha_hora AS DATE)'), [
                $request->input('fecha_inicio'),
                $request->input('fecha_fin')
            ]);
        }
        if ($request->filled('usuario')) {
            $query->where('aud.usuario', 'LIKE', '%' . $request->input('usuario') . '%');
        }
        if ($request->filled('asiento_id')) {
            $query->where('aud.asiento_id', $request->input('asiento_id'));
        }
        if ($request->filled('accion')) {
            $query->where('aud.accion', $request->input('accion'));
        }

        $reporte = $query->orderBy('aud.fecha_hora', 'desc')
                         ->paginate(50)
                         ->appends($request->query());

        // ▼▼▼ LÓGICA PARA PROCESAR LOS CAMBIOS ▼▼▼
        // Procesamos la colección paginada para crear un log de cambios legible
        $reporte->through(function ($log) {
            
            $log->cambios = []; // Creamos una nueva propiedad 'cambios'
            
            $anteriores = (array) json_decode($log->datos_anteriores);
            $nuevos     = (array) json_decode($log->datos_nuevos);

            // 1. Si fue una MODIFICACIÓN, comparamos campos
            if ($log->accion === 'MODIFICAR') {
                foreach ($nuevos as $campo => $valorNuevo) {
                    $valorAnterior = $anteriores[$campo] ?? null;
                    
                    // Comparamos los valores
                    if ($valorAnterior != $valorNuevo) {
                        $log->cambios[] = [
                            'campo'   => $campo,
                            'antes'   => $valorAnterior,
                            'despues' => $valorNuevo
                        ];
                    }
                }
            } 
            // 2. Si fue una CREACIÓN, mostramos todos los campos nuevos
            elseif ($log->accion === 'CREAR') {
                foreach ($nuevos as $campo => $valorNuevo) {
                    $log->cambios[] = [
                        'campo'   => $campo,
                        'antes'   => '<i class="text-muted">N/A</i>', // No había nada antes
                        'despues' => $valorNuevo
                    ];
                }
            }
            // 3. Si fue una ELIMINACIÓN, mostramos los campos que se borraron
            elseif ($log->accion === 'ELIMINAR') {
                foreach ($anteriores as $campo => $valorAnterior) {
                    $log->cambios[] = [
                        'campo'   => $campo,
                        'antes'   => $valorAnterior,
                        'despues' => '<i class="text-muted">ELIMINADO</i>' // No hay nada después
                    ];
                }
            }
            
            return $log; // Devolvemos el $log modificado
        });
        // ▲▲▲ FIN DE LA LÓGICA DE CAMBIOS ▲▲▲

        return view('reportes.auditoria.libro_diario', [
            'reporte' => $reporte,
            'filters' => $request->all() // Para "recordar" los filtros en la vista
        ]);
    }

    /**
     * Muestra el reporte de auditoría GENERAL
     * basado en la tabla [Auditoria_Sistema]
     */
    public function sistemaGeneral(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'nullable|date',
            'fecha_fin'    => 'nullable|date|after_or_equal:fecha_inicio',
            'usuario'      => 'nullable|string',
            'tabla'        => 'nullable|string',
        ]);

        $query = DB::table('Auditoria_Sistema as aud')
            ->select('aud.id', 'aud.fecha', 'aud.usuario', 'aud.accion', 'aud.tabla', 'aud.detalle');

        // --- Aplicar Filtros ---
        if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
            $query->whereBetween(DB::raw('CAST(aud.fecha AS DATE)'), [
                $request->input('fecha_inicio'),
                $request->input('fecha_fin')
            ]);
        }
        if ($request->filled('usuario')) {
            $query->where('aud.usuario', 'LIKE', '%' . $request->input('usuario') . '%');
        }
        if ($request->filled('tabla')) {
            $query->where('aud.tabla', $request->input('tabla'));
        }

        $reporte = $query->orderBy('aud.fecha', 'desc')
                         ->paginate(50)
                         ->appends($request->query());

        // Obtener la lista de tablas para el dropdown del filtro
        $tablasDisponibles = DB::table('Auditoria_Sistema')
                               ->select('tabla')
                               ->distinct()
                               ->orderBy('tabla', 'asc')
                               ->pluck('tabla');

        return view('reportes.auditoria.sistema_general', [
            'reporte' => $reporte,
            'filters' => $request->all(),
            'tablasDisponibles' => $tablasDisponibles
        ]);
    }
}