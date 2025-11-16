<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\AuditoriaService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AuditoriaController extends Controller
{
    protected $auditoriaService;

    public function __construct(AuditoriaService $auditoriaService)
    {
        $this->auditoriaService = $auditoriaService;
    }

    /**
     * Dashboard principal de auditoría
     */
    public function index(Request $request)
    {
        $filtros = [
            'usuario' => $request->get('usuario'),
            'accion' => $request->get('accion'),
            'fecha_inicio' => $request->get('fecha_inicio'),
            'fecha_fin' => $request->get('fecha_fin'),
            'buscar' => $request->get('buscar'),
        ];

        $eventos = $this->auditoriaService->obtenerEventos($filtros);
        $estadisticas = $this->auditoriaService->obtenerEstadisticas();
        $usuarios = $this->auditoriaService->obtenerUsuariosConActividad();
        
        return view('admin.auditoria.index', compact('eventos', 'estadisticas', 'usuarios', 'filtros'));
    }

    /**
     * Ver detalle completo de un evento
     */
    public function detalle($id)
    {
        $evento = $this->auditoriaService->obtenerEvento($id);
        
        if (!$evento) {
            return redirect()->route('admin.auditoria.index')
                ->with('error', 'Evento no encontrado.');
        }
        
        return view('admin.auditoria.detalle', compact('evento'));
    }

    /**
     * Dashboard de estadísticas
     */
    public function estadisticas(Request $request)
    {
        $periodo = $request->get('periodo', 'mes'); // dia, semana, mes, anio
        
        $estadisticas = $this->auditoriaService->obtenerEstadisticasDetalladas($periodo);
        $accionesCriticas = $this->auditoriaService->obtenerAccionesCriticas();
        $usuariosMasActivos = $this->auditoriaService->obtenerUsuariosMasActivos();
        
        return view('admin.auditoria.estadisticas', compact(
            'estadisticas', 
            'accionesCriticas', 
            'usuariosMasActivos',
            'periodo'
        ));
    }

    /**
     * Timeline de actividad
     */
    public function timeline(Request $request)
    {
        $usuario = $request->get('usuario');
        $fecha = $request->get('fecha', Carbon::today()->toDateString());
        
        $timeline = $this->auditoriaService->obtenerTimeline($usuario, $fecha);
        
        return view('admin.auditoria.timeline', compact('timeline', 'usuario', 'fecha'));
    }

    /**
     * Exportar logs a Excel
     */
    public function exportar(Request $request)
    {
        $filtros = [
            'usuario' => $request->get('usuario'),
            'accion' => $request->get('accion'),
            'fecha_inicio' => $request->get('fecha_inicio'),
            'fecha_fin' => $request->get('fecha_fin'),
        ];
        
        return $this->auditoriaService->exportarAExcel($filtros);
    }

    /**
     * Ver eventos por usuario específico
     */
    public function porUsuario($usuario)
    {
        $eventos = $this->auditoriaService->obtenerEventosPorUsuario($usuario);
        $estadisticasUsuario = $this->auditoriaService->obtenerEstadisticasUsuario($usuario);
        
        return view('admin.auditoria.por-usuario', compact('eventos', 'estadisticasUsuario', 'usuario'));
    }

    /**
     * Ver eventos por tipo de acción
     */
    public function porAccion($accion)
    {
        $eventos = $this->auditoriaService->obtenerEventosPorAccion($accion);
        
        return view('admin.auditoria.por-usuario', compact('eventos', 'accion'));
    }

    /**
     * Limpiar logs antiguos (opcional - con confirmación)
     */
    public function limpiarLogs(Request $request)
    {
        $request->validate([
            'dias' => 'required|integer|min:30',
        ]);

        $resultado = $this->auditoriaService->limpiarLogsAntiguos($request->dias);

        if ($resultado['success']) {
            return redirect()->route('admin.auditoria.index')
                ->with('success', "Se eliminaron {$resultado['eliminados']} registros antiguos.");
        }

        return back()->with('error', 'Error al limpiar logs.');
    }
}
