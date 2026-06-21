<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboard
    ) {}

    /** Tableau de bord Blade complet. */
    public function index(): View
    {
        $data = $this->dashboard->getDashboardData();

        return view('admin.dashboard.index', $data);
    }

    /** API JSON pour rafraîchir les graphiques (AJAX). */
    public function charts(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'charts'  => $this->dashboard->getChartsJson(),
        ]);
    }
}
