<?php

namespace App\Http\Controllers\Contabilidad;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Contabilidad\EstadoResultadosService; // 1. Importamos el servicio
use Illuminate\Support\Facades\Log;

class EstadoResultadosController extends Controller
{
    protected $service;

    // 2. Inyectamos el servicio
    public function __construct(EstadoResultadosService $service)
    {
        $this->service = $service;
    }

    /**
     * Muestra el Estado de Resultados principal.
     */
    public function index(Request $request)
    {
        try {
            // 3. Delegamos toda la lógica al servicio
            $data = $this->service->getEstadoResultadosData($request->all());
            
            return view('contabilidad.libros.estados-financieros.resultados', $data);

        } catch (\Exception $e) {
            Log::error('Error en EstadoResultadosController@index: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return back()->with('error', 'Error al cargar Estado de Resultados: ' . $e->getMessage());
        }
    }

    /**
     * Muestra el Estado de Resultados por períodos (mensual).
     */
    public function porPeriodos(Request $request)
    {
        try {
            // 3. Delegamos toda la lógica al servicio
            $data = $this->service->getResultadosPorPeriodos($request->all());
            
            return view('contabilidad.libros.estados-financieros.periodos', $data);

        } catch (\Exception $e) {
            Log::error('Error en EstadoResultadosController@porPeriodos: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return back()->with('error', 'Error al cargar resultados por períodos: ' . $e->getMessage());
        }
    }

    /**
     * Muestra el comparativo de Estado de Resultados.
     */
    public function comparativo(Request $request)
    {
        try {
            // 3. Delegamos toda la lógica al servicio
            $data = $this->service->getComparativoData($request->all());
            
            // Pasamos las variables que la vista espera
            return view('contabilidad.libros.estados-financieros.comparativo', [
                'comparativo' => $data['comparativo'],
                'periodos' => $data['periodos'],
                'variaciones' => $data['variaciones']
            ]);

        } catch (\Exception $e) {
            Log::error('Error en EstadoResultadosController@comparativo: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return back()->with('error', 'Error al cargar comparativo: ' . $e->getMessage());
        }
    }

    /**
     * Muestra el detalle de una cuenta específica (Ingreso o Gasto).
     */
    public function detalleCuenta(Request $request, $cuenta)
    {
        try {
            // 3. Delegamos toda la lógica al servicio
            $data = $this->service->getDetalleCuentaData($request->all(), $cuenta);
            
            return view('contabilidad.libros.estados-financieros.detalle', $data);

        } catch (\Exception $e) {
            Log::error('Error en EstadoResultadosController@detalleCuenta: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return back()->with('error', 'Error al cargar detalles de la cuenta: ' . $e->getMessage());
        }
    }

    /**
     * Exporta el Estado de Resultados.
     */
    public function exportar(Request $request)
    {
        // Esta función no estaba implementada, la delegamos al servicio
        try {
            return $this->service->exportar($request->all());
        } catch (\Exception $e) {
            Log::error('Error en EstadoResultadosController@exportar: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return back()->with('error', 'Error al exportar: ' . $e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Métodos Movidos
    |--------------------------------------------------------------------------
    |
    | Las funciones 'balanceGeneral' y 'cashFlow' se movieron a sus
    | propios controladores (BalanceGeneralController y FlujoCajaController)
    | para mantener la arquitectura limpia.
    |
    */
}
