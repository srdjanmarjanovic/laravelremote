@extends('layouts.public')

@php
use App\Enums\ListingType;
@endphp

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
        <button onclick="history.back()" class="inline-flex items-center text-sm text-muted-foreground hover:text-primary mb-6 transition-colors cursor-pointer">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to positions
        </button>

        <!-- Position Header Card -->
        <div class="rounded-lg shadow-lg p-8 mb-6 {{ in_array($position->listing_type, [ListingType::Featured, ListingType::Top]) ? 'border-2 border-primary bg-gradient-to-br from-primary/10 via-primary/5 to-card shadow-lg shadow-primary/30' : 'bg-card border border-border' }} transition-colors duration-300">
            <div class="flex flex-col md:flex-row gap-6">
                <!-- Company Logo -->
                @if($position->company->logo)
                    <img src="{{ asset('storage/' . $position->company->logo) }}" alt="{{ $position->company->name }}" class="w-20 h-20 rounded-lg object-cover">
                @else
                    <div class="w-20 h-20 rounded-lg bg-gradient-to-br from-primary to-accent flex items-center justify-center text-primary-foreground font-bold text-2xl">
                        {{ substr($position->company->name, 0, 1) }}
                    </div>
                @endif

                <!-- Header Info -->
                <div class="flex-1">
                    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-2 mb-2">
                                <h1 class="{{ $position->listing_type === ListingType::Top ? 'text-5xl' : 'text-3xl' }} font-bold text-foreground">
                                    {{ $position->title }}
                                    @if($position->status === 'draft')
                                        <span class="inline-flex items-center px-3 py-1 text-sm font-medium rounded-full bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300 ml-2">
                                            Draft
                                        </span>
                                    @endif
                                </h1>
                                @if($position->listing_type === ListingType::Top)
                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-primary/20 text-primary border border-primary">
                                        ⭐ Top
                                    </span>
                                @endif
                            </div>
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
                                        <strong>Global</strong>
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
                                    <span class="text-muted-foreground" title="Published on {{ $position->published_at->format('M d, Y \a\t g:i A T') }}">
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
                                        Apply (Preview)
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
                    <div class="prose dark:prose-invert prose-li:leading-7 prose-p:m-2 max-w-none text-muted-foreground">
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

                    @if($position->company->website || !empty($position->company->social_links))
                        <div class="flex flex-wrap gap-3">
                            @if($position->company->website)
                                <a href="{{ $position->company->website }}" target="_blank" class="text-primary hover:underline flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                                    </svg>
                                    Visit Website
                                </a>
                            @endif

                            @if(!empty($position->company->social_links['twitter']))
                                <a href="{{ $position->company->social_links['twitter'] }}" target="_blank" class="text-primary hover:underline flex items-center gap-2" title="Twitter / X">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                                    </svg>
                                    Twitter
                                </a>
                            @endif

                            @if(!empty($position->company->social_links['linkedin']))
                                <a href="{{ $position->company->social_links['linkedin'] }}" target="_blank" class="text-primary hover:underline flex items-center gap-2" title="LinkedIn">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                                    </svg>
                                    LinkedIn
                                </a>
                            @endif

                            @if(!empty($position->company->social_links['github']))
                                <a href="{{ $position->company->social_links['github'] }}" target="_blank" class="text-primary hover:underline flex items-center gap-2" title="GitHub">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"/>
                                    </svg>
                                    GitHub
                                </a>
                            @endif
                        </div>
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

