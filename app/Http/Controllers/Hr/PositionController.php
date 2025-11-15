<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePositionRequest;
use App\Http\Requests\UpdatePositionRequest;
use App\Models\Company;
use App\Models\Position;
use App\Models\Technology;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PositionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $user = auth()->user();

        // Get companies the user has access to
        $companyIds = $user->isAdmin()
            ? Company::pluck('id')
            : $user->companies->pluck('id');

        $positions = Position::query()
            ->whereIn('company_id', $companyIds)
            ->with(['company', 'technologies', 'creator'])
            ->withCount('applications')
            ->latest()
            ->paginate(15);

        return Inertia::render('Hr/Positions/Index', [
            'positions' => $positions,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        $user = auth()->user();

        // Get companies the user can create positions for
        $companies = $user->isAdmin()
            ? Company::all()
            : $user->companies;

        $technologies = Technology::orderBy('name')->get();

        return Inertia::render('Hr/Positions/Create', [
            'companies' => $companies,
            'technologies' => $technologies,
        ]);
    }

    /**
     * Store a newly created resource in storage.
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

        // Set published_at if status is published
        if ($validated['status'] === 'published') {
            $validated['published_at'] = now();
        }

        // Extract technology IDs and custom questions before creating
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

        return redirect()->route('hr.positions.index')
            ->with('message', 'Position created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Position $position): Response
    {
        $this->authorize('view', $position);

        $position->load([
            'company',
            'technologies',
            'creator',
            'customQuestions',
            'applications' => function ($query) {
                $query->with('user')->latest();
            },
        ]);

        $position->loadCount([
            'applications',
            'views',
        ]);

        return Inertia::render('Hr/Positions/Show', [
            'position' => $position,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Position $position): Response
    {
        $this->authorize('update', $position);

        $user = auth()->user();

        // Get companies the user can assign to
        $companies = $user->isAdmin()
            ? Company::all()
            : $user->companies;

        $technologies = Technology::orderBy('name')->get();

        $position->load(['technologies', 'customQuestions']);

        return Inertia::render('Hr/Positions/Edit', [
            'position' => $position,
            'companies' => $companies,
            'technologies' => $technologies,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePositionRequest $request, Position $position): RedirectResponse
    {
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

        // Set published_at if status changed to published and not already set
        if ($validated['status'] === 'published' && ! $position->published_at) {
            $validated['published_at'] = now();
        }

        // Extract technology IDs and custom questions
        $technologyIds = $validated['technology_ids'] ?? [];
        $customQuestions = $validated['custom_questions'] ?? [];

        unset($validated['technology_ids'], $validated['custom_questions']);

        // Update the position
        $position->update($validated);

        // Sync technologies
        $position->technologies()->sync($technologyIds);

        // Handle custom questions (update existing, create new, delete marked)
        $existingQuestionIds = [];
        foreach ($customQuestions as $index => $question) {
            if (isset($question['_destroy']) && $question['_destroy']) {
                // Delete question if marked for destruction
                if (isset($question['id'])) {
                    $position->customQuestions()->where('id', $question['id'])->delete();
                }

                continue;
            }

            if (isset($question['id'])) {
                // Update existing question
                $position->customQuestions()->where('id', $question['id'])->update([
                    'question_text' => $question['question_text'],
                    'is_required' => $question['is_required'] ?? false,
                    'order' => $question['order'] ?? $index,
                ]);
                $existingQuestionIds[] = $question['id'];
            } else {
                // Create new question
                $newQuestion = $position->customQuestions()->create([
                    'question_text' => $question['question_text'],
                    'is_required' => $question['is_required'] ?? false,
                    'order' => $question['order'] ?? $index,
                ]);
                $existingQuestionIds[] = $newQuestion->id;
            }
        }

        // Delete questions that weren't in the update but exist in the database
        // (This handles questions removed without _destroy flag)
        if (! empty($customQuestions)) {
            $position->customQuestions()
                ->whereNotIn('id', $existingQuestionIds)
                ->delete();
        }

        return redirect()->route('hr.positions.index')
            ->with('message', 'Position updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Position $position): RedirectResponse
    {
        $this->authorize('delete', $position);

        $position->delete();

        return redirect()->route('hr.positions.index')
            ->with('message', 'Position deleted successfully.');
    }
}
