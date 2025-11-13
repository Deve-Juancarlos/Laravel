<?php

namespace App\Http\Controllers\Contabilidad;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Contabilidad\ContadorDashboardService; 
use Illuminate\Support\Facades\Log;

class ContadorDashboardController extends Controller
{
    /**
     * @var ContadorDashboardService
     */
    protected $dashboardService;

    public function __construct(ContadorDashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }
    
    public function contadorDashboard(Request $request)
    {
        try {

            $data = $this->dashboardService->getDashboardData();
            
            return view('dashboard.contador', $data);

        } catch (\Exception $e) {
            Log::error('Error en ContadorDashboardController: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            
            return view('dashboard.contador', $this->dashboardService->getDatosVacios());
        }
    }

    
    public function getStats(Request $request)
    {
        try {
          
            $stats = $this->dashboardService->getApiStats();
            
            return response()->json(['success' => true, 'data' => $stats]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas: ' . $e->getMessage()
            ], 500);
        }
    }

    
    public function clearCache()
    {
        try {
           
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

    public function getChartData()
    {
        try {
            // El servicio ya tiene estas funciones individuales cacheadas
            $data = [
                'labels' => $this->dashboardService->obtenerMesesLabels(6),
                'ventas' => $this->dashboardService->obtenerVentasPorMes(6),
                'cobranzas' => $this->dashboardService->obtenerCobranzasPorMes(6),
            ];
            
            return response()->json($data);

        } catch (\Exception $e) {
            Log::error('Error en getChartData: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener datos del gráfico'
            ], 500);
        }
    }
}