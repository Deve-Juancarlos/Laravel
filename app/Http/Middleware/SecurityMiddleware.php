<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class SecurityMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // 1️⃣ Registrar todos los accesos
        Log::channel('security')->info('Acceso detectado', [
            'user' => Auth::user()?->usuario ?? 'Guest',
            'ip' => $request->ip(),
            'route' => $request->path(),
            'method' => $request->method(),
            'user_agent' => $request->userAgent()
        ]);

        // 2️⃣ Detectar patrones sospechosos (SQL Injection / XSS)
        $suspicious_patterns = [
            '/(\bUNION\b|\bSELECT\b|\bINSERT\b|\bDELETE\b|\bUPDATE\b|\bDROP\b)/i',
            '/(OR|AND)\s+\d+\s*=\s*\d+/i',
            '/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/i'
        ];

        foreach ($request->all() as $key => $value) {
            if (is_string($value)) {
                foreach ($suspicious_patterns as $pattern) {
                    if (preg_match($pattern, $value)) {
                        Log::channel('security')->warning('Intento de inyección detectado', [
                            'user' => Auth::user()?->usuario ?? 'Guest',
                            'ip' => $request->ip(),
                            'field' => $key,
                            'value' => $value
                        ]);

                        if ($request->expectsJson()) {
                            return response()->json(['error' => 'Solicitud bloqueada por seguridad'], 403);
                        } else {
                            abort(403, 'Solicitud bloqueada por seguridad');
                        }
                    }
                }
            }
        }

        //  Rate limiting por IP
        $key = 'rate_limit:' . $request->ip();
        $attempts = cache()->get($key, 0);

        if ($attempts >= 100) { 
            Log::channel('security')->alert('Rate limit excedido', [
                'ip' => $request->ip(),
                'attempts' => $attempts
            ]);

            if ($request->expectsJson()) {
                return response()->json(['error' => 'Demasiadas solicitudes. Intente más tarde.'], 429);
            } else {
                abort(429, 'Demasiadas solicitudes. Intente más tarde.');
            }
        }

        cache()->put($key, $attempts + 1, now()->addMinute());

        return $next($request);
    }
}
