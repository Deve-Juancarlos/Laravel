<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LibroDiario;
use App\Services\Contabilidad\LibroDiarioService; // <-- ¡REUTILIZA TU SERVICIO!
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class SolicitudAsientoController extends Controller
{
    protected $libroDiarioService;

    // Inyectamos el servicio que SÍ SABE CÓMO ELIMINAR
    public function __construct(LibroDiarioService $libroDiarioService)
    {
        $this->libroDiarioService = $libroDiarioService;
    }

    /**
     * Muestra la lista de asientos pendientes de eliminación.
     */
    public function index()
    {
        $solicitudes = LibroDiario::where('estado', 'PENDIENTE_ELIMINACION')
            ->orderBy('updated_at', 'desc')
            ->paginate(20);

        return view('admin.solicitudes.index', compact('solicitudes'));
    }

    /**
     * APRUEBA la eliminación del asiento.
     */
    public function aprobar($id)
    {
        try {
            // ¡Aquí llamamos al SERVICIO que SÍ elimina!
            // El Observador registrará que el (Auth::user()) ADMIN fue quien lo borró.
            $this->libroDiarioService->deleteAsiento($id, Auth::user()->usuario ?? 'Admin');
            
            return redirect()->route('admin.solicitudes.index')
                ->with('success', 'Asiento eliminado permanentemente.');

        } catch (\Exception $e) {
            Log::error('Error al APROBAR eliminación: ' . $e->getMessage());
            return redirect()->route('admin.solicitudes.index')
                ->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * RECHAZA la eliminación y revierte el estado.
     */
    public function rechazar($id)
    {
        try {
            $asiento = LibroDiario::findOrFail($id);
            $asiento->estado = 'ACTIVO'; // Revertimos el estado
            $asiento->save(); // El Observador auditará este cambio de estado

            // Opcional: Notificar al usuario que lo solicitó
            // $asiento->notificarUsuario(...); 

            return redirect()->route('admin.solicitudes.index')
                ->with('success', 'Solicitud de eliminación rechazada. El asiento ha sido restaurado.');

        } catch (\Exception $e) {
            Log::error('Error al RECHAZAR eliminación: ' . $e->getMessage());
            return redirect()->route('admin.solicitudes.index')
                ->with('error', 'Error: ' . $e->getMessage());
        }
    }
}