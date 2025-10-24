<?php

namespace App\Http\Controllers\Farmacia;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ControlLotesController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'rol:farmaceutico,administrador']);
    }

    /**
     * Display a listing of resource.
     */
    public function index(Request $request)
    {
        $query = DB::table('Productos as p')
            ->leftJoin('Saldos as s', 'p.CodPro', '=', 's.CodPro')
            ->leftJoin('Laboratorios as l', 'p.CodLab', '=', 'l.CodLab')
            ->leftJoin('Doccab as dc', 's.CodDoc', '=', 'dc.CodDoc')
            ->select([
                'p.CodPro',
                'p.Nombre',
                's.Lote',
                's.Vencimiento',
                's.Cantidad',
                's.Costo',
                's.StockMinimo',
                'l.Descripcion as Laboratorio',
                'dc.Tipo',
                'dc.Fecha as FechaIngreso',
                DB::raw('DATEDIFF(day, GETDATE(), s.Vencimiento) as DiasVencimiento')
            ])
            ->where('s.Lote', '!=', '')
            ->whereNotNull('s.Lote');

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

        if ($request->filled('vencimiento')) {
            if ($request->vencimiento == 'proximo') {
                $query->where('s.Vencimiento', '<=', Carbon::now()->addDays(30)->toDateString());
            } elseif ($request->vencimiento == 'vencido') {
                $query->where('s.Vencimiento', '<', Carbon::now()->toDateString());
            }
        }

        if ($request->filled('stock_bajo')) {
            $query->whereRaw('s.Cantidad <= s.StockMinimo');
        }

        $lotes = $query->orderBy('s.Vencimiento')
            ->orderBy('p.Nombre')
            ->paginate(20);

        // Estadísticas
        $estadisticas = [
            'total_productos' => $query->count(),
            'proximos_vencer' => DB::table('Saldos as s')
                ->where('s.Vencimiento', '<=', Carbon::now()->addDays(30)->toDateString())
                ->where('s.Lote', '!=', '')
                ->count(),
            'vencidos' => DB::table('Saldos as s')
                ->where('s.Vencimiento', '<', Carbon::now()->toDateString())
                ->where('s.Lote', '!=', '')
                ->count(),
            'stock_bajo' => DB::table('Saldos as s')
                ->whereRaw('s.Cantidad <= s.StockMinimo')
                ->where('s.Lote', '!=', '')
                ->count(),
        ];

        // Alertas automáticas
        $alertas = $this->generarAlertasLotes();

        return compact('lotes', 'estadisticas', 'alertas');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Obtener productos sin lotes para crear registros
        $productos = DB::table('Productos as p')
            ->leftJoin('Laboratorios as l', 'p.CodLab', '=', 'l.CodLab')
            ->select('p.CodPro', 'p.Nombre', 'l.Descripcion as Laboratorio')
            ->orderBy('p.Nombre')
            ->get();

        $laboratorios = DB::table('Laboratorios')
            ->orderBy('Descripcion')
            ->get();

        return compact('productos', 'laboratorios');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'codpro' => 'required|exists:Productos,CodPro',
            'lote' => 'required|string|max:50',
            'vencimiento' => 'required|date|after:today',
            'cantidad' => 'required|numeric|min:0',
            'costo' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Verificar si ya existe el lote
            $loteExistente = DB::table('Saldos')
                ->where('CodPro', $request->codpro)
                ->where('Lote', $request->lote)
                ->first();

            if ($loteExistente) {
                // Actualizar lote existente
                DB::table('Saldos')
                    ->where('CodPro', $request->codpro)
                    ->where('Lote', $request->lote)
                    ->update([
                        'Cantidad' => $request->cantidad,
                        'Costo' => $request->costo,
                        'Vencimiento' => $request->vencimiento,
                        'FechaModificacion' => Carbon::now(),
                        'UsuarioModificacion' => Auth::id()
                    ]);
            } else {
                // Crear nuevo lote
                $codDoc = $this->generarCodDoc('LOT');
                
                DB::table('Saldos')->insert([
                    'CodDoc' => $codDoc,
                    'CodPro' => $request->codpro,
                    'Lote' => $request->lote,
                    'Vencimiento' => $request->vencimiento,
                    'Cantidad' => $request->cantidad,
                    'Costo' => $request->costo,
                    'StockMinimo' => $request->stock_minimo ?? 10,
                    'FechaCreacion' => Carbon::now(),
                    'UsuarioCreacion' => Auth::id()
                ]);

                // Registrar en log de trazabilidad
                $this->registrarTrazabilidad($request->codpro, $request->lote, 'CREACION_LOTE', 'Lote creado', $request->cantidad);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Lote registrado correctamente',
                'data' => []
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar lote: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($codpro, $lote)
    {
        $producto = DB::table('Productos as p')
            ->leftJoin('Laboratorios as l', 'p.CodLab', '=', 'l.CodLab')
            ->select('p.*', 'l.Descripcion as NombreLab')
            ->where('p.CodPro', $codpro)
            ->first();

        $loteDetalle = DB::table('Saldos as s')
            ->leftJoin('Doccab as dc', 's.CodDoc', '=', 'dc.CodDoc')
            ->where('s.CodPro', $codpro)
            ->where('s.Lote', $lote)
            ->select([
                's.*',
                'dc.Tipo',
                'dc.Fecha',
                'dc.Serie',
                'dc.Numero',
                DB::raw('DATEDIFF(day, GETDATE(), s.Vencimiento) as DiasVencimiento')
            ])
            ->first();

        if (!$producto || !$loteDetalle) {
            return response()->json(['message' => 'Producto o lote no encontrado'], 404);
        }

        // Historial de movimientos del lote
        $historial = $this->obtenerHistorialLote($codpro, $lote);

        // Análisis de rotación
        $rotacion = $this->calcularRotacionLote($codpro, $lote);

        return compact('producto', 'loteDetalle', 'historial', 'rotacion');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($codpro, $lote)
    {
        $lote = DB::table('Saldos as s')
            ->leftJoin('Productos as p', 's.CodPro', '=', 'p.CodPro')
            ->leftJoin('Laboratorios as l', 'p.CodLab', '=', 'l.CodLab')
            ->where('s.CodPro', $codpro)
            ->where('s.Lote', $lote)
            ->select([
                's.*',
                'p.Nombre',
                'p.CodPro',
                'l.Descripcion as Laboratorio'
            ])
            ->first();

        if (!$lote) {
            return response()->json(['message' => 'Lote no encontrado'], 404);
        }

        return compact('lote');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $codpro, $lote)
    {
        $request->validate([
            'vencimiento' => 'required|date',
            'cantidad' => 'required|numeric|min:0',
            'costo' => 'required|numeric|min:0',
            'stock_minimo' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $loteAnterior = DB::table('Saldos')
                ->where('CodPro', $codpro)
                ->where('Lote', $lote)
                ->first();

            DB::table('Saldos')
                ->where('CodPro', $codpro)
                ->where('Lote', $lote)
                ->update([
                    'Vencimiento' => $request->vencimiento,
                    'Cantidad' => $request->cantidad,
                    'Costo' => $request->costo,
                    'StockMinimo' => $request->stock_minimo,
                    'FechaModificacion' => Carbon::now(),
                    'UsuarioModificacion' => Auth::id()
                ]);

            // Registrar cambios en trazabilidad
            if ($loteAnterior->Cantidad != $request->cantidad) {
                $this->registrarTrazabilidad($codpro, $lote, 'AJUSTE_CANTIDAD', 
                    "Cantidad ajustada de {$loteAnterior->Cantidad} a {$request->cantidad}", 
                    $request->cantidad);
            }

            if ($loteAnterior->Costo != $request->costo) {
                $this->registrarTrazabilidad($codpro, $lote, 'AJUSTE_COSTO', 
                    "Costo ajustado de {$loteAnterior->Costo} a {$request->costo}", 
                    $request->cantidad);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Lote actualizado correctamente',
                'data' => []
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar lote: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($codpro, $lote)
    {
        try {
            DB::beginTransaction();

            // Verificar si hay movimientos
            $movimientos = DB::table('Doccab as dc')
                ->join('Docdet as dd', 'dc.CodDoc', '=', 'dd.CodDoc')
                ->where('dd.CodPro', $codpro)
                ->where('dd.Lote', $lote)
                ->count();

            if ($movimientos > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar el lote porque tiene movimientos registrados'
                ], 400);
            }

            DB::table('Saldos')
                ->where('CodPro', $codpro)
                ->where('Lote', $lote)
                ->delete();

            $this->registrarTrazabilidad($codpro, $lote, 'ELIMINACION_LOTE', 'Lote eliminado', 0);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Lote eliminado correctamente',
                'data' => []
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar lote: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar lotes a Excel
     */
    public function exportar(Request $request)
    {
        $query = DB::table('Productos as p')
            ->leftJoin('Saldos as s', 'p.CodPro', '=', 's.CodPro')
            ->leftJoin('Laboratorios as l', 'p.CodLab', '=', 'l.CodLab')
            ->select([
                'p.CodPro',
                'p.Nombre',
                's.Lote',
                's.Vencimiento',
                's.Cantidad',
                's.Costo',
                'l.Descripcion as Laboratorio',
                DB::raw('DATEDIFF(day, GETDATE(), s.Vencimiento) as DiasVencimiento')
            ])
            ->where('s.Lote', '!=', '')
            ->orderBy('s.Vencimiento');

        $lotes = $query->get();

        // Preparar datos para Excel
        $datos = [];
        foreach ($lotes as $lote) {
            $datos[] = [
                'Código' => $lote->CodPro,
                'Producto' => $lote->Nombre,
                'Lote' => $lote->Lote,
                'Vencimiento' => $lote->Vencimiento,
                'Cantidad' => $lote->Cantidad,
                'Costo' => $lote->Costo,
                'Laboratorio' => $lote->Laboratorio,
                'Días para vencer' => $lote->DiasVencimiento,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $datos,
            'message' => 'Datos preparados para exportación'
        ]);
    }

    /**
     * Análisis de rotación de lotes
     */
    public function analisisRotacion()
    {
        // Lotes con alta rotación (menos de 30 días)
        $altaRotacion = DB::table('Productos as p')
            ->join('Saldos as s', 'p.CodPro', '=', 's.CodPro')
            ->leftJoin('Doccab as dc', 's.CodDoc', '=', 'dc.CodDoc')
            ->where('s.Lote', '!=', '')
            ->select([
                'p.CodPro',
                'p.Nombre',
                's.Lote',
                's.Vencimiento',
                's.Cantidad',
                'dc.Fecha as FechaIngreso',
                DB::raw('DATEDIFF(day, dc.Fecha, GETDATE()) as DiasInventario')
            ])
            ->whereRaw('DATEDIFF(day, dc.Fecha, GETDATE()) <= 30')
            ->orderBy('DiasInventario')
            ->get();

        // Lotes de baja rotación (más de 180 días)
        $bajaRotacion = DB::table('Productos as p')
            ->join('Saldos as s', 'p.CodPro', '=', 's.CodPro')
            ->leftJoin('Doccab as dc', 's.CodDoc', '=', 'dc.CodDoc')
            ->where('s.Lote', '!=', '')
            ->select([
                'p.CodPro',
                'p.Nombre',
                's.Lote',
                's.Vencimiento',
                's.Cantidad',
                'dc.Fecha as FechaIngreso',
                DB::raw('DATEDIFF(day, dc.Fecha, GETDATE()) as DiasInventario')
            ])
            ->whereRaw('DATEDIFF(day, dc.Fecha, GETDATE()) > 180')
            ->orderByDesc('DiasInventario')
            ->get();

        return compact('altaRotacion', 'bajaRotacion');
    }

    /**
     * Alertas automáticas de lotes
     */
    private function generarAlertasLotes()
    {
        $alertas = [];

        // Productos próximos a vencer (7 días)
        $proximos = DB::table('Saldos as s')
            ->join('Productos as p', 's.CodPro', '=', 'p.CodPro')
            ->where('s.Lote', '!=', '')
            ->where('s.Vencimiento', '<=', Carbon::now()->addDays(7)->toDateString())
            ->where('s.Vencimiento', '>=', Carbon::now()->toDateString())
            ->select('p.Nombre', 's.Lote', 's.Vencimiento', 's.Cantidad')
            ->get();

        foreach ($proximos as $item) {
            $alertas[] = [
                'tipo' => 'vencimiento',
                'nivel' => 'critico',
                'mensaje' => "Producto {$item->Nombre} (Lote: {$item->Lote}) vence en {$item->Vencimiento}",
                'accion' => 'revisar_urgente'
            ];
        }

        // Productos con stock bajo
        $stockBajo = DB::table('Saldos as s')
            ->join('Productos as p', 's.CodPro', '=', 'p.CodPro')
            ->whereRaw('s.Cantidad <= s.StockMinimo')
            ->where('s.Lote', '!=', '')
            ->select('p.Nombre', 's.Lote', 's.Cantidad', 's.StockMinimo')
            ->get();

        foreach ($stockBajo as $item) {
            $alertas[] = [
                'tipo' => 'stock_bajo',
                'nivel' => 'medio',
                'mensaje' => "Producto {$item->Nombre} tiene stock bajo: {$item->Cantidad} (Min: {$item->StockMinimo})",
                'accion' => 'reabastecer'
            ];
        }

        return $alertas;
    }

    /**
     * Obtener historial de un lote
     */
    private function obtenerHistorialLote($codpro, $lote)
    {
        return DB::table('Trazabilidad as t')
            ->where('t.CodPro', $codpro)
            ->where('t.Lote', $lote)
            ->orderBy('t.Fecha', 'desc')
            ->get();
    }

    /**
     * Calcular rotación de lote
     */
    private function calcularRotacionLote($codpro, $lote)
    {
        $fechaIngreso = DB::table('Doccab as dc')
            ->join('Saldos as s', 'dc.CodDoc', '=', 's.CodDoc')
            ->where('s.CodPro', $codpro)
            ->where('s.Lote', $lote)
            ->value('dc.Fecha');

        if ($fechaIngreso) {
            $dias = Carbon::parse($fechaIngreso)->diffInDays(Carbon::now());
            
            return [
                'fecha_ingreso' => $fechaIngreso,
                'dias_inventario' => $dias,
                'rotacion' => $dias <= 30 ? 'alta' : ($dias <= 90 ? 'media' : 'baja')
            ];
        }

        return null;
    }

    /**
     * Generar código de documento
     */
    private function generarCodDoc($tipo)
    {
        $ultimo = DB::table('Saldos')
            ->where('CodDoc', 'like', $tipo . '%')
            ->max('CodDoc');

        if ($ultimo) {
            $numero = (int) substr($ultimo, 3) + 1;
        } else {
            $numero = 1;
        }

        return $tipo . str_pad($numero, 6, '0', STR_PAD_LEFT);
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
            'Observaciones' => 'Control de lotes'
        ]);
    }
}