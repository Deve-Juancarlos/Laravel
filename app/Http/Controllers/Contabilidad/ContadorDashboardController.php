<?php

namespace App\Http\Controllers\Contabilidad; // <-- 1. Movido a su módulo

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Contabilidad\ContadorDashboardService; // <-- 2. Importamos el nuevo Servicio
use Illuminate\Support\Facades\Log;

class ContadorDashboardController extends Controller
{
    /**
     * @var ContadorDashboardService
     */
    protected $dashboardService;

    /**
     * 3. Inyectamos el Servicio en el constructor.
     * Laravel lo hará automáticamente por nosotros.
     */
    public function __construct(ContadorDashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Muestra el dashboard principal.
     * Fíjate qué limpio queda.
     */
    public function contadorDashboard(Request $request)
    {

        
        try {
            // 4. El controlador ya no sabe CÓMO se obtienen los datos,
            // solo los PIDE al servicio.
            $data = $this->dashboardService->getDashboardData();
            
            return view('dashboard.contador', $data);

        } catch (\Exception $e) {
            Log::error('Error en ContadorDashboardController: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            // 5. El servicio también nos da los datos vacíos en caso de error.
            return view('dashboard.contador', $this->dashboardService->getDatosVacios());
        }
    }

    /**
     * Endpoint de API para estadísticas rápidas.
     */
    public function getStats(Request $request)
    {
        try {
            // 6. El servicio se encarga de esto también.
            $stats = $this->dashboardService->getApiStats();
            
            return response()->json(['success' => true, 'data' => $stats]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Limpia el cache del dashboard.
     */
    public function clearCache()
    {
        try {
            // 7. El servicio maneja su propio cache.
            $this->dashboardService->clearDashboardCache();
            
            return response()->json([
                'success' => true,
                'message' => 'Cache limpiado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al limpiar cache: ' . $e->getMessage()
            ], 500);
        }
    }
}
