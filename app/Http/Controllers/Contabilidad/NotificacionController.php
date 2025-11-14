<?php

namespace App\Http\Controllers\Contabilidad;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class NotificacionController extends Controller
{
    protected $connection = 'sqlsrv';

    /**
     * Muestra el historial completo de notificaciones
     */
    public function index()
    {
        $notificaciones = DB::connection($this->connection)
            ->table('notificaciones')
            ->where('usuario_id', Auth::user()->idusuario)
            ->orderBy('created_at', 'desc')
            ->paginate(30);
            
        return view('contabilidad.notificaciones.index', compact('notificaciones'));
    }

    /**
     * Muestra el formulario para crear una nueva notificación (para la Contadora)
     */
    public function create()
    {
        // Buscamos a los usuarios que sean 'ADMIN'
        $admins = DB::connection($this->connection)
            ->table('accesoweb')
            ->where('tipousuario', 'ADMIN') // ¡Asumimos que el admin tiene este tipo!
            ->get();

        return view('contabilidad.notificaciones.create', compact('admins'));
    }

    /**
     * Guarda la nueva notificación en la BD
     */
    public function store(Request $request)
    {
        $request->validate([
            'usuario_id' => 'required|integer|exists:sqlsrv.accesoweb,idusuario',
            'titulo' => 'required|string|max:255',
            'mensaje' => 'required|string|max:1000',
            'icono' => 'required|string',
            'color' => 'required|string',
            'url' => 'nullable|string|max:500',
        ]);

        try {
            DB::connection($this->connection)->table('notificaciones')->insert([
                'usuario_id' => $request->usuario_id,
                'titulo' => $request->titulo,
                'mensaje' => $request->mensaje,
                'icono' => $request->icono,
                'color' => $request->color,
                'url' => $request->url,
                'leida' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return redirect()->route('contador.notificaciones.create')
                             ->with('success', '¡Notificación enviada exitosamente!');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al enviar: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Marca una notificación como leída (para el AJAX de la campanita)
     */
    public function markAsRead($id)
    {
        try {
            DB::connection($this->connection)
                ->table('notificaciones')
                ->where('id', $id)
                ->where('usuario_id', Auth::user()->idusuario) // Seguridad: solo el dueño
                ->update([
                    'leida' => 1,
                    'leida_en' => now()
                ]);
            
            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}