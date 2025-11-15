<?php

namespace App\Http\Controllers;

use App\Models\Position;
use App\Models\PositionView;
use App\Models\Technology;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class PublicPositionController extends Controller
{
    /**
     * Display a listing of positions.
     */
    public function index(Request $request): View
    {
        $query = Position::query()
            ->where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->with(['company', 'technologies'])
            ->withCount('applications');

        // Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('short_description', 'like', "%{$search}%")
                    ->orWhere('long_description', 'like', "%{$search}%");
            });
        }

        // Filter by technology
        if ($request->filled('technology')) {
            $query->whereHas('technologies', function ($q) use ($request) {
                $q->where('slug', $request->input('technology'));
            });
        }

        // Filter by seniority
        if ($request->filled('seniority')) {
            $query->where('seniority', $request->input('seniority'));
        }

        // Filter by remote type
        if ($request->filled('remote_type')) {
            $query->where('remote_type', $request->input('remote_type'));
        }

        // Filter by salary range
        if ($request->filled('min_salary')) {
            $query->where('salary_max', '>=', $request->input('min_salary'));
        }

        // Sort
        $sortBy = $request->input('sort', 'published_at');
        $sortOrder = $request->input('order', 'desc');

        if ($sortBy === 'featured') {
            $query->orderBy('is_featured', 'desc')
                ->orderBy('published_at', 'desc');
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        $positions = $query->paginate(20)->withQueryString();

        $technologies = Technology::orderBy('name')->get();

        return view('positions.index', [
            'positions' => $positions,
            'technologies' => $technologies,
            'filters' => $request->only(['search', 'technology', 'seniority', 'remote_type', 'min_salary', 'sort', 'order']),
        ]);
    }

    /**
     * Display the specified position.
     */
    public function show(Request $request, string $slug): View
    {
        $position = Position::where('slug', $slug)
            ->where('status', 'published')
            ->with(['company', 'technologies', 'customQuestions'])
            ->firstOrFail();

        // Track view (anonymized)
        $this->trackView($request, $position);

        $position->loadCount('applications');

        return view('positions.show', [
            'position' => $position,
        ]);
    }

    /**
     * Track position view (anonymized).
     */
    protected function trackView(Request $request, Position $position): void
    {
        $ipAddress = $request->ip();
        $ipHash = Hash::make($ipAddress);

        // Only track one view per IP per position per day
        $today = now()->startOfDay();
        $existingView = PositionView::where('position_id', $position->id)
            ->where('ip_address_hash', $ipHash)
            ->where('viewed_at', '>=', $today)
            ->exists();

        if ($existingView) {
            return;
        }

        PositionView::create([
            'position_id' => $position->id,
            'ip_address_hash' => $ipHash,
            'user_agent' => $request->userAgent(),
            'referrer' => $request->header('referer'),
            'viewed_at' => now(),
        ]);
    }
}
