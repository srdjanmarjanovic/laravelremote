<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Payment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    /**
     * Display a listing of payments for the authenticated HR user.
     */
    public function index(Request $request): Response
    {
        $user = auth()->user();

        // Get companies the user has access to
        $companyIds = $user->isAdmin()
            ? Company::pluck('id')
            : $user->companies->pluck('id');

        $query = Payment::query()
            ->whereHas('position', function ($q) use ($companyIds) {
                $q->whereIn('company_id', $companyIds);
            })
            ->with(['position.company', 'user'])
            ->where('user_id', $user->id);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        // Filter by tier
        if ($request->filled('tier')) {
            $query->where('tier', $request->input('tier'));
        }

        // Search by position title
        if ($request->filled('search')) {
            $query->whereHas('position', function ($q) use ($request) {
                $q->where('title', 'like', '%'.$request->input('search').'%');
            });
        }

        // Sort
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $payments = $query->paginate(20)->withQueryString();

        return Inertia::render('Hr/Payments/Index', [
            'payments' => $payments,
            'filters' => $request->only(['status', 'type', 'tier', 'search', 'sort_by', 'sort_order']),
        ]);
    }
}
