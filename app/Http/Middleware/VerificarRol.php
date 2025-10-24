<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VerificarRol
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string ...$roles  // Roles permitidos para acceder
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // Verificar si el usuario está autenticado
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'No autenticado',
                    'mensaje' => 'Debe iniciar sesión para acceder a este recurso'
                ], 401);
            }
            
            return redirect()->route('login')->with('error', 'Debe iniciar sesión para continuar');
        }

        $user = Auth::user();
        
        // Obtener el rol del usuario desde la base de datos
        $userRole = DB::table('Accesoweb')
            ->where('Id', $user->id)
            ->value('Rol');
            
        if (!$userRole) {
            Log::warning('Usuario sin rol asignado', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Sin permisos',
                    'mensaje' => 'Usuario sin rol asignado'
                ], 403);
            }
            
            return redirect()->route('dashboard')->with('error', 'Usuario sin permisos asignados');
        }

       
        if (!in_array($userRole, $roles)) {
            Log::warning('Acceso denegado por rol insuficiente', [
                'user_id' => $user->id,
                'user_role' => $userRole,
                'required_roles' => $roles,
                'route' => $request->route()->getName(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Permisos insuficientes',
                    'mensaje' => 'Su rol actual no tiene permisos para realizar esta acción',
                    'rol_actual' => $userRole,
                    'roles_requeridos' => $roles
                ], 403);
            }
            
            
            return $this->redirectByRole($userRole, $request);
        }


        Log::info('Acceso autorizado', [
            'user_id' => $user->id,
            'user_role' => $userRole,
            'route' => $request->route()->getName(),
            'ip' => $request->ip()
        ]);

       
        $request->attributes->set('user_role', $userRole);
        $request->attributes->set('current_user', $user);

        return $next($request);
    }

    private function redirectByRole(string $role, Request $request)
    {
        $routeMap = [
            'Administrador' => 'dashboard',
            'Gerente' => 'dashboard',
            'Jefe Farmacia' => 'farmacia.dashboard',
            'Farmacéutico' => 'farmacia.productos',
            'Asistente Farmacia' => 'farmacia.inventario',
            'Contador' => 'contabilidad.dashboard',
            'Vendedor' => 'ventas.dashboard',
            'Cajero' => 'ventas.caja',
            'Almacenero' => 'almacen.dashboard',
            'Supervisor' => 'supervision.dashboard',
            'Auditor' => 'auditoria.dashboard',
            'Usuario' => 'dashboard'
        ];

        $targetRoute = $routeMap[$role] ?? 'dashboard';
        
        
        $intendedRoute = $request->route()->getName();
        $errorMessage = "Acceso denegado. Su rol ('{$role}') no tiene permisos para: {$intendedRoute}";
        
        return redirect()->route($targetRoute)->with('error', $errorMessage);
    }

    
    private function checkSpecificPermissions(Request $request, string $userRole): bool
    {
        $sensitiveActions = [
            'usuarios' => ['Administrador'],
            'configuracion' => ['Administrador', 'Gerente'],
            'reportes_financieros' => ['Administrador', 'Gerente', 'Contador'],
            'auditoria' => ['Administrador', 'Auditor', 'Gerente'],
            'inventario_ajustes' => ['Administrador', 'Jefe Farmacia', 'Farmacéutico'],
            'contabilidad_asientos' => ['Administrador', 'Contador'],
            'sunat_envio' => ['Administrador', 'Contador', 'Jefe Farmacia'],
            'medicamentos_controlados' => ['Administrador', 'Farmacéutico', 'Jefe Farmacia']
        ];

        $routeName = $request->route()->getName();
        
        foreach ($sensitiveActions as $action => $allowedRoles) {
            if (str_contains($routeName, $action)) {
                return in_array($userRole, $allowedRoles);
            }
        }
        
        return true; 
    }
}