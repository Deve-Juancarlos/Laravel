<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

use App\Http\Middleware\CheckAdmin;
use App\Http\Middleware\CheckVendedor;
use App\Http\Middleware\CheckContador;
use App\Http\Middleware\SecurityMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        // ✅ Alias para middlewares de rol
        $middleware->alias([
            'check.admin' => CheckAdmin::class,
            'check.vendedor' => CheckVendedor::class,
            'check.contador' => CheckContador::class,
            'security' => SecurityMiddleware::class,
        ]);

        
        $middleware->prepend(SecurityMiddleware::class);

    })
    ->withExceptions(function (Exceptions $exceptions): void {
        
    })
    ->create();
