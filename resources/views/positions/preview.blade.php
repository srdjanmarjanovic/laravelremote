@extends('layouts.public')

@section('title', $position->title . ' at ' . $position->company->name . ' (Preview)')
@section('description', $position->short_description)

@section('content')
<div class="bg-muted min-h-screen transition-colors duration-300">
    <!-- Preview Banner -->
    <div class="bg-yellow-100 border-b-2 border-yellow-400 dark:bg-yellow-900 dark:border-yellow-600">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <div>
                        <p class="font-semibold text-yellow-800 dark:text-yellow-200">Preview Mode</p>
                        <p class="text-sm text-yellow-700 dark:text-yellow-300">This is how your position will appear to candidates. This preview is only visible to you.</p>
                    </div>
                </div>
                <a href="{{ route('hr.positions.edit', $position) }}" class="px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white font-medium rounded-lg transition-colors text-sm">
                    Edit Position
                </a>
            </div>
        </div>
    </div>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Back Button -->
        <a href="{{ route('hr.positions.index') }}" class="inline-flex items-center text-sm text-muted-foreground hover:text-primary mb-6 transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to positions
        </a>

        <!-- Position Header Card -->
        <div class="bg-card rounded-lg shadow-lg p-8 mb-6 border border-border transition-colors duration-300">
            <div class="flex flex-col md:flex-row gap-6">
                <!-- Company Logo -->
                @if($position->company->logo)
                    <img src="{{ $position->company->logo }}" alt="{{ $position->company->name }}" class="w-20 h-20 rounded-lg object-cover">
                @else
                    <div class="w-20 h-20 rounded-lg bg-gradient-to-br from-primary to-accent flex items-center justify-center text-primary-foreground font-bold text-2xl">
                        {{ substr($position->company->name, 0, 1) }}
                    </div>
                @endif

                <!-- Header Info -->
                <div class="flex-1">
                    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                        <div>
                            <h1 class="text-3xl font-bold text-foreground mb-2">
                                {{ $position->title }}
                                @if($position->is_featured)
                                    <span class="inline-flex items-center px-3 py-1 text-sm font-medium rounded-full bg-secondary text-secondary-foreground ml-2">
                                        ⭐ Featured
                                    </span>
                                @endif
                                @if($position->status === 'draft')
                                    <span class="inline-flex items-center px-3 py-1 text-sm font-medium rounded-full bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300 ml-2">
                                        Draft
                                    </span>
                                @endif
                            </h1>
                            <p class="text-xl text-muted-foreground mb-4">{{ $position->company->name }}</p>

                            <div class="flex flex-wrap items-center gap-4 text-sm text-muted-foreground">
                                @if($position->seniority)
                                    <span class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        {{ ucfirst($position->seniority) }}
                                    </span>
                                @endif

                                <span class="flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    @if ($position->remote_type !== 'global' )
                                        {{ $position->location_restriction }}
                                    @else
                                        <strong>Worldwide</strong>
                                    @endif
                                </span>

                                @if($position->salary_min && $position->salary_max)
                                    <span class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        ${{ number_format($position->salary_min) }} - ${{ number_format($position->salary_max) }}
                                    </span>
                                @endif

                                @if($position->published_at)
                                    <span class="text-muted-foreground">
                                        Posted {{ $position->published_at->diffForHumans() }}
                                    </span>
                                @else
                                    <span class="text-muted-foreground">
                                        Not yet published
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Apply Button (Preview) -->
                        <div class="flex-shrink-0">
                            @if($position->status === 'published')
                                @if($position->canReceiveApplications())
                                    <button disabled class="inline-flex items-center px-6 py-3 bg-primary/50 text-primary-foreground font-medium rounded-lg cursor-not-allowed">
                                        Apply Now (Preview)
                                        <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </button>
                                @elseif($position->is_external)
                                    <button disabled class="inline-flex items-center px-6 py-3 bg-primary/50 text-primary-foreground font-medium rounded-lg cursor-not-allowed">
                                        Apply on Company Site (Preview)
                                        <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                        </svg>
                                    </button>
                                @else
                                    <span class="inline-flex items-center px-6 py-3 bg-muted text-muted-foreground font-medium rounded-lg">
                                        Applications Closed
                                    </span>
                                @endif
                            @else
                                <button disabled class="inline-flex items-center px-6 py-3 bg-gray-300 text-gray-600 font-medium rounded-lg cursor-not-allowed dark:bg-gray-700 dark:text-gray-400">
                                    Not Published
                                </button>
                            @endif
                        </div>
                    </div>

                    <!-- Technologies -->
                    @if($position->technologies->isNotEmpty())
                        <div class="mt-4 flex flex-wrap gap-2">
                            @foreach($position->technologies as $tech)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-accent text-accent-foreground">
                                    {{ $tech->name }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Job Description -->
                <div class="bg-card rounded-lg shadow p-8 border border-border transition-colors duration-300">
                    <h2 class="text-2xl font-bold text-foreground mb-4">About the Position</h2>
                    <div class="prose dark:prose-invert max-w-none text-muted-foreground">
                        {!! $position->long_description !!}
                    </div>
                </div>

                <!-- Custom Questions Preview -->
                @if($position->customQuestions->isNotEmpty())
                    <div class="bg-card rounded-lg shadow p-8 border border-border transition-colors duration-300">
                        <h2 class="text-2xl font-bold text-foreground mb-4">Application Questions</h2>
                        <p class="text-muted-foreground mb-4">Candidates will be asked to answer these questions when applying:</p>
                        <ol class="space-y-2 list-decimal list-inside text-muted-foreground">
                            @foreach($position->customQuestions as $question)
                                <li>
                                    {{ $question->question_text }}
                                    @if($question->is_required)
                                        <span class="text-destructive">*</span>
                                    @endif
                                </li>
                            @endforeach
                        </ol>
                    </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Company Info -->
                <div class="bg-card rounded-lg shadow p-6 border border-border transition-colors duration-300">
                    <h3 class="text-lg font-bold text-foreground mb-4">About {{ $position->company->name }}</h3>

                    @if($position->company->description)
                        <p class="text-muted-foreground mb-4">{{ $position->company->description }}</p>
                    @endif

                    @if($position->company->website)
                        <a href="{{ $position->company->website }}" target="_blank" class="text-primary hover:underline flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                            </svg>
                            Visit Website
                        </a>
                    @endif
                </div>

                <!-- Position Stats -->
                <div class="bg-card rounded-lg shadow p-6 border border-border transition-colors duration-300">
                    <h3 class="text-lg font-bold text-foreground mb-4">Position Stats</h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Applications:</span>
                            <span class="font-medium text-foreground">{{ $position->applications_count }}</span>
                        </div>
                        @if($position->expires_at)
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">Expires:</span>
                                <span class="font-medium text-foreground">{{ $position->expires_at->format('M d, Y') }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Share -->
                <div class="bg-card rounded-lg shadow p-6 border border-border transition-colors duration-300">
                    <h3 class="text-lg font-bold text-foreground mb-4">Share this position</h3>
                    <div class="flex gap-2">
                        <button disabled class="flex-1 px-4 py-2 bg-muted/50 text-muted-foreground rounded-lg text-sm cursor-not-allowed">
                            Copy Link (Preview)
                        </button>
                    </div>
                    <p class="text-xs text-muted-foreground mt-2">Sharing will be available once the position is published.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

