<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->middleware('auth:api');
        $this->dashboardService = $dashboardService;
    }

    public function index()
    {
        $vendedorId = Auth::id();
        $metrics = $this->dashboardService->getMetrics($vendedorId);
        return response()->json($metrics);
    }
}
