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

        // Get recent applications
        $applications = $user->applications()
            ->with(['position.company'])
            ->latest('applied_at')
            ->limit(5)
            ->get();

        // Calculate stats
        $stats = [
            'total_applications' => $user->applications()->count(),
            'pending' => $user->applications()->where('status', 'pending')->count(),
            'accepted' => $user->applications()->where('status', 'accepted')->count(),
            'rejected' => $user->applications()->where('status', 'rejected')->count(),
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
