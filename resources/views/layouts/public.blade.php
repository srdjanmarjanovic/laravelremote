<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ appearance: '{{ $appearance ?? 'system' }}' }" x-init="appearance = (function() { const value = '; ' + document.cookie; const parts = value.split('; appearance='); if (parts.length === 2) return parts.pop().split(';').shift(); return 'system'; })()">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Remote Laravel Jobs')</title>

    <!-- Appearance Script - Must run before page renders to prevent flicker -->
    <script>
        (function() {
            // Get appearance from cookie or default to system
            function getCookie(name) {
                const value = `; ${document.cookie}`;
                const parts = value.split(`; ${name}=`);
                if (parts.length === 2) return parts.pop().split(';').shift();
                return null;
            }

            const appearance = getCookie('appearance') || '{{ $appearance ?? "system" }}' || 'system';

            if (appearance === 'dark') {
                document.documentElement.classList.add('dark');
            } else if (appearance === 'light') {
                document.documentElement.classList.remove('dark');
            } else if (appearance === 'system') {
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                if (prefersDark) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            }
        })();
    </script>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Meta Tags -->
    <meta name="description" content="@yield('description', 'Find the best remote Laravel developer jobs from top companies around the world.')">
    <meta name="keywords" content="remote laravel jobs, laravel developer, remote php jobs, work from home">

    <!-- Scripts -->
    @vite(['resources/css/app.css'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="font-sans antialiased bg-gray-50 dark:bg-gray-900 transition-colors duration-300">
    <!-- Navigation -->
    <nav class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 sticky top-0 z-50 transition-colors duration-300" x-data="{ mobileMenuOpen: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <!-- Logo -->
                    <a href="/" class="flex items-center space-x-2">
                        <svg class="h-8 w-8 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <span class="text-xl font-bold text-foreground">RemoteLaravel<span class="text-primary">Jobs</span></span>
                    </a>

                    <!-- Navigation Links - Desktop -->
                    <div class="hidden md:ml-10 md:flex md:space-x-8">
                        <a href="/positions" class="text-muted-foreground hover:text-primary px-3 py-2 text-sm font-medium transition-colors">
                            Browse Jobs
                        </a>
                        <a href="/companies" class="text-muted-foreground hover:text-primary px-3 py-2 text-sm font-medium transition-colors">
                            Companies
                        </a>
                        <a href="{{ route('about') }}" class="text-muted-foreground hover:text-primary px-3 py-2 text-sm font-medium transition-colors">
                            About
                        </a>
                    </div>
                </div>

                <!-- Desktop Actions -->
                <div class="hidden md:flex items-center space-x-4">
                    <!-- Appearance Toggle -->
                    <button @click="
                        const current = appearance;
                        if (current === 'light') {
                            appearance = 'dark';
                        } else if (current === 'dark') {
                            appearance = 'system';
                        } else {
                            appearance = 'light';
                        }
                        const maxAge = 365 * 24 * 60 * 60;
                        document.cookie = 'appearance=' + appearance + ';path=/;max-age=' + maxAge + ';SameSite=Lax';
                        if (appearance === 'dark') {
                            document.documentElement.classList.add('dark');
                        } else if (appearance === 'light') {
                            document.documentElement.classList.remove('dark');
                        } else {
                            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                            if (prefersDark) {
                                document.documentElement.classList.add('dark');
                            } else {
                                document.documentElement.classList.remove('dark');
                            }
                        }
                    " class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors" :title="appearance === 'light' ? 'Light mode' : appearance === 'dark' ? 'Dark mode' : 'System mode'">
                        <svg x-show="appearance === 'light'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <svg x-show="appearance === 'dark'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                        <svg x-show="appearance === 'system'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" x-cloak>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </button>

                    @auth
                        <a href="/dashboard" class="text-muted-foreground hover:text-primary px-3 py-2 text-sm font-medium transition-colors">
                            Dashboard
                        </a>
                    @else
                        <a href="/login" class="text-muted-foreground hover:text-primary px-3 py-2 text-sm font-medium transition-colors">
                            Sign in
                        </a>
                        <a href="/register" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-primary-foreground bg-primary hover:bg-primary/90 transition-colors">
                            Post a Job
                        </a>
                    @endauth
                </div>

                <!-- Mobile Menu Button -->
                <div class="md:hidden flex items-center">
                    <button
                        @click="mobileMenuOpen = !mobileMenuOpen"
                        class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors"
                        aria-label="Toggle menu"
                    >
                        <svg x-show="!mobileMenuOpen" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <svg x-show="mobileMenuOpen" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" x-cloak>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div
            x-show="mobileMenuOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            x-cloak
            class="md:hidden border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800"
        >
            <div class="px-2 pt-2 pb-3 space-y-1">
                <!-- Navigation Links -->
                <a href="/positions" @click="mobileMenuOpen = false" class="block px-3 py-2 text-base font-medium text-muted-foreground hover:text-primary hover:bg-gray-50 dark:hover:bg-gray-700 rounded-md transition-colors">
                    Browse Jobs
                </a>
                <a href="/companies" @click="mobileMenuOpen = false" class="block px-3 py-2 text-base font-medium text-muted-foreground hover:text-primary hover:bg-gray-50 dark:hover:bg-gray-700 rounded-md transition-colors">
                    Companies
                </a>
                <a href="{{ route('about') }}" @click="mobileMenuOpen = false" class="block px-3 py-2 text-base font-medium text-muted-foreground hover:text-primary hover:bg-gray-50 dark:hover:bg-gray-700 rounded-md transition-colors">
                    About
                </a>

                <!-- Divider -->
                <div class="border-t border-gray-200 dark:border-gray-700 my-2"></div>

                <!-- Theme Toggle -->
                <div class="px-3 py-2">
                    <div class="flex items-center justify-between">
                        <span class="text-base font-medium text-foreground">Theme</span>
                        <button @click="
                            const current = appearance;
                            if (current === 'light') {
                                appearance = 'dark';
                            } else if (current === 'dark') {
                                appearance = 'system';
                            } else {
                                appearance = 'light';
                            }
                            const maxAge = 365 * 24 * 60 * 60;
                            document.cookie = 'appearance=' + appearance + ';path=/;max-age=' + maxAge + ';SameSite=Lax';
                            if (appearance === 'dark') {
                                document.documentElement.classList.add('dark');
                            } else if (appearance === 'light') {
                                document.documentElement.classList.remove('dark');
                            } else {
                                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                                if (prefersDark) {
                                    document.documentElement.classList.add('dark');
                                } else {
                                    document.documentElement.classList.remove('dark');
                                }
                            }
                        " class="flex items-center space-x-2 p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors rounded-md hover:bg-gray-50 dark:hover:bg-gray-700">
                            <svg x-show="appearance === 'light'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            <svg x-show="appearance === 'dark'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                            </svg>
                            <svg x-show="appearance === 'system'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" x-cloak>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <span class="text-sm" x-text="appearance === 'light' ? 'Light' : appearance === 'dark' ? 'Dark' : 'System'"></span>
                        </button>
                    </div>
                </div>

                <!-- Divider -->
                <div class="border-t border-gray-200 dark:border-gray-700 my-2"></div>

                <!-- Auth Links -->
                @auth
                    <a href="/dashboard" @click="mobileMenuOpen = false" class="block px-3 py-2 text-base font-medium text-muted-foreground hover:text-primary hover:bg-gray-50 dark:hover:bg-gray-700 rounded-md transition-colors">
                        Dashboard
                    </a>
                @else
                    <a href="/login" @click="mobileMenuOpen = false" class="block px-3 py-2 text-base font-medium text-muted-foreground hover:text-primary hover:bg-gray-50 dark:hover:bg-gray-700 rounded-md transition-colors">
                        Sign in
                    </a>
                    <a href="/register" @click="mobileMenuOpen = false" class="block w-full mt-2 px-4 py-2 text-center text-base font-medium rounded-md text-primary-foreground bg-primary hover:bg-primary/90 transition-colors">
                        Post a Job
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 mt-16 transition-colors duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="space-y-4">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wider">Company</h3>
                    <ul class="space-y-2">
                        <li><a href="{{ route('about') }}" class="text-muted-foreground hover:text-primary transition-colors">About</a></li>
                        <li><a href="{{ route('privacy') }}" class="text-muted-foreground hover:text-primary transition-colors">Privacy</a></li>
                        <li><a href="{{ route('terms') }}" class="text-muted-foreground hover:text-primary transition-colors">Terms</a></li>
                    </ul>
                </div>

                <div class="space-y-4">
                    <h3 class="text-sm font-semibold text-foreground uppercase tracking-wider">For Job Seekers</h3>
                    <ul class="space-y-2">
                        <li><a href="/positions" class="text-muted-foreground hover:text-primary transition-colors">Browse Jobs</a></li>
                        <li><a href="/register" class="text-muted-foreground hover:text-primary transition-colors">Create Profile</a></li>
                    </ul>
                </div>

                <div class="space-y-4">
                    <h3 class="text-sm font-semibold text-foreground uppercase tracking-wider">For Employers</h3>
                    <ul class="space-y-2">
                        <li><a href="/register" class="text-muted-foreground hover:text-primary transition-colors">Post a Job</a></li>
                        <li><a href="{{ route('pricing') }}" class="text-muted-foreground hover:text-primary transition-colors">Pricing</a></li>
                    </ul>
                </div>
            </div>

            <div class="mt-8 border-t border-gray-200 dark:border-gray-700 pt-8">
                <div class="text-center mb-4">
                    <p class="text-gray-700 dark:text-gray-300 text-sm">
                        If you have any issues or suggestions, please feel free to reach out at <a href="mailto:hello@laravelremote.com" class="text-primary hover:underline">hello@laravelremote.com</a>
                    </p>
                </div>
                <p class="text-center text-gray-500 dark:text-gray-400 text-sm">
                    © {{ date('Y') }} RemoteLaravelJobs. All rights reserved.
                </p>
            </div>
        </div>
    </footer>

    <style>
        [x-cloak] { display: none !important; }
    </style>
    @vite(['resources/js/app.ts'])
</body>
</html>

