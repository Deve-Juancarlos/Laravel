<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\EmpleadoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class EmpleadoController extends Controller
{
    protected $empleadoService;

    public function __construct(EmpleadoService $empleadoService)
    {
        $this->empleadoService = $empleadoService;
    }

    public function index(Request $request)
    {
        $filtros = [
            'buscar' => $request->get('buscar'),
            'tipo' => $request->get('tipo'),
        ];

        $empleados = $this->empleadoService->obtenerEmpleados($filtros);
        $estadisticas = $this->empleadoService->obtenerEstadisticas();
        
        return view('admin.empleados.index', compact('empleados', 'estadisticas', 'filtros'));
    }

    public function create()
    {
        return view('admin.empleados.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'Codemp' => 'required|integer|unique:Empleados,Codemp',
            'Nombre' => 'required|string|max:50',
            'Documento' => 'nullable|string|max:12',
            'Direccion' => 'nullable|string|max:60',
            'Telefono1' => 'nullable|string|max:10',
            'Telefono2' => 'nullable|string|max:10',
            'Celular' => 'nullable|string|max:10',
            'Nextel' => 'nullable|string|max:15',
            'Cumpleaños' => 'nullable|string|max:50',
            'Tipo' => 'required|integer',
        ]);

        $resultado = $this->empleadoService->crearEmpleado($request->all());

        if ($resultado) {
            return redirect()->route('admin.empleados.index')
                ->with('success', 'Empleado creado correctamente.');
        }

        return back()->with('error', 'Error al crear el empleado. Verifique que el código no exista.');
    }

    public function show($id)
{
    // Obtener empleado
    $empleado = $this->empleadoService->obtenerEmpleado($id);
    if (!$empleado) {
        return redirect()->route('admin.empleados.index')
            ->with('error', 'Empleado no encontrado.');
    }

    // Obtener usuario vinculado
    $usuario = $this->empleadoService->obtenerUsuarioVinculado($id);

    // Si no existe usuario, creamos un objeto temporal para evitar error de propiedad indefinida
    if (!$usuario) {
        $usuario = new \stdClass();
        $usuario->estado = 0; // inactivo
        $usuario->usuario = null;
        $usuario->tipousuario = null;
    } else {
        $usuario->estado = 1; // activo
    }

    return view('admin.empleados.show', compact('empleado', 'usuario'));
}


    public function edit($id)
    {
        $empleado = $this->empleadoService->obtenerEmpleado($id);
        if (!$empleado) {
            return redirect()->route('admin.empleados.index')
                ->with('error', 'Empleado no encontrado.');
        }
        return view('admin.empleados.edit', compact('empleado'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'Nombre' => 'required|string|max:50',
            'Documento' => 'nullable|string|max:12|unique:Empleados,Documento,' . $id . ',Codemp',
            'Direccion' => 'nullable|string|max:60',
            'Telefono1' => 'nullable|string|max:10',
            'Telefono2' => 'nullable|string|max:10',
            'Celular' => 'nullable|string|max:10',
            'Nextel' => 'nullable|string|max:15',
            'Cumpleaños' => 'nullable|string|max:50',
            'Tipo' => 'required|integer',
        ]);

        $resultado = $this->empleadoService->actualizarEmpleado($id, $request->all());

        if ($resultado) {
            return redirect()->route('admin.empleados.index')
                ->with('success', 'Empleado actualizado correctamente.');
        }

        return back()->with('error', 'Error al actualizar el empleado.');
    }

    public function destroy($id)
    {
        // Primero verificar dependencias
        $tieneUsuario = $this->empleadoService->obtenerUsuarioVinculado($id) !== null;
        $esVendedor = DB::table('Doccab')->where('Vendedor', $id)->exists();

        if ($tieneUsuario || $esVendedor) {
            return back()->with('error', 'No se puede eliminar: el empleado está vinculado a ventas o tiene un usuario del sistema.');
        }

        $resultado = $this->empleadoService->eliminarEmpleado($id);

        if ($resultado) {
            return redirect()->route('admin.empleados.index')
                ->with('success', 'Empleado eliminado correctamente.');
        }

        return back()->with('error', 'Error al eliminar el empleado.');
    }
}