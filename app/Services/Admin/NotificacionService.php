<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class NotificacionService
{
    public function obtenerNotificaciones($filtros = [])
    {
        $query = DB::table('notificaciones')
            ->leftJoin('accesoweb', 'notificaciones.usuario_id', '=', 'accesoweb.idusuario')
            ->select(
                'notificaciones.*',
                'accesoweb.usuario as usuario_nombre'
            )
            ->orderBy('notificaciones.created_at', 'desc');

        if (!empty($filtros['tipo'])) {
            $query->where('notificaciones.tipo', $filtros['tipo']);
        }
        if (isset($filtros['leida']) && $filtros['leida'] !== '') {
            $query->where('notificaciones.leida', $filtros['leida']);
        }
        if (!empty($filtros['fecha_inicio'])) {
            $query->whereDate('notificaciones.created_at', '>=', $filtros['fecha_inicio']);
        }
        if (!empty($filtros['fecha_fin'])) {
            $query->whereDate('notificaciones.created_at', '<=', $filtros['fecha_fin']);
        }

        return $query->limit(100)->get();
    }

    public function obtenerEstadisticas()
    {
        $hoy = Carbon::today();

        return [
            'total' => DB::table('notificaciones')->count(),
            'no_leidas' => DB::table('notificaciones')->where('leida', 0)->count(),
            'criticas' => DB::table('notificaciones')->where('tipo', 'CRITICO')->where('leida', 0)->count(),
            'hoy' => DB::table('notificaciones')->whereDate('created_at', $hoy)->count(),
            'alertas' => DB::table('notificaciones')->where('tipo', 'ALERTA')->where('leida', 0)->count(),
        ];
    }

    public function crearNotificacion($datos)
    {
        try {
            DB::table('notificaciones')->insert([
                'usuario_id' => $datos['usuario_id'] ?? null,
                'tipo' => $datos['tipo'],
                'titulo' => $datos['titulo'],
                'mensaje' => $datos['mensaje'],
                'icono' => $datos['icono'] ?? 'fa-bell',
                'color' => $datos['color'] ?? 'info',
                'url' => $datos['url'] ?? null,
                'leida' => 0,
                'leida_en' => null,
                'metadata' => isset($datos['metadata']) ? json_encode($datos['metadata']) : null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            return true;
        } catch (\Exception $e) {
            \Log::error('Error al crear notificación: ' . $e->getMessage());
            return false;
        }
    }

    public function marcarComoLeida($id)
    {
        try {
            DB::table('notificaciones')
                ->where('id', $id)
                ->update([
                    'leida' => 1,
                    'leida_en' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            return true;
        } catch (\Exception $e) {
            \Log::error('Error al marcar como leída: ' . $e->getMessage());
            return false;
        }
    }

    public function marcarTodasComoLeidas($usuarioId)
    {
        try {
            DB::table('notificaciones')
                ->where(function ($q) use ($usuarioId) {
                    $q->where('usuario_id', $usuarioId)->orWhereNull('usuario_id');
                })
                ->where('leida', 0)
                ->update([
                    'leida' => 1,
                    'leida_en' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            return true;
        } catch (\Exception $e) {
            \Log::error('Error al marcar todas como leídas: ' . $e->getMessage());
            return false;
        }
    }

    public function eliminarNotificacion($id)
    {
        try {
            DB::table('notificaciones')->where('id', $id)->delete();
            return true;
        } catch (\Exception $e) {
            \Log::error('Error al eliminar notificación: ' . $e->getMessage());
            return false;
        }
    }

    public function obtenerNoLeidas($usuarioId, $limite = 10)
    {
        return DB::table('notificaciones')
            ->where(function ($q) use ($usuarioId) {
                $q->where('usuario_id', $usuarioId)->orWhereNull('usuario_id');
            })
            ->where('leida', 0)
            ->orderBy('created_at', 'desc')
            ->limit($limite)
            ->get();
    }

    public function obtenerEstadisticasDetalladas()
    {
        $mesActual = Carbon::now()->startOfMonth();

        return [
            'por_tipo' => DB::table('notificaciones')
                ->select('tipo', DB::raw('COUNT(*) as total'))
                ->where('created_at', '>=', $mesActual)
                ->groupBy('tipo')
                ->get(),
            'por_dia' => DB::table('notificaciones')
                ->select(
                    DB::raw('CAST(created_at AS DATE) as fecha'),
                    DB::raw('COUNT(*) as total')
                )
                ->where('created_at', '>=', $mesActual)
                ->groupBy(DB::raw('CAST(created_at AS DATE)'))
                ->orderBy('fecha', 'asc')
                ->get(),
        ];
    }

    public function obtenerNotificacionesPorTipo()
    {
        return DB::table('notificaciones')
            ->select('tipo', DB::raw('COUNT(*) as total'))
            ->groupBy('tipo')
            ->orderByDesc('total')
            ->get();
    }

    public function obtenerNotificacionesRecientes($limite = 20)
    {
        return DB::table('notificaciones')
            ->orderBy('created_at', 'desc')
            ->limit($limite)
            ->get();
    }

    public function obtenerUsuariosDisponibles()
    {
        return DB::table('accesoweb')
            ->where('usuario', '!=', '')
            ->orderBy('usuario', 'asc')
            ->get();
    }

    public function generarNotificacionesAutomaticas()
    {
        $creadas = 0;

        // Productos por vencer
        $productosVencer = DB::table('v_productos_por_vencer')
            ->where('DiasParaVencer', '<=', 30)
            ->count();
        if ($productosVencer > 0) {
            $this->crearNotificacion([
                'tipo' => 'ALERTA',
                'titulo' => 'Productos próximos a vencer',
                'mensaje' => "Hay {$productosVencer} productos que vencen en los próximos 30 días",
                'url' => route('admin.reportes.productos-vencer'),
                'icono' => 'fa-exclamation-triangle',
                'color' => 'warning',
            ]);
            $creadas++;
        }

        // Cuentas por cobrar críticas
        $cuentasVencidas = DB::table('v_aging_cartera')
            ->where('rango', '90+')
            ->count();
        if ($cuentasVencidas > 0) {
            $this->crearNotificacion([
                'tipo' => 'CRITICO',
                'titulo' => 'Cuentas por cobrar críticas',
                'mensaje' => "Hay {$cuentasVencidas} facturas con más de 90 días de vencimiento",
                'url' => route('admin.reportes.cuentas-cobrar'),
                'icono' => 'fa-hand-holding-usd',
                'color' => 'danger',
            ]);
            $creadas++;
        }

        // Saldo bancario bajo
        $saldosBajos = DB::table('v_saldos_bancarios_actuales')
            ->join('Bancos', 'v_saldos_bancarios_actuales.Cuenta', '=', 'Bancos.Cuenta')
            ->where('v_saldos_bancarios_actuales.saldo_actual', '<', 1000)
            ->count();
        if ($saldosBajos > 0) {
            $this->crearNotificacion([
                'tipo' => 'ALERTA',
                'titulo' => 'Saldos bancarios bajos',
                'mensaje' => "Hay {$saldosBajos} cuentas con saldo inferior a S/ 1,000",
                'url' => route('admin.bancos.saldos'),
                'icono' => 'fa-exclamation-circle',
                'color' => 'warning',
            ]);
            $creadas++;
        }

        // Intentos de acceso fallidos
        $intentosFallidos = DB::table('libro_diario_auditoria')
            ->where('accion', 'ACCESO_DENEGADO')
            ->whereDate('fecha_hora', Carbon::today())
            ->count();
        if ($intentosFallidos > 5) {
            $this->crearNotificacion([
                'tipo' => 'CRITICO',
                'titulo' => 'Múltiples intentos de acceso fallidos',
                'mensaje' => "Se detectaron {$intentosFallidos} intentos de acceso no autorizados hoy",
                'url' => route('admin.auditoria.index'),
                'icono' => 'fa-shield-alt',
                'color' => 'danger',
            ]);
            $creadas++;
        }

        return ['creadas' => $creadas];
    }
}