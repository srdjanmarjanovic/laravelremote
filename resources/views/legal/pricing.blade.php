@extends('layouts.public')

@section('title', 'Pricing - LaravelRemote.com')

@section('content')
<div class="bg-white dark:bg-gray-900 min-h-screen py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="text-center mb-16">
            <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">Simple, Transparent Pricing</h1>
            <p class="text-xl text-muted-foreground max-w-3xl mx-auto">
                Choose the perfect tier for your job posting. All plans include 30 days of visibility and access to qualified Laravel developers. <strong class="text-foreground">Featured and Top tiers are prominently displayed on our homepage</strong> for maximum exposure. <strong class="text-foreground">All companies with active job listings automatically appear on our <a href="{{ route('companies.index') }}" class="text-primary hover:underline">companies page</a></strong> to help candidates discover your organization.
            </p>
        </div>

        <!-- Pricing Cards -->
        <div class="grid md:grid-cols-3 gap-8 mb-16">
            <!-- Regular Tier -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg border-2 border-gray-200 dark:border-gray-700 p-8 hover:shadow-xl transition-shadow flex flex-col">
                <div class="text-center flex flex-col flex-grow">
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Regular</h3>
                    <div class="mb-6">
                        <span class="text-5xl font-bold text-gray-900 dark:text-white">${{ number_format($pricing['regular'], 0) }}</span>
                        <span class="text-muted-foreground">/listing</span>
                    </div>
                    <p class="text-muted-foreground mb-6">Perfect for standard job postings</p>
                    <ul class="text-left space-y-3 mb-8">
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-700 dark:text-gray-300">{{ $duration_days }} days visibility</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-700 dark:text-gray-300">Standard placement in listings</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-700 dark:text-gray-300">Analytics Overview</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-700 dark:text-gray-300">Applicant Tracking System</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-700 dark:text-gray-300">Company listed on <a href="{{ route('companies.index') }}" class="text-primary hover:underline">companies page</a></span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-gray-400 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            <span class="text-gray-500 dark:text-gray-400">Not featured on homepage</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-gray-400 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            <span class="text-gray-500 dark:text-gray-400">No company logo in the listing</span>
                        </li>
                    </ul>
                    <div class="mt-auto">
                    @auth
                        @if(auth()->user()->isHR())
                            <a href="{{ route('hr.positions.create') }}" class="block w-full text-center bg-gray-900 dark:bg-gray-100 text-white dark:text-gray-900 px-6 py-3 rounded-md font-semibold hover:bg-gray-800 dark:hover:bg-gray-200 transition-colors">
                                Post a Job
                            </a>
                        @else
                            <a href="{{ route('register') }}" class="block w-full text-center bg-gray-900 dark:bg-gray-100 text-white dark:text-gray-900 px-6 py-3 rounded-md font-semibold hover:bg-gray-800 dark:hover:bg-gray-200 transition-colors">
                                Get Started
                            </a>
                        @endif
                    @else
                        <a href="{{ route('register') }}" class="block w-full text-center bg-gray-900 dark:bg-gray-100 text-white dark:text-gray-900 px-6 py-3 rounded-md font-semibold hover:bg-gray-800 dark:hover:bg-gray-200 transition-colors">
                            Get Started
                        </a>
                    @endauth
                    </div>
                </div>
            </div>

            <!-- Featured Tier -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg border-2 border-primary p-8 hover:shadow-xl transition-shadow relative flex flex-col">
                <div class="absolute top-0 left-1/2 transform -translate-x-1/2 -translate-y-1/2">
                    <span class="bg-primary text-primary-foreground px-4 py-1 rounded-full text-sm font-semibold">Most Popular</span>
                </div>
                <div class="text-center flex flex-col flex-grow">
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Featured</h3>
                    <div class="mb-6">
                        <span class="text-5xl font-bold text-gray-900 dark:text-white">${{ number_format($pricing['featured'], 0) }}</span>
                        <span class="text-muted-foreground">/listing</span>
                    </div>
                    <p class="text-muted-foreground mb-6">Get more visibility for your posting</p>
                    <ul class="text-left space-y-3 mb-8">
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-700 dark:text-gray-300">{{ $duration_days }} days visibility</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-700 dark:text-gray-300">Highlighted among regular listings</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-700 dark:text-gray-300 font-semibold">⭐ Shown on homepage</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-700 dark:text-gray-300">Analytics Overview</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-700 dark:text-gray-300">Standard support</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-700 dark:text-gray-300">Applicant Tracking System</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-700 dark:text-gray-300">Company listed on <a href="{{ route('companies.index') }}" class="text-primary hover:underline">companies page</a></span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-700 dark:text-gray-300">Company logo in the listing</span>
                        </li>
                    </ul>
                    <div class="mt-auto">
                    @auth
                        @if(auth()->user()->isHR())
                            <a href="{{ route('hr.positions.create') }}" class="block w-full text-center bg-primary text-primary-foreground px-6 py-3 rounded-md font-semibold hover:bg-primary/90 transition-colors">
                                Post a Job
                            </a>
                        @else
                            <a href="{{ route('register') }}" class="block w-full text-center bg-primary text-primary-foreground px-6 py-3 rounded-md font-semibold hover:bg-primary/90 transition-colors">
                                Get Started
                            </a>
                        @endif
                    @else
                        <a href="{{ route('register') }}" class="block w-full text-center bg-primary text-primary-foreground px-6 py-3 rounded-md font-semibold hover:bg-primary/90 transition-colors">
                            Get Started
                        </a>
                    @endauth
                    </div>
                </div>
            </div>

            <!-- Top Tier -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg border-2 border-gray-200 dark:border-gray-700 p-8 hover:shadow-xl transition-shadow flex flex-col">
                <div class="text-center flex flex-col flex-grow">
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Top</h3>
                    <div class="mb-6">
                        <span class="text-5xl font-bold text-gray-900 dark:text-white">${{ number_format($pricing['top'], 0) }}</span>
                        <span class="text-muted-foreground">/listing</span>
                    </div>
                    <p class="text-muted-foreground mb-6">Maximum visibility and exposure</p>
                    <ul class="text-left space-y-3 mb-8">
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-700 dark:text-gray-300">{{ $duration_days }} days visibility</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-700 dark:text-gray-300">Pinned at the top of listings</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-700 dark:text-gray-300 font-semibold">⭐ Shown on homepage</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-700 dark:text-gray-300">Featured badge and premium styling</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-700 dark:text-gray-300">Analytics Overview</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-700 dark:text-gray-300">Priority support</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-700 dark:text-gray-300">Applicant Tracking System</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-700 dark:text-gray-300">Company listed on <a href="{{ route('companies.index') }}" class="text-primary hover:underline">companies page</a></span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-700 dark:text-gray-300">Company logo in the listing</span>
                        </li>
                    </ul>
                    <div class="mt-auto">
                    @auth
                        @if(auth()->user()->isHR())
                            <a href="{{ route('hr.positions.create') }}" class="block w-full text-center bg-gray-900 dark:bg-gray-100 text-white dark:text-gray-900 px-6 py-3 rounded-md font-semibold hover:bg-gray-800 dark:hover:bg-gray-200 transition-colors">
                                Post a Job
                            </a>
                        @else
                            <a href="{{ route('register') }}" class="block w-full text-center bg-gray-900 dark:bg-gray-100 text-white dark:text-gray-900 px-6 py-3 rounded-md font-semibold hover:bg-gray-800 dark:hover:bg-gray-200 transition-colors">
                                Get Started
                            </a>
                        @endif
                    @else
                        <a href="{{ route('register') }}" class="block w-full text-center bg-gray-900 dark:bg-gray-100 text-white dark:text-gray-900 px-6 py-3 rounded-md font-semibold hover:bg-gray-800 dark:hover:bg-gray-200 transition-colors">
                            Get Started
                        </a>
                    @endauth
                    </div>
                </div>
            </div>
        </div>

        <!-- Feature Comparison Table -->
        <div class="mb-16">
            <h2 class="text-3xl font-bold text-gray-900 dark:text-white text-center mb-8">Feature Comparison</h2>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900 dark:text-white">Feature</th>
                                <th class="px-6 py-4 text-center text-sm font-semibold text-gray-900 dark:text-white">Regular</th>
                                <th class="px-6 py-4 text-center text-sm font-semibold text-gray-900 dark:text-white">Featured</th>
                                <th class="px-6 py-4 text-center text-sm font-semibold text-gray-900 dark:text-white">Top</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">Listing Duration</td>
                                <td class="px-6 py-4 text-center text-sm text-gray-700 dark:text-gray-300">{{ $duration_days }} days</td>
                                <td class="px-6 py-4 text-center text-sm text-gray-700 dark:text-gray-300">{{ $duration_days }} days</td>
                                <td class="px-6 py-4 text-center text-sm text-gray-700 dark:text-gray-300">{{ $duration_days }} days</td>
                            </tr>
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">Placement Priority</td>
                                <td class="px-6 py-4 text-center text-sm text-gray-700 dark:text-gray-300">Standard</td>
                                <td class="px-6 py-4 text-center text-sm text-gray-700 dark:text-gray-300">Highlighted</td>
                                <td class="px-6 py-4 text-center text-sm text-green-600 dark:text-green-400 font-semibold">✓ Pinned at Top</td>
                            </tr>
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">Applicant Tracking System</td>
                                <td class="px-6 py-4 text-center text-sm text-green-600 dark:text-green-400">✓</td>
                                <td class="px-6 py-4 text-center text-sm text-green-600 dark:text-green-400">✓</td>
                                <td class="px-6 py-4 text-center text-sm text-green-600 dark:text-green-400">✓</td>
                            </tr>
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">Analytics</td>
                                <td class="px-6 py-4 text-center text-sm text-green-600 dark:text-green-400">✓ Analytics Overview</td>
                                <td class="px-6 py-4 text-center text-sm text-green-600 dark:text-green-400">✓ Analytics Overview</td>
                                <td class="px-6 py-4 text-center text-sm text-green-600 dark:text-green-400">✓ Analytics Overview</td>
                            </tr>
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">Homepage Visibility</td>
                                <td class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">—</td>
                                <td class="px-6 py-4 text-center text-sm text-green-600 dark:text-green-400 font-semibold">✓ Featured</td>
                                <td class="px-6 py-4 text-center text-sm text-green-600 dark:text-green-400 font-semibold">✓ Featured</td>
                            </tr>
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">Support</td>
                                <td class="px-6 py-4 text-center text-sm text-gray-700 dark:text-gray-300">Standard</td>
                                <td class="px-6 py-4 text-center text-sm text-gray-700 dark:text-gray-300">Standard</td>
                                <td class="px-6 py-4 text-center text-sm text-green-600 dark:text-green-400 font-semibold">✓ Priority</td>
                            </tr>
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">Visual Badge</td>
                                <td class="px-6 py-4 text-center text-sm text-gray-700 dark:text-gray-300">—</td>
                                <td class="px-6 py-4 text-center text-sm text-gray-700 dark:text-gray-300">✓</td>
                                <td class="x-6 py-4 text-center text-sm text-green-600 dark:text-green-400 font-semibold">✓ Premium</td>
                            </tr>
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">Company Logo displayed in the listing</td>
                                <td class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">—</td>
                                <td class="px-6 py-4 text-center text-sm text-green-600 dark:text-green-400">✓</td>
                                <td class="px-6 py-4 text-center text-sm text-green-600 dark:text-green-400">✓</td>
                            </tr>
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">Company listed on <a href="{{ route('companies.index') }}" class="text-primary hover:underline">companies page</a></td>
                                <td class="px-6 py-4 text-center text-sm text-green-600 dark:text-green-400">✓</td>
                                <td class="px-6 py-4 text-center text-sm text-green-600 dark:text-green-400">✓</td>
                                <td class="px-6 py-4 text-center text-sm text-green-600 dark:text-green-400">✓</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Upgrade Information -->
        <div class="bg-primary/10 dark:bg-primary/20 rounded-lg p-8 mb-16">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Upgrade Anytime</h2>
            <p class="text-gray-700 dark:text-gray-300 leading-relaxed mb-4">
                Already posted a job? You can upgrade your listing at any time to get more visibility. Upgrade pricing is prorated based on the remaining days in your listing period.
            </p>
            <ul class="list-disc pl-6 space-y-2 text-gray-700 dark:text-gray-300">
                <li>Upgrade from Regular to Featured or Top</li>
                <li>Upgrade from Featured to Top</li>
                <li>Prorated pricing ensures you only pay for the remaining time</li>
                <li>Downgrades are not available</li>
            </ul>
        </div>

        <!-- Listing Management Information -->
        <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-8 mb-16 border border-green-200 dark:border-green-800">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Flexible Listing Management</h2>
            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Extend Your Listing
                    </h3>
                    <p class="text-gray-700 dark:text-gray-300">
                        All listings can be extended upon expiration. Simply renew your listing to keep it active and continue receiving applications from qualified candidates.
                    </p>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                        </svg>
                        Control Applications
                    </h3>
                    <p class="text-gray-700 dark:text-gray-300">
                        You have full control over your job posting. Close applications when you've found the right candidate, or reopen them anytime to continue accepting new applications.
                    </p>
                </div>
            </div>
        </div>

        <!-- Payment Methods -->
        <div class="mb-16">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Secure Payment</h2>
            <p class="text-gray-700 dark:text-gray-300 leading-relaxed mb-4">
                We accept all major credit cards and process payments securely through trusted payment providers. Your payment information is never stored on our servers.
            </p>
            <div class="flex flex-wrap gap-4 items-center">
                <span class="text-sm text-muted-foreground">Accepted payment methods:</span>
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Credit Cards</span>
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Debit Cards</span>
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Other methods via payment provider</span>
            </div>
        </div>

        <!-- FAQ Section -->
        <div class="mb-16">
            <h2 class="text-3xl font-bold text-gray-900 dark:text-white text-center mb-8">Frequently Asked Questions</h2>
            <div class="space-y-6">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">How long do listings stay active?</h3>
                    <p class="text-gray-700 dark:text-gray-300">All listings remain active for {{ $duration_days }} days from the date of publication. After this period, listings expire and are removed from public view. However, you can extend your listing at any time to keep it active.</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Can I upgrade my listing later?</h3>
                    <p class="text-gray-700 dark:text-gray-300">Yes! You can upgrade your listing at any time during its active period. Upgrade pricing is prorated based on the remaining days.</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Can I close or reopen applications?</h3>
                    <p class="text-gray-700 dark:text-gray-300">Yes! You have full control over your job posting. You can close applications when you've found the right candidate, or reopen them at any time to continue accepting new applications. This gives you flexibility to manage your hiring process.</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Can I extend my listing after it expires?</h3>
                    <p class="text-gray-700 dark:text-gray-300">Absolutely! All listings can be extended upon expiration. Simply renew your listing through your dashboard to keep it active and continue receiving applications from qualified candidates.</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">What happens if I need to cancel?</h3>
                    <p class="text-gray-700 dark:text-gray-300">Payments are non-refundable except as required by law. However, you can remove your listing from public view at any time through your dashboard.</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Do I need to pay for each job posting?</h3>
                    <p class="text-gray-700 dark:text-gray-300">Yes, each job posting requires a separate payment. This ensures fair pricing and helps maintain the quality of listings on our platform.</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">What payment methods do you accept?</h3>
                    <p class="text-gray-700 dark:text-gray-300">We accept all major credit and debit cards through our secure payment processors. Payment information is processed securely and never stored on our servers.</p>
                </div>
            </div>
        </div>

        <!-- Feedback Section -->
        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-8 mb-8">
            <div class="text-center">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">We'd Love to Hear From You</h2>
                <p class="text-gray-700 dark:text-gray-300">
                    If you have any issues or suggestions, please feel free to reach out at <a href="mailto:hello@laravelremote.com" class="text-primary hover:underline font-medium">hello@laravelremote.com</a>
                </p>
            </div>
        </div>

        <!-- Terms Link -->
        <div class="text-center text-muted-foreground">
            <p class="mb-2">By posting a job, you agree to our <a href="{{ route('terms') }}" class="text-primary hover:underline">Terms & Conditions</a></p>
            <p>For questions about pricing, contact us at <a href="mailto:hello@laravelremote.com" class="text-primary hover:underline">hello@laravelremote.com</a></p>
        </div>
    </div>
</div>
@endsection
