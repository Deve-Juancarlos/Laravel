<?php

namespace App\Http\Controllers\Contabilidad;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Contabilidad\PlanCuentasService; // Importamos el Servicio
use Illuminate\Support\Facades\Log;

class PlanCuentasController extends Controller
{
    protected $planCuentasService;

    public function __construct(PlanCuentasService $planCuentasService)
    {
        $this->planCuentasService = $planCuentasService;
    }

    /**
     * Muestra el listado del Plan de Cuentas.
     */
    public function index(Request $request)
    {
        try {
            $data = $this->planCuentasService->get($request->all());
            return view('contabilidad.plan-cuentas.index', $data);
        } catch (\Exception $e) {
            Log::error('Error en PlanCuentasController@index: ' . $e->getMessage());
            return redirect()->route('dashboard.contador')->with('error', 'Error al cargar el Plan de Cuentas.');
        }
    }

    /**
     * Muestra el formulario para crear una nueva cuenta.
     */
    public function create()
    {
        $data = $this->planCuentasService->getFormData();
        return view('contabilidad.plan-cuentas.create', $data);
    }

    /**
     * Almacena la nueva cuenta en la base de datos.
     */
    public function store(Request $request)
    {
        // Validación (Básica, se puede expandir)
        $validatedData = $request->validate([
            'codigo' => 'required|string|max:20|unique:plan_cuentas,codigo',
            'nombre' => 'required|string|max:200',
            'tipo' => 'required|string|max:30',
            'subtipo' => 'nullable|string|max:30',
            'activo' => 'boolean',
            'nivel' => 'required|integer|min:1',
            'cuenta_padre' => 'nullable|string|max:20|exists:plan_cuentas,codigo',
        ]);
        
        // El checkbox 'activo' no envía valor si está desmarcado
        $validatedData['activo'] = $request->has('activo') ? 1 : 0;

        try {
            $this->planCuentasService->create($validatedData);
            return redirect()->route('contador.plan-cuentas.index')->with('success', 'Cuenta creada exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error en PlanCuentasController@store: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Error al crear la cuenta: ' . $e->getMessage());
        }
    }

    /**
     * Muestra el formulario para editar una cuenta existente.
     */
    public function edit($codigo)
    {
        $data = $this->planCuentasService->getFormData($codigo);

        if (!$data['cuenta']) {
            return redirect()->route('contador.plan-cuentas.index')->with('error', 'Cuenta no encontrada.');
        }
        
        return view('contabilidad.plan-cuentas.edit', $data);
    }

    /**
     * Actualiza una cuenta existente en la base de datos.
     */
    public function update(Request $request, $codigo)
    {
        $validatedData = $request->validate([
            'codigo' => 'required|string|max:20|unique:plan_cuentas,codigo,' . $codigo . ',codigo', // Ignorar el código actual
            'nombre' => 'required|string|max:200',
            'tipo' => 'required|string|max:30',
            'subtipo' => 'nullable|string|max:30',
            'activo' => 'boolean',
            'nivel' => 'required|integer|min:1',
            'cuenta_padre' => 'nullable|string|max:20|exists:plan_cuentas,codigo',
        ]);

        $validatedData['activo'] = $request->has('activo') ? 1 : 0;

        try {
            $this->planCuentasService->update($codigo, $validatedData);
            return redirect()->route('contador.plan-cuentas.index')->with('success', 'Cuenta actualizada exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error en PlanCuentasController@update: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Error al actualizar la cuenta: ' . $e->getMessage());
        }
    }

    /**
     * Elimina una cuenta (con validación).
     */
    public function destroy($codigo)
    {
        try {
            $result = $this->planCuentasService->delete($codigo);
            
            if (!$result['success']) {
                return redirect()->back()->with('error', $result['message']);
            }

            return redirect()->route('contador.plan-cuentas.index')->with('success', $result['message']);
        } catch (\Exception $e) {
            Log::error('Error en PlanCuentasController@destroy: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al eliminar la cuenta: ' . $e->getMessage());
        }
    }
}
