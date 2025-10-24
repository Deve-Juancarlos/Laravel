<?php

namespace App\Policies;

use App\Models\Accesoweb;
use App\Models\Factura;
use Illuminate\Auth\Access\HandlesAuthorization;

class FacturaPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\Accesoweb  $accesoweb
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(Accesoweb $accesoweb)
    {
        // Verificar si el usuario tiene permiso para ver facturas
        return $this->verificarPermiso($accesoweb, 'facturas.view');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\Accesoweb  $accesoweb
     * @param  \App\Models\Factura  $factura
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(Accesoweb $accesoweb, Factura $factura)
    {
        // Verificar permiso básico de visualización
        if (!$this->verificarPermiso($accesoweb, 'facturas.view')) {
            return false;
        }

        // Los usuarios pueden ver sus propias facturas
        if ($factura->usuario_id === $accesoweb->id) {
            return true;
        }

        // Los gerentes y administradores pueden ver todas las facturas
        if ($this->verificarRol($accesoweb, ['gerente', 'administrador'])) {
            return true;
        }

        // Los vendedores pueden ver facturas de su área
        if ($accesoweb->rol === 'vendedor') {
            return $this->verificarMismaSede($accesoweb, $factura);
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\Accesoweb  $accesoweb
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(Accesoweb $accesoweb)
    {
        // Verificar si el usuario puede crear facturas
        if (!$this->verificarPermiso($accesoweb, 'facturas.create')) {
            return false;
        }

        // Solo personal autorizado puede crear facturas
        return $this->verificarRol($accesoweb, [
            'vendedor',
            'farmaceutico',
            'supervisor',
            'gerente',
            'administrador'
        ]);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\Accesoweb  $accesoweb
     * @param  \App\Models\Factura  $factura
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(Accesoweb $accesoweb, Factura $factura)
    {
        // No se pueden modificar facturas ya aprobadas
        if ($factura->estado === 'APROBADA' && !$this->verificarRol($accesoweb, ['administrador'])) {
            return false;
        }

        // Los usuarios pueden modificar sus propias facturas pendientes
        if ($factura->usuario_id === $accesoweb->id && $factura->estado === 'PENDIENTE') {
            return $this->verificarPermiso($accesoweb, 'facturas.edit');
        }

        // Supervisores y superiores pueden modificar facturas
        if ($this->verificarRol($accesoweb, ['supervisor', 'gerente', 'administrador'])) {
            return $this->verificarPermiso($accesoweb, 'facturas.edit');
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\Accesoweb  $accesoweb
     * @param  \App\Models\Factura  $factura
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(Accesoweb $accesoweb, Factura $factura)
    {
        // No se pueden eliminar facturas aprobadas
        if ($factura->estado === 'APROBADA') {
            return false;
        }

        // Solo administradores pueden eliminar facturas
        if (!$this->verificarRol($accesoweb, ['administrador'])) {
            return false;
        }

        return $this->verificarPermiso($accesoweb, 'facturas.delete');
    }

    /**
     * Determine whether the user can approve the model.
     *
     * @param  \App\Models\Accesoweb  $accesoweb
     * @param  \App\Models\Factura  $factura
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function approve(Accesoweb $accesoweb, Factura $factura)
    {
        // Solo supervisores y superiores pueden aprobar facturas
        if (!$this->verificarRol($accesoweb, ['supervisor', 'gerente', 'administrador'])) {
            return false;
        }

        // No se pueden aprobar facturas propias (evitar conflictos de interés)
        if ($factura->usuario_id === $accesoweb->id) {
            return false;
        }

        return $this->verificarPermiso($accesoweb, 'facturas.approve');
    }

    /**
     * Determine whether the user can export the model.
     *
     * @param  \App\Models\Accesoweb  $accesoweb
     * @param  \App\Models\Factura  $factura
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function export(Accesoweb $accesoweb, Factura $factura = null)
    {
        // Solo personal autorizado puede exportar facturas
        if (!$this->verificarPermiso($accesoweb, 'facturas.export')) {
            return false;
        }

        // Los vendedores pueden exportar solo sus facturas
        if ($accesoweb->rol === 'vendedor') {
            if ($factura && $factura->usuario_id !== $accesoweb->id) {
                return false;
            }
            return true;
        }

        // Supervisores y superiores pueden exportar todas las facturas
        return $this->verificarRol($accesoweb, [
            'supervisor',
            'gerente',
            'administrador',
            'contador'
        ]);
    }

    /**
     * Determine whether the user can import the model.
     *
     * @param  \App\Models\Accesoweb  $accesoweb
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function import(Accesoweb $accesoweb)
    {
        // Solo contadores, gerentes y administradores pueden importar facturas
        return $this->verificarRol($accesoweb, ['contador', 'gerente', 'administrador'])
            && $this->verificarPermiso($accesoweb, 'facturas.import');
    }

    /**
     * Determine whether the user can view reports for the model.
     *
     * @param  \App\Models\Accesoweb  $accesoweb
     * @param  \App\Models\Factura  $factura
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewReports(Accesoweb $accesoweb, Factura $factura = null)
    {
        // Contadores pueden ver reportes financieros
        if ($accesoweb->rol === 'contador') {
            return $this->verificarPermiso($accesoweb, 'facturas.reports');
        }

        // Gerentes y administradores pueden ver todos los reportes
        if ($this->verificarRol($accesoweb, ['gerente', 'administrador'])) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can audit the model.
     *
     * @param  \App\Models\Accesoweb  $accesoweb
     * @param  \App\Models\Factura  $factura
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function audit(Accesoweb $accesoweb, Factura $factura)
    {
        // Solo auditores, contadores y administradores pueden auditar facturas
        if (!$this->verificarRol($accesoweb, ['auditor', 'contador', 'administrador'])) {
            return false;
        }

        return $this->verificarPermiso($accesoweb, 'facturas.audit');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\Accesoweb  $accesoweb
     * @param  \App\Models\Factura  $factura
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(Accesoweb $accesoweb, Factura $factura)
    {
        // Solo administradores pueden restaurar facturas
        return $this->verificarRol($accesoweb, ['administrador'])
            && $this->verificarPermiso($accesoweb, 'facturas.restore');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\Accesoweb  $accesoweb
     * @param  \App\Models\Factura  $factura
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(Accesoweb $accesoweb, Factura $factura)
    {
        // Solo administradores pueden eliminar permanentemente
        return $this->verificarRol($accesoweb, ['administrador'])
            && $this->verificarPermiso($accesoweb, 'facturas.forceDelete');
    }

    /**
     * Verificar si el usuario tiene un rol específico
     *
     * @param Accesoweb $accesoweb
     * @param array|string $roles
     * @return bool
     */
    private function verificarRol(Accesoweb $accesoweb, $roles): bool
    {
        if (is_string($roles)) {
            $roles = [$roles];
        }

        return in_array($accesoweb->rol, $roles);
    }

    /**
     * Verificar si el usuario tiene un permiso específico
     *
     * @param Accesoweb $accesoweb
     * @param string $permiso
     * @return bool
     */
    private function verificarPermiso(Accesoweb $accesoweb, string $permiso): bool
    {
        // En una implementación real, esto consultaría la base de datos
        // Por ahora simulamos que todos los usuarios tienen todos los permisos
        return true;
        
        // Ejemplo de implementación real:
        // return $accesoweb->hasPermissionTo($permiso);
    }

    /**
     * Verificar si el usuario está en la misma sede que la factura
     *
     * @param Accesoweb $accesoweb
     * @param Factura $factura
     * @return bool
     */
    private function verificarMismaSede(Accesoweb $accesoweb, Factura $factura): bool
    {
        // Implementar lógica de sede si es necesario
        return true;
    }
}