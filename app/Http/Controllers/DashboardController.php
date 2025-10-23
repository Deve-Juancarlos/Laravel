<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            Auth::logout();
            return redirect()->route('login')->with('error', 'No se encontró información de acceso');
        }

        $accesoWeb = DB::table('accesoweb')
            ->where('usuario', $user->name)
            ->first();

        if (!$accesoWeb) {
            Auth::logout();
            return redirect()->route('login')->with('error', 'No se encontró información de acceso');
        }

        $tipoUsuario = strtolower(trim($accesoWeb->tipousuario));

        switch ($tipoUsuario) {
            case 'administrador':
                return $this->administrador();
            case 'vendedor':
                return $this->vendedor();
            case 'contador':
                return $this->contador();
            default:
                Log::warning('Tipo de usuario no válido detectado', [
                    'usuario' => $user->name,
                    'tipo' => $accesoWeb->tipousuario
                ]);
                Auth::logout();
                return redirect()->route('login')
                    ->with('error', 'Tipo de usuario no válido. Contacte al administrador.');
        }
    }

    public function administrador()
    {
        try {
            $stats = $this->getAdminStats();
            $chartData = $this->getAdminChartData();
            $recentData = $this->getAdminRecentData();
            
            $data = array_merge($stats, $chartData, $recentData);

            return view('admin', compact('data'));
            
        } catch (\Exception $e) {
            Log::error('Error en dashboard administrador', [
                'error' => $e->getMessage(),
                'usuario' => Auth::id(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            
            $data = $this->getDefaultAdminData();
            return view('admin', compact('data'))->with('warning', 'Algunos datos no pudieron cargarse correctamente');
        }
    }

    private function getAdminStats()
    {
        $fechaActual = Carbon::now();
        $inicioMes = $fechaActual->copy()->startOfMonth()->format('Y-m-d');
        $finMes = $fechaActual->copy()->endOfMonth()->format('Y-m-d');

        $totalProductos = DB::table('Productos')
            ->where('Eliminado', 0)
            ->count();

        $ventasMes = DB::table('t_FacturasXCobrar')
            ->whereBetween('Fecha', [$inicioMes, $finMes])
            ->sum('Importe') ?? 0;

        $inicioMesAnterior = $fechaActual->copy()->subMonth()->startOfMonth()->format('Y-m-d');
        $finMesAnterior = $fechaActual->copy()->subMonth()->endOfMonth()->format('Y-m-d');
        
        $ventasMesAnterior = DB::table('t_FacturasXCobrar')
            ->whereBetween('Fecha', [$inicioMesAnterior, $finMesAnterior])
            ->sum('Importe') ?? 0;

        $stockBajo = DB::table('Productos')
            ->where('Eliminado', 0)
            ->whereRaw('Stock <= Minimo')
            ->whereNotNull('Minimo')
            ->where('Minimo', '>', 0)
            ->count();

        $stockCritico = DB::table('Productos')
            ->where('Eliminado', 0)
            ->whereRaw('Stock <= (Minimo * 0.5)')
            ->whereNotNull('Minimo')
            ->where('Minimo', '>', 0)
            ->count();

        $recetasProcesadas = DB::table('t_FacturasXCobrar')
            ->whereBetween('Fecha', [$inicioMes, $finMes])
            ->count();

        $tendenciaVentas = $ventasMesAnterior > 0 ? 
            (($ventasMes - $ventasMesAnterior) / $ventasMesAnterior) * 100 : 0;

        return [
            'totalProductos' => $totalProductos,
            'ventasMes' => round($ventasMes, 2),
            'stockBajo' => $stockBajo,
            'stockCritico' => $stockCritico,
            'recetasProcesadas' => $recetasProcesadas,
            'tendencias' => [
                'ventas' => round($tendenciaVentas, 1),
                'productos' => $this->calculateProductTrend(),
                'stock' => -$stockBajo,
                'recetas' => $this->calculatePrescriptionTrend()
            ]
        ];
    }

   private function getAdminChartData()
    {
        $anioActual = Carbon::now()->year;

        // Inicializamos un array de 12 meses con valor 0
        $ventasPorMes = array_fill(1, 12, 0);

        // Traemos ventas por mes desde la tabla Doccab
        $ventasBD = DB::table('Doccab')
            ->selectRaw('MONTH(Fecha) as Mes, SUM(Total) as TotalMes')
            ->where('Eliminado', 0)
            ->whereYear('Fecha', $anioActual)
            ->groupByRaw('MONTH(Fecha)')
            ->pluck('TotalMes', 'Mes');

        // Llenamos el array con los totales reales
        foreach ($ventasBD as $mes => $total) {
            $ventasPorMes[$mes] = round($total, 2);
        }

        // Preparamos los datos del gráfico
        $graficosPorMes = [];
        for ($i = 1; $i <= 12; $i++) {
            $nombreMes = Carbon::create()->month($i)->locale('es')->monthName;
            $graficosPorMes[] = [
                'mes' => ucfirst($nombreMes),    // Nombre del mes en español
                'data' => [$ventasPorMes[$i]]    // Un solo valor por mes
            ];
        }

        // Datos de top 5 productos (opcional)
        $topProductos = DB::table('Productos')
            ->select('Nombre', 'Stock')
            ->where('Eliminado', 0)
            ->where('Stock', '>', 0)
            ->orderBy('Stock', 'desc')
            ->take(5)
            ->get();

        $productosLabels = [];
        $productosData = [];

        foreach ($topProductos as $producto) {
            $productosLabels[] = substr($producto->Nombre, 0, 20) . '...';
            $productosData[] = $producto->Stock;
        }

        // Rellenar hasta 5 productos si hay menos
        while (count($productosLabels) < 5) {
            $productosLabels[] = 'Producto ' . (count($productosLabels) + 1);
            $productosData[] = 0;
        }

        return [
            'graficosPorMes' => $graficosPorMes,
            'topProductos' => [
                'labels' => $productosLabels,
                'data' => $productosData,
                'colors' => ['#2563eb', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6']
            ]
        ];
    }


    private function getAdminRecentData()
    {
        $alertasInventario = DB::table('InventarioB')
            ->select('nombre', 'stock', 'conteo1', 'conteo2', 'codpro')
            ->whereNotNull('stock')
            ->orderBy('stock', 'asc')
            ->take(10)
            ->get()
            ->map(function ($item) {
                $minimo = $item->conteo1 ?? 0;
                $porcentaje = ($minimo > 0) ? ($item->stock / $minimo) : 0;

                if ($item->stock == 0) {
                    $estado = 'Sin Stock';
                    $clase = 'danger';
                } elseif ($porcentaje <= 0.5) {
                    $estado = 'Crítico';
                    $clase = 'danger';
                } else {
                    $estado = 'Bajo';
                    $clase = 'warning';
                }

                return [
                    'codigo' => $item->codpro,
                    'nombre' => $item->nombre,
                    'stock'  => $item->stock,
                    'minimo' => $minimo,
                    'estado' => $estado,
                    'clase'  => $clase
                ];
            });

        $ultimasVentas = DB::table('t_FacturasXCobrar')
            ->select('Documento', 'Razon', 'Importe', 'Fecha', 'Saldo')
            ->whereNotNull('Fecha')
            ->orderBy('Fecha', 'desc')
            ->take(10)
            ->get()
            ->map(function ($venta) {
                $estado = 'Pendiente';
                $clase = 'warning';

                if ($venta->Saldo == 0) {
                    $estado = 'Pagado';
                    $clase = 'success';
                } elseif ($venta->Saldo < $venta->Importe) {
                    $estado = 'Parcial';
                    $clase = 'info';
                }

                return [
                    'documento' => $venta->Documento,
                    'cliente'   => $venta->Razon,
                    'importe'   => $venta->Importe,
                    'fecha'     => Carbon::parse($venta->Fecha)->format('d/m/Y'),
                    'estado'    => $estado,
                    'clase'     => $clase
                ];
            });

        $productosNoFacturados = DB::table('NoFacturado')
            ->select('codpro', 'nombre', 'Pedido', 'stock')
            ->where('Pedido', '>', 0)
            ->orderBy('nombre')
            ->take(10)
            ->get();

        $topClientes = DB::table('t_FacturasXCobrar')
            ->select('Razon', 'Ruc')
            ->selectRaw('COUNT(*) as num_facturas, SUM(Importe) as total_comprado')
            ->where('Fecha', '>=', Carbon::now()->subMonth()->format('Y-m-d'))
            ->whereNotNull('Razon')
            ->groupBy('Razon', 'Ruc')
            ->orderBy('total_comprado', 'desc')
            ->take(5)
            ->get();

        return [
            'alertasInventario'    => $alertasInventario,
            'ultimasVentas'        => $ultimasVentas,
            'productosNoFacturados'=> $productosNoFacturados,
            'topClientes'          => $topClientes
        ];
    }

    private function calculateProductTrend()
    {
        $productosActuales = DB::table('Productos')
            ->where('Eliminado', 0)
            ->count();
        
        $productosAnterior = max($productosActuales - 10, 0); 
        
        return $productosAnterior > 0 ? 
            round((($productosActuales - $productosAnterior) / $productosAnterior) * 100, 1) : 3.2;
    }

    private function calculatePrescriptionTrend()
    {
        $fechaActual = Carbon::now();
        $inicioMes = $fechaActual->copy()->startOfMonth()->format('Y-m-d');
        $inicioMesAnterior = $fechaActual->copy()->subMonth()->startOfMonth()->format('Y-m-d');
        $finMesAnterior = $fechaActual->copy()->subMonth()->endOfMonth()->format('Y-m-d');

        $recetasActuales = DB::table('t_FacturasXCobrar')
            ->whereBetween('Fecha', [$inicioMes, $fechaActual->format('Y-m-d')])
            ->count();

        $recetasAnterior = DB::table('t_FacturasXCobrar')
            ->whereBetween('Fecha', [$inicioMesAnterior, $finMesAnterior])
            ->count();

        return $recetasAnterior > 0 ? 
            round((($recetasActuales - $recetasAnterior) / $recetasAnterior) * 100, 1) : 8.1;
    }

    private function getDefaultAdminData()
    {
        return [
            'totalProductos' => 0,
            'ventasMes' => 0,
            'stockBajo' => 0,
            'recetasProcesadas' => 0,
            'tendencias' => [
                'ventas' => 0,
                'productos' => 0,
                'stock' => 0,
                'recetas' => 0
            ],
            'graficosPorMes' => [],
            'topProductos' => [
                'labels' => ['Sin datos', 'Sin datos', 'Sin datos', 'Sin datos', 'Sin datos'],
                'data' => [0, 0, 0, 0, 0],
                'colors' => ['#2563eb', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6']
            ],
            'alertasInventario' => [],
            'ultimasVentas' => [],
            'productosNoFacturados' => [],
            'topClientes' => []
        ];
    }

    public function getStats(Request $request)
    {
        try {
            $user = Auth::user();
            
            $accesoWeb = DB::table('accesoweb')
                ->where('usuario', $user->name)
                ->first();
                
            if (!$accesoWeb) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no autorizado'
                ], 403);
            }

            $tipoUsuario = strtolower(trim($accesoWeb->tipousuario));

            if ($tipoUsuario === 'administrador') {
                $stats = $this->getAdminStats();
                
                return response()->json([
                    'success' => true,
                    'data' => [
                        'totalProductos' => $stats['totalProductos'],
                        'ventasMes' => $stats['ventasMes'],
                        'stockBajo' => $stats['stockBajo'],
                        'stockCritico' => $stats['stockCritico'],
                        'recetasProcesadas' => $stats['recetasProcesadas'],
                        'tendencias' => $stats['tendencias'],
                        'timestamp' => Carbon::now()->format('d/m/Y H:i:s')
                    ]
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Acceso no autorizado para este tipo de usuario'
            ], 403);

        } catch (\Exception $e) {
            Log::error('Error al obtener estadísticas', [
                'error' => $e->getMessage(),
                'usuario' => Auth::id(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    public function getAlerts(Request $request)
    {
        try {
            $alertas = [];

            $stockCritico = DB::table('Productos')
                ->where('Eliminado', 0)
                ->whereRaw('Stock <= (Minimo * 0.5)')
                ->whereNotNull('Minimo')
                ->where('Minimo', '>', 0)
                ->count();

            if ($stockCritico > 0) {
                $alertas[] = [
                    'tipo' => 'danger',
                    'icono' => 'fas fa-exclamation-triangle',
                    'titulo' => 'Stock Crítico',
                    'descripcion' => "{$stockCritico} productos con stock crítico",
                    'tiempo' => 'Ahora',
                    'url' => '#inventory'
                ];
            }

            $stockBajo = DB::table('Productos')
                ->where('Eliminado', 0)
                ->whereRaw('Stock <= Minimo AND Stock > (Minimo * 0.5)')
                ->whereNotNull('Minimo')
                ->where('Minimo', '>', 0)
                ->count();

            if ($stockBajo > 0) {
                $alertas[] = [
                    'tipo' => 'warning',
                    'icono' => 'fas fa-exclamation-circle',
                    'titulo' => 'Stock Bajo',
                    'descripcion' => "{$stockBajo} productos con stock bajo",
                    'tiempo' => 'Hoy',
                    'url' => '#inventory'
                ];
            }

            $noFacturados = DB::table('NoFacturado')
                ->where('Pedido', '>', 0)
                ->count();

            if ($noFacturados > 0) {
                $alertas[] = [
                    'tipo' => 'info',
                    'icono' => 'fas fa-file-invoice',
                    'titulo' => 'Productos Pendientes',
                    'descripcion' => "{$noFacturados} productos pendientes de facturar",
                    'tiempo' => 'Hoy',
                    'url' => '#products'
                ];
            }

            return response()->json([
                'success' => true,
                'alertas' => $alertas,
                'count' => count($alertas),
                'timestamp' => Carbon::now()->format('d/m/Y H:i:s')
            ]);

        } catch (\Exception $e) {
            Log::error('Error al cargar alertas', [
                'error' => $e->getMessage(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al cargar alertas'
            ], 500);
        }
    }


    

    public function vendedor()
    {
        $data = [
            'ventasMes' => 12450,
            'pedidosCompletados' => 38,
            'clientesActivos' => 24,
            'comisionesGanadas' => 1870,
        ];

        return view('vendedor', compact('data'));
    }

    public function contador()
    {

       return view('layouts.contador');
    }
}
