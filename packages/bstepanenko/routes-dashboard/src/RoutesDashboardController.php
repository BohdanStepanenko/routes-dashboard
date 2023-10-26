<?php

namespace Bstepanenko\RoutesDashboard;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class RoutesDashboardController extends Controller
{
    private RouteDashboardService $routeDashboardService;

    public function __construct(RouteDashboardService $routeDashboardService)
    {
        $this->routeDashboardService = $routeDashboardService;
    }

    public function renderDashboardPage(): View
    {
        $routesData = $this->routeDashboardService->getSummaryData();

        return view('routes-dashboard::dashboard', compact('routesData'));
    }
}
