<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ContadorDashboardController extends Controller
{
    /**
     * Dashboard principal del contador - DISTRIBUIDORA DE FÁRMACOS
     */
    public function contadorDashboard(Request $request)
    {
        try {
            // ==================== MÉTRICAS FINANCIERAS ====================
            $ventasMes = $this->calcularVentasMes();
            $ventasMesAnterior = $this->calcularVentasMesAnterior();
            $variacionVentas = $this->calcularVariacion($ventasMes, $ventasMesAnterior);
            
            $cuentasPorCobrar = $this->calcularCuentasPorCobrar();
            $cuentasPorCobrarVencidas = $this->calcularCuentasPorCobrarVencidas();
            
            // ==================== MÉTRICAS OPERATIVAS ====================
            $clientesActivos = $this->contarClientesActivos();
            $facturasPendientes = $this->contarFacturasPendientes();
            $facturasVencidas = $this->contarFacturasVencidas();
            
            // ==================== INDICADORES DE DISTRIBUIDORA ====================
            $ticketPromedio = $this->calcularTicketPromedio();
            $diasPromedioCobranza = $this->calcularDiasPromedioCobranza();
            $margenBrutoMes = $this->calcularMargenBrutoMes();
            
            // ==================== DATOS PARA GRÁFICOS ====================
            $mesesLabels = $this->obtenerMesesLabels(6);
            $ventasData = $this->obtenerVentasPorMes(6);
            $cobranzasData = $this->obtenerCobranzasPorMes(6);
            
            // Top 10 clientes del mes
            $topClientes = $this->obtenerTopClientesMes(10);
            
            // ==================== VENTAS RECIENTES ====================
            $ventasRecientes = $this->obtenerVentasRecientes(15);
            
            // ==================== ALERTAS Y NOTIFICACIONES ====================
            $alertas = $this->generarAlertas();
            
            // ==================== PRODUCTOS CRÍTICOS ====================
            $productosStockBajo = $this->obtenerProductosStockBajo(10);
            $productosProximosVencer = $this->obtenerProductosProximosVencer(10);

            return view('dashboard.contador', compact(
                // Métricas financieras
                'ventasMes',
                'ventasMesAnterior',
                'variacionVentas',
                'cuentasPorCobrar',
                'cuentasPorCobrarVencidas',
                
                // Métricas operativas
                'clientesActivos',
                'facturasPendientes',
                'facturasVencidas',
                
                // Indicadores
                'ticketPromedio',
                'diasPromedioCobranza',
                'margenBrutoMes',
                
                // Gráficos
                'mesesLabels',
                'ventasData',
                'cobranzasData',
                'topClientes',
                
                // Listados
                'ventasRecientes',
                'alertas',
                'productosStockBajo',
                'productosProximosVencer'
            ));

        } catch (\Exception $e) {
            \Log::error('Error en dashboard contador: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return view('dashboard.contador', $this->getDatosVacios());
        }
    }

    // ==================== MÉTRICAS FINANCIERAS ====================
    
    private function calcularVentasMes()
    {
        return DB::table('Doccab')
            ->whereYear('Fecha', now()->year)
            ->whereMonth('Fecha', now()->month)
            ->where('Eliminado', 0)
            ->sum('Total') ?? 0;
    }

    private function calcularVentasMesAnterior()
    {
        $mesAnterior = now()->subMonth();
        return DB::table('Doccab')
            ->whereYear('Fecha', $mesAnterior->year)
            ->whereMonth('Fecha', $mesAnterior->month)
            ->where('Eliminado', 0)
            ->sum('Total') ?? 0;
    }

    private function calcularVariacion($actual, $anterior)
    {
        if ($anterior == 0) return 0;
        return round((($actual - $anterior) / $anterior) * 100, 2);
    }

    private function calcularCuentasPorCobrar()
    {
        return DB::table('CtaCliente')
            ->where('Saldo', '>', 0)
            ->sum('Saldo') ?? 0;
    }

    private function calcularCuentasPorCobrarVencidas()
    {
        return DB::table('CtaCliente')
            ->where('Saldo', '>', 0)
            ->where('FechaV', '<', now())
            ->sum('Saldo') ?? 0;
    }

    private function calcularMargenBrutoMes()
    {
        $resultado = DB::table('Doccab as dc')
            ->join('Docdet as dd', function($join) {
                $join->on('dc.Numero', '=', 'dd.Numero')
                     ->on('dc.Tipo', '=', 'dd.Tipo');
            })
            ->whereYear('dc.Fecha', now()->year)
            ->whereMonth('dc.Fecha', now()->month)
            ->where('dc.Eliminado', 0)
            ->selectRaw('
                SUM(dd.Subtotal) as ventas_totales,
                SUM(dd.Cantidad * dd.Costo) as costo_total
            ')
            ->first();

        if (!$resultado || $resultado->ventas_totales == 0) return 0;
        
        $margen = (($resultado->ventas_totales - $resultado->costo_total) / $resultado->ventas_totales) * 100;
        return round($margen, 2);
    }

    // ==================== MÉTRICAS OPERATIVAS ====================
    
    private function contarClientesActivos()
    {
        return DB::table('Clientes')
            ->where('Activo', 1)
            ->count() ?? 0;
    }

    private function contarFacturasPendientes()
    {
        return DB::table('CtaCliente')
            ->where('Saldo', '>', 0)
            ->count() ?? 0;
    }

    private function contarFacturasVencidas()
    {
        return DB::table('CtaCliente')
            ->where('Saldo', '>', 0)
            ->where('FechaV', '<', now())
            ->count() ?? 0;
    }

    private function calcularTicketPromedio()
    {
        $resultado = DB::table('Doccab')
            ->whereYear('Fecha', now()->year)
            ->whereMonth('Fecha', now()->month)
            ->where('Eliminado', 0)
            ->selectRaw('
                COUNT(*) as total_ventas,
                SUM(Total) as total_monto
            ')
            ->first();

        if (!$resultado || $resultado->total_ventas == 0) return 0;
        
        return round($resultado->total_monto / $resultado->total_ventas, 2);
    }

    private function calcularDiasPromedioCobranza()
    {
        $resultado = DB::table('CtaCliente')
            ->whereRaw('Saldo = 0') // Facturas pagadas
            ->whereYear('FechaF', now()->year)
            ->selectRaw('
                AVG(DATEDIFF(day, FechaF, FechaV)) as dias_promedio
            ')
            ->first();

        return $resultado && $resultado->dias_promedio ? round($resultado->dias_promedio, 0) : 0;
    }

    // ==================== DATOS PARA GRÁFICOS ====================
    
    private function obtenerMesesLabels($cantidad = 6)
    {
        $meses = [];
        for ($i = $cantidad - 1; $i >= 0; $i--) {
            $fecha = now()->subMonths($i);
            $meses[] = ucfirst($fecha->locale('es')->isoFormat('MMM'));
        }
        return $meses;
    }

    private function obtenerVentasPorMes($cantidad = 6)
    {
        $ventas = [];
        for ($i = $cantidad - 1; $i >= 0; $i--) {
            $fecha = now()->subMonths($i);
            $total = DB::table('Doccab')
                ->whereYear('Fecha', $fecha->year)
                ->whereMonth('Fecha', $fecha->month)
                ->where('Eliminado', 0)
                ->sum('Total');
            $ventas[] = round($total ?? 0, 2);
        }
        return $ventas;
    }

    private function obtenerCobranzasPorMes($cantidad = 6)
    {
        $cobranzas = [];
        for ($i = $cantidad - 1; $i >= 0; $i--) {
            $fecha = now()->subMonths($i);
            
            // Calculamos las cobranzas como: facturas del mes que tienen saldo = 0
            $total = DB::table('CtaCliente')
                ->whereYear('FechaF', $fecha->year)
                ->whereMonth('FechaF', $fecha->month)
                ->where('Saldo', 0)
                ->sum('Importe');
            
            $cobranzas[] = round($total ?? 0, 2);
        }
        return $cobranzas;
    }

    private function obtenerTopClientesMes($limite = 10)
    {
        return DB::table('Doccab as dc')
            ->join('Clientes as c', 'dc.CodClie', '=', 'c.Codclie')
            ->whereYear('dc.Fecha', now()->year)
            ->whereMonth('dc.Fecha', now()->month)
            ->where('dc.Eliminado', 0)
            ->select(
                'c.Codclie',
                'c.Razon as cliente',
                DB::raw('COUNT(*) as total_facturas'),
                DB::raw('SUM(dc.Total) as total_ventas')
            )
            ->groupBy('c.Codclie', 'c.Razon')
            ->orderBy('total_ventas', 'desc')
            ->limit($limite)
            ->get()
            ->map(function($item) {
                return [
                    'codigo' => $item->Codclie,
                    'cliente' => $item->cliente,
                    'facturas' => $item->total_facturas,
                    'total' => round($item->total_ventas, 2),
                    'avatar_color' => $this->getAvatarColor($item->Codclie) // Nueva función
                ];
            })
            ->toArray();
    }

    // ==================== VENTAS RECIENTES ====================
    
    private function obtenerVentasRecientes($limite = 15)
    {
        return DB::table('Doccab as dc')
            ->join('Clientes as c', 'dc.CodClie', '=', 'c.Codclie')
            ->leftJoin('CtaCliente as cc', function($join) {
                $join->on('dc.Numero', '=', 'cc.Documento')
                     ->on('dc.Tipo', '=', 'cc.Tipo');
            })
            ->where('dc.Eliminado', 0)
            ->select(
                'dc.Numero',
                'dc.Tipo',
                'dc.Fecha',
                'dc.Total',
                'c.Razon as cliente',
                DB::raw('CASE 
                    WHEN cc.Saldo IS NULL THEN \'SIN CTA\'
                    WHEN cc.Saldo = 0 THEN \'PAGADA\'
                    WHEN cc.FechaV < GETDATE() THEN \'VENCIDA\'
                    ELSE \'PENDIENTE\'
                END as estado'),
                'cc.Saldo'
            )
            ->orderBy('dc.Fecha', 'desc')
            ->limit($limite)
            ->get()
            ->map(function($venta) {
                return [
                    'numero' => trim($venta->Numero),
                    'tipo' => $this->obtenerTipoDocumento($venta->Tipo),
                    'cliente' => $venta->cliente,
                    'fecha' => Carbon::parse($venta->Fecha)->format('d/m/Y'),
                    'total' => round($venta->Total, 2),
                    'saldo' => round($venta->Saldo ?? 0, 2),
                    'estado' => $venta->estado,
                    'estado_class' => $this->obtenerClaseEstado($venta->estado)
                ];
            })
            ->toArray();
    }

    // ==================== PRODUCTOS CRÍTICOS ====================
    
    private function obtenerProductosStockBajo($limite = 10)
    {
        return DB::table('Productos as p')
            ->leftJoin('Laboratorios as l', DB::raw('LEFT(p.CodPro, 2)'), '=', DB::raw('RTRIM(l.CodLab)'))
            ->where('p.Eliminado', 0)
            ->whereRaw('p.Stock <= p.Minimo')
            ->where('p.Stock', '>', 0)
            ->select(
                'p.CodPro',
                'p.Nombre',
                'l.Descripcion as laboratorio',
                'p.Stock',
                'p.Minimo',
                DB::raw('ROUND((p.Stock / NULLIF(p.Minimo, 0)) * 100, 0) as porcentaje')
            )
            ->orderBy('porcentaje', 'asc')
            ->limit($limite)
            ->get()
            ->map(function($item) {
                return [
                    'codigo' => trim($item->CodPro),
                    'nombre' => $item->Nombre,
                    'laboratorio' => $item->laboratorio ?? 'Sin laboratorio',
                    'stock' => round($item->Stock, 2),
                    'minimo' => round($item->Minimo, 2),
                    'porcentaje' => $item->porcentaje ?? 0
                ];
            })
            ->toArray();
    }

    private function obtenerProductosProximosVencer($limite = 10)
    {
        $fechaLimite = now()->addMonths(3);
        
        return DB::table('Saldos as s')
            ->join('Productos as p', 's.codpro', '=', 'p.CodPro')
            ->leftJoin('Laboratorios as l', DB::raw('LEFT(p.CodPro, 2)'), '=', DB::raw('RTRIM(l.CodLab)'))
            ->where('p.Eliminado', 0)
            ->where('s.saldo', '>', 0)
            ->where('s.vencimiento', '<=', $fechaLimite)
            ->where('s.vencimiento', '>', now())
            ->select(
                'p.CodPro',
                'p.Nombre',
                'l.Descripcion as laboratorio',
                's.Lote',
                's.vencimiento',
                's.saldo',
                DB::raw('DATEDIFF(day, GETDATE(), s.vencimiento) as dias_restantes')
            )
            ->orderBy('dias_restantes', 'asc')
            ->limit($limite)
            ->get()
            ->map(function($item) {
                return [
                    'codigo' => trim($item->CodPro),
                    'nombre' => $item->Nombre,
                    'laboratorio' => $item->laboratorio ?? 'Sin laboratorio',
                    'lote' => trim($item->Lote),
                    'vencimiento' => Carbon::parse($item->vencimiento)->format('d/m/Y'),
                    'stock' => round($item->saldo, 2),
                    'dias' => $item->dias_restantes
                ];
            })
            ->toArray();
    }

    // ==================== ALERTAS ====================
    
    private function generarAlertas()
    {
        $alertas = [];

        // 1. Facturas vencidas
        $facturasVencidas = $this->contarFacturasVencidas();
        $montoVencido = $this->calcularCuentasPorCobrarVencidas();
        
        if ($facturasVencidas > 0) {
            $alertas[] = [
                'tipo' => 'danger',
                'icono' => 'exclamation-triangle',
                'titulo' => 'Facturas Vencidas',
                'mensaje' => "{$facturasVencidas} facturas vencidas por S/ " . number_format($montoVencido, 2)
            ];
        }

        // 2. Clientes sin actividad (90 días)
        $clientesSinActividad = DB::table('Clientes as c')
            ->leftJoin('Doccab as d', 'c.Codclie', '=', 'd.CodClie')
            ->where('c.Activo', 1)
            ->whereRaw('(d.Fecha IS NULL OR d.Fecha < DATEADD(MONTH, -3, GETDATE()))')
            ->whereRaw('NOT EXISTS (
                SELECT 1 FROM Doccab d2 
                WHERE d2.CodClie = c.Codclie 
                AND d2.Fecha >= DATEADD(MONTH, -3, GETDATE())
                AND d2.Eliminado = 0
            )')
            ->count();

        if ($clientesSinActividad > 5) {
            $alertas[] = [
                'tipo' => 'warning',
                'icono' => 'users',
                'titulo' => 'Clientes Inactivos',
                'mensaje' => "{$clientesSinActividad} clientes sin compras en los últimos 90 días"
            ];
        }

        // 3. Productos con stock bajo
        $productosStockBajo = DB::table('Productos')
            ->whereRaw('Stock <= Minimo')
            ->where('Stock', '>', 0)
            ->where('Eliminado', 0)
            ->count();

        if ($productosStockBajo > 0) {
            $alertas[] = [
                'tipo' => 'warning',
                'icono' => 'box',
                'titulo' => 'Stock Bajo',
                'mensaje' => "{$productosStockBajo} productos requieren reposición"
            ];
        }

        // 4. Productos próximos a vencer (90 días)
        $productosVencer = DB::table('Saldos')
            ->where('saldo', '>', 0)
            ->where('vencimiento', '<=', now()->addMonths(3))
            ->where('vencimiento', '>', now())
            ->count();

        if ($productosVencer > 0) {
            $alertas[] = [
                'tipo' => 'info',
                'icono' => 'calendar-times',
                'titulo' => 'Productos por Vencer',
                'mensaje' => "{$productosVencer} lotes vencen en los próximos 90 días"
            ];
        }

        // 5. Cartera vencida crítica (> 30% del total)
        $porcentajeVencido = $this->calcularCuentasPorCobrar() > 0 
            ? ($this->calcularCuentasPorCobrarVencidas() / $this->calcularCuentasPorCobrar()) * 100 
            : 0;

        if ($porcentajeVencido > 30) {
            $alertas[] = [
                'tipo' => 'danger',
                'icono' => 'exclamation-circle',
                'titulo' => 'Cartera Crítica',
                'mensaje' => number_format($porcentajeVencido, 1) . "% de la cartera está vencida"
            ];
        }

        return $alertas;
    }

    // ==================== HELPERS ====================
    
    private function obtenerTipoDocumento($tipo)
    {
        $tipos = [
            1 => 'FACTURA',
            2 => 'BOLETA',
            3 => 'NOTA CRÉDITO',
            4 => 'GUÍA',
            7 => 'LETRA',
            8 => 'NOTA DÉBITO'
        ];
        return $tipos[$tipo] ?? 'DOCUMENTO';
    }

    private function obtenerClaseEstado($estado)
    {
        $clases = [
            'PAGADA' => 'success',
            'PENDIENTE' => 'warning',
            'VENCIDA' => 'danger',
            'SIN CTA' => 'secondary'
        ];
        return $clases[$estado] ?? 'secondary';
    }

    private function getDatosVacios()
    {
        return [
            'ventasMes' => 0,
            'ventasMesAnterior' => 0,
            'variacionVentas' => 0,
            'cuentasPorCobrar' => 0,
            'cuentasPorCobrarVencidas' => 0,
            'clientesActivos' => 0,
            'facturasPendientes' => 0,
            'facturasVencidas' => 0,
            'ticketPromedio' => 0,
            'diasPromedioCobranza' => 0,
            'margenBrutoMes' => 0,
            'mesesLabels' => [],
            'ventasData' => [],
            'cobranzasData' => [],
            'topClientes' => [],
            'ventasRecientes' => [],
            'productosStockBajo' => [],
            'productosProximosVencer' => [],
            'alertas' => [[
                'tipo' => 'danger',
                'icono' => 'exclamation-triangle',
                'titulo' => 'Error del Sistema',
                'mensaje' => 'No se pudieron cargar los datos. Contacte al administrador.'
            ]]
        ];
    }

    // ==================== API ENDPOINTS ====================
    
    public function getStats(Request $request)
    {
        try {
            $stats = [
                'ventas_hoy' => DB::table('Doccab')
                    ->whereDate('Fecha', today())
                    ->where('Eliminado', 0)
                    ->sum('Total') ?? 0,
                'ventas_mes' => $this->calcularVentasMes(),
                'clientes_activos' => $this->contarClientesActivos(),
                'facturas_pendientes' => $this->contarFacturasPendientes(),
                'margen_bruto' => $this->calcularMargenBrutoMes(),
                'ticket_promedio' => $this->calcularTicketPromedio()
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas: ' . $e->getMessage()
            ], 500);
        }
    }


    private function getAvatarColor($codigo)
    {
        $colors = ['primary', 'success', 'warning', 'danger', 'info', 'purple'];
        return $colors[$codigo % count($colors)];
    }
}