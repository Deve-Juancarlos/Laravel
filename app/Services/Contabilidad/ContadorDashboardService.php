<?php

namespace App\Services\Contabilidad;

// Modelos (sin cambios)
use App\Models\Cliente;
use App\Models\CtaCliente;
use App\Models\Doccab;
use App\Models\Docdet;
use App\Models\Producto;
use App\Models\Saldo;
use App\Models\Vistas\VistaAgingCartera;
use App\Models\Vistas\VistaProductosPorVencer;

// Facades
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
// ❗️ IMPORTANTE: No necesitamos Artisan si usamos Tags
// use Illuminate\Support\Facades\Artisan; 

class ContadorDashboardService
{
    
    private $cache_ttl = 900; // 15 minutos
    
    // 🏷️ Esta es la etiqueta que usaremos para agrupar todo el caché
    private $cache_tag = 'contador_dashboard';

    public function getDashboardData()
    {
        // Esta función no cambia, sigue llamando a las demás
        $data = [
            'ventasMes' => (float) $this->calcularVentasMes(),
            'ventasMesAnterior' => (float) $this->calcularVentasMesAnterior(),
            'variacionVentas' => $this->calcularVariacionVentas(),
            'cuentasPorCobrar' => (float) $this->calcularCuentasPorCobrar(),
            'cuentasPorCobrarVencidas' => (float) $this->calcularCuentasPorCobrarVencidas(),
            'clientesActivos' => $this->contarClientesActivos(),
            'facturasPendientes' => $this->contarFacturasPendientes(),
            'facturasVencidas' => $this->contarFacturasVencidas(),
            'ticketPromedio' => (float) $this->calcularTicketPromedio(),
            'diasPromedioCobranza' => (int) $this->calcularDiasPromedioCobranza(),
            'margenBrutoMes' => (float) $this->calcularMargenBrutoMes(),
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
            'ultimasFacturas' => $this->obtenerUltimasFacturas(10),
            'topClientesSaldo' => $this->obtenerTopClientesSaldo(5), 
        ];

        if (!isset($data['topClientesSaldo'])) {
             $data['topClientesSaldo'] = $this->obtenerTopClientesSaldo(5);
        }

        return $data;
    }

    public function getApiStats()
    {
        // Esta función no cambia
        return [
            'ventas_hoy' => (float) $this->calcularVentasHoy(),
            'ventas_mes' => $this->calcularVentasMes(),
            'clientes_activos' => $this->contarClientesActivos(),
            'facturas_pendientes' => $this->contarFacturasPendientes(),
            'margen_bruto' => $this->calcularMargenBrutoMes(),
            'ticket_promedio' => $this->calcularTicketPromedio()
        ];
    }

    // ===================================================================
    // 🚀 SOLUCIÓN DE CACHÉ
    // ===================================================================
    public function clearDashboardCache()
    {
        Log::info('Limpiando caché del dashboard de contador (Método de TAGS)');
        
        // ¡Y ya está! Esta única línea limpia todos los KPIs
        // sin importar sus nombres, y sin tocar el caché de otras partes de la app.
        Cache::tags([$this->cache_tag])->flush();
    }
    
    // ===================================================================
    // 🚀 SOLUCIÓN DE HORA DE PERÚ + CACHE TAGS
    // ===================================================================

    public function calcularVentasHoy()
    {
         // Ya no necesitamos 'America/Lima', 'today()' es suficiente.
         $cacheKey = 'ventas_hoy_' . today()->format('Y-m-d');
         
         // 🏷️ Agregamos el tag
         return Cache::tags([$this->cache_tag])->remember($cacheKey, $this->cache_ttl, function() {
            Log::debug('EJECUTANDO QUERY (CON MODELO): calcularVentasHoy');
            return Doccab::whereDate('Fecha', today()) // <- 'today()' simple
                         ->where('Eliminado', 0)
                         ->sum('Total') ?? 0;
         });
    }

