<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\UsuarioService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UsuarioController extends Controller
{
    protected $usuarioService;

    public function __construct(UsuarioService $usuarioService)
    {
        $this->usuarioService = $usuarioService;
    }

    /**
     * Lista todos los usuarios del sistema
     */
    public function index(Request $request)
    {
        $filtros = [
            'tipo' => $request->get('tipo'),
            'estado' => $request->get('estado'),
            'buscar' => $request->get('buscar'),
        ];

        $usuarios = $this->usuarioService->obtenerUsuarios($filtros);
        $estadisticas = $this->usuarioService->obtenerEstadisticas();
        
        return view('admin.usuarios.index', compact('usuarios', 'estadisticas', 'filtros'));
    }

    /**
     * Muestra formulario para crear usuario con empleado
     */
    public function create()
    {
        $empleadosDisponibles = $this->usuarioService->obtenerEmpleadosSinUsuario();
        
        return view('admin.usuarios.create', compact('empleadosDisponibles'));
    }

    /**
     * Crea un nuevo usuario vinculado a un empleado
     */
    public function store(Request $request)
    {
        $request->validate([
            'usuario' => 'required|string|max:100|unique:accesoweb,usuario',
            'password' => 'required|string|min:6|confirmed',
            'tipousuario' => 'required|in:administrador,CONTADOR,VENDEDOR',
            'idusuario' => 'required|exists:Empleados,Codemp|unique:accesoweb,idusuario',
        ]);

        DB::beginTransaction();

        try {
            
            $result = DB::table('accesoweb')->insert([
                'usuario' => $request->usuario,
                'password' => Hash::make($request->password),
                'tipousuario' => $request->tipousuario,
                'idusuario' => $request->idusuario,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if (!$result) {
                throw new \Exception('No se pudo crear el usuario');
            }

            DB::commit();

            return redirect()->route('admin.usuarios.index')
                ->with('success', 'Usuario creado y asociado al empleado exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al crear usuario: ' . $e->getMessage());
            return back()->with('error', 'Error al crear usuario: ' . $e->getMessage())
                        ->withInput();
        }
    }
    
    
    public function roles($usuario)
    {
        $usuarioData = $this->usuarioService->obtenerUsuarioPorNombre($usuario);
        
        if (!$usuarioData) {
            return redirect()->route('admin.usuarios.index')
                ->with('error', 'Usuario no encontrado.');
        }

        return view('admin.usuarios.roles', compact('usuarioData'));
    }

    /**
     * Actualizar rol de usuario
     */
    public function updateRol(Request $request, $usuario)
    {
        $request->validate([
            'tipousuario' => 'required|in:administrador,CONTADOR,VENDEDOR',
        ]);

        $resultado = $this->usuarioService->cambiarRol($usuario, $request->tipousuario);

        if ($resultado) {
            return redirect()->route('admin.usuarios.index')
                ->with('success', 'Rol actualizado correctamente.');
        }

        return back()->with('error', 'Error al actualizar el rol.');
    }

    /**
     * Activar usuario
     */
    public function activar($usuario)
    {
        $resultado = $this->usuarioService->activarUsuario($usuario);

        if ($resultado) {
            return redirect()->route('admin.usuarios.index')
                ->with('success', 'Usuario activado correctamente.');
        }

        return back()->with('error', 'Error al activar el usuario.');
    }

    /**
     * Desactivar usuario
     */
    public function desactivar($usuario)
    {
        $resultado = $this->usuarioService->desactivarUsuario($usuario);

        if ($resultado) {
            return redirect()->route('admin.usuarios.index')
                ->with('success', 'Usuario desactivado correctamente.');
        }

        return back()->with('error', 'Error al desactivar el usuario.');
    }

    /**
     * Ver historial de accesos
     */
    public function historial($usuario)
    {
        $usuarioData = $this->usuarioService->obtenerUsuarioPorNombre($usuario);
        $historial = $this->usuarioService->obtenerHistorialAccesos($usuario);
        
        return view('admin.usuarios.historial', compact('usuarioData', 'historial'));
    }

    /**
     * Editar vinculación de usuario con empleado
     */
    public function edit($usuario)
    {
        $usuarioData = $this->usuarioService->obtenerUsuarioPorNombre($usuario);
        $empleadosDisponibles = $this->usuarioService->obtenerEmpleadosSinUsuario();
        
        return view('admin.usuarios.edit', compact('usuarioData', 'empleadosDisponibles'));
    }

    /**
     * Actualizar vinculación de usuario con empleado
     */
    public function update(Request $request, $usuario)
    {
        $request->validate([
            'idusuario' => 'required|integer|exists:Empleados,Codemp',
            'tipousuario' => 'required|in:administrador,CONTADOR,VENDEDOR',
        ]);

        $resultado = $this->usuarioService->actualizarUsuario($usuario, [
            'idusuario' => $request->idusuario,
            'tipousuario' => $request->tipousuario,
        ]);

        if ($resultado) {
            return redirect()->route('admin.usuarios.index')
                ->with('success', 'Usuario actualizado correctamente.');
        }

        return back()->with('error', 'Error al actualizar el usuario.');
    }

    /**
     * Resetear contraseña
     */
    public function resetPassword(Request $request, $usuario)
    {
        $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ]);

        $resultado = $this->usuarioService->resetearPassword($usuario, $request->password);

        if ($resultado) {
            return redirect()->route('admin.usuarios.index')
                ->with('success', 'Contraseña actualizada correctamente.');
        }

        return back()->with('error', 'Error al actualizar la contraseña.');
    }
}
