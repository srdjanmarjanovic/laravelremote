@extends('layouts.public')

@section('title', $position->title . ' at ' . $position->company->name)
@section('description', $position->short_description)

@section('content')
<div class="bg-gray-50 dark:bg-gray-900 min-h-screen transition-colors duration-300">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Back Button -->
        <a href="/" class="inline-flex items-center text-sm text-gray-600 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 mb-6 transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to all positions
        </a>

        <!-- Position Header Card -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-8 mb-6 transition-colors duration-300">
            <div class="flex flex-col md:flex-row gap-6">
                <!-- Company Logo -->
                @if($position->company->logo)
                    <img src="{{ $position->company->logo }}" alt="{{ $position->company->name }}" class="w-20 h-20 rounded-lg object-cover">
                @else
                    <div class="w-20 h-20 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-2xl">
                        {{ substr($position->company->name, 0, 1) }}
                    </div>
                @endif

                <!-- Header Info -->
                <div class="flex-1">
                    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                                {{ $position->title }}
                                @if($position->is_featured)
                                    <span class="inline-flex items-center px-3 py-1 text-sm font-medium rounded-full bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 ml-2">
                                        ⭐ Featured
                                    </span>
                                @endif
                            </h1>
                            <p class="text-xl text-gray-600 dark:text-gray-400 mb-4">{{ $position->company->name }}</p>

                            <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600 dark:text-gray-400">
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
                                    </svg>
                                    {{ ucfirst($position->remote_type) }} Remote
                                    @if($position->location_restriction)
                                        - {{ $position->location_restriction }}
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

                                <span class="text-gray-500">
                                    Posted {{ $position->published_at->diffForHumans() }}
                                </span>
                            </div>
                        </div>

                        <!-- Apply Button -->
                        <div class="flex-shrink-0">
                            @auth
                                @if($position->canReceiveApplications())
                                    <a href="/jobs/{{ $position->slug }}/apply" class="inline-flex items-center px-6 py-3 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 text-white font-medium rounded-lg transition-colors">
                                        Apply Now
                                        <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </a>
                                @elseif($position->is_external)
                                    <a href="{{ $position->external_apply_url }}" target="_blank" class="inline-flex items-center px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors">
                                        Apply on Company Site
                                        <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                        </svg>
                                    </a>
                                @else
                                    <span class="inline-flex items-center px-6 py-3 bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-lg">
                                        Applications Closed
                                    </span>
                                @endif
                            @else
                                <a href="/register" class="inline-flex items-center px-6 py-3 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 text-white font-medium rounded-lg transition-colors">
                                    Sign up to Apply
                                </a>
                            @endauth
                        </div>
                    </div>

                    <!-- Technologies -->
                    @if($position->technologies->isNotEmpty())
                        <div class="mt-4 flex flex-wrap gap-2">
                            @foreach($position->technologies as $tech)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-indigo-100 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-200">
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
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-8 transition-colors duration-300">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">About the Position</h2>
                    <div class="prose dark:prose-invert max-w-none text-gray-700 dark:text-gray-300">
                        {!! nl2br(e($position->long_description)) !!}
                    </div>
                </div>

                <!-- Custom Questions Preview -->
                @if($position->customQuestions->isNotEmpty())
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-8 transition-colors duration-300">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Application Questions</h2>
                        <p class="text-gray-600 dark:text-gray-400 mb-4">You'll be asked to answer these questions when applying:</p>
                        <ol class="space-y-2 list-decimal list-inside text-gray-700 dark:text-gray-300">
                            @foreach($position->customQuestions as $question)
                                <li>
                                    {{ $question->question_text }}
                                    @if($question->is_required)
                                        <span class="text-red-600 dark:text-red-400">*</span>
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
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 transition-colors duration-300">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">About {{ $position->company->name }}</h3>

                    @if($position->company->description)
                        <p class="text-gray-700 dark:text-gray-300 mb-4">{{ $position->company->description }}</p>
                    @endif

                    @if($position->company->website)
                        <a href="{{ $position->company->website }}" target="_blank" class="text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                            </svg>
                            Visit Website
                        </a>
                    @endif
                </div>

                <!-- Position Stats -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 transition-colors duration-300">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Position Stats</h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Applications:</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ $position->applications_count }}</span>
                        </div>
                        @if($position->expires_at)
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Expires:</span>
                                <span class="font-medium text-gray-900 dark:text-white">{{ $position->expires_at->format('M d, Y') }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Share -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 transition-colors duration-300">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Share this position</h3>
                    <div class="flex gap-2">
                        <button onclick="navigator.clipboard.writeText(window.location.href)" class="flex-1 px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-sm transition-colors">
                            Copy Link
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