    public function obtenerTopClientesSaldo($limite = 5)
    {
        // 🏷️ Agregamos el tag
        return Cache::tags([$this->cache_tag])->remember('top_clientes_saldo_' . $limite, $this->cache_ttl, function () use ($limite) {
            Log::debug('EJECUTANDO QUERY (CON MODELO): obtenerTopClientesSaldo');
            return Cliente::select('Razon')
                ->withSum(['cuentasPorCobrar as saldo' => fn($query) => $query->where('Saldo', '>', 0)], 'Saldo')
                ->orderByDesc('saldo')
                ->limit($limite)
                ->get();
        });
    }

    public function obtenerUltimasFacturas($limite = 10)
    {
        // 🏷️ Agregamos el tag
        return Cache::tags([$this->cache_tag])->remember('ultimas_facturas_' . $limite, $this->cache_ttl, function () use ($limite) {
            Log::debug('EJECUTANDO QUERY (CON MODELO): obtenerUltimasFacturas');
            return CtaCliente::with('cliente:Codclie,Razon')
                ->select('Documento', 'Importe', 'Saldo', 'FechaF', 'FechaV', 'CodClie')
                ->orderByDesc('FechaF')
                ->limit($limite)
                ->get();
        });
    }

    public function calcularVariacionVentas()
    {
        // No necesita caché, ya que usa funciones que sí lo tienen
        $actual = $this->calcularVentasMes();
        $anterior = $this->calcularVentasMesAnterior();
        if ($anterior == 0) {
            return $actual > 0 ? 100.00 : 0.00;
        }
        return round((($actual - $anterior) / $anterior) * 100, 2);
    }

    public function obtenerAnalisisFinanciero()
    {
        // 🏷️ Agregamos el tag
        return Cache::tags([$this->cache_tag])->remember('analisis_financiero_mes_actual', $this->cache_ttl, function () {
            Log::debug('EJECUTANDO QUERY (DB::table): obtenerAnalisisFinanciero');
            
            $fechaHoy = now(); // <- 'now()' simple

            $resultado = DB::table('Doccab as dc')
                ->join('Docdet as dd', function($join) {
                    $join->on('dc.Numero', '=', 'dd.Numero')
                         ->on('dc.Tipo', '=', 'dd.Tipo');
                })
                ->join('Clientes as c', 'dc.CodClie', '=', 'c.Codclie')
                ->whereYear('dc.Fecha', $fechaHoy->year)
                ->whereMonth('dc.Fecha', $fechaHoy->month)
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

            if (!$resultado) return [
                'clientes_activos' => 0, 'total_facturas' => 0, 'ventas_totales' => 0,
                'costo_total' => 0, 'ticket_promedio' => 0, 'venta_minima' => 0,
                'venta_maxima' => 0, 'margen_bruto' => 0,
            ];

             return [
                'clientes_activos' => $resultado->clientes_activos ?? 0,
                'total_facturas' => $resultado->total_facturas ?? 0,
                'ventas_totales' => round($resultado->ventas_totales ?? 0, 2),
                'costo_total' => round($resultado->costo_total ?? 0, 2),
                'ticket_promedio' => round($resultado->ticket_promedio ?? 0, 2),
                'venta_minima' => round($resultado->venta_minima ?? 0, 2),
                'venta_maxima' => round($resultado->venta_maxima ?? 0, 2),
                'margen_bruto' => ($resultado->ventas_totales > 0) 
                    ? round((($resultado->ventas_totales - $resultado->costo_total) / $resultado->ventas_totales) * 100, 2)
                    : 0,
            ];
        });
    }

