<?php

namespace App\Http\Controllers\Contabilidad;

use App\Http\Controllers\Controller;
use App\Models\ClienteReniec;
use App\Http\Requests\ClienteRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ClienteController extends Controller
{
    public function __construct()
    {
       
        $this->middleware(function ($request, $next) {
            $user = Auth::user();
            $rol = strtolower($user->rol ?? $user->tipousuario ?? '');

            if (!in_array($rol, ['admin', 'contador'])) {
                abort(403, 'Acceso denegado');
            }

            return $next($request);
        });

    }

    /**
     * Mostrar lista de clientes
     */
    public function index(Request $request)
    {
        try {
            $query = ClienteReniec::query()
                ->with(['cuentas:id,cliente_id', 'usuario:id,usuario'])
                ->orderBy('created_at', 'desc');

            // Filtros
            if ($request->filled('buscar')) {
                $buscar = $request->buscar;
                $query->where(function($q) use ($buscar) {
                    $q->where('razon_social', 'LIKE', "%$buscar%")
                      ->orWhere('nombres', 'LIKE', "%$buscar%")
                      ->orWhere('apellido_paterno', 'LIKE', "%$buscar%")
                      ->orWhere('apellido_materno', 'LIKE', "%$buscar%")
                      ->orWhere('numero_documento', 'LIKE', "%$buscar%");
                });
            }

            if ($request->filled('tipo_documento')) {
                $query->where('tipo_documento', $request->tipo_documento);
            }

            if ($request->filled('estado')) {
                $query->where('estado', $request->estado);
            }

            $clientes = $query->paginate(15);
            $contadores = [
                'total' => ClienteReniec::count(),
                'activos' => ClienteReniec::where('estado', 1)->count(),
                'dni' => ClienteReniec::where('tipo_documento', 'DNI')->count(),
                'ruc' => ClienteReniec::where('tipo_documento', 'RUC')->count()
            ];

            return view('contabilidad.clientes.index', compact('clientes', 'contadores'));
            
        } catch (\Exception $e) {
            Log::error('Error en ClienteController@index: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar los clientes');
        }
    }

    /**
     * Formulario de creación manual
     */
    public function create()
    {
        return view('contabilidad.clientes.crear');
    }

    /**
     * Guardar cliente manual
     */
    public function store(ClienteRequest $request)
    {
        try {
            DB::beginTransaction();

            $cliente = ClienteReniec::create([
                'tipo_documento' => strtoupper($request->tipo_documento),
                'numero_documento' => $request->numero_documento,
                'nombres' => $request->nombres,
                'apellido_paterno' => $request->apellido_paterno,
                'apellido_materno' => $request->apellido_materno,
                'razon_social' => $request->razon_social,
                'direccion' => $request->direccion,
                'ubigeo' => $request->ubigeo,
                'departamento' => $request->departamento,
                'provincia' => $request->provincia,
                'distrito' => $request->distrito,
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'estado' => 1,
                'creado_por' => Auth::id()
            ]);

            DB::commit();

            return redirect()
                ->route('contabilidad.clientes.index')
                ->with('success', 'Cliente registrado exitosamente');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error guardando cliente: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Error al registrar el cliente');
        }
    }

    /**
     * Buscar cliente por DNI/RUC
     */
    public function buscar()
    {
        return view('contabilidad.clientes.buscar');
    }

    /**
     * Consultar cliente en base de datos
     */
    public function consultar(Request $request)
    {
        try {
            $request->validate([
                'documento' => 'required|digits_between:8,11|numeric'
            ], [
                'documento.required' => 'El documento es obligatorio',
                'documento.digits_between' => 'El documento debe tener entre 8 y 11 dígitos',
                'documento.numeric' => 'El documento debe ser numérico'
            ]);

            $documento = $request->documento;
            $tipoDocumento = strlen($documento) === 8 ? 'DNI' : 'RUC';

            $cliente = ClienteReniec::where('numero_documento', $documento)
                ->where('tipo_documento', $tipoDocumento)
                ->where('estado', 1)
                ->first();

            if (!$cliente) {
                return response()->json([
                    'encontrado' => false,
                    'mensaje' => 'No se encontró un cliente registrado con este documento'
                ]);
            }

            return response()->json([
                'encontrado' => true,
                'cliente' => [
                    'id' => $cliente->id,
                    'nombre_completo' => $cliente->getNombreCompleto(),
                    'numero_documento' => $cliente->numero_documento,
                    'tipo_documento' => $cliente->tipo_documento,
                    'direccion' => $cliente->direccion,
                    'ubigeo' => $cliente->ubigeo,
                    'departamento' => $cliente->departamento,
                    'provincia' => $cliente->provincia,
                    'distrito' => $cliente->distrito,
                    'fecha_nacimiento' => $cliente->fecha_nacimiento,
                    'edad' => $cliente->edad,
                    'ubicacion_completa' => $cliente->ubicacion_completa,
                    'es_mayor_edad' => $cliente->es_mayor_edad
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error consultando cliente: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error al consultar el cliente'
            ], 500);
        }
    }

    /**
     * Consultar DNI en RENIEC y crear cliente
     */
    public function consultarReniec(Request $request)
    {
        try {
            $request->validate([
                'dni' => 'required|digits:8|numeric'
            ], [
                'dni.required' => 'El DNI es obligatorio',
                'dni.digits' => 'El DNI debe tener 8 dígitos',
                'dni.numeric' => 'El DNI debe ser numérico'
            ]);

            
            $reniecController = app(ClienteController::class);
            $response = $reniecController->consultarDni($request);
            $data = $response->getData();

            if (!$data->success) {
                return response()->json([
                    'success' => false,
                    'message' => $data->message
                ]);
            }

            $reniecData = $data->data;

            // Verificar si ya existe
            $existe = ClienteReniec::where('numero_documento', $request->dni)
                ->where('tipo_documento', 'DNI')
                ->first();

            if ($existe) {
                return response()->json([
                    'success' => true,
                    'existe' => true,
                    'cliente' => [
                        'id' => $existe->id,
                        'nombre_completo' => $existe->getNombreCompleto(),
                        'numero_documento' => $existe->numero_documento,
                        'ubicacion_completa' => $existe->ubicacion_completa
                    ],
                    'message' => 'Cliente ya registrado en el sistema'
                ]);
            }

            return response()->json([
                'success' => true,
                'existe' => false,
                'reniec_data' => $reniecData
            ]);

        } catch (\Exception $e) {
            Log::error('Error consultando RENIEC: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al consultar en RENIEC'
            ], 500);
        }
    }

    /**
     * Crear cliente desde RENIEC
     */
    public function crearDesdeReniec(Request $request)
    {
        try {
            $request->validate([
                'reniec_data' => 'required|array'
            ]);

            DB::beginTransaction();

            $data = $request->reniec_data;

            $cliente = ClienteReniec::create([
                'tipo_documento' => 'DNI',
                'numero_documento' => $data['dni'],
                'nombres' => $data['nombres'] ?? null,
                'apellido_paterno' => $data['apellido_paterno'] ?? null,
                'apellido_materno' => $data['apellido_materno'] ?? null,
                'direccion' => $data['direccion_completa'] ?? null,
                'departamento' => $data['departamento'] ?? null,
                'provincia' => $data['provincia'] ?? null,
                'distrito' => $data['distrito'] ?? null,
                'ubigeo' => $data['ubigeo'] ?? null,
                'fecha_nacimiento' => $data['fecha_nacimiento'] ?? null,
                'estado' => 1,
                'creado_por' => Auth::id()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cliente creado exitosamente desde RENIEC',
                'cliente' => [
                    'id' => $cliente->id,
                    'nombre_completo' => $cliente->getNombreCompleto(),
                    'numero_documento' => $cliente->numero_documento
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error creando cliente desde RENIEC: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el cliente'
            ], 500);
        }
    }

    /**
     * Ver detalles del cliente
     */
    public function show($id)
    {
        try {
            $cliente = ClienteReniec::with(['cuentas', 'usuario'])
                ->findOrFail($id);

            return view('contabilidad.clientes.show', compact('cliente'));
            
        } catch (\Exception $e) {
            return back()->with('error', 'Cliente no encontrado');
        }
    }

    /**
     * Editar cliente
     */
    public function edit($id)
    {
        try {
            $cliente = ClienteReniec::findOrFail($id);
            return view('contabilidad.clientes.editar', compact('cliente'));
            
        } catch (\Exception $e) {
            return back()->with('error', 'Cliente no encontrado');
        }
    }

    /**
     * Actualizar cliente
     */
    public function update(ClienteRequest $request, $id)
    {
        try {
            $cliente = ClienteReniec::findOrFail($id);

            DB::beginTransaction();

            $cliente->update([
                'tipo_documento' => strtoupper($request->tipo_documento),
                'numero_documento' => $request->numero_documento,
                'nombres' => $request->nombres,
                'apellido_paterno' => $request->apellido_paterno,
                'apellido_materno' => $request->apellido_materno,
                'razon_social' => $request->razon_social,
                'direccion' => $request->direccion,
                'ubigeo' => $request->ubigeo,
                'departamento' => $request->departamento,
                'provincia' => $request->provincia,
                'distrito' => $request->distrito,
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'estado' => $request->estado
            ]);

            DB::commit();

            return redirect()
                ->route('contabilidad.clientes.index')
                ->with('success', 'Cliente actualizado exitosamente');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error actualizando cliente: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Error al actualizar el cliente');
        }
    }

    /**
     * Eliminar cliente (desactivar)
     */
    public function destroy($id)
    {
        try {
            $cliente = ClienteReniec::findOrFail($id);
            
            // Verificar si tiene cuentas activas
            $cuentasActivas = $cliente->cuentas()->count();
            
            if ($cuentasActivas > 0) {
                return back()->with('error', 
                    'No se puede eliminar el cliente porque tiene ' . $cuentasActivas . ' cuenta(s) asociada(s)');
            }

            $cliente->update(['estado' => 0]);

            return back()->with('success', 'Cliente desactivado exitosamente');

        } catch (\Exception $e) {
            Log::error('Error eliminando cliente: ' . $e->getMessage());
            return back()->with('error', 'Error al eliminar el cliente');
        }
    }
}