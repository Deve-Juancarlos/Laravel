<?php

namespace App\Providers;

use App\Models\AccesoWeb;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
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

       
        Gate::define('es-admin', function (AccesoWeb $user) {
            
            $rol = strtoupper(trim($user->rol));
            return $rol === 'ADMIN';
        });

        Gate::define('es-contador', function (AccesoWeb $user) {
        
            $rol = strtoupper(trim($user->rol));
            return $rol === 'CONTADOR';
        });

       
        Gate::define('gestionar-contabilidad', function (AccesoWeb $user) {
         
            $rol = strtoupper(trim($user->rol));
            
            return $rol === 'ADMIN' || $rol === 'CONTADOR';
        });

        Gate::define('aprobar-eliminacion-asiento', function (AccesoWeb $user) {
        
            return Gate::allows('es-admin', $user);
        });
    }
}