<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class CheckContador
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login')
                ->with('error', 'Debe iniciar sesión para acceder al sistema contable.');
        }

        $user = Auth::user();
        $rol = strtolower($user->tipousuario ?? '');

        $rolesPermitidos = ['contador', 'admin', 'super_admin'];

        if (!in_array($rol, $rolesPermitidos)) {

            Log::warning('Intento de acceso no autorizado al sistema contable', [
                'usuario' => $user->usuario ?? 'desconocido',
                'tipousuario' => $rol,
                'ip' => $request->ip(),
                'url' => $request->url(),
            ]);

            // Redirige según su tipo de usuario real
            $redirectRoute = match ($rol) {
                'admin', 'super_admin' => 'dashboard.admin',
                'contador' => 'contador.dashboard.contador',
                default => 'login',
            };

            return redirect()->route($redirectRoute)
                ->with('error', 'No tiene permisos para acceder al módulo contable.');
        }

        Log::info('Acceso permitido al sistema contable', [
            'usuario' => $user->usuario,
            'rol' => $rol,
            'ip' => $request->ip(),
            'url' => $request->url()
        ]);

        // Pasa el usuario y su rol como atributos adicionales
        $request->attributes->set('contador_user', $user);
        $request->attributes->set('contador_rol', $rol);

        return $next($request);
    }
}
