<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CompanyController extends Controller
{
    /**
     * Display a listing of all companies.
     */
    public function index(Request $request): Response
    {
        $query = Company::query()
            ->with(['creator'])
            ->withCount(['positions', 'users']);

        // Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $companies = $query->paginate(20)->withQueryString();

        return Inertia::render('Admin/Companies/Index', [
            'companies' => $companies,
            'filters' => $request->only(['search', 'sort_by', 'sort_order']),
        ]);
    }

    /**
     * Show the form for creating a new company.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Companies/Create');
    }

    /**
     * Store a newly created company.
     */
    public function store(StoreCompanyRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Generate slug from name
        $validated['slug'] = Str::slug($validated['name']);

        // Ensure slug is unique
        $originalSlug = $validated['slug'];
        $counter = 1;
        while (Company::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $originalSlug.'-'.$counter;
            $counter++;
        }

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('company-logos', 'public');
            $validated['logo'] = $logoPath;
        }

        // Set creator
        $validated['created_by_user_id'] = auth()->id();

        // Create the company
        $company = Company::create($validated);

        return redirect()->route('admin.companies.index')
            ->with('message', 'Company created successfully.');
    }

    /**
     * Display the specified company.
     */
    public function show(Company $company): Response
    {
        $this->authorize('view', $company);

        $company->load(['creator', 'users' => function ($query) {
            $query->orderBy('company_user.created_at', 'desc');
        }])
            ->loadCount(['positions', 'users']);

        return Inertia::render('Admin/Companies/Show', [
            'company' => $company,
        ]);
    }

    /**
     * Show the form for editing the specified company.
     */
    public function edit(Company $company): Response
    {
        return Inertia::render('Admin/Companies/Edit', [
            'company' => $company,
        ]);
    }

    /**
     * Update the specified company.
     */
    public function update(UpdateCompanyRequest $request, Company $company): RedirectResponse
    {
        $this->authorize('update', $company);

        $validated = $request->validated();

        // Update slug if name changed
        if (isset($validated['name']) && $validated['name'] !== $company->name) {
            $validated['slug'] = Str::slug($validated['name']);

            // Ensure slug is unique
            $originalSlug = $validated['slug'];
            $counter = 1;
            while (Company::where('slug', $validated['slug'])
                ->where('id', '!=', $company->id)
                ->exists()) {
                $validated['slug'] = $originalSlug.'-'.$counter;
                $counter++;
            }
        }

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($company->logo && Storage::disk('public')->exists($company->logo)) {
                Storage::disk('public')->delete($company->logo);
            }

            $logoPath = $request->file('logo')->store('company-logos', 'public');
            $validated['logo'] = $logoPath;
        }

        $company->update($validated);

        return redirect()->route('admin.companies.index')
            ->with('message', 'Company updated successfully.');
    }

    /**
     * Remove the specified company.
     */
    public function destroy(Company $company): RedirectResponse
    {
        $this->authorize('delete', $company);

        // Delete logo file if exists
        if ($company->logo && Storage::disk('public')->exists($company->logo)) {
            Storage::disk('public')->delete($company->logo);
        }

        $company->delete();

        return redirect()->route('admin.companies.index')
            ->with('message', 'Company deleted successfully.');
    }

    /**
     * Search for users to attach to the company.
     */
    public function searchUsers(Request $request, Company $company): JsonResponse
    {
        $this->authorize('manageTeam', $company);

        $search = $request->input('search', '');
        $excludeIds = $company->users()->pluck('users.id')->toArray();

        $users = User::query()
            ->whereNotIn('id', $excludeIds)
            ->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->limit(20)
            ->get(['id', 'name', 'email', 'role']);

        return response()->json($users);
    }

    /**
     * Attach a user to the company.
     */
    public function attachUser(Request $request, Company $company): RedirectResponse
    {
        $this->authorize('manageTeam', $company);

        $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'role' => ['required', 'in:owner,admin,member'],
        ]);

        $userId = $request->input('user_id');
        $role = $request->input('role', 'member');

        // Check if user is already attached
        if ($company->users()->where('user_id', $userId)->exists()) {
            return back()->with('error', 'User is already attached to this company.');
        }

        $company->users()->attach($userId, [
            'role' => $role,
            'invited_by' => auth()->id(),
            'joined_at' => now(),
        ]);

        return back()->with('message', 'User attached to company successfully.');
    }

    /**
     * Detach a user from the company.
     */
    public function detachUser(Company $company, User $user): RedirectResponse
    {
        $this->authorize('manageTeam', $company);

        // Prevent detaching the creator if they're the only owner
        if ($company->created_by_user_id === $user->id) {
            $ownersCount = $company->users()->wherePivot('role', 'owner')->count();
            if ($ownersCount === 1) {
                return back()->with('error', 'Cannot detach the company creator as they are the only owner.');
            }
        }

        $company->users()->detach($user->id);

        return back()->with('message', 'User detached from company successfully.');
    }

    /**
     * Update a user's role in the company.
     */
    public function updateUserRole(Request $request, Company $company, User $user): RedirectResponse
    {
        $this->authorize('manageTeam', $company);

        $request->validate([
            'role' => ['required', 'in:owner,admin,member'],
        ]);

        // Prevent changing the creator's role if they're the only owner
        if ($company->created_by_user_id === $user->id && $request->input('role') !== 'owner') {
            $ownersCount = $company->users()->wherePivot('role', 'owner')->count();
            if ($ownersCount === 1) {
                return back()->with('error', 'Cannot change the company creator\'s role as they are the only owner.');
            }
        }

        $company->users()->updateExistingPivot($user->id, [
            'role' => $request->input('role'),
        ]);

        return back()->with('message', 'User role updated successfully.');
    }
}
