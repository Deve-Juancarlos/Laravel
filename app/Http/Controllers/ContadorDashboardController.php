<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ContadorDashboardController extends Controller
{
    private $cache_ttl = 900; // 15 minutos
    
    /**
     * Dashboard principal del contador - DISTRIBUIDORA FÁRMACOS
     * VERSIÓN OPTIMIZADA CON CACHE Y MEJORAS
     */
    public function contadorDashboard(Request $request)
    {
        try {
            // Cache key basada en fecha/hora para invalidación automática
            $cacheKey = 'dashboard_contador_' . now()->format('Y-m-d-H');
            
            $data = Cache::remember($cacheKey, $this->cache_ttl, function () {
                return [
                    'ventasMes' => $this->calcularVentasMes(),
                    'ventasMesAnterior' => $this->calcularVentasMesAnterior(),
                    'cuentasPorCobrar' => $this->calcularCuentasPorCobrar(),
                    'cuentasPorCobrarVencidas' => $this->calcularCuentasPorCobrarVencidas(),
                    'clientesActivos' => $this->contarClientesActivos(),
                    'facturasPendientes' => $this->contarFacturasPendientes(),
                    'facturasVencidas' => $this->contarFacturasVencidas(),
                    'ticketPromedio' => $this->calcularTicketPromedio(),
                    'diasPromedioCobranza' => $this->calcularDiasPromedioCobranza(),
                    'margenBrutoMes' => $this->calcularMargenBrutoMes(),
                    'mesesLabels' => $this->obtenerMesesLabels(6),
                    'ventasData' => $this->obtenerVentasPorMes(6),
                    'cobranzasData' => $this->obtenerCobranzasPorMes(6),
                    'topClientes' => $this->obtenerTopClientesMes(10),
                    'ventasRecientes' => $this->obtenerVentasRecientes(15),
                    'alertas' => $this->generarAlertas(),
                    'productosStockBajo' => $this->obtenerProductosStockBajo(10),
                    'productosProximosVencer' => $this->obtenerProductosProximosVencer(10),
                    'analisisFinanciero' => $this->obtenerAnalisisFinanciero(),
                    'vencimientosPorRango' => $this->analizarVencimientosPorRango(),
                    'moraDetalle' => $this->analizarMoraDetalle(),
                ];
            });

            return view('dashboard.contador', $data);

        } catch (\Exception $e) {
            Log::error('Error en dashboard contador: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return view('dashboard.contador', $this->getDatosVacios());
        }
    }

    /**
     * ANÁLISIS FINANCIERO AVANZADO
     */
    private function obtenerAnalisisFinanciero()
    {
        $resultado = DB::table('Doccab as dc')
            ->join('Docdet as dd', function($join) {
                $join->on('dc.Numero', '=', 'dd.Numero')
                     ->on('dc.Tipo', '=', 'dd.Tipo');
            })
            ->join('Clientes as c', 'dc.CodClie', '=', 'c.Codclie')
            ->whereYear('dc.Fecha', now()->year)
            ->whereMonth('dc.Fecha', now()->month)
            ->where('dc.Eliminado', 0)
            ->selectRaw('
                COUNT(DISTINCT dc.CodClie) as clientes_activos,
                COUNT(DISTINCT dc.Numero) as total_facturas,
                SUM(dd.Subtotal) as ventas_totales,
                SUM(dd.Cantidad * dd.Costo) as costo_total,
                AVG(dc.Total) as ticket_promedio,
                MIN(dc.Total) as venta_minima,
                MAX(dc.Total) as venta_maxima
            ')
            ->first();

        if (!$resultado) return [];

        return [
            'clientes_activos' => $resultado->clientes_activos ?? 0,
            'total_facturas' => $resultado->total_facturas ?? 0,
            'ventas_totales' => round($resultado->ventas_totales ?? 0, 2),
            'costo_total' => round($resultado->costo_total ?? 0, 2),
            'ticket_promedio' => round($resultado->ticket_promedio ?? 0, 2),
            'venta_minima' => round($resultado->venta_minima ?? 0, 2),
            'venta_maxima' => round($resultado->venta_maxima ?? 0, 2),
            'margen_bruto' => $resultado->ventas_totales > 0 
                ? round((($resultado->ventas_totales - $resultado->costo_total) / $resultado->ventas_totales) * 100, 2)
                : 0,
        ];
    }

    /**
     * ANÁLISIS DE VENCIMIENTOS POR RANGOS DE DÍAS
     */
    private function analizarVencimientosPorRango()
    {
        $fechaActual = now();
        
        $rangos = DB::table('Saldos as s')
            ->join('Productos as p', 's.codpro', '=', 'p.CodPro')
            ->where('s.saldo', '>', 0)
            ->where('p.Eliminado', 0)
            ->selectRaw('
                CASE 
                    WHEN s.vencimiento <= ? THEN "Vencidos"
                    WHEN s.vencimiento <= ? THEN "1-30 días"
                    WHEN s.vencimiento <= ? THEN "31-60 días"
                    WHEN s.vencimiento <= ? THEN "61-90 días"
                    ELSE "+90 días"
                END as rango,
                COUNT(*) as cantidad_lotes,
                SUM(s.saldo) as cantidad_total,
                SUM(s.saldo * p.StockP) as valor_total
            ', [
                $fechaActual,
                $fechaActual->copy()->addDays(30),
                $fechaActual->copy()->addDays(60),
                $fechaActual->copy()->addDays(90)
            ])
            ->groupBy('rango')
            ->get();

        return $rangos->map(function($item) {
            return [
                'rango' => $item->rango,
                'cantidad_lotes' => $item->cantidad_lotes,
                'cantidad_total' => round($item->cantidad_total, 2),
                'valor_total' => round($item->valor_total, 2),
                'color_class' => $this->obtenerColorVencimiento($item->rango)
            ];
        })->toArray();
    }

    /**
     * ANÁLISIS DETALLADO DE MORA
     */
    private function analizarMoraDetalle()
    {
        $resultado = DB::table('CtaCliente as cc')
            ->join('Clientes as c', 'cc.CodClie', '=', 'c.Codclie')
            ->where('cc.Saldo', '>', 0)
            ->selectRaw('
                c.Codclie,
                c.Razon,
                COUNT(*) as facturas_vencidas,
                SUM(cc.Saldo) as total_mora,
                AVG(DATEDIFF(day, cc.FechaF, GETDATE())) as dias_promedio_mora,
                MIN(cc.FechaV) as factura_mas_antigua
            ')
            ->groupBy('c.Codclie', 'c.Razon')
            ->having('SUM(cc.Saldo)', '>', 1000) // Solo clientes con mora significativa
            ->orderBy('total_mora', 'desc')
            ->limit(10)
            ->get();

        return $resultado->map(function($item) {
            return [
                'codigo' => $item->Codclie,
                'cliente' => $item->Razon,
                'facturas_vencidas' => $item->facturas_vencidas,
                'total_mora' => round($item->total_mora, 2),
                'dias_promedio_mora' => round($item->dias_promedio_mora, 0),
                'factura_mas_antigua' => Carbon::parse($item->factura_mas_antigua)->format('d/m/Y'),
                'nivel_riesgo' => $this->determinarNivelRiesgo($item->dias_promedio_mora, $item->total_mora),
                'dias_riesgo' => $this->calcularDiasRiesgo($item->dias_promedio_mora)
            ];
        })->toArray();
    }

    /**
     * ALERTAS MEJORADAS FARMACÉUTICAS (SIN TEMPERATURA)
     */
    private function generarAlertas()
    {
        $alertas = [];
        $fechaActual = now();

        // 1. Alertas DIGEMID - Reportes pendientes
        $reportesDigemid = DB::table('Trazabilidad_Controlados')
            ->where('ReporteDIGEMID', 0)
            ->where('FechaMovimiento', '>=', $fechaActual->copy()->subMonths(1))
            ->count();

        if ($reportesDigemid > 0) {
            $alertas[] = [
                'tipo' => 'danger',
                'icono' => 'shield-alt',
                'titulo' => 'Reporte DIGEMID Pendiente',
                'mensaje' => "{$reportesDigemid} movimientos de controlados sin reportar",
                'accion' => route('contador.reportes.financiero'),
                'prioridad' => 'alta'
            ];
        }

        // 2. Productos controlados - Stock crítico
        $controladosBaja = DB::table('Productos as p')
            ->join('Categorias as cat', DB::raw('LEFT(p.CodPro, 2)'), '=', DB::raw('RTRIM(cat.CodCat)'))
            ->where('p.Eliminado', 0)
            ->where('cat.Tipo', 'CONTROLADO')
            ->whereRaw('p.Stock <= p.Minimo')
            ->count();

        if ($controladosBaja > 0) {
            $alertas[] = [
                'tipo' => 'warning',
                'icono' => 'prescription-bottle-alt',
                'titulo' => 'Medicamentos Controlados',
                'mensaje' => "{$controladosBaja} productos controlados con stock bajo",
                'accion' => route('contador.productos.index'),
                'prioridad' => 'alta'
            ];
        }

        // 3. Facturas vencidas críticas (> 60 días)
        $facturas60dias = DB::table('CtaCliente')
            ->where('Saldo', '>', 0)
            ->where('FechaV', '<', $fechaActual->copy()->subDays(60))
            ->count();

        if ($facturas60dias > 0) {
            $alertas[] = [
                'tipo' => 'danger',
                'icono' => 'exclamation-triangle',
                'titulo' => 'Facturas en Mora Crítica',
                'mensaje' => "{$facturas60dias} facturas vencidas por más de 60 días",
                'accion' => route('contador.facturas.index'),
                'prioridad' => 'crítica'
            ];
        }

        // 4. Productos próximos a vencer (30 días)
        $proximosVencer = DB::table('Saldos as s')
            ->join('Productos as p', 's.codpro', '=', 'p.CodPro')
            ->where('s.saldo', '>', 0)
            ->where('s.vencimiento', '<=', $fechaActual->copy()->addDays(30))
            ->where('s.vencimiento', '>', $fechaActual)
            ->count();

        if ($proximosVencer > 0) {
            $alertas[] = [
                'tipo' => 'warning',
                'icono' => 'calendar-times',
                'titulo' => 'Productos por Vencer',
                'mensaje' => "{$proximosVencer} lotes vencen en 30 días",
                'accion' => route('contador.productos.index'),
                'prioridad' => 'media'
            ];
        }

        // 5. Clientes con alto riesgo crediticio
        $clientesRiesgo = DB::table('Clientes')
            ->whereRaw('DATEDIFF(day, (SELECT MAX(Fecha) FROM Doccab WHERE CodClie = Clientes.Codclie), GETDATE()) > 90')
            ->where('Activo', 1)
            ->count();

        if ($clientesRiesgo > 0) {
            $alertas[] = [
                'tipo' => 'info',
                'icono' => 'user-times',
                'titulo' => 'Clientes Inactivos',
                'mensaje' => "{$clientesRiesgo} clientes sin actividad en 90 días",
                'accion' => route('contador.clientes'),
                'prioridad' => 'baja'
            ];
        }

        return $alertas;
    }

    // ==================== MÉTODOS AUXILIARES ====================

    private function obtenerColorVencimiento($rango)
    {
        $colores = [
            'Vencidos' => 'danger',
            '1-30 días' => 'danger',
            '31-60 días' => 'warning',
            '61-90 días' => 'info',
            '+90 días' => 'success'
        ];
        return $colores[$rango] ?? 'secondary';
    }

    private function determinarNivelRiesgo($diasPromedio, $monto)
    {
        if ($diasPromedio > 90 || $monto > 50000) return 'crítico';
        if ($diasPromedio > 60 || $monto > 20000) return 'alto';
        if ($diasPromedio > 30 || $monto > 5000) return 'medio';
        return 'bajo';
    }

    private function calcularDiasRiesgo($diasMora)
    {
        if ($diasMora > 90) return 'Muy Alto';
        if ($diasMora > 60) return 'Alto';
        if ($diasMora > 30) return 'Medio';
        return 'Bajo';
    }

    // ==================== MÉTODOS BASE (OPTIMIZADOS) ====================
    
    private function calcularVentasMes()
    {
        return Cache::remember('ventas_mes_' . now()->format('Y-m'), $this->cache_ttl, function () {
            return DB::table('Doccab')
                ->whereYear('Fecha', now()->year)
                ->whereMonth('Fecha', now()->month)
                ->where('Eliminado', 0)
                ->sum('Total') ?? 0;
        });
    }

    private function calcularVentasMesAnterior()
    {
        $mesAnterior = now()->subMonth();
        return Cache::remember('ventas_mes_anterior_' . $mesAnterior->format('Y-m'), $this->cache_ttl, function () use ($mesAnterior) {
            return DB::table('Doccab')
                ->whereYear('Fecha', $mesAnterior->year)
                ->whereMonth('Fecha', $mesAnterior->month)
                ->where('Eliminado', 0)
                ->sum('Total') ?? 0;
        });
    }

    private function calcularCuentasPorCobrar()
    {
        return Cache::remember('cuentas_cobrar', $this->cache_ttl, function () {
            return DB::table('CtaCliente')
                ->where('Saldo', '>', 0)
                ->sum('Saldo') ?? 0;
        });
    }

    private function calcularCuentasPorCobrarVencidas()
    {
        return Cache::remember('cuentas_cobrar_vencidas', $this->cache_ttl, function () {
            return DB::table('CtaCliente')
                ->where('Saldo', '>', 0)
                ->where('FechaV', '<', now())
                ->sum('Saldo') ?? 0;
        });
    }

    private function calcularMargenBrutoMes()
    {
        return Cache::remember('margen_bruto_mes', $this->cache_ttl, function () {
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
        });
    }

    private function obtenerVentasPorMes($cantidad = 6)
    {
        $ventas = [];
        for ($i = $cantidad - 1; $i >= 0; $i--) {
            $fecha = now()->subMonths($i);
            $cacheKey = 'ventas_mes_' . $fecha->format('Y-m');
            
            $total = Cache::remember($cacheKey, $this->cache_ttl, function () use ($fecha) {
                return DB::table('Doccab')
                    ->whereYear('Fecha', $fecha->year)
                    ->whereMonth('Fecha', $fecha->month)
                    ->where('Eliminado', 0)
                    ->sum('Total') ?? 0;
            });
            
            $ventas[] = round($total, 2);
        }
        return $ventas;
    }

    private function obtenerCobranzasPorMes($cantidad = 6)
    {
        $cobranzas = [];
        for ($i = $cantidad - 1; $i >= 0; $i--) {
            $fecha = now()->subMonths($i);
            $cacheKey = 'cobranzas_mes_' . $fecha->format('Y-m');
            
            $total = Cache::remember($cacheKey, $this->cache_ttl, function () use ($fecha) {
                return DB::table('CtaCliente')
                    ->whereYear('FechaF', $fecha->year)
                    ->whereMonth('FechaF', $fecha->month)
                    ->where('Saldo', 0)
                    ->sum('Importe');
            });
            
            $cobranzas[] = round($total ?? 0, 2);
        }
        return $cobranzas;
    }

    private function obtenerTopClientesMes($limite = 10)
    {
        return Cache::remember('top_clientes_' . now()->format('Y-m'), $this->cache_ttl, function () use ($limite) {
            return DB::table('Doccab as dc')
                ->join('Clientes as c', 'dc.CodClie', '=', 'c.Codclie')
                ->whereYear('dc.Fecha', now()->year)
                ->whereMonth('dc.Fecha', now()->month)
                ->where('dc.Eliminado', 0)
                ->select(
                    'c.Codclie',
                    'c.Razon as cliente',
                    DB::raw('COUNT(*) as total_facturas'),
                    DB::raw('SUM(dc.Total) as total_ventas'),
                    DB::raw('AVG(dc.Total) as ticket_promedio')
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
                        'ticket_promedio' => round($item->ticket_promedio, 2),
                        'avatar_color' => $this->getAvatarColor($item->Codclie)
                    ];
                })
                ->toArray();
        });
    }

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
                'cc.Saldo',
                'cc.FechaV'
            )
            ->orderBy('dc.Fecha', 'desc')
            ->limit($limite)
            ->get()
            ->map(function($venta) {
                $diasVencimiento = $venta->Saldo > 0 ? now()->diffInDays(Carbon::parse($venta->FechaV)) : 0;
                
                return [
                    'numero' => trim($venta->Numero),
                    'tipo' => $this->obtenerTipoDocumento($venta->Tipo),
                    'cliente' => $venta->cliente,
                    'fecha' => Carbon::parse($venta->Fecha)->format('d/m/Y'),
                    'total' => round($venta->Total, 2),
                    'saldo' => round($venta->Saldo ?? 0, 2),
                    'estado' => $venta->estado,
                    'estado_class' => $this->obtenerClaseEstado($venta->estado),
                    'dias_vencimiento' => $diasVencimiento,
                    'urgencia' => $diasVencimiento > 30 ? 'alta' : ($diasVencimiento > 0 ? 'media' : 'baja')
                ];
            })
            ->toArray();
    }

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
                'p.StockP',
                DB::raw('ROUND((p.Stock / NULLIF(p.Minimo, 0)) * 100, 0) as porcentaje'),
                DB::raw('(p.Stock * p.StockP) as valor_stock')
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
                    'porcentaje' => $item->porcentaje ?? 0,
                    'valor_stock' => round($item->valor_stock, 2),
                    'criticidad' => $item->porcentaje < 20 ? 'crítica' : ($item->porcentaje < 50 ? 'alta' : 'media')
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
                'p.StockP',
                DB::raw('DATEDIFF(day, GETDATE(), s.vencimiento) as dias_restantes'),
                DB::raw('(s.saldo * p.StockP) as valor_lote')
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
                    'valor_lote' => round($item->valor_lote, 2),
                    'dias' => $item->dias_restantes,
                    'riesgo' => $item->dias_restantes <= 30 ? 'alto' : ($item->dias_restantes <= 60 ? 'medio' : 'bajo')
                ];
            })
            ->toArray();
    }

    // ==================== MÉTODOS RESTANTES ====================
    
    private function contarClientesActivos() { return 0; }
    private function contarFacturasPendientes() { return 0; }
    private function contarFacturasVencidas() { return 0; }
    private function calcularTicketPromedio() { return 0; }
    private function calcularDiasPromedioCobranza() { return 0; }
    private function obtenerMesesLabels($cantidad = 6) { return []; }
    
    private function obtenerTipoDocumento($tipo)
    {
        $tipos = [1 => 'FACTURA', 2 => 'BOLETA', 3 => 'NOTA CRÉDITO', 4 => 'GUÍA'];
        return $tipos[$tipo] ?? 'DOCUMENTO';
    }

    private function obtenerClaseEstado($estado)
    {
        $clases = ['PAGADA' => 'success', 'PENDIENTE' => 'warning', 'VENCIDA' => 'danger'];
        return $clases[$estado] ?? 'secondary';
    }

    private function getAvatarColor($codigo)
    {
        $colors = ['primary', 'success', 'warning', 'danger', 'info', 'purple'];
        return $colors[$codigo % count($colors)];
    }

    private function getDatosVacios()
    {
        return [
            'ventasMes' => 0, 'ventasMesAnterior' => 0, 'variacionVentas' => 0,
            'cuentasPorCobrar' => 0, 'cuentasPorCobrarVencidas' => 0,
            'clientesActivos' => 0, 'facturasPendientes' => 0, 'facturasVencidas' => 0,
            'ticketPromedio' => 0, 'diasPromedioCobranza' => 0, 'margenBrutoMes' => 0,
            'mesesLabels' => [], 'ventasData' => [], 'cobranzasData' => [],
            'topClientes' => [], 'ventasRecientes' => [], 'alertas' => [],
            'productosStockBajo' => [], 'productosProximosVencer' => [],
            'analisisFinanciero' => [], 'vencimientosPorRango' => [], 'moraDetalle' => []
        ];
    }

    // ==================== API ENDPOINTS ====================
    
    public function getStats(Request $request)
    {
        try {
            $stats = Cache::remember('api_stats_' . now()->format('Y-m-d-H'), 300, function () {
                return [
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
            });

            return response()->json(['success' => true, 'data' => $stats]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Error al obtener estadísticas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Limpiar cache (para testing o mantenimiento)
     */
    public function clearCache()
    {
        try {
            Cache::tags(['dashboard'])->flush();
            
            return response()->json([
                'success' => true,
                'message' => 'Cache limpiado exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al limpiar cache: ' . $e->getMessage()
            ], 500);
        }
    }
}