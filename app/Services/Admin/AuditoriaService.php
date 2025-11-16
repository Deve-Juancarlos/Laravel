<?php

namespace App\Services\Admin;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AuditoriaService
{
    /**
     * Obtiene eventos combinados o filtrados de ambas tablas de auditoría.
     */
    public function obtenerEventos($filtros = [], $limite = 100)
{
    $querySistema = DB::table('Auditoria_Sistema')
        ->select(
            'id',
            'usuario',
            'accion',
            DB::raw("ISNULL(CAST(detalle AS NVARCHAR(MAX)), '') as descripcion"),
            'fecha',
            DB::raw("CONVERT(varchar(8), fecha, 108) as hora"), // Hora hh:mm:ss
            DB::raw("NULL as ip"),
            DB::raw("'sistema' as fuente")
        );

    $queryContable = DB::table('libro_diario_auditoria')
        ->select(
            'id',
            'usuario',
            'accion',
            DB::raw("ISNULL(CAST(datos_nuevos AS NVARCHAR(MAX)), ISNULL(CAST(datos_anteriores AS NVARCHAR(MAX)), '')) as descripcion"),
            DB::raw('fecha_hora as fecha'),
            DB::raw("CONVERT(varchar(8), fecha_hora, 108) as hora"), // Hora hh:mm:ss
            DB::raw('ip_address as ip'),
            DB::raw("'contable' as fuente")
        );

    // Aplicar filtros generales en ambas tablas
    if (!empty($filtros['usuario'])) {
        $querySistema->where('usuario', $filtros['usuario']);
        $queryContable->where('usuario', $filtros['usuario']);
    }

    if (!empty($filtros['accion'])) {
        $querySistema->where('accion', $filtros['accion']);
        $queryContable->where('accion', $filtros['accion']);
    }

    if (!empty($filtros['fecha_inicio'])) {
        $querySistema->whereDate('fecha', '>=', $filtros['fecha_inicio']);
        $queryContable->whereDate('fecha_hora', '>=', $filtros['fecha_inicio']);
    }

    if (!empty($filtros['fecha_fin'])) {
        $querySistema->whereDate('fecha', '<=', $filtros['fecha_fin']);
        $queryContable->whereDate('fecha_hora', '<=', $filtros['fecha_fin']);
    }

    if (!empty($filtros['buscar'])) {
        $term = '%' . $filtros['buscar'] . '%';
        $querySistema->where(function ($q) use ($term) {
            $q->where('detalle', 'like', $term)
              ->orWhere('usuario', 'like', $term)
              ->orWhere('accion', 'like', $term);
        });

        $queryContable->where(function ($q) use ($term) {
            $q->where('datos_nuevos', 'like', $term)
              ->orWhere('datos_anteriores', 'like', $term)
              ->orWhere('usuario', 'like', $term)
              ->orWhere('accion', 'like', $term);
        });
    }

    // Combinar ambos conjuntos con UNION
    $eventos = $querySistema->union($queryContable)
        ->orderBy('fecha', 'desc')
        ->limit($limite)
        ->get();

    return $eventos;
}



   public function obtenerEvento($id)
{
    // Primero buscar en Auditoria_Sistema
    $evento = DB::table('Auditoria_Sistema')
        ->select(
            'id',
            'usuario',
            'accion',
            DB::raw("detalle as descripcion"),
            'fecha',
            DB::raw("CONVERT(varchar(8), fecha, 108) as hora"),
            DB::raw("NULL as ip")
        )
        ->where('id', $id)
        ->first();

    if ($evento) return $evento;

    // Luego en libro_diario_auditoria
    return DB::table('libro_diario_auditoria')
        ->select(
            'id',
            'usuario',
            'accion',
            DB::raw("ISNULL(datos_nuevos, datos_anteriores) as descripcion"),
            'fecha_hora as fecha',
            DB::raw("CONVERT(varchar(8), fecha_hora, 108) as hora"),
            'ip_address as ip'
        )
        ->where('id', $id)
        ->first();
}


