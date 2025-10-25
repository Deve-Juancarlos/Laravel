<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\HandleInertiaRequests;

use App\Http\Middleware\CheckAdmin;
use App\Http\Middleware\CheckVendedor;
use App\Http\Middleware\CheckContador;
use App\Http\Middleware\SecurityMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        
        // ✅ MIDDLEWARE PARA WEB (Vistas Blade)
        $middleware->web(append: [
            HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        // ✅ MIDDLEWARE PARA API (JSON/REST)
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);

        // ✅ ALIAS PARA MIDDLEWARES DE ROL SIFANO
        $middleware->alias([
            'check.admin' => CheckAdmin::class,
            'check.vendedor' => CheckVendedor::class,
            'check.contador' => CheckContador::class,
            'security' => SecurityMiddleware::class,
            'verificar.rol' => SecurityMiddleware::class, // Alias para compatibilidad
        ]);

        // ✅ MIDDLEWARE GLOBAL (Se ejecuta en todas las peticiones)
        $middleware->prepend(SecurityMiddleware::class);

        // ✅ MIDDLEWARE GROUPS
        $middleware->appendToGroup('web', [
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        $middleware->appendToGroup('api', [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        // ✅ RATE LIMITING PERSONALIZADO PARA SIFANO
        $middleware->appendToGroup('api', [
            \Illuminate\Routing\Middleware\ThrottleRequests::class . ':100,1', // 100 requests por minuto
        ]);

        // ✅ MIDDLEWARE PARA MANEJO DE ARCHIVOS (Upload)
        $middleware->append([
            \Illuminate\Http\Middleware\ValidatePostSize::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        
        // ✅ MANEJO PERSONALIZADO DE EXCEPCIONES SIFANO
        
        // Error 404 - Página no encontrada
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'error' => 'Recurso no encontrado',
                    'message' => 'La ruta solicitada no existe en el sistema SIFANO',
                    'code' => 404
                ], 404);
            }
            return response()->view('errors.404', [], 404);
        });

        // Error 403 - No autorizado
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'error' => 'Acceso denegado',
                    'message' => 'No tienes permisos para acceder a este recurso en SIFANO',
                    'code' => 403
                ], 403);
            }
            return response()->view('errors.403', [], 403);
        });

        // Error 500 - Error del servidor
        $exceptions->render(function (\Exception $e, $request) {
            if ($request->is('api/*')) {
                // Log del error para debugging
                \Log::error('API Error SIFANO: ' . $e->getMessage(), [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                return response()->json([
                    'error' => 'Error interno del servidor',
                    'message' => app()->environment('local') ? $e->getMessage() : 'Error interno en sistema SIFANO',
                    'code' => 500
                ], 500);
            }
            
            // En producción, no mostrar detalles del error
            if (!app()->environment('local')) {
                return response()->view('errors.500', [], 500);
            }
            
            return response()->view('errors.500', [
                'exception' => $e
            ], 500);
        });

        // ✅ MANEJO DE ERRORES DE VALIDACIÓN
        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'error' => 'Error de validación',
                    'message' => 'Los datos proporcionados no son válidos',
                    'details' => $e->errors(),
                    'code' => 422
                ], 422);
            }
            
            // Para web, redireccionar con errores
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        });

        // ✅ MANEJO DE ERRORES DE AUTENTICACIÓN
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'error' => 'No autenticado',
                    'message' => 'Debes iniciar sesión para acceder a este recurso',
                    'code' => 401
                ], 401);
            }
            
            return redirect()->route('login');
        });
    })
    ->create();