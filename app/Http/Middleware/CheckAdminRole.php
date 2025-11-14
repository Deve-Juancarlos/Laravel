<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate; // Usamos Gate
use Symfony\Component\HttpFoundation\Response;

class CheckAdminRole
{
    public function handle(Request $request, Closure $next): Response
    {
        
        if (Gate::denies('es-admin')) {
            abort(403, 'Acceso exclusivo para administradores.');
        }

        return $next($request);
    }
}
