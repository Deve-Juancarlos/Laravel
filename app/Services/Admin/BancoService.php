<?php

namespace App\Services\Admin;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BancoService
{
    /**
     * Obtener todas las cuentas bancarias con sus saldos actuales
     */
    public function obtenerCuentasConSaldos()
    {
        return DB::table('v_saldos_bancarios_actuales')
            ->join('Bancos', 'Bancos.Cuenta', '=', 'v_saldos_bancarios_actuales.Cuenta')
            ->select(
                'Bancos.Cuenta',
                'Bancos.Banco',
                'Bancos.Moneda',
                'v_saldos_bancarios_actuales.total_ingresos',
                'v_saldos_bancarios_actuales.total_egresos',
                'v_saldos_bancarios_actuales.saldo_actual as saldoactual',
                'v_saldos_bancarios_actuales.ultima_actualizacion as ultimaactualizacion'
            )
            ->orderBy('Bancos.Banco')
            ->orderBy('Bancos.Cuenta')
            ->get();
    }

    /**
     * Obtener resumen general de todas las cuentas
     */
    public function obtenerResumenGeneral()
    {
        // Saldos por moneda
        $saldos = DB::table('v_saldos_bancarios_actuales')
            ->join('Bancos', 'Bancos.Cuenta', '=', 'v_saldos_bancarios_actuales.Cuenta')
            ->select(
                DB::raw('SUM(CASE WHEN Bancos.Moneda = 1 THEN v_saldos_bancarios_actuales.saldo_actual ELSE 0 END) as saldo_soles'),
                DB::raw('SUM(CASE WHEN Bancos.Moneda = 2 THEN v_saldos_bancarios_actuales.saldo_actual ELSE 0 END) as saldo_dolares')
            )
            ->first();

        // Movimientos del mes
        $inicioMes = Carbon::now()->startOfMonth();
        $finMes = Carbon::now()->endOfMonth();

        $movimientosMes = DB::table('CtaBanco')
            ->select(
                DB::raw('SUM(CASE WHEN Tipo = 1 THEN Monto ELSE 0 END) as total_ingresos_mes'),
                DB::raw('SUM(CASE WHEN Tipo = 2 THEN Monto ELSE 0 END) as total_egresos_mes')
            )
            ->whereBetween('Fecha', [$inicioMes, $finMes])
            ->first();

        return [
            'total_cuentas' => DB::table('Bancos')->count(),
            'cuentas_activas' => DB::table('Bancos')->count(), // No hay columna 'Estado'
            'saldo_total_soles' => $saldos->saldo_soles ?? 0,
            'saldo_total_dolares' => $saldos->saldo_dolares ?? 0,
            'total_ingresos_mes' => $movimientosMes->total_ingresos_mes ?? 0,
            'total_egresos_mes' => $movimientosMes->total_egresos_mes ?? 0,
        ];
    }

    /**
     * Obtener datos de una cuenta específica
     */
    public function obtenerCuenta($cuenta)
    {
        return DB::table('v_saldos_bancarios_actuales')
            ->join('Bancos', 'Bancos.Cuenta', '=', 'v_saldos_bancarios_actuales.Cuenta')
            ->select(
                'Bancos.*',
                'v_saldos_bancarios_actuales.total_ingresos',
                'v_saldos_bancarios_actuales.total_egresos',
                'v_saldos_bancarios_actuales.saldo_actual as saldoactual',
                'v_saldos_bancarios_actuales.ultima_actualizacion as ultimaactualizacion'
            )
            ->where('Bancos.Cuenta', $cuenta)
            ->first();
    }

    /**
     * Obtener movimientos de una cuenta con filtros
     */
    public function obtenerMovimientos($cuenta, $filtros = [])
    {
        $query = DB::table('v_movimientos_con_saldo')
            ->where('Cuenta', $cuenta)
            ->orderBy('Fecha', 'desc');

        if (!empty($filtros['fecha_inicio'])) {
            $query->whereDate('Fecha', '>=', $filtros['fecha_inicio']);
        }
        if (!empty($filtros['fecha_fin'])) {
            $query->whereDate('Fecha', '<=', $filtros['fecha_fin']);
        }
        if (!empty($filtros['tipo'])) {
            $query->where('Tipo', $filtros['tipo']); // 1 o 2
        }
        if (!empty($filtros['clase'])) {
            $query->where('Clase', $filtros['clase']); // 1, 2, 3...
        }

        return $query->get();
    }

