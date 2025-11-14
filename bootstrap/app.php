<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

use App\Http\Middleware\CheckVendedor;
use App\Http\Middleware\SecurityMiddleware;
use App\Http\Middleware\Authenticate; // ← corregido

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        // Alias personalizados
        $middleware->alias([
            'role.admin' => \App\Http\Middleware\CheckAdminRole::class,
            'access.contador' => \App\Http\Middleware\CheckContadorAccess::class,
            'security' => SecurityMiddleware::class,

            // Solo si NO usas Breeze/Fortify
            'auth' => Authenticate::class,
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions): void {

    })
    ->create();
