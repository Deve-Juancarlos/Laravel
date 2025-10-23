<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class CheckContador
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login')
                ->with('error', 'Debe iniciar sesión para acceder al sistema contable.');
        }

        $user = Auth::user();
        $rolesPermitidos = ['contador', 'admin', 'super_admin'];

        // Verificar si tiene un rol permitido
        if (!$this->tieneRolPermitido($user, $rolesPermitidos)) {

            Log::warning('Intento de acceso no autorizado al sistema contable', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'user_roles' => $user->roles ?? $user->role ?? 'unknown',
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->url()
            ]);

            // Redirigir según el rol principal
            $redirectRoute = match ($this->obtenerRolPrincipal($user)) {
                'admin', 'super_admin' => 'dashboard.admin',
                'contador' => 'dashboard.contador',
                default => 'login',
            };

            return redirect()->route($redirectRoute)
                ->with('error', 'No tiene permisos para acceder al sistema contable. Contacte al administrador.');
        }

        // Logging de acceso permitido
        Log::info('Acceso al sistema contable', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'rol' => $this->obtenerRolPrincipal($user),
            'ip' => $request->ip(),
            'url' => $request->url()
        ]);

        // Agregar información del usuario a la request
        $request->attributes->set('contador_user', $user);
        $request->attributes->set('contador_rol', $this->obtenerRolPrincipal($user));

        return $next($request);
    }

    /**
     * Verificar si el usuario tiene uno de los roles permitidos
     */
    private function tieneRolPermitido($user, array $rolesPermitidos): bool
    {
        // Propiedad role directa
        if (property_exists($user, 'role') && in_array($user->role, $rolesPermitidos)) {
            return true;
        }

        // Propiedad roles como array
        if (property_exists($user, 'roles') && is_array($user->roles)) {
            return !empty(array_intersect($user->roles, $rolesPermitidos));
        }

        // Relación roles Eloquent
        if (method_exists($user, 'roles') && $user->roles()->exists()) {
            $userRoles = $user->roles()->pluck('name')->toArray();
            return !empty(array_intersect($userRoles, $rolesPermitidos));
        }

        // Tabla pivote userRoles
        if (property_exists($user, 'userRoles')) {
            $userRoles = $user->userRoles->pluck('role_name')->toArray();
            return !empty(array_intersect($userRoles, $rolesPermitidos));
        }

        return false;
    }

    /**
     * Obtener el rol principal del usuario
     */
    private function obtenerRolPrincipal($user): string
    {
        // Prioridad: super_admin > admin > contador
        if (property_exists($user, 'role')) {
            return $user->role;
        }

        if (method_exists($user, 'roles') && $user->roles()->exists()) {
            $roles = $user->roles()->pluck('name')->toArray();

            if (in_array('super_admin', $roles)) return 'super_admin';
            if (in_array('admin', $roles)) return 'admin';
            if (in_array('contador', $roles)) return 'contador';
        }

        // Pivote userRoles
        if (property_exists($user, 'userRoles')) {
            $roles = $user->userRoles->pluck('role_name')->toArray();

            if (in_array('super_admin', $roles)) return 'super_admin';
            if (in_array('admin', $roles)) return 'admin';
            if (in_array('contador', $roles)) return 'contador';
        }

        return 'unknown';
    }
}
