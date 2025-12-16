@extends('layouts.public')

@php
use App\Enums\ListingType;
@endphp

@section('title', $position->title . ' at ' . $position->company->name)
@section('description', $position->short_description)

@section('content')
<div class="bg-muted min-h-screen transition-colors duration-300" 
     x-data="applicationModal">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Back Button -->
        <button onclick="history.back()" class="inline-flex items-center text-sm text-muted-foreground hover:text-primary mb-6 transition-colors cursor-pointer">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to all positions
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

                                <span class="text-muted-foreground" title="Published on {{ $position->published_at->format('M d, Y \a\t g:i A T') }}">
                                    Posted {{ $position->published_at->diffForHumans() }}
                                </span>
                            </div>
                        </div>

                        <!-- Apply Button -->
                        <div class="flex-shrink-0">
                            @auth
                                @if(auth()->user()->isHR() && auth()->user()->canManagePosition($position))
                                    {{-- HR users see Edit button --}}
                                    <a href="{{ route('hr.positions.edit', $position) }}" class="inline-flex items-center px-6 py-3 bg-secondary hover:bg-secondary/90 text-secondary-foreground font-medium rounded-lg transition-colors">
                                        <svg class="mr-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        Edit Position
                                    </a>
                                @elseif(auth()->user()->isDeveloper())
                                    {{-- Developers see Apply Now --}}
                                    @if($hasApplied)
                                        <div class="inline-flex flex-col items-end">
                                            <span class="inline-flex items-center px-6 py-3 bg-muted text-muted-foreground font-medium rounded-lg cursor-not-allowed">
                                                Applied
                                                <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </span>
                                            <p class="text-xs text-muted-foreground mt-1">You've submitted an application for this position.</p>
                                        </div>
                                    @elseif($position->canReceiveApplications())
                                        <button 
                                            @click="applicationModalOpen = true" 
                                            class="inline-flex items-center px-6 py-3 bg-primary hover:bg-primary/90 text-primary-foreground font-medium rounded-lg transition-colors">
                                            Apply Now
                                            <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </button>
                                    @elseif($position->is_external)
                                        <a href="{{ $position->external_apply_url }}" target="_blank" class="inline-flex items-center px-6 py-3 bg-primary hover:bg-primary/90 text-primary-foreground font-medium rounded-lg transition-colors">
                                            Apply
                                            <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                            </svg>
                                        </a>
                                    @else
                                        <span class="inline-flex items-center px-6 py-3 bg-muted text-muted-foreground font-medium rounded-lg">
                                            Applications Closed
                                        </span>
                                    @endif
                                @else
                                    {{-- Non-developer authenticated users (admins, HR, etc.) --}}
                                    <div class="inline-flex flex-col items-end">
                                        <span class="inline-flex items-center px-6 py-3 bg-muted text-muted-foreground font-medium rounded-lg cursor-not-allowed">
                                            Apply Now
                                        </span>
                                        <p class="text-xs text-muted-foreground mt-1">Only developers can apply to positions.</p>
                                    </div>
                                @endif
                            @else
                                {{-- Visitors see Apply Now that redirects to login --}}
                                @if($position->canReceiveApplications())
                                    <a href="{{ route('login') }}?redirect={{ urlencode(route('positions.apply', $position)) }}" class="inline-flex items-center px-6 py-3 bg-primary hover:bg-primary/90 text-primary-foreground font-medium rounded-lg transition-colors">
                                        Apply Now
                                        <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </a>
                                @elseif($position->is_external)
                                    <a href="{{ $position->external_apply_url }}" target="_blank" class="inline-flex items-center px-6 py-3 bg-primary hover:bg-primary/90 text-primary-foreground font-medium rounded-lg transition-colors">
                                        Apply
                                        <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                        </svg>
                                    </a>
                                @else
                                    <span class="inline-flex items-center px-6 py-3 bg-muted text-muted-foreground font-medium rounded-lg">
                                        Applications Closed
                                    </span>
                                @endif
                            @endauth
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

                <!-- Share -->
                <div class="bg-card rounded-lg shadow p-6 border border-border transition-colors duration-300">
                    <h3 class="text-lg font-bold text-foreground mb-4">Share this position</h3>
                    <div class="flex gap-2">
                        <button id="copy-link-btn" onclick="copyLink()" class="flex-1 px-4 py-2 bg-muted hover:bg-muted/80 text-foreground rounded-lg text-sm transition-all duration-200">
                            <span id="copy-link-text" class="inline-flex items-center justify-center gap-2">
                                <svg id="copy-icon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                                <svg id="check-icon" class="w-4 h-4 hidden text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span id="btn-label">Copy Link</span>
                            </span>
                        </button>
                    </div>
                </div>

                <script>
                    function copyLink() {
                        navigator.clipboard.writeText(window.location.href).then(() => {
                            const btn = document.getElementById('copy-link-btn');
                            const copyIcon = document.getElementById('copy-icon');
                            const checkIcon = document.getElementById('check-icon');
                            const label = document.getElementById('btn-label');

                            // Switch to success state
                            copyIcon.classList.add('hidden');
                            checkIcon.classList.remove('hidden');
                            label.textContent = 'Copied!';
                            btn.classList.add('bg-green-500/20', 'text-green-600', 'dark:text-green-400');
                            btn.classList.remove('bg-muted');

                            // Revert after 2 seconds
                            setTimeout(() => {
                                copyIcon.classList.remove('hidden');
                                checkIcon.classList.add('hidden');
                                label.textContent = 'Copy Link';
                                btn.classList.remove('bg-green-500/20', 'text-green-600', 'dark:text-green-400');
                                btn.classList.add('bg-muted');
                            }, 2000);
                        });
                    }
                </script>
            </div>
        </div>
    </div>

    <!-- Application Modal -->
    @auth
        @if($user && $user->isDeveloper() && !$hasApplied && $position->canReceiveApplications())
            <!-- Modal Overlay -->
            <div x-show="applicationModalOpen" 
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click.self="closeModal()"
                 class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4"
                 style="display: none;">
                
                <!-- Modal Content -->
                <div x-show="applicationModalOpen"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     @click.self.stop
                     class="bg-card rounded-lg shadow-lg border border-border w-full max-w-3xl max-h-[90vh] overflow-y-auto">
                    
                    <!-- Modal Header -->
                    <div class="p-6 border-b border-border">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-2xl font-bold text-foreground">Review Your Application</h2>
                                <p class="text-sm text-muted-foreground mt-1">
                                    Review your profile information and answer any additional questions before submitting your application.
                                </p>
                            </div>
                            <button @click="closeModal()" 
                                    :disabled="processing"
                                    class="text-muted-foreground hover:text-foreground transition-colors">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Modal Body -->
                    <div class="p-6 space-y-6">
                        <!-- Profile Information Card -->
                        <div class="bg-muted rounded-lg p-6 border border-border">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h3 class="text-lg font-semibold text-foreground">Your Profile</h3>
                                    <p class="text-sm text-muted-foreground">This information will be shared with the employer</p>
                                </div>
                                <a href="{{ route('developer.profile.edit') }}" target="_blank" 
                                   class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg border border-border bg-background hover:bg-muted transition-colors">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    Edit Profile
                                </a>
                            </div>

                            <div class="space-y-4">
                                <!-- Basic Info -->
                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div class="flex items-start gap-3">
                                        <svg class="w-4 h-4 mt-1 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        <div>
                                            <p class="text-sm font-medium text-muted-foreground">Name</p>
                                            <p class="text-sm text-foreground">{{ $user->name }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-3">
                                        <svg class="w-4 h-4 mt-1 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                        <div>
                                            <p class="text-sm font-medium text-muted-foreground">Email</p>
                                            <p class="text-sm text-foreground">{{ $user->email }}</p>
                                        </div>
                                    </div>
                                </div>

                                @if($user->developerProfile)
                                    <div class="border-t border-border pt-4"></div>

                                    <!-- Summary -->
                                    @if($user->developerProfile->summary)
                                        <div>
                                            <p class="text-sm font-medium text-muted-foreground mb-1">Summary</p>
                                            <p class="text-sm text-foreground">{{ $user->developerProfile->summary }}</p>
                                        </div>
                                    @endif

                                    <!-- CV -->
                                    @if($user->developerProfile->cv_path)
                                        <div class="flex items-center gap-2">
                                            <svg class="w-4 h-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            <span class="text-sm text-foreground">CV attached</span>
                                            <span class="ml-2 inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                                Available
                                            </span>
                                            <a href="{{ route('developer.profile.cv.download') }}" 
                                               target="_blank"
                                               class="ml-auto inline-flex items-center px-3 py-1 text-sm text-primary hover:underline">
                                                Review CV
                                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                                </svg>
                                            </a>
                                        </div>
                                    @endif

                                    <!-- Links -->
                                    @if($user->developerProfile->github_url || $user->developerProfile->linkedin_url || $user->developerProfile->portfolio_url)
                                        <div>
                                            <p class="text-sm font-medium text-muted-foreground mb-2">Links</p>
                                            <div class="flex flex-wrap gap-2">
                                                @if($user->developerProfile->github_url)
                                                    <a href="{{ $user->developerProfile->github_url }}" target="_blank" 
                                                       class="inline-flex items-center gap-1 text-sm text-blue-600 hover:underline dark:text-blue-400">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                                        </svg>
                                                        GitHub
                                                    </a>
                                                @endif
                                                @if($user->developerProfile->linkedin_url)
                                                    <a href="{{ $user->developerProfile->linkedin_url }}" target="_blank" 
                                                       class="inline-flex items-center gap-1 text-sm text-blue-600 hover:underline dark:text-blue-400">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                                        </svg>
                                                        LinkedIn
                                                    </a>
                                                @endif
                                                @if($user->developerProfile->portfolio_url)
                                                    <a href="{{ $user->developerProfile->portfolio_url }}" target="_blank" 
                                                       class="inline-flex items-center gap-1 text-sm text-blue-600 hover:underline dark:text-blue-400">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                                        </svg>
                                                        Portfolio
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </div>

                        <!-- Custom Questions -->
                        @if($position->customQuestions->isNotEmpty())
                            <div class="bg-muted rounded-lg p-6 border border-border">
                                <div class="mb-4">
                                    <h3 class="text-lg font-semibold text-foreground">Additional Questions</h3>
                                    <p class="text-sm text-muted-foreground">Please answer the following questions from the employer</p>
                                </div>
                                <div class="space-y-6">
                                    @foreach($position->customQuestions as $question)
                                        <div class="space-y-2">
                                            <label for="question-{{ $question->id }}" class="flex items-center gap-2 text-sm font-medium text-foreground">
                                                {{ $question->question_text }}
                                                @if($question->is_required)
                                                    <span class="text-red-500">*</span>
                                                @endif
                                            </label>
                                            <textarea 
                                                id="question-{{ $question->id }}"
                                                x-model="customAnswers[{{ $question->id }}]"
                                                :disabled="processing"
                                                placeholder="{{ $question->is_required ? 'Your answer (required)' : 'Your answer (optional)' }}"
                                                rows="4"
                                                maxlength="2000"
                                                class="w-full px-3 py-2 bg-background border border-border rounded-lg text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent disabled:opacity-50 disabled:cursor-not-allowed"></textarea>
                                            <p class="text-xs text-muted-foreground">Maximum 2000 characters</p>
                                            <template x-if="errors['custom_answers.{{ $question->id }}']">
                                                <p class="text-sm text-red-600 dark:text-red-400" x-text="errors['custom_answers.{{ $question->id }}']"></p>
                                            </template>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <!-- Info Alert -->
                            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 flex items-start gap-3">
                                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="text-sm text-blue-800 dark:text-blue-200">
                                    This position does not require any additional information. Click "Submit Application" to complete your application.
                                </p>
                            </div>
                        @endif
                    </div>

                    <!-- Modal Footer -->
                    <div class="p-6 border-t border-border flex items-center justify-end gap-3">
                        <button @click="closeModal()" 
                                :disabled="processing"
                                class="px-4 py-2 text-sm font-medium rounded-lg border border-border bg-background hover:bg-muted text-foreground transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            Cancel
                        </button>
                        <button @click="submitApplication()" 
                                :disabled="processing"
                                class="px-4 py-2 text-sm font-medium rounded-lg bg-primary hover:bg-primary/90 text-primary-foreground transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-show="!processing">Submit Application</span>
                            <span x-show="processing">Submitting...</span>
                        </button>
                    </div>
                </div>
            </div>
        @endif
    @endauth

    <!-- Toast Notification -->
    <div id="toast" 
         class="fixed top-4 right-4 z-50 hidden transform transition-all duration-300"
         x-cloak>
        <div id="toast-type" class="rounded-lg shadow-lg p-4 min-w-[300px] max-w-md">
            <div class="flex items-center gap-3">
                <svg id="toast-icon-success" class="w-5 h-5 text-white hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <svg id="toast-icon-error" class="w-5 h-5 text-white hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                <p id="toast-message" class="text-sm font-medium text-white flex-1"></p>
            </div>
        </div>
    </div>

    <style>
        @keyframes slide-in {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        @keyframes slide-out {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
        .animate-slide-in {
            animation: slide-in 0.3s ease-out;
        }
        .animate-slide-out {
            animation: slide-out 0.3s ease-in;
        }
    </style>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('applicationModal', () => ({
                applicationModalOpen: false,
                processing: false,
                errors: {},
                customAnswers: @json($position->customQuestions->mapWithKeys(fn($q) => [$q->id => ''])->toArray()),
                
                showToast(message, type = 'success') {
                    const toast = document.getElementById('toast');
                    const toastMessage = document.getElementById('toast-message');
                    const toastType = document.getElementById('toast-type');
                    const toastIconSuccess = document.getElementById('toast-icon-success');
                    const toastIconError = document.getElementById('toast-icon-error');
                    
                    toastMessage.textContent = message;
                    toastType.className = type === 'success' ? 'bg-green-500' : 'bg-red-500';
                    
                    if (type === 'success') {
                        toastIconSuccess.classList.remove('hidden');
                        toastIconError.classList.add('hidden');
                    } else {
                        toastIconSuccess.classList.add('hidden');
                        toastIconError.classList.remove('hidden');
                    }
                    
                    toast.classList.remove('hidden');
                    toast.classList.add('animate-slide-in');
                    
                    setTimeout(() => {
                        toast.classList.remove('animate-slide-in');
                        toast.classList.add('animate-slide-out');
                        setTimeout(() => {
                            toast.classList.add('hidden');
                            toast.classList.remove('animate-slide-out');
                        }, 300);
                    }, 4000);
                },
                
                async submitApplication() {
                    this.processing = true;
                    this.errors = {};
                    
                    const formData = {
                        custom_answers: this.customAnswers,
                        _token: document.querySelector('meta[name="csrf-token"]').content
                    };
                    
                    try {
                        const response = await fetch('{{ route('positions.apply.store', $position) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': formData._token,
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify(formData)
                        });
                        
                        const contentType = response.headers.get('content-type');
                        
                        if (!contentType || !contentType.includes('application/json')) {
                            if (response.ok) {
                                this.showToast('Your application has been submitted successfully!', 'success');
                                this.applicationModalOpen = false;
                                setTimeout(() => {
                                    window.location.reload();
                                }, 500);
                                return;
                            } else {
                                throw new Error('Server returned non-JSON response');
                            }
                        }
                        
                        const data = await response.json();
                        
                        if (response.ok && data.success) {
                            this.showToast(data.message || 'Your application has been submitted successfully!', 'success');
                            this.applicationModalOpen = false;
                            setTimeout(() => {
                                window.location.reload();
                            }, 500);
                        } else {
                            if (data.errors) {
                                const flattenedErrors = {};
                                Object.keys(data.errors).forEach(key => {
                                    if (Array.isArray(data.errors[key])) {
                                        flattenedErrors[key] = data.errors[key][0];
                                    } else {
                                        flattenedErrors[key] = data.errors[key];
                                    }
                                });
                                this.errors = flattenedErrors;
                                this.showToast('Please check the form for errors.', 'error');
                            } else {
                                this.showToast(data.message || 'There was an error submitting your application.', 'error');
                            }
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        this.showToast('There was an error submitting your application. Please try again.', 'error');
                    } finally {
                        this.processing = false;
                    }
                },
                
                closeModal() {
                    if (!this.processing) {
                        this.applicationModalOpen = false;
                        this.errors = {};
                        this.customAnswers = @json($position->customQuestions->mapWithKeys(fn($q) => [$q->id => ''])->toArray());
                    }
                }
            }));
        });
    </script>
</div>
@endsection

