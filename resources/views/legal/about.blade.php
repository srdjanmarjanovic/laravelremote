@extends('layouts.public')

@section('title', 'About Us - LaravelRemote.com')

@section('content')
<div class="bg-white dark:bg-gray-900 min-h-screen py-16">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="prose prose-lg dark:prose-invert max-w-none">
            <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-2">About LaravelRemote.com</h1>
            <p class="text-muted-foreground mb-8">Connecting remote Laravel developers with amazing opportunities worldwide</p>

            <div class="space-y-8">
                <section>
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mt-8 mb-4">Our Mission</h2>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                        LaravelRemote.com is dedicated to creating the premier platform for connecting remote Laravel developers with companies seeking top talent. We believe that great developers should have access to the best opportunities, regardless of their location, and that companies should be able to find the perfect Laravel talent from anywhere in the world.
                    </p>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed mt-4">
                        Our platform simplifies the job search and hiring process, making it easier for developers to find their dream remote position and for employers to discover exceptional Laravel talent.
                    </p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mt-8 mb-4">What We Do</h2>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                        LaravelRemote.com is a specialized job board platform focused exclusively on remote Laravel development positions. We provide a streamlined experience for both job seekers and employers, featuring:
                    </p>
                    <ul class="list-disc pl-6 space-y-2 text-gray-700 dark:text-gray-300 mt-4">
                        <li><strong>Curated Job Listings:</strong> High-quality remote Laravel positions from verified companies</li>
                        <li><strong>Developer Profiles:</strong> Comprehensive profiles showcasing skills, experience, and portfolios</li>
                        <li><strong>Advanced Search:</strong> Filter by technology stack, seniority level, salary range, and more</li>
                        <li><strong>Application Management:</strong> Track applications and manage your job search in one place</li>
                        <li><strong>Company Profiles:</strong> Detailed company information to help developers make informed decisions</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mt-8 mb-4">For Developers</h2>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed mb-4">
                        Whether you're a junior developer starting your career or a senior Laravel expert, LaravelRemote.com helps you find the perfect remote opportunity:
                    </p>
                    <ul class="list-disc pl-6 space-y-2 text-gray-700 dark:text-gray-300">
                        <li><strong>Create Your Profile:</strong> Build a professional profile showcasing your Laravel expertise, experience, and portfolio</li>
                        <li><strong>Browse Opportunities:</strong> Explore remote positions from companies around the world</li>
                        <li><strong>Apply Easily:</strong> Submit applications with cover letters and answers to custom questions</li>
                        <li><strong>Track Applications:</strong> Monitor your application status and stay organized</li>
                        <li><strong>Social Authentication:</strong> Sign in quickly with GitHub, Google, or LinkedIn</li>
                    </ul>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed mt-4">
                        Our platform is designed to make your job search as smooth and efficient as possible, so you can focus on what matters most—finding your next great opportunity.
                    </p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mt-8 mb-4">For Employers</h2>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed mb-4">
                        Hiring remote Laravel developers has never been easier. Our platform provides everything you need to attract and hire top talent:
                    </p>
                    <ul class="list-disc pl-6 space-y-2 text-gray-700 dark:text-gray-300">
                        <li><strong>Post Job Listings:</strong> Create detailed job postings with custom questions to find the right candidates</li>
                        <li><strong>Company Profiles:</strong> Showcase your company culture, mission, and what makes you unique</li>
                        <li><strong>Application Management:</strong> Review applications, track candidate status, and manage your hiring pipeline</li>
                        <li><strong>Technology Tags:</strong> Tag positions with relevant technologies to attract developers with the right skills</li>
                        <li><strong>Featured Listings:</strong> Boost visibility with featured and top-tier job listings</li>
                        <li><strong>Team Collaboration:</strong> Multiple team members can manage positions and applications</li>
                    </ul>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed mt-4">
                        We understand that finding the right developer is crucial to your success. That's why we've built tools to help you identify and connect with the best Laravel talent in the remote work ecosystem.
                    </p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mt-8 mb-4">Key Features</h2>
                    <div class="grid md:grid-cols-2 gap-6 mt-4">
                        <div class="bg-gray-50 dark:bg-gray-800 p-6 rounded-lg">
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">Flexible Application Options</h3>
                            <p class="text-gray-700 dark:text-gray-300">
                                Applications can be made both on platform and on external links, giving employers flexibility in their hiring process.
                            </p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-800 p-6 rounded-lg">
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">Social Authentication</h3>
                            <p class="text-gray-700 dark:text-gray-300">
                                Quick and secure sign-in with GitHub, Google, or LinkedIn—no need to remember another password.
                            </p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-800 p-6 rounded-lg">
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">Advanced Search & Filtering</h3>
                            <p class="text-gray-700 dark:text-gray-300">
                                Find exactly what you're looking for with filters for technology, seniority, remote type, and salary range.
                            </p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-800 p-6 rounded-lg">
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">Custom Application Questions</h3>
                            <p class="text-gray-700 dark:text-gray-300">
                                Employers can ask custom questions to better understand candidates and their fit for the role.
                            </p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-800 p-6 rounded-lg">
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">View Tracking</h3>
                            <p class="text-gray-700 dark:text-gray-300">
                                Track position views with anonymized analytics to understand job listing performance.
                            </p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-800 p-6 rounded-lg">
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">Modern UI/UX</h3>
                            <p class="text-gray-700 dark:text-gray-300">
                                Beautiful, responsive design with dark mode support for a comfortable browsing experience.
                            </p>
                        </div>
                    </div>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mt-8 mb-4">Our Commitment</h2>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                        We're committed to maintaining a high-quality platform that serves both developers and employers. We continuously work to improve our features, enhance user experience, and ensure that LaravelRemote.com remains the go-to destination for remote Laravel opportunities.
                    </p>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed mt-4">
                        Your privacy and security are paramount to us. We implement industry-standard security measures and are transparent about how we handle your data. For more information, please review our <a href="{{ route('privacy') }}" class="text-primary hover:underline">Privacy Policy</a> and <a href="{{ route('terms') }}" class="text-primary hover:underline">Terms & Conditions</a>.
                    </p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mt-8 mb-4">Get Started</h2>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed mb-4">
                        Ready to find your next opportunity or hire top Laravel talent?
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 mt-6">
                        <a href="/register" class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-primary hover:bg-primary/90 transition-colors">
                            Create Developer Profile
                        </a>
                        <a href="/register" class="inline-flex items-center justify-center px-6 py-3 border border-primary text-base font-medium rounded-md text-primary bg-transparent hover:bg-primary/10 transition-colors">
                            Post a Job
                        </a>
                    </div>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mt-8 mb-4">Contact Us</h2>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed mb-4">
                        Have questions or feedback? We'd love to hear from you!
                    </p>
                    <div class="bg-gray-50 dark:bg-gray-800 p-6 rounded-lg">
                        <p class="text-gray-700 dark:text-gray-300 mb-2">
                            Email: <a href="mailto:hello@laravelremote.com" class="text-primary hover:underline">hello@laravelremote.com</a>
                        </p>
                        <p class="text-gray-700 dark:text-gray-300 text-sm">
                            If you have any issues or suggestions, please feel free to reach out at <a href="mailto:hello@laravelremote.com" class="text-primary hover:underline">hello@laravelremote.com</a>
                        </p>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>
@endsection
