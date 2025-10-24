<?php

namespace App\Http\Controllers\Farmacia;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class InventarioController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'rol:farmaceutico,administrador,contador']);
    }

    /**
     * Display a listing of resource.
     */
    public function index(Request $request)
    {
        $query = DB::table('Productos as p')
            ->leftJoin('Laboratorios as l', 'p.CodLab', '=', 'l.CodLab')
            ->leftJoin('Saldos as s', 'p.CodPro', '=', 's.CodPro')
            ->leftJoin('Doccab as dc', 's.CodDoc', '=', 'dc.CodDoc')
            ->select([
                'p.CodPro',
                'p.Nombre',
                'p.Presentacion',
                'p.CostoPromedio',
                'p.StockMinimo',
                'p.CadenaFria',
                'p.EsControlado',
                'p.TipoControl',
                'l.Descripcion as Laboratorio',
                's.Lote',
                's.Vencimiento',
                's.Cantidad',
                's.Costo',
                'dc.Tipo as TipoUltimoIngreso',
                'dc.Fecha as FechaUltimoIngreso',
                DB::raw('SUM(s.Cantidad) OVER (PARTITION BY s.CodPro) as StockTotal'),
                DB::raw('SUM(s.Cantidad * s.Costo) OVER (PARTITION BY s.CodPro) as ValorTotalStock'),
                DB::raw('DATEDIFF(day, GETDATE(), s.Vencimiento) as DiasVencimiento'),
                DB::raw('ROW_NUMBER() OVER (PARTITION BY s.CodPro ORDER BY s.Vencimiento) as rn')
            ])
            ->where('s.Cantidad', '>', 0);

        // Filtros
        if ($request->filled('codigo')) {
            $query->where('p.CodPro', 'like', '%' . $request->codigo . '%');
        }

        if ($request->filled('nombre')) {
            $query->where('p.Nombre', 'like', '%' . $request->nombre . '%');
        }

        if ($request->filled('laboratorio')) {
            $query->where('l.CodLab', $request->laboratorio);
        }

        if ($request->filled('categoria')) {
            switch ($request->categoria) {
                case 'cadena_fria':
                    $query->where('p.CadenaFria', true);
                    break;
                case 'controlados':
                    $query->where('p.EsControlado', true);
                    break;
                case 'stock_bajo':
                    $query->whereRaw('s.Cantidad <= p.StockMinimo');
                    break;
                case 'vencimiento_proximo':
                    $query->where('s.Vencimiento', '<=', Carbon::now()->addDays(30)->toDateString());
                    break;
                case 'vencido':
                    $query->where('s.Vencimiento', '<', Carbon::now()->toDateString());
                    break;
            }
        }

        if ($request->filled('valor_minimo')) {
            $query->where('s.Costo', '>=', $request->valor_minimo);
        }

        if ($request->filled('valor_maximo')) {
            $query->where('s.Costo', '<=', $request->valor_maximo);
        }

        if ($request->filled('sin_movimiento')) {
            $query->where('dc.Fecha', '<', Carbon::now()->subDays(60)->toDateString());
        }

        $inventario = $query->where('rn', 1) // Obtener solo el primer lote por producto
            ->orderBy('p.Nombre')
            ->paginate(25);

        // Estadísticas de inventario
        $estadisticas = $this->calcularEstadisticasInventario($request);

        // Análisis ABC
        $analisisABC = $this->realizarAnalisisABC($request);

        // Productos sin movimiento
        $productosSinMovimiento = $this->obtenerProductosSinMovimiento();

        return compact('inventario', 'estadisticas', 'analisisABC', 'productosSinMovimiento');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $laboratorios = DB::table('Laboratorios')
            ->orderBy('Descripcion')
            ->get();

        $categorias = [
            'medicamento' => 'Medicamento',
            'producto_nutricional' => 'Producto Nutricional',
            'dispositivo_medico' => 'Dispositivo Médico',
            'cosmético' => 'Cosmético',
            'suplemento' => 'Suplemento',
            'otro' => 'Otro'
        ];

        return compact('laboratorios', 'categorias');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'codpro' => 'required|string|max:20|unique:Productos,CodPro',
            'nombre' => 'required|string|max:200',
            'presentacion' => 'required|string|max:100',
            'codlab' => 'required|exists:Laboratorios,CodLab',
            'costo_promedio' => 'required|numeric|min:0',
            'precio_venta' => 'required|numeric|min:0',
            'stock_minimo' => 'required|numeric|min:0',
            'categoria' => 'required|in:medicamento,producto_nutricional,dispositivo_medico,cosmético,suplemento,otro',
            'concentracion' => 'nullable|string|max:100',
            'forma_farmaceutica' => 'nullable|string|max:100',
            'unidad_medida' => 'required|string|max:20',
        ]);

        try {
            DB::beginTransaction();

            // Crear producto
            DB::table('Productos')->insert([
                'CodPro' => $request->codpro,
                'Nombre' => $request->nombre,
                'Presentacion' => $request->presentacion,
                'CodLab' => $request->codlab,
                'CostoPromedio' => $request->costo_promedio,
                'PrecioVenta' => $request->precio_venta,
                'StockMinimo' => $request->stock_minimo,
                'Categoria' => $request->categoria,
                'Concentracion' => $request->concentracion,
                'FormaFarmaceutica' => $request->forma_farmaceutica,
                'UnidadMedida' => $request->unidad_medida,
                'EsActivo' => true,
                'FechaCreacion' => Carbon::now(),
                'UsuarioCreacion' => Auth::id()
            ]);

            // Registrar en trazabilidad
            $this->registrarTrazabilidadInventario($request->codpro, 'CREACION_PRODUCTO', 
                "Producto creado en inventario: {$request->nombre}", 0);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Producto registrado en inventario correctamente',
                'data' => []
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar producto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($codpro)
    {
        $producto = DB::table('Productos as p')
            ->leftJoin('Laboratorios as l', 'p.CodLab', '=', 'l.CodLab')
            ->leftJoin('users as uc', 'p.UsuarioCreacion', '=', 'uc.id')
            ->leftJoin('users as um', 'p.UsuarioModificacion', '=', 'um.id')
            ->select([
                'p.*',
                'l.Descripcion as NombreLab',
                'uc.name as UsuarioCreacionNombre',
                'um.name as UsuarioModificacionNombre'
            ])
            ->where('p.CodPro', $codpro)
            ->first();

        if (!$producto) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }

        // Stock por lotes
        $stockLotes = DB::table('Saldos as s')
            ->leftJoin('Doccab as dc', 's.CodDoc', '=', 'dc.CodDoc')
            ->where('s.CodPro', $codpro)
            ->select([
                's.*',
                'dc.Tipo',
                'dc.Serie',
                'dc.Numero',
                'dc.Fecha',
                DB::raw('DATEDIFF(day, GETDATE(), s.Vencimiento) as DiasVencimiento'),
                DB::raw('(s.Cantidad * s.Costo) as ValorTotal')
            ])
            ->orderBy('s.Vencimiento')
            ->get();

        // Resumen de stock
        $resumenStock = [
            'total_unidades' => $stockLotes->sum('Cantidad'),
            'valor_total' => $stockLotes->sum('ValorTotal'),
            'numero_lotes' => $stockLotes->count(),
            'vencimiento_promedio' => $stockLotes->avg('DiasVencimiento'),
            'lote_mas_antiguo' => $stockLotes->last(),
            'lote_mas_reciente' => $stockLotes->first()
        ];

        // Movimientos recientes
        $movimientosRecientes = $this->obtenerMovimientosRecientesInventario($codpro);

        // Análisis de rotación
        $analisisRotacion = $this->analizarRotacionProducto($codpro);

        // Productos relacionados (mismo laboratorio)
        $productosRelacionados = $this->obtenerProductosRelacionados($codpro, $producto->CodLab);

        return compact('producto', 'stockLotes', 'resumenStock', 'movimientosRecientes', 
            'analisisRotacion', 'productosRelacionados');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($codpro)
    {
        $producto = DB::table('Productos as p')
            ->leftJoin('Laboratorios as l', 'p.CodLab', '=', 'l.CodLab')
            ->where('p.CodPro', $codpro)
            ->select('p.*', 'l.Descripcion as NombreLab')
            ->first();

        if (!$producto) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }

        $laboratorios = DB::table('Laboratorios')
            ->orderBy('Descripcion')
            ->get();

        $categorias = [
            'medicamento' => 'Medicamento',
            'producto_nutricional' => 'Producto Nutricional',
            'dispositivo_medico' => 'Dispositivo Médico',
            'cosmético' => 'Cosmético',
            'suplemento' => 'Suplemento',
            'otro' => 'Otro'
        ];

        return compact('producto', 'laboratorios', 'categorias');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $codpro)
    {
        $request->validate([
            'nombre' => 'required|string|max:200',
            'presentacion' => 'required|string|max:100',
            'codlab' => 'required|exists:Laboratorios,CodLab',
            'costo_promedio' => 'required|numeric|min:0',
            'precio_venta' => 'required|numeric|min:0',
            'stock_minimo' => 'required|numeric|min:0',
            'categoria' => 'required|in:medicamento,producto_nutricional,dispositivo_medico,cosmético,suplemento,otro',
            'concentracion' => 'nullable|string|max:100',
            'forma_farmaceutica' => 'nullable|string|max:100',
            'unidad_medida' => 'required|string|max:20',
        ]);

        try {
            DB::beginTransaction();

            $producto = DB::table('Productos')
                ->where('CodPro', $codpro)
                ->first();

            if (!$producto) {
                return response()->json(['message' => 'Producto no encontrado'], 404);
            }

            // Actualizar producto
            DB::table('Productos')
                ->where('CodPro', $codpro)
                ->update([
                    'Nombre' => $request->nombre,
                    'Presentacion' => $request->presentacion,
                    'CodLab' => $request->codlab,
                    'CostoPromedio' => $request->costo_promedio,
                    'PrecioVenta' => $request->precio_venta,
                    'StockMinimo' => $request->stock_minimo,
                    'Categoria' => $request->categoria,
                    'Concentracion' => $request->concentracion,
                    'FormaFarmaceutica' => $request->forma_farmaceutica,
                    'UnidadMedida' => $request->unidad_medida,
                    'FechaModificacion' => Carbon::now(),
                    'UsuarioModificacion' => Auth::id()
                ]);

            // Registrar en trazabilidad
            $this->registrarTrazabilidadInventario($codpro, 'MODIFICACION_PRODUCTO', 
                "Producto modificado: {$request->nombre}", 0);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Producto actualizado correctamente',
                'data' => []
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar producto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($codpro)
    {
        try {
            DB::beginTransaction();

            $producto = DB::table('Productos')
                ->where('CodPro', $codpro)
                ->first();

            if (!$producto) {
                return response()->json(['message' => 'Producto no encontrado'], 404);
            }

            // Verificar si tiene stock
            $tieneStock = DB::table('Saldos')
                ->where('CodPro', $codpro)
                ->where('Cantidad', '>', 0)
                ->exists();

            if ($tieneStock) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar un producto que tiene stock'
                ], 400);
            }

            // Verificar si tiene movimientos
            $tieneMovimientos = DB::table('Doccab as dc')
                ->join('Docdet as dd', 'dc.CodDoc', '=', 'dd.CodDoc')
                ->where('dd.CodPro', $codpro)
                ->exists();

            if ($tieneMovimientos) {
                // Marcar como inactivo en lugar de eliminar
                DB::table('Productos')
                    ->where('CodPro', $codpro)
                    ->update([
                        'EsActivo' => false,
                        'FechaModificacion' => Carbon::now(),
                        'UsuarioModificacion' => Auth::id(),
                        'Observaciones' => 'Producto desactivado del inventario'
                    ]);
            } else {
                // Eliminar físicamente si no tiene movimientos
                DB::table('Productos')
                    ->where('CodPro', $codpro)
                    ->delete();
            }

            // Registrar en trazabilidad
            $this->registrarTrazabilidadInventario($codpro, 'ELIMINACION_PRODUCTO', 
                $tieneMovimientos ? 'Producto desactivado' : 'Producto eliminado', 0);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $tieneMovimientos ? 'Producto desactivado correctamente' : 'Producto eliminado correctamente',
                'data' => []
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar producto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ajustar stock de producto
     */
    public function ajustarStock(Request $request, $codpro)
    {
        $request->validate([
            'lote' => 'required|string|max:50',
            'cantidad_ajuste' => 'required|numeric',
            'tipo_ajuste' => 'required|in:inventario,vencido,deteriorado,ajuste_error,diferencia_contador',
            'observaciones' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            $lote = DB::table('Saldos')
                ->where('CodPro', $codpro)
                ->where('Lote', $request->lote)
                ->first();

            if (!$lote) {
                return response()->json(['message' => 'Lote no encontrado'], 404);
            }

            $cantidadAnterior = $lote->Cantidad;
            $nuevaCantidad = $cantidadAnterior + $request->cantidad_ajuste;

            if ($nuevaCantidad < 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'El ajuste resultaría en cantidad negativa'
                ], 400);
            }

            // Actualizar cantidad
            DB::table('Saldos')
                ->where('CodPro', $codpro)
                ->where('Lote', $request->lote)
                ->update([
                    'Cantidad' => $nuevaCantidad,
                    'FechaModificacion' => Carbon::now(),
                    'UsuarioModificacion' => Auth::id(),
                    'Observaciones' => "Ajuste {$request->tipo_ajuste}: {$request->observaciones}"
                ]);

            // Registrar ajuste
            $this->registrarAjusteStock($codpro, $request->lote, $cantidadAnterior, $nuevaCantidad, $request->tipo_ajuste, $request->observaciones);

            // Generar asiento contable si es necesario
            if ($request->tipo_ajuste != 'inventario') {
                $this->generarAsientoAjuste($codpro, $request->cantidad_ajuste, $lote->Costo, $request->tipo_ajuste);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Stock ajustado correctamente',
                'data' => [
                    'cantidad_anterior' => $cantidadAnterior,
                    'cantidad_nueva' => $nuevaCantidad,
                    'diferencia' => $request->cantidad_ajuste
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al ajustar stock: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Inventario físico - reporte
     */
    public function inventarioFisico(Request $request)
    {
        $request->validate([
            'fecha_inventario' => 'required|date',
        ]);

        // Productos activos
        $productos = DB::table('Productos as p')
            ->leftJoin('Laboratorios as l', 'p.CodLab', '=', 'l.CodLab')
            ->where('p.EsActivo', true)
            ->select([
                'p.CodPro',
                'p.Nombre',
                'p.Presentacion',
                'p.CostoPromedio',
                'p.PrecioVenta',
                'p.StockMinimo',
                'l.Descripcion as Laboratorio'
            ])
            ->orderBy('p.Nombre')
            ->get();

        // Stock actual por producto
        $stockActual = [];
        foreach ($productos as $producto) {
            $stock = DB::table('Saldos')
                ->where('CodPro', $producto->CodPro)
                ->where('Cantidad', '>', 0)
                ->select([
                    'Lote',
                    'Cantidad',
                    'Vencimiento',
                    'Costo',
                    DB::raw('(Cantidad * Costo) as ValorTotal')
                ])
                ->get();

            $stockActual[] = [
                'producto' => $producto,
                'stock' => $stock,
                'total_unidades' => $stock->sum('Cantidad'),
                'valor_total' => $stock->sum('ValorTotal')
            ];
        }

        // Resumen general
        $resumen = [
            'fecha_inventario' => $request->fecha_inventario,
            'total_productos' => count($productos),
            'productos_con_stock' => collect($stockActual)->where('total_unidades', '>', 0)->count(),
            'total_unidades' => collect($stockActual)->sum('total_unidades'),
            'valor_total_inventario' => collect($stockActual)->sum('valor_total')
        ];

        return compact('stockActual', 'resumen');
    }

    /**
     * Dashboard de inventario
     */
    public function dashboard()
    {
        // Estadísticas generales
        $totalProductos = DB::table('Productos')->where('EsActivo', true)->count();
        $productosConStock = DB::table('Saldos')
            ->where('Cantidad', '>', 0)
            ->distinct('CodPro')
            ->count('CodPro');

        // Valor total del inventario
        $valorInventario = DB::table('Saldos as s')
            ->where('s.Cantidad', '>', 0)
            ->sum(DB::raw('s.Cantidad * s.Costo'));

        // Productos con stock bajo
        $stockBajo = DB::table('Productos as p')
            ->join('Saldos as s', 'p.CodPro', '=', 's.CodPro')
            ->whereRaw('s.Cantidad <= p.StockMinimo')
            ->where('s.Cantidad', '>', 0)
            ->select([
                'p.CodPro',
                'p.Nombre',
                's.Cantidad',
                'p.StockMinimo'
            ])
            ->distinct()
            ->count();

        // Productos próximos a vencer
        $proximosVencer = DB::table('Saldos as s')
            ->join('Productos as p', 's.CodPro', '=', 'p.CodPro')
            ->where('s.Vencimiento', '<=', Carbon::now()->addDays(30)->toDateString())
            ->where('s.Cantidad', '>', 0)
            ->select([
                'p.CodPro',
                'p.Nombre',
                's.Lote',
                's.Cantidad',
                's.Vencimiento'
            ])
            ->get();

        // Top productos por valor
        $topValor = DB::table('Saldos as s')
            ->join('Productos as p', 's.CodPro', '=', 'p.CodPro')
            ->where('s.Cantidad', '>', 0)
            ->select([
                'p.CodPro',
                'p.Nombre',
                DB::raw('SUM(s.Cantidad * s.Costo) as ValorTotal')
            ])
            ->groupBy('p.CodPro', 'p.Nombre')
            ->orderByDesc('ValorTotal')
            ->limit(10)
            ->get();

        // Rotación de productos (últimos 30 días)
        $rotacionProductos = $this->calcularRotacionInventario();

        return compact('totalProductos', 'productosConStock', 'valorInventario', 
            'stockBajo', 'proximosVencer', 'topValor', 'rotacionProductos');
    }

    /**
     * Reporte de inventario por laboratorio
     */
    public function reportePorLaboratorio(Request $request)
    {
        $request->validate([
            'codlab' => 'required|exists:Laboratorios,CodLab',
        ]);

        $laboratorio = DB::table('Laboratorios')
            ->where('CodLab', $request->codlab)
            ->first();

        $productosLab = DB::table('Productos as p')
            ->leftJoin('Saldos as s', 'p.CodPro', '=', 's.CodPro')
            ->where('p.CodLab', $request->codlab)
            ->where('p.EsActivo', true)
            ->select([
                'p.CodPro',
                'p.Nombre',
                'p.Presentacion',
                'p.CostoPromedio',
                'p.PrecioVenta',
                DB::raw('SUM(COALESCE(s.Cantidad, 0)) as StockTotal'),
                DB::raw('SUM(COALESCE(s.Cantidad * s.Costo, 0)) as ValorTotal'),
                DB::raw('COUNT(s.CodPro) as NumeroLotes')
            ])
            ->groupBy('p.CodPro', 'p.Nombre', 'p.Presentacion', 'p.CostoPromedio', 'p.PrecioVenta')
            ->orderBy('p.Nombre')
            ->get();

        $resumenLab = [
            'laboratorio' => $laboratorio,
            'total_productos' => $productosLab->count(),
            'productos_con_stock' => $productosLab->where('StockTotal', '>', 0)->count(),
            'valor_total_inventario' => $productosLab->sum('ValorTotal'),
            'total_unidades' => $productosLab->sum('StockTotal')
        ];

        return compact('productosLab', 'resumenLab');
    }

    /**
     * Calcular estadísticas de inventario
     */
    private function calcularEstadisticasInventario($request)
    {
        $query = DB::table('Productos as p')
            ->leftJoin('Saldos as s', 'p.CodPro', '=', 's.CodPro')
            ->where('p.EsActivo', true);

        // Aplicar filtros similares al índice
        if ($request->filled('categoria')) {
            switch ($request->categoria) {
                case 'cadena_fria':
                    $query->where('p.CadenaFria', true);
                    break;
                case 'controlados':
                    $query->where('p.EsControlado', true);
                    break;
            }
        }

        $estadisticas = [
            'total_productos' => $query->count(),
            'productos_con_stock' => $query->where('s.Cantidad', '>', 0)->count(),
            'valor_total' => $query->where('s.Cantidad', '>', 0)->sum(DB::raw('s.Cantidad * s.Costo')),
            'unidades_totales' => $query->where('s.Cantidad', '>', 0)->sum('s.Cantidad'),
            'promedio_costo' => $query->where('s.Cantidad', '>', 0)->avg('s.Costo')
        ];

        return $estadisticas;
    }

    /**
     * Realizar análisis ABC
     */
    private function realizarAnalisisABC($request)
    {
        $productos = DB::table('Saldos as s')
            ->join('Productos as p', 's.CodPro', '=', 'p.CodPro')
            ->where('s.Cantidad', '>', 0)
            ->select([
                'p.CodPro',
                'p.Nombre',
                DB::raw('SUM(s.Cantidad * s.Costo) as ValorTotal')
            ])
            ->groupBy('p.CodPro', 'p.Nombre')
            ->orderByDesc('ValorTotal')
            ->get();

        $valorTotal = $productos->sum('ValorTotal');
        $acumulado = 0;

        foreach ($productos as $index => $producto) {
            $acumulado += $producto->ValorTotal;
            $porcentaje = ($acumulado / $valorTotal) * 100;
            
            if ($porcentaje <= 80) {
                $producto->CategoriaABC = 'A';
            } elseif ($porcentaje <= 95) {
                $producto->CategoriaABC = 'B';
            } else {
                $producto->CategoriaABC = 'C';
            }
        }

        return [
            'productos_a' => $productos->where('CategoriaABC', 'A'),
            'productos_b' => $productos->where('CategoriaABC', 'B'),
            'productos_c' => $productos->where('CategoriaABC', 'C'),
            'resumen' => [
                'categoria_a' => $productos->where('CategoriaABC', 'A')->count(),
                'categoria_b' => $productos->where('CategoriaABC', 'B')->count(),
                'categoria_c' => $productos->where('CategoriaABC', 'C')->count()
            ]
        ];
    }

    /**
     * Obtener productos sin movimiento
     */
    private function obtenerProductosSinMovimiento()
    {
        return DB::table('Productos as p')
            ->leftJoin('Saldos as s', 'p.CodPro', '=', 's.CodPro')
            ->leftJoin('Doccab as dc', 's.CodDoc', '=', 'dc.CodDoc')
            ->where('p.EsActivo', true)
            ->where('dc.Fecha', '<', Carbon::now()->subDays(60)->toDateString())
            ->orWhereNull('dc.Fecha')
            ->select([
                'p.CodPro',
                'p.Nombre',
                'p.Presentacion',
                DB::raw('MAX(dc.Fecha) as UltimoMovimiento'),
                DB::raw('SUM(COALESCE(s.Cantidad, 0)) as StockActual')
            ])
            ->groupBy('p.CodPro', 'p.Nombre', 'p.Presentacion')
            ->orderBy('UltimoMovimiento')
            ->limit(20)
            ->get();
    }

    /**
     * Obtener movimientos recientes de inventario
     */
    private function obtenerMovimientosRecientesInventario($codpro)
    {
        // Entradas
        $entradas = DB::table('Doccab as dc')
            ->join('Docdet as dd', 'dc.CodDoc', '=', 'dd.CodDoc')
            ->where('dd.CodPro', $codpro)
            ->whereIn('dc.Tipo', ['COM', 'NOT'])
            ->select([
                'dc.Tipo',
                'dc.Serie',
                'dc.Numero',
                'dc.Fecha',
                'dd.Cantidad',
                'dd.Costo',
                'dd.Subtotal',
                'dd.Lote'
            ])
            ->orderBy('dc.Fecha', 'desc')
            ->limit(10)
            ->get();

        // Salidas
        $salidas = DB::table('Doccab as dc')
            ->join('Docdet as dd', 'dc.CodDoc', '=', 'dd.CodDoc')
            ->where('dd.CodPro', $codpro)
            ->whereIn('dc.Tipo', ['FAC', 'BOL'])
            ->select([
                'dc.Tipo',
                'dc.Serie',
                'dc.Numero',
                'dc.Fecha',
                'dd.Cantidad',
                'dd.Costo',
                'dd.Subtotal',
                'dd.Lote'
            ])
            ->orderBy('dc.Fecha', 'desc')
            ->limit(10)
            ->get();

        return [
            'entradas' => $entradas,
            'salidas' => $salidas
        ];
    }

    /**
     * Analizar rotación de producto
     */
    private function analizarRotacionProducto($codpro)
    {
        $fechaLimite = Carbon::now()->subDays(90);

        // Ventas en los últimos 90 días
        $ventas = DB::table('Doccab as dc')
            ->join('Docdet as dd', 'dc.CodDoc', '=', 'dd.CodDoc')
            ->where('dd.CodPro', $codpro)
            ->where('dc.Fecha', '>=', $fechaLimite->toDateString())
            ->whereIn('dc.Tipo', ['FAC', 'BOL'])
            ->sum('dd.Cantidad');

        // Stock actual
        $stockActual = DB::table('Saldos')
            ->where('CodPro', $codpro)
            ->where('Cantidad', '>', 0)
            ->sum('Cantidad');

        // Calcular rotación
        $rotacion = $stockActual > 0 ? $ventas / $stockActual : 0;
        $diasInventario = $ventas > 0 ? ($stockActual * 90) / $ventas : 0;

        return [
            'ventas_90_dias' => $ventas,
            'stock_actual' => $stockActual,
            'rotacion' => round($rotacion, 2),
            'dias_inventario' => round($diasInventario, 1),
            'clasificacion_rotacion' => $rotacion > 4 ? 'alta' : ($rotacion > 2 ? 'media' : 'baja')
        ];
    }

    /**
     * Obtener productos relacionados
     */
    private function obtenerProductosRelacionados($codpro, $codlab)
    {
        return DB::table('Productos')
            ->where('CodLab', $codlab)
            ->where('CodPro', '!=', $codpro)
            ->where('EsActivo', true)
            ->select('CodPro', 'Nombre', 'Presentacion')
            ->orderBy('Nombre')
            ->limit(10)
            ->get();
    }

    /**
     * Registrar ajuste de stock
     */
    private function registrarAjusteStock($codpro, $lote, $cantidadAnterior, $nuevaCantidad, $tipoAjuste, $observaciones)
    {
        DB::table('AjustesStock')->insert([
            'CodPro' => $codpro,
            'Lote' => $lote,
            'CantidadAnterior' => $cantidadAnterior,
            'CantidadNueva' => $nuevaCantidad,
            'Diferencia' => $nuevaCantidad - $cantidadAnterior,
            'TipoAjuste' => $tipoAjuste,
            'Observaciones' => $observaciones,
            'Fecha' => Carbon::now(),
            'Usuario' => Auth::id()
        ]);

        // Registrar en trazabilidad
        $this->registrarTrazabilidadInventario($codpro, 'AJUSTE_STOCK', 
            "Ajuste de stock: {$tipoAjuste} - {$cantidadAnterior} -> {$nuevaCantidad}", 
            $nuevaCantidad - $cantidadAnterior);
    }

    /**
     * Generar asiento contable por ajuste
     */
    private function generarAsientoAjuste($codpro, $diferencia, $costoUnitario, $tipoAjuste)
    {
        $valorAjuste = abs($diferencia) * $costoUnitario;
        $glosa = "Ajuste de inventario - {$tipoAjuste}";
        
        if ($diferencia > 0) {
            // Ajuste positivo: Debito inventario, Credito diferencia
            DB::table('asientos_diario')->insert([
                'Codigo' => $this->generarCodigoAsiento(),
                'Fecha' => Carbon::now(),
                'CuentaContable' => '2011', // Inventario
                'Glosa' => $glosa,
                'Debe' => $valorAjuste,
                'Haber' => 0,
                'Referencia' => $codpro,
                'TipoAsiento' => 'AJUSTE_STOCK',
                'FechaCreacion' => Carbon::now(),
                'UsuarioCreacion' => Auth::id()
            ]);
        } else {
            // Ajuste negativo: Debito diferencia, Credito inventario
            DB::table('asientos_diario')->insert([
                'Codigo' => $this->generarCodigoAsiento(),
                'Fecha' => Carbon::now(),
                'CuentaContable' => '2011', // Inventario
                'Glosa' => $glosa,
                'Debe' => 0,
                'Haber' => $valorAjuste,
                'Referencia' => $codpro,
                'TipoAsiento' => 'AJUSTE_STOCK',
                'FechaCreacion' => Carbon::now(),
                'UsuarioCreacion' => Auth::id()
            ]);
        }
    }

    /**
     * Calcular rotación de inventario
     */
    private function calcularRotacionInventario()
    {
        // Calcular rotación para todos los productos activos
        $productos = DB::table('Productos')
            ->where('EsActivo', true)
            ->select('CodPro', 'Nombre')
            ->get();

        $rotacion = [];
        foreach ($productos as $producto) {
            $analisis = $this->analizarRotacionProducto($producto->CodPro);
            if ($analisis['ventas_90_dias'] > 0) {
                $rotacion[] = [
                    'codpro' => $producto->CodPro,
                    'nombre' => $producto->Nombre,
                    'rotacion' => $analisis['rotacion'],
                    'clasificacion' => $analisis['clasificacion_rotacion']
                ];
            }
        }

        return collect($rotacion)->sortByDesc('rotacion')->take(20)->values();
    }

    /**
     * Generar código de asiento
     */
    private function generarCodigoAsiento()
    {
        $ultimo = DB::table('asientos_diario')
            ->max('Codigo');

        if ($ultimo) {
            $numero = (int) substr($ultimo, 3) + 1;
        } else {
            $numero = 1;
        }

        return 'ASI' . str_pad($numero, 8, '0', STR_PAD_LEFT);
    }

    /**
     * Registrar trazabilidad de inventario
     */
    private function registrarTrazabilidadInventario($codpro, $accion, $descripcion, $cantidad)
    {
        DB::table('TrazabilidadInventario')->insert([
            'CodPro' => $codpro,
            'Accion' => $accion,
            'Descripcion' => $descripcion,
            'Cantidad' => $cantidad,
            'Fecha' => Carbon::now(),
            'Usuario' => Auth::id()
        ]);
    }
}