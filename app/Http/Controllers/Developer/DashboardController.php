<?php

namespace App\Http\Controllers\Developer;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the developer dashboard.
     */
    public function __invoke(): Response
    {
        $user = auth()->user();

        // Load developer profile
        $profile = $user->developerProfile;

        // Get recent applications (without status field)
        $applications = $user->applications()
            ->with(['position.company'])
            ->latest('applied_at')
            ->limit(5)
            ->get()
            ->makeHidden('status');

        // Calculate stats (only total, no status breakdown)
        $stats = [
            'total_applications' => $user->applications()->count(),
        ];

        return Inertia::render('Developer/Dashboard', [
            'profile' => $profile ? [
                'summary' => $profile->summary,
                'cv_path' => $profile->cv_path,
            ] : null,
            'applications' => $applications,
            'stats' => $stats,
        ]);
    }
}
