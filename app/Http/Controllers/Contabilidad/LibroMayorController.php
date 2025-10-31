<?php

namespace App\Http\Controllers\Contabilidad;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Contabilidad\LibroMayorService; // 1. Importamos el servicio
use Illuminate\Support\Facades\Log;

class LibroMayorController extends Controller
{
    protected $libroMayorService;

    // 2. Inyectamos el servicio en el constructor
    public function __construct(LibroMayorService $libroMayorService)
    {
        $this->libroMayorService = $libroMayorService;
    }

    /**
     * Vista principal del Libro Mayor (Resumen por Cuentas)
     */
    public function index(Request $request)
    {
        try {
            // 3. Delegamos toda la lógica al servicio
            $data = $this->libroMayorService->getMayorIndexData($request->all());
            
            return view('contabilidad.libros.mayor.index', $data);

        } catch (\Exception $e) {
            Log::error('Error en LibroMayorController@index: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return redirect()->back()->with('error', 'Error al cargar el libro mayor: ' . $e->getMessage());
        }
    }

    /**
     * Detalle de movimientos de una cuenta específica
     */
    public function cuenta(Request $request, $codigoCuenta)
    {
        try {
            // 3. Delegamos toda la lógica al servicio
            $data = $this->libroMayorService->getMayorCuentaData($request->all(), $codigoCuenta);

            if (isset($data['error'])) {
                return redirect()->route('contador.libro-mayor.index')
                         ->with('error', $data['error']);
            }

            return view('contabilidad.libros.mayor.cuenta', $data);

        } catch (\Exception $e) {
            Log::error('Error en LibroMayorController@cuenta: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return redirect()->back()->with('error', 'Error al cargar la cuenta: ' . $e->getMessage());
        }
    }

    /**
     * Exportar a Excel/CSV (manejado por el servicio)
     */
    public function exportar(Request $request)
    {
        try {
            // 3. Delegamos toda la lógica al servicio
            return $this->libroMayorService->exportar($request->all());

        } catch (\Exception $e) {
            Log::error('Error en LibroMayorController@exportar: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return redirect()->back()->with('error', 'Error al exportar: ' . $e->getMessage());
        }
    }

    /**
     * Exportar cuenta específica (manejado por el servicio)
     */
    public function exportarCuenta(Request $request, $codigoCuenta)
    {
         try {
            // 3. Delegamos toda la lógica al servicio
            return $this->libroMayorService->exportarCuenta($request->all(), $codigoCuenta);

        } catch (\Exception $e) {
            Log::error('Error en LibroMayorController@exportarCuenta: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return redirect()->back()->with('error', 'Error al exportar: ' . $e->getMessage());
        }
    }

    /**
     * Comparación entre períodos
     */
    public function comparacionPeriodos(Request $request)
    {
        try {
            // 3. Delegamos toda la lógica al servicio
            $data = $this->libroMayorService->getComparacionPeriodosData($request->all());
            
            return view('contabilidad.libros.mayor.comparacion', $data);

        } catch (\Exception $e) {
            Log::error('Error en LibroMayorController@comparacionPeriodos: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return redirect()->back()->with('error', 'Error al generar comparación de períodos: ' . $e->getMessage());
        }
    }

    

    /**
     * Movimientos del Libro Mayor (Detallado)
     */
    public function movimientos(Request $request)
    {
        try {
            // 3. Delegamos toda la lógica al servicio
            $data = $this->libroMayorService->getMovimientosData($request->all());
            
            return view('contabilidad.libros.mayor.movimientos', $data);

        } catch (\Exception $e) {
            Log::error('Error en LibroMayorController@movimientos: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return redirect()->back()->with('error', 'Error al cargar movimientos: ' . $e->getMessage());
        }
    }
}