    /**
     * Estadísticas de una cuenta en un rango de fechas
     */
    public function obtenerEstadisticasCuenta($cuenta, $filtros = [])
    {
        $inicio = $filtros['fecha_inicio'] ?? Carbon::now()->startOfMonth()->toDateString();
        $fin = $filtros['fecha_fin'] ?? Carbon::now()->endOfMonth()->toDateString();

        $ingresos = DB::table('CtaBanco')
            ->where('Cuenta', $cuenta)
            ->where('Tipo', 1)
            ->whereBetween('Fecha', [$inicio, $fin])
            ->sum('Monto');

        $egresos = DB::table('CtaBanco')
            ->where('Cuenta', $cuenta)
            ->where('Tipo', 2)
            ->whereBetween('Fecha', [$inicio, $fin])
            ->sum('Monto');

        $cantIngresos = DB::table('CtaBanco')
            ->where('Cuenta', $cuenta)
            ->where('Tipo', 1)
            ->whereBetween('Fecha', [$inicio, $fin])
            ->count();

        $cantEgresos = DB::table('CtaBanco')
            ->where('Cuenta', $cuenta)
            ->where('Tipo', 2)
            ->whereBetween('Fecha', [$inicio, $fin])
            ->count();

        return [
            'total_ingresos' => $ingresos,
            'total_egresos' => $egresos,
            'cantidad_ingresos' => $cantIngresos,
            'cantidad_egresos' => $cantEgresos,
        ];
    }

    /**
     * Estadísticas detalladas por período
     */
    public function obtenerEstadisticasDetalladas($periodo = 'mes')
    {
        $fechaInicio = match ($periodo) {
            'dia' => Carbon::today(),
            'semana' => Carbon::now()->startOfWeek(),
            'mes' => Carbon::now()->startOfMonth(),
            'anio' => Carbon::now()->startOfYear(),
            default => Carbon::now()->startOfMonth(),
        };

        $porCuenta = DB::table('v_movimientos_con_saldo')
            ->select(
                'Cuenta',
                'Banco',
                DB::raw('SUM(ingreso) as total_ingresos'),
                DB::raw('SUM(egreso) as total_egresos')
            )
            ->whereDate('Fecha', '>=', $fechaInicio)
            ->groupBy('Cuenta', 'Banco')
            ->get();

        $porDia = DB::table('v_movimientos_con_saldo')
            ->select(
                'Fecha',
                DB::raw('SUM(ingreso) as ingresos'),
                DB::raw('SUM(egreso) as egresos')
            )
            ->whereDate('Fecha', '>=', $fechaInicio)
            ->groupBy('Fecha')
            ->orderBy('Fecha')
            ->get();

        return [
            'por_cuenta' => $porCuenta,
            'por_dia' => $porDia,
        ];
    }

    /**
     * Flujo de caja proyectado (saldo acumulado ya viene en la vista)
     */
    public function obtenerFlujoCaja($periodo = 'mes')
    {
        $fechaInicio = match ($periodo) {
            'dia' => Carbon::today(),
            'semana' => Carbon::now()->startOfWeek(),
            'mes' => Carbon::now()->startOfMonth(),
            'anio' => Carbon::now()->startOfYear(),
            default => Carbon::now()->startOfMonth(),
        };

        return DB::table('v_movimientos_con_saldo')
            ->select('Fecha', 'ingreso', 'egreso', 'saldo_acumulado')
            ->whereDate('Fecha', '>=', $fechaInicio)
            ->orderBy('Fecha')
            ->get();
    }

