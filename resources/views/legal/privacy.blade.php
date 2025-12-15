@extends('layouts.public')

@section('title', 'Privacy Policy - LaravelRemote.com')

@section('content')
<div class="bg-white dark:bg-gray-900 min-h-screen py-16">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="prose prose-lg dark:prose-invert max-w-none">
            <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-2">Privacy Policy</h1>
            <p class="text-muted-foreground mb-8">Last updated: {{ date('F j, Y') }}</p>

            <div class="space-y-8">
                <section>
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mt-8 mb-4">1. Introduction</h2>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                        FooBar Dev ("we," "our," or "us") operates LaravelRemote.com (the "Platform"), a job board connecting remote Laravel developers with employers. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use our Platform.
                    </p>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed mt-4">
                        By using our Platform, you consent to the data practices described in this Privacy Policy. If you do not agree with the data practices described, please do not use our Platform.
                    </p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mt-8 mb-4">2. Information We Collect</h2>
                    
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mt-6 mb-3">2.1 Information You Provide</h3>
                    <ul class="list-disc pl-6 space-y-2 text-gray-700 dark:text-gray-300">
                        <li><strong>Account Information:</strong> Name, email address, password, role (developer, HR, admin)</li>
                        <li><strong>Profile Information:</strong> Professional profile, CV/resume, photo, skills, work experience, portfolio links</li>
                        <li><strong>Company Information:</strong> Company name, logo, description, website, location (for HR users)</li>
                        <li><strong>Job Postings:</strong> Job title, description, requirements, salary range, location preferences</li>
                        <li><strong>Applications:</strong> Cover letters, answers to custom questions, application status</li>
                        <li><strong>Payment Information:</strong> Billing details, payment method (processed securely through third-party payment processors)</li>
                        <li><strong>Communication:</strong> Messages sent through the Platform, support requests</li>
                    </ul>

                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mt-6 mb-3">2.2 Automatically Collected Information</h3>
                    <ul class="list-disc pl-6 space-y-2 text-gray-700 dark:text-gray-300">
                        <li><strong>Usage Data:</strong> Pages visited, time spent on pages, click patterns, search queries</li>
                        <li><strong>Device Information:</strong> IP address, browser type, operating system, device type</li>
                        <li><strong>Location Data:</strong> General location based on IP address</li>
                        <li><strong>Cookies and Tracking:</strong> See our Cookie Policy below</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mt-8 mb-4">3. How We Use Your Information</h2>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed mb-4">We use the information we collect for the following purposes:</p>
                    <ul class="list-disc pl-6 space-y-2 text-gray-700 dark:text-gray-300">
                        <li><strong>Platform Operation:</strong> To provide, maintain, and improve our services</li>
                        <li><strong>Account Management:</strong> To create and manage your account, authenticate users</li>
                        <li><strong>Job Matching:</strong> To connect developers with relevant job opportunities and employers with qualified candidates</li>
                        <li><strong>Communication:</strong> To send notifications, updates, and respond to inquiries</li>
                        <li><strong>Payment Processing:</strong> To process payments for job postings and upgrades</li>
                        <li><strong>Analytics:</strong> To analyze Platform usage, improve user experience, and develop new features</li>
                        <li><strong>Security:</strong> To detect and prevent fraud, abuse, and security threats</li>
                        <li><strong>Legal Compliance:</strong> To comply with legal obligations and enforce our Terms & Conditions</li>
                        <li><strong>Marketing:</strong> To send promotional communications (with your consent, where required)</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mt-8 mb-4">4. Legal Basis for Processing (GDPR)</h2>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed mb-4">For users in the European Economic Area (EEA), we process your personal data based on the following legal grounds:</p>
                    <ul class="list-disc pl-6 space-y-2 text-gray-700 dark:text-gray-300">
                        <li><strong>Contract Performance:</strong> To fulfill our contract with you (providing Platform services)</li>
                        <li><strong>Legitimate Interests:</strong> To operate and improve our Platform, prevent fraud, ensure security</li>
                        <li><strong>Consent:</strong> For marketing communications and optional features (you may withdraw consent at any time)</li>
                        <li><strong>Legal Obligation:</strong> To comply with applicable laws and regulations</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mt-8 mb-4">5. Information Sharing and Disclosure</h2>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed mb-4">We share your information in the following circumstances:</p>
                    
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mt-6 mb-3">5.1 Public Information</h3>
                    <ul class="list-disc pl-6 space-y-2 text-gray-700 dark:text-gray-300">
                        <li>Developer profiles (name, skills, experience) are visible to employers when you apply</li>
                        <li>Job postings are publicly visible on the Platform</li>
                        <li>Company information is displayed with job postings</li>
                    </ul>

                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mt-6 mb-3">5.2 Service Providers</h3>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed">We share information with trusted third-party service providers who assist us in:</p>
                    <ul class="list-disc pl-6 space-y-2 text-gray-700 dark:text-gray-300">
                        <li>Payment processing (Lemon Squeezy, Paddle, Creem)</li>
                        <li>Cloud hosting and infrastructure</li>
                        <li>Email delivery services</li>
                        <li>Analytics and performance monitoring</li>
                        <li>Customer support tools</li>
                    </ul>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed mt-4">These providers are contractually obligated to protect your information and use it only for specified purposes.</p>

                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mt-6 mb-3">5.3 Legal Requirements</h3>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed">We may disclose information if required by law, court order, or government regulation, or to protect our rights, property, or safety.</p>

                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mt-6 mb-3">5.4 Business Transfers</h3>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed">In the event of a merger, acquisition, or sale of assets, your information may be transferred to the acquiring entity.</p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mt-8 mb-4">6. Your Rights (GDPR & CCPA)</h2>
                    
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mt-6 mb-3">6.1 GDPR Rights (EU Users)</h3>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed mb-4">If you are located in the EEA, you have the following rights:</p>
                    <ul class="list-disc pl-6 space-y-2 text-gray-700 dark:text-gray-300">
                        <li><strong>Right of Access:</strong> Request a copy of your personal data</li>
                        <li><strong>Right to Rectification:</strong> Correct inaccurate or incomplete data</li>
                        <li><strong>Right to Erasure:</strong> Request deletion of your personal data ("right to be forgotten")</li>
                        <li><strong>Right to Restrict Processing:</strong> Limit how we use your data</li>
                        <li><strong>Right to Data Portability:</strong> Receive your data in a structured, machine-readable format</li>
                        <li><strong>Right to Object:</strong> Object to processing based on legitimate interests</li>
                        <li><strong>Right to Withdraw Consent:</strong> Withdraw consent for processing based on consent</li>
                        <li><strong>Right to Lodge a Complaint:</strong> File a complaint with your local data protection authority</li>
                    </ul>

                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mt-6 mb-3">6.2 CCPA Rights (California Users)</h3>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed mb-4">If you are a California resident, you have the following rights:</p>
                    <ul class="list-disc pl-6 space-y-2 text-gray-700 dark:text-gray-300">
                        <li><strong>Right to Know:</strong> Request information about categories and specific pieces of personal information collected</li>
                        <li><strong>Right to Delete:</strong> Request deletion of personal information</li>
                        <li><strong>Right to Opt-Out:</strong> Opt-out of the sale of personal information (we do not sell personal information)</li>
                        <li><strong>Right to Non-Discrimination:</strong> We will not discriminate against you for exercising your rights</li>
                    </ul>

                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mt-6 mb-3">6.3 Exercising Your Rights</h3>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                        To exercise any of these rights, please contact us at <a href="mailto:hello@laravelremote.com" class="text-primary hover:underline">hello@laravelremote.com</a>. We will respond to your request within 30 days (or as required by applicable law).
                    </p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mt-8 mb-4">7. CCPA Disclosures</h2>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed mb-4"><strong>Categories of Personal Information Collected:</strong></p>
                    <ul class="list-disc pl-6 space-y-2 text-gray-700 dark:text-gray-300">
                        <li>Identifiers (name, email, IP address)</li>
                        <li>Personal information categories (profile data, employment history)</li>
                        <li>Commercial information (payment history, transaction records)</li>
                        <li>Internet activity (browsing history, search queries)</li>
                        <li>Geolocation data (general location from IP)</li>
                    </ul>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed mt-4 mb-4"><strong>Business Purposes:</strong> Service delivery, security, analytics, legal compliance, customer support</p>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed mb-4"><strong>Third-Party Sharing:</strong> We share information with service providers (payment processors, hosting providers) for business purposes only. We do not sell personal information.</p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mt-8 mb-4">8. Cookies and Tracking Technologies</h2>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed mb-4">We use cookies and similar technologies to:</p>
                    <ul class="list-disc pl-6 space-y-2 text-gray-700 dark:text-gray-300">
                        <li>Authenticate users and maintain sessions</li>
                        <li>Remember preferences (theme, language)</li>
                        <li>Analyze Platform usage and performance</li>
                        <li>Provide personalized content</li>
                    </ul>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed mt-4">
                        You can control cookies through your browser settings. However, disabling cookies may limit Platform functionality.
                    </p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mt-8 mb-4">9. Data Security</h2>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                        We implement industry-standard security measures to protect your information, including encryption, secure servers, access controls, and regular security assessments. However, no method of transmission over the Internet is 100% secure.
                    </p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mt-8 mb-4">10. Data Retention</h2>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                        We retain your information for as long as necessary to provide services, comply with legal obligations, resolve disputes, and enforce agreements. When you delete your account, we will delete or anonymize your personal information, except where retention is required by law.
                    </p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mt-8 mb-4">11. International Data Transfers</h2>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                        Your information may be transferred to and processed in countries other than your country of residence. We ensure appropriate safeguards are in place, including Standard Contractual Clauses (SCCs) for transfers from the EEA, to protect your information in accordance with this Privacy Policy.
                    </p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mt-8 mb-4">12. Children's Privacy</h2>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                        Our Platform is not intended for individuals under 18 years of age. We do not knowingly collect personal information from children. If you believe we have collected information from a child, please contact us immediately.
                    </p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mt-8 mb-4">13. Changes to This Privacy Policy</h2>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                        We may update this Privacy Policy from time to time. We will notify you of material changes by posting the new Privacy Policy on this page and updating the "Last updated" date. Your continued use of the Platform after changes constitutes acceptance of the updated policy.
                    </p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mt-8 mb-4">14. Contact Us</h2>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed mb-4">
                        If you have questions about this Privacy Policy or wish to exercise your rights, please contact us:
                    </p>
                    <div class="bg-gray-50 dark:bg-gray-800 p-6 rounded-lg">
                        <p class="text-gray-700 dark:text-gray-300"><strong>FooBar Dev</strong></p>
                        <p class="text-gray-700 dark:text-gray-300">Email: <a href="mailto:hello@laravelremote.com" class="text-primary hover:underline">hello@laravelremote.com</a></p>
                        <p class="text-gray-700 dark:text-gray-300 mt-2"><em>Please include "Privacy Request" in the subject line for data-related inquiries.</em></p>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>
@endsection
