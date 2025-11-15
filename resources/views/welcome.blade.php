@extends('layouts.public')

@section('title', 'Find Your Dream Remote Job')

@section('content')
    <!-- Hero Section -->
    <div class="relative isolate overflow-hidden bg-gradient-to-br from-primary/20 via-accent/30 to-secondary/20">
        <div class="mx-auto max-w-7xl px-6 py-24 sm:py-32 lg:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <h1 class="text-4xl font-bold tracking-tight text-foreground sm:text-6xl">
                    Find Your Dream Remote Job
                </h1>
                <p class="mt-6 text-lg leading-8 text-muted-foreground">
                    Connect with top companies hiring remote developers worldwide. Browse thousands of opportunities and take your career to the next level.
                </p>
                
                <!-- Search Form -->
                <form action="{{ route('positions.index') }}" method="GET" class="mt-10" x-data="{ search: '', location: '' }">
                    <div class="flex flex-col sm:flex-row gap-3 justify-center items-center">
                        <input
                            type="text"
                            name="search"
                            x-model="search"
                            placeholder="Job title, keywords, or company"
                            class="w-full sm:w-96 rounded-md border-0 px-4 py-3 text-foreground bg-background shadow-sm ring-1 ring-inset ring-border placeholder:text-muted-foreground focus:ring-2 focus:ring-inset focus:ring-ring"
                        >
                        <button
                            type="submit"
                            class="w-full sm:w-auto inline-flex items-center justify-center rounded-md bg-primary px-8 py-3 text-sm font-semibold text-primary-foreground shadow-sm hover:bg-primary/90 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring transition"
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            Search Jobs
                        </button>
                    </div>
                </form>

                <!-- Quick Stats -->
                <div class="mt-16 grid grid-cols-2 gap-8 sm:grid-cols-4">
                    <div>
                        <div class="text-4xl font-bold text-foreground">{{ number_format($stats['total_positions']) }}+</div>
                        <div class="mt-2 text-sm text-muted-foreground">Active Jobs</div>
                    </div>
                    <div>
                        <div class="text-4xl font-bold text-foreground">{{ number_format($stats['total_companies']) }}+</div>
                        <div class="mt-2 text-sm text-muted-foreground">Companies</div>
                    </div>
                    <div>
                        <div class="text-4xl font-bold text-foreground">{{ number_format($stats['total_applications']) }}+</div>
                        <div class="mt-2 text-sm text-muted-foreground">Applications</div>
                    </div>
                    <div>
                        <div class="text-4xl font-bold text-foreground">100%</div>
                        <div class="mt-2 text-sm text-muted-foreground">Remote</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Decorative background -->
        <svg class="absolute inset-0 -z-10 h-full w-full stroke-muted-foreground/10 [mask-image:radial-gradient(100%_100%_at_top_right,white,transparent)]" aria-hidden="true">
            <defs>
                <pattern id="983e3e4c-de6d-4c3f-8d64-b9761d1534cc" width="200" height="200" x="50%" y="-1" patternUnits="userSpaceOnUse">
                    <path d="M.5 200V.5H200" fill="none" />
                </pattern>
            </defs>
            <svg x="50%" y="-1" class="overflow-visible fill-accent/20">
                <path d="M-200 0h201v201h-201Z M600 0h201v201h-201Z M-400 600h201v201h-201Z M200 800h201v201h-201Z" stroke-width="0" />
            </svg>
            <rect width="100%" height="100%" stroke-width="0" fill="url(#983e3e4c-de6d-4c3f-8d64-b9761d1534cc)" />
        </svg>
    </div>

    <!-- Featured Positions -->
    @if($featured_positions->isNotEmpty())
        <div class="bg-muted py-16">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h2 class="text-3xl font-bold tracking-tight text-foreground sm:text-4xl">
                        Featured Positions
                    </h2>
                    <p class="mt-4 text-lg text-muted-foreground">
                        Hand-picked remote opportunities from top companies
                    </p>
                </div>

                <div class="mt-12 grid gap-6 lg:grid-cols-2">
                    @foreach($featured_positions as $position)
                        <a href="{{ route('positions.show', $position->slug) }}" class="group block bg-card rounded-lg shadow-md hover:shadow-xl transition-all duration-200 overflow-hidden border border-border">
                            <div class="p-6">
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex items-center">
                                        @if($position->company->logo)
                                            <img src="{{ asset('storage/' . $position->company->logo) }}" alt="{{ $position->company->name }} logo" class="h-12 w-12 rounded-lg object-cover mr-4">
                                        @else
                                            <div class="h-12 w-12 rounded-lg bg-gradient-to-br from-primary to-accent flex items-center justify-center text-lg font-bold text-primary-foreground mr-4">
                                                {{ substr($position->company->name, 0, 1) }}
                                            </div>
                                        @endif
                                        <div>
                                            <h3 class="text-xl font-semibold text-card-foreground group-hover:text-primary transition-colors">
                                                {{ $position->title }}
                                            </h3>
                                            <p class="text-primary font-medium">
                                                {{ $position->company->name }}
                                            </p>
                                        </div>
                                    </div>
                                    <span class="inline-flex items-center rounded-full bg-secondary px-3 py-1 text-xs font-medium text-secondary-foreground">
                                        ⭐ Featured
                                    </span>
                                </div>

                                <p class="text-muted-foreground mb-4 line-clamp-2">
                                    {{ $position->short_description }}
                                </p>

                                <div class="flex flex-wrap items-center gap-3 text-sm text-muted-foreground mb-4">
                                    <span class="inline-flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        {{ ucfirst($position->remote_type) }} Remote
                                    </span>
                                    @if($position->seniority)
                                        <span>• {{ ucfirst($position->seniority) }}</span>
                                    @endif
                                    @if($position->salary_min && $position->salary_max)
                                        <span>• ${{ number_format($position->salary_min / 1000) }}k - ${{ number_format($position->salary_max / 1000) }}k</span>
                                    @endif
                                </div>

                                @if($position->technologies->isNotEmpty())
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($position->technologies->take(5) as $tech)
                                            <span class="inline-flex items-center rounded-full bg-accent px-2.5 py-0.5 text-xs font-medium text-accent-foreground">
                                                {{ $tech->name }}
                                            </span>
                                        @endforeach
                                        @if($position->technologies->count() > 5)
                                            <span class="inline-flex items-center rounded-full bg-muted px-2.5 py-0.5 text-xs font-medium text-muted-foreground">
                                                +{{ $position->technologies->count() - 5 }}
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>

                <div class="mt-10 text-center">
                    <a href="{{ route('positions.index') }}" class="inline-flex items-center justify-center rounded-md bg-primary px-6 py-3 text-base font-semibold text-primary-foreground shadow-sm hover:bg-primary/90 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring transition">
                        View All Positions
                        <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    @endif

    <!-- Popular Technologies -->
    @if($popular_technologies->isNotEmpty())
        <div class="py-16 bg-background">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h2 class="text-3xl font-bold tracking-tight text-foreground sm:text-4xl">
                        Browse by Technology
                    </h2>
                    <p class="mt-4 text-lg text-muted-foreground">
                        Find positions for your favorite tech stack
                    </p>
                </div>

                <div class="mt-12 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
                    @foreach($popular_technologies as $tech)
                        <a href="{{ route('positions.index', ['technologies' => [$tech->id]]) }}" class="group relative flex flex-col items-center justify-center rounded-lg border-2 border-border p-6 hover:border-primary hover:shadow-lg transition-all duration-200">
                            <div class="text-4xl mb-3">
                                @if($tech->icon)
                                    <img src="{{ $tech->icon }}" alt="{{ $tech->name }}" class="h-12 w-12">
                                @else
                                    <div class="h-12 w-12 rounded-full bg-gradient-to-br from-primary to-accent flex items-center justify-center text-lg font-bold text-primary-foreground">
                                        {{ substr($tech->name, 0, 1) }}
                                    </div>
                                @endif
                            </div>
                            <h3 class="text-base font-semibold text-foreground group-hover:text-primary transition-colors">
                                {{ $tech->name }}
                            </h3>
                            <p class="mt-1 text-sm text-muted-foreground">
                                {{ $tech->positions_count }} {{ Str::plural('position', $tech->positions_count) }}
                            </p>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- How It Works -->
    <div class="bg-muted py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="text-3xl font-bold tracking-tight text-foreground sm:text-4xl">
                    How It Works
                </h2>
                <p class="mt-4 text-lg text-muted-foreground">
                    Find your next remote role in three simple steps
                </p>
            </div>

            <div class="mt-12 grid gap-8 md:grid-cols-3">
                <div class="text-center">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-primary text-primary-foreground text-2xl font-bold">
                        1
                    </div>
                    <h3 class="mt-6 text-xl font-semibold text-foreground">
                        Create Your Profile
                    </h3>
                    <p class="mt-4 text-muted-foreground">
                        Sign up and build a comprehensive profile showcasing your skills, experience, and portfolio.
                    </p>
                </div>

                <div class="text-center">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-primary text-primary-foreground text-2xl font-bold">
                        2
                    </div>
                    <h3 class="mt-6 text-xl font-semibold text-foreground">
                        Browse & Apply
                    </h3>
                    <p class="mt-4 text-muted-foreground">
                        Search through thousands of remote positions and apply to ones that match your expertise.
                    </p>
                </div>

                <div class="text-center">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-primary text-primary-foreground text-2xl font-bold">
                        3
                    </div>
                    <h3 class="mt-6 text-xl font-semibold text-foreground">
                        Get Hired
                    </h3>
                    <p class="mt-4 text-muted-foreground">
                        Connect with hiring managers, ace your interviews, and land your dream remote job.
                    </p>
                </div>
            </div>

            <div class="mt-12 text-center">
                <a href="{{ route('register') }}" class="inline-flex items-center justify-center rounded-md bg-primary px-8 py-4 text-lg font-semibold text-primary-foreground shadow-sm hover:bg-primary/90 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring transition">
                    Get Started Today
                    <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                    </svg>
                </a>
            </div>
        </div>
    </div>

    <!-- CTA Section -->
    <div class="bg-primary">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8 lg:py-16">
            <div class="mx-auto max-w-3xl text-center">
                <h2 class="text-3xl font-bold tracking-tight text-primary-foreground sm:text-4xl">
                    Ready to hire remote talent?
                </h2>
                <p class="mt-4 text-lg text-primary-foreground/80">
                    Post your positions and connect with skilled developers from around the world.
                </p>
                <div class="mt-8">
                    <a href="{{ route('register') }}" class="inline-flex items-center justify-center rounded-md bg-background px-8 py-4 text-lg font-semibold text-foreground shadow-sm hover:bg-background/90 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-background transition">
                        Post a Job
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

