<?php

namespace App\Services;

use App\Models\AccesoWeb;
use App\Models\Notificacion;

class NotificacionService
{
    /**
     * Crear notificación individual
     */
    public static function crear($usuarioId, $tipo, $titulo, $mensaje, $opciones = [])
    {
        return Notificacion::create([
            'usuario_id' => $usuarioId,
            'tipo' => $tipo,
            'titulo' => $titulo,
            'mensaje' => $mensaje,
            'icono' => $opciones['icono'] ?? null,
            'color' => $opciones['color'] ?? 'info',
            'url' => $opciones['url'] ?? null,
            'metadata' => $opciones['metadata'] ?? null,
        ]);
    }

    /**
     * Notificar a todos los usuarios con roles específicos
     */
    public static function notificarPorRoles(array $roles, $tipo, $titulo, $mensaje, $opciones = [])
    {
        $usuarios = AccesoWeb::whereIn('rol', $roles)
            ->orWhere('is_admin', true) // Siempre incluir admins si aplica
            ->get();

        foreach ($usuarios as $usuario) {
            self::crear($usuario->id, $tipo, $titulo, $mensaje, $opciones);
        }
    }

    /**
     * Nueva planilla creada para roles específicos
     */
    public static function nuevaPlanilla($planillaId, $numeroPlanilla, $roles = ['admin'])
    {
        return self::notificarPorRoles(
            $roles,
            'planilla',
            'Nueva Planilla Creada',
            "Se ha creado la planilla #{$numeroPlanilla}",
            [
                'icono' => 'fa-file-invoice-dollar',
                'color' => 'success',
                'url' => route('admin.planillas.show', $planillaId),
                'metadata' => ['planilla_id' => $planillaId],
            ]
        );
    }

    /**
     * Planilla confirmada
     */
    public static function planillaConfirmada($planillaId, $numeroPlanilla, $roles = ['admin'])
    {
        return self::notificarPorRoles(
            $roles,
            'planilla',
            'Planilla Confirmada',
            "La planilla #{$numeroPlanilla} ha sido confirmada",
            [
                'icono' => 'fa-check-circle',
                'color' => 'success',
                'url' => route('admin.planillas.show', $planillaId),
                'metadata' => ['planilla_id' => $planillaId],
            ]
        );
    }

    /**
     * Nuevo pago registrado
     */
    public static function nuevoPago($usuarioId, $pagoId, $monto, $concepto)
    {
        return self::crear(
            $usuarioId,
            'pago',
            'Nuevo Pago Registrado',
            "Se registró un pago de S/ {$monto} - {$concepto}",
            [
                'icono' => 'fa-money-bill-wave',
                'color' => 'success',
                'url' => null,
                'metadata' => ['pago_id' => $pagoId, 'monto' => $monto]
            ]
        );
    }

    /**
     * Nuevo movimiento bancario
     */
    public static function nuevoMovimiento($usuarioId, $tipo, $monto, $descripcion)
    {
        $colorPorTipo = [
            'ingreso' => 'success',
            'egreso' => 'danger',
            'cobranza' => 'info',
        ];

        return self::crear(
            $usuarioId,
            'movimiento',
            'Nuevo Movimiento Bancario',
            "{$descripcion}: S/ {$monto}",
            [
                'icono' => 'fa-exchange-alt',
                'color' => $colorPorTipo[$tipo] ?? 'info',
                'url' => route('admin.reportes.movimientos'),
                'metadata' => ['tipo' => $tipo, 'monto' => $monto]
            ]
        );
    }

    /**
     * Alertas generales a roles específicos
     */
    public static function alerta($roles, $titulo, $mensaje, $gravedad = 'warning')
    {
        return self::notificarPorRoles(
            $roles,
            'alerta',
            $titulo,
            $mensaje,
            [
                'icono' => 'fa-exclamation-triangle',
                'color' => $gravedad,
            ]
        );
    }

    /**
     * Cuenta bancaria actualizada
     */
    public static function bancoActualizado($usuarioId, $nombreBanco, $accion)
    {
        return self::crear(
            $usuarioId,
            'banco',
            'Cuenta Bancaria ' . ucfirst($accion),
            "La cuenta {$nombreBanco} ha sido {$accion}",
            [
                'icono' => 'fa-university',
                'color' => $accion === 'creada' ? 'success' : 'info',
                'url' => route('admin.bancos.index'),
            ]
        );
    }

    /**
     * Notificar todos los administradores (método legado, opcional)
     */
    public static function notificarAdministradores($tipo, $titulo, $mensaje, $opciones = [])
    {
        return self::notificarPorRoles(['admin'], $tipo, $titulo, $mensaje, $opciones);
    }

    public static function notificarRoles(array $roles, $tipo, $titulo, $mensaje, $opciones = [])
    {
        $usuarios = AccesoWeb::whereIn('rol', $roles)
            ->orWhere('is_admin', true) // opcional: siempre incluir admins
            ->get();

        foreach ($usuarios as $user) {
            self::crear($user->id, $tipo, $titulo, $mensaje, $opciones);
        }
    }

}
