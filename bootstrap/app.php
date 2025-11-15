<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\SecurityMiddleware;

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
        // ...
    })->create();