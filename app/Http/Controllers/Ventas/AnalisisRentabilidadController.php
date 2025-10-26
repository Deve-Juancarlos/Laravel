<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalisisRentabilidadController extends Controller
{
    /**
     * Constructor con middleware de autenticación y autorización
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('rol:contador|administrador');
    }

    /**
     * Dashboard principal de análisis de rentabilidad
     */
    public function index(Request $request)
    {
        $fechaDesde = $request->get('fecha_desde', now()->startOfMonth()->toDateString());
        $fechaHasta = $request->get('fecha_hasta', now()->endOfMonth()->toDateString());
        $tipoAnalisis = $request->get('tipo', 'general'); // general, producto, cliente, vendedor

        switch ($tipoAnalisis) {
            case 'producto':
                return $this->analisisRentabilidadProductos($fechaDesde, $fechaHasta);
            case 'cliente':
                return $this->analisisRentabilidadClientes($fechaDesde, $fechaHasta);
            case 'vendedor':
                return $this->analisisRentabilidadVendedores($fechaDesde, $fechaHasta);
            default:
                return $this->analisisRentabilidadGeneral($fechaDesde, $fechaHasta);
        }
    }

    /**
     * Análisis de rentabilidad general
     */
    public function analisisRentabilidadGeneral($fechaDesde, $fechaHasta)
    {
        // Consulta base de ventas con márgenes
        $ventas = DB::table('Doccab')
            ->join('Docdet', 'Doccab.Numero', '=', 'Docdet.Numero')
            ->join('Productos', 'Docdet.CodPro', '=', 'Productos.CodPro')
            ->leftJoin('Clientes', 'Doccab.CodClie', '=', 'Clientes.Codclie')
            ->whereBetween('Doccab.Fecha', [$fechaDesde, $fechaHasta])
            ->where('Doccab.Tipo', '!=', 'AN')
            ->select([
                'Doccab.Numero',
                'Doccab.Fecha',
                'Doccab.Total as TotalFac',
                'Docdet.Codpro',
                'Docdet.Cantidad',
                'Docdet.Precio',
                'Docdet.Subtotal',
                'Productos.Nombre as Producto',
                'Productos.Costo',
                'Productos.Categoria',
                'Clientes.Razon as Cliente',
                'Doccab.Vendedor'
            ])
            ->get();

        // Calcular rentabilidad por venta
        $ventasCalculadas = $ventas->map(function ($venta) {
            $costoTotal = $venta->Cantidad * ($venta->Costo ?? 0);
            $margen = $venta->Subtotal - $costoTotal;
            $margenPorcentaje = $venta->Subtotal > 0 ? ($margen / $venta->Subtotal) * 100 : 0;
            
            $venta->costo_total = $costoTotal;
            $venta->margen_absoluto = $margen;
            $venta->margen_porcentaje = $margenPorcentaje;
            $venta->rentabilidad = $this->clasificarRentabilidad($margenPorcentaje);
            
            return $venta;
        });

        // Resumen general
        $resumen = [
            'total_ventas' => $ventasCalculadas->sum('Subtotal'),
            'total_costos' => $ventasCalculadas->sum('costo_total'),
            'margen_total' => $ventasCalculadas->sum('margen_absoluto'),
            'margen_promedio' => $ventasCalculadas->avg('margen_porcentaje'),
            'rentabilidad_global' => $this->calcularRentabilidadGlobal($ventasCalculadas),
            'numero_transacciones' => $ventasCalculadas->groupBy('Numero')->count()
        ];

        // Análisis por categorías
        $porCategorias = $this->analizarRentabilidadPorCategoria($ventasCalculadas);
        
        // Tendencia de rentabilidad
        $tendencia = $this->analizarTendenciaRentabilidad($fechaDesde, $fechaHasta);
        
        // Productos más y menos rentables
        $productosDestacados = $this->productosDestacados($ventasCalculadas);

        return response()->json([
            'resumen' => $resumen,
            'por_categorias' => $porCategorias,
            'tendencia' => $tendencia,
            'productos_destacados' => $productosDestacados,
            'periodo' => [
                'desde' => $fechaDesde,
                'hasta' => $fechaHasta
            ]
        ]);
    }

    /**
     * Análisis de rentabilidad por productos
     */
    public function analisisRentabilidadProductos($fechaDesde, $fechaHasta)
    {
        $productos = DB::table('Docdet')
            ->join('Doccab', 'Docdet.Numero', '=', 'Doccab.Numero')
            ->join('Productos', 'Docdet.CodPro', '=', 'Productos.CodPro')
            ->whereBetween('Doccab.Fecha', [$fechaDesde, $fechaHasta])
            ->where('Doccab.Tipo', '!=', 'AN')
            ->select([
                'Productos.CodPro',
                'Productos.Nombre as Producto',
                'Productos.Categoria',
                'Productos.Costo',
                DB::raw('SUM(Docdet.Cantidad) as cantidad_vendida'),
                DB::raw('SUM(Docdet.Subtotal) as ingresos_totales'),
                DB::raw('AVG(Docdet.Precio) as precio_promedio'),
                DB::raw('COUNT(DISTINCT Docdet.Numero) as frecuencia_venta')
            ])
            ->groupBy('Productos.CodPro', 'Productos.Nombre', 'Productos.Categoria', 'Productos.Costo')
            ->get();

        // Calcular métricas de rentabilidad
        $productosAnalizados = $productos->map(function ($producto) {
            $costoTotal = $producto->cantidad_vendida * ($producto->Costo ?? 0);
            $margen = $producto->ingresos_totales - $costoTotal;
            $margenPorcentaje = $producto->ingresos_totales > 0 ? ($margen / $producto->ingresos_totales) * 100 : 0;
            
            $producto->costo_total = $costoTotal;
            $producto->margen_absoluto = $margen;
            $producto->margen_porcentaje = $margenPorcentaje;
            $producto->rentabilidad = $this->clasificarRentabilidad($margenPorcentaje);
            $producto->roi = $producto->Costo > 0 ? ($margen / $producto->Costo) * 100 : 0;
            
            return $producto;
        });

        // Top productos más rentables
        $topRentables = $productosAnalizados
            ->sortByDesc('margen_porcentaje')
            ->take(10);

        // Productos con bajo margen
        $bajoMargen = $productosAnalizados
            ->sortBy('margen_porcentaje')
            ->take(10);

        // Análisis por categoría
        $porCategoria = $productosAnalizados->groupBy('Categoria')->map(function ($categoria) {
            return [
                'total_productos' => $categoria->count(),
                'ingresos_totales' => $categoria->sum('ingresos_totales'),
                'margen_promedio' => $categoria->avg('margen_porcentaje'),
                'mejor_producto' => $categoria->sortByDesc('margen_porcentaje')->first()
            ];
        });

        return response()->json([
            'productos' => $productosAnalizados->sortByDesc('ingresos_totales'),
            'top_rentables' => $topRentables,
            'bajo_margen' => $bajoMargen,
            'por_categoria' => $porCategoria,
            'periodo' => [
                'desde' => $fechaDesde,
                'hasta' => $fechaHasta
            ]
        ]);
    }

    /**
     * Análisis de rentabilidad por clientes
     */
    public function analisisRentabilidadClientes($fechaDesde, $fechaHasta)
    {
        $clientes = DB::table('Doccab')
            ->leftJoin('Clientes', 'Doccab.CodClie', '=', 'Clientes.Codclie')
            ->leftJoin('Docdet', 'Doccab.Numero', '=', 'Docdet.Numero')
            ->leftJoin('Productos', 'Docdet.Codpro', '=', 'Productos.CodPro')
            ->whereBetween('Doccab.Fecha', [$fechaDesde, $fechaHasta])
            ->where('Doccab.Tipo', '!=', 'AN')
            ->select([
                'Clientes.Codclie',
                'Clientes.Razon as Cliente',
                'Clientes.Categoria as CategoriaCli',
                DB::raw('COUNT(DISTINCT Doccab.Numero) as numero_compras'),
                DB::raw('SUM(Doccab.Total) as total_facturado'),
                DB::raw('SUM(Docdet.Cantidad) as total_items'),
                DB::raw('AVG(Doccab.Total) as ticket_promedio'),
                DB::raw('SUM(Docdet.Cantidad * COALESCE(Productos.Costo, 0)) as costo_estimado')
            ])
            ->groupBy('Clientes.Codclie', 'Clientes.Razon', 'Clientes.Categoria')
            ->get();

        // Calcular rentabilidad por cliente
        $clientesAnalizados = $clientes->map(function ($cliente) {
            $margen = $cliente->total_facturado - ($cliente->costo_estimado ?? 0);
            $margenPorcentaje = $cliente->total_facturado > 0 ? ($margen / $cliente->total_facturado) * 100 : 0;
            $rentabilidad = $margen / max($cliente->numero_compras, 1);
            
            $cliente->margen_absoluto = $margen;
            $cliente->margen_porcentaje = $margenPorcentaje;
            $cliente->rentabilidad_por_compra = $rentabilidad;
            $cliente->clasificacion = $this->clasificarCliente($cliente->total_facturado, $margenPorcentaje);
            
            return $cliente;
        });

        // Top clientes más rentables
        $topClientes = $clientesAnalizados
            ->sortByDesc('margen_absoluto')
            ->take(10);

        // Clientes por categoría de rentabilidad
        $porCategoria = [
            'alto_valor' => $clientesAnalizados->where('clasificacion', 'ALTO_VALOR'),
            'rentables' => $clientesAnalizados->where('clasificacion', 'RENTABLE'),
            'medianos' => $clientesAnalizados->where('clasificacion', 'MEDIANO'),
            'bajo_rendimiento' => $clientesAnalizados->where('clasificacion', 'BAJO_RENDIMIENTO')
        ];

        // Análisis de frecuencia vs rentabilidad
        $analisisFrecuencia = $this->analizarFrecuenciaVsRentabilidad($clientesAnalizados);

        return response()->json([
            'clientes' => $clientesAnalizados->sortByDesc('total_facturado'),
            'top_clientes' => $topClientes,
            'por_categoria' => $porCategoria,
            'analisis_frecuencia' => $analisisFrecuencia,
            'periodo' => [
                'desde' => $fechaDesde,
                'hasta' => $fechaHasta
            ]
        ]);
    }

    /**
     * Análisis de rentabilidad por vendedores
     */
    public function analisisRentabilidadVendedores($fechaDesde, $fechaHasta)
    {
        $vendedores = DB::table('Doccab')
            ->leftJoin('Docdet', 'Doccab.Numero', '=', 'Docdet.Numero')
            ->leftJoin('Productos', 'Docdet.Codpro', '=', 'Productos.CodPro')
            ->leftJoin('accesoweb', 'Doccab.Vendedor', '=', 'accesoweb.usuario')
            ->whereBetween('Doccab.Fecha', [$fechaDesde, $fechaHasta])
            ->where('Doccab.Tipo', '!=', 'AN')
            ->select([
                'Doccab.Vendedor',
                'accesoweb.usuario as VendedorNombre',
                'Usuarios.Cargo',
                DB::raw('COUNT(DISTINCT Doccab.Numero) as ventas_realizadas'),
                DB::raw('SUM(Doccab.Total) as ventas_totales'),
                DB::raw('AVG(Doccab.Total) as ticket_promedio'),
                DB::raw('SUM(Docdet.Cantidad * COALESCE(Productos.Costo, 0)) as costo_total_vendido'),
                DB::raw('SUM(Docdet.Subtotal) as ingresos_totales')
            ])
            ->groupBy('Doccab.Vendedor', 'accesoweb.usuario', 'Usuarios.Cargo')
            ->get();

        // Calcular rentabilidad por vendedor
        $vendedoresAnalizados = $vendedores->map(function ($vendedor) {
            $margen = $vendedor->ingresos_totales - ($vendedor->costo_total_vendido ?? 0);
            $margenPorcentaje = $vendedor->ingresos_totales > 0 ? ($margen / $vendedor->ingresos_totales) * 100 : 0;
            $rentabilidadPorVenta = $margen / max($vendedor->ventas_realizadas, 1);
            
            $vendedor->margen_absoluto = $margen;
            $vendedor->margen_porcentaje = $margenPorcentaje;
            $vendedor->rentabilidad_por_venta = $rentabilidadPorVenta;
            $vendedor->clasificacion = $this->clasificarVendedor($vendedor->ventas_totales, $margenPorcentaje);
            
            return $vendedor;
        });

        // Ranking de vendedores
        $ranking = [
            'por_ventas' => $vendedoresAnalizados->sortByDesc('ventas_totales'),
            'por_margen' => $vendedoresAnalizados->sortByDesc('margen_absoluto'),
            'por_ticket' => $vendedoresAnalizados->sortByDesc('ticket_promedio')
        ];

        // Análisis de efectividad
        $efectividad = $this->analizarEfectividadVendedores($vendedoresAnalizados);

        return response()->json([
            'vendedores' => $vendedoresAnalizados,
            'ranking' => $ranking,
            'efectividad' => $efectividad,
            'periodo' => [
                'desde' => $fechaDesde,
                'hasta' => $fechaHasta
            ]
        ]);
    }

    /**
     * Análisis de punto de equilibrio
     */
    public function puntoEquilibrio(Request $request)
    {
        $año = $request->get('año', now()->year);
        $mes = $request->get('mes', now()->month);
        $productoId = $request->get('producto_id');

        if ($productoId) {
            // Punto de equilibrio por producto específico
            return $this->puntoEquilibrioProducto($productoId, $año, $mes);
        }

        // Punto de equilibrio general
        return $this->puntoEquilibrioGeneral($año, $mes);
    }

    /**
     * Punto de equilibrio por producto
     */
    private function puntoEquilibrioProducto($productoId, $año, $mes)
    {
        $producto = DB::table('Productos')->where('CodPro', $productoId)->first();
        
        if (!$producto) {
            return response()->json(['error' => 'Producto no encontrado'], 404);
        }

        // Datos históricos del producto
        $ventasProducto = DB::table('Docdet')
            ->join('Doccab', 'Docdet.Numero', '=', 'Doccab.Numero')
            ->where('Docdet.Codpro', $productoId)
            ->whereYear('Doccab.Fecha', $año)
            ->whereMonth('Doccab.Fecha', $mes)
            ->select([
                'Doccab.Fecha',
                'Docdet.Cantidad',
                'Docdet.Precio'
            ])
            ->get();

        $precioPromedio = $ventasProducto->avg('Precio') ?? $producto->Precio ?? 0;
        $costoUnitario = $producto->Costo ?? 0;
        $cantidadVendida = $ventasProducto->sum('Cantidad');

        // Costos fijos (estimados)
        $costosFijos = $this->calcularCostosFijosProducto($productoId);

        // Calcular punto de equilibrio
        $margenUnitario = $precioPromedio - $costoUnitario;
        $puntoEquilibrioCantidad = $margenUnitario > 0 ? ceil($costosFijos / $margenUnitario) : 0;
        $puntoEquilibrioValor = $puntoEquilibrioCantidad * $precioPromedio;

        return response()->json([
            'producto' => $producto,
            'precio_promedio' => $precioPromedio,
            'costo_unitario' => $costoUnitario,
            'margen_unitario' => $margenUnitario,
            'costos_fijos' => $costosFijos,
            'punto_equilibrio' => [
                'cantidad' => $puntoEquilibrioCantidad,
                'valor' => number_format($puntoEquilibrioValor, 2)
            ],
            'ventas_actuales' => $cantidadVendida,
            'supera_equilibrio' => $cantidadVendida >= $puntoEquilibrioCantidad
        ]);
    }

    /**
     * Análisis de tendencias de rentabilidad
     */
    public function tendenciasRentabilidad(Request $request)
    {
        $periodo = $request->get('periodo', 'mensual'); // diario, semanal, mensual
        $años = $request->get('años', 1); // cuántos años hacia atrás

        $fechaInicio = match($periodo) {
            'diario' => now()->subDays(30 * $años),
            'semanal' => now()->subWeeks(52 * $años),
            'mensual' => now()->subMonths(12 * $años),
            default => now()->subMonths(12 * $años)
        };

        $tendencias = DB::table('Doccab')
            ->join('Docdet', 'Doccab.Numero', '=', 'Docdet.Numero')
            ->leftJoin('Productos', 'Docdet.Codpro', '=', 'Productos.CodPro')
            ->where('Doccab.Fecha', '>=', $fechaInicio)
            ->where('Doccab.Tipo', '!=', 'AN')
            ->select([
                $this->getPeriodoSelect($periodo, 'Doccab.Fecha') . ' as periodo',
                DB::raw('SUM(Docdet.Subtotal) as ingresos'),
                DB::raw('SUM(Docdet.Cantidad * COALESCE(Productos.Costo, 0)) as costos'),
                DB::raw('COUNT(DISTINCT Doccab.Numero) as transacciones')
            ])
            ->groupBy($this->getPeriodoGroup($periodo))
            ->orderBy('periodo')
            ->get();

        // Calcular márgenes y tendencias
        $tendenciasCalculadas = $tendencias->map(function ($tendencia) {
            $tendencia->margen = $tendencia->ingresos - ($tendencia->costos ?? 0);
            $tendencia->margen_porcentaje = $tendencia->ingresos > 0 ? 
                ($tendencia->margen / $tendencia->ingresos) * 100 : 0;
            
            $tendencia->rentabilidad = $tendencia->transacciones > 0 ?
                ($tendencia->margen / $tendencia->transacciones) : 0;
                
            return $tendencia;
        });

        // Análisis de tendencias estadísticas
        $analisisTendencias = $this->calcularTendenciasEstadisticas($tendenciasCalculadas);

        return response()->json([
            'tendencias' => $tendenciasCalculadas,
            'analisis' => $analisisTendencias,
            'periodo' => [
                'tipo' => $periodo,
                'años_analizados' => $años,
                'inicio' => $fechaInicio->toDateString(),
                'fin' => now()->toDateString()
            ]
        ]);
    }
            
    /**
     * Recomendaciones de rentabilidad
     */
    public function recomendaciones(Request $request)
    {
        $fechaDesde = $request->get('fecha_desde', now()->startOfMonth()->toDateString());
        $fechaHasta = $request->get('fecha_hasta', now()->endOfMonth()->toDateString());

        $recomendaciones = [];

        // Analizar productos con bajo margen
        $productosBajoMargen = $this->identificarProductosBajoMargen($fechaDesde, $fechaHasta);
        if ($productosBajoMargen->isNotEmpty()) {
            $recomendaciones[] = [
                'tipo' => 'precio',
                'categoria' => 'productos',
                'titulo' => 'Revisar precios de productos',
                'descripcion' => 'Los siguientes productos tienen márgenes por debajo del 15%:',
                'items' => $productosBajoMargen->take(5)->pluck('Producto'),
                'accion_sugerida' => 'Considerar aumentar precios o reducir costos'
            ];
        }

        // Analizar clientes de bajo valor
        $clientesBajoValor = $this->identificarClientesBajoValor($fechaDesde, $fechaHasta);
        if ($clientesBajoValor->isNotEmpty()) {
            $recomendaciones[] = [
                'tipo' => 'clientes',
                'categoria' => 'retencion',
                'titulo' => 'Estrategia para clientes de bajo valor',
                'descripcion' => 'Identificar clientes con baja rentabilidad:',
                'items' => $clientesBajoValor->take(3)->pluck('Cliente'),
                'accion_sugerida' => 'Implementar programas de fidelización o políticas de crédito'
            ];
        }

        // Analizar vendedores con bajo rendimiento
        $vendedoresBajRendimiento = $this->identificarVendedoresBajRendimiento($fechaDesde, $fechaHasta);
        if ($vendedoresBajRendimiento->isNotEmpty()) {
            $recomendaciones[] = [
                'tipo' => 'vendedores',
                'categoria' => 'capacitacion',
                'titulo' => 'Capacitación de vendedores',
                'descripcion' => 'Vendedores con rentabilidad por debajo del promedio:',
                'items' => $vendedoresBajRendimiento->take(3)->pluck('VendedorNombre'),
                'accion_sugerida' => 'Entrenamiento en técnicas de venta y conocimiento de productos'
            ];
        }

        // Oportunidades de crecimiento
        $oportunidades = $this->identificarOportunidadesCrecimiento($fechaDesde, $fechaHasta);
        $recomendaciones = array_merge($recomendaciones, $oportunidades);

        return response()->json([
            'recomendaciones' => $recomendaciones,
            'prioridad' => $this->priorizarRecomendaciones($recomendaciones),
            'fecha_analisis' => now()->toDateString()
        ]);
    }

    // ===== MÉTODOS PRIVADOS DE SOPORTE =====

    /**
     * Clasificar rentabilidad por porcentaje
     */
    private function clasificarRentabilidad($margenPorcentaje)
    {
        if ($margenPorcentaje >= 40) return 'EXCELENTE';
        if ($margenPorcentaje >= 25) return 'BUENA';
        if ($margenPorcentaje >= 15) return 'REGULAR';
        if ($margenPorcentaje >= 5) return 'BAJA';
        return 'MUY_BAJA';
    }

    /**
     * Clasificar cliente por valor y rentabilidad
     */
    private function clasificarCliente($totalFacturado, $margenPorcentaje)
    {
        if ($totalFacturado >= 50000 && $margenPorcentaje >= 20) return 'ALTO_VALOR';
        if ($totalFacturado >= 10000 && $margenPorcentaje >= 15) return 'RENTABLE';
        if ($totalFacturado >= 5000 && $margenPorcentaje >= 10) return 'MEDIANO';
        return 'BAJO_RENDIMIENTO';
    }

    /**
     * Clasificar vendedor
     */
    private function clasificarVendedor($ventasTotales, $margenPorcentaje)
    {
        if ($ventasTotales >= 100000 && $margenPorcentaje >= 20) return 'ESTRELLA';
        if ($ventasTotales >= 50000 && $margenPorcentaje >= 15) return 'BUEN_RENDIMIENTO';
        if ($ventasTotales >= 25000 && $margenPorcentaje >= 10) return 'ACEPTABLE';
        return 'NECESITA_MEJORA';
    }

    /**
     * Calcular rentabilidad global
     */
    private function calcularRentabilidadGlobal($ventas)
    {
        $totalIngresos = $ventas->sum('Subtotal');
        $totalCostos = $ventas->sum('costo_total');
        return $totalIngresos > 0 ? (($totalIngresos - $totalCostos) / $totalIngresos) * 100 : 0;
    }

    /**
     * Analizar rentabilidad por categoría
     */
    private function analizarRentabilidadPorCategoria($ventas)
    {
        return $ventas->groupBy('Categoria')->map(function ($categoria) {
            return [
                'total_ventas' => $categoria->sum('Subtotal'),
                'total_costos' => $categoria->sum('costo_total'),
                'margen_promedio' => $categoria->avg('margen_porcentaje'),
                'productos_count' => $categoria->groupBy('Codpro')->count(),
                'mejor_producto' => $categoria->sortByDesc('margen_porcentaje')->first()
            ];
        });
    }

    /**
     * Analizar tendencia de rentabilidad
     */
    private function analizarTendenciaRentabilidad($fechaDesde, $fechaHasta)
    {
        $tendencia = DB::table('Doccab')
            ->join('Docdet', 'Doccab.Numero', '=', 'Docdet.Numero')
            ->leftJoin('Productos', 'Docdet.Codpro', '=', 'Productos.CodPro')
            ->whereBetween('Doccab.Fecha', [$fechaDesde, $fechaHasta])
            ->where('Doccab.Tipo', '!=', 'AN')
            ->select([
                DB::raw('WEEK(Doccab.Fecha) as semana'),
                DB::raw('SUM(Docdet.Subtotal) as ingresos'),
                DB::raw('SUM(Docdet.Cantidad * COALESCE(Productos.Costo, 0)) as costos')
            ])
            ->groupBy(DB::raw('WEEK(Doccab.Fecha)'))
            ->orderBy('semana')
            ->get()
            ->map(function ($semana) {
                $semana->margen = $semana->ingresos - ($semana->costos ?? 0);
                $semana->margen_porcentaje = $semana->ingresos > 0 ? 
                    ($semana->margen / $semana->ingresos) * 100 : 0;
                return $semana;
            });

        return $tendencia;
    }

    /**
     * Productos destacados
     */
    private function productosDestacados($ventas)
    {
        $agrupados = $ventas->groupBy('Codpro')->map(function ($productoVentas) {
            return [
                'producto' => $productoVentas->first()->Producto,
                'ingresos' => $productoVentas->sum('Subtotal'),
                'margen_promedio' => $productoVentas->avg('margen_porcentaje'),
                'frecuencia' => $productoVentas->count()
            ];
        });

        return [
            'mas_vendidos' => $agrupados->sortByDesc('frecuencia')->take(5),
            'mayor_margen' => $agrupados->sortByDesc('margen_promedio')->take(5),
            'mayor_ingresos' => $agrupados->sortByDesc('ingresos')->take(5)
        ];
    }

    /**
     * Analizar frecuencia vs rentabilidad de clientes
     */
    private function analizarFrecuenciaVsRentabilidad($clientes)
    {
        $frecuenciaAlta = $clientes->where('numero_compras', '>=', 10)->avg('margen_porcentaje');
        $frecuenciaMedia = $clientes->whereBetween('numero_compras', [3, 9])->avg('margen_porcentaje');
        $frecuenciaBaja = $clientes->where('numero_compras', '<=', 2)->avg('margen_porcentaje');

        return [
            'frecuencia_alta' => number_format($frecuenciaAlta ?? 0, 2),
            'frecuencia_media' => number_format($frecuenciaMedia ?? 0, 2),
            'frecuencia_baja' => number_format($frecuenciaBaja ?? 0, 2)
        ];
    }

    /**
     * Analizar efectividad de vendedores
     */
    private function analizarEfectividadVendedores($vendedores)
    {
        $promedioVentas = $vendedores->avg('ventas_totales');
        $promedioMargen = $vendedores->avg('margen_porcentaje');

        return $vendedores->map(function ($vendedor) use ($promedioVentas, $promedioMargen) {
            $vendedor->efectividad_ventas = $promedioVentas > 0 ? 
                ($vendedor->ventas_totales / $promedioVentas) * 100 : 0;
            $vendedor->efectividad_margen = $promedioMargen > 0 ? 
                ($vendedor->margen_porcentaje / $promedioMargen) * 100 : 0;
            return $vendedor;
        });
    }

    /**
     * Calcular costos fijos por producto
     */
    private function calcularCostosFijosProducto($productoId)
    {
        // Implementación básica - en producción sería más compleja
        return 5000; // S/ 5000 mensuales como costos fijos estimados
    }

    /**
     * Obtener select según período
     */
    private function getPeriodoSelect($periodo, $campoFecha)
    {
        return match($periodo) {
            'diario' => "DATE({$campoFecha})",
            'semanal' => "WEEK({$campoFecha})",
            'mensual' => "MONTH({$campoFecha})",
            default => "MONTH({$campoFecha})"
        };
    }

    /**
     * Obtener group by según período
     */
    private function getPeriodoGroup($periodo)
    {
        return match($periodo) {
            'diario' => DB::raw('DATE(Doccab.Fecha)'),
            'semanal' => DB::raw('WEEK(Doccab.Fecha)'),
            'mensual' => DB::raw('MONTH(Doccab.Fecha)'),
            default => DB::raw('MONTH(Doccab.Fecha)')
        };
    }

    /**
     * Calcular tendencias estadísticas
     */
    private function calcularTendenciasEstadisticas($tendencias)
    {
        if ($tendencias->count() < 2) {
            return ['tendencia' => 'insuficientes_datos'];
        }

        $margenes = $tendencias->pluck('margen_porcentaje');
        $media = $margenes->avg();
        $tendencia = $this->calcularRegresionLineal($margenes->values());

        return [
            'tendencia' => $tendencia > 0 ? 'mejorando' : ($tendencia < 0 ? 'empeorando' : 'estable'),
            'variacion' => number_format(abs($tendencia), 2),
            'media_margen' => number_format($media, 2),
            'volatilidad' => $this->calcularVolatilidad($margenes)
        ];
    }

    /**
     * Regresión lineal simple
     */
    private function calcularRegresionLineal($valores)
    {
        $n = count($valores);
        $sumX = 0;
        $sumY = 0;
        $sumXY = 0;
        $sumXX = 0;

        for ($i = 0; $i < $n; $i++) {
            $x = $i + 1;
            $y = $valores[$i];
            $sumX += $x;
            $sumY += $y;
            $sumXY += $x * $y;
            $sumXX += $x * $x;
        }

        return ($n * $sumXY - $sumX * $sumY) / ($n * $sumXX - $sumX * $sumX);
    }

    /**
     * Calcular volatilidad
     */
    private function calcularVolatilidad($valores)
    {
        $media = $valores->avg();
        $varianza = $valores->map(function ($valor) use ($media) {
            return pow($valor - $media, 2);
        })->avg();
        
        return number_format(sqrt($varianza), 2);
    }

    /**
     * Identificar productos con bajo margen
     */
    private function identificarProductosBajoMargen($fechaDesde, $fechaHasta)
    {
        return DB::table('Docdet')
            ->join('Doccab', 'Docdet.Numero', '=', 'Doccab.Numero')
            ->join('Productos', 'Docdet.CodPro', '=', 'Productos.CodPro')
            ->whereBetween('Doccab.Fecha', [$fechaDesde, $fechaHasta])
            ->where('Doccab.Tipo', '!=', 'AN')
            ->select([
                'Productos.CodPro',
                'Productos.Nombre as Producto',
                DB::raw('AVG(Docdet.Subtotal / (Docdet.Cantidad * COALESCE(Productos.Costo, 1)) - 1) * 100 as margen_porcentaje')
            ])
            ->groupBy('Productos.CodPro', 'Productos.Nombre')
            ->having('margen_porcentaje', '<', 15)
            ->orderBy('margen_porcentaje')
            ->limit(10)
            ->get();
    }

    /**
     * Identificar clientes de bajo valor
     */
    private function identificarClientesBajoValor($fechaDesde, $fechaHasta)
    {
        return DB::table('Doccab')
            ->leftJoin('Clientes', 'Doccab.CodClie', '=', 'Clientes.Codclie')
            ->leftJoin('Docdet', 'Doccab.Numero', '=', 'Docdet.Numero')
            ->leftJoin('Productos', 'Docdet.Codpro', '=', 'Productos.CodPro')
            ->whereBetween('Doccab.Fecha', [$fechaDesde, $fechaHasta])
            ->where('Doccab.Tipo', '!=', 'AN')
            ->select([
                'Clientes.Razon as Cliente',
                DB::raw('SUM(Doccab.Total) as total_facturado'),
                DB::raw('AVG((Docdet.Subtotal - Docdet.Cantidad * COALESCE(Productos.Costo, 0)) / Docdet.Subtotal * 100) as margen_promedio')
            ])
            ->groupBy('Clientes.Codclie', 'Clientes.Razon')
            ->having('total_facturado', '<', 5000)
            ->having('margen_promedio', '<', 10)
            ->orderBy('total_facturado')
            ->limit(10)
            ->get();
    }

    /**
     * Identificar vendedores de bajo rendimiento
     */
    private function identificarVendedoresBajRendimiento($fechaDesde, $fechaHasta)
    {
        $promedioGeneral = DB::table('Doccab')
            ->leftJoin('Docdet', 'Doccab.Numero', '=', 'Docdet.Numero')
            ->leftJoin('Productos', 'Docdet.Codpro', '=', 'Productos.CodPro')
            ->whereBetween('Doccab.Fecha', [$fechaDesde, $fechaHasta])
            ->where('Doccab.Tipo', '!=', 'AN')
            ->select([
                DB::raw('AVG((Docdet.Subtotal - Docdet.Cantidad * COALESCE(Productos.Costo, 0)) / Docdet.Subtotal * 100) as margen_promedio_general')
            ])->first()->margen_promedio_general ?? 15;

        return DB::table('Doccab')
            ->leftJoin('accesoweb', 'Doccab.Vendedor', '=', 'accesoweb.usuario')
            ->leftJoin('Docdet', 'Doccab.Numero', '=', 'Docdet.Numero')
            ->leftJoin('Productos', 'Docdet.Codpro', '=', 'Productos.CodPro')
            ->whereBetween('Doccab.Fecha', [$fechaDesde, $fechaHasta])
            ->where('Doccab.Tipo', '!=', 'AN')
            ->select([
                'Doccab.Vendedor',
                'accesoweb.usuario as VendedorNombre',
                DB::raw('SUM(Doccab.Total) as ventas_totales'),
                DB::raw('AVG((Docdet.Subtotal - Docdet.Cantidad * COALESCE(Productos.Costo, 0)) / Docdet.Subtotal * 100) as margen_promedio')
            ])
            ->groupBy('Doccab.Vendedor', 'accesoweb.usuario')
            ->having('margen_promedio', '<', $promedioGeneral * 0.8)
            ->orderBy('ventas_totales', 'desc')
            ->limit(10)
            ->get();
    }

    /**
     * Identificar oportunidades de crecimiento
     */
    private function identificarOportunidadesCrecimiento($fechaDesde, $fechaHasta)
    {
        $oportunidades = [];

        // Categorías con potencial
        $categoriasPotencial = DB::table('Docdet')
            ->join('Doccab', 'Docdet.Numero', '=', 'Doccab.Numero')
            ->join('Productos', 'Docdet.CodPro', '=', 'Productos.CodPro')
            ->whereBetween('Doccab.Fecha', [$fechaDesde, $fechaHasta])
            ->where('Doccab.Tipo', '!=', 'AN')
            ->select([
                'Productos.Categoria',
                DB::raw('COUNT(DISTINCT Productos.CodPro) as productos_activos'),
                DB::raw('SUM(Docdet.Subtotal) as ingresos_categoria')
            ])
            ->groupBy('Productos.Categoria')
            ->orderByDesc('productos_activos')
            ->limit(3)
            ->get();

        foreach ($categoriasPotencial as $categoria) {
            $oportunidades[] = [
                'tipo' => 'expansion',
                'categoria' => 'productos',
                'titulo' => 'Expandir categoría: ' . $categoria->Categoria,
                'descripcion' => "La categoría tiene {$categoria->productos_activos} productos activos",
                'items' => [$categoria->Categoria],
                'accion_sugerida' => 'Agregar más productos a esta categoría rentable'
            ];
        }

        return $oportunidades;
    }

    /**
     * Priorizar recomendaciones
     */
    private function priorizarRecomendaciones($recomendaciones)
    {
        $prioridades = ['precio' => 1, 'clientes' => 2, 'vendedores' => 3, 'expansion' => 4];
        
        return collect($recomendaciones)->sortBy(function ($rec) use ($prioridades) {
            return $prioridades[$rec['tipo']] ?? 5;
        })->values();
    }

    /**
     * Exportar análisis de rentabilidad
     */
    public function exportar(Request $request)
    {
        $tipo = $request->get('tipo', 'general');
        $fechaDesde = $request->get('fecha_desde', now()->startOfMonth()->toDateString());
        $fechaHasta = $request->get('fecha_hasta', now()->endOfMonth()->toDateString());

        switch ($tipo) {
            case 'productos':
                $datos = $this->analisisRentabilidadProductos($fechaDesde, $fechaHasta)->getData();
                break;
            case 'clientes':
                $datos = $this->analisisRentabilidadClientes($fechaDesde, $fechaHasta)->getData();
                break;
            case 'vendedores':
                $datos = $this->analisisRentabilidadVendedores($fechaDesde, $fechaHasta)->getData();
                break;
            default:
                $datos = $this->analisisRentabilidadGeneral($fechaDesde, $fechaHasta)->getData();
        }

        return response()->json([
            'mensaje' => 'Exportación de análisis iniciada',
            'tipo_analisis' => $tipo,
            'periodo' => [
                'desde' => $fechaDesde,
                'hasta' => $fechaHasta
            ],
            'datos' => $datos
        ]);
    }
}