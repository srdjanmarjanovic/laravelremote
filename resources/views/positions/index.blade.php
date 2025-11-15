@extends('layouts.public')

@section('title', 'Browse Remote Laravel Jobs')
@section('description', 'Find the best remote Laravel developer positions from top companies. Filter by technology, seniority, and location.')

@section('content')
<div class="bg-gradient-to-br from-accent/20 via-background to-secondary/20 transition-colors duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <!-- Hero Section -->
        <div class="text-center mb-12">
            <h1 class="text-4xl md:text-5xl font-bold text-foreground mb-4">
                Find Your Next <span class="text-primary">Remote Laravel Job</span>
            </h1>
            <p class="text-xl text-muted-foreground max-w-3xl mx-auto">
                Join the best remote Laravel teams. Browse {{ $positions->total() }} open positions.
            </p>
        </div>

        <!-- Search and Filters -->
        <div class="bg-card rounded-lg shadow-lg p-6 mb-8 border border-border transition-colors duration-300">
            <form action="/positions" method="GET" class="space-y-4">
                <!-- Search Bar -->
                <div class="flex flex-col md:flex-row gap-4">
                    <div class="flex-1">
                        <input
                            type="text"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Search by title, description, or company..."
                            class="w-full px-4 py-3 border border-input bg-background text-foreground rounded-lg focus:ring-2 focus:ring-ring focus:border-transparent transition-colors"
                        >
                    </div>
                    <button type="submit" class="px-6 py-3 bg-primary hover:bg-primary/90 text-primary-foreground font-medium rounded-lg transition-colors">
                        Search
                    </button>
                </div>

                <!-- Advanced Filters -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-4 border-t border-border">
                    <!-- Technology Filter -->
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-2">Technology</label>
                        <select name="technology" @change="$el.form.submit()" class="w-full px-4 py-2 border border-input bg-background text-foreground rounded-lg focus:ring-2 focus:ring-ring transition-colors">
                            <option value="">All Technologies</option>
                            @foreach($technologies as $tech)
                                <option value="{{ $tech->slug }}" {{ request('technology') === $tech->slug ? 'selected' : '' }}>
                                    {{ $tech->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Seniority Filter -->
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-2">Seniority</label>
                        <select name="seniority" @change="$el.form.submit()" class="w-full px-4 py-2 border border-input bg-background text-foreground rounded-lg focus:ring-2 focus:ring-ring transition-colors">
                            <option value="">All Levels</option>
                            <option value="junior" {{ request('seniority') === 'junior' ? 'selected' : '' }}>Junior</option>
                            <option value="mid" {{ request('seniority') === 'mid' ? 'selected' : '' }}>Mid-Level</option>
                            <option value="senior" {{ request('seniority') === 'senior' ? 'selected' : '' }}>Senior</option>
                            <option value="lead" {{ request('seniority') === 'lead' ? 'selected' : '' }}>Lead</option>
                            <option value="principal" {{ request('seniority') === 'principal' ? 'selected' : '' }}>Principal</option>
                        </select>
                    </div>

                    <!-- Remote Type Filter -->
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-2">Remote Type</label>
                        <select name="remote_type" @change="$el.form.submit()" class="w-full px-4 py-2 border border-input bg-background text-foreground rounded-lg focus:ring-2 focus:ring-ring transition-colors">
                            <option value="">All Types</option>
                            <option value="global" {{ request('remote_type') === 'global' ? 'selected' : '' }}>Global</option>
                            <option value="timezone" {{ request('remote_type') === 'timezone' ? 'selected' : '' }}>Specific Timezone</option>
                            <option value="country" {{ request('remote_type') === 'country' ? 'selected' : '' }}>Specific Country</option>
                        </select>
                    </div>
                </div>

                @if(request()->hasAny(['search', 'technology', 'seniority', 'remote_type']))
                    <div class="flex items-center justify-between pt-4 border-t border-border">
                        <span class="text-sm text-muted-foreground">
                            Showing {{ $positions->firstItem() }}-{{ $positions->lastItem() }} of {{ $positions->total() }} results
                        </span>
                        <a href="/positions" class="text-sm text-primary hover:underline">Clear filters</a>
                    </div>
                @endif
            </form>
        </div>

        <!-- Position Cards -->
        <div class="space-y-4">
            @forelse($positions as $position)
                <a href="/positions/{{ $position->slug }}" class="block">
                    <div class="bg-card rounded-lg shadow hover:shadow-lg transition-all duration-300 p-6 border border-border hover:border-primary">
                        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                            <div class="flex-1 space-y-2">
                                <!-- Company Logo and Title -->
                                <div class="flex items-start gap-4">
                                    @if($position->company->logo)
                                        <img src="{{ $position->company->logo }}" alt="{{ $position->company->name }}" class="w-12 h-12 rounded-lg object-cover">
                                    @else
                                        <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-primary to-accent flex items-center justify-center text-primary-foreground font-bold text-xl">
                                            {{ substr($position->company->name, 0, 1) }}
                                        </div>
                                    @endif

                                    <div class="flex-1">
                                        <h3 class="text-xl font-semibold text-foreground hover:text-primary transition-colors">
                                            {{ $position->title }}
                                            @if($position->is_featured)
                                                <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full bg-secondary text-secondary-foreground ml-2">
                                                    ⭐ Featured
                                                </span>
                                            @endif
                                        </h3>
                                        <p class="text-muted-foreground">{{ $position->company->name }}</p>
                                    </div>
                                </div>

                                <!-- Description -->
                                <p class="text-muted-foreground line-clamp-2">
                                    {{ $position->short_description }}
                                </p>

                                <!-- Meta Information -->
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
                                        {{ ucfirst($position->remote_type) }} Remote
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
                                        {{ $position->published_at->diffForHumans() }}
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

                            <!-- Apply Button -->
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
            @empty
                <div class="bg-card rounded-lg shadow p-12 text-center border border-border transition-colors duration-300">
                    <svg class="mx-auto h-12 w-12 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <h3 class="mt-2 text-lg font-medium text-foreground">No positions found</h3>
                    <p class="mt-1 text-muted-foreground">Try adjusting your search or filter criteria.</p>
                    <a href="/positions" class="mt-4 inline-block text-primary hover:underline">Clear all filters</a>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($positions->hasPages())
            <div class="mt-8">
                {{ $positions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

