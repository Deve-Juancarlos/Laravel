<?php

namespace App\Http\Controllers\Admin; // ¡Asegúrate que el namespace sea correcto!

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Admin\DashboardService; // 1. ¡Importamos el nuevo Servicio!
use Illuminate\Support\Facades\Log;

class AdminDashboardController extends Controller
{
    protected $dashboardService;

    // 2. ¡Inyectamos el Servicio!
    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }
    
    
    public function index()
    {
        try {
            // 3. ¡Llamamos al Servicio para obtener los KPIs!
            $kpis = $this->dashboardService->getDashboardKpis();
            
            // 4. Pasamos los KPIs a la vista
            return view('admin.dashboard.index', compact('kpis')); // ¡Asegúrate que la vista exista!

        } catch (\Exception $e) {
            Log::error("Error al cargar Dashboard Admin: " . $e->getMessage());
            return back()->with('error', 'Error al cargar el dashboard: ' . $e->getMessage());
        }
    }
}