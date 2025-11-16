<?php

namespace App\Services\Admin;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardService
{
    /**
     * KPIs generales del negocio
     * Usa vistas y tablas reales de tu base de datos
     */
    public function obtenerKPIsGenerales()
{
    $hoy = Carbon::today();
    $mesActual = Carbon::now()->startOfMonth();

    return [
        // Ventas del día usando tabla Doccab
        'ventas_hoy' => DB::table('Doccab')
            ->whereDate('Fecha', $hoy)
            ->where('Eliminado', 0)
            ->sum('Total'),
            
        // Ventas del mes actual
        'ventas_mes' => DB::table('Doccab')
            ->whereMonth('Fecha', $mesActual->month)
            ->whereYear('Fecha', $mesActual->year)
            ->where('Eliminado', 0)
            ->sum('Total'),
            
        // Total por cobrar (CtaCliente con Saldo > 0)
        'total_por_cobrar' => DB::table('CtaCliente')
            ->where('Saldo', '>', 0)
            ->sum('Saldo'),
            
        // Cuentas vencidas
        'cuentas_vencidas' => DB::table('CtaCliente')
            ->where('Saldo', '>', 0)
            ->where('FechaV', '<', $hoy)
            ->sum('Saldo'),
            
        // Total por pagar a proveedores
        'total_por_pagar' => DB::table('CtaProveedor')
            ->where('Saldo', '>', 0)
            ->sum('Saldo'),
            
        // Valor del inventario (Saldos * Costo)
        'valor_inventario' => DB::table('Saldos')
            ->join('Productos', 'Saldos.codpro', '=', 'Productos.CodPro')
            ->where('Productos.Eliminado', 0)
            ->where('Saldos.saldo', '>', 0)
            ->selectRaw('SUM(Saldos.saldo * Productos.Costo) as total')
            ->value('total') ?? 0,
            
        // ✅ CORRECTO - Saldo en bancos usando la vista vsaldosbancariosactuales
        'saldo_bancos' => DB::table('dbo.v_saldos_bancarios_actuales')->sum('saldo_actual'),

            
        // Saldo en caja (del día)
        'saldo_caja' => DB::table('Caja')
            ->whereDate('Fecha', $hoy)
            ->where('Eliminado', 0)
            ->sum('Monto'),
            
        // Utilidad estimada del mes (ventas - costos)
        'utilidad_mes' => $this->calcularUtilidadMes($mesActual),
    ];
}

    /**
     * Ventas del día actual con detalles
     */
    public function ventasDelDia()
    {
        $hoy = Carbon::today();
        
        return DB::table('Doccab')
            ->join('Clientes', 'Doccab.CodClie', '=', 'Clientes.Codclie')
            ->select(
                'Doccab.Numero',
                'Doccab.Tipo',
                'Doccab.Fecha',
                'Clientes.Razon as cliente_nombre',
                'Doccab.Total',
                'Doccab.estado_sunat'
            )
            ->whereDate('Doccab.Fecha', $hoy)
            ->where('Doccab.Eliminado', 0)
            ->orderBy('Doccab.Fecha', 'desc')
            ->limit(20)
            ->get();
    }

    /**
     * Resumen de ventas del mes
     */
    public function ventasDelMes()
    {
        $mesActual = Carbon::now()->startOfMonth();
        
        return [
            'total' => DB::table('Doccab')
                ->whereMonth('Fecha', $mesActual->month)
                ->whereYear('Fecha', $mesActual->year)
                ->where('Eliminado', 0)
                ->sum('Total'),
                
            'cantidad_documentos' => DB::table('Doccab')
                ->whereMonth('Fecha', $mesActual->month)
                ->whereYear('Fecha', $mesActual->year)
                ->where('Eliminado', 0)
                ->count(),
                
            'ticket_promedio' => DB::table('Doccab')
                ->whereMonth('Fecha', $mesActual->month)
                ->whereYear('Fecha', $mesActual->year)
                ->where('Eliminado', 0)
                ->avg('Total'),
                
            'por_tipo_doc' => DB::table('Doccab')
                ->join('TiposDocumentoSUNAT', 'Doccab.tipo_documento_sunat', '=', 'TiposDocumentoSUNAT.Codigo')
                ->selectRaw('TiposDocumentoSUNAT.Descripcion, SUM(Doccab.Total) as total, COUNT(*) as cantidad')
                ->whereMonth('Doccab.Fecha', $mesActual->month)
                ->whereYear('Doccab.Fecha', $mesActual->year)
                ->where('Doccab.Eliminado', 0)
                ->groupBy('TiposDocumentoSUNAT.Descripcion')
                ->get(),
        ];
    }

