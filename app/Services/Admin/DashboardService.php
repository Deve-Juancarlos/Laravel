<?php

namespace App\Services\Admin;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardService
{
    protected $connection = 'sqlsrv';

    /**
     * Obtiene los KPIs (Indicadores Clave) para el Dashboard Gerencial.
     * ¡Usa las tablas contables nuevas!
     */
    public function getDashboardKpis()
    {
        $inicioMes = Carbon::now()->startOfMonth()->toDateString();
        $finMes = Carbon::now()->endOfMonth()->toDateString();

        // 1. Ventas del Mes (de Doccab)
        $totalVentas = DB::connection($this->connection)->table('Doccab')
            ->where('Eliminado', 0)
            ->whereBetween('Fecha', [$inicioMes, $finMes])
            ->whereIn('Tipo', [1, 3]) // Solo Facturas y Boletas
            ->sum('Total');
            
        // 2. Compras del Mes (de CompraCab)
        $totalCompras = DB::connection($this->connection)->table('CompraCab')
            ->where('Estado', '!=', 'ANULADO') // O como marques las anuladas
            ->whereBetween('FechaEmision', [$inicioMes, $finMes])
            ->sum('Total');
            
        // 3. Saldo Total por Cobrar (de CtaCliente)
        $saldoCxC = DB::connection($this->connection)->table('CtaCliente')
            ->where('Saldo', '>', 0)
            ->sum('Saldo');
            
        // 4. Saldo Total por Pagar (de CtaProveedor)
        $saldoCxP = DB::connection($this->connection)->table('CtaProveedor')
            ->where('Saldo', '>', 0)
            ->sum('Saldo');
            
        // 5. Órdenes Pendientes de Recepción
        $ordenesPendientes = DB::connection($this->connection)->table('OrdenCompraCab')
            ->where('Estado', 'PENDIENTE')
            ->count();
            
        // 6. Inventario Valorizado (¡GOLAZO!)
        $stockValorizado = DB::connection($this->connection)->table('Saldos as s')
            ->join('Productos as p', 's.codpro', '=', 'p.CodPro')
            ->where('s.saldo', '>', 0)
            ->where('p.Eliminado', 0)
            ->sum(DB::raw('ISNULL(s.saldo, 0) * ISNULL(p.Costo, 0)')); // Multiplica saldo * costo

        return [
            'totalVentasMes' => (float) $totalVentas,
            'totalComprasMes' => (float) $totalCompras,
            'saldoTotalCxC' => (float) $saldoCxC,
            'saldoTotalCxP' => (float) $saldoCxP,
            'ordenesPendientes' => (int) $ordenesPendientes,
            'stockValorizado' => (float) $stockValorizado,
        ];
    }
}