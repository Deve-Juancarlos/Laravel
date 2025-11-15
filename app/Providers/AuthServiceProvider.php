<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log; // <-- Añadido para el debug

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Gate para administradores
        Gate::define('es-admin', function ($user) {
            // --- CORREGIDO con trim() ---
            $tipo = strtolower(trim($user->tipousuario));
            return $tipo === 'administrador';
        });

        // Gate para contadores o administradores
        Gate::define('gestionar-contabilidad', function ($user) {
            
            // --- CORREGIDO con trim() y Log::debug() ---
            $original = $user->tipousario;
            $limpiado = trim($original);
            $tipo_final = strtolower($limpiado);
            $permitido = in_array($tipo_final, ['contador', 'administrador']);

            // --- CORREGIDO: Forzamos el log al canal 'security' ---
            Log::channel('security')->debug('Verificando Gate [gestionar-contabilidad]', [
                'idusuario' => $user->getKey(),
                'tipousuario_original' => $original,
                'despues_de_trim()' => $limpiado,
                'despues_de_strtolower()' => $tipo_final,
                'lista_permitida' => "['contador', 'administrador']",
                'es_permitido' => $permitido
            ]);

            return $permitido;
        });

        // Gate solo administrador para eliminar o aprobar asientos
        Gate::define('aprobar-eliminacion-asiento', function ($user) {
            // --- CORREGIDO con trim() ---
            $tipo = strtolower(trim($user->tipousuario));
            return $tipo === 'administrador';
        });
    }
}