    /**
     * Alertas críticas del sistema
     */
    public function alertasCriticas()
    {
        $alertas = [];
        $hoy = Carbon::today();

        // 1. Productos con stock mínimo (usando campo Minimo de Productos)
        $stockMinimo = DB::table('Productos')
            ->join('Saldos', 'Productos.CodPro', '=', 'Saldos.codpro')
            ->select('Productos.CodPro')
            ->groupBy('Productos.CodPro', 'Productos.Minimo')
            ->havingRaw('SUM(Saldos.saldo) <= Productos.Minimo')
            ->count();
            
        if ($stockMinimo > 0) {
            $alertas[] = [
                'tipo' => 'danger',
                'icono' => 'box',
                'mensaje' => "{$stockMinimo} productos con stock mínimo",
               # 'url' => route('admin.inventario.stock-minimo')
            ];
        }

        // 2. Productos próximos a vencer (usando vista v_productos_por_vencer)
        $productosVencer = DB::table('dbo.v_productos_por_vencer')
        ->where('DiasParaVencer', '<=', 30)
        ->where('DiasParaVencer', '>', 0)
        ->count();

            
        if ($productosVencer > 0) {
            $alertas[] = [
                'tipo' => 'warning',
                'icono' => 'calendar-alert',
                'mensaje' => "{$productosVencer} productos próximos a vencer (30 días)",
               # 'url' => route('admin.inventario.productos-vencer')
            ];
        }

        // 3. Productos VENCIDOS
        $productosVencidos = DB::table('Saldos')
            ->where('vencimiento', '<', $hoy)
            ->where('saldo', '>', 0)
            ->count();
            
        if ($productosVencidos > 0) {
            $alertas[] = [
                'tipo' => 'danger',
                'icono' => 'x-circle',
                'mensaje' => "{$productosVencidos} lotes de productos VENCIDOS",
                #'url' => route('admin.inventario.productos-vencidos')
            ];
        }

        // 4. Cuentas vencidas (clientes morosos) usando vista v_aging_cartera
        $cuentasVencidas = DB::table('dbo.v_aging_cartera')
            ->where('rango', '!=', 'VIGENTE')
            ->count();
            
        if ($cuentasVencidas > 0) {
            $montoVencido = DB::table('dbo.v_aging_cartera')
                ->where('rango', '!=', 'VIGENTE')
                ->sum('Saldo');
                
            $alertas[] = [
                'tipo' => 'danger',
                'icono' => 'alert-circle',
                'mensaje' => "{$cuentasVencidas} cuentas vencidas (S/ " . number_format($montoVencido, 2) . ")",
              #  'url' => route('admin.cuentas-cobrar.vencidas')
            ];
        }

        // 5. Pagos a proveedores próximos a vencer (7 días)
        $pagosPendientes = DB::table('CtaProveedor')
            ->where('Saldo', '>', 0)
            ->whereBetween('FechaV', [$hoy, $hoy->copy()->addDays(7)])
            ->count();
            
        if ($pagosPendientes > 0) {
            $montoPendiente = DB::table('CtaProveedor')
                ->where('Saldo', '>', 0)
                ->whereBetween('FechaV', [$hoy, $hoy->copy()->addDays(7)])
                ->sum('Saldo');
                
            $alertas[] = [
                'tipo' => 'info',
                'icono' => 'credit-card',
                'mensaje' => "{$pagosPendientes} pagos a proveedores próximos (S/ " . number_format($montoPendiente, 2) . ")",
              #  'url' => route('admin.cuentas-pagar.por-vencer')
            ];
        }

        // 6. Asientos contables sin balancear
        $asientosSinBalancear = DB::table('libro_diario')
            ->where('balanceado', 0)
            ->where('estado', 'ACTIVO')
            ->count();
            
        if ($asientosSinBalancear > 0) {
            $alertas[] = [
                'tipo' => 'warning',
                'icono' => 'alert-triangle',
                'mensaje' => "{$asientosSinBalancear} asientos contables sin balancear",
              #  'url' => route('admin.contabilidad.asientos-desbalanceados')
            ];
        }

        return $alertas;
    }

