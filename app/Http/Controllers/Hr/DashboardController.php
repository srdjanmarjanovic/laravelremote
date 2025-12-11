<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Company;
use App\Models\Position;
use App\Models\PositionView;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the HR dashboard.
     */
    public function __invoke(): Response
    {
        $user = auth()->user();

        // Get companies the user has access to
        $companyIds = $user->isAdmin()
            ? Company::pluck('id')
            : $user->companies->pluck('id');

        // Get recent positions
        $recentPositions = Position::query()
            ->whereIn('company_id', $companyIds)
            ->with(['company'])
            ->withCount('applications')
            ->latest()
            ->limit(5)
            ->get();

        // Calculate stats
        $totalPositions = Position::whereIn('company_id', $companyIds)->count();
        $publishedPositions = Position::whereIn('company_id', $companyIds)
            ->where('status', 'published')
            ->count();
        $draftPositions = Position::whereIn('company_id', $companyIds)
            ->where('status', 'draft')
            ->count();

        $positionIds = Position::whereIn('company_id', $companyIds)->pluck('id');
        $totalApplications = Application::whereIn('position_id', $positionIds)->count();
        $pendingApplications = Application::whereIn('position_id', $positionIds)
            ->where('status', 'pending')
            ->count();
        $totalViews = PositionView::whereIn('position_id', $positionIds)->count();

        // Get primary company for HR users
        $company = $user->isAdmin() ? null : $user->primaryCompany();

        return Inertia::render('Hr/Dashboard', [
            'company' => $company ? [
                'name' => $company->name,
                'description' => $company->description,
            ] : null,
            'positions' => $recentPositions,
            'stats' => [
                'total_positions' => $totalPositions,
                'published_positions' => $publishedPositions,
                'draft_positions' => $draftPositions,
                'total_applications' => $totalApplications,
                'pending_applications' => $pendingApplications,
                'total_views' => $totalViews,
            ],
        ]);
    }
}
