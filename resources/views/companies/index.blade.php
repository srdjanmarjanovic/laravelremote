@extends('layouts.public')

@section('title', 'Browse Companies')
@section('description', 'Discover companies hiring remote Laravel developers. Find your next opportunity with top companies around the world.')

@section('content')
<div class="bg-gradient-to-br from-accent/20 via-background to-secondary/20 transition-colors duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <!-- Hero Section -->
        <div class="text-center mb-12">
            <h1 class="text-4xl md:text-5xl font-bold text-foreground mb-4">
                Discover <span class="text-primary">Companies</span> Hiring Laravel Developers
            </h1>
            <p class="text-xl text-muted-foreground max-w-3xl mx-auto">
                Explore {{ $companies->total() }} companies actively hiring remote Laravel talent.
            </p>
        </div>

        <!-- Company Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($companies as $company)
                <a href="/companies/{{ $company->slug }}" class="block">
                    <div class="bg-card rounded-lg shadow hover:shadow-lg transition-all duration-300 p-6 border border-border hover:border-primary h-full flex flex-col">
                        <!-- Company Logo -->
                        <div class="flex items-start gap-4 mb-4">
                            @if($company->logo)
                                <img src="{{ asset('storage/' . $company->logo) }}" alt="{{ $company->name }}" class="w-16 h-16 rounded-lg object-cover flex-shrink-0">
                            @else
                                <div class="w-16 h-16 rounded-lg bg-gradient-to-br from-primary to-accent flex items-center justify-center text-primary-foreground font-bold text-2xl flex-shrink-0">
                                    {{ substr($company->name, 0, 1) }}
                                </div>
                            @endif

                            <div class="flex-1 min-w-0">
                                <h3 class="text-xl font-semibold text-foreground hover:text-primary transition-colors mb-1">
                                    {{ $company->name }}
                                </h3>
                                <p class="text-sm text-muted-foreground">
                                    {{ $company->positions_count }} {{ Str::plural('position', $company->positions_count) }}
                                </p>
                            </div>
                        </div>

                        <!-- Description -->
                        @if($company->description)
                            <p class="text-muted-foreground line-clamp-3 mb-4 flex-1">
                                {{ $company->description }}
                            </p>
                        @endif

                        <!-- Website Link -->
                        @if($company->website)
                            <div class="mt-auto pt-4 border-t border-border">
                                <span class="text-sm text-primary hover:underline flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                                    </svg>
                                    Visit Website
                                </span>
                            </div>
                        @endif
                    </div>
                </a>
            @empty
                <div class="col-span-full bg-card rounded-lg shadow p-12 text-center border border-border transition-colors duration-300">
                    <svg class="mx-auto h-12 w-12 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <h3 class="mt-2 text-lg font-medium text-foreground">No companies found</h3>
                    <p class="mt-1 text-muted-foreground">There are no companies with open positions at the moment.</p>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($companies->hasPages())
            <div class="mt-8">
                {{ $companies->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