    // Esta es tu versión optimizada que ya incluiste
    public function analizarVencimientosPorRango()
    {
        // 🏷️ Agregamos el tag
        return Cache::tags([$this->cache_tag])->remember('vencimientos_por_rango', $this->cache_ttl, function () {
            Log::debug('EJECUTANDO QUERY (OPTIMIZADA DB::raw): analizarVencimientosPorRango');

            $hoy = now()->format('Y-m-d'); // <- 'now()' simple

            $resultados = DB::table('Saldos as s')
                ->join('Productos as p', 's.codpro', '=', 'p.CodPro')
                ->where('s.saldo', '>', 0)
                ->where('p.Eliminado', 0)
                ->selectRaw("
                    CASE 
                        WHEN DATEDIFF(day, ?, s.vencimiento) < 0 THEN 'Vencidos'
                        WHEN DATEDIFF(day, ?, s.vencimiento) <= 30 THEN '1-30 días'
                        WHEN DATEDIFF(day, ?, s.vencimiento) <= 60 THEN '31-60 días'
                        WHEN DATEDIFF(day, ?, s.vencimiento) <= 90 THEN '61-90 días'
                        ELSE '+90 días'
                    END AS rango,
                    COUNT(*) as cantidad_lotes,
                    SUM(s.saldo) as cantidad_total,
                    SUM(s.saldo * ISNULL(p.CosReal, p.Costo)) as valor_total
                ", [$hoy, $hoy, $hoy, $hoy]) 
                ->groupBy('rango')
                ->orderByRaw("
                    CASE 
                        WHEN rango = 'Vencidos' THEN 1
                        WHEN rango = '1-30 días' THEN 2
                        WHEN rango = '31-60 días' THEN 3
                        WHEN rango = '61-90 días' THEN 4
                        ELSE 5
                    END
                ")
                ->get();

            return $resultados->map(function ($vals) {
                return [
                    'rango' => $vals->rango,
                    'cantidad_lotes' => $vals->cantidad_lotes,
                    'cantidad_total' => round($vals->cantidad_total, 2),
                    'valor_total' => round($vals->valor_total, 2),
                    'color_class' => $this->obtenerColorVencimiento($vals->rango)
                ];
            })->toArray();
        });
    }

    public function analizarMoraDetalle()
    {
        // 🏷️ Agregamos el tag
        return Cache::tags([$this->cache_tag])->remember('mora_detalle', $this->cache_ttl, function () {
            Log::debug('EJECUTANDO QUERY (CON VISTA): analizarMoraDetalle');
            
            $resultados = VistaAgingCartera::select('Codclie', 'Razon')
                ->selectRaw('COUNT(*) as facturas_vencidas, 
                             SUM(Saldo) as total_mora,
                             AVG(dias_vencidos) as dias_promedio_mora,
                             MIN(FechaV) as factura_mas_antigua')
                ->where('dias_vencidos', '>', 0)
                ->groupBy('Codclie', 'Razon')
                ->having('total_mora', '>', 1000)
                ->orderByDesc('total_mora')
                ->limit(10)
                ->get();

            return $resultados->map(function($item) {
                return [
                    'codigo' => $item->Codclie,
                    'cliente' => $item->Razon,
                    'facturas_vencidas' => $item->facturas_vencidas,
                    'total_mora' => round($item->total_mora, 2),
                    'dias_promedio_mora' => round($item->dias_promedio_mora, 0),
                    'factura_mas_antigua' => $item->factura_mas_antigua ? Carbon::parse($item->factura_mas_antigua)->format('d/m/Y') : 'N/A',
                    'nivel_riesgo' => $this->determinarNivelRiesgo($item->dias_promedio_mora, $item->total_mora),
                    'dias_riesgo' => $this->calcularDiasRiesgo($item->dias_promedio_mora)
                ];
            })->toArray();
        });
    }

    public function generarAlertas()
    {
        // 🏷️ Agregamos el tag
        // Nota: Las alertas NO deberían estar cacheadas si son críticas, pero seguimos tu lógica
        return Cache::tags([$this->cache_tag])->remember('dashboard_alertas', $this->cache_ttl, function () {
            Log::debug('EJECUTANDO QUERY (CON MODELO): generarAlertas');
            $alertas = [];
            $fechaActual = now(); // <- 'now()' simple
            
            // ... (lógica de DIGEMID)
            
            $facturas60dias = CtaCliente::where('Saldo', '>', 0)
                ->where('FechaV', '<', $fechaActual->copy()->subDays(60))
                ->count();

            if ($facturas60dias > 0) {
                $alertas[] = [
                    'tipo' => 'danger', 'icono' => 'exclamation-triangle', 'titulo' => 'Facturas en Mora Crítica',
                    'mensaje' => "{$facturas60dias} facturas vencidas por más de 60 días",
                    'accion' => route('contador.facturas.index'), 'prioridad' => 'crítica'
                ];
            }
            
            return $alertas;
        });
    }

    
    public function calcularVentasMes()
    {
        $fechaHoy = now(); // <- 'now()' simple
        // 🏷️ Agregamos el tag
        return Cache::tags([$this->cache_tag])->remember('ventas_mes_' . $fechaHoy->format('Y-m'), $this->cache_ttl, function () use ($fechaHoy) {
            Log::debug('EJECUTANDO QUERY (CON MODELO): calcularVentasMes');
            return Doccab::whereYear('Fecha', $fechaHoy->year)
                ->whereMonth('Fecha', $fechaHoy->month)
                ->where('Eliminado', 0)
                ->sum('Total') ?? 0;
        });
    }

    public function calcularVentasMesAnterior()
    {
        $mesAnterior = now()->subMonth(); // <- 'now()' simple
        // 🏷️ Agregamos el tag
        return Cache::tags([$this->cache_tag])->remember('ventas_mes_anterior_' . $mesAnterior->format('Y-m'), $this->cache_ttl, function () use ($mesAnterior) {
            Log::debug('EJECUTANDO QUERY (CON MODELO): calcularVentasMesAnterior');
            return Doccab::whereYear('Fecha', $mesAnterior->year)
                ->whereMonth('Fecha', $mesAnterior->month)
                ->where('Eliminado', 0)
                ->sum('Total') ?? 0;
        });
    }

    public function calcularCuentasPorCobrar()
    {
        // 🏷️ Agregamos el tag
        return Cache::tags([$this->cache_tag])->remember('cuentas_cobrar_total', $this->cache_ttl, function () {
            Log::debug('EJECUTANDO QUERY (CON MODELO): calcularCuentasPorCobrar');
            return CtaCliente::where('Saldo', '>', 0)->sum('Saldo') ?? 0;
        });
    }

    public function calcularCuentasPorCobrarVencidas()
    {
        // 🏷️ Agregamos el tag
        return Cache::tags([$this->cache_tag])->remember('cuentas_cobrar_vencidas', $this->cache_ttl, function () {
            Log::debug('EJECUTANDO QUERY (CON MODELO): calcularCuentasPorCobrarVencidas');
            return CtaCliente::where('Saldo', '>', 0)
                ->where('FechaV', '<', now()) // <- 'now()' simple
                ->sum('Saldo') ?? 0;
        });
    }

    public function calcularMargenBrutoMes()
    {
        $fechaHoy = now(); // <- 'now()' simple
        // 🏷️ Agregamos el tag
        return Cache::tags([$this->cache_tag])->remember('margen_bruto_mes', $this->cache_ttl, function () use ($fechaHoy) {
            Log::debug('EJECUTANDO QUERY (DB::table): calcularMargenBrutoMes');
            $resultado = DB::table('Doccab as dc')
                ->join('Docdet as dd', function($join) {
                    $join->on('dc.Numero', '=', 'dd.Numero')
                         ->on('dc.Tipo', '=', 'dd.Tipo');
                })
                ->whereYear('dc.Fecha', $fechaHoy->year)
                ->whereMonth('dc.Fecha', $fechaHoy->month)
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

    public function obtenerVentasPorMes($cantidad = 6)
    {
        $cacheKey = 'ventas_por_mes_ultimos_' . $cantidad;
        // 🏷️ Agregamos el tag
        return Cache::tags([$this->cache_tag])->remember($cacheKey, $this->cache_ttl, function () use ($cantidad) {
            Log::debug('EJECUTANDO QUERY (OPTIMIZADA N+1): obtenerVentasPorMes');
            $fechaInicio = now()->subMonths($cantidad - 1)->startOfMonth(); // <- 'now()' simple
            $ventasPorMes = Doccab::where('Fecha', '>=', $fechaInicio)
                ->where('Eliminado', 0)
                ->select(
                    DB::raw('YEAR(Fecha) as anio'),
                    DB::raw('MONTH(Fecha) as mes'),
                    DB::raw('SUM(Total) as total')
                )
                ->groupBy(DB::raw('YEAR(Fecha)'), DB::raw('MONTH(Fecha)'))
                ->orderBy(DB::raw('YEAR(Fecha)'), 'asc') 
                ->orderBy(DB::raw('MONTH(Fecha)'), 'asc') 
                ->get()
                ->keyBy(fn($item) => $item->anio . '-' . $item->mes); 

            $datos = [];
            for ($i = $cantidad - 1; $i >= 0; $i--) {
                $fecha = now()->subMonths($i); // <- 'now()' simple
                $key = $fecha->year . '-' . $fecha->month;
                $datos[] = round($ventasPorMes->get($key)->total ?? 0, 2);
            }
            return $datos;
        });
    }
    
    public function obtenerCobranzasPorMes($cantidad = 6)
    {
        $cacheKey = 'cobranzas_por_mes_ultimos_' . $cantidad;
        // 🏷️ Agregamos el tag
        return Cache::tags([$this->cache_tag])->remember($cacheKey, $this->cache_ttl, function () use ($cantidad) {
            Log::debug('EJECUTANDO QUERY (OPTIMIZADA N+1): obtenerCobranzasPorMes');
            
            $fechaInicio = now()->subMonths($cantidad - 1)->startOfMonth(); // <- 'now()' simple

            $cobranzasPorMes = CtaCliente::where('FechaF', '>=', $fechaInicio)
                ->where('Saldo', 0) 
                ->select(
                    DB::raw('YEAR(FechaF) as anio'),
                    DB::raw('MONTH(FechaF) as mes'),
                    DB::raw('SUM(Importe) as total')
                )
                ->groupBy(DB::raw('YEAR(FechaF)'), DB::raw('MONTH(FechaF)'))
                ->orderBy(DB::raw('YEAR(FechaF)'), 'asc') 
                ->orderBy(DB::raw('MONTH(FechaF)'), 'asc') 
                ->get()
                ->keyBy(fn($item) => $item->anio . '-' . $item->mes);

            $datos = [];
            for ($i = $cantidad - 1; $i >= 0; $i--) {
                $fecha = now()->subMonths($i); // <- 'now()' simple
                $key = $fecha->year . '-' . $fecha->month;
                $datos[] = round($cobranzasPorMes->get($key)->total ?? 0, 2);
            }
            return $datos;
        });
    }

    public function obtenerTopClientesMes($limite = 10)
    {
        $fechaHoy = now(); // <- 'now()' simple
        // 🏷️ Agregamos el tag
        return Cache::tags([$this->cache_tag])->remember('top_clientes_' . $fechaHoy->format('Y-m'), $this->cache_ttl, function () use ($limite, $fechaHoy) {
            Log::debug('EJECUTANDO QUERY (CON MODELO): obtenerTopClientesMes');
            
            return Doccab::with('cliente:Codclie,Razon')
                ->whereYear('Fecha', $fechaHoy->year)
                ->whereMonth('Fecha', $fechaHoy->month)
                ->where('Eliminado', 0)
                ->select(
                    'CodClie',
                    DB::raw('COUNT(*) as total_facturas'),
                    DB::raw('SUM(Total) as total_ventas'),
                    DB::raw('AVG(Total) as ticket_promedio')
                )
                ->groupBy('CodClie') 
                ->orderBy('total_ventas', 'desc')
                ->limit($limite)
                ->get()
                ->map(function($item) {
                    $clienteNombre = trim($item->cliente->Razon ?? 'Cliente Desconocido');
                    $initial = empty($clienteNombre) ? '?' : mb_substr($clienteNombre, 0, 1);
                    return [
                        'codigo' => $item->CodClie,
                        'cliente' => $clienteNombre,
                        'initial' => $initial,
                        'facturas' => $item->total_facturas,
                        'total' => round($item->total_ventas, 2),
                        'ticket_promedio' => round($item->ticket_promedio, 2),
                        'avatar_color' => $this->getAvatarColor($item->CodClie)
                    ];
                })->toArray();
        });
    }
    public function obtenerVentasRecientes($limite = 15)
    {
        // 🏷️ Agregamos el tag
        return Cache::tags([$this->cache_tag])->remember('ventas_recientes_' . $limite, $this->cache_ttl, function () use ($limite) {
            Log::debug('EJECUTANDO QUERY (CON MODELO): obtenerVentasRecientes');
            
            return Doccab::with([
                    'cliente:Codclie,Razon', 
                    'cuentaPorCobrar'
                ])
                ->where('Eliminado', 0)
                ->orderBy('Fecha', 'desc')
                ->limit($limite)
                ->get()
                ->map(function($venta) {
                    $cuenta = $venta->cuentaPorCobrar;
                    $estado = 'SIN CTA';
                    $diasVencimiento = 0;
                    $saldo = 0;

                    if ($cuenta) {
                        $saldo = $cuenta->Saldo;
                        if ($saldo == 0) {
                            $estado = 'PAGADA';
                        } elseif ($cuenta->FechaV < now()) { // <- 'now()' simple
                            $estado = 'VENCIDA';
                            $diasVencimiento = now()->diffInDays(Carbon::parse($cuenta->FechaV)); // <- 'now()' simple
                        } else {
                            $estado = 'PENDIENTE';
                        }
                    }

                    return [
                        'numero' => trim($venta->Numero),
                        'tipo' => $this->obtenerTipoDocumento($venta->Tipo),
                        'cliente' => $venta->cliente->Razon ?? 'Sin Cliente',
                        'fecha' => Carbon::parse($venta->Fecha)->format('d/m/Y'),
                        'total' => round($venta->Total, 2),
                        'saldo' => round($saldo, 2),
                        'estado' => $estado,
                        'estado_class' => $this->obtenerClaseEstado($estado),
                        'dias_vencimiento' => $diasVencimiento,
                        'urgencia' => $diasVencimiento > 30 ? 'alta' : ($diasVencimiento > 0 ? 'media' : 'baja')
                    ];
                })->toArray();
        });
    }
    public function obtenerProductosStockBajo($limite = 10)
    {
        // 🏷️ Agregamos el tag
        return Cache::tags([$this->cache_tag])->remember('productos_stock_bajo_' . $limite, $this->cache_ttl, function () use ($limite) {
            Log::debug('EJECUTANDO QUERY (CON MODELO Y JOIN CORREGIDO): obtenerProductosStockBajo');
            
            $productos = Producto::with('laboratorio:CodLab,Descripcion')
                ->stockBajo() 
                ->select(
                    'CodPro', 'Nombre', 'CodProv', 'Stock', 'Minimo', 'CosReal', 'Costo',
                    DB::raw('(Stock / NULLIF(Minimo, 0)) * 100 as porcentaje')
                )
                ->orderBy('porcentaje', 'asc')
                ->limit($limite)
                ->get();
                
            return $productos->map(function($item) {
                $porcentaje = $item->porcentaje ?? 0;
                $unidad_valor = $item->CosReal ?? $item->Costo ?? 0;
                return [
                    'codigo' => trim($item->CodPro), 'nombre' => $item->Nombre,
                    'laboratorio' => $item->laboratorio->Descripcion ?? 'Sin laboratorio',
                    'stock' => round($item->Stock, 2), 'minimo' => round($item->Minimo, 2),
                    'porcentaje' => round($porcentaje, 0),
                    'valor_stock' => round($item->Stock * $unidad_valor, 2),
                    'criticidad' => $porcentaje < 20 ? 'crítica' : ($porcentaje < 50 ? 'alta' : 'media')
                ];
            })->toArray();
        });
    }

    public function obtenerProductosProximosVencer($limite = 10)
    {
        // 🏷️ Agregamos el tag
        return Cache::tags([$this->cache_tag])->remember('productos_proximos_vencer_' . $limite, $this->cache_ttl, function () use ($limite) {
            Log::debug('EJECUTANDO QUERY (CON VISTA): obtenerProductosProximosVencer');
            
            return VistaProductosPorVencer::orderBy('DiasParaVencer', 'asc')
                ->limit($limite)
                ->get()
                ->map(function($item) {
                    return [
                        'codigo' => trim($item->CodPro), 'nombre' => $item->Nombre,
                        'laboratorio' => $item->Laboratorio ?? 'Sin laboratorio', 'lote' => trim($item->Lote),
                        'vencimiento' => Carbon::parse($item->Vencimiento)->format('d/m/Y'),
                        'stock' => round($item->Stock, 2),
                        'valor_lote' => round($item->ValorInventario, 2),
                        'dias' => (int)$item->DiasParaVencer,
                        'riesgo' => $item->DiasParaVencer <= 30 ? 'alto' : ($item->DiasParaVencer <= 60 ? 'medio' : 'baja')
                    ];
                })->toArray();
        });
    }
    
    public function contarClientesActivos()
    {
        // 🏷️ Agregamos el tag
        return (int) Cache::tags([$this->cache_tag])->remember('clientes_activos', $this->cache_ttl, function () {
            Log::debug('EJECUTANDO QUERY (CON MODELO): contarClientesActivos');
            return Cliente::where('Activo', 1)->count();
        });
    }

    public function contarFacturasPendientes()
    {
        // 🏷️ Agregamos el tag
        return (int) Cache::tags([$this->cache_tag])->remember('facturas_pendientes', $this->cache_ttl, function () {
            Log::debug('EJECUTANDO QUERY (CON MODELO): contarFacturasPendientes');
            return CtaCliente::where('Saldo', '>', 0)->count();
        });
    }

    public function contarFacturasVencidas()
    {
        // 🏷️ Agregamos el tag
        return (int) Cache::tags([$this->cache_tag])->remember('facturas_vencidas', $this->cache_ttl, function () {
            Log::debug('EJECUTANDO QUERY (CON MODELO): contarFacturasVencidas');
            return CtaCliente::where('Saldo', '>', 0)
                ->whereNotNull('FechaV')
                ->where('FechaV', '<', Carbon::today()) // <- 'today()' simple
                ->count();
        });
    }

    public function calcularTicketPromedio()
    {
        $fechaHoy = now(); // <- 'now()' simple
        // 🏷️ Agregamos el tag
        return Cache::tags([$this->cache_tag])->remember('ticket_promedio_' . $fechaHoy->format('Y-m'), $this->cache_ttl, function () use ($fechaHoy) {
            Log::debug('EJECUTANDO QUERY (CON MODELO): calcularTicketPromedio');
            $avg = Doccab::whereYear('Fecha', $fechaHoy->year)
                ->whereMonth('Fecha', $fechaHoy->month)
                ->where('Eliminado', 0)
                ->avg('Total');
            return round((float) ($avg ?? 0), 2);
        });
    }

    public function calcularDiasPromedioCobranza()
    {
        // 🏷️ Agregamos el tag
        return Cache::tags([$this->cache_tag])->remember('dias_promedio_cobranza', $this->cache_ttl, function () {
            Log::debug('EJECUTANDO QUERY (CON MODELO): calcularDiasPromedioCobranza');
            
            $row = CtaCliente::selectRaw('AVG(CAST(DATEDIFF(day, FechaF, FechaV) AS FLOAT)) as avg_days')
                ->where('Saldo', 0) 
                ->whereYear('FechaV', now()->year) // <- 'now()' simple
                ->first();
            return $row && $row->avg_days ? round($row->avg_days) : 0;
        });
    }

    public function obtenerMesesLabels($cantidad = 6)
    {
        $labels = [];
        for ($i = $cantidad - 1; $i >= 0; $i--) {
            $dt = Carbon::now()->subMonths($i); // <- 'now()' simple
            $labels[] = $dt->locale('es')->translatedFormat('M/Y');
        }
        return $labels;
    }
    
      public function obtenerTipoDocumento($tipo)
    {
        $tipos = [1 => 'FACTURA', 2 => 'BOLETA', 3 => 'NOTA CRÉDITO', 4 => 'GUÍA'];
        return $tipos[$tipo] ?? 'DOCUMENTO';
    }

    public function obtenerClaseEstado($estado)
    {
        $clases = ['PAGADA' => 'success', 'PENDIENTE' => 'warning', 'VENCIDA' => 'danger'];
        return $clases[$estado] ?? 'secondary';
    }

    public function getAvatarColor($codigo)
    {
        $colors = ['primary', 'success', 'warning', 'danger', 'info', 'purple'];
        $hash = is_numeric($codigo) ? $codigo : hexdec(substr(md5($codigo), 0, 6));
        return $colors[$hash % count($colors)];
    }
    public function obtenerColorVencimiento($rango)
    {
        $colores = [
            'Vencidos' => 'danger', '1-30 días' => 'danger', '31-60 días' => 'warning',
            '61-90 días' => 'info', '+90 días' => 'success'
        ];
        return $colores[$rango] ?? 'secondary';
    }
     public function determinarNivelRiesgo($diasPromedio, $monto)
    {
        if ($diasPromedio > 90 || $monto > 50000) return 'crítico';
        if ($diasPromedio > 60 || $monto > 20000) return 'alto';
        if ($diasPromedio > 30 || $monto > 5000) return 'medio';
        return 'bajo';
    }
    public function calcularDiasRiesgo($diasMora)
    {
        if ($diasMora > 90) return 'Muy Alto';
        if ($diasMora > 60) return 'Alto';
        if ($diasMora > 30) return 'Medio';
        return 'Bajo';
    }
    public function getDatosVacios()
    {
        // Esta función no cambia
        return [
            'ventasMes' => 0, 'ventasMesAnterior' => 0, 'variacionVentas' => 0,
            'cuentasPorCobrar' => 0, 'cuentasPorCobrarVencidas' => 0,
            'clientesActivos' => 0, 'facturasPendientes' => 0, 'facturasVencidas' => 0,
            'ticketPromedio' => 0, 'diasPromedioCobranza' => 0, 'margenBrutoMes' => 0,
            'mesesLabels' => [], 'ventasData' => [], 'cobranzasData' => [],
            'topClientes' => [], 'ventasRecientes' => [], 'alertas' => [],
            'productosStockBajo' => [], 'productosProximosVencer' => [],
            'analisisFinanciero' => [], 'vencimientosPorRango' => [],
            'moraDetalle' => [], 'ultimasFacturas' => collect(), 'topClientesSaldo' => [],
        ];
    }
}