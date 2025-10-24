<?php

namespace App\Http\Controllers\Farmacia;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ControlTemperaturaController extends Controller
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
        $query = DB::table('ControlTemperatura as ct')
            ->leftJoin('EquiposRefrigeracion as er', 'ct.CodEquipo', '=', 'er.CodEquipo')
            ->leftJoin('Ubicaciones as u', 'er.CodUbicacion', '=', 'u.CodUbicacion')
            ->select([
                'ct.*',
                'er.Nombre as Equipo',
                'er.TipoEquipo',
                'er.Marca',
                'er.Modelo',
                'u.Descripcion as Ubicacion',
                'u.TemperaturaMinima',
                'u.TemperaturaMaxima'
            ]);

        // Filtros
        if ($request->filled('fecha_desde')) {
            $query->where('ct.FechaRegistro', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->where('ct.FechaRegistro', '<=', $request->fecha_hasta . ' 23:59:59');
        }

        if ($request->filled('equipo')) {
            $query->where('ct.CodEquipo', $request->equipo);
        }

        if ($request->filled('ubicacion')) {
            $query->where('er.CodUbicacion', $request->ubicacion);
        }

        if ($request->filled('tipo_control')) {
            $query->where('ct.TipoControl', $request->tipo_control);
        }

        if ($request->filled('estado')) {
            if ($request->estado == 'normal') {
                $query->where('ct.Estado', 'normal');
            } elseif ($request->estado == 'alerta') {
                $query->whereIn('ct.Estado', ['alerta', 'critico']);
            }
        }

        $controles = $query->orderBy('ct.FechaRegistro', 'desc')
            ->paginate(25);

        // Estadísticas de control de temperatura
        $estadisticas = $this->calcularEstadisticasTemperatura($request);

        // Alertas activas
        $alertas = $this->obtenerAlertasTemperatura();

        // Tendencias de temperatura
        $tendencias = $this->analizarTendenciasTemperatura($request);

        return compact('controles', 'estadisticas', 'alertas', 'tendencias');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $equipos = DB::table('EquiposRefrigeracion as er')
            ->leftJoin('Ubicaciones as u', 'er.CodUbicacion', '=', 'u.CodUbicacion')
            ->select('er.*', 'u.Descripcion as Ubicacion')
            ->where('er.Estado', 'activo')
            ->orderBy('er.Nombre')
            ->get();

        $tiposControl = [
            'manual' => 'Registro Manual',
            'automatico' => 'Registro Automático',
            'verificacion' => 'Verificación Periódica',
            'mantenimiento' => 'Control de Mantenimiento'
        ];

        $ubicaciones = DB::table('Ubicaciones')
            ->where('Tipo', 'farma')
            ->orderBy('Descripcion')
            ->get();

        return compact('equipos', 'tiposControl', 'ubicaciones');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'cod_equipo' => 'required|exists:EquiposRefrigeracion,CodEquipo',
            'temperatura' => 'required|numeric|between:-20,50',
            'humedad' => 'nullable|numeric|between:0,100',
            'tipo_control' => 'required|in:manual,automatico,verificacion,mantenimiento',
            'observaciones' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            // Verificar equipo activo
            $equipo = DB::table('EquiposRefrigeracion')
                ->where('CodEquipo', $request->cod_equipo)
                ->where('Estado', 'activo')
                ->first();

            if (!$equipo) {
                return response()->json([
                    'success' => false,
                    'message' => 'El equipo no está activo o no existe'
                ], 400);
            }

            // Determinar estado basado en límites
            $estado = $this->determinarEstadoTemperatura($request->temperatura, $equipo->CodEquipo);

            // Registrar control de temperatura
            DB::table('ControlTemperatura')->insert([
                'CodEquipo' => $request->cod_equipo,
                'Temperatura' => $request->temperatura,
                'Humedad' => $request->humedad,
                'TipoControl' => $request->tipo_control,
                'Estado' => $estado['estado'],
                'Alerta' => $estado['alerta'],
                'Observaciones' => $request->observaciones,
                'FechaRegistro' => Carbon::now(),
                'Usuario' => Auth::id(),
                'ObservacionesTemperatura' => $estado['observaciones']
            ]);

            // Verificar si necesita generar alerta
            if ($estado['alerta']) {
                $this->generarAlertaTemperatura($request->cod_equipo, $request->temperatura, $estado);
            }

            // Actualizar última lectura del equipo
            DB::table('EquiposRefrigeracion')
                ->where('CodEquipo', $request->cod_equipo)
                ->update([
                    'UltimaTemperatura' => $request->temperatura,
                    'FechaUltimaLectura' => Carbon::now()
                ]);

            // Registrar en log de trazabilidad
            $this->registrarTrazabilidadTemperatura($request->cod_equipo, $estado['estado'], 
                "Control de temperatura: {$request->temperatura}°C", $request->temperatura);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Control de temperatura registrado correctamente',
                'data' => $estado
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar temperatura: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $control = DB::table('ControlTemperatura as ct')
            ->leftJoin('EquiposRefrigeracion as er', 'ct.CodEquipo', '=', 'er.CodEquipo')
            ->leftJoin('Ubicaciones as u', 'er.CodUbicacion', '=', 'u.CodUbicacion')
            ->leftJoin('users as us', 'ct.Usuario', '=', 'us.id')
            ->select([
                'ct.*',
                'er.Nombre as Equipo',
                'er.TipoEquipo',
                'er.Marca',
                'er.Modelo',
                'er.Estado as EstadoEquipo',
                'u.Descripcion as Ubicacion',
                'u.TemperaturaMinima',
                'u.TemperaturaMaxima',
                'us.name as UsuarioRegistro',
                DB::raw('DATEDIFF(minute, ct.FechaRegistro, GETDATE()) as MinutosTranscurridos')
            ])
            ->where('ct.Id', $id)
            ->first();

        if (!$control) {
            return response()->json(['message' => 'Control de temperatura no encontrado'], 404);
        }

        // Historial reciente del equipo
        $historialEquipo = $this->obtenerHistorialEquipo($control->CodEquipo, 24);

        // Análisis de estabilidad
        $analisisEstabilidad = $this->analizarEstabilidad($control->CodEquipo);

        // Productos afectados (si hay alerta)
        $productosAfectados = [];
        if ($control->Alerta) {
            $productosAfectados = $this->obtenerProductosAfectados($control->CodEquipo);
        }

        return compact('control', 'historialEquipo', 'analisisEstabilidad', 'productosAfectados');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $control = DB::table('ControlTemperatura as ct')
            ->join('EquiposRefrigeracion as er', 'ct.CodEquipo', '=', 'er.CodEquipo')
            ->where('ct.Id', $id)
            ->select('ct.*', 'er.Nombre as Equipo')
            ->first();

        if (!$control) {
            return response()->json(['message' => 'Control de temperatura no encontrado'], 404);
        }

        $tiposControl = [
            'manual' => 'Registro Manual',
            'automatico' => 'Registro Automático',
            'verificacion' => 'Verificación Periódica',
            'mantenimiento' => 'Control de Mantenimiento'
        ];

        return compact('control', 'tiposControl');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'temperatura' => 'required|numeric|between:-20,50',
            'humedad' => 'nullable|numeric|between:0,100',
            'tipo_control' => 'required|in:manual,automatico,verificacion,mantenimiento',
            'observaciones' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            $controlAnterior = DB::table('ControlTemperatura')
                ->where('Id', $id)
                ->first();

            if (!$controlAnterior) {
                return response()->json(['message' => 'Control de temperatura no encontrado'], 404);
            }

            // Verificar equipo activo
            $equipo = DB::table('EquiposRefrigeracion')
                ->where('CodEquipo', $controlAnterior->CodEquipo)
                ->first();

            if (!$equipo || $equipo->Estado != 'activo') {
                return response()->json([
                    'success' => false,
                    'message' => 'El equipo no está activo'
                ], 400);
            }

            // Recalcular estado
            $estado = $this->determinarEstadoTemperatura($request->temperatura, $equipo->CodEquipo);

            // Actualizar control
            DB::table('ControlTemperatura')
                ->where('Id', $id)
                ->update([
                    'Temperatura' => $request->temperatura,
                    'Humedad' => $request->humedad,
                    'TipoControl' => $request->tipo_control,
                    'Estado' => $estado['estado'],
                    'Alerta' => $estado['alerta'],
                    'Observaciones' => $request->observaciones,
                    'ObservacionesTemperatura' => $estado['observaciones'],
                    'FechaModificacion' => Carbon::now(),
                    'UsuarioModificacion' => Auth::id()
                ]);

            // Registrar en trazabilidad
            $this->registrarTrazabilidadTemperatura($controlAnterior->CodEquipo, 
                $estado['estado'], 
                "Temperatura modificada de {$controlAnterior->Temperatura}°C a {$request->temperatura}°C", 
                $request->temperatura);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Control de temperatura actualizado correctamente',
                'data' => $estado
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar temperatura: ' . $e->getMessage()
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

            $control = DB::table('ControlTemperatura')
                ->where('Id', $id)
                ->first();

            if (!$control) {
                return response()->json(['message' => 'Control de temperatura no encontrado'], 404);
            }

            // No permitir eliminar si tiene alertas críticas
            if ($control->Estado == 'critico') {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar un control con estado crítico'
                ], 400);
            }

            DB::table('ControlTemperatura')
                ->where('Id', $id)
                ->delete();

            // Registrar en trazabilidad
            $this->registrarTrazabilidadTemperatura($control->CodEquipo, 
                'eliminado', 
                "Control de temperatura eliminado - Temp: {$control->Temperatura}°C", 
                $control->Temperatura);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Control de temperatura eliminado correctamente',
                'data' => []
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar control: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Dashboard de control de temperatura
     */
    public function dashboard()
    {
        $hoy = Carbon::now()->toDateString();

        // Equipos activos
        $equiposActivos = DB::table('EquiposRefrigeracion')
            ->where('Estado', 'activo')
            ->count();

        // Lecturas del día
        $lecturasHoy = DB::table('ControlTemperatura')
            ->where('FechaRegistro', '>=', $hoy)
            ->count();

        // Alertas activas
        $alertasActivas = DB::table('ControlTemperatura')
            ->where('Alerta', true)
            ->where('FechaRegistro', '>=', Carbon::now()->subDay()->toDateString())
            ->count();

        // Equipos con problemas
        $equiposConProblemas = DB::table('ControlTemperatura as ct')
            ->join('EquiposRefrigeracion as er', 'ct.CodEquipo', '=', 'er.CodEquipo')
            ->where('ct.Alerta', true)
            ->where('ct.FechaRegistro', '>=', Carbon::now()->subDay()->toDateString())
            ->distinct('ct.CodEquipo')
            ->count('ct.CodEquipo');

        // Promedio de temperatura del día
        $promedioTemperatura = DB::table('ControlTemperatura')
            ->where('FechaRegistro', '>=', $hoy)
            ->avg('Temperatura');

        // Tendencia de temperatura (últimas 24 horas)
        $tendencia24h = DB::table('ControlTemperatura')
            ->select([
                DB::raw('DATEPART(hour, FechaRegistro) as Hora'),
                DB::raw('AVG(Temperatura) as TemperaturaPromedio')
            ])
            ->where('FechaRegistro', '>=', Carbon::now()->subDay()->toDateString())
            ->groupBy(DB::raw('DATEPART(hour, FechaRegistro)'))
            ->orderBy('Hora')
            ->get();

        // Productos en riesgo por temperatura
        $productosEnRiesgo = $this->obtenerProductosEnRiesgo();

        return compact('equiposActivos', 'lecturasHoy', 'alertasActivas', 
            'equiposConProblemas', 'promedioTemperatura', 'tendencia24h', 'productosEnRiesgo');
    }

    /**
     * Reporte de temperatura por equipo
     */
    public function reporteEquipo(Request $request)
    {
        $request->validate([
            'cod_equipo' => 'required|exists:EquiposRefrigeracion,CodEquipo',
            'fecha_desde' => 'required|date',
            'fecha_hasta' => 'required|date|after:fecha_desde',
        ]);

        // Información del equipo
        $equipo = DB::table('EquiposRefrigeracion as er')
            ->leftJoin('Ubicaciones as u', 'er.CodUbicacion', '=', 'u.CodUbicacion')
            ->select('er.*', 'u.Descripcion as Ubicacion')
            ->where('er.CodEquipo', $request->cod_equipo)
            ->first();

        // Lecturas en el período
        $lecturas = DB::table('ControlTemperatura')
            ->where('CodEquipo', $request->cod_equipo)
            ->whereBetween('FechaRegistro', [$request->fecha_desde, $request->fecha_hasta . ' 23:59:59'])
            ->orderBy('FechaRegistro')
            ->get();

        // Estadísticas
        $estadisticas = [
            'lecturas_totales' => $lecturas->count(),
            'temperatura_promedio' => $lecturas->avg('Temperatura'),
            'temperatura_minima' => $lecturas->min('Temperatura'),
            'temperatura_maxima' => $lecturas->max('Temperatura'),
            'alertas_totales' => $lecturas->where('Alerta', true)->count(),
            'porcentaje_cumplimiento' => $lecturas->count() > 0 ? 
                (($lecturas->where('Estado', 'normal')->count() / $lecturas->count()) * 100) : 0
        ];

        // Análisis de cumplimiento por rango
        $cumplimientoHorarios = DB::table('ControlTemperatura')
            ->select([
                DB::raw('DATEPART(hour, FechaRegistro) as Hora'),
                DB::raw('COUNT(*) as Lecturas'),
                DB::raw('SUM(CASE WHEN Estado = "normal" THEN 1 ELSE 0 END) as Normales'),
                DB::raw('(SUM(CASE WHEN Estado = "normal" THEN 1 ELSE 0 END) * 100.0 / COUNT(*)) as Porcentaje')
            ])
            ->where('CodEquipo', $request->cod_equipo)
            ->whereBetween('FechaRegistro', [$request->fecha_desde, $request->fecha_hasta . ' 23:59:59'])
            ->groupBy(DB::raw('DATEPART(hour, FechaRegistro)'))
            ->orderBy('Hora')
            ->get();

        return compact('equipo', 'lecturas', 'estadisticas', 'cumplimientoHorarios');
    }

    /**
     * Calcular estadísticas de temperatura
     */
    private function calcularEstadisticasTemperatura($request)
    {
        $query = DB::table('ControlTemperatura as ct');

        if ($request->filled('fecha_desde')) {
            $query->where('ct.FechaRegistro', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->where('ct.FechaRegistro', '<=', $request->fecha_hasta . ' 23:59:59');
        }

        return [
            'total_controles' => $query->count(),
            'lecturas_normales' => $query->where('ct.Estado', 'normal')->count(),
            'alertas' => $query->where('ct.Alerta', true)->count(),
            'temperatura_promedio' => $query->avg('ct.Temperatura'),
            'equipos_con_problemas' => $query->where('ct.Alerta', true)
                ->distinct('ct.CodEquipo')
                ->count('ct.CodEquipo')
        ];
    }

    /**
     * Obtener alertas de temperatura
     */
    private function obtenerAlertasTemperatura()
    {
        $alertas = [];

        // Alertas de las últimas 24 horas
        $alertasRecientes = DB::table('ControlTemperatura as ct')
            ->join('EquiposRefrigeracion as er', 'ct.CodEquipo', '=', 'er.CodEquipo')
            ->where('ct.Alerta', true)
            ->where('ct.FechaRegistro', '>=', Carbon::now()->subDay()->toDateString())
            ->select([
                'ct.*',
                'er.Nombre as Equipo',
                DB::raw('DATEDIFF(minute, ct.FechaRegistro, GETDATE()) as MinutosTranscurridos')
            ])
            ->orderBy('ct.FechaRegistro', 'desc')
            ->get();

        foreach ($alertasRecientes as $alerta) {
            $nivel = $alerta->Estado == 'critico' ? 'critico' : 'alto';
            $mensaje = "{$alerta->Equipo}: Temperatura {$alerta->Temperatura}°C fuera de rango";
            
            $alertas[] = [
                'nivel' => $nivel,
                'equipo' => $alerta->Equipo,
                'temperatura' => $alerta->Temperatura,
                'fecha' => $alerta->FechaRegistro,
                'minutos_transcurridos' => $alerta->MinutosTranscurridos,
                'mensaje' => $mensaje,
                'accion_sugerida' => $alerta->Estado == 'critico' ? 'revisar_inmediatamente' : 'verificar_equipo'
            ];
        }

        return $alertas;
    }

    /**
     * Analizar tendencias de temperatura
     */
    private function analizarTendenciasTemperatura($request)
    {
        // Tendencia por día (últimos 7 días)
        $tendenciaSemanal = DB::table('ControlTemperatura')
            ->select([
                DB::raw('CAST(FechaRegistro as DATE) as Fecha'),
                DB::raw('AVG(Temperatura) as TemperaturaPromedio'),
                DB::raw('MIN(Temperatura) as TemperaturaMinima'),
                DB::raw('MAX(Temperatura) as TemperaturaMaxima'),
                DB::raw('COUNT(CASE WHEN Alerta = 1 THEN 1 END) as Alertas')
            ])
            ->where('FechaRegistro', '>=', Carbon::now()->subDays(7)->toDateString())
            ->groupBy(DB::raw('CAST(FechaRegistro as DATE)'))
            ->orderBy('Fecha')
            ->get();

        return $tendenciaSemanal;
    }

    /**
     * Determinar estado de temperatura
     */
    private function determinarEstadoTemperatura($temperatura, $codEquipo)
    {
        // Obtener límites del equipo
        $equipo = DB::table('EquiposRefrigeracion as er')
            ->leftJoin('Ubicaciones as u', 'er.CodUbicacion', '=', 'u.CodUbicacion')
            ->where('er.CodEquipo', $codEquipo)
            ->select('er.*', 'u.TemperaturaMinima', 'u.TemperaturaMaxima')
            ->first();

        $tempMin = $equipo->TemperaturaMinima ?? 2;
        $tempMax = $equipo->TemperaturaMaxima ?? 8;

        // Determinar estado
        if ($temperatura < ($tempMin - 2) || $temperatura > ($tempMax + 2)) {
            return [
                'estado' => 'critico',
                'alerta' => true,
                'observaciones' => 'Temperatura crítica - requiere acción inmediata'
            ];
        } elseif ($temperatura < $tempMin || $temperatura > $tempMax) {
            return [
                'estado' => 'alerta',
                'alerta' => true,
                'observaciones' => 'Temperatura fuera del rango óptimo'
            ];
        } else {
            return [
                'estado' => 'normal',
                'alerta' => false,
                'observaciones' => 'Temperatura dentro del rango normal'
            ];
        }
    }

    /**
     * Generar alerta de temperatura
     */
    private function generarAlertaTemperatura($codEquipo, $temperatura, $estado)
    {
        $equipo = DB::table('EquiposRefrigeracion')
            ->where('CodEquipo', $codEquipo)
            ->first();

        // Aquí se podría integrar con un sistema de notificaciones
        // Por ejemplo, enviar email, SMS, o push notification
        
        // Registrar alerta en tabla de alertas
        DB::table('AlertasFarmacia')->insert([
            'Tipo' => 'temperatura',
            'Nivel' => $estado['estado'],
            'Codigo' => $codEquipo,
            'Descripcion' => "Equipo {$equipo->Nombre}: {$estado['observaciones']} - Temp: {$temperatura}°C",
            'Fecha' => Carbon::now(),
            'Estado' => 'pendiente',
            'Usuario' => Auth::id()
        ]);
    }

    /**
     * Obtener historial del equipo
     */
    private function obtenerHistorialEquipo($codEquipo, $horas = 24)
    {
        return DB::table('ControlTemperatura')
            ->where('CodEquipo', $codEquipo)
            ->where('FechaRegistro', '>=', Carbon::now()->subHours($horas)->toDateTimeString())
            ->orderBy('FechaRegistro', 'desc')
            ->limit(20)
            ->get();
    }

    /**
     * Analizar estabilidad del equipo
     */
    private function analizarEstabilidad($codEquipo)
    {
        $lecturas = $this->obtenerHistorialEquipo($codEquipo, 24);
        
        if ($lecturas->count() < 5) {
            return ['estabilidad' => 'insuficiente_datos', 'score' => 0];
        }

        $temperaturas = $lecturas->pluck('Temperatura');
        $promedio = $temperaturas->avg();
        $desviacion = sqrt($temperaturas->map(function($temp) use ($promedio) {
            return pow($temp - $promedio, 2);
        })->avg());

        // Calcular score de estabilidad (0-100)
        $score = max(0, 100 - ($desviacion * 10));
        
        $estabilidad = $score > 80 ? 'excelente' : 
                      ($score > 60 ? 'buena' : 
                      ($score > 40 ? 'regular' : 'inestable'));

        return [
            'estabilidad' => $estabilidad,
            'score' => round($score, 1),
            'desviacion' => round($desviacion, 2),
            'promedio' => round($promedio, 2)
        ];
    }

    /**
     * Obtener productos afectados por temperatura
     */
    private function obtenerProductosAfectados($codEquipo)
    {
        // Productos en equipos con temperatura fuera de rango
        return DB::table('Saldos as s')
            ->join('Productos as p', 's.CodPro', '=', 'p.CodPro')
            ->leftJoin('EquiposRefrigeracion as er', function($join) {
                $join->on('er.CodUbicacion', '=', 's.CodUbicacion')
                     ->orWhere('er.CodUbicacion', '=', 'p.CodUbicacion');
            })
            ->where('er.CodEquipo', $codEquipo)
            ->where('s.Cantidad', '>', 0)
            ->select('p.Nombre', 'p.CodPro', 's.Lote', 's.Cantidad', 's.Vencimiento')
            ->get();
    }

    /**
     * Obtener productos en riesgo
     */
    private function obtenerProductosEnRiesgo()
    {
        // Productos que requieren cadena de frío en equipos con problemas
        return DB::table('Productos as p')
            ->join('Saldos as s', 'p.CodPro', '=', 's.CodPro')
            ->leftJoin('EquiposRefrigeracion as er', function($join) {
                $join->on('er.CodUbicacion', '=', 's.CodUbicacion')
                     ->orWhere('er.CodUbicacion', '=', 'p.CodUbicacion');
            })
            ->leftJoin('ControlTemperatura as ct', 'er.CodEquipo', '=', 'ct.CodEquipo')
            ->where('p.CadenaFria', true)
            ->where('s.Cantidad', '>', 0)
            ->where('ct.Alerta', true)
            ->where('ct.FechaRegistro', '>=', Carbon::now()->subHours(2)->toDateTimeString())
            ->select('p.Nombre', 'p.CodPro', 's.Lote', 's.Cantidad', 'ct.Temperatura')
            ->distinct()
            ->get();
    }

    /**
     * Registrar en trazabilidad de temperatura
     */
    private function registrarTrazabilidadTemperatura($codEquipo, $estado, $descripcion, $temperatura)
    {
        DB::table('TrazabilidadTemperatura')->insert([
            'CodEquipo' => $codEquipo,
            'Estado' => $estado,
            'Descripcion' => $descripcion,
            'Temperatura' => $temperatura,
            'Fecha' => Carbon::now(),
            'Usuario' => Auth::id()
        ]);
    }
}