<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ListingType;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePositionRequest;
use App\Http\Requests\UpdatePositionRequest;
use App\Models\Company;
use App\Models\Position;
use App\Models\Technology;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
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

        // Admin position listing logic:
        // - Show only admin's own draft positions (not other companies' drafts)
        // - Show all published/expired/archived positions from everyone
        $query->where(function ($q) {
            $q->where('status', '!=', 'draft')
                ->orWhere(function ($subQ) {
                    $subQ->where('status', 'draft')
                        ->where('created_by_user_id', auth()->id());
                });
        });

        // Filter by status
        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status === 'draft') {
                // For draft filter, only show admin's own drafts
                $query->where('status', 'draft')
                    ->where('created_by_user_id', auth()->id());
            } else {
                $query->where('status', $status);
            }
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
     * Show the form for creating a new position (admin - no cost).
     */
    public function create(): Response
    {
        $companies = Company::orderBy('name')->get();
        $technologies = Technology::orderBy('name')->get();

        return Inertia::render('Admin/Positions/Create', [
            'companies' => $companies,
            'technologies' => $technologies->toArray(),
        ]);
    }

    /**
     * Store a newly created position (admin - bypasses payment).
     */
    public function store(StorePositionRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Generate slug from title
        $validated['slug'] = Str::slug($validated['title']);

        // Ensure slug is unique
        $originalSlug = $validated['slug'];
        $counter = 1;
        while (Position::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $originalSlug.'-'.$counter;
            $counter++;
        }

        // Set creator
        $validated['created_by_user_id'] = auth()->id();

        // Admin can set status and expiration directly (no payment required)
        $validated['status'] = $request->input('status', 'draft');
        if ($validated['status'] === 'published' && ! $request->filled('published_at')) {
            $validated['published_at'] = now();
        }

        // Admin can set expiration date
        if ($request->filled('expires_at')) {
            $validated['expires_at'] = $request->input('expires_at');
        } elseif ($validated['status'] === 'published' && ! $request->filled('expires_at')) {
            // Default to 30 days if published without expiration
            $validated['expires_at'] = now()->addDays(30);
        }

        // Admin can set listing type directly (no payment)
        if ($request->filled('listing_type')) {
            $validated['listing_type'] = ListingType::from($request->input('listing_type'));
        }

        // Extract technology IDs and custom questions
        $technologyIds = $validated['technology_ids'] ?? [];
        $customQuestions = $validated['custom_questions'] ?? [];

        unset($validated['technology_ids'], $validated['custom_questions']);

        // Create the position
        $position = Position::create($validated);

        // Attach technologies
        if (! empty($technologyIds)) {
            $position->technologies()->attach($technologyIds);
        }

        // Create custom questions
        foreach ($customQuestions as $index => $question) {
            $position->customQuestions()->create([
                'question_text' => $question['question_text'],
                'is_required' => $question['is_required'] ?? false,
                'order' => $question['order'] ?? $index,
            ]);
        }

        return redirect()->route('admin.positions.index')
            ->with('message', 'Position created successfully.');
    }

    /**
     * Show the form for editing a position.
     */
    public function edit(Position $position): Response
    {
        $this->authorize('update', $position);

        $companies = Company::orderBy('name')->get();
        $technologies = Technology::orderBy('name')->get();

        $position->load(['technologies', 'customQuestions']);

        return Inertia::render('Admin/Positions/Edit', [
            'position' => $position,
            'companies' => $companies,
            'technologies' => $technologies,
        ]);
    }

    /**
     * Update the specified position.
     */
    public function update(UpdatePositionRequest $request, Position $position): RedirectResponse
    {
        $this->authorize('update', $position);

        $validated = $request->validated();

        // Update slug if title changed
        if ($validated['title'] !== $position->title) {
            $validated['slug'] = Str::slug($validated['title']);

            // Ensure slug is unique
            $originalSlug = $validated['slug'];
            $counter = 1;
            while (Position::where('slug', $validated['slug'])
                ->where('id', '!=', $position->id)
                ->exists()) {
                $validated['slug'] = $originalSlug.'-'.$counter;
                $counter++;
            }
        }

        // Admin can change status and expiration
        if ($request->filled('status')) {
            $validated['status'] = $request->input('status');
            if ($validated['status'] === 'published' && ! $position->published_at) {
                $validated['published_at'] = now();
            }
        }

        if ($request->filled('expires_at')) {
            $validated['expires_at'] = $request->input('expires_at');
        }

        // Admin can change listing type freely (no payment)
        if ($request->filled('listing_type')) {
            $validated['listing_type'] = ListingType::from($request->input('listing_type'));
        }

        // Extract technology IDs and custom questions
        $technologyIds = $validated['technology_ids'] ?? [];
        $customQuestions = $validated['custom_questions'] ?? [];

        unset($validated['technology_ids'], $validated['custom_questions']);

        // Update the position
        $position->update($validated);

        // Sync technologies
        $position->technologies()->sync($technologyIds);

        // Handle custom questions
        $existingQuestionIds = [];
        foreach ($customQuestions as $index => $question) {
            if (isset($question['_destroy']) && $question['_destroy']) {
                if (isset($question['id'])) {
                    $position->customQuestions()->where('id', $question['id'])->delete();
                }

                continue;
            }

            if (isset($question['id'])) {
                $position->customQuestions()->where('id', $question['id'])->update([
                    'question_text' => $question['question_text'],
                    'is_required' => $question['is_required'] ?? false,
                    'order' => $question['order'] ?? $index,
                ]);
                $existingQuestionIds[] = $question['id'];
            } else {
                $newQuestion = $position->customQuestions()->create([
                    'question_text' => $question['question_text'],
                    'is_required' => $question['is_required'] ?? false,
                    'order' => $question['order'] ?? $index,
                ]);
                $existingQuestionIds[] = $newQuestion->id;
            }
        }

        // Delete questions that weren't in the update
        if (! empty($customQuestions)) {
            $position->customQuestions()
                ->whereNotIn('id', $existingQuestionIds)
                ->delete();
        }

        return redirect()->route('admin.positions.index')
            ->with('message', 'Position updated successfully.');
    }

    /**
     * Update listing tier (Regular/Featured/Top).
     */
    public function updateTier(Request $request, Position $position): RedirectResponse
    {
        $this->authorize('feature', $position);

        $validated = $request->validate([
            'listing_type' => ['required', 'string', 'in:regular,featured,top'],
        ]);

        $position->update([
            'listing_type' => ListingType::from($validated['listing_type']),
        ]);

        return back()->with('message', 'Position tier updated successfully.');
    }

    /**
     * Extend expiration date.
     */
    public function extendExpiration(Request $request, Position $position): RedirectResponse
    {
        $this->authorize('update', $position);

        $validated = $request->validate([
            'days' => ['required', 'integer', 'min:1', 'max:365'],
        ]);

        $newExpiration = $position->expires_at
            ? $position->expires_at->addDays($validated['days'])
            : now()->addDays($validated['days']);

        $position->update(['expires_at' => $newExpiration]);

        return back()->with('message', "Position expiration extended by {$validated['days']} days.");
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
