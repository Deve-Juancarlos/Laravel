<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
// Quitamos 'Gate' porque ya no lo usamos
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminRole
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // --- LÓGICA DIRECTA PARA SALTAR EL CACHÉ DEL GATE ---

        // 1. Obtenemos el valor original
        $original = $user->tipousuario;
        
        // 2. Limpiamos espacios en blanco
        $limpiado = trim($original);
        
        // 3. Convertimos a minúsculas
        $tipo_final = strtolower($limpiado);
        
        // 4. Comparamos (Solo debe ser 'administrador')
        $permitido = ($tipo_final === 'administrador');

        // 5. Log de depuración
        Log::channel('security')->info('Verificando [CheckAdminRole]', [
            'idusuario' => $user->getKey(),
            'tipousario_original_DB' => $original,
            'tipo_final' => $tipo_final,
            'comparando_con' => "'administrador'",
            'RESULTADO_PERMITIDO' => $permitido
        ]);

        // 6. Si no es permitido, fallamos
        if (!$permitido) {
            Log::channel('security')->warning('Acceso denegado. No es admin.', [
                'idusuario' => $user->getKey(),
                'tipousario_recibido' => $tipo_final
            ]);
            abort(403, 'Acceso exclusivo para administradores.');
        }

        // 7. Si es permitido, continuamos
        return $next($request);
    }
}