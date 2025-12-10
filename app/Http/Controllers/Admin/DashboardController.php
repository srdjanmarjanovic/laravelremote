<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Company;
use App\Models\Payment;
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
        // Payment statistics
        $totalRevenue = Payment::where('status', PaymentStatus::Completed)->sum('amount');
        $pendingPayments = Payment::where('status', PaymentStatus::Pending)->sum('amount');
        $failedPayments = Payment::where('status', PaymentStatus::Failed)->sum('amount');
        $refundedPayments = Payment::where('status', PaymentStatus::Refunded)->sum('amount');
        $totalPayments = Payment::count();
        $completedPayments = Payment::where('status', PaymentStatus::Completed)->count();

        // Monthly revenue (last 6 months) - database agnostic approach
        $monthlyRevenue = Payment::where('status', PaymentStatus::Completed)
            ->where('created_at', '>=', now()->subMonths(6))
            ->get()
            ->groupBy(fn ($payment) => $payment->created_at->format('Y-m'))
            ->map(fn ($payments) => $payments->sum('amount'))
            ->sortKeys();

        // Payment stats by status
        $paymentsByStatus = Payment::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

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
            'total_revenue' => (float) $totalRevenue,
            'pending_payments' => (float) $pendingPayments,
            'failed_payments' => (float) $failedPayments,
            'refunded_payments' => (float) $refundedPayments,
            'total_payments' => $totalPayments,
            'completed_payments' => $completedPayments,
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
            'paymentsByStatus' => $paymentsByStatus,
            'monthlyRevenue' => $monthlyRevenue,
        ]);
    }
}
