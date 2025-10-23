<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckVendedor
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Verificar que esté autenticado
        if (!$user) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'message' => 'Debes iniciar sesión para continuar.',
                    'status' => 'error'
                ], 401);
            }

            return redirect()->route('login')
                ->with('error', 'Debes iniciar sesión para continuar.');
        }

        // Verificar que sea vendedor (normalizado a minúsculas)
        if (strtolower($user->tipousuario) !== 'vendedor') {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'message' => 'No tienes permisos para acceder a esta sección.',
                    'status' => 'error'
                ], 403);
            }

            return redirect()->route('access.denied')
                ->with('error', 'No tienes permisos de vendedor.');
        }

        return $next($request);
    }
}