    /**
     * Saldos agrupados por banco
     */
    public function obtenerSaldosPorBanco()
    {
        return DB::table('v_saldos_bancarios_actuales')
            ->join('Bancos', 'Bancos.Cuenta', '=', 'v_saldos_bancarios_actuales.Cuenta')
            ->select(
                'Bancos.Banco',
                DB::raw('COUNT(*) as cantidad_cuentas'),
                DB::raw('SUM(v_saldos_bancarios_actuales.saldo_actual) as saldo_total')
            )
            ->groupBy('Bancos.Banco')
            ->orderByDesc('saldo_total')
            ->get();
    }

    /**
     * Saldos por moneda
     */
    public function obtenerSaldosPorMoneda()
    {
        return DB::table('v_saldos_bancarios_actuales')
            ->join('Bancos', 'Bancos.Cuenta', '=', 'v_saldos_bancarios_actuales.Cuenta')
            ->select(
                'Bancos.Moneda',
                DB::raw('SUM(v_saldos_bancarios_actuales.saldo_actual) as saldo_total')
            )
            ->groupBy('Bancos.Moneda')
            ->get();
    }

    /**
     * Liquidez disponible
     */
    public function obtenerLiquidez()
    {
        $liquidez = DB::table('v_liquidez_bancaria')->first();

        return [
            'disponible_inmediato' => $liquidez->disponible ?? 0,
            'cheques_pendientes' => $liquidez->cheques_pendientes ?? 0,
            'depositos_transito' => $liquidez->depositos_transito ?? 0,
            'liquidez_estimada' => $liquidez->liquidez_estimada ?? 0,
        ];
    }

    /**
     * Movimientos sin conciliar (Conciliado = 0)
     */
    public function obtenerMovimientosSinConciliar($cuenta, $fecha)
    {
        return DB::table('CtaBanco')
            ->where('Cuenta', $cuenta)
            ->whereDate('Fecha', '<=', $fecha)
            ->where('Conciliado', 0)
            ->orderBy('Fecha')
            ->get();
    }

    /**
     * Historial de conciliaciones
     */
    public function obtenerHistorialConciliaciones($cuenta)
    {
        return DB::table('BancosConciliacion')
            ->where('cuenta', $cuenta)
            ->orderBy('fecha_conciliacion', 'desc')
            ->get();
    }

    /**
     * Registrar conciliación
     */
    public function registrarConciliacion($cuenta, $fecha, $saldoBancario, $observaciones)
    {
        $usuario = auth()->user()?->usuario ?? 'SISTEMA';

        DB::table('BancosConciliacion')->insert([
            'cuenta' => $cuenta,
            'fecha_conciliacion' => $fecha,
            'saldo_libros' => 0, // Se calculará en el SP real
            'saldo_bancario' => $saldoBancario,
            'diferencia' => 0,
            'observaciones' => $observaciones,
            'usuario_creador' => $usuario,
            'created_at' => now(),
        ]);

        // Opcional: llamar a sp_registrar_conciliacion
        return ['success' => true];
    }

    /**
     * Cheques pendientes
     */
    public function obtenerChequesPendientes()
    {
        return DB::table('v_cheques_pendientes')
            ->join('Bancos', 'Bancos.Cuenta', '=', 'v_cheques_pendientes.Cuenta')
            ->select('v_cheques_pendientes.*', 'Bancos.Banco')
            ->get();
    }

    /**
     * Transferencias bancarias
     */
    public function obtenerTransferencias($filtros = [])
    {
        $query = DB::table('v_transferencias_bancarias');

        if (!empty($filtros['fecha_inicio'])) {
            $query->whereDate('Fecha', '>=', $filtros['fecha_inicio']);
        }
        if (!empty($filtros['fecha_fin'])) {
            $query->whereDate('Fecha', '<=', $filtros['fecha_fin']);
        }
        if (!empty($filtros['estado'])) {
            $query->where('estado_transferencia', $filtros['estado']);
        }

        return $query->get();
    }

    /**
     * Exportar movimientos
     */
    public function exportarMovimientos($cuenta, $filtros = [])
    {
        return $this->obtenerMovimientos($cuenta, $filtros);
    }
}