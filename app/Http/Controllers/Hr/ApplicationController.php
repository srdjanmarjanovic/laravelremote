<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Company;
use App\Notifications\ApplicationStatusChangedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ApplicationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $user = auth()->user();

        // Get companies the user has access to
        $companyIds = $user->isAdmin()
            ? Company::pluck('id')
            : $user->companies->pluck('id');

        $query = Application::query()
            ->whereHas('position', function ($query) use ($companyIds) {
                $query->whereIn('company_id', $companyIds);
            })
            ->with([
                'position.company',
                'user' => function ($query) {
                    $query->withTrashed();
                },
                'user.developerProfile',
            ]);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filter by position
        if ($request->filled('position_id')) {
            $query->where('position_id', $request->input('position_id'));
        }

        // Filter by company
        if ($request->filled('company_id')) {
            $query->whereHas('position', function ($q) use ($request) {
                $q->where('company_id', $request->input('company_id'));
            });
        }

        // Sort
        $sortBy = $request->input('sort_by', 'applied_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $applications = $query->paginate(20)->withQueryString();

        // Add user_archived flag to applications
        $applications->through(function ($application) {
            $application->user_archived = $application->user->trashed();

            return $application;
        });

        // Get positions for filter dropdown
        $positions = \App\Models\Position::query()
            ->whereIn('company_id', $companyIds)
            ->orderBy('title')
            ->get(['id', 'title']);

        return Inertia::render('Hr/Applications/Index', [
            'applications' => $applications,
            'filters' => $request->only(['status', 'position_id', 'company_id', 'sort_by', 'sort_order']),
            'positions' => $positions,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Application $application): Response|RedirectResponse
    {
        $this->authorize('view', $application);

        // Check if user is archived
        $application->load(['user' => function ($query) {
            $query->withTrashed();
        }]);

        if ($application->user->trashed()) {
            return back()->with('error', 'Cannot view details of archived user application.');
        }

        $application->load([
            'position.company',
            'position.technologies',
            'position.customQuestions',
            'user.developerProfile',
            'reviewer',
        ]);

        return Inertia::render('Hr/Applications/Show', [
            'application' => $application,
        ]);
    }

    /**
     * Update the specified resource status.
     */
    public function update(Request $request, Application $application): RedirectResponse
    {
        $this->authorize('updateStatus', $application);

        // Check if user is archived
        $application->load(['user' => function ($query) {
            $query->withTrashed();
        }]);

        $userArchived = $application->user->trashed();

        // For archived users, only allow changing to rejected
        if ($userArchived) {
            $validated = $request->validate([
                'status' => ['required', 'in:rejected'],
            ]);

            if ($validated['status'] !== 'rejected') {
                return back()->with('error', 'Can only reject applications from archived users.');
            }
        } else {
            $validated = $request->validate([
                'status' => ['required', 'in:pending,reviewing,accepted,rejected'],
            ]);
        }

        $previousStatus = $application->status;

        $application->update([
            'status' => $validated['status'],
            'reviewed_by_user_id' => auth()->id(),
        ]);

        // Send notification to applicant about status change
        if ($previousStatus !== $validated['status'] && ! $userArchived) {
            $application->refresh();
            $application->load(['position.company', 'user']);
            $application->user->notify(new ApplicationStatusChangedNotification($application, $previousStatus));
        }

        return back()->with('message', 'Application status updated successfully.');
    }
}
