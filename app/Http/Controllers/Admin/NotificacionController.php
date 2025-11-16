<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\NotificacionService;
use Illuminate\Http\Request;

class NotificacionController extends Controller
{
    protected $notificacionService;

    public function __construct(NotificacionService $notificacionService)
    {
        $this->notificacionService = $notificacionService;
    }

    /**
     * Lista de todas las notificaciones
     */
    public function index(Request $request)
    {
        $filtros = [
            'tipo' => $request->get('tipo'),
            'leida' => $request->get('leida'),
            'fecha_inicio' => $request->get('fecha_inicio'),
            'fecha_fin' => $request->get('fecha_fin'),
        ];

        $notificaciones = $this->notificacionService->obtenerNotificaciones($filtros);
        $estadisticas = $this->notificacionService->obtenerEstadisticas();
        
        return view('admin.notificaciones.index', compact('notificaciones', 'estadisticas', 'filtros'));
    }

    /**
     * Formulario para crear notificación manual
     */
    public function create()
    {
        $usuarios = $this->notificacionService->obtenerUsuariosDisponibles();
        
        return view('admin.notificaciones.create', compact('usuarios'));
    }

    /**
     * Guardar nueva notificación
     */
    public function store(Request $request)
    {
        $request->validate([
            'tipo' => 'required|in:INFO,ALERTA,CRITICO,EXITO',
            'titulo' => 'required|string|max:255',
            'mensaje' => 'required|string',
            'usuario_id' => 'nullable|integer',
            'url' => 'nullable|url|max:500',
        ]);

        $resultado = $this->notificacionService->crearNotificacion([
            'tipo' => $request->tipo,
            'titulo' => $request->titulo,
            'mensaje' => $request->mensaje,
            'usuario_id' => $request->usuario_id, // NULL = para todos
            'url' => $request->url,
            'icono' => $request->icono ?? 'fa-bell',
            'color' => $this->getColorPorTipo($request->tipo),
        ]);

        if ($resultado) {
            return redirect()->route('admin.notificaciones.index')
                ->with('success', 'Notificación creada correctamente.');
        }

        return back()->with('error', 'Error al crear la notificación.');
    }

    /**
     * Marcar notificación como leída
     */
    public function marcarLeida($id)
    {
        $resultado = $this->notificacionService->marcarComoLeida($id);

        if ($resultado) {
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 400);
    }

    /**
     * Marcar todas como leídas
     */
    public function marcarTodasLeidas()
    {
        $usuarioId = auth()->user()->id;
        $resultado = $this->notificacionService->marcarTodasComoLeidas($usuarioId);

        return redirect()->back()->with('success', 'Todas las notificaciones fueron marcadas como leídas.');
    }

    /**
     * Eliminar notificación
     */
    public function destroy($id)
    {
        $resultado = $this->notificacionService->eliminarNotificacion($id);

        if ($resultado) {
            return redirect()->route('admin.notificaciones.index')
                ->with('success', 'Notificación eliminada correctamente.');
        }

        return back()->with('error', 'Error al eliminar la notificación.');
    }

    /**
     * Dashboard de estadísticas
     */
    public function estadisticas()
    {
        $estadisticas = $this->notificacionService->obtenerEstadisticasDetalladas();
        $porTipo = $this->notificacionService->obtenerNotificacionesPorTipo();
        $recientes = $this->notificacionService->obtenerNotificacionesRecientes(20);
        
        return view('admin.notificaciones.estadisticas', compact('estadisticas', 'porTipo', 'recientes'));
    }

    /**
     * Generar notificaciones automáticas manualmente
     */
    public function generarAutomaticas()
    {
        $resultado = $this->notificacionService->generarNotificacionesAutomaticas();

        return redirect()->route('admin.notificaciones.index')
            ->with('success', "Se generaron {$resultado['creadas']} notificaciones automáticas.");
    }

    /**
     * Ver notificaciones no leídas (API para dropdown)
     */
    public function noLeidas()
    {
        $usuarioId = auth()->user()->id;
        $notificaciones = $this->notificacionService->obtenerNoLeidas($usuarioId);

        return response()->json($notificaciones);
    }

    /**
     * Obtener color según tipo
     */
    private function getColorPorTipo($tipo)
    {
        return match($tipo) {
            'INFO' => 'primary',
            'ALERTA' => 'warning',
            'CRITICO' => 'danger',
            'EXITO' => 'success',
            default => 'secondary'
        };
    }
}
