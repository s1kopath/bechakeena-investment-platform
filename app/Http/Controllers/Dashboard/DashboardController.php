<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Investor dashboard home (Phase 0 smoke test).
     *
     * Real portfolio summary is built in Phase 5. Auth middleware is added in Phase 1.
     */
    public function index(): Response
    {
        return Inertia::render('Dashboard/Index', [
            'appName' => config('app.name'),
        ]);
    }
}
