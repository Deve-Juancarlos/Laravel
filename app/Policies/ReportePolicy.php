<?php

namespace App\Policies;

use App\Models\Accesoweb;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReportePolicy
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
        // Verificar si el usuario tiene permiso para ver reportes
        return $this->verificarPermiso($accesoweb, 'reportes.view');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\Accesoweb  $accesoweb
     * @param  string  $tipoReporte
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(Accesoweb $accesoweb, string $tipoReporte = null)
    {
        // Verificar permiso básico
        if (!$this->verificarPermiso($accesoweb, 'reportes.view')) {
            return false;
        }

        // Si no se especifica tipo, verificar acceso general
        if (!$tipoReporte) {
            return $this->verificarRol($accesoweb, [
                'vendedor',
                'supervisor',
                'contador',
                'gerente',
                'administrador'
            ]);
        }

        // Verificar permisos específicos por tipo de reporte
        return $this->verificarAccesoReporte($accesoweb, $tipoReporte);
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\Accesoweb  $accesoweb
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(Accesoweb $accesoweb)
    {
        // Supervisores y superiores pueden crear reportes
        return $this->verificarRol($accesoweb, [
            'supervisor',
            'contador',
            'gerente',
            'administrador'
        ]) && $this->verificarPermiso($accesoweb, 'reportes.create');
    }

    /**
     * Determine whether the user can generate reports.
     *
     * @param  \App\Models\Accesoweb  $accesoweb
     * @param  string  $tipoReporte
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function generate(Accesoweb $accesoweb, string $tipoReporte)
    {
        return $this->verificarAccesoReporte($accesoweb, $tipoReporte, 'generate');
    }

    /**
     * Determine whether the user can export the model.
     *
     * @param  \App\Models\Accesoweb  $accesoweb
     * @param  string  $tipoReporte
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function export(Accesoweb $accesoweb, string $tipoReporte)
    {
        return $this->verificarAccesoReporte($accesoweb, $tipoReporte, 'export');
    }

    /**
     * Determine whether the user can import the model.
     *
     * @param  \App\Models\Accesoweb  $accesoweb
     * @param  string  $tipoReporte
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function import(Accesoweb $accesoweb, string $tipoReporte)
    {
        // Solo contadores y administradores pueden importar reportes
        return $this->verificarRol($accesoweb, ['contador', 'administrador'])
            && $this->verificarPermiso($accesoweb, 'reportes.import');
    }

    /**
     * Determine whether the user can schedule reports.
     *
     * @param  \App\Models\Accesoweb  $accesoweb
     * @param  string  $tipoReporte
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function schedule(Accesoweb $accesoweb, string $tipoReporte)
    {
        // Solo supervisores y superiores pueden programar reportes
        return $this->verificarRol($accesoweb, ['supervisor', 'gerente', 'administrador'])
            && $this->verificarAccesoReporte($accesoweb, $tipoReporte, 'schedule');
    }

    /**
     * Determine whether the user can configure report templates.
     *
     * @param  \App\Models\Accesoweb  $accesoweb
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function configureTemplates(Accesoweb $accesoweb)
    {
        // Solo administradores pueden configurar plantillas de reportes
        return $this->verificarRol($accesoweb, ['administrador'])
            && $this->verificarPermiso($accesoweb, 'reportes.templates');
    }

    /**
     * Determine whether the user can view financial reports.
     *
     * @param  \App\Models\Accesoweb  $accesoweb
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewFinancial(Accesoweb $accesoweb)
    {
        // Contadores, gerentes y administradores pueden ver reportes financieros
        return $this->verificarRol($accesoweb, ['contador', 'gerente', 'administrador'])
            && $this->verificarPermiso($accesoweb, 'reportes.financial');
    }

    /**
     * Determine whether the user can view inventory reports.
     *
     * @param  \App\Models\Accesoweb  $accesoweb
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewInventory(Accesoweb $accesoweb)
    {
        // Vendedores, supervisores y superiores pueden ver reportes de inventario
        return $this->verificarRol($accesoweb, [
            'vendedor',
            'farmaceutico',
            'supervisor',
            'gerente',
            'administrador'
        ]) && $this->verificarPermiso($accesoweb, 'reportes.inventory');
    }

    /**
     * Determine whether the user can view sales reports.
     *
     * @param  \App\Models\Accesoweb  $accesoweb
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewSales(Accesoweb $accesoweb)
    {
        // Vendedores pueden ver sus propias ventas, supervisores pueden ver todas
        if ($accesoweb->rol === 'vendedor') {
            return $this->verificarPermiso($accesoweb, 'reportes.sales');
        }

        // Supervisores y superiores pueden ver todos los reportes de ventas
        return $this->verificarRol($accesoweb, [
            'supervisor',
            'gerente',
            'administrador'
        ]) && $this->verificarPermiso($accesoweb, 'reportes.sales');
    }

    /**
     * Determine whether the user can view regulatory reports.
     *
     * @param  \App\Models\Accesoweb  $accesoweb
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewRegulatory(Accesoweb $accesoweb)
    {
        // Farmacéuticos, supervisores y superiores pueden ver reportes regulatorios
        return $this->verificarRol($accesoweb, [
            'farmaceutico',
            'supervisor',
            'gerente',
            'administrador'
        ]) && $this->verificarPermiso($accesoweb, 'reportes.regulatory');
    }

    /**
     * Determine whether the user can view controlled substances reports.
     *
     * @param  \App\Models\Accesoweb  $accesoweb
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewControlledSubstances(Accesoweb $accesoweb)
    {
        // Solo farmacéuticos y superiores pueden ver reportes de sustancias controladas
        return $this->verificarRol($accesoweb, [
            'farmaceutico',
            'supervisor',
            'gerente',
            'administrador'
        ]) && $this->verificarPermiso($accesoweb, 'reportes.controlled_substances');
    }

    /**
     * Determine whether the user can view audit reports.
     *
     * @param  \App\Models\Accesoweb  $accesoweb
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAudit(Accesoweb $accesoweb)
    {
        // Solo auditores y administradores pueden ver reportes de auditoría
        return $this->verificarRol($accesoweb, ['auditor', 'administrador'])
            && $this->verificarPermiso($accesoweb, 'reportes.audit');
    }

    /**
     * Determine whether the user can view compliance reports.
     *
     * @param  \App\Models\Accesoweb  $accesoweb
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewCompliance(Accesoweb $accesoweb)
    {
        // Contadores, auditores y administradores pueden ver reportes de cumplimiento
        return $this->verificarRol($accesoweb, [
            'contador',
            'auditor',
            'gerente',
            'administrador'
        ]) && $this->verificarPermiso($accesoweb, 'reportes.compliance');
    }

    /**
     * Determine whether the user can view temperature reports.
     *
     * @param  \App\Models\Accesoweb  $accesoweb
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewTemperature(Accesoweb $accesoweb)
    {
        // Farmacéuticos y superiores pueden ver reportes de temperatura
        return $this->verificarRol($accesoweb, [
            'farmaceutico',
            'supervisor',
            'gerente',
            'administrador'
        ]) && $this->verificarPermiso($accesoweb, 'reportes.temperature');
    }

    /**
     * Determine whether the user can view expiration reports.
     *
     * @param  \App\Models\Accesoweb  $accesoweb
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewExpiration(Accesoweb $accesoweb)
    {
        // Todos los roles pueden ver reportes de vencimiento
        return $this->verificarPermiso($accesoweb, 'reportes.expiration');
    }

    /**
     * Determine whether the user can view user activity reports.
     *
     * @param  \App\Models\Accesoweb  $accesoweb
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewUserActivity(Accesoweb $accesoweb)
    {
        // Supervisores y superiores pueden ver reportes de actividad de usuarios
        return $this->verificarRol($accesoweb, [
            'supervisor',
            'gerente',
            'administrador'
        ]) && $this->verificarPermiso($accesoweb, 'reportes.user_activity');
    }

    /**
     * Determine whether the user can view system performance reports.
     *
     * @param  \App\Models\Accesoweb  $accesoweb
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewSystemPerformance(Accesoweb $accesoweb)
    {
        // Gerentes y administradores pueden ver reportes de rendimiento del sistema
        return $this->verificarRol($accesoweb, ['gerente', 'administrador'])
            && $this->verificarPermiso($accesoweb, 'reportes.system_performance');
    }

    /**
     * Determine whether the user can share reports.
     *
     * @param  \App\Models\Accesoweb  $accesoweb
     * @param  string  $tipoReporte
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function share(Accesoweb $accesoweb, string $tipoReporte)
    {
        // Solo supervisores y superiores pueden compartir reportes
        return $this->verificarRol($accesoweb, [
            'supervisor',
            'gerente',
            'administrador'
        ]) && $this->verificarAccesoReporte($accesoweb, $tipoReporte, 'share');
    }

    /**
     * Determine whether the user can comment on reports.
     *
     * @param  \App\Models\Accesoweb  $accesoweb
     * @param  string  $tipoReporte
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function comment(Accesoweb $accesoweb, string $tipoReporte)
    {
        // Solo usuarios con rol suficiente pueden comentar en reportes
        return $this->verificarRol($accesoweb, [
            'supervisor',
            'contador',
            'auditor',
            'gerente',
            'administrador'
        ]) && $this->verificarAccesoReporte($accesoweb, $tipoReporte, 'comment');
    }

    /**
     * Determine whether the user can archive reports.
     *
     * @param  \App\Models\Accesoweb  $accesoweb
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function archive(Accesoweb $accesoweb)
    {
        // Solo administradores pueden archivar reportes
        return $this->verificarRol($accesoweb, ['administrador'])
            && $this->verificarPermiso($accesoweb, 'reportes.archive');
    }

    /**
     * Verificar acceso a un tipo de reporte específico
     *
     * @param Accesoweb $accesoweb
     * @param string $tipoReporte
     * @param string $accion
     * @return bool
     */
    private function verificarAccesoReporte(Accesoweb $accesoweb, string $tipoReporte, string $accion = 'view'): bool
    {
        $permisoBase = "reportes.{$tipoReporte}.{$accion}";
        
        // Verificar si el usuario tiene el permiso específico
        if (!$this->verificarPermiso($accesoweb, $permisoBase)) {
            return false;
        }

        // Verificaciones adicionales por tipo de reporte
        switch ($tipoReporte) {
            case 'financial':
                return $this->verificarRol($accesoweb, ['contador', 'gerente', 'administrador']);
            
            case 'controlled_substances':
                return $this->verificarRol($accesoweb, [
                    'farmaceutico', 'supervisor', 'gerente', 'administrador'
                ]);
            
            case 'audit':
                return $this->verificarRol($accesoweb, ['auditor', 'administrador']);
            
            case 'compliance':
                return $this->verificarRol($accesoweb, [
                    'contador', 'auditor', 'gerente', 'administrador'
                ]);
            
            case 'temperature':
                return $this->verificarRol($accesoweb, [
                    'farmaceutico', 'supervisor', 'gerente', 'administrador'
                ]);
            
            case 'user_activity':
                return $this->verificarRol($accesoweb, [
                    'supervisor', 'gerente', 'administrador'
                ]);
            
            case 'sales':
                if ($accesoweb->rol === 'vendedor' && $accion === 'view') {
                    return true; // Los vendedores pueden ver sus propias ventas
                }
                return $this->verificarRol($accesoweb, [
                    'supervisor', 'gerente', 'administrador'
                ]);
            
            default:
                // Para otros tipos de reporte, verificar rol general
                return $this->verificarRol($accesoweb, [
                    'vendedor',
                    'farmaceutico',
                    'supervisor',
                    'contador',
                    'auditor',
                    'gerente',
                    'administrador'
                ]);
        }
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
}