    public function obtenerEstadisticas()
    {
        $hoy = Carbon::today();
        $mesActual = Carbon::now()->startOfMonth();

        $totalSistema = DB::table('Auditoria_Sistema')->count();
        $totalContable = DB::table('libro_diario_auditoria')->count();

        return [
            'total' => $totalSistema + $totalContable,
            'hoy' => DB::table('Auditoria_Sistema')->whereDate('fecha', $hoy)->count()
                   + DB::table('libro_diario_auditoria')->whereDate('fecha_hora', $hoy)->count(),
            'ultimos_7_dias' => DB::table('Auditoria_Sistema')->where('fecha', '>=', $hoy->copy()->subDays(7))->count()
                               + DB::table('libro_diario_auditoria')->where('fecha_hora', '>=', $hoy->copy()->subDays(7))->count(),
            'mes_actual' => DB::table('Auditoria_Sistema')->where('fecha', '>=', $mesActual)->count()
                          + DB::table('libro_diario_auditoria')->where('fecha_hora', '>=', $mesActual)->count(),
            'usuarios_activos_hoy' => DB::table('Auditoria_Sistema')->whereDate('fecha', $hoy)->distinct('usuario')->count('usuario')
                                    + DB::table('libro_diario_auditoria')->whereDate('fecha_hora', $hoy)->distinct('usuario')->count('usuario'),
            'acciones_criticas' => DB::table('Auditoria_Sistema')
                ->whereIn('accion', ['MODIFICAR', 'ELIMINAR', 'ANULAR', 'ACCESO_DENEGADO'])
                ->where('fecha', '>=', $mesActual)->count(),
            'logins_hoy' => DB::table('Auditoria_Sistema')
                ->where('accion', 'LOGIN')
                ->whereDate('fecha', $hoy)->count(),
            'accesos_denegados' => DB::table('Auditoria_Sistema')
                ->where('accion', 'ACCESO_DENEGADO')
                ->where('fecha', '>=', $mesActual->toDateString())->count(),
        ];
    }

    public function obtenerEstadisticasDetalladas($periodo = 'mes')
    {
        $fechaInicio = match($periodo) {
            'dia' => Carbon::today(),
            'semana' => Carbon::now()->startOfWeek(),
            'mes' => Carbon::now()->startOfMonth(),
            'anio' => Carbon::now()->startOfYear(),
            default => Carbon::now()->startOfMonth(),
        };

        // Solo de Auditoria_Sistema (por simplicidad y relevancia)
        $eventosPorAccion = DB::table('Auditoria_Sistema')
            ->select('accion', DB::raw('COUNT(*) as total'))
            ->where('fecha', '>=', $fechaInicio)
            ->groupBy('accion')
            ->orderByDesc('total')
            ->get();

        $eventosPorDia = DB::table('Auditoria_Sistema')
            ->select(DB::raw('CAST(fecha AS DATE) as fecha'), DB::raw('COUNT(*) as total'))
            ->where('fecha', '>=', $fechaInicio)
            ->groupBy(DB::raw('CAST(fecha AS DATE)'))
            ->orderBy('fecha')
            ->get();

        return [
            'por_accion' => $eventosPorAccion,
            'por_dia' => $eventosPorDia,
            'por_hora' => collect(), // no se puede obtener hora de forma confiable desde Auditoria_Sistema
        ];
    }

    public function obtenerAccionesCriticas($limite = 50)
    {
        return DB::table('Auditoria_Sistema')
            ->select('id', 'usuario', 'accion', 'detalle as descripcion', 'fecha', DB::raw("NULL as ip"))
            ->whereIn('accion', ['MODIFICAR', 'ELIMINAR', 'ANULAR', 'ACCESO_DENEGADO', 'CAMBIAR_ROL', 'DESACTIVAR_USUARIO'])
            ->where('fecha', '>=', Carbon::now()->startOfMonth())
            ->orderBy('fecha', 'desc')
            ->limit($limite)
            ->get();
    }

    public function obtenerUsuariosMasActivos($limite = 10)
    {
        $mesActual = Carbon::now()->startOfMonth();
        return DB::table('Auditoria_Sistema')
            ->select('usuario', DB::raw('COUNT(*) as total_acciones'))
            ->where('fecha', '>=', $mesActual)
            ->groupBy('usuario')
            ->orderByDesc('total_acciones')
            ->limit($limite)
            ->get();
    }

    public function obtenerUsuariosConActividad()
    {
        return DB::table('Auditoria_Sistema')
            ->union(DB::table('libro_diario_auditoria')->select('usuario'))
            ->distinct()
            ->orderBy('usuario')
            ->pluck('usuario');
    }

   public function obtenerTimeline($usuario = null, $fecha = null)
{
    $querySistema = DB::table('Auditoria_Sistema')
        ->select(
            'id',
            'usuario',
            'accion',
            DB::raw("ISNULL(CAST(detalle AS NVARCHAR(MAX)), '') as descripcion"),
            'fecha',
            DB::raw("CONVERT(varchar(8), fecha, 108) as hora"),
            DB::raw("NULL as ip"),
            DB::raw("'sistema' as fuente")
        );

    $queryContable = DB::table('libro_diario_auditoria')
        ->select(
            'id',
            'usuario',
            'accion',
            DB::raw("ISNULL(CAST(datos_nuevos AS NVARCHAR(MAX)), ISNULL(CAST(datos_anteriores AS NVARCHAR(MAX)), '')) as descripcion"),
            'fecha_hora as fecha',
            DB::raw("CONVERT(varchar(8), fecha_hora, 108) as hora"),
            'ip_address as ip',
            DB::raw("'contable' as fuente")
        );

    $unionQuery = $querySistema->union($queryContable);

    // Usamos fromSub para poder filtrar sobre la union
    $query = DB::query()->fromSub($unionQuery, 'eventos');

    if ($usuario) {
        $query->where('usuario', $usuario);
    }

    if ($fecha) {
        $query->whereDate('fecha', $fecha);
    } else {
        $hace7dias = Carbon::today()->subDays(7);
        $query->where('fecha', '>=', $hace7dias);
    }

    return $query->orderBy('fecha', 'desc')->limit(200)->get();
}


