<?php

namespace App\Services\Admin;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardService
{
    protected $connection = 'sqlsrv';

    /**
     * Obtiene los datos financieros y operativos para el Dashboard Gerencial.
     */
    public function getDashboardData()
    {
        $inicioMes = Carbon::now()->startOfMonth();
        $finMes = Carbon::now()->endOfMonth();
        
        // --- 1. CÁLCULO DE KPIs FINANCIEROS Y OPERATIVOS ---

        // KPIs de Rentabilidad
        $totalVentas = (float) DB::connection($this->connection)->table('Doccab')
            ->where('Eliminado', 0)->whereIn('Tipo', [1, 3])
            ->whereBetween('Fecha', [$inicioMes->toDateString(), $finMes->toDateString()])
            ->sum('Total');

        $costoVentas = (float) DB::connection($this->connection)->table('Docdet as dd')
            ->join('Doccab as dc', 'dd.Numero', '=', 'dc.Numero')
            ->where('dc.Eliminado', 0)->whereIn('dc.Tipo', [1, 3])
            ->whereBetween('dc.Fecha', [$inicioMes->toDateString(), $finMes->toDateString()])
            ->sum(DB::raw('ISNULL(dd.Cantidad, 0) * ISNULL(dd.Costo, 0)'));
        
        $utilidadBruta = $totalVentas - $costoVentas;
        $margenBruto = ($totalVentas > 0) ? ($utilidadBruta / $totalVentas) * 100 : 0;

        // KPIs de Liquidez y Flujo de Caja
        $ingresosCaja = (float) DB::connection($this->connection)->table('CtaBanco')
            ->where('Tipo', 1)->whereBetween('Fecha', [$inicioMes->toDateString(), $finMes->toDateString()])
            ->sum('Monto');

        $egresosCaja = (float) DB::connection($this->connection)->table('CtaBanco')
            ->where('Tipo', 2)->whereBetween('Fecha', [$inicioMes->toDateString(), $finMes->toDateString()])
            ->sum('Monto');

        $flujoCajaNeto = $ingresosCaja - $egresosCaja;
        
        $saldoTotalBancos = (float) DB::connection($this->connection)->table('CtaBanco')
             ->selectRaw('ISNULL(SUM(CASE WHEN Tipo = 1 THEN Monto ELSE -Monto END), 0) as saldo')
             ->value('saldo');

        // KPIs de Cuentas por Cobrar y Pagar
        $saldoCxC = (float) DB::connection($this->connection)->table('CtaCliente')->where('Saldo', '>', 0)->sum('Saldo');
        $saldoCxP = (float) DB::connection($this->connection)->table('CtaProveedor')->where('Saldo', '>', 0)->sum('Saldo');
        
        // KPI de Inventario
        $stockValorizado = (float) DB::connection($this->connection)->table('Saldos as s')
            ->join('Productos as p', 's.codpro', '=', 'p.CodPro')
            ->where('s.saldo', '>', 0)->where('p.Eliminado', 0)
            ->sum(DB::raw('ISNULL(s.saldo, 0) * ISNULL(p.Costo, 0)'));
            
        // Ratio Financiero Clave
        $activosCorrientes = $saldoTotalBancos + $saldoCxC + $stockValorizado;
        $ratioLiquidez = ($saldoCxP > 0) ? $activosCorrientes / $saldoCxP : $activosCorrientes;

        // --- 2. DATOS PARA GRÁFICOS GERENCIALES ---

        // Gráfico 1: Antigüedad de Cuentas por Cobrar (Aging) - CORREGIDO
        $agingCxC = DB::connection($this->connection)->table('CtaCliente')
            ->where('Saldo', '>', 0)
            ->select(DB::raw("
                CASE
                    WHEN DATEDIFF(day, FechaDoc, GETDATE()) <= 30 THEN '0-30'
                    WHEN DATEDIFF(day, FechaDoc, GETDATE()) BETWEEN 31 AND 60 THEN '31-60'
                    WHEN DATEDIFF(day, FechaDoc, GETDATE()) BETWEEN 61 AND 90 THEN '61-90'
                    ELSE '90+'
                END as rango,
                SUM(Saldo) as total
            "))
            ->groupBy(DB::raw("CASE
                    WHEN DATEDIFF(day, FechaDoc, GETDATE()) <= 30 THEN '0-30'
                    WHEN DATEDIFF(day, FechaDoc, GETDATE()) BETWEEN 31 AND 60 THEN '31-60'
                    WHEN DATEDIFF(day, FechaDoc, GETDATE()) BETWEEN 61 AND 90 THEN '61-90'
                    ELSE '90+'
                END"))
            ->orderByRaw("MIN(DATEDIFF(day, FechaDoc, GETDATE()))")
            ->get();

        // Gráfico 2: Antigüedad de Cuentas por Pagar (Aging) - CORREGIDO
        $agingCxP = DB::connection($this->connection)->table('CtaProveedor')
            ->where('Saldo', '>', 0)
            ->select(DB::raw("
                CASE
                    WHEN DATEDIFF(day, FechaEmision, GETDATE()) <= 30 THEN '0-30'
                    WHEN DATEDIFF(day, FechaEmision, GETDATE()) BETWEEN 31 AND 60 THEN '31-60'
                    WHEN DATEDIFF(day, FechaEmision, GETDATE()) BETWEEN 61 AND 90 THEN '61-90'
                    ELSE '90+'
                END as rango,
                SUM(Saldo) as total
            "))
            ->groupBy(DB::raw("CASE
                    WHEN DATEDIFF(day, FechaEmision, GETDATE()) <= 30 THEN '0-30'
                    WHEN DATEDIFF(day, FechaEmision, GETDATE()) BETWEEN 31 AND 60 THEN '31-60'
                    WHEN DATEDIFF(day, FechaEmision, GETDATE()) BETWEEN 61 AND 90 THEN '61-90'
                    ELSE '90+'
                END"))
            ->orderByRaw("MIN(DATEDIFF(day, FechaEmision, GETDATE()))")
            ->get();
            
        // Gráfico 3: Ventas vs Compras (Últimos 6 meses)
        $meses = [];
        $ventasPorMes = [];
        for ($i = 5; $i >= 0; $i--) {
            $fecha = Carbon::now()->subMonths($i);
            $meses[] = $fecha->format('M Y');
            $inicio = $fecha->startOfMonth()->toDateString();
            $fin = $fecha->endOfMonth()->toDateString();

            $ventasPorMes[] = (float) DB::connection($this->connection)->table('Doccab')
                ->where('Eliminado', 0)->whereIn('Tipo', [1, 3])
                ->whereBetween('Fecha', [$inicio, $fin])->sum('Total');
        }
        $chartVentasHistorico = ['labels' => $meses, 'ventas' => $ventasPorMes];


        // --- 3. EMPAQUETAR DATOS PARA LA VISTA ---
        return [
            'kpis' => [
                'flujoCajaNeto' => $flujoCajaNeto,
                'ratioLiquidez' => $ratioLiquidez,
                'margenBruto' => $margenBruto,
                'totalVentasMes' => $totalVentas,
                'saldoTotalCxC' => $saldoCxC,
                'saldoTotalCxP' => $saldoCxP,
                'stockValorizado' => $stockValorizado,
            ],
            'charts' => [
                'agingCxC' => $agingCxC,
                'agingCxP' => $agingCxP,
                'ventasHistorico' => $chartVentasHistorico,
            ]
        ];
    }
}
