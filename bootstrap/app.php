<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\SecurityMiddleware;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

       
        $middleware->web(append: [
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            
        ]);

        
        $middleware->alias([
            'role.admin' => \App\Http\Middleware\CheckAdminRole::class,
            'access.contador' => \App\Http\Middleware\CheckContadorAccess::class,
            'security' => SecurityMiddleware::class,
            'auth' => Authenticate::class,
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions): void {
        
        // --- COPIA Y PEGA ESTE BLOQUE COMPLETO ---
        $exceptions->renderable(function (NotFoundHttpException $e, Request $request) {
            
            // Verificamos si la URL que falla contiene el texto del error
            if (str_contains($request->path(), '{{ route')) {
                
                // ¡TRAMPA! Encontramos el error.
                // Registramos un error Nivel "Emergency" para encontrarlo fácil
                Log::emergency(
                    '¡¡¡ERROR DE RUTA ROTA ATRAPADO!!!', 
                    [
                        'url_rota' => $request->path(),
                        'ip' => $request->ip(),
                        'archivo_culpable_trace' => $e->getTraceAsString() // <-- ¡ESTO ES LO IMPORTANTE!
                    ]
                );

                
            }
        });
        

    })->create();