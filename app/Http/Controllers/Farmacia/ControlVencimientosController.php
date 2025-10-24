<?php

namespace App\Http\Controllers\Farmacia;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ControlVencimientosController extends Controller
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
        $query = DB::table('Saldos as s')
            ->join('Productos as p', 's.CodPro', '=', 'p.CodPro')
            ->leftJoin('Laboratorios as l', 'p.CodLab', '=', 'l.CodLab')
            ->leftJoin('Doccab as dc', 's.CodDoc', '=', 'dc.CodDoc')
            ->select([
                'p.CodPro',
                'p.Nombre',
                'p.Presentacion',
                's.Lote',
                's.Vencimiento',
                's.Cantidad',
                's.Costo',
                'l.Descripcion as Laboratorio',
                'dc.Fecha as FechaIngreso',
                'dc.Tipo',
                DB::raw('DATEDIFF(day, GETDATE(), s.Vencimiento) as DiasVencimiento'),
                DB::raw('(s.Cantidad * s.Costo) as ValorTotal')
            ])
            ->where('s.Lote', '!=', '')
            ->whereNotNull('s.Vencimiento');

        // Filtros por categoría de vencimiento
        if ($request->filled('categoria')) {
            switch ($request->categoria) {
                case 'vencidos':
                    $query->where('s.Vencimiento', '<', Carbon::now()->toDateString());
                    break;
                case 'proximo_vencer':
                    $query->whereBetween('s.Vencimiento', [
                        Carbon::now()->toDateString(),
                        Carbon::now()->addDays(30)->toDateString()
                    ]);
                    break;
                case 'poco_stock':
                    $query->whereRaw('s.Cantidad <= s.StockMinimo');
                    break;
                case 'critico':
                    $query->where('s.Vencimiento', '<=', Carbon::now()->addDays(7)->toDateString());
                    break;
            }
        }

        // Filtro por días
        if ($request->filled('dias')) {
            $dias = (int) $request->dias;
            $query->whereBetween('s.Vencimiento', [
                Carbon::now()->toDateString(),
                Carbon::now()->addDays($dias)->toDateString()
            ]);
        }

        // Filtros adicionales
        if ($request->filled('codigo')) {
            $query->where('p.CodPro', 'like', '%' . $request->codigo . '%');
        }

        if ($request->filled('laboratorio')) {
            $query->where('l.CodLab', $request->laboratorio);
        }

        if ($request->filled('minimo_valor')) {
            $query->where('s.Costo', '>=', $request->minimo_valor);
        }

        $vencimientos = $query->orderBy('s.Vencimiento')
            ->orderBy('DiasVencimiento')
            ->paginate(25);

        // Estadísticas de vencimiento
        $estadisticas = $this->calcularEstadisticasVencimientos();

        // Alertas de vencimiento
        $alertas = $this->generarAlertasVencimientos();

        // Análisis de riesgos
        $analisisRiesgos = $this->analizarRiesgosVencimiento();

        return compact('vencimientos', 'estadisticas', 'alertas', 'analisisRiesgos');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $productos = DB::table('Productos as p')
            ->leftJoin('Laboratorios as l', 'p.CodLab', '=', 'l.CodLab')
            ->leftJoin('Saldos as s', 'p.CodPro', '=', 's.CodPro')
            ->select('p.CodPro', 'p.Nombre', 'l.Descripcion as Laboratorio')
            ->where('s.Lote', '=', '')
            ->orWhereNull('s.Lote')
            ->distinct()
            ->orderBy('p.Nombre')
            ->get();

        return compact('productos');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'codpro' => 'required|exists:Productos,CodPro',
            'vencimiento' => 'required|date|after:today',
            'cantidad' => 'required|numeric|min:0.01',
            'costo' => 'required|numeric|min:0',
            'lote' => 'required|string|max:50',
        ]);

        try {
            DB::beginTransaction();

            // Verificar si el producto ya tiene este lote
            $loteExistente = DB::table('Saldos')
                ->where('CodPro', $request->codpro)
                ->where('Lote', $request->lote)
                ->first();

            if ($loteExistente) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe un registro para este lote'
                ], 400);
            }

            $codDoc = $this->generarCodDoc('VCT');
            
            // Crear registro de vencimiento
            DB::table('Saldos')->insert([
                'CodDoc' => $codDoc,
                'CodPro' => $request->codpro,
                'Lote' => $request->lote,
                'Vencimiento' => $request->vencimiento,
                'Cantidad' => $request->cantidad,
                'Costo' => $request->costo,
                'StockMinimo' => $request->stock_minimo ?? 10,
                'FechaCreacion' => Carbon::now(),
                'UsuarioCreacion' => Auth::id(),
                'Observaciones' => 'Control de vencimiento inicial'
            ]);

            // Registrar en trazabilidad
            $this->registrarTrazabilidad($request->codpro, $request->lote, 'REGISTRO_VENCIMIENTO', 
                "Vencimiento registrado para {$request->vencimiento}", $request->cantidad);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Control de vencimiento registrado correctamente',
                'data' => []
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar vencimiento: ' . $e->getMessage()
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

        $vencimiento = DB::table('Saldos as s')
            ->leftJoin('Doccab as dc', 's.CodDoc', '=', 'dc.CodDoc')
            ->where('s.CodPro', $codpro)
            ->where('s.Lote', $lote)
            ->select([
                's.*',
                'dc.Tipo',
                'dc.Fecha',
                'dc.Serie',
                'dc.Numero',
                DB::raw('DATEDIFF(day, GETDATE(), s.Vencimiento) as DiasVencimiento'),
                DB::raw('(s.Cantidad * s.Costo) as ValorTotal')
            ])
            ->first();

        if (!$producto || !$vencimiento) {
            return response()->json(['message' => 'Producto o vencimiento no encontrado'], 404);
        }

        // Análisis de riesgo de vencimiento
        $riesgo = $this->analizarRiesgoVencimiento($codpro, $lote);

        // Historial de movimientos
        $historial = $this->obtenerHistorialVencimiento($codpro, $lote);

        // Recomendaciones
        $recomendaciones = $this->generarRecomendaciones($codpro, $lote);

        return compact('producto', 'vencimiento', 'riesgo', 'historial', 'recomendaciones');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($codpro, $lote)
    {
        $vencimiento = DB::table('Saldos as s')
            ->join('Productos as p', 's.CodPro', '=', 'p.CodPro')
            ->where('s.CodPro', $codpro)
            ->where('s.Lote', $lote)
            ->select([
                's.*',
                'p.Nombre',
                'p.CodPro',
                'p.Presentacion'
            ])
            ->first();

        if (!$vencimiento) {
            return response()->json(['message' => 'Vencimiento no encontrado'], 404);
        }

        return compact('vencimiento');
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
        ]);

        try {
            DB::beginTransaction();

            $vencimientoAnterior = DB::table('Saldos')
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
                    'FechaModificacion' => Carbon::now(),
                    'UsuarioModificacion' => Auth::id()
                ]);

            // Registrar cambios en trazabilidad
            $this->registrarTrazabilidad($codpro, $lote, 'MODIFICACION_VENCIMIENTO', 
                "Fecha vencimiento modificada de {$vencimientoAnterior->Vencimiento} a {$request->vencimiento}", 
                $request->cantidad);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Vencimiento actualizado correctamente',
                'data' => []
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar vencimiento: ' . $e->getMessage()
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

            // Verificar si hay movimientos de salida
            $movimientos = DB::table('Doccab as dc')
                ->join('Docdet as dd', 'dc.CodDoc', '=', 'dd.CodDoc')
                ->where('dd.CodPro', $codpro)
                ->where('dd.Lote', $lote)
                ->whereIn('dc.Tipo', ['FAC', 'BOL', 'NOT'])
                ->count();

            if ($movimientos > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar porque tiene movimientos de venta'
                ], 400);
            }

            $vencimiento = DB::table('Saldos')
                ->where('CodPro', $codpro)
                ->where('Lote', $lote)
                ->first();

            DB::table('Saldos')
                ->where('CodPro', $codpro)
                ->where('Lote', $lote)
                ->delete();

            $this->registrarTrazabilidad($codpro, $lote, 'ELIMINACION_VENCIMIENTO', 
                'Control de vencimiento eliminado', $vencimiento->Cantidad ?? 0);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Vencimiento eliminado correctamente',
                'data' => []
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar vencimiento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Marcar producto como vencido
     */
    public function marcarVencido(Request $request, $codpro, $lote)
    {
        $request->validate([
            'motivo' => 'required|in:vencido,retirado,deteriorado,devolucion',
            'observaciones' => 'nullable|string|max:500',
            'cantidad_perdida' => 'required|numeric|min:0'
        ]);

        try {
            DB::beginTransaction();

            $vencimiento = DB::table('Saldos')
                ->where('CodPro', $codpro)
                ->where('Lote', $lote)
                ->first();

            if (!$vencimiento) {
                return response()->json(['message' => 'Vencimiento no encontrado'], 404);
            }

            // Actualizar cantidad
            $nuevaCantidad = $vencimiento->Cantidad - $request->cantidad_perdida;
            
            DB::table('Saldos')
                ->where('CodPro', $codpro)
                ->where('Lote', $lote)
                ->update([
                    'Cantidad' => $nuevaCantidad,
                    'FechaModificacion' => Carbon::now(),
                    'UsuarioModificacion' => Auth::id(),
                    'Observaciones' => "Marcado como {$request->motivo}: {$request->observaciones}"
                ]);

            // Registrar en control de mermas
            DB::table('ControlMermas')->insert([
                'CodPro' => $codpro,
                'Lote' => $lote,
                'CantidadPerdida' => $request->cantidad_perdida,
                'Motivo' => $request->motivo,
                'Observaciones' => $request->observaciones,
                'FechaVencimiento' => $vencimiento->Vencimiento,
                'FechaRegistro' => Carbon::now(),
                'Usuario' => Auth::id()
            ]);

            // Registrar en trazabilidad
            $this->registrarTrazabilidad($codpro, $lote, 'MARCA_VENCIDO', 
                "Marcado como {$request->motivo} - Cantidad perdida: {$request->cantidad_perdida}", 
                $nuevaCantidad);

            // Generar asiento contable si es necesario
            if ($request->cantidad_perdida > 0) {
                $this->generarAsientoMerma($codpro, $request->cantidad_perdida, $vencimiento->Costo, $request->motivo);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Producto marcado como vencido correctamente',
                'data' => []
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al marcar como vencido: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reporte de productos vencidos
     */
    public function reporteVencidos(Request $request)
    {
        $query = DB::table('Saldos as s')
            ->join('Productos as p', 's.CodPro', '=', 'p.CodPro')
            ->leftJoin('Laboratorios as l', 'p.CodLab', '=', 'l.CodLab')
            ->select([
                'p.CodPro',
                'p.Nombre',
                'p.Presentacion',
                's.Lote',
                's.Vencimiento',
                's.Cantidad',
                's.Costo',
                'l.Descripcion as Laboratorio',
                DB::raw('DATEDIFF(day, GETDATE(), s.Vencimiento) as DiasVencimiento'),
                DB::raw('(s.Cantidad * s.Costo) as ValorTotal')
            ])
            ->where('s.Vencimiento', '<', Carbon::now()->toDateString())
            ->orderBy('s.Vencimiento');

        $vencidos = $query->get();

        $totalPerdido = $vencidos->sum('ValorTotal');

        return compact('vencidos', 'totalPerdido');
    }

    /**
     * Alertas automáticas de vencimiento
     */
    public function alertasVencimientos()
    {
        $alertas = [];

        // Productos vencidos (más de 1 día)
        $vencidos = DB::table('Saldos as s')
            ->join('Productos as p', 's.CodPro', '=', 'p.CodPro')
            ->where('s.Vencimiento', '<', Carbon::now()->toDateString())
            ->where('s.Cantidad', '>', 0)
            ->select('p.Nombre', 's.Lote', 's.Vencimiento', 's.Cantidad')
            ->get();

        foreach ($vencidos as $item) {
            $dias = Carbon::parse($item->Vencimiento)->diffInDays(Carbon::now());
            $alertas[] = [
                'tipo' => 'vencido',
                'nivel' => 'critico',
                'producto' => $item->Nombre,
                'lote' => $item->Lote,
                'fecha_vencimiento' => $item->Vencimiento,
                'cantidad' => $item->Cantidad,
                'dias_vencido' => $dias,
                'mensaje' => "Producto {$item->Nombre} vencido hace {$dias} días",
                'accion' => 'retirar_inmediatamente'
            ];
        }

        // Próximos a vencer (7 días)
        $proximos = DB::table('Saldos as s')
            ->join('Productos as p', 's.CodPro', '=', 'p.CodPro')
            ->whereBetween('s.Vencimiento', [
                Carbon::now()->toDateString(),
                Carbon::now()->addDays(7)->toDateString()
            ])
            ->where('s.Cantidad', '>', 0)
            ->select('p.Nombre', 's.Lote', 's.Vencimiento', 's.Cantidad')
            ->get();

        foreach ($proximos as $item) {
            $dias = Carbon::parse($item->Vencimiento)->diffInDays(Carbon::now());
            $alertas[] = [
                'tipo' => 'proximo_vencer',
                'nivel' => 'alto',
                'producto' => $item->Nombre,
                'lote' => $item->Lote,
                'fecha_vencimiento' => $item->Vencimiento,
                'cantidad' => $item->Cantidad,
                'dias_restantes' => $dias,
                'mensaje' => "Producto {$item->Nombre} vence en {$dias} días",
                'accion' => 'priorizar_venta'
            ];
        }

        return $alertas;
    }

    /**
     * Calcular estadísticas de vencimientos
     */
    private function calcularEstadisticasVencimientos()
    {
        $estadisticas = [];

        // Total de productos en control de vencimiento
        $estadisticas['total_productos'] = DB::table('Saldos')
            ->where('Lote', '!=', '')
            ->whereNotNull('Vencimiento')
            ->count();

        // Productos vencidos
        $estadisticas['productos_vencidos'] = DB::table('Saldos')
            ->where('Lote', '!=', '')
            ->where('Vencimiento', '<', Carbon::now()->toDateString())
            ->count();

        // Productos próximos a vencer (30 días)
        $estadisticas['proximos_vencer'] = DB::table('Saldos')
            ->where('Lote', '!=', '')
            ->whereBetween('Vencimiento', [
                Carbon::now()->toDateString(),
                Carbon::now()->addDays(30)->toDateString()
            ])
            ->count();

        // Valor total en riesgo
        $valorRiesgo = DB::table('Saldos as s')
            ->where('s.Lote', '!=', '')
            ->where('s.Vencimiento', '<=', Carbon::now()->addDays(30)->toDateString())
            ->sum(DB::raw('s.Cantidad * s.Costo'));

        $estadisticas['valor_riesgo'] = $valorRiesgo;

        return $estadisticas;
    }

    /**
     * Generar alertas de vencimiento
     */
    private function generarAlertasVencimientos()
    {
        $alertas = [];

        // Alerta crítica: productos vencidos
        $criticos = DB::table('Saldos as s')
            ->join('Productos as p', 's.CodPro', '=', 'p.CodPro')
            ->where('s.Vencimiento', '<', Carbon::now()->toDateString())
            ->where('s.Cantidad', '>', 0)
            ->select('p.Nombre', 's.Lote', 's.Cantidad', 's.Vencimiento')
            ->get();

        foreach ($criticos as $item) {
            $alertas[] = [
                'nivel' => 'critico',
                'producto' => $item->Nombre,
                'lote' => $item->Lote,
                'cantidad' => $item->Cantidad,
                'vencimiento' => $item->Vencimiento,
                'mensaje' => "URGENTE: {$item->Nombre} (Lote {$item->Lote}) está vencido",
                'accion' => 'retirar_inmediatamente'
            ];
        }

        return $alertas;
    }

    /**
     * Analizar riesgos de vencimiento
     */
    private function analizarRiesgosVencimiento()
    {
        $riesgos = [];

        // Productos con alta rotación pero próximos a vencer
        $altaRotacionRiesgo = DB::table('Productos as p')
            ->join('Saldos as s', 'p.CodPro', '=', 's.CodPro')
            ->leftJoin('Doccab as dc', 's.CodDoc', '=', 'dc.CodDoc')
            ->where('s.Lote', '!=', '')
            ->whereBetween('s.Vencimiento', [
                Carbon::now()->toDateString(),
                Carbon::now()->addDays(15)->toDateString()
            ])
            ->select([
                'p.CodPro',
                'p.Nombre',
                's.Lote',
                's.Vencimiento',
                's.Cantidad',
                DB::raw('AVG(dd.Cantidad) as PromedioVentasDiarias')
            ])
            ->groupBy('p.CodPro', 'p.Nombre', 's.Lote', 's.Vencimiento', 's.Cantidad')
            ->get();

        foreach ($altaRotacionRiesgo as $item) {
            if ($item->PromedioVentasDiarias > 10) {
                $riesgos[] = [
                    'tipo' => 'rotacion_alta',
                    'producto' => $item->Nombre,
                    'lote' => $item->Lote,
                    'dias_restantes' => Carbon::parse($item->Vencimiento)->diffInDays(Carbon::now()),
                    'ventas_diarias' => $item->PromedioVentasDiarias,
                    'recomendacion' => 'Promocionar venta urgente'
                ];
            }
        }

        return $riesgos;
    }

    /**
     * Análisis individual de riesgo
     */
    private function analizarRiesgoVencimiento($codpro, $lote)
    {
        $vencimiento = DB::table('Saldos as s')
            ->where('s.CodPro', $codpro)
            ->where('s.Lote', $lote)
            ->first();

        if (!$vencimiento) return null;

        $diasRestantes = Carbon::parse($vencimiento->Vencimiento)->diffInDays(Carbon::now());
        
        // Calcular riesgo basado en días y cantidad
        $nivelRiesgo = 'bajo';
        $puntuacionRiesgo = 0;

        if ($diasRestantes <= 0) {
            $nivelRiesgo = 'critico';
            $puntuacionRiesgo = 100;
        } elseif ($diasRestantes <= 7) {
            $nivelRiesgo = 'alto';
            $puntuacionRiesgo = 80;
        } elseif ($diasRestantes <= 30) {
            $nivelRiesgo = 'medio';
            $puntuacionRiesgo = 50;
        }

        // Ajuste por cantidad
        if ($vencimiento->Cantidad > 100) {
            $puntuacionRiesgo += 20;
        }

        return [
            'nivel' => $nivelRiesgo,
            'puntuacion' => min($puntuacionRiesgo, 100),
            'dias_restantes' => $diasRestantes,
            'cantidad_stock' => $vencimiento->Cantidad,
            'acciones_sugeridas' => $this->obtenerAccionesSugeridas($nivelRiesgo, $diasRestantes)
        ];
    }

    /**
     * Obtener acciones sugeridas
     */
    private function obtenerAccionesSugeridas($nivelRiesgo, $diasRestantes)
    {
        $acciones = [];

        switch ($nivelRiesgo) {
            case 'critico':
                $acciones = ['Retirar de venta inmediatamente', 'Contactar laboratorio', 'Gestionar devolución'];
                break;
            case 'alto':
                $acciones = ['Promocionar venta urgente', 'Revisar precios', 'Contactar proveedores'];
                break;
            case 'medio':
                $acciones = ['Planificar promociones', 'Revisar rotación', 'Monitorear venta'];
                break;
            default:
                $acciones = ['Monitoreo regular', 'Mantener control'];
        }

        if ($diasRestantes <= 30 && $diasRestantes > 0) {
            $acciones[] = 'Preparar plan de acción preventivo';
        }

        return $acciones;
    }

    /**
     * Obtener historial de vencimiento
     */
    private function obtenerHistorialVencimiento($codpro, $lote)
    {
        return DB::table('Trazabilidad as t')
            ->where('t.CodPro', $codpro)
            ->where('t.Lote', $lote)
            ->where('t.Accion', 'like', '%VENCIMIENTO%')
            ->orderBy('t.Fecha', 'desc')
            ->get();
    }

    /**
     * Generar recomendaciones
     */
    private function generarRecomendaciones($codpro, $lote)
    {
        $vencimiento = DB::table('Saldos as s')
            ->where('s.CodPro', $codpro)
            ->where('s.Lote', $lote)
            ->first();

        if (!$vencimiento) return [];

        $diasRestantes = Carbon::parse($vencimiento->Vencimiento)->diffInDays(Carbon::now());
        $recomendaciones = [];

        // Recomendaciones basadas en días
        if ($diasRestantes <= 7) {
            $recomendaciones[] = [
                'tipo' => 'urgente',
                'descripcion' => 'Implementar descuentos agresivos para liquidar stock',
                'plazo' => '3 días',
                'impacto' => 'alto'
            ];
        } elseif ($diasRestantes <= 30) {
            $recomendaciones[] = [
                'tipo' => 'preventivo',
                'descripcion' => 'Contactar laboratorio para gestionar extensión de vida útil',
                'plazo' => '1 semana',
                'impacto' => 'medio'
            ];
        }

        // Recomendaciones basadas en cantidad
        if ($vencimiento->Cantidad > 50) {
            $recomendaciones[] = [
                'tipo' => 'estrategico',
                'descripcion' => 'Distribuir stock a otras sucursales',
                'plazo' => '5 días',
                'impacto' => 'alto'
            ];
        }

        return $recomendaciones;
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
     * Generar asiento contable por merma
     */
    private function generarAsientoMerma($codpro, $cantidad, $costo, $motivo)
    {
        $producto = DB::table('Productos')
            ->where('CodPro', $codpro)
            ->first();

        $cuentaMerma = '4395'; // Cuenta de mermas farmacéuticas
        $cuentaInventario = '2011'; // Cuenta de inventario

        // Aquí se registraría el asiento contable en la tabla de asientos_diario
        // para cumplir con los requerimientos contables
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
            'Observaciones' => 'Control de vencimientos'
        ]);
    }
}