    /**
     * Top 10 productos más vendidos del mes
     * Usa tablas Docdet, Doccab y Productos
     */
    public function productosTop($limite = 10)
    {
        $mesActual = Carbon::now()->startOfMonth();
        
        return DB::table('Docdet')
            ->join('Doccab', function($join) use ($mesActual) {
                $join->on('Docdet.Numero', '=', 'Doccab.Numero')
                     ->on('Docdet.Tipo', '=', 'Doccab.Tipo')
                     ->whereMonth('Doccab.Fecha', $mesActual->month)
                     ->whereYear('Doccab.Fecha', $mesActual->year)
                     ->where('Doccab.Eliminado', 0);
            })
            ->join('Productos', 'Docdet.Codpro', '=', 'Productos.CodPro')
            ->leftJoin('Laboratorios', 'Productos.CodProv', '=', 'Laboratorios.CodLab')
            ->select(
                'Productos.CodPro',
                'Productos.Nombre',
                'Laboratorios.Descripcion as laboratorio',
                DB::raw('SUM(Docdet.Cantidad) as cantidad_vendida'),
                DB::raw('SUM(Docdet.Subtotal) as total_vendido')
            )
            ->groupBy('Productos.CodPro', 'Productos.Nombre', 'Laboratorios.Descripcion')
            ->orderByDesc('total_vendido')
            ->limit($limite)
            ->get();
    }

    /**
     * Top 10 clientes del mes
     */
    public function clientesTop($limite = 10)
    {
        $mesActual = Carbon::now()->startOfMonth();
        
        return DB::table('Doccab')
            ->join('Clientes', 'Doccab.CodClie', '=', 'Clientes.Codclie')
            ->select(
                'Clientes.Codclie',
                'Clientes.Razon',
                'Clientes.Zona',
                DB::raw('COUNT(*) as total_documentos'),
                DB::raw('SUM(Doccab.Total) as total_comprado')
            )
            ->whereMonth('Doccab.Fecha', $mesActual->month)
            ->whereYear('Doccab.Fecha', $mesActual->year)
            ->where('Doccab.Eliminado', 0)
            ->groupBy('Clientes.Codclie', 'Clientes.Razon', 'Clientes.Zona')
            ->orderByDesc('total_comprado')
            ->limit($limite)
            ->get();
    }

    /**
     * Resumen de cuentas por cobrar
     */
    public function resumenCuentasPorCobrar()
    {
        $hoy = Carbon::today();
        
        return [
            'total' => DB::table('CtaCliente')->where('Saldo', '>', 0)->sum('Saldo'),
            'vencidas' => DB::table('CtaCliente')->where('Saldo', '>', 0)->where('FechaV', '<', $hoy)->sum('Saldo'),
            'por_vencer_7dias' => DB::table('CtaCliente')
                ->where('Saldo', '>', 0)
                ->whereBetween('FechaV', [$hoy, $hoy->copy()->addDays(7)])
                ->sum('Saldo'),
            'vigentes' => DB::table('CtaCliente')
                ->where('Saldo', '>', 0)
                ->where('FechaV', '>=', $hoy)
                ->sum('Saldo'),
        ];
    }

    /**
     * Resumen de cuentas por pagar
     */
    public function resumenCuentasPorPagar()
    {
        $hoy = Carbon::today();
        
        return [
            'total' => DB::table('CtaProveedor')->where('Saldo', '>', 0)->sum('Saldo'),
            'vencidas' => DB::table('CtaProveedor')->where('Saldo', '>', 0)->where('FechaV', '<', $hoy)->sum('Saldo'),
            'por_vencer_7dias' => DB::table('CtaProveedor')
                ->where('Saldo', '>', 0)
                ->whereBetween('FechaV', [$hoy, $hoy->copy()->addDays(7)])
                ->sum('Saldo'),
        ];
    }

    /**
     * Productos con stock bajo mínimo
     */
    public function productosStockMinimo()
    {
        return DB::table('Productos')
            ->join('Saldos', 'Productos.CodPro', '=', 'Saldos.codpro')
            ->leftJoin('Laboratorios', 'Productos.CodProv', '=', 'Laboratorios.CodLab')
            ->select(
                'Productos.CodPro',
                'Productos.Nombre',
                'Laboratorios.Descripcion as laboratorio',
                'Productos.Minimo as stock_minimo',
                DB::raw('SUM(Saldos.saldo) as stock_actual')
            )
            ->where('Productos.Eliminado', 0)
            ->groupBy('Productos.CodPro', 'Productos.Nombre', 'Laboratorios.Descripcion', 'Productos.Minimo')
            ->havingRaw('SUM(Saldos.saldo) <= Productos.Minimo')
            ->orderBy('stock_actual', 'asc')
            ->limit(10)
            ->get();
    }

    /**
     * Productos próximos a vencer
     * Usa la vista vproductosporvencer
     */
    public function productosProximosVencer($dias = 30)
    {
        return DB::table('dbo.v_productos_por_vencer')
            ->where('DiasParaVencer', '<=', $dias)
            ->where('DiasParaVencer', '>', 0)
            ->orderBy('DiasParaVencer', 'asc')
            ->limit(10)
            ->get();

    }

