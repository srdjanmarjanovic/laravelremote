@extends('layouts.public')

@section('title', $company->name . ' - Remote Laravel Jobs')
@section('description', $company->description ? Str::limit($company->description, 160) : 'View open positions at ' . $company->name)

@section('content')
<div class="bg-muted min-h-screen transition-colors duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Back Button -->
        <a href="/companies" class="inline-flex items-center text-sm text-muted-foreground hover:text-primary mb-6 transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to all companies
        </a>

        <!-- Company Header Card -->
        <div class="bg-card rounded-lg shadow-lg p-8 mb-6 border border-border transition-colors duration-300">
            <div class="flex flex-col md:flex-row gap-6">
                <!-- Company Logo -->
                @if($company->logo)
                    <img src="{{ asset('storage/' . $company->logo) }}" alt="{{ $company->name }}" class="w-24 h-24 rounded-lg object-cover flex-shrink-0">
                @else
                    <div class="w-24 h-24 rounded-lg bg-gradient-to-br from-primary to-accent flex items-center justify-center text-primary-foreground font-bold text-3xl flex-shrink-0">
                        {{ substr($company->name, 0, 1) }}
                    </div>
                @endif

                <!-- Company Info -->
                <div class="flex-1">
                    <h1 class="text-4xl font-bold text-foreground mb-2">{{ $company->name }}</h1>
                    
                    @if($company->description)
                        <p class="text-lg text-muted-foreground mb-4">{{ $company->description }}</p>
                    @endif

                    <!-- Links -->
                    @if($company->website || !empty($company->social_links))
                        <div class="flex flex-wrap gap-4">
                            @if($company->website)
                                <a href="{{ $company->website }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 text-primary hover:underline">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                                    </svg>
                                    Visit Website
                                </a>
                            @endif

                            @if(!empty($company->social_links['twitter']))
                                <a href="{{ $company->social_links['twitter'] }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 text-primary hover:underline" title="Twitter / X">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                                    </svg>
                                    Twitter
                                </a>
                            @endif

                            @if(!empty($company->social_links['linkedin']))
                                <a href="{{ $company->social_links['linkedin'] }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 text-primary hover:underline" title="LinkedIn">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                                    </svg>
                                    LinkedIn
                                </a>
                            @endif

                            @if(!empty($company->social_links['github']))
                                <a href="{{ $company->social_links['github'] }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 text-primary hover:underline" title="GitHub">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"/>
                                    </svg>
                                    GitHub
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Open Positions -->
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-foreground mb-4">
                Open Positions ({{ $positions->count() }})
            </h2>

            @if($positions->isNotEmpty())
                <div class="space-y-4">
                    @foreach($positions as $position)
                        <a href="/positions/{{ $position->slug }}" class="block">
                            <div class="bg-card rounded-lg shadow hover:shadow-lg transition-all duration-300 p-6 border border-border hover:border-primary">
                                <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                                    <div class="flex-1">
                                        <h3 class="text-xl font-semibold text-foreground hover:text-primary transition-colors mb-2">
                                            {{ $position->title }}
                                        </h3>

                                        <p class="text-muted-foreground line-clamp-2 mb-4">
                                            {{ $position->short_description }}
                                        </p>

                                        <!-- Meta Information -->
                                        <div class="flex flex-wrap items-center gap-4 text-sm text-muted-foreground mb-4">
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
                                                @if ($position->remote_type !== 'global')
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

                                            <span class="text-muted-foreground">
                                                Posted {{ $position->published_at->diffForHumans() }}
                                            </span>
                                        </div>

                                        <!-- Technologies -->
                                        @if($position->technologies->isNotEmpty())
                                            <div class="flex flex-wrap gap-2">
                                                @foreach($position->technologies->take(5) as $tech)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-accent text-accent-foreground">
                                                        {{ $tech->name }}
                                                    </span>
                                                @endforeach
                                                @if($position->technologies->count() > 5)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-muted text-muted-foreground">
                                                        +{{ $position->technologies->count() - 5 }} more
                                                    </span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>

                                    <!-- View Button -->
                                    <div class="flex items-center">
                                        <span class="inline-flex items-center px-4 py-2 bg-primary hover:bg-primary/90 text-primary-foreground text-sm font-medium rounded-lg transition-colors">
                                            View Details
                                            <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="bg-card rounded-lg shadow p-12 text-center border border-border transition-colors duration-300">
                    <svg class="mx-auto h-12 w-12 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="mt-2 text-lg font-medium text-foreground">No open positions</h3>
                    <p class="mt-1 text-muted-foreground">This company doesn't have any open positions at the moment.</p>
                    <a href="/positions" class="mt-4 inline-block text-primary hover:underline">Browse all positions</a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
