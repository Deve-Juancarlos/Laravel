<?php

namespace App\Http\Controllers\Contabilidad;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
// ¡Importar el Servicio!
use App\Services\Contabilidad\BalanceComprobacionService;
use Illuminate\Support\Facades\Log;

class BalanceComprobacionController extends Controller
{
    protected $balanceService;

    // Inyectar el servicio
    public function __construct(BalanceComprobacionService $balanceService)
    {
        $this->balanceService = $balanceService;
    }

    /**
     * Muestra el Balance de Comprobación principal.
     */
    public function index(Request $request)
    {
        try {
            $data = $this->balanceService->getBalanceData($request->all());
            return view('contabilidad.libros.balance-comprobacion.index', $data);
        } catch (\Exception $e) {
            Log::error('Error en BalanceComprobacionController@index: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar el balance: ' . $e->getMessage());
        }
    }

    /**
     * Muestra el detalle de movimientos de todas las cuentas.
     */
    public function detalleCuenta(Request $request)
    {
        try {
            $data = $this->balanceService->getDetalleCuentaData($request->all());
            return view('contabilidad.libros.balance-comprobacion.detalle', $data);
        } catch (\Exception $e) {
            Log::error('Error en BalanceComprobacionController@detalleCuenta: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar el detalle: ' . $e->getMessage());
        }
    }

    /**
     * Muestra el balance agrupado por clases (Activo, Pasivo, etc.).
     */
    public function porClases(Request $request)
    {
        try {
            $data = $this->balanceService->getPorClasesData($request->all());
            return view('contabilidad.libros.balance-comprobacion.clases', $data);
        } catch (\Exception $e) {
            Log::error('Error en BalanceComprobacionController@porClases: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar el reporte por clases: ' . $e->getMessage());
        }
    }

    /**
     * Muestra la comparación del balance entre dos períodos.
     */
    public function comparacion(Request $request)
    {
        try {
            $data = $this->balanceService->getComparacionData($request->all());
            return view('contabilidad.libros.balance-comprobacion.comparacion', $data);
        } catch (\Exception $e) {
            Log::error('Error en BalanceComprobacionController@comparacion: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al generar la comparación: ' . $e->getMessage());
        }
    }

    /**
     * Muestra la página de verificación de salud contable.
     */
    public function verificar(Request $request)
    {
        try {
            $data = $this->balanceService->getVerificacionData($request->all());
            return view('contabilidad.libros.balance-comprobacion.verificar', $data);
        } catch (\Exception $e) {
            Log::error('Error en BalanceComprobacionController@verificar: ' . $e->getMessage());
            return back()->with('error', 'Error en verificación: ' . $e->getMessage());
        }
    }

    /**
     * Maneja la exportación de los diferentes reportes del balance.
     */
    public function exportar(Request $request)
    {
        try {
            // ¡ACTUALIZADO! Se pasa el request completo al servicio.
            return $this->balanceService->exportar($request->all());
        } catch (\Exception $e) {
            Log::error('Error en BalanceComprobacionController@exportar: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al generar la exportación: ' . $e->getMessage());
        }
    }
}

