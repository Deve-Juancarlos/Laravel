<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notificacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificacionController extends Controller
{
    public function index(Request $request)
    {
        $usuarioId = Auth::id();
        
        $notificaciones = Notificacion::where('usuario_id', $usuarioId)
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->map(function($notif) {
                return [
                    'id' => $notif->id,
                    'tipo' => $notif->tipo,
                    'titulo' => $notif->titulo,
                    'mensaje' => $notif->mensaje,
                    'icono' => $notif->icono,
                    'color' => $notif->color,
                    'url' => $notif->url,
                    'leida' => $notif->leida,
                    'tiempo' => $notif->created_at->diffForHumans(),
                    'fecha_completa' => $notif->created_at->format('d/m/Y H:i'),
                ];
            });

        return response()->json([
            'success' => true,
            'notificaciones' => $notificaciones
        ]);
    }

    public function countNoLeidas()
    {
        $usuarioId = Auth::id();
        
        $count = Notificacion::where('usuario_id', $usuarioId)
            ->where('leida', false)
            ->count();

        return response()->json([
            'success' => true,
            'count' => $count
        ]);
    }

    public function marcarLeida($id)
    {
        $usuarioId = Auth::id();
        
        $notificacion = Notificacion::where('id', $id)
            ->where('usuario_id', $usuarioId)
            ->first();

        if (!$notificacion) {
            return response()->json([
                'success' => false,
                'message' => 'Notificación no encontrada'
            ], 404);
        }

        $notificacion->update([
            'leida' => true,
            'leida_en' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Notificación marcada como leída'
        ]);
    }

    public function marcarTodasLeidas()
    {
        $usuarioId = Auth::id();
        
        Notificacion::where('usuario_id', $usuarioId)
            ->where('leida', false)
            ->update([
                'leida' => true,
                'leida_en' => now()
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Todas las notificaciones marcadas como leídas'
        ]);
    }

    public function eliminar($id)
    {
        $usuarioId = Auth::id();
        
        $notificacion = Notificacion::where('id', $id)
            ->where('usuario_id', $usuarioId)
            ->first();

        if (!$notificacion) {
            return response()->json([
                'success' => false,
                'message' => 'Notificación no encontrada'
            ], 404);
        }

        $notificacion->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notificación eliminada'
        ]);
    }

    public function limpiarLeidas()
    {
        $usuarioId = Auth::id();
        
        $eliminadas = Notificacion::where('usuario_id', $usuarioId)
            ->where('leida', true)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => "Se eliminaron {$eliminadas} notificaciones leídas"
        ]);
    }
}