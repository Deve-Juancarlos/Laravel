<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Admin\DashboardService;
use Illuminate\Support\Facades\Log;

class AdminDashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index()
    {
        try {

          
            $data = $this->dashboardService->getDashboardData();

           
            return view('admin.dashboard.index', $data);

        } catch (\Exception $e) {
            Log::error("Error al cargar Dashboard Admin: " . $e->getMessage());
            return back()->with('error', 'Error al cargar el dashboard: ' . $e->getMessage());
        }
    }
}
