<?php

namespace App\Http\Controllers\Farmacia;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ControlMermasController extends Controller
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
        $query = DB::table('ControlMermas as cm')
            ->join('Productos as p', 'cm.CodPro', '=', 'p.CodPro')
            ->leftJoin('Laboratorios as l', 'p.CodLab', '=', 'l.CodLab')
            ->leftJoin('Saldos as s', function($join) {
                $join->on('cm.CodPro', '=', 's.CodPro')
                     ->on('cm.Lote', '=', 's.Lote');
            })
            ->select([
                'cm.*',
                'p.Nombre as Producto',
                'p.Presentacion',
                'p.CostoPromedio',
                'l.Descripcion as Laboratorio',
                's.Vencimiento',
                DB::raw('(cm.CantidadPerdida * cm.CostoUnitario) as ValorPerdida'),
                DB::raw('DATEDIFF(day, cm.FechaVencimiento, cm.FechaRegistro) as DiasVencimiento')
            ]);

        // Filtros
        if ($request->filled('fecha_desde')) {
            $query->where('cm.FechaRegistro', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->where('cm.FechaRegistro', '<=', $request->fecha_hasta . ' 23:59:59');
        }

        if ($request->filled('motivo')) {
            $query->where('cm.Motivo', $request->motivo);
        }

        if ($request->filled('codigo')) {
            $query->where('p.CodPro', 'like', '%' . $request->codigo . '%');
        }

        if ($request->filled('laboratorio')) {
            $query->where('l.CodLab', $request->laboratorio);
        }

        if ($request->filled('producto')) {
            $query->where('p.Nombre', 'like', '%' . $request->producto . '%');
        }

        $mermas = $query->orderBy('cm.FechaRegistro', 'desc')
            ->paginate(25);

        // Estadísticas de mermas
        $estadisticas = $this->calcularEstadisticasMermas($request);

        // Análisis por motivo
        $analisisMotivos = $this->analizarMermasPorMotivo($request);

        // Top productos con más mermas
        $topProductosMermas = $this->obtenerTopProductosMermas($request);

        return compact('mermas', 'estadisticas', 'analisisMotivos', 'topProductosMermas');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $productos = DB::table('Productos as p')
            ->leftJoin('Laboratorios as l', 'p.CodLab', '=', 'l.CodLab')
            ->leftJoin('Saldos as s', 'p.CodPro', '=', 's.CodPro')
            ->select('p.CodPro', 'p.Nombre', 'p.Presentacion', 'l.Descripcion as Laboratorio')
            ->where('s.Cantidad', '>', 0)
            ->distinct()
            ->orderBy('p.Nombre')
            ->get();

        $motivos = [
            'vencido' => 'Producto Vencido',
            'deteriorado' => 'Producto Deteriorado',
            'robado' => 'Robo o Pérdida',
            'devolucion_cliente' => 'Devolución de Cliente',
            'error_entrega' => 'Error en Entrega',
            'cadena_fria' => 'Falla en Cadena de Frío',
            'accidente' => 'Accidente o Daño',
            'retiro_regulatorio' => 'Retiro Regulatorio',
            'otros' => 'Otros'
        ];

        $laboratorios = DB::table('Laboratorios')
            ->orderBy('Descripcion')
            ->get();

        return compact('productos', 'motivos', 'laboratorios');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'codpro' => 'required|exists:Productos,CodPro',
            'lote' => 'required|string|max:50',
            'cantidad_perdida' => 'required|numeric|min:0.01',
            'motivo' => 'required|in:vencido,deteriorado,robado,devolucion_cliente,error_entrega,cadena_fria,accidente,retiro_regulatorio,otros',
            'fecha_vencimiento' => 'required|date',
            'observaciones' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            // Verificar que existe el lote
            $lote = DB::table('Saldos')
                ->where('CodPro', $request->codpro)
                ->where('Lote', $request->lote)
                ->first();

            if (!$lote) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró el lote especificado'
                ], 400);
            }

            // Verificar que la cantidad perdida no sea mayor al stock disponible
            if ($request->cantidad_perdida > $lote->Cantidad) {
                return response()->json([
                    'success' => false,
                    'message' => 'La cantidad perdida no puede ser mayor al stock disponible'
                ], 400);
            }

            // Obtener costo unitario
            $costoUnitario = $lote->Costo;
            
            // Registrar merma
            $codMerma = $this->generarCodMerma();
            
            DB::table('ControlMermas')->insert([
                'CodMerma' => $codMerma,
                'CodPro' => $request->codpro,
                'Lote' => $request->lote,
                'CantidadPerdida' => $request->cantidad_perdida,
                'Motivo' => $request->motivo,
                'FechaVencimiento' => $request->fecha_vencimiento,
                'FechaRegistro' => Carbon::now(),
                'CostoUnitario' => $costoUnitario,
                'Observaciones' => $request->observaciones,
                'Usuario' => Auth::id(),
                'Estado' => 'registrada'
            ]);

            // Actualizar stock del lote
            $nuevaCantidad = $lote->Cantidad - $request->cantidad_perdida;
            
            DB::table('Saldos')
                ->where('CodPro', $request->codpro)
                ->where('Lote', $request->lote)
                ->update([
                    'Cantidad' => $nuevaCantidad,
                    'FechaModificacion' => Carbon::now(),
                    'UsuarioModificacion' => Auth::id()
                ]);

            // Registrar en trazabilidad
            $this->registrarTrazabilidad($request->codpro, $request->lote, 'MERMA_REGISTRADA', 
                "Merma registrada por {$request->motivo} - Cantidad: {$request->cantidad_perdida}", 
                $nuevaCantidad);

            // Generar asiento contable
            $this->generarAsientoMerma($codMerma, $request->codpro, $request->cantidad_perdida, 
                $costoUnitario, $request->motivo);

            // Si es vencimiento, marcar producto como tal
            if ($request->motivo == 'vencido') {
                $this->marcarVencido($request->codpro, $request->lote, $request->cantidad_perdida);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Merma registrada correctamente',
                'data' => [
                    'cod_merma' => $codMerma,
                    'valor_perdida' => $request->cantidad_perdida * $costoUnitario
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar merma: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($codmerma)
    {
        $merma = DB::table('ControlMermas as cm')
            ->join('Productos as p', 'cm.CodPro', '=', 'p.CodPro')
            ->leftJoin('Laboratorios as l', 'p.CodLab', '=', 'l.CodLab')
            ->leftJoin('Saldos as s', function($join) {
                $join->on('cm.CodPro', '=', 's.CodPro')
                     ->on('cm.Lote', '=', 's.Lote');
            })
            ->leftJoin('users as u', 'cm.Usuario', '=', 'u.id')
            ->select([
                'cm.*',
                'p.Nombre as Producto',
                'p.Presentacion',
                'p.CostoPromedio',
                'l.Descripcion as Laboratorio',
                's.Vencimiento',
                's.Cantidad as StockActual',
                'u.name as UsuarioRegistro',
                DB::raw('(cm.CantidadPerdida * cm.CostoUnitario) as ValorPerdida'),
                DB::raw('DATEDIFF(day, cm.FechaVencimiento, cm.FechaRegistro) as DiasVencimiento')
            ])
            ->where('cm.CodMerma', $codmerma)
            ->first();

        if (!$merma) {
            return response()->json(['message' => 'Merma no encontrada'], 404);
        }

        // Historial del producto
        $historialProducto = $this->obtenerHistorialProducto($merma->CodPro);

        // Análisis financiero
        $analisisFinanciero = $this->analizarImpactoFinanciero($codmerma);

        // Documentos relacionados
        $documentosRelacionados = $this->obtenerDocumentosRelacionados($codmerma);

        return compact('merma', 'historialProducto', 'analisisFinanciero', 'documentosRelacionados');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($codmerma)
    {
        $merma = DB::table('ControlMermas as cm')
            ->join('Productos as p', 'cm.CodPro', '=', 'p.CodPro')
            ->where('cm.CodMerma', $codmerma)
            ->select('cm.*', 'p.Nombre as Producto', 'p.Presentacion')
            ->first();

        if (!$merma) {
            return response()->json(['message' => 'Merma no encontrada'], 404);
        }

        // No permitir editar si ya tiene asiento contable
        if ($merma->AsientoContable) {
            return response()->json([
                'message' => 'No se puede editar una merma que ya tiene asiento contable generado'
            ], 400);
        }

        $motivos = [
            'vencido' => 'Producto Vencido',
            'deteriorado' => 'Producto Deteriorado',
            'robado' => 'Robo o Pérdida',
            'devolucion_cliente' => 'Devolución de Cliente',
            'error_entrega' => 'Error en Entrega',
            'cadena_fria' => 'Falla en Cadena de Frío',
            'accidente' => 'Accidente o Daño',
            'retiro_regulatorio' => 'Retiro Regulatorio',
            'otros' => 'Otros'
        ];

        return compact('merma', 'motivos');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $codmerma)
    {
        $request->validate([
            'cantidad_perdida' => 'required|numeric|min:0.01',
            'motivo' => 'required|in:vencido,deteriorado,robado,devolucion_cliente,error_entrega,cadena_fria,accidente,retiro_regulatorio,otros',
            'observaciones' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            $mermaAnterior = DB::table('ControlMermas')
                ->where('CodMerma', $codmerma)
                ->first();

            if (!$mermaAnterior) {
                return response()->json(['message' => 'Merma no encontrada'], 404);
            }

            if ($mermaAnterior->AsientoContable) {
                return response()->json([
                    'message' => 'No se puede editar una merma que ya tiene asiento contable'
                ], 400);
            }

            // Verificar que la nueva cantidad no sea mayor al stock disponible
            $lote = DB::table('Saldos')
                ->where('CodPro', $mermaAnterior->CodPro)
                ->where('Lote', $mermaAnterior->Lote)
                ->first();

            $stockDisponible = $lote->Cantidad + $mermaAnterior->CantidadPerdida;
            
            if ($request->cantidad_perdida > $stockDisponible) {
                return response()->json([
                    'message' => 'La cantidad perdida no puede ser mayor al stock disponible'
                ], 400);
            }

            // Revertir cambio anterior en stock
            DB::table('Saldos')
                ->where('CodPro', $mermaAnterior->CodPro)
                ->where('Lote', $mermaAnterior->Lote)
                ->update([
                    'Cantidad' => $lote->Cantidad + $mermaAnterior->CantidadPerdida
                ]);

            // Aplicar nuevo cambio
            $nuevaCantidad = $lote->Cantidad + $mermaAnterior->CantidadPerdida - $request->cantidad_perdida;
            
            DB::table('Saldos')
                ->where('CodPro', $mermaAnterior->CodPro)
                ->where('Lote', $mermaAnterior->Lote)
                ->update([
                    'Cantidad' => $nuevaCantidad,
                    'FechaModificacion' => Carbon::now(),
                    'UsuarioModificacion' => Auth::id()
                ]);

            // Actualizar merma
            DB::table('ControlMermas')
                ->where('CodMerma', $codmerma)
                ->update([
                    'CantidadPerdida' => $request->cantidad_perdida,
                    'Motivo' => $request->motivo,
                    'Observaciones' => $request->observaciones,
                    'FechaModificacion' => Carbon::now(),
                    'UsuarioModificacion' => Auth::id()
                ]);

            // Registrar en trazabilidad
            $this->registrarTrazabilidad($mermaAnterior->CodPro, $mermaAnterior->Lote, 
                'MERMA_MODIFICADA', 
                "Merma modificada - Cantidad: {$mermaAnterior->CantidadPerdida} -> {$request->cantidad_perdida}", 
                $nuevaCantidad);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Merma actualizada correctamente',
                'data' => []
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar merma: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($codmerma)
    {
        try {
            DB::beginTransaction();

            $merma = DB::table('ControlMermas')
                ->where('CodMerma', $codmerma)
                ->first();

            if (!$merma) {
                return response()->json(['message' => 'Merma no encontrada'], 404);
            }

            if ($merma->AsientoContable) {
                return response()->json([
                    'message' => 'No se puede eliminar una merma que ya tiene asiento contable'
                ], 400);
            }

            // Revertir stock
            DB::table('Saldos')
                ->where('CodPro', $merma->CodPro)
                ->where('Lote', $merma->Lote)
                ->increment('Cantidad', $merma->CantidadPerdida);

            // Eliminar merma
            DB::table('ControlMermas')
                ->where('CodMerma', $codmerma)
                ->delete();

            // Registrar en trazabilidad
            $this->registrarTrazabilidad($merma->CodPro, $merma->Lote, 
                'MERMA_ELIMINADA', 
                "Merma eliminada - Cantidad: {$merma->CantidadPerdida}", 
                $merma->CantidadPerdida);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Merma eliminada correctamente',
                'data' => []
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar merma: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Aprobar merma y generar asiento contable
     */
    public function aprobar(Request $request, $codmerma)
    {
        $request->validate([
            'observaciones_aprobacion' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            $merma = DB::table('ControlMermas')
                ->where('CodMerma', $codmerma)
                ->first();

            if (!$merma) {
                return response()->json(['message' => 'Merma no encontrada'], 404);
            }

            if ($merma->Estado == 'aprobada') {
                return response()->json(['message' => 'La merma ya está aprobada'], 400);
            }

            // Generar asiento contable si no existe
            if (!$merma->AsientoContable) {
                $this->generarAsientoMerma($codmerma, $merma->CodPro, $merma->CantidadPerdida, 
                    $merma->CostoUnitario, $merma->Motivo);
            }

            // Aprobar merma
            DB::table('ControlMermas')
                ->where('CodMerma', $codmerma)
                ->update([
                    'Estado' => 'aprobada',
                    'FechaAprobacion' => Carbon::now(),
                    'UsuarioAprobacion' => Auth::id(),
                    'ObservacionesAprobacion' => $request->observaciones_aprobacion
                ]);

            // Registrar en trazabilidad
            $this->registrarTrazabilidad($merma->CodPro, $merma->Lote, 'MERMA_APROBADA', 
                "Merma aprobada por " . Auth::user()->name, $merma->CantidadPerdida);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Merma aprobada correctamente',
                'data' => []
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al aprobar merma: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reporte de mermas por período
     */
    public function reportePeriodo(Request $request)
    {
        $request->validate([
            'fecha_desde' => 'required|date',
            'fecha_hasta' => 'required|date|after:fecha_desde',
        ]);

        $mermas = DB::table('ControlMermas as cm')
            ->join('Productos as p', 'cm.CodPro', '=', 'p.CodPro')
            ->leftJoin('Laboratorios as l', 'p.CodLab', '=', 'l.CodLab')
            ->select([
                'cm.*',
                'p.Nombre as Producto',
                'p.Presentacion',
                'l.Descripcion as Laboratorio',
                DB::raw('(cm.CantidadPerdida * cm.CostoUnitario) as ValorPerdida')
            ])
            ->whereBetween('cm.FechaRegistro', [$request->fecha_desde, $request->fecha_hasta . ' 23:59:59'])
            ->orderBy('cm.FechaRegistro')
            ->get();

        // Resumen por motivo
        $resumenMotivos = $mermas->groupBy('Motivo')->map(function($group) {
            return [
                'cantidad_mermas' => $group->count(),
                'total_perdido' => $group->sum('ValorPerdida'),
                'total_unidades' => $group->sum('CantidadPerdida')
            ];
        });

        // Resumen por laboratorio
        $resumenLaboratorios = $mermas->groupBy('Laboratorio')->map(function($group) {
            return [
                'cantidad_mermas' => $group->count(),
                'total_perdido' => $group->sum('ValorPerdida'),
                'productos_afectados' => $group->pluck('Producto')->unique()->count()
            ];
        });

        $totalPerdido = $mermas->sum('ValorPerdida');
        $totalUnidades = $mermas->sum('CantidadPerdida');

        return compact('mermas', 'resumenMotivos', 'resumenLaboratorios', 'totalPerdido', 'totalUnidades');
    }

    /**
     * Dashboard de mermas
     */
    public function dashboard()
    {
        $hoy = Carbon::now()->toDateString();
        $inicioMes = Carbon::now()->startOfMonth()->toDateString();
        $finMes = Carbon::now()->endOfMonth()->toDateString();

        // Mermas del día
        $mermasHoy = DB::table('ControlMermas')
            ->where('FechaRegistro', '>=', $hoy)
            ->count();

        // Mermas del mes
        $mermasMes = DB::table('ControlMermas')
            ->whereBetween('FechaRegistro', [$inicioMes, $finMes])
            ->sum(DB::raw('CantidadPerdida * CostoUnitario'));

        // Productos más problemáticos
        $productosProblematicos = DB::table('ControlMermas as cm')
            ->join('Productos as p', 'cm.CodPro', '=', 'p.CodPro')
            ->select([
                'p.CodPro',
                'p.Nombre',
                DB::raw('SUM(cm.CantidadPerdida * cm.CostoUnitario) as TotalPerdido'),
                DB::raw('COUNT(*) as VecesMerma')
            ])
            ->where('cm.FechaRegistro', '>=', Carbon::now()->subMonths(6)->toDateString())
            ->groupBy('p.CodPro', 'p.Nombre')
            ->orderByDesc('TotalPerdido')
            ->limit(10)
            ->get();

        // Tendencia mensual
        $tendenciaMensual = DB::table('ControlMermas')
            ->select([
                DB::raw('YEAR(FechaRegistro) as Anio'),
                DB::raw('MONTH(FechaRegistro) as Mes'),
                DB::raw('SUM(CantidadPerdida * CostoUnitario) as TotalPerdido')
            ])
            ->where('FechaRegistro', '>=', Carbon::now()->subYear()->toDateString())
            ->groupBy(DB::raw('YEAR(FechaRegistro), MONTH(FechaRegistro)'))
            ->orderBy('Anio', 'desc')
            ->orderBy('Mes', 'desc')
            ->get();

        return compact('mermasHoy', 'mermasMes', 'productosProblematicos', 'tendenciaMensual');
    }

    /**
     * Calcular estadísticas de mermas
     */
    private function calcularEstadisticasMermas($request)
    {
        $query = DB::table('ControlMermas as cm')
            ->select([
                DB::raw('COUNT(*) as TotalMermas'),
                DB::raw('SUM(cm.CantidadPerdida * cm.CostoUnitario) as ValorTotal'),
                DB::raw('SUM(cm.CantidadPerdida) as TotalUnidades'),
                DB::raw('AVG(cm.CantidadPerdida) as PromedioUnidades')
            ]);

        if ($request->filled('fecha_desde')) {
            $query->where('cm.FechaRegistro', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->where('cm.FechaRegistro', '<=', $request->fecha_hasta . ' 23:59:59');
        }

        return $query->first();
    }

    /**
     * Analizar mermas por motivo
     */
    private function analizarMermasPorMotivo($request)
    {
        $query = DB::table('ControlMermas as cm')
            ->select([
                'cm.Motivo',
                DB::raw('COUNT(*) as Cantidad'),
                DB::raw('SUM(cm.CantidadPerdida * cm.CostoUnitario) as Valor'),
                DB::raw('AVG(cm.CantidadPerdida) as PromedioUnidades')
            ])
            ->groupBy('cm.Motivo');

        if ($request->filled('fecha_desde')) {
            $query->where('cm.FechaRegistro', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->where('cm.FechaRegistro', '<=', $request->fecha_hasta . ' 23:59:59');
        }

        return $query->orderByDesc('Valor')->get();
    }

    /**
     * Obtener top productos con más mermas
     */
    private function obtenerTopProductosMermas($request)
    {
        $query = DB::table('ControlMermas as cm')
            ->join('Productos as p', 'cm.CodPro', '=', 'p.CodPro')
            ->select([
                'p.CodPro',
                'p.Nombre',
                DB::raw('SUM(cm.CantidadPerdida * cm.CostoUnitario) as TotalPerdido'),
                DB::raw('COUNT(*) as VecesMerma')
            ])
            ->groupBy('p.CodPro', 'p.Nombre')
            ->orderByDesc('TotalPerdido')
            ->limit(10);

        if ($request->filled('fecha_desde')) {
            $query->where('cm.FechaRegistro', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->where('cm.FechaRegistro', '<=', $request->fecha_hasta . ' 23:59:59');
        }

        return $query->get();
    }

    /**
     * Obtener historial del producto
     */
    private function obtenerHistorialProducto($codpro)
    {
        return DB::table('ControlMermas as cm')
            ->where('cm.CodPro', $codpro)
            ->orderBy('cm.FechaRegistro', 'desc')
            ->limit(10)
            ->get();
    }

    /**
     * Analizar impacto financiero
     */
    private function analizarImpactoFinanciero($codmerma)
    {
        $merma = DB::table('ControlMermas')
            ->where('CodMerma', $codmerma)
            ->first();

        if (!$merma) return null;

        $valorPerdida = $merma->CantidadPerdida * $merma->CostoUnitario;

        // Calcular porcentaje sobre ventas del producto (último mes)
        $ventasMes = DB::table('Docdet as dd')
            ->join('Doccab as dc', 'dd.CodDoc', '=', 'dc.CodDoc')
            ->where('dd.CodPro', $merma->CodPro)
            ->where('dc.Fecha', '>=', Carbon::now()->subMonth()->toDateString())
            ->sum(DB::raw('dd.Subtotal'));

        $porcentajeImpacto = $ventasMes > 0 ? ($valorPerdida / $ventasMes) * 100 : 0;

        return [
            'valor_perdida' => $valorPerdida,
            'porcentaje_impacto' => round($porcentajeImpacto, 2),
            'impacto_financiero' => $porcentajeImpacto > 5 ? 'alto' : ($porcentajeImpacto > 2 ? 'medio' : 'bajo')
        ];
    }

    /**
     * Obtener documentos relacionados
     */
    private function obtenerDocumentosRelacionados($codmerma)
    {
        $merma = DB::table('ControlMermas')
            ->where('CodMerma', $codmerma)
            ->first();

        if (!$merma) return [];

        // Documentos de compra originales
        $documentosCompra = DB::table('Saldos as s')
            ->join('Doccab as dc', 's.CodDoc', '=', 'dc.CodDoc')
            ->where('s.CodPro', $merma->CodPro)
            ->where('s.Lote', $merma->Lote)
            ->select([
                'dc.Tipo',
                'dc.Serie',
                'dc.Numero',
                'dc.Fecha',
                'dc.CodDoc'
            ])
            ->get();

        // Asientos contables relacionados
        $asientosContables = DB::table('asientos_diario')
            ->where('Referencia', 'like', '%' . $codmerma . '%')
            ->select([
                'Codigo',
                'Fecha',
                'Glosa',
                'Debe',
                'Haber',
                'CuentaContable'
            ])
            ->get();

        return compact('documentosCompra', 'asientosContables');
    }

    /**
     * Marcar producto como vencido
     */
    private function marcarVencido($codpro, $lote, $cantidad)
    {
        DB::table('Saldos')
            ->where('CodPro', $codpro)
            ->where('Lote', $lote)
            ->update([
                'Observaciones' => 'Marcado por merma - Vencimiento',
                'FechaModificacion' => Carbon::now()
            ]);
    }

    /**
     * Generar código de merma
     */
    private function generarCodMerma()
    {
        $ultimo = DB::table('ControlMermas')
            ->max('CodMerma');

        if ($ultimo) {
            $numero = (int) substr($ultimo, 3) + 1;
        } else {
            $numero = 1;
        }

        return 'MER' . str_pad($numero, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Generar asiento contable por merma
     */
    private function generarAsientoMerma($codMerma, $codpro, $cantidad, $costoUnitario, $motivo)
    {
        $valorTotal = $cantidad * $costoUnitario;
        $glosa = "Merma por {$motivo} - Producto: {$codpro} - Código: {$codMerma}";
        
        // Cuenta de mermas (6395) y cuenta de inventario (2011)
        $cuentasMerma = [
            'merma' => '6395',  // Cuenta de mermas
            'inventario' => '2011'  // Cuenta de inventario
        ];

        // Registrar asiento en tabla asientos_diario
        $fecha = Carbon::now();
        
        // Debe: Merma
        DB::table('asientos_diario')->insert([
            'Codigo' => $this->generarCodigoAsiento(),
            'Fecha' => $fecha,
            'CuentaContable' => $cuentasMerma['merma'],
            'Glosa' => $glosa,
            'Debe' => $valorTotal,
            'Haber' => 0,
            'Referencia' => $codMerma,
            'TipoAsiento' => 'MERMA',
            'FechaCreacion' => $fecha,
            'UsuarioCreacion' => Auth::id()
        ]);

        // Haber: Inventario
        DB::table('asientos_diario')->insert([
            'Codigo' => $this->generarCodigoAsiento(),
            'Fecha' => $fecha,
            'CuentaContable' => $cuentasMerma['inventario'],
            'Glosa' => $glosa,
            'Debe' => 0,
            'Haber' => $valorTotal,
            'Referencia' => $codMerma,
            'TipoAsiento' => 'MERMA',
            'FechaCreacion' => $fecha,
            'UsuarioCreacion' => Auth::id()
        ]);

        // Actualizar merma con el asiento
        DB::table('ControlMermas')
            ->where('CodMerma', $codMerma)
            ->update(['AsientoContable' => 'REGISTRADO']);
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
     * Registrar en trazabilidad
     */
    private function registrarTrazabilidad($codpro, $lote, $accion, $descripcion, $cantidad)
    {
        DB::table('Trazabilidad')->insert([
            'CodPro' => $codpro,
            'Lote' => $lote,
            'Accion' => $accion,
            'Descripcion' => $descripcion,
            'Cantidad' => $cantidad,
            'Fecha' => Carbon::now(),
            'Usuario' => Auth::id(),
            'Observaciones' => 'Control de mermas'
        ]);
    }
}