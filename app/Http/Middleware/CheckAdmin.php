<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; 
use Symfony\Component\HttpFoundation\Response;

class CheckAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        Log::info('CheckAdmin: Usuario autenticado', [ 
            'usuario' => Auth::user()->usuario,
            'tipousuario_raw' => Auth::user()->tipousuario,
            'tipousuario_normalized' => strtolower(Auth::user()->tipousuario)
        ]);

        if (strtolower(Auth::user()->tipousuario) !== 'administrador') {
            return redirect()->route('access.denied');
        }

        return $next($request);
    }

    
}