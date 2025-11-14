<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// --- TUS IMPORTS (PARA EL IDIOMA) ---
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterval;
use Carbon\CarbonPeriod;

// --- MIS IMPORTS (PARA LA CAMPANITA 🔔) ---
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

// --- IMPORTS QUE NECESITA TU CÓDIGO (Rutas y Artisan) ---
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // (Tu código register() estaba vacío, lo dejamos así)
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // --- (INICIO) TU CÓDIGO ACTUAL (AJUSTADO) ---
        // 1. Configuración de idioma Español
        setlocale(LC_TIME, 'es_ES.UTF-8', 'Spanish_Spain', 'es_PE', 'es_ES');
        Carbon::setLocale('es');
        CarbonImmutable::setLocale('es');
        CarbonInterval::setLocale('es');
        CarbonPeriod::setLocale('es');

        // 2. Observer (Como me pediste "olvida los modelos", comento esta línea)
        // Si SÍ quieres usar Modelos, la descomentamos, pero requerirá
        // que creemos el archivo App\Models\LibroDiario.
        // LibroDiario::observe(LibroDiarioObserver::class);

        // 3. Ruta de Limpieza de Caché
        if ($this->app->environment('local')) {
            Route::get('/clear-cache', function () {
                Artisan::call('optimize:clear');
                return response()->json([
                    'success' => true,
                    'message' => 'Caché limpiada correctamente.'
                ]);
            });
        }
        // --- (FIN) TU CÓDIGO ACTUAL ---


        // --- (INICIO) CÓDIGO NUEVO (EL "GOLAZO") ---
        // 4. Compartir Notificaciones con la Navbar
        // Esto comparte las notificaciones con 'partials.navbar'
        View::composer('partials.navbar', function ($view) {
            $notificacionesNoLeidas = collect(); // Por defecto, vacío

            if (Auth::check()) {
                $usuarioId = Auth::user()->idusuario; // Usamos el ID de 'accesoweb'
                
                $notificacionesNoLeidas = DB::connection('sqlsrv')
                    ->table('notificaciones')
                    ->where('usuario_id', $usuarioId)
                    ->where('leida', 0)
                    ->orderBy('created_at', 'desc')
                    ->take(10) // Tomamos las últimas 10
                    ->get();
            }

            // Pasamos la variable a la vista 'partials.navbar'
            $view->with('notificacionesNoLeidas', $notificacionesNoLeidas);
        });
        // --- (FIN) CÓDIGO NUEVO ---
    }
}