<?php

namespace App\Http\Controllers\Farmacia;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TrazabilidadController extends Controller
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
        $query = DB::table('Trazabilidad as t')
            ->leftJoin('Productos as p', 't.CodPro', '=', 'p.CodPro')
            ->leftJoin('Laboratorios as l', 'p.CodLab', '=', 'l.CodLab')
            ->leftJoin('users as u', 't.Usuario', '=', 'u.id')
            ->select([
                't.*',
                'p.Nombre as Producto',
                'p.Presentacion',
                'l.Descripcion as Laboratorio',
                'u.name as UsuarioNombre'
            ]);

        // Filtros
        if ($request->filled('codpro')) {
            $query->where('t.CodPro', 'like', '%' . $request->codpro . '%');
        }

        if ($request->filled('producto')) {
            $query->where('p.Nombre', 'like', '%' . $request->producto . '%');
        }

        if ($request->filled('lote')) {
            $query->where('t.Lote', 'like', '%' . $request->lote . '%');
        }

        if ($request->filled('accion')) {
            $query->where('t.Accion', $request->accion);
        }

        if ($request->filled('fecha_desde')) {
            $query->where('t.Fecha', '>=', $request->fecha_desde . ' 00:00:00');
        }

        if ($request->filled('fecha_hasta')) {
            $query->where('t.Fecha', '<=', $request->fecha_hasta . ' 23:59:59');
        }

        if ($request->filled('usuario')) {
            $query->where('t.Usuario', $request->usuario);
        }

        if ($request->filled('tipo_evento')) {
            switch ($request->tipo_evento) {
                case 'movimiento':
                    $query->whereIn('t.Accion', ['ENTRADA', 'SALIDA', 'AJUSTE_STOCK']);
                    break;
                case 'control':
                    $query->whereIn('t.Accion', ['CREACION_LOTE', 'MODIFICACION_VENCIMIENTO', 'MERMA_REGISTRADA']);
                    break;
                case 'dispensacion':
                    $query->whereIn('t.Accion', ['DISPENSACION_CONTROLADA', 'VENTA_MEDICAMENTO_CONTROLADO']);
                    break;
            }
        }

        $trazabilidades = $query->orderBy('t.Fecha', 'desc')
            ->paginate(25);

        // Estadísticas de trazabilidad
        $estadisticas = $this->calcularEstadisticasTrazabilidad($request);

        // Acciones más frecuentes
        $accionesFrecuentes = $this->obtenerAccionesFrecuentes($request);

        // Productos con más movimientos
        $productosActivos = $this->obtenerProductosMasActivos($request);

        return compact('trazabilidades', 'estadisticas', 'accionesFrecuentes', 'productosActivos');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $productos = DB::table('Productos as p')
            ->leftJoin('Laboratorios as l', 'p.CodLab', '=', 'l.CodLab')
            ->where('p.EsActivo', true)
            ->select('p.CodPro', 'p.Nombre', 'p.Presentacion', 'l.Descripcion as Laboratorio')
            ->orderBy('p.Nombre')
            ->get();

        $acciones = [
            'ENTRADA' => 'Entrada de Producto',
            'SALIDA' => 'Salida de Producto',
            'AJUSTE_STOCK' => 'Ajuste de Stock',
            'CREACION_LOTE' => 'Creación de Lote',
            'MODIFICACION_VENCIMIENTO' => 'Modificación de Vencimiento',
            'MERMA_REGISTRADA' => 'Merma Registrada',
            'DISPENSACION_CONTROLADA' => 'Dispensación Controlada',
            'VENTA_MEDICAMENTO_CONTROLADO' => 'Venta Medicamento Controlado',
            'RETIRO_VENCIDO' => 'Retiro por Vencimiento',
            'DEVOLUCION_PROVEEDOR' => 'Devolución a Proveedor',
            'TRANSFERENCIA_ALMACEN' => 'Transferencia de Almacén',
            'REVISION_CALIDAD' => 'Revisión de Calidad',
            'OTRO' => 'Otro'
        ];

        $usuarios = DB::table('users')
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return compact('productos', 'acciones', 'usuarios');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'codpro' => 'required|exists:Productos,CodPro',
            'lote' => 'required|string|max:50',
            'accion' => 'required|in:ENTRADA,SALIDA,AJUSTE_STOCK,CREACION_LOTE,MODIFICACION_VENCIMIENTO,MERMA_REGISTRADA,DISPENSACION_CONTROLADA,VENTA_MEDICAMENTO_CONTROLADO,RETIRO_VENCIDO,DEVOLUCION_PROVEEDOR,TRANSFERENCIA_ALMACEN,REVISION_CALIDAD,OTRO',
            'descripcion' => 'required|string|max:500',
            'cantidad' => 'required|numeric',
            'observaciones' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            // Registrar trazabilidad
            DB::table('Trazabilidad')->insert([
                'CodPro' => $request->codpro,
                'Lote' => $request->lote,
                'Accion' => $request->accion,
                'Descripcion' => $request->descripcion,
                'Cantidad' => $request->cantidad,
                'Observaciones' => $request->observaciones,
                'Fecha' => Carbon::now(),
                'Usuario' => Auth::id(),
                'IPAddress' => $request->ip(),
                'UserAgent' => $request->userAgent()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Trazabilidad registrada correctamente',
                'data' => []
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar trazabilidad: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $trazabilidad = DB::table('Trazabilidad as t')
            ->leftJoin('Productos as p', 't.CodPro', '=', 'p.CodPro')
            ->leftJoin('Laboratorios as l', 'p.CodLab', '=', 'l.CodLab')
            ->leftJoin('users as u', 't.Usuario', '=', 'u.id')
            ->select([
                't.*',
                'p.Nombre as Producto',
                'p.Presentacion',
                'p.CodLab',
                'l.Descripcion as Laboratorio',
                'u.name as UsuarioNombre',
                'u.email as UsuarioEmail'
            ])
            ->where('t.Id', $id)
            ->first();

        if (!$trazabilidad) {
            return response()->json(['message' => 'Registro de trazabilidad no encontrado'], 404);
        }

        // Contexto del producto
        $contextoProducto = $this->obtenerContextoProducto($trazabilidad->CodPro, $trazabilidad->Lote);

        // Eventos relacionados
        $eventosRelacionados = $this->obtenerEventosRelacionados($trazabilidad->CodPro, $trazabilidad->Lote, $trazabilidad->Fecha);

        // Análisis de impacto
        $analisisImpacto = $this->analizarImpactoTrazabilidad($trazabilidad);

        return compact('trazabilidad', 'contextoProducto', 'eventosRelacionados', 'analisisImpacto');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $trazabilidad = DB::table('Trazabilidad as t')
            ->where('t.Id', $id)
            ->first();

        if (!$trazabilidad) {
            return response()->json(['message' => 'Registro de trazabilidad no encontrado'], 404);
        }

        // Verificar si se puede editar (solo observaciones y descripción)
        $puedeEditar = Carbon::parse($trazabilidad->Fecha)->diffInHours(Carbon::now()) < 24;

        if (!$puedeEditar) {
            return response()->json([
                'message' => 'No se puede editar un registro con más de 24 horas de antigüedad'
            ], 400);
        }

        $acciones = [
            'ENTRADA' => 'Entrada de Producto',
            'SALIDA' => 'Salida de Producto',
            'AJUSTE_STOCK' => 'Ajuste de Stock',
            'CREACION_LOTE' => 'Creación de Lote',
            'MODIFICACION_VENCIMIENTO' => 'Modificación de Vencimiento',
            'MERMA_REGISTRADA' => 'Merma Registrada',
            'DISPENSACION_CONTROLADA' => 'Dispensación Controlada',
            'VENTA_MEDICAMENTO_CONTROLADO' => 'Venta Medicamento Controlado',
            'RETIRO_VENCIDO' => 'Retiro por Vencimiento',
            'DEVOLUCION_PROVEEDOR' => 'Devolución a Proveedor',
            'TRANSFERENCIA_ALMACEN' => 'Transferencia de Almacén',
            'REVISION_CALIDAD' => 'Revisión de Calidad',
            'OTRO' => 'Otro'
        ];

        return compact('trazabilidad', 'acciones');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'accion' => 'required|in:ENTRADA,SALIDA,AJUSTE_STOCK,CREACION_LOTE,MODIFICACION_VENCIMIENTO,MERMA_REGISTRADA,DISPENSACION_CONTROLADA,VENTA_MEDICAMENTO_CONTROLADO,RETIRO_VENCIDO,DEVOLUCION_PROVEEDOR,TRANSFERENCIA_ALMACEN,REVISION_CALIDAD,OTRO',
            'descripcion' => 'required|string|max:500',
            'observaciones' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            $trazabilidad = DB::table('Trazabilidad')
                ->where('Id', $id)
                ->first();

            if (!$trazabilidad) {
                return response()->json(['message' => 'Registro de trazabilidad no encontrado'], 404);
            }

            // Verificar antigüedad
            if (Carbon::parse($trazabilidad->Fecha)->diffInHours(Carbon::now()) >= 24) {
                return response()->json([
                    'message' => 'No se puede editar un registro con más de 24 horas de antigüedad'
                ], 400);
            }

            // Actualizar solo campos editables
            DB::table('Trazabilidad')
                ->where('Id', $id)
                ->update([
                    'Accion' => $request->accion,
                    'Descripcion' => $request->descripcion,
                    'Observaciones' => $request->observaciones,
                    'FechaModificacion' => Carbon::now(),
                    'UsuarioModificacion' => Auth::id()
                ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Trazabilidad actualizada correctamente',
                'data' => []
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar trazabilidad: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $trazabilidad = DB::table('Trazabilidad')
                ->where('Id', $id)
                ->first();

            if (!$trazabilidad) {
                return response()->json(['message' => 'Registro de trazabilidad no encontrado'], 404);
            }

            // Solo permitir eliminar registros sin modificar
            if (Carbon::parse($trazabilidad->Fecha)->diffInHours(Carbon::now()) >= 24) {
                return response()->json([
                    'message' => 'No se puede eliminar un registro con más de 24 horas de antigüedad'
                ], 400);
            }

            // Verificar si es un registro crítico (medicamentos controlados)
            $esControlado = DB::table('Productos')
                ->where('CodPro', $trazabilidad->CodPro)
                ->where('EsControlado', true)
                ->exists();

            if ($esControlado) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pueden eliminar registros de medicamentos controlados'
                ], 400);
            }

            DB::table('Trazabilidad')
                ->where('Id', $id)
                ->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Registro de trazabilidad eliminado correctamente',
                'data' => []
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar trazabilidad: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Trazabilidad completa de un producto
     */
    public function trazabilidadCompleta(Request $request, $codpro)
    {
        $request->validate([
            'fecha_desde' => 'nullable|date',
            'fecha_hasta' => 'nullable|date|after:fecha_desde',
            'lote' => 'nullable|string|max:50',
        ]);

        $producto = DB::table('Productos as p')
            ->leftJoin('Laboratorios as l', 'p.CodLab', '=', 'l.CodLab')
            ->select('p.*', 'l.Descripcion as Laboratorio')
            ->where('p.CodPro', $codpro)
            ->first();

        if (!$producto) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }

        // Query base para trazabilidad
        $query = DB::table('Trazabilidad')
            ->where('CodPro', $codpro);

        if ($request->filled('lote')) {
            $query->where('Lote', $request->lote);
        }

        if ($request->filled('fecha_desde')) {
            $query->where('Fecha', '>=', $request->fecha_desde . ' 00:00:00');
        }

        if ($request->filled('fecha_hasta')) {
            $query->where('Fecha', '<=', $request->fecha_hasta . ' 23:59:59');
        }

        $eventos = $query->orderBy('Fecha')
            ->get();

        // Stock actual del producto
        $stockActual = DB::table('Saldos')
            ->where('CodPro', $codpro)
            ->where('Cantidad', '>', 0)
            ->get();

        // Resumen de trazabilidad
        $resumen = [
            'total_eventos' => $eventos->count(),
            'primera_entrada' => $eventos->where('Accion', 'ENTRADA')->min('Fecha'),
            'ultimo_evento' => $eventos->max('Fecha'),
            'entradas_total' => $eventos->where('Accion', 'ENTRADA')->sum('Cantidad'),
            'salidas_total' => $eventos->where('Accion', 'SALIDA')->sum('Cantidad'),
            'ajustes_total' => $eventos->where('Accion', 'AJUSTE_STOCK')->sum('Cantidad'),
            'lotes_diferentes' => $eventos->pluck('Lote')->unique()->count()
        ];

        // Línea de tiempo cronológica
        $timeline = $eventos->map(function($evento) {
            return [
                'fecha' => $evento->Fecha,
                'accion' => $evento->Accion,
                'descripcion' => $evento->Descripcion,
                'cantidad' => $evento->Cantidad,
                'lote' => $evento->Lote,
                'observaciones' => $evento->Observaciones,
                'tipo_evento' => $this->clasificarEvento($evento->Accion)
            ];
        });

        return compact('producto', 'stockActual', 'eventos', 'resumen', 'timeline');
    }

    /**
     * Trazabilidad por lote
     */
    public function trazabilidadLote(Request $request, $codpro, $lote)
    {
        $producto = DB::table('Productos')
            ->where('CodPro', $codpro)
            ->first();

        if (!$producto) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }

        $loteInfo = DB::table('Saldos')
            ->where('CodPro', $codpro)
            ->where('Lote', $lote)
            ->first();

        if (!$loteInfo) {
            return response()->json(['message' => 'Lote no encontrado'], 404);
        }

        // Eventos del lote
        $eventosLote = DB::table('Trazabilidad')
            ->where('CodPro', $codpro)
            ->where('Lote', $lote)
            ->orderBy('Fecha')
            ->get();

        // Información del lote
        $infoLote = [
            'lote' => $lote,
            'vencimiento' => $loteInfo->Vencimiento,
            'cantidad_inicial' => $eventosLote->where('Accion', 'CREACION_LOTE')->first()->Cantidad ?? 0,
            'cantidad_actual' => $loteInfo->Cantidad,
            'fecha_vencimiento' => $loteInfo->Vencimiento,
            'dias_vendidos' => $loteInfo->Vencimiento ? Carbon::parse($loteInfo->Vencimiento)->diffInDays(Carbon::now()) : null
        ];

        // Análisis del lote
        $analisisLote = $this->analizarLote($eventosLote, $infoLote);

        return compact('producto', 'infoLote', 'eventosLote', 'analisisLote');
    }

    /**
     * Dashboard de trazabilidad
     */
    public function dashboard()
    {
        // Estadísticas generales del día
        $hoy = Carbon::now()->toDateString();
        
        $eventosHoy = DB::table('Trazabilidad')
            ->where('Fecha', '>=', $hoy . ' 00:00:00')
            ->count();

        // Eventos por tipo (últimos 7 días)
        $eventosSemana = DB::table('Trazabilidad')
            ->where('Fecha', '>=', Carbon::now()->subDays(7)->toDateString())
            ->select('Accion', DB::raw('COUNT(*) as Cantidad'))
            ->groupBy('Accion')
            ->orderByDesc('Cantidad')
            ->get();

        // Productos más trazados
        $productosTrazados = DB::table('Trazabilidad as t')
            ->join('Productos as p', 't.CodPro', '=', 'p.CodPro')
            ->where('t.Fecha', '>=', Carbon::now()->subDays(30)->toDateString())
            ->select([
                't.CodPro',
                'p.Nombre',
                DB::raw('COUNT(*) as TotalEventos')
            ])
            ->groupBy('t.CodPro', 'p.Nombre')
            ->orderByDesc('TotalEventos')
            ->limit(10)
            ->get();

        // Alertas de trazabilidad
        $alertasTrazabilidad = $this->generarAlertasTrazabilidad();

        return compact('eventosHoy', 'eventosSemana', 'productosTrazados', 'alertasTrazabilidad');
    }

    /**
     * Reporte de trazabilidad por período
     */
    public function reportePeriodo(Request $request)
    {
        $request->validate([
            'fecha_desde' => 'required|date',
            'fecha_hasta' => 'required|date|after:fecha_desde',
        ]);

        $eventos = DB::table('Trazabilidad as t')
            ->leftJoin('Productos as p', 't.CodPro', '=', 'p.CodPro')
            ->leftJoin('Laboratorios as l', 'p.CodLab', '=', 'l.CodLab')
            ->select([
                't.*',
                'p.Nombre as Producto',
                'l.Descripcion as Laboratorio'
            ])
            ->whereBetween('t.Fecha', [$request->fecha_desde . ' 00:00:00', $request->fecha_hasta . ' 23:59:59'])
            ->orderBy('t.Fecha')
            ->get();

        // Resumen por acción
        $resumenAcciones = $eventos->groupBy('Accion')->map(function($group) {
            return [
                'cantidad_eventos' => $group->count(),
                'cantidad_total' => $group->sum('Cantidad'),
                'productos_afectados' => $group->pluck('CodPro')->unique()->count()
            ];
        });

        // Resumen por laboratorio
        $resumenLaboratorios = $eventos->groupBy('Laboratorio')->map(function($group) {
            return [
                'eventos' => $group->count(),
                'productos' => $group->pluck('CodPro')->unique()->count()
            ];
        });

        return compact('eventos', 'resumenAcciones', 'resumenLaboratorios');
    }

    /**
     * Verificar integridad de trazabilidad
     */
    public function verificarIntegridad()
    {
        $problemas = [];

        // Verificar stocks inconsistentes
        $stocksInconsistentes = DB::table('Saldos as s')
            ->leftJoin('Trazabilidad as t', function($join) {
                $join->on('s.CodPro', '=', 't.CodPro')
                     ->on('s.Lote', '=', 't.Lote');
            })
            ->where('s.Cantidad', '>', 0)
            ->select([
                's.CodPro',
                's.Lote',
                's.Cantidad as StockActual',
                DB::raw('COALESCE(SUM(CASE 
                    WHEN t.Accion = "CREACION_LOTE" THEN t.Cantidad
                    WHEN t.Accion = "ENTRADA" THEN t.Cantidad
                    WHEN t.Accion = "SALIDA" THEN -t.Cantidad
                    WHEN t.Accion = "AJUSTE_STOCK" THEN t.Cantidad
                    ELSE 0
                END), 0) as CantidadTrazabilidad')
            ])
            ->groupBy('s.CodPro', 's.Lote', 's.Cantidad')
            ->havingRaw('ABS(s.Cantidad - COALESCE(SUM(CASE 
                WHEN t.Accion = "CREACION_LOTE" THEN t.Cantidad
                WHEN t.Accion = "ENTRADA" THEN t.Cantidad
                WHEN t.Accion = "SALIDA" THEN -t.Cantidad
                WHEN t.Accion = "AJUSTE_STOCK" THEN t.Cantidad
                ELSE 0
            END), 0)) > 0.01')
            ->get();

        foreach ($stocksInconsistentes as $stock) {
            $problemas[] = [
                'tipo' => 'stock_inconsistente',
                'codpro' => $stock->CodPro,
                'lote' => $stock->Lote,
                'stock_sistema' => $stock->StockActual,
                'stock_trazabilidad' => round($stock->CantidadTrazabilidad, 2),
                'diferencia' => round($stock->StockActual - $stock->CantidadTrazabilidad, 2)
            ];
        }

        // Verificar eventos sin lote
        $eventosSinLote = DB::table('Trazabilidad')
            ->where('Lote', '')
            ->orWhereNull('Lote')
            ->count();

        if ($eventosSinLote > 0) {
            $problemas[] = [
                'tipo' => 'eventos_sin_lote',
                'cantidad' => $eventosSinLote,
                'descripcion' => 'Eventos registrados sin información de lote'
            ];
        }

        return [
            'integridad_ok' => empty($problemas),
            'problemas_encontrados' => $problemas,
            'total_problemas' => count($problemas)
        ];
    }

    /**
     * Calcular estadísticas de trazabilidad
     */
    private function calcularEstadisticasTrazabilidad($request)
    {
        $query = DB::table('Trazabilidad');

        if ($request->filled('fecha_desde')) {
            $query->where('Fecha', '>=', $request->fecha_desde . ' 00:00:00');
        }

        if ($request->filled('fecha_hasta')) {
            $query->where('Fecha', '<=', $request->fecha_hasta . ' 23:59:59');
        }

        return [
            'total_registros' => $query->count(),
            'eventos_entrada' => $query->where('Accion', 'ENTRADA')->count(),
            'eventos_salida' => $query->where('Accion', 'SALIDA')->count(),
            'ajustes_stock' => $query->where('Accion', 'AJUSTE_STOCK')->count(),
            'medicamentos_controlados' => $query->whereIn('Accion', ['DISPENSACION_CONTROLADA', 'VENTA_MEDICAMENTO_CONTROLADO'])->count(),
            'productos_afectados' => $query->distinct('CodPro')->count('CodPro'),
            'usuarios_activos' => $query->distinct('Usuario')->count('Usuario')
        ];
    }

    /**
     * Obtener acciones más frecuentes
     */
    private function obtenerAccionesFrecuentes($request)
    {
        $query = DB::table('Trazabilidad');

        if ($request->filled('fecha_desde')) {
            $query->where('Fecha', '>=', $request->fecha_desde . ' 00:00:00');
        }

        if ($request->filled('fecha_hasta')) {
            $query->where('Fecha', '<=', $request->fecha_hasta . ' 23:59:59');
        }

        return $query->select('Accion', DB::raw('COUNT(*) as Cantidad'))
            ->groupBy('Accion')
            ->orderByDesc('Cantidad')
            ->limit(10)
            ->get();
    }

    /**
     * Obtener productos más activos
     */
    private function obtenerProductosMasActivos($request)
    {
        $query = DB::table('Trazabilidad as t')
            ->join('Productos as p', 't.CodPro', '=', 'p.CodPro');

        if ($request->filled('fecha_desde')) {
            $query->where('t.Fecha', '>=', $request->fecha_desde . ' 00:00:00');
        }

        if ($request->filled('fecha_hasta')) {
            $query->where('t.Fecha', '<=', $request->fecha_hasta . ' 23:59:59');
        }

        return $query->select([
            't.CodPro',
            'p.Nombre',
            DB::raw('COUNT(*) as TotalEventos')
        ])
        ->groupBy('t.CodPro', 'p.Nombre')
        ->orderByDesc('TotalEventos')
        ->limit(10)
        ->get();
    }

    /**
     * Obtener contexto del producto
     */
    private function obtenerContextoProducto($codpro, $lote)
    {
        $contexto = [];

        // Información del lote
        $contexto['lote_actual'] = DB::table('Saldos')
            ->where('CodPro', $codpro)
            ->where('Lote', $lote)
            ->first();

        // Información del producto
        $contexto['producto'] = DB::table('Productos as p')
            ->leftJoin('Laboratorios as l', 'p.CodLab', '=', 'l.CodLab')
            ->where('p.CodPro', $codpro)
            ->select('p.*', 'l.Descripcion as Laboratorio')
            ->first();

        return $contexto;
    }

    /**
     * Obtener eventos relacionados
     */
    private function obtenerEventosRelacionados($codpro, $lote, $fechaEvento)
    {
        $fechaLimite = Carbon::parse($fechaEvento)->subHours(2);
        $fechaLimiteSuperior = Carbon::parse($fechaEvento)->addHours(2);

        return DB::table('Trazabilidad')
            ->where('CodPro', $codpro)
            ->where('Lote', $lote)
            ->whereBetween('Fecha', [$fechaLimite, $fechaLimiteSuperior])
            ->where('Id', '!=', DB::table('Trazabilidad')
                ->where('CodPro', $codpro)
                ->where('Lote', $lote)
                ->where('Fecha', $fechaEvento)
                ->value('Id'))
            ->orderBy('Fecha')
            ->get();
    }

    /**
     * Analizar impacto de trazabilidad
     */
    private function analizarImpactoTrazabilidad($trazabilidad)
    {
        $impacto = [
            'nivel' => 'bajo',
            'descripcion' => 'Evento sin impacto significativo',
            'recomendaciones' => []
        ];

        switch ($trazabilidad->Accion) {
            case 'DISPENSACION_CONTROLADA':
                $impacto['nivel'] = 'critico';
                $impacto['descripcion'] = 'Dispensación de medicamento controlado - Requiere seguimiento';
                $impacto['recomendaciones'] = ['Verificar receta', 'Mantener registro', 'Revisar stock'];
                break;
            case 'MERMA_REGISTRADA':
                $impacto['nivel'] = 'alto';
                $impacto['descripcion'] = 'Pérdida de producto - Impacto financiero';
                $impacto['recomendaciones'] = ['Analizar causas', 'Revisar controles', 'Actualizar inventario'];
                break;
            case 'AJUSTE_STOCK':
                $impacto['nivel'] = 'medio';
                $impacto['descripcion'] = 'Ajuste de inventario - Verificar consistencia';
                $impacto['recomendaciones'] = ['Verificar documentación', 'Confirmar con supervisor'];
                break;
        }

        return $impacto;
    }

    /**
     * Clasificar evento
     */
    private function clasificarEvento($accion)
    {
        $clasificaciones = [
            'ENTRADA' => 'movimiento',
            'SALIDA' => 'movimiento',
            'AJUSTE_STOCK' => 'movimiento',
            'CREACION_LOTE' => 'control',
            'MODIFICACION_VENCIMIENTO' => 'control',
            'MERMA_REGISTRADA' => 'control',
            'DISPENSACION_CONTROLADA' => 'dispensacion',
            'VENTA_MEDICAMENTO_CONTROLADO' => 'dispensacion',
            'RETIRO_VENCIDO' => 'control',
            'DEVOLUCION_PROVEEDOR' => 'movimiento',
            'TRANSFERENCIA_ALMACEN' => 'movimiento',
            'REVISION_CALIDAD' => 'control',
            'OTRO' => 'general'
        ];

        return $clasificaciones[$accion] ?? 'general';
    }

    /**
     * Analizar lote
     */
    private function analizarLote($eventos, $infoLote)
    {
        $analisis = [
            'dias_en_inventario' => Carbon::parse($eventos->min('Fecha'))->diffInDays(Carbon::now()),
            'rotacion' => 'desconocida',
            'estado' => 'normal',
            'riesgo_vencimiento' => 'bajo',
            'recomendaciones' => []
        ];

        // Calcular días en inventario
        if ($infoLote['dias_vendidos'] !== null) {
            if ($infoLote['dias_vendidos'] <= 30) {
                $analisis['riesgo_vencimiento'] = 'critico';
                $analisis['recomendaciones'][] = 'Venta urgente - Producto próximo a vencer';
            } elseif ($infoLote['dias_vendidos'] <= 90) {
                $analisis['riesgo_vencimiento'] = 'medio';
                $analisis['recomendaciones'][] = 'Monitorear rotación';
            }
        }

        // Evaluar rotación
        $ventas = $eventos->whereIn('Accion', ['SALIDA', 'DISPENSACION_CONTROLADA'])->sum('Cantidad');
        $rotacion = $infoLote['cantidad_inicial'] > 0 ? $ventas / $infoLote['cantidad_inicial'] : 0;

        if ($rotacion > 0.8) {
            $analisis['rotacion'] = 'alta';
        } elseif ($rotacion > 0.4) {
            $analisis['rotacion'] = 'media';
        } else {
            $analisis['rotacion'] = 'baja';
            $analisis['recomendaciones'][] = 'Promocionar producto para mejorar rotación';
        }

        return $analisis;
    }

    /**
     * Generar alertas de trazabilidad
     */
    private function generarAlertasTrazabilidad()
    {
        $alertas = [];

        // Productos sin trazabilidad reciente
        $sinTrazabilidad = DB::table('Saldos as s')
            ->join('Productos as p', 's.CodPro', '=', 'p.CodPro')
            ->leftJoin('Trazabilidad as t', 's.CodPro', '=', 't.CodPro')
            ->where('s.Cantidad', '>', 0)
            ->where('t.Fecha', '<', Carbon::now()->subDays(7)->toDateString())
            ->select([
                'p.Nombre',
                's.Lote',
                's.Cantidad',
                't.Fecha'
            ])
            ->distinct()
            ->get();

        foreach ($sinTrazabilidad as $producto) {
            $alertas[] = [
                'tipo' => 'falta_trazabilidad',
                'nivel' => 'medio',
                'producto' => $producto->Nombre,
                'lote' => $producto->Lote,
                'cantidad' => $producto->Cantidad,
                'dias_sin_registro' => Carbon::parse($producto->Fecha)->diffInDays(Carbon::now()),
                'mensaje' => "Producto {$producto->Nombre} sin trazabilidad reciente",
                'accion' => 'registrar_movimiento'
            ];
        }

        return $alertas;
    }

    /**
     * Exportar trazabilidad
     */
    public function exportar(Request $request)
    {
        $query = DB::table('Trazabilidad as t')
            ->leftJoin('Productos as p', 't.CodPro', '=', 'p.CodPro')
            ->leftJoin('Laboratorios as l', 'p.CodLab', '=', 'l.CodLab')
            ->select([
                't.Fecha',
                't.CodPro',
                'p.Nombre as Producto',
                'l.Descripcion as Laboratorio',
                't.Lote',
                't.Accion',
                't.Descripcion',
                't.Cantidad',
                't.Observaciones'
            ]);

        if ($request->filled('fecha_desde')) {
            $query->where('t.Fecha', '>=', $request->fecha_desde . ' 00:00:00');
        }

        if ($request->filled('fecha_hasta')) {
            $query->where('t.Fecha', '<=', $request->fecha_hasta . ' 23:59:59');
        }

        $datos = $query->orderBy('t.Fecha')->get();

        return response()->json([
            'success' => true,
            'data' => $datos,
            'message' => 'Datos preparados para exportación'
        ]);
    }
}