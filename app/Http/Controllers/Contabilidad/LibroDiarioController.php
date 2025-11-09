<?php

namespace App\Http\Controllers\Contabilidad;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Contabilidad\LibroDiarioService; // 1. Importamos el Servicio
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use App\Models\LibroDiario;

class LibroDiarioController extends Controller
{
    protected $libroDiarioService;

    // 2. Inyectamos el servicio
    public function __construct(LibroDiarioService $libroDiarioService)
    {
        $this->middleware('auth');
        $this->libroDiarioService = $libroDiarioService;
    }

    /**
     * Muestra el dashboard del libro diario.
     */
    public function index(Request $request)
    {
        try {
            // 3. El controlador solo pide los datos
            $data = $this->libroDiarioService->getDashboardData($request);
            
            return view('contabilidad.libros.diario.index', $data);

        } catch (\Exception $e) {
            Log::error('Error en LibroDiarioController@index: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar el libro diario: ' . $e->getMessage());
        }
    }

    /**
     * Muestra el formulario para crear un nuevo asiento.
     */
    public function create()
    {
        try {
            $data = $this->libroDiarioService->getCreateFormData();
            return view('contabilidad.libros.diario.create', $data);

        } catch (\Exception $e) {
            Log::error('Error en LibroDiarioController@create: ' . $e->getMessage());
            return redirect()->route('contador.libro-diario.index')
                ->with('error', 'Error al cargar el formulario de asiento');
        }
    }

    /**
     * Guarda el nuevo asiento en la base de datos.
     */
    public function store(Request $request)
    {
        // 4. La validación se queda en el controlador (o en un FormRequest)
        try {
            $validatedData = $request->validate([
                'fecha' => 'required|date',
                'glosa' => 'required|string|max:255',
                'detalles' => 'required|array|min:2',
                'detalles.*.cuenta_contable' => 'required|string',
                'detalles.*.debe' => 'nullable|numeric|min:0',
                'detalles.*.haber' => 'nullable|numeric|min:0',
                'detalles.*.concepto' => 'required|string|max:255',
                'detalles.*.documento_referencia' => 'nullable|string|max:100', // Agregado
                'observaciones' => 'nullable|string', // Agregado
            ]);

            // 5. Enviamos los datos validados al servicio
            $asientoId = $this->libroDiarioService->storeAsiento(
                $validatedData,
                $request->observaciones,
                auth()->id()
            );

            return redirect()->route('contador.libro-diario.show', $asientoId)
                ->with('success', 'Asiento contable registrado correctamente.');

        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Error al crear asiento contable: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al registrar el asiento: ' . $e->getMessage());
        }
    }

    /**
     * Muestra un asiento específico.
     */
    public function show($id)
    {
        try {
            $data = $this->libroDiarioService->getAsientoDetails($id);
            if (!$data) {
                 return redirect()->route('contador.libro-diario.index')
                    ->with('error', 'Asiento no encontrado');
            }
            return view('contabilidad.libros.diario.show', $data);

        } catch (\Exception $e) {
            Log::error('Error al mostrar asiento: ' . $e->getMessage());
            return redirect()->route('contador.libro-diario.index')
                ->with('error', 'Error al cargar el asiento');
        }
    }

    /**
     * Muestra el formulario para editar un asiento.
     */
    public function edit($id)
    {
        try {
            $data = $this->libroDiarioService->getEditFormData($id);
             if (!$data['asiento']) {
                 return redirect()->route('contador.libro-diario.index')
                    ->with('error', 'Asiento no encontrado');
            }
            return view('contabilidad.libros.diario.edit', $data);
            
        } catch (\Exception $e) {
            Log::error('Error al editar asiento: ' . $e->getMessage());
            return redirect()->route('contador.libro-diario.index')
                ->with('error', 'Error al cargar el asiento para edición');
        }
    }

    /**
     * Actualiza la cabecera de un asiento.
     */
    public function update(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'fecha' => 'required|date',
                'glosa' => 'required|string|max:500',
                'observaciones' => 'nullable|string|max:1000',
            ]);

            $this->libroDiarioService->updateAsiento($id, $validatedData);

            return redirect()->route('contador.libro-diario.show', $id)
                ->with('success', 'Asiento actualizado correctamente.');

        } catch (ValidationException $e) {
             return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Error al actualizar asiento: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al guardar los cambios: ' . $e->getMessage());
        }
    }

    /**
     * Elimina un asiento y sus detalles.
     */
    public function destroy($id)
    {
        try {
            // 1. Busca el asiento con Eloquent
            $asiento = LibroDiario::findOrFail($id);
            $usuarioActual = auth()->user()->usuario ?? 'Sistema';

            // 2. Cambia el estado a "Pendiente"
            $asiento->estado = 'PENDIENTE_ELIMINACION';
            $asiento->save(); // ¡Esto disparará el Observer y auditará el CAMBIO DE ESTADO!

            // 3. Prepara la notificación para el Admin
            $titulo = "Solicitud de Eliminación de Asiento";
            $mensaje = "El usuario {$usuarioActual} ha solicitado eliminar el asiento N° {$asiento->numero}.";
            
            // ¡Esta URL apuntará a la nueva página de aprobación del Admin!
            $url = route('admin.solicitudes.index'); 

            // 4. Llama a la nueva función del Modelo para notificar
            $asiento->notificarAdmin($titulo, $mensaje, $url);
            
            return redirect()->route('contador.libro-diario.index')
                ->with('success', 'Solicitud de eliminación enviada al Administrador.');
                
        } catch (\Exception $e) {
            Log::error('Error al solicitar eliminación de asiento: ' + $e->getMessage());
            return redirect()->route('contador.libro-diario.index')
                ->with('error', 'Error al enviar la solicitud: ' + $e->getMessage());
        }
    }

    /**
     * Exporta el libro diario a PDF o Excel.
     */
    public function exportar(Request $request)
    {
        try {
            // 6. El servicio se encarga de generar y devolver la descarga
            return $this->libroDiarioService->export($request);
            
        } catch (\Exception $e) {
            Log::error('Error al exportar libro diario: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al generar el reporte');
        }
    }
}
