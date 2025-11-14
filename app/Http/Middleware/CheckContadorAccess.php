<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate; // Usamos Gate
use Illuminate\Support\Facades\Log;   // Mantenemos tus Logs, son una buena práctica
use Symfony\Component\HttpFoundation\Response;

class CheckContadorAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        // El Gate 'gestionar-contabilidad' decide si el usuario puede pasar.
        if (Gate::denies('gestionar-contabilidad')) {
            
            // Si el Gate dice "no", registramos el intento y abortamos.
            Log::warning('Acceso denegado al módulo de contabilidad.', [
                'usuario_id' => $request->user()->id,
                'usuario_nombre' => $request->user()->usuario, // Asumiendo que 'usuario' es el nombre
                'rol' => $request->user()->rol,
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
            ]);

            abort(403, 'No tiene permisos para acceder al módulo de contabilidad.');
        }

        // Si el usuario tiene permiso, el log es opcional, pero útil.
        Log::info('Acceso permitido al módulo de contabilidad', [
            'usuario_id' => $request->user()->id,
            'rol' => $request->user()->rol
        ]);

        return $next($request);
    }
}
