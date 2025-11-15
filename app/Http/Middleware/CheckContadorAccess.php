<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckContadorAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        
        // 1. Obtenemos el valor original
        $original = $user->tipousuario;
        
        // 2. Limpiamos espacios en blanco
        $limpiado = trim($original);
        
        // 3. Convertimos a minúsculas
        $tipo_final = strtolower($limpiado);
        
        // 4. Comparamos
        $lista_permitida = ['contador', 'administrador'];
        $permitido = in_array($tipo_final, $lista_permitida);

        // 5. Log de depuración EXACTO
        // Usamos 'info' para que aparezca en tu log sí o sí.
        Log::channel('security')->info('--- MÉTODO DE DEPURACIÓN EXACTO ---', [
            'idusuario' => $user->getKey(),
            'tipousario_original_DB' => $original,
            'despues_de_trim()' => $limpiado,
            'despues_de_strtolower()' => $tipo_final,
            'comparando_con' => "['contador', 'administrador']",
            'RESULTADO_PERMITIDO' => $permitido
        ]);

        // 6. Si no es permitido, fallamos
        if (!$permitido) {
            // Ya no usamos el Gate, solo la lógica directa
            Log::channel('security')->warning('Acceso denegado. Falló la lógica de tipousario.', [
                'idusuario' => $user->getKey(),
                'tipousario_recibido' => $tipo_final
            ]);
            abort(403, 'No tiene permisos para acceder al módulo de contabilidad.');
        }

        // 7. Si es permitido, continuamos
        Log::channel('security')->info('Acceso PERMITIDO al módulo de contabilidad', [
            'idusuario' => $user->getKey(),
            'tipousario' => $tipo_final
        ]);

        return $next($request);
    }
}