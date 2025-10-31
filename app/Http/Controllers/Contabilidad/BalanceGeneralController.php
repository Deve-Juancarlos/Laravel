<?php

namespace App\Http\Controllers\Contabilidad;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Contabilidad\BalanceGeneralService; // 1. Importamos el nuevo servicio
use Illuminate\Support\Facades\Log;

class BalanceGeneralController extends Controller
{
    protected $service;

    // 2. Inyectamos el servicio
    public function __construct(BalanceGeneralService $service)
    {
        $this->service = $service;
    }

    /**
     * Muestra el Balance General.
     */
    public function index(Request $request)
    {
        try {
            // 3. Delegamos toda la lógica al servicio
            $data = $this->service->getBalanceGeneralData($request->all());
            
            return view('contabilidad.libros.estados-financieros.balance-general-index', $data);

        } catch (\Exception $e) {
            Log::error('Error en BalanceGeneralController@index: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return back()->with('error', 'Error al cargar balance general: ' . $e->getMessage());
        }
    }
}
