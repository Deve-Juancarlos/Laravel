<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PlanillasController extends Controller
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
     * Dashboard principal de planillas - usando tabla Empleados real
     */
    public function index(Request $request)
    {
        $año = $request->get('año', now()->year);
        $mes = $request->get('mes', now()->month);
        
        // Obtener empleados desde la tabla real Empleados
        $empleados = DB::table('Empleados as e')
            ->leftJoin('accesoweb as aw', 'e.Codemp', '=', 'aw.idusuario')
            ->where('e.Tipo', '!=', 0) // Excluir tipos no válidos
            ->select([
                'e.*',
                'aw.usuario',
                'aw.tipousuario',
                DB::raw("'ACTIVO' as EstadoEmpleado") // Agregar campo para compatibilidad
            ])
            ->orderBy('e.Nombre')
            ->paginate(20);

        // Resumen del período
        $resumen = $this->calcularResumenEmpleados($año, $mes);
        
        // Estadísticas anuales
        $estadisticasAnuales = $this->calcularEstadisticasEmpleados($año);
        
        // Empleados activos
        $empleadosActivos = $this->obtenerEmpleadosActivos();

        return response()->json([
            'empleados' => $empleados,
            'resumen' => $resumen,
            'estadisticas' => $estadisticasAnuales,
            'empleados_activos' => $empleadosActivos,
            'periodo_actual' => [
                'año' => $año,
                'mes' => $mes,
                'nombre_mes' => Carbon::create($año, $mes)->format('F')
            ]
        ]);
    }

    /**
     * Crear nuevo registro de empleado
     */
    public function crearEmpleado(Request $request)
    {
        $request->validate([
            'Nombre' => 'required|string|max:50',
            'Documento' => 'nullable|string|max:12',
            'Direccion' => 'nullable|string|max:60',
            'Telefono1' => 'nullable|string|max:10',
            'Celular' => 'nullable|string|max:10',
            'Tipo' => 'required|integer|min:1',
            'Cumpleaños' => 'nullable|string|max:50'
        ]);

        try {
            DB::beginTransaction();

            // Obtener siguiente código de empleado
            $ultimoCodigo = DB::table('Empleados')
                ->max('Codemp') ?? 0;
            
            $nuevoCodigo = $ultimoCodigo + 1;

            // Crear empleado
            $empleado = DB::table('Empleados')->insert([
                'Codemp' => $nuevoCodigo,
                'Nombre' => $request->Nombre,
                'Documento' => $request->Documento,
                'Direccion' => $request->Direccion,
                'Telefono1' => $request->Telefono1,
                'Celular' => $request->Celular,
                'Tipo' => $request->Tipo,
                'Cumpleaños' => $request->Cumpleaños
            ]);

            // Si se proporciona usuario, crear acceso web
            if ($request->filled('usuario') && $request->filled('password')) {
                DB::table('accesoweb')->insert([
                    'idusuario' => $nuevoCodigo,
                    'usuario' => $request->usuario,
                    'tipousuario' => $this->mapearTipoEmpleado($request->Tipo),
                    'password' => bcrypt($request->password),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Empleado creado exitosamente',
                'empleado' => [
                    'Codemp' => $nuevoCodigo,
                    'Nombre' => $request->Nombre,
                    'Tipo' => $request->Tipo
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error al crear empleado: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar empleado
     */
    public function actualizarEmpleado(Request $request, $id)
    {
        $request->validate([
            'Nombre' => 'required|string|max:50',
            'Documento' => 'nullable|string|max:12',
            'Direccion' => 'nullable|string|max:60',
            'Telefono1' => 'nullable|string|max:10',
            'Celular' => 'nullable|string|max:10',
            'Tipo' => 'required|integer|min:1',
            'Cumpleaños' => 'nullable|string|max:50'
        ]);

        try {
            DB::beginTransaction();

            // Actualizar empleado
            $empleado = DB::table('Empleados')
                ->where('Codemp', $id)
                ->update([
                    'Nombre' => $request->Nombre,
                    'Documento' => $request->Documento,
                    'Direccion' => $request->Direccion,
                    'Telefono1' => $request->Telefono1,
                    'Celular' => $request->Celular,
                    'Tipo' => $request->Tipo,
                    'Cumpleaños' => $request->Cumpleaños
                ]);

            // Actualizar acceso web si existe
            if ($request->filled('usuario')) {
                $datosAcceso = [
                    'usuario' => $request->usuario,
                    'tipousuario' => $this->mapearTipoEmpleado($request->Tipo),
                    'updated_at' => now()
                ];

                if ($request->filled('password')) {
                    $datosAcceso['password'] = bcrypt($request->password);
                }

                DB::table('accesoweb')
                    ->where('idusuario', $id)
                    ->update($datosAcceso);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Empleado actualizado exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar empleado: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar empleado (desactivar)
     */
    public function eliminarEmpleado($id)
    {
        try {
            DB::beginTransaction();

            // Verificar que el empleado existe
            $empleado = DB::table('Empleados')->where('Codemp', $id)->first();
            if (!$empleado) {
                return response()->json([
                    'success' => false,
                    'message' => 'Empleado no encontrado'
                ], 404);
            }

            // Verificar que no tenga transacciones relacionadas
            $tieneTransacciones = DB::table('Doccab')
                ->where('Vendedor', $id)
                ->exists();

            if ($tieneTransacciones) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar empleado con transacciones registradas'
                ], 400);
            }

            // Cambiar tipo a 0 para marcar como inactivo
            DB::table('Empleados')
                ->where('Codemp', $id)
                ->update(['Tipo' => 0]);

            // Desactivar acceso web
            DB::table('accesoweb')
                ->where('idusuario', $id)
                ->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Empleado eliminado exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar empleado: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener detalles de un empleado
     */
    public function mostrarEmpleado($id)
    {
        $empleado = DB::table('Empleados as e')
            ->leftJoin('accesoweb as aw', 'e.Codemp', '=', 'aw.idusuario')
            ->where('e.Codemp', $id)
            ->select('e.*', 'aw.usuario', 'aw.tipousuario')
            ->first();

        if (!$empleado) {
            return response()->json([
                'success' => false,
                'message' => 'Empleado no encontrado'
            ], 404);
        }

        // Obtener estadísticas del empleado
        $estadisticas = $this->calcularEstadisticasEmpleado($id);

        return response()->json([
            'success' => true,
            'empleado' => $empleado,
            'estadisticas' => $estadisticas
        ]);
    }

    /**
     * Buscar empleados
     */
    public function buscarEmpleados(Request $request)
    {
        $query = DB::table('Empleados as e')
            ->leftJoin('accesoweb as aw', 'e.Codemp', '=', 'aw.idusuario')
            ->where('e.Tipo', '!=', 0);

        if ($request->filled('busqueda')) {
            $busqueda = '%' . $request->busqueda . '%';
            $query->where(function($q) use ($busqueda) {
                $q->where('e.Nombre', 'like', $busqueda)
                  ->orWhere('e.Documento', 'like', $busqueda)
                  ->orWhere('e.Celular', 'like', $busqueda);
            });
        }

        if ($request->filled('tipo')) {
            $query->where('e.Tipo', $request->tipo);
        }

        $empleados = $query->select([
            'e.*',
            'aw.usuario',
            'aw.tipousuario'
        ])
        ->orderBy('e.Nombre')
        ->paginate(20);

        return response()->json([
            'success' => true,
            'empleados' => $empleados
        ]);
    }

    /**
     * Calcular resumen de empleados
     */
    private function calcularResumenEmpleados($año, $mes)
    {
        $totalEmpleados = DB::table('Empleados')
            ->where('Tipo', '!=', 0)
            ->count();

        $empleadosActivos = DB::table('Empleados')
            ->where('Tipo', '!=', 0)
            ->where('Tipo', '!=', 99) // Excluir tipos especiales
            ->count();

        $empleadosConAcceso = DB::table('Empleados as e')
            ->join('accesoweb as aw', 'e.Codemp', '=', 'aw.idusuario')
            ->where('e.Tipo', '!=', 0)
            ->count();

        return [
            'total_empleados' => $totalEmpleados,
            'empleados_activos' => $empleadosActivos,
            'empleados_con_acceso' => $empleadosConAcceso,
            'empleados_sin_acceso' => $totalEmpleados - $empleadosConAcceso
        ];
    }

    /**
     * Calcular estadísticas anuales
     */
    private function calcularEstadisticasAnuales($año)
    {
        $empleadosPorMes = DB::table('Empleados')
            ->selectRaw('MONTH(created_at) as mes, COUNT(*) as cantidad')
            ->whereYear('created_at', $año)
            ->where('Tipo', '!=', 0)
            ->groupBy('mes')
            ->get();

        $empleadosPorTipo = DB::table('Empleados')
            ->selectRaw('Tipo, COUNT(*) as cantidad')
            ->where('Tipo', '!=', 0)
            ->groupBy('Tipo')
            ->get();

        return [
            'empleados_por_mes' => $empleadosPorMes,
            'empleados_por_tipo' => $empleadosPorTipo
        ];
    }

    /**
     * Obtener empleados activos
     */
    private function obtenerEmpleadosActivos()
    {
        return DB::table('Empleados')
            ->where('Tipo', '!=', 0)
            ->where('Tipo', '!=', 99)
            ->select('Codemp', 'Nombre', 'Tipo')
            ->orderBy('Nombre')
            ->get();
    }

    /**
     * Calcular estadísticas de empleado específico
     */
    private function calcularEstadisticasEmpleado($empleadoId)
    {
        $ventas = DB::table('Doccab')
            ->where('Vendedor', $empleadoId)
            ->whereYear('Fecha', now()->year)
            ->count();

        $totalVentas = DB::table('Doccab')
            ->where('Vendedor', $empleadoId)
            ->whereYear('Fecha', now()->year)
            ->sum('Total');

        return [
            'ventas_realizadas' => $ventas,
            'total_ventas' => $totalVentas,
            'ticket_promedio' => $ventas > 0 ? $totalVentas / $ventas : 0
        ];
    }

    /**
     * Mapear tipo de empleado a tipousuario
     */
    private function mapearTipoEmpleado($tipo)
    {
        switch ($tipo) {
            case 1:
                return 'administrador';
            case 2:
                return 'contador';
            default:
                return 'empleado';
        }
    }
}
