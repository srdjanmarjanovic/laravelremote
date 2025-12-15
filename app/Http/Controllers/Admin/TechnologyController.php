<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTechnologyRequest;
use App\Http\Requests\UpdateTechnologyRequest;
use App\Models\Technology;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TechnologyController extends Controller
{
    /**
     * Display a listing of technologies.
     */
    public function index(Request $request): Response
    {
        $query = Technology::query()->withCount('positions');

        // Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortBy = $request->input('sort_by', 'name');
        $sortOrder = $request->input('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $technologies = $query->paginate(20)->withQueryString();

        return Inertia::render('Admin/Technologies/Index', [
            'technologies' => $technologies,
            'filters' => $request->only(['search', 'sort_by', 'sort_order']),
        ]);
    }

    /**
     * Store a newly created technology.
     */
    public function store(StoreTechnologyRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Ensure slug is unique (in case validation passed but slug was auto-generated)
        $originalSlug = $validated['slug'];
        $counter = 1;
        while (Technology::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $originalSlug.'-'.$counter;
            $counter++;
        }

        Technology::create($validated);

        return back()->with('message', 'Technology created successfully.');
    }

    /**
     * Update the specified technology.
     */
    public function update(UpdateTechnologyRequest $request, Technology $technology): RedirectResponse
    {
        $validated = $request->validated();

        // Ensure slug is unique (excluding current technology) - in case validation passed but slug was auto-generated
        $originalSlug = $validated['slug'];
        $counter = 1;
        while (Technology::where('slug', $validated['slug'])
            ->where('id', '!=', $technology->id)
            ->exists()) {
            $validated['slug'] = $originalSlug.'-'.$counter;
            $counter++;
        }

        $technology->update($validated);

        return back()->with('message', 'Technology updated successfully.');
    }

    /**
     * Remove the specified technology.
     */
    public function destroy(Technology $technology): RedirectResponse
    {
        // Check if technology is used by any positions
        if ($technology->positions()->count() > 0) {
            return back()->withErrors([
                'technology' => 'Cannot delete technology that is associated with positions.',
            ]);
        }

        $technology->delete();

        return back()->with('message', 'Technology deleted successfully.');
    }
}
