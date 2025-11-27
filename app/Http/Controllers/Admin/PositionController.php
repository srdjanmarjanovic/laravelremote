<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ListingType;
use App\Http\Controllers\Controller;
use App\Models\Position;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PositionController extends Controller
{
    /**
     * Display a listing of all positions.
     */
    public function index(Request $request): Response
    {
        $query = Position::query()
            ->with(['company', 'creator', 'technologies'])
            ->withCount('applications');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filter by company
        if ($request->filled('company_id')) {
            $query->where('company_id', $request->input('company_id'));
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('short_description', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $positions = $query->paginate(20)->withQueryString();

        return Inertia::render('Admin/Positions/Index', [
            'positions' => $positions,
            'filters' => $request->only(['status', 'company_id', 'search', 'sort_by', 'sort_order']),
        ]);
    }

    /**
     * Feature a position.
     */
    public function feature(Position $position): RedirectResponse
    {
        $this->authorize('feature', $position);

        $newType = $position->listing_type === ListingType::Featured
            ? ListingType::Regular
            : ListingType::Featured;

        $position->update(['listing_type' => $newType]);

        $message = $position->listing_type === ListingType::Featured
            ? 'Position featured successfully.'
            : 'Position unfeatured successfully.';

        return back()->with('message', $message);
    }

    /**
     * Archive a position.
     */
    public function archive(Position $position): RedirectResponse
    {
        $this->authorize('archive', $position);

        $position->update(['status' => 'archived']);

        return back()->with('message', 'Position archived successfully.');
    }

    /**
     * Bulk actions on positions.
     */
    public function bulkAction(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'action' => ['required', 'in:feature,unfeature,archive,delete'],
            'position_ids' => ['required', 'array'],
            'position_ids.*' => ['exists:positions,id'],
        ]);

        $positions = Position::whereIn('id', $validated['position_ids'])->get();

        foreach ($positions as $position) {
            switch ($validated['action']) {
                case 'feature':
                    $this->authorize('feature', $position);
                    $position->update(['listing_type' => ListingType::Featured]);
                    break;
                case 'unfeature':
                    $this->authorize('feature', $position);
                    $position->update(['listing_type' => ListingType::Regular]);
                    break;
                case 'archive':
                    $this->authorize('archive', $position);
                    $position->update(['status' => 'archived']);
                    break;
                case 'delete':
                    $this->authorize('delete', $position);
                    $position->delete();
                    break;
            }
        }

        $count = count($validated['position_ids']);
        $message = "Successfully performed {$validated['action']} action on {$count} position(s).";

        return back()->with('message', $message);
    }
}
