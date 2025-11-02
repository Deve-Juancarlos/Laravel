<?php

namespace App\Http\Controllers\Contabilidad;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Contabilidad\CajaService; // Importamos el servicio
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class CajaController extends Controller
{
    protected $cajaService;

    public function __construct(CajaService $cajaService)
    {
        $this->cajaService = $cajaService;
    }

    /**
     * Muestra el dashboard/index de Caja.
     */
    public function index(Request $request)
    {
        try {
            $data = $this->cajaService->getIndexData($request->all());
            return view('contabilidad.caja.index', $data);
        } catch (\Exception $e) {
            Log::error('Error en CajaController@index: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar el módulo de caja: ' . $e->getMessage());
        }
    }

    /**
     * Muestra el formulario para crear un nuevo movimiento.
     */
    public function create()
    {
        try {
            $data = $this->cajaService->getCreateData();
            return view('contabilidad.caja.create', $data);
        } catch (\Exception $e) {
            Log::error('Error en CajaController@create: ' . $e->getMessage());
            return redirect()->route('contador.caja.index')->with('error', 'Error al cargar el formulario.');
        }
    }

    /**
     * Guarda el nuevo movimiento en Caja y Libro Diario.
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'fecha' => 'required|date',
                'tipo' => 'required|integer|in:1,2', // 1=Ingreso, 2=Egreso
                'clase' => 'required|integer', // 1=Efectivo, etc
                'monto' => 'required|numeric|min:0.01',
                'razon_id' => 'required|string|exists:plan_cuentas,codigo', // Cuenta contrapartida
                'cuenta_caja' => 'required|string|exists:plan_cuentas,codigo',
                'documento' => 'nullable|string|max:50',
                'glosa' => 'required|string|max:255',
            ]);

            $movimiento = $this->cajaService->storeMovimiento($validatedData);

            return redirect()->route('contador.caja.show', $movimiento->Numero)
                ->with('success', 'Movimiento de caja registrado con éxito. Asiento #' . $movimiento->asiento_numero . ' creado.');

        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Error en CajaController@store: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al guardar el movimiento: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Muestra el detalle de un movimiento de caja.
     */
    public function show($id)
    {
        try {
            $data = $this->cajaService->getShowData($id);
            if (!$data['movimiento']) {
                return redirect()->route('contador.caja.index')->with('error', 'Movimiento no encontrado.');
            }
            return view('contabilidad.caja.show', $data);
        } catch (\Exception $e) {
            Log::error('Error en CajaController@show: ' . $e->getMessage());
            return redirect()->route('contador.caja.index')->with('error', 'Error al mostrar el movimiento.');
        }
    }

    /**
     * Muestra el formulario para editar un movimiento.
     * (Solo edita la glosa/referencia, no los montos para mantener integridad)
     */
    public function edit($id)
    {
        try {
            $data = $this->cajaService->getEditData($id);
            if (!$data['movimiento']) {
                return redirect()->route('contador.caja.index')->with('error', 'Movimiento no encontrado.');
            }
            return view('contabilidad.caja.edit', $data);
        } catch (\Exception $e) {
            Log::error('Error en CajaController@edit: ' . $e->getMessage());
            return redirect()->route('contador.caja.index')->with('error', 'Error al cargar el formulario de edición.');
        }
    }

    /**
     * Actualiza un movimiento de caja y su asiento.
     */
    public function update(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'fecha' => 'required|date',
                'documento' => 'nullable|string|max:50',
                'glosa' => 'required|string|max:255',
                // No se permite cambiar montos, tipo o cuentas para mantener integridad.
                // Se debe anular (destroy) y crear uno nuevo.
            ]);

            $this->cajaService->updateMovimiento($id, $validatedData);

            return redirect()->route('contador.caja.show', $id)
                ->with('success', 'Movimiento actualizado con éxito.');

        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Error en CajaController@update: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al actualizar: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Anula un movimiento de caja y su asiento contable.
     * (No lo borra, lo marca como Anulado/Eliminado)
     */
    public function destroy($id)
    {
        try {
            $this->cajaService->anularMovimiento($id);
            return redirect()->route('contador.caja.index')->with('success', 'Movimiento de caja y asiento contable anulados.');
        } catch (\Exception $e) {
            Log::error('Error en CajaController@destroy: ' . $e->getMessage());
            return redirect()->route('contador.caja.index')->with('error', 'Error al anular el movimiento: ' . $e->getMessage());
        }
    }
}
