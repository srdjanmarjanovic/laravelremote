<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Models\Company;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CompanySetupController extends Controller
{
    /**
     * Show the company setup form.
     */
    public function show(): Response|RedirectResponse
    {
        $user = auth()->user();
        $company = $user->primaryCompany();

        // If user already has a complete company profile, redirect to edit page
        if ($company && $company->isComplete()) {
            return redirect()->route('hr.company.edit');
        }

        return Inertia::render('Hr/CompanySetup', [
            'company' => $company,
            'isEditing' => false,
        ]);
    }

    /**
     * Show the company edit form.
     */
    public function edit(): Response|RedirectResponse
    {
        $user = auth()->user();
        $company = $user->primaryCompany();

        // If user doesn't have a company, redirect to setup
        if (! $company) {
            return redirect()->route('hr.company.setup');
        }

        return Inertia::render('Hr/CompanySetup', [
            'company' => $company,
            'isEditing' => true,
        ]);
    }

    /**
     * Store the company information.
     */
    public function store(StoreCompanyRequest $request): RedirectResponse
    {
        $user = auth()->user();
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
        $validated['created_by_user_id'] = $user->id;

        // Create the company
        $company = Company::create($validated);

        // Attach user to company as admin
        $company->users()->attach($user->id, [
            'role' => 'admin',
            'joined_at' => now(),
        ]);

        return redirect()->route('hr.dashboard')
            ->with('message', 'Company profile created successfully!');
    }

    /**
     * Update the company information.
     */
    public function update(UpdateCompanyRequest $request): RedirectResponse
    {
        $user = auth()->user();
        $company = $user->primaryCompany();

        if (! $company) {
            return redirect()->route('hr.company.setup')
                ->with('error', 'No company found.');
        }

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

        return redirect()->route('hr.dashboard')
            ->with('message', 'Company profile updated successfully!');
    }

    /**
     * Delete the company logo.
     */
    public function deleteLogo(): RedirectResponse
    {
        $user = auth()->user();
        $company = $user->primaryCompany();

        if (! $company || ! $company->logo) {
            return back()->with('error', 'Logo not found.');
        }

        if (Storage::disk('public')->exists($company->logo)) {
            Storage::disk('public')->delete($company->logo);
        }

        $company->update(['logo' => null]);

        return back()->with('message', 'Logo deleted successfully.');
    }
}
