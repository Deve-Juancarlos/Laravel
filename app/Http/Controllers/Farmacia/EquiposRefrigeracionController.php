<?php

namespace App\Http\Controllers\Farmacia;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class EquiposRefrigeracionController extends Controller
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
        $query = DB::table('EquiposRefrigeracion as er')
            ->leftJoin('Ubicaciones as u', 'er.CodUbicacion', '=', 'u.CodUbicacion')
            ->leftJoin('ControlTemperatura as ct', 'er.CodEquipo', '=', 'ct.CodEquipo')
            ->leftJoin('MantenimientoEquipos as me', 'er.CodEquipo', '=', 'me.CodEquipo')
            ->select([
                'er.*',
                'u.Descripcion as Ubicacion',
                'u.Tipo as TipoUbicacion',
                'ct.Temperatura as UltimaTemperatura',
                'ct.FechaRegistro as FechaUltimaLectura',
                'ct.Estado as EstadoTemperatura',
                DB::raw('MAX(me.FechaMantenimiento) as UltimoMantenimiento'),
                DB::raw('COUNT(ct.Id) as LecturasMes'),
                DB::raw('AVG(ct.Temperatura) as TemperaturaPromedioMes'),
                DB::raw('(SELECT COUNT(*) FROM ControlTemperatura WHERE CodEquipo = er.CodEquipo AND Alerta = 1 AND FechaRegistro >= DATEADD(day, -30, GETDATE())) as AlertasMes')
            ])
            ->groupBy([
                'er.CodEquipo', 'er.Nombre', 'er.TipoEquipo', 'er.Marca', 'er.Modelo', 
                'er.Serie', 'er.CodUbicacion', 'er.CapacidadLitros', 'er.TemperaturaMinima', 
                'er.TemperaturaMaxima', 'er.Estado', 'er.FechaInstalacion', 'er.Costo', 
                'er.NumeroGarantia', 'er.FechaGarantia', 'er.Observaciones', 
                'er.FechaCreacion', 'er.UsuarioCreacion', 'er.FechaModificacion', 
                'er.UsuarioModificacion', 'er.UltimaTemperatura', 'er.FechaUltimaLectura', 
                'u.Descripcion', 'u.Tipo', 'ct.Temperatura', 'ct.FechaRegistro', 'ct.Estado'
            ]);

        // Filtros
        if ($request->filled('estado')) {
            $query->where('er.Estado', $request->estado);
        }

        if ($request->filled('tipo_equipo')) {
            $query->where('er.TipoEquipo', $request->tipo_equipo);
        }

        if ($request->filled('ubicacion')) {
            $query->where('er.CodUbicacion', $request->ubicacion);
        }

        if ($request->filled('marca')) {
            $query->where('er.Marca', 'like', '%' . $request->marca . '%');
        }

        if ($request->filled('alerta_temperatura')) {
            if ($request->alerta_temperatura == 'si') {
                $query->where(function($q) {
                    $q->where('ct.Estado', '!=', 'normal')
                      ->orWhereNull('ct.Estado');
                });
            }
        }

        $equipos = $query->orderBy('er.Nombre')
            ->paginate(20);

        // Estadísticas de equipos
        $estadisticas = $this->calcularEstadisticasEquipos();

        // Mantenimientos pendientes
        $mantenimientosPendientes = $this->obtenerMantenimientosPendientes();

        // Análisis de rendimiento
        $analisisRendimiento = $this->analizarRendimientoEquipos();

        return compact('equipos', 'estadisticas', 'mantenimientosPendientes', 'analisisRendimiento');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $ubicaciones = DB::table('Ubicaciones')
            ->where('Tipo', 'farma')
            ->orderBy('Descripcion')
            ->get();

        $tiposEquipos = [
            'refrigerador' => 'Refrigerador Farmacéutico',
            'congelador' => 'Congelador',
            'vitrina' => 'Vitrina Refrigerada',
            'camara_fria' => 'Cámara Fría',
            'cold_room' => 'Cuarto Frío',
            'ultracongelador' => 'Ultracongelador',
            'otro' => 'Otro'
        ];

        $marcasComunes = [
            'Thermo Scientific', 'Eppendorf', 'Memmert', 'Thermo Fisher',
            'Panasonic', 'Sanyo', 'VWR', 'Labnet', 'EPPendorf', 'Otro'
        ];

        return compact('ubicaciones', 'tiposEquipos', 'marcasComunes');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'tipo_equipo' => 'required|in:refrigerador,congelador,vitrina,camara_fria,cold_room,ultracongelador,otro',
            'marca' => 'required|string|max:50',
            'modelo' => 'required|string|max:50',
            'serie' => 'required|string|max:50|unique:EquiposRefrigeracion,Serie',
            'cod_ubicacion' => 'required|exists:Ubicaciones,CodUbicacion',
            'capacidad_litros' => 'required|numeric|min:0',
            'temperatura_minima' => 'required|numeric|between:-30,25',
            'temperatura_maxima' => 'required|numeric|between:-30,25',
            'fecha_instalacion' => 'required|date',
            'costo' => 'required|numeric|min:0',
            'numero_garantia' => 'nullable|string|max:50',
            'fecha_garantia' => 'nullable|date|after:fecha_instalacion',
            'observaciones' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            // Verificar que temperatura máxima sea mayor que mínima
            if ($request->temperatura_maxima <= $request->temperatura_minima) {
                return response()->json([
                    'success' => false,
                    'message' => 'La temperatura máxima debe ser mayor que la temperatura mínima'
                ], 400);
            }

            $codEquipo = $this->generarCodEquipo();

            // Crear equipo
            DB::table('EquiposRefrigeracion')->insert([
                'CodEquipo' => $codEquipo,
                'Nombre' => $request->nombre,
                'TipoEquipo' => $request->tipo_equipo,
                'Marca' => $request->marca,
                'Modelo' => $request->modelo,
                'Serie' => $request->serie,
                'CodUbicacion' => $request->cod_ubicacion,
                'CapacidadLitros' => $request->capacidad_litros,
                'TemperaturaMinima' => $request->temperatura_minima,
                'TemperaturaMaxima' => $request->temperatura_maxima,
                'Estado' => 'activo',
                'FechaInstalacion' => $request->fecha_instalacion,
                'Costo' => $request->costo,
                'NumeroGarantia' => $request->numero_garantia,
                'FechaGarantia' => $request->fecha_garantia,
                'Observaciones' => $request->observaciones,
                'FechaCreacion' => Carbon::now(),
                'UsuarioCreacion' => Auth::id()
            ]);

            // Programar primer mantenimiento
            $this->programarMantenimientoInicial($codEquipo, $request->fecha_instalacion);

            // Registrar en log
            $this->registrarLogEquipos($codEquipo, 'CREACION_EQUIPO', 
                "Equipo {$request->nombre} creado - Serie: {$request->serie}");

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Equipo de refrigeración registrado correctamente',
                'data' => ['cod_equipo' => $codEquipo]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar equipo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($codEquipo)
    {
        $equipo = DB::table('EquiposRefrigeracion as er')
            ->leftJoin('Ubicaciones as u', 'er.CodUbicacion', '=', 'u.CodUbicacion')
            ->leftJoin('users as uc', 'er.UsuarioCreacion', '=', 'uc.id')
            ->leftJoin('users as um', 'er.UsuarioModificacion', '=', 'um.id')
            ->select([
                'er.*',
                'u.Descripcion as Ubicacion',
                'u.Tipo as TipoUbicacion',
                'u.TemperaturaMinima as TempMinUbicacion',
                'u.TemperaturaMaxima as TempMaxUbicacion',
                'uc.name as UsuarioCreacionNombre',
                'um.name as UsuarioModificacionNombre'
            ])
            ->where('er.CodEquipo', $codEquipo)
            ->first();

        if (!$equipo) {
            return response()->json(['message' => 'Equipo no encontrado'], 404);
        }

        // Últimas lecturas de temperatura
        $ultimasLecturas = DB::table('ControlTemperatura')
            ->where('CodEquipo', $codEquipo)
            ->orderBy('FechaRegistro', 'desc')
            ->limit(10)
            ->get();

        // Mantenimientos
        $mantenimientos = DB::table('MantenimientoEquipos')
            ->where('CodEquipo', $codEquipo)
            ->orderBy('FechaMantenimiento', 'desc')
            ->get();

        // Alertas recientes
        $alertasRecientes = DB::table('ControlTemperatura')
            ->where('CodEquipo', $codEquipo)
            ->where('Alerta', true)
            ->orderBy('FechaRegistro', 'desc')
            ->limit(5)
            ->get();

        // Estadísticas del equipo
        $estadisticasEquipo = $this->obtenerEstadisticasEquipo($codEquipo);

        // Productos en el equipo
        $productosEquipo = $this->obtenerProductosEquipo($codEquipo);

        return compact('equipo', 'ultimasLecturas', 'mantenimientos', 'alertasRecientes', 
            'estadisticasEquipo', 'productosEquipo');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($codEquipo)
    {
        $equipo = DB::table('EquiposRefrigeracion as er')
            ->where('er.CodEquipo', $codEquipo)
            ->first();

        if (!$equipo) {
            return response()->json(['message' => 'Equipo no encontrado'], 404);
        }

        $ubicaciones = DB::table('Ubicaciones')
            ->where('Tipo', 'farma')
            ->orderBy('Descripcion')
            ->get();

        $tiposEquipos = [
            'refrigerador' => 'Refrigerador Farmacéutico',
            'congelador' => 'Congelador',
            'vitrina' => 'Vitrina Refrigerada',
            'camara_fria' => 'Cámara Fría',
            'cold_room' => 'Cuarto Frío',
            'ultracongelador' => 'Ultracongelador',
            'otro' => 'Otro'
        ];

        return compact('equipo', 'ubicaciones', 'tiposEquipos');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $codEquipo)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'tipo_equipo' => 'required|in:refrigerador,congelador,vitrina,camara_fria,cold_room,ultracongelador,otro',
            'marca' => 'required|string|max:50',
            'modelo' => 'required|string|max:50',
            'cod_ubicacion' => 'required|exists:Ubicaciones,CodUbicacion',
            'capacidad_litros' => 'required|numeric|min:0',
            'temperatura_minima' => 'required|numeric|between:-30,25',
            'temperatura_maxima' => 'required|numeric|between:-30,25',
            'fecha_instalacion' => 'required|date',
            'costo' => 'required|numeric|min:0',
            'numero_garantia' => 'nullable|string|max:50',
            'fecha_garantia' => 'nullable|date|after:fecha_instalacion',
            'observaciones' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            // Verificar que existe el equipo
            $equipo = DB::table('EquiposRefrigeracion')
                ->where('CodEquipo', $codEquipo)
                ->first();

            if (!$equipo) {
                return response()->json(['message' => 'Equipo no encontrado'], 404);
            }

            // Verificar serie única (excluyendo el equipo actual)
            if ($request->filled('serie') && $request->serie != $equipo->Serie) {
                $serieExiste = DB::table('EquiposRefrigeracion')
                    ->where('Serie', $request->serie)
                    ->where('CodEquipo', '!=', $codEquipo)
                    ->exists();

                if ($serieExiste) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Ya existe un equipo con esa serie'
                    ], 400);
                }
            }

            // Verificar que temperatura máxima sea mayor que mínima
            if ($request->temperatura_maxima <= $request->temperatura_minima) {
                return response()->json([
                    'success' => false,
                    'message' => 'La temperatura máxima debe ser mayor que la temperatura mínima'
                ], 400);
            }

            // Actualizar equipo
            DB::table('EquiposRefrigeracion')
                ->where('CodEquipo', $codEquipo)
                ->update([
                    'Nombre' => $request->nombre,
                    'TipoEquipo' => $request->tipo_equipo,
                    'Marca' => $request->marca,
                    'Modelo' => $request->modelo,
                    'Serie' => $request->serie,
                    'CodUbicacion' => $request->cod_ubicacion,
                    'CapacidadLitros' => $request->capacidad_litros,
                    'TemperaturaMinima' => $request->temperatura_minima,
                    'TemperaturaMaxima' => $request->temperatura_maxima,
                    'FechaInstalacion' => $request->fecha_instalacion,
                    'Costo' => $request->costo,
                    'NumeroGarantia' => $request->numero_garantia,
                    'FechaGarantia' => $request->fecha_garantia,
                    'Observaciones' => $request->observaciones,
                    'FechaModificacion' => Carbon::now(),
                    'UsuarioModificacion' => Auth::id()
                ]);

            // Registrar en log
            $this->registrarLogEquipos($codEquipo, 'MODIFICACION_EQUIPO', 
                "Equipo {$request->nombre} modificado");

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Equipo actualizado correctamente',
                'data' => []
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar equipo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($codEquipo)
    {
        try {
            DB::beginTransaction();

            $equipo = DB::table('EquiposRefrigeracion')
                ->where('CodEquipo', $codEquipo)
                ->first();

            if (!$equipo) {
                return response()->json(['message' => 'Equipo no encontrado'], 404);
            }

            // Verificar si tiene lecturas de temperatura
            $lecturas = DB::table('ControlTemperatura')
                ->where('CodEquipo', $codEquipo)
                ->count();

            if ($lecturas > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar un equipo que tiene lecturas de temperatura'
                ], 400);
            }

            // Verificar si tiene mantenimientos
            $mantenimientos = DB::table('MantenimientoEquipos')
                ->where('CodEquipo', $codEquipo)
                ->count();

            if ($mantenimientos > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar un equipo que tiene registros de mantenimiento'
                ], 400);
            }

            DB::table('EquiposRefrigeracion')
                ->where('CodEquipo', $codEquipo)
                ->delete();

            // Registrar en log
            $this->registrarLogEquipos($codEquipo, 'ELIMINACION_EQUIPO', 
                "Equipo {$equipo->Nombre} eliminado");

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Equipo eliminado correctamente',
                'data' => []
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar equipo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cambiar estado del equipo
     */
    public function cambiarEstado(Request $request, $codEquipo)
    {
        $request->validate([
            'estado' => 'required|in:activo,inactivo,mantenimiento,fuera_servicio',
            'observaciones' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            $equipo = DB::table('EquiposRefrigeracion')
                ->where('CodEquipo', $codEquipo)
                ->first();

            if (!$equipo) {
                return response()->json(['message' => 'Equipo no encontrado'], 404);
            }

            $estadoAnterior = $equipo->Estado;

            DB::table('EquiposRefrigeracion')
                ->where('CodEquipo', $codEquipo)
                ->update([
                    'Estado' => $request->estado,
                    'FechaModificacion' => Carbon::now(),
                    'UsuarioModificacion' => Auth::id(),
                    'Observaciones' => $request->observaciones
                ]);

            // Registrar en log
            $this->registrarLogEquipos($codEquipo, 'CAMBIO_ESTADO', 
                "Estado cambiado de {$estadoAnterior} a {$request->estado}");

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Estado del equipo actualizado correctamente',
                'data' => []
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar estado: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Programar mantenimiento
     */
    public function programarMantenimiento(Request $request, $codEquipo)
    {
        $request->validate([
            'tipo_mantenimiento' => 'required|in:preventivo,correctivo,predictivo',
            'fecha_programada' => 'required|date|after:today',
            'descripcion' => 'required|string|max:500',
            'responsable' => 'required|string|max:100',
            'prioridad' => 'required|in:baja,media,alta,critica',
        ]);

        try {
            DB::beginTransaction();

            $codMantenimiento = $this->generarCodMantenimiento();

            DB::table('MantenimientoEquipos')->insert([
                'CodMantenimiento' => $codMantenimiento,
                'CodEquipo' => $codEquipo,
                'TipoMantenimiento' => $request->tipo_mantenimiento,
                'FechaProgramada' => $request->fecha_programada,
                'Descripcion' => $request->descripcion,
                'Responsable' => $request->responsable,
                'Prioridad' => $request->prioridad,
                'Estado' => 'programado',
                'FechaCreacion' => Carbon::now(),
                'UsuarioCreacion' => Auth::id()
            ]);

            $this->registrarLogEquipos($codEquipo, 'MANTENIMIENTO_PROGRAMADO', 
                "Mantenimiento {$request->tipo_mantenimiento} programado para {$request->fecha_programada}");

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Mantenimiento programado correctamente',
                'data' => ['cod_mantenimiento' => $codMantenimiento]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al programar mantenimiento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Dashboard de equipos
     */
    public function dashboard()
    {
        // Estadísticas generales
        $totalEquipos = DB::table('EquiposRefrigeracion')->count();
        $equiposActivos = DB::table('EquiposRefrigeracion')->where('Estado', 'activo')->count();
        $equiposMantenimiento = DB::table('EquiposRefrigeracion')->where('Estado', 'mantenimiento')->count();

        // Alertas de temperatura activas
        $alertasActivas = DB::table('ControlTemperatura as ct')
            ->join('EquiposRefrigeracion as er', 'ct.CodEquipo', '=', 'er.CodEquipo')
            ->where('ct.Alerta', true)
            ->where('er.Estado', 'activo')
            ->where('ct.FechaRegistro', '>=', Carbon::now()->subHours(2)->toDateTimeString())
            ->count();

        // Mantenimientos pendientes
        $mantenimientosPendientes = DB::table('MantenimientoEquipos')
            ->where('Estado', 'programado')
            ->where('FechaProgramada', '<=', Carbon::now()->addDays(7)->toDateString())
            ->count();

        // Equipos con mayor consumo energético (estimado)
        $equiposMayorConsumo = DB::table('EquiposRefrigeracion as er')
            ->leftJoin('ControlTemperatura as ct', 'er.CodEquipo', '=', 'ct.CodEquipo')
            ->select([
                'er.CodEquipo',
                'er.Nombre',
                'er.TipoEquipo',
                'er.CapacidadLitros',
                'er.Estado',
                DB::raw('AVG(ct.Temperatura) as TempPromedio'),
                DB::raw('COUNT(ct.Id) as Lecturas')
            ])
            ->where('er.Estado', 'activo')
            ->groupBy('er.CodEquipo', 'er.Nombre', 'er.TipoEquipo', 'er.CapacidadLitros', 'er.Estado')
            ->orderByDesc('er.CapacidadLitros')
            ->limit(10)
            ->get();

        // Eficiencia de equipos (basado en estabilidad de temperatura)
        $eficienciaEquipos = $this->calcularEficienciaEquipos();

        return compact('totalEquipos', 'equiposActivos', 'equiposMantenimiento', 
            'alertasActivas', 'mantenimientosPendientes', 'equiposMayorConsumo', 'eficienciaEquipos');
    }

    /**
     * Reporte de equipos por período
     */
    public function reporteEquipos(Request $request)
    {
        $request->validate([
            'fecha_desde' => 'required|date',
            'fecha_hasta' => 'required|date|after:fecha_desde',
        ]);

        $equipos = DB::table('EquiposRefrigeracion as er')
            ->leftJoin('Ubicaciones as u', 'er.CodUbicacion', '=', 'u.CodUbicacion')
            ->leftJoin('ControlTemperatura as ct', 'er.CodEquipo', '=', 'ct.CodEquipo')
            ->select([
                'er.CodEquipo',
                'er.Nombre',
                'er.TipoEquipo',
                'er.Marca',
                'er.Modelo',
                'er.Serie',
                'u.Descripcion as Ubicacion',
                'er.Estado',
                'er.CapacidadLitros',
                'er.Costo',
                DB::raw('COUNT(ct.Id) as TotalLecturas'),
                DB::raw('AVG(ct.Temperatura) as TemperaturaPromedio'),
                DB::raw('MIN(ct.Temperatura) as TemperaturaMinima'),
                DB::raw('MAX(ct.Temperatura) as TemperaturaMaxima'),
                DB::raw('SUM(CASE WHEN ct.Alerta = 1 THEN 1 ELSE 0 END) as TotalAlertas'),
                DB::raw('(SUM(CASE WHEN ct.Alerta = 1 THEN 1 ELSE 0 END) * 100.0 / NULLIF(COUNT(ct.Id), 0)) as PorcentajeAlertas')
            ])
            ->whereBetween('er.FechaInstalacion', [$request->fecha_desde, $request->fecha_hasta])
            ->groupBy([
                'er.CodEquipo', 'er.Nombre', 'er.TipoEquipo', 'er.Marca', 'er.Modelo', 
                'er.Serie', 'u.Descripcion', 'er.Estado', 'er.CapacidadLitros', 'er.Costo'
            ])
            ->orderBy('er.Nombre')
            ->get();

        return compact('equipos');
    }

    /**
     * Calcular estadísticas de equipos
     */
    private function calcularEstadisticasEquipos()
    {
        return [
            'total_equipos' => DB::table('EquiposRefrigeracion')->count(),
            'equipos_activos' => DB::table('EquiposRefrigeracion')->where('Estado', 'activo')->count(),
            'equipos_mantenimiento' => DB::table('EquiposRefrigeracion')->where('Estado', 'mantenimiento')->count(),
            'equipos_fuera_servicio' => DB::table('EquiposRefrigeracion')->where('Estado', 'fuera_servicio')->count(),
            'capacidad_total' => DB::table('EquiposRefrigeracion')
                ->where('Estado', 'activo')
                ->sum('CapacidadLitros'),
            'inversion_total' => DB::table('EquiposRefrigeracion')
                ->where('Estado', 'activo')
                ->sum('Costo')
        ];
    }

    /**
     * Obtener mantenimientos pendientes
     */
    private function obtenerMantenimientosPendientes()
    {
        return DB::table('MantenimientoEquipos as me')
            ->join('EquiposRefrigeracion as er', 'me.CodEquipo', '=', 'er.CodEquipo')
            ->select([
                'me.*',
                'er.Nombre as Equipo',
                'er.TipoEquipo'
            ])
            ->where('me.Estado', 'programado')
            ->where('me.FechaProgramada', '<=', Carbon::now()->addDays(7)->toDateString())
            ->orderBy('me.FechaProgramada')
            ->get();
    }

    /**
     * Analizar rendimiento de equipos
     */
    private function analizarRendimientoEquipos()
    {
        $equipos = DB::table('EquiposRefrigeracion as er')
            ->leftJoin('ControlTemperatura as ct', 'er.CodEquipo', '=', 'ct.CodEquipo')
            ->where('er.Estado', 'activo')
            ->where('ct.FechaRegistro', '>=', Carbon::now()->subDays(30)->toDateString())
            ->select([
                'er.CodEquipo',
                'er.Nombre',
                'er.TipoEquipo',
                'er.CapacidadLitros',
                DB::raw('AVG(ct.Temperatura) as TempPromedio'),
                DB::raw('COUNT(ct.Id) as Lecturas'),
                DB::raw('SUM(CASE WHEN ct.Alerta = 1 THEN 1 ELSE 0 END) as Alertas'),
                DB::raw('(SUM(CASE WHEN ct.Alerta = 1 THEN 1 ELSE 0 END) * 100.0 / NULLIF(COUNT(ct.Id), 0)) as PorcentajeAlertas')
            ])
            ->groupBy('er.CodEquipo', 'er.Nombre', 'er.TipoEquipo', 'er.CapacidadLitros')
            ->get();

        // Clasificar rendimiento
        foreach ($equipos as $equipo) {
            if ($equipo->PorcentajeAlertas > 20) {
                $equipo->Rendimiento = 'Deficiente';
            } elseif ($equipo->PorcentajeAlertas > 10) {
                $equipo->Rendimiento = 'Regular';
            } elseif ($equipo->PorcentajeAlertas > 5) {
                $equipo->Rendimiento = 'Bueno';
            } else {
                $equipo->Rendimiento = 'Excelente';
            }
        }

        return $equipos;
    }

    /**
     * Obtener estadísticas del equipo
     */
    private function obtenerEstadisticasEquipo($codEquipo)
    {
        $estadisticas = [];

        // Últimos 30 días
        $estadisticas['lecturas_30d'] = DB::table('ControlTemperatura')
            ->where('CodEquipo', $codEquipo)
            ->where('FechaRegistro', '>=', Carbon::now()->subDays(30)->toDateString())
            ->count();

        $estadisticas['alertas_30d'] = DB::table('ControlTemperatura')
            ->where('CodEquipo', $codEquipo)
            ->where('Alerta', true)
            ->where('FechaRegistro', '>=', Carbon::now()->subDays(30)->toDateString())
            ->count();

        $estadisticas['temp_promedio_30d'] = DB::table('ControlTemperatura')
            ->where('CodEquipo', $codEquipo)
            ->where('FechaRegistro', '>=', Carbon::now()->subDays(30)->toDateString())
            ->avg('Temperatura');

        // Tiempo de actividad
        $estadisticas['dias_operativo'] = DB::table('EquiposRefrigeracion')
            ->where('CodEquipo', $codEquipo)
            ->where('Estado', 'activo')
            ->value(DB::raw('DATEDIFF(day, FechaInstalacion, GETDATE())'));

        return $estadisticas;
    }

    /**
     * Obtener productos en el equipo
     */
    private function obtenerProductosEquipo($codEquipo)
    {
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
     * Calcular eficiencia de equipos
     */
    private function calcularEficienciaEquipos()
    {
        $equipos = DB::table('EquiposRefrigeracion as er')
            ->leftJoin('ControlTemperatura as ct', 'er.CodEquipo', '=', 'ct.CodEquipo')
            ->where('er.Estado', 'activo')
            ->where('ct.FechaRegistro', '>=', Carbon::now()->subDays(7)->toDateString())
            ->select([
                'er.CodEquipo',
                'er.Nombre',
                'er.TipoEquipo',
                'er.CapacidadLitros',
                DB::raw('AVG(ct.Temperatura) as TempPromedio'),
                DB::raw('COUNT(ct.Id) as Lecturas'),
                DB::raw('SUM(CASE WHEN ct.Alerta = 1 THEN 1 ELSE 0 END) as Alertas'),
                DB::raw('(SUM(CASE WHEN ct.Alerta = 1 THEN 1 ELSE 0 END) * 100.0 / NULLIF(COUNT(ct.Id), 0)) as PorcentajeFallas')
            ])
            ->groupBy('er.CodEquipo', 'er.Nombre', 'er.TipoEquipo', 'er.CapacidadLitros')
            ->having('Lecturas', '>', 10)
            ->orderBy('PorcentajeFallas')
            ->get();

        return $equipos;
    }

    /**
     * Programar mantenimiento inicial
     */
    private function programarMantenimientoInicial($codEquipo, $fechaInstalacion)
    {
        $fechaMantenimiento = Carbon::parse($fechaInstalacion)->addMonths(6);
        
        DB::table('MantenimientoEquipos')->insert([
            'CodMantenimiento' => $this->generarCodMantenimiento(),
            'CodEquipo' => $codEquipo,
            'TipoMantenimiento' => 'preventivo',
            'FechaProgramada' => $fechaMantenimiento,
            'Descripcion' => 'Mantenimiento preventivo inicial',
            'Responsable' => 'Departamento Técnico',
            'Prioridad' => 'media',
            'Estado' => 'programado',
            'FechaCreacion' => Carbon::now(),
            'UsuarioCreacion' => Auth::id()
        ]);
    }

    /**
     * Generar código de equipo
     */
    private function generarCodEquipo()
    {
        $ultimo = DB::table('EquiposRefrigeracion')
            ->max('CodEquipo');

        if ($ultimo) {
            $numero = (int) substr($ultimo, 3) + 1;
        } else {
            $numero = 1;
        }

        return 'EQF' . str_pad($numero, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generar código de mantenimiento
     */
    private function generarCodMantenimiento()
    {
        $ultimo = DB::table('MantenimientoEquipos')
            ->max('CodMantenimiento');

        if ($ultimo) {
            $numero = (int) substr($ultimo, 3) + 1;
        } else {
            $numero = 1;
        }

        return 'MAN' . str_pad($numero, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Registrar log de equipos
     */
    private function registrarLogEquipos($codEquipo, $accion, $descripcion)
    {
        DB::table('LogEquipos')->insert([
            'CodEquipo' => $codEquipo,
            'Accion' => $accion,
            'Descripcion' => $descripcion,
            'Fecha' => Carbon::now(),
            'Usuario' => Auth::id()
        ]);
    }
}