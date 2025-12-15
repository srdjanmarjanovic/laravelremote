<?php

namespace App\Http\Controllers\Hr;

use App\Enums\ListingType;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePositionRequest;
use App\Http\Requests\UpdatePositionRequest;
use App\Models\Company;
use App\Models\Position;
use App\Models\Technology;
use App\Services\Payment\PositionPaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Inertia\Inertia;
use Inertia\Response;

class PositionController extends Controller
{
    public function __construct(
        protected PositionPaymentService $paymentService
    ) {}

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

        $query = Position::query()
            ->whereIn('company_id', $companyIds)
            ->with(['company', 'technologies', 'creator'])
            ->withCount(['applications', 'views'])
            ->with(['payments' => function ($q) {
                $q->latest()->limit(1);
            }]);

        // Apply search filter
        if (request('search')) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%'.request('search').'%')
                    ->orWhere('short_description', 'like', '%'.request('search').'%');
            });
        }

        // Apply status filter
        if (request('status') && request('status') !== 'archived') {
            $query->where('status', request('status'));
        }

        // Handle archived positions
        if (request('status') === 'archived') {
            // If archived status is selected, show only archived
            $query->where('status', 'archived');
        } elseif (! request('show_archived')) {
            // Otherwise, exclude archived unless explicitly requested
            $query->where('status', '!=', 'archived');
        }

        // Apply company filter
        if (request('company_id')) {
            $query->where('company_id', request('company_id'));
        }

        $positions = $query->latest()->paginate(15)->withQueryString();

        // Add payment status to each position
        $positions->getCollection()->transform(function ($position) {
            $position->payment_status = $this->getPaymentStatus($position);

            return $position;
        });

        // Get all companies for filter dropdown
        $companies = $user->isAdmin()
            ? Company::orderBy('name')->get(['companies.id', 'companies.name'])
            : $user->companies()->orderBy('companies.name')->select('companies.id', 'companies.name')->get();

        return Inertia::render('Hr/Positions/Index', [
            'positions' => $positions,
            'filters' => [
                'search' => request('search'),
                'status' => request('status'),
                'company_id' => request('company_id') ? (int) request('company_id') : null,
                'show_archived' => request('show_archived') ? true : false,
            ],
            'companies' => $companies,
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
            'technologies' => $technologies->toArray(),
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

        // HR cannot control status - always save as draft
        $validated['status'] = 'draft';

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

        // Redirect to payment page after creation
        return redirect()->route('hr.positions.payment', $position)
            ->with('message', 'Position created successfully. Please complete payment to publish.');
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
                $query->with([
                    'user' => function ($q) {
                        $q->withTrashed();
                    },
                    'user.developerProfile',
                ])->latest('applied_at');
            },
        ]);

        // Add user_archived flag to each application
        $position->applications->transform(function ($application) {
            $application->user_archived = $application->user->trashed();

            return $application;
        });

        $position->loadCount([
            'applications',
            'views',
        ]);

        // Get application counts by status
        $applicationStats = [
            'total' => $position->applications_count,
            'pending' => $position->applications()->where('status', 'pending')->count(),
            'reviewing' => $position->applications()->where('status', 'reviewing')->count(),
            'accepted' => $position->applications()->where('status', 'accepted')->count(),
            'rejected' => $position->applications()->where('status', 'rejected')->count(),
        ];

        // Get analytics data
        $analytics = [
            'total_views' => $position->views_count,
            'countries' => \App\Models\PositionView::where('position_id', $position->id)
                ->whereNotNull('country_code')
                ->selectRaw('country_code, COUNT(*) as count')
                ->groupBy('country_code')
                ->orderByDesc('count')
                ->limit(10)
                ->get()
                ->map(fn ($item) => [
                    'country' => $item->country_code,
                    'count' => $item->count,
                ])
                ->values()
                ->toArray(),
            'devices' => \App\Models\PositionView::where('position_id', $position->id)
                ->whereNotNull('device_name')
                ->selectRaw('device_name, device_type, browser, os, COUNT(*) as count')
                ->groupBy('device_name', 'device_type', 'browser', 'os')
                ->orderByDesc('count')
                ->get()
                ->map(fn ($item) => [
                    'device' => $item->device_name,
                    'device_type' => $item->device_type,
                    'browser' => $item->browser,
                    'os' => $item->os,
                    'count' => $item->count,
                ])
                ->values()
                ->toArray(),
            'views_by_date' => \App\Models\PositionView::where('position_id', $position->id)
                ->selectRaw('DATE(viewed_at) as date, COUNT(*) as count')
                ->where('viewed_at', '>=', now()->subDays(30))
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->map(fn ($item) => [
                    'date' => $item->date,
                    'count' => $item->count,
                ])
                ->values()
                ->toArray(),
        ];

        // Calculate upgrade options if position is published
        $upgradeOptions = [];
        if ($position->status === 'published' && $position->hasPaid()) {
            $currentTier = $position->listing_type;
            $remainingDays = $this->paymentService->getRemainingDays($position);

            if ($this->paymentService->canUpgradeTo($position, ListingType::Featured)) {
                $upgradeOptions['featured'] = [
                    'tier' => 'featured',
                    'label' => 'Featured',
                    'price' => $this->paymentService->calculateUpgradePrice($position, ListingType::Featured),
                    'remaining_days' => $remainingDays,
                ];
            }

            if ($this->paymentService->canUpgradeTo($position, ListingType::Top)) {
                $upgradeOptions['top'] = [
                    'tier' => 'top',
                    'label' => 'Top',
                    'price' => $this->paymentService->calculateUpgradePrice($position, ListingType::Top),
                    'remaining_days' => $remainingDays,
                ];
            }
        }

        return Inertia::render('Hr/Positions/Show', [
            'position' => $position,
            'applicationStats' => $applicationStats,
            'analytics' => $analytics,
            'upgradeOptions' => $upgradeOptions,
            'pricing' => [
                'regular' => config('payments.pricing.regular'),
                'featured' => config('payments.pricing.featured'),
                'top' => config('payments.pricing.top'),
            ],
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
     * Preview the position as it would appear to candidates.
     */
    public function preview(Position $position): View
    {
        $this->authorize('preview', $position);

        $position->load(['company', 'technologies', 'customQuestions']);
        $position->loadCount('applications');

        return view('positions.preview', [
            'position' => $position,
            'isPreview' => true,
        ]);
    }

    /**
     * Archive the specified resource.
     */
    public function archive(Position $position): RedirectResponse
    {
        $this->authorize('archive', $position);

        $position->update(['status' => 'archived']);

        return redirect()->route('hr.positions.index')
            ->with('message', 'Position archived successfully.');
    }

    /**
     * Toggle applications open/closed for the position.
     */
    public function toggleApplications(Position $position): RedirectResponse
    {
        $this->authorize('update', $position);

        $newValue = ! $position->allow_platform_applications;
        $position->update(['allow_platform_applications' => $newValue]);

        $message = $newValue
            ? 'Applications are now open for this position.'
            : 'Applications are now closed for this position.';

        return redirect()->back()
            ->with('message', $message);
    }

    /**
     * Get payment status for a position.
     */
    private function getPaymentStatus(Position $position): string
    {
        // If paid_at is set, position is paid
        if ($position->paid_at !== null) {
            return 'paid';
        }

        // Check latest payment
        $latestPayment = $position->payments->first();
        if ($latestPayment) {
            return match ($latestPayment->status->value) {
                'completed' => 'paid',
                'pending' => 'pending',
                'failed' => 'failed',
                'refunded' => 'refunded',
                default => 'unpaid',
            };
        }

        return 'unpaid';
    }
}