    /**
     * Gráfico: Ventas por mes (últimos 12 meses)
     */
    public function graficoVentasPorMes($meses = 12)
    {
        $fechaInicio = Carbon::now()->subMonths($meses);
        
        return DB::table('Doccab')
            ->selectRaw("
                YEAR(Fecha) as anio,
                MONTH(Fecha) as mes,
                SUM(Total) as total
            ")
            ->where('Fecha', '>=', $fechaInicio)
            ->where('Eliminado', 0)
            ->groupBy(DB::raw('YEAR(Fecha)'), DB::raw('MONTH(Fecha)'))
            ->orderBy('anio', 'asc')
            ->orderBy('mes', 'asc')
            ->get();
    }

    /**
     * Gráfico: Ventas por laboratorio
     */
    public function graficoVentasPorLaboratorio()
    {
        $mesActual = Carbon::now()->startOfMonth();
        
        return DB::table('Docdet')
            ->join('Doccab', function($join) use ($mesActual) {
                $join->on('Docdet.Numero', '=', 'Doccab.Numero')
                     ->on('Docdet.Tipo', '=', 'Doccab.Tipo')
                     ->whereMonth('Doccab.Fecha', $mesActual->month)
                     ->whereYear('Doccab.Fecha', $mesActual->year)
                     ->where('Doccab.Eliminado', 0);
            })
            ->join('Productos', 'Docdet.Codpro', '=', 'Productos.CodPro')
            ->join('Laboratorios', 'Productos.CodProv', '=', 'Laboratorios.CodLab')
            ->select(
                'Laboratorios.Descripcion as laboratorio',
                DB::raw('SUM(Docdet.Subtotal) as total')
            )
            ->groupBy('Laboratorios.Descripcion')
            ->orderByDesc('total')
            ->limit(10)
            ->get();
    }

    /**
     * Gráfico: Ventas por vendedor
     */
    public function graficoVentasPorVendedor()
    {
        $mesActual = Carbon::now()->startOfMonth();
        
        return DB::table('Doccab')
            ->join('Empleados', 'Doccab.Vendedor', '=', 'Empleados.Codemp')
            ->select(
                'Empleados.Nombre as vendedor',
                DB::raw('COUNT(*) as cantidad_ventas'),
                DB::raw('SUM(Doccab.Total) as total')
            )
            ->whereMonth('Doccab.Fecha', $mesActual->month)
            ->whereYear('Doccab.Fecha', $mesActual->year)
            ->where('Doccab.Eliminado', 0)
            ->groupBy('Empleados.Nombre')
            ->orderByDesc('total')
            ->get();
    }

    /**
     * Gráfico: Aging de cartera
     * Usa la vista vagingcartera
     */
    public function graficoAgingCartera()
    {
        $aging = DB::table('dbo.v_aging_cartera')
            ->selectRaw("
                SUM(CASE WHEN rango = 'VIGENTE' THEN Saldo ELSE 0 END) as vigente,
                SUM(CASE WHEN rango = '1-30' THEN Saldo ELSE 0 END) as dias_1_30,
                SUM(CASE WHEN rango = '31-60' THEN Saldo ELSE 0 END) as dias_31_60,
                SUM(CASE WHEN rango = '61-90' THEN Saldo ELSE 0 END) as dias_61_90,
                SUM(CASE WHEN rango = '>90' THEN Saldo ELSE 0 END) as dias_mas_90
            ")
            ->first();
            
        return $aging;
    }

    /**
     * Calcular utilidad del mes
     * (Ventas - Costos de productos vendidos)
     */
    private function calcularUtilidadMes($mesActual)
    {
        // Total de ventas del mes
        $ventas = DB::table('Doccab')
            ->whereMonth('Fecha', $mesActual->month)
            ->whereYear('Fecha', $mesActual->year)
            ->where('Eliminado', 0)
            ->sum('Total');
            
        // Costo de lo vendido (usando campo Costo de Docdet)
        $costos = DB::table('Docdet')
            ->join('Doccab', function($join) use ($mesActual) {
                $join->on('Docdet.Numero', '=', 'Doccab.Numero')
                     ->on('Docdet.Tipo', '=', 'Doccab.Tipo')
                     ->whereMonth('Doccab.Fecha', $mesActual->month)
                     ->whereYear('Doccab.Fecha', $mesActual->year)
                     ->where('Doccab.Eliminado', 0);
            })
            ->sum(DB::raw('Docdet.Cantidad * Docdet.Costo'));
            
        return $ventas - $costos;
    }

    /**
     * Generar resumen ejecutivo
     */
    public function generarResumenEjecutivo($periodo)
    {
        // Implementar según período (día, semana, mes, año)
        $fechaInicio = Carbon::now()->startOfMonth();
        $fechaFin = Carbon::now()->endOfMonth();
        
        if ($periodo == 'anio_actual') {
            $fechaInicio = Carbon::now()->startOfYear();
            $fechaFin = Carbon::now()->endOfYear();
        }
        
        return [
            'periodo' => $periodo,
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'ventas_totales' => DB::table('Doccab')
                ->whereBetween('Fecha', [$fechaInicio, $fechaFin])
                ->where('Eliminado', 0)
                ->sum('Total'),
            'utilidad' => $this->calcularUtilidadPeriodo($fechaInicio, $fechaFin),
            'clientes_activos' => DB::table('Doccab')
                ->whereBetween('Fecha', [$fechaInicio, $fechaFin])
                ->where('Eliminado', 0)
                ->distinct('CodClie')
                ->count('CodClie'),
        ];
    }
    
    private function calcularUtilidadPeriodo($fechaInicio, $fechaFin)
    {
        $ventas = DB::table('Doccab')
            ->whereBetween('Fecha', [$fechaInicio, $fechaFin])
            ->where('Eliminado', 0)
            ->sum('Total');
            
        $costos = DB::table('Docdet')
            ->join('Doccab', function($join) use ($fechaInicio, $fechaFin) {
                $join->on('Docdet.Numero', '=', 'Doccab.Numero')
                     ->on('Docdet.Tipo', '=', 'Doccab.Tipo')
                     ->whereBetween('Doccab.Fecha', [$fechaInicio, $fechaFin])
                     ->where('Doccab.Eliminado', 0);
            })
            ->sum(DB::raw('Docdet.Cantidad * Docdet.Costo'));
            
        return $ventas - $costos;
    }
    public function getDashboardData()
    {
        return [
            'kpis' => $this->obtenerKPIsGenerales(),
            'ventas_hoy' => $this->ventasDelDia(),
            'ventas_mes' => $this->ventasDelMes(),
            'alertas' => $this->alertasCriticas(),
            'top_productos' => $this->productosTop(),
            'top_clientes' => $this->clientesTop(),
            'cuentas_por_cobrar' => $this->resumenCuentasPorCobrar(),
            'cuentas_por_pagar' => $this->resumenCuentasPorPagar(),
        ];
    }
    public function graficoFlujoCaja($dias = 30)
    {
        return DB::table('Caja')
            ->select(
                DB::raw("CONVERT(date, Fecha) as fecha"),
                DB::raw("SUM(CASE WHEN Tipo = 1 THEN Monto ELSE 0 END) AS ingresos"),
                DB::raw("SUM(CASE WHEN Tipo = 2 THEN Monto ELSE 0 END) AS egresos")
            )
            ->where('Eliminado', 0)
            ->where('Fecha', '>=', now()->subDays($dias))
            ->groupBy(DB::raw("CONVERT(date, Fecha)"))
            ->orderBy(DB::raw("CONVERT(date, Fecha)"))
            ->get();
    }



}
