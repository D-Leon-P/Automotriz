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
        $this->middleware('permission:ver_dashboard_propio,ver_dashboard_todos');
        $this->dashboardService = $dashboardService;
    }

    public function index()
    {
        $user = Auth::user();
        $empleadoId = $user->hasPermission('ver_dashboard_todos') ? null : $user->id;
        $metrics = $this->dashboardService->getMetrics($empleadoId);
        return response()->json($metrics);
    }
}
