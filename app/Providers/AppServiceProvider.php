<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;


use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterval;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Services\Contabilidad\CajaService;
use App\Services\Contabilidad\CanjeService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    
    public function register()
    {
        $this->app->singleton(CanjeService::class, function ($app) {
            return new CanjeService($app->make(CajaService::class));
        });
    }

    public function boot(): void
    {
       
        Paginator::useBootstrapFive();
        setlocale(LC_TIME, 'es_ES.UTF-8', 'Spanish_Spain', 'es_PE', 'es_ES');
        Carbon::setLocale('es');
        CarbonImmutable::setLocale('es');
        CarbonInterval::setLocale('es');
        CarbonPeriod::setLocale('es');

        if ($this->app->environment('local')) {
            Route::get('/clear-cache', function () {
                Artisan::call('optimize:clear');
                return response()->json([
                    'success' => true,
                    'message' => 'Caché limpiada correctamente.'
                ]);
            });
        }
       
        View::composer('partials.navbar', function ($view) {
            $notificacionesNoLeidas = collect(); 

            if (Auth::check()) {
                $usuarioId = Auth::user()->idusuario; 
                
                $notificacionesNoLeidas = DB::connection('sqlsrv')
                    ->table('notificaciones')
                    ->where('usuario_id', $usuarioId)
                    ->where('leida', 0)
                    ->orderBy('created_at', 'desc')
                    ->take(10) 
                    ->get();
            }

            
            $view->with('notificacionesNoLeidas', $notificacionesNoLeidas);
        });
        
    }
    
}
