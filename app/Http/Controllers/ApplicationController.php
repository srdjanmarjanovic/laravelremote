<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreApplicationRequest;
use App\Models\Position;
use App\Notifications\NewApplicationNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Notification;
use Inertia\Inertia;
use Inertia\Response;

class ApplicationController extends Controller
{
    /**
     * Show the application form for a position.
     */
    public function create(Position $position): Response|RedirectResponse
    {
        $user = auth()->user();

        // Redirect unauthenticated users to login
        if (! $user) {
            return redirect()->route('login')
                ->with('info', 'Please log in to apply for this position.');
        }

        // Check if user is developer or admin
        if (! $user->isDeveloper() && ! $user->isAdmin()) {
            return redirect()->route('positions.show', $position->slug)
                ->with('error', 'Only developers and admins can apply to positions.');
        }

        // Check if profile is complete (only required for developers)
        if ($user->isDeveloper() && ! $user->hasCompleteProfile()) {
            return redirect()->route('developer.profile.edit')
                ->with('warning', 'Please complete your profile before applying to positions.');
        }

        // Check if position accepts applications
        if (! $position->canReceiveApplications()) {
            return redirect()->route('positions.show', $position->slug)
                ->with('error', 'This position is not accepting applications.');
        }

        // Check if user already applied
        if ($position->applications()->where('user_id', $user->id)->exists()) {
            return redirect()->route('positions.show', $position->slug)
                ->with('error', 'You have already applied to this position.');
        }

        $position->load(['company', 'technologies', 'customQuestions']);

        // Return position data for modal (not a full page)
        return Inertia::render('Positions/Apply', [
            'position' => $position,
        ]);
    }

    /**
     * Store the application.
     */
    public function store(StoreApplicationRequest $request, Position $position): RedirectResponse|JsonResponse
    {
        $validated = $request->validated();

        $application = $position->applications()->create([
            'user_id' => auth()->id(),
            'custom_answers' => $validated['custom_answers'] ?? [],
            'status' => 'pending',
            'applied_at' => now(),
        ]);

        // Send notification to HR team members about new application
        $application->load(['position.company', 'user']);
        $position->load('company.users');
        $hrUsers = $position->company->users()->where('users.role', 'hr')->get();

        if ($hrUsers->isNotEmpty()) {
            Notification::send($hrUsers, new NewApplicationNotification($application));
        }

        // Return JSON response for AJAX requests, redirect for regular requests
        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'message' => 'Your application has been submitted successfully!',
                'success' => true,
            ]);
        }

        // Return to position show page with success message
        // The modal will be closed by the frontend on success
        return redirect()->route('positions.show', $position->slug)
            ->with('message', 'Your application has been submitted successfully!');
    }

    /**
     * Show the user's applications.
     */
    public function index(): Response
    {
        $user = auth()->user();

        $applications = $user->applications()
            ->with(['position.company', 'position.technologies'])
            ->latest('applied_at')
            ->paginate(15);

        return Inertia::render('Developer/Applications', [
            'applications' => $applications,
        ]);
    }

    /**
     * Show a specific application.
     */
    public function show(\App\Models\Application $application): Response
    {
        $this->authorize('view', $application);

        $application->load([
            'position.company',
            'position.technologies',
            'position.customQuestions',
            'reviewer',
        ]);

        return Inertia::render('Developer/ApplicationShow', [
            'application' => $application,
        ]);
    }
}
