<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Company;
use App\Models\Position;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function __invoke(Request $request): Response
    {
        // Statistics
        $stats = [
            'total_positions' => Position::count(),
            'active_positions' => Position::where('status', 'published')
                ->where(function ($q) {
                    $q->whereNull('expires_at')
                        ->orWhere('expires_at', '>', now());
                })
                ->count(),
            'total_applications' => Application::count(),
            'pending_applications' => Application::where('status', 'pending')->count(),
            'total_companies' => Company::count(),
            'total_developers' => User::where('role', 'developer')->count(),
            'total_hrs' => User::where('role', 'hr')->count(),
        ];

        // Recent positions
        $recentPositions = Position::with(['company', 'creator'])
            ->withCount('applications')
            ->latest()
            ->limit(10)
            ->get();

        // Recent applications
        $recentApplications = Application::with(['position.company', 'user'])
            ->latest('applied_at')
            ->limit(10)
            ->get();

        // Position stats by status
        $positionsByStatus = Position::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        // Application stats by status
        $applicationsByStatus = Application::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'recentPositions' => $recentPositions,
            'recentApplications' => $recentApplications,
            'positionsByStatus' => $positionsByStatus,
            'applicationsByStatus' => $applicationsByStatus,
        ]);
    }
}