    public function obtenerEventosPorUsuario($usuario, $limite = 100)
{
    $sistema = DB::table('Auditoria_Sistema')
        ->select(
            'id',
            'usuario',
            'accion',
            DB::raw("ISNULL(CAST(detalle AS NVARCHAR(MAX)), '') as descripcion"),
            'fecha',
            DB::raw("CONVERT(varchar(8), fecha, 108) as hora"),
            DB::raw("NULL as ip"),
            DB::raw("'sistema' as fuente")
        )
        ->where('usuario', $usuario);

    $contable = DB::table('libro_diario_auditoria')
        ->select(
            'id',
            'usuario',
            'accion',
            DB::raw("ISNULL(CAST(datos_nuevos AS NVARCHAR(MAX)), ISNULL(CAST(datos_anteriores AS NVARCHAR(MAX)), '')) as descripcion"),
            'fecha_hora as fecha',
            DB::raw("CONVERT(varchar(8), fecha_hora, 108) as hora"),
            'ip_address as ip',
            DB::raw("'contable' as fuente")
        )
        ->where('usuario', $usuario);

    $unionQuery = $sistema->unionAll($contable);

    $query = DB::query()->fromSub($unionQuery, 'eventos')
        ->orderBy('fecha', 'desc')
        ->limit($limite);

    return $query->get();
}


    public function obtenerEstadisticasUsuario($usuario)
    {
        $mesActual = Carbon::now()->startOfMonth();

        return [
            'total_acciones' => DB::table('Auditoria_Sistema')->where('usuario', $usuario)->count()
                              + DB::table('libro_diario_auditoria')->where('usuario', $usuario)->count(),
            'acciones_mes' => DB::table('Auditoria_Sistema')->where('usuario', $usuario)->where('fecha', '>=', $mesActual)->count()
                            + DB::table('libro_diario_auditoria')->where('usuario', $usuario)->where('fecha_hora', '>=', $mesActual)->count(),
            'ultimo_acceso' => DB::table('Auditoria_Sistema')
                ->where('usuario', $usuario)
                ->where('accion', 'LOGIN')
                ->orderBy('fecha', 'desc')
                ->first(),
            'acciones_por_tipo' => DB::table('Auditoria_Sistema')
                ->select('accion', DB::raw('COUNT(*) as total'))
                ->where('usuario', $usuario)
                ->where('fecha', '>=', $mesActual)
                ->groupBy('accion')
                ->orderByDesc('total')
                ->get(),
        ];
    }

    public function obtenerEventosPorAccion($accion, $limite = 100)
    {
        $sistema = DB::table('Auditoria_Sistema')
            ->select('id', 'usuario', 'accion', 'detalle as descripcion', 'fecha', DB::raw("NULL as ip"))
            ->where('accion', $accion);

        $contable = DB::table('libro_diario_auditoria')
            ->select('id', 'usuario', 'accion',
                DB::raw("ISNULL(datos_nuevos, datos_anteriores) as descripcion"),
                'fecha_hora as fecha',
                'ip_address as ip'
            )
            ->where('accion', $accion);

        return $sistema->union($contable)
            ->orderBy('fecha', 'desc')
            ->limit($limite)
            ->get();
    }

    public function exportarAExcel($filtros = [])
    {
        $eventos = $this->obtenerEventos($filtros, 10000);
        // Aquí iría la lógica de exportación con Laravel Excel o PhpSpreadsheet
        return $eventos;
    }

    public function limpiarLogsAntiguos($dias = 90)
    {
        try {
            $fechaLimite = Carbon::today()->subDays($dias);

            $eliminados1 = DB::table('Auditoria_Sistema')->where('fecha', '<', $fechaLimite)->delete();
            $eliminados2 = DB::table('libro_diario_auditoria')->where('fecha_hora', '<', $fechaLimite)->delete();

            $this->registrarEvento('LIMPIAR_LOGS', "Se eliminaron {$eliminados1} + {$eliminados2} registros antiguos");

            return ['success' => true, 'eliminados' => $eliminados1 + $eliminados2];
        } catch (\Exception $e) {
            \Log::error('Error al limpiar logs: ' . $e->getMessage());
            return ['success' => false, 'eliminados' => 0];
        }
    }

    public function registrarEvento($accion, $descripcion)
    {
        try {
            $usuario = auth()->user()?->usuario ?? 'SISTEMA';
            DB::table('Auditoria_Sistema')->insert([
                'usuario' => $usuario,
                'accion' => $accion,
                'detalle' => $descripcion,
                'fecha' => Carbon::now(),
            ]);
            return true;
        } catch (\Exception $e) {
            \Log::error('Error al registrar evento: ' . $e->getMessage());
            return false;
        }
    }
}