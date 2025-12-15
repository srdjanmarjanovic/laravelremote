@extends('layouts.public')

@section('title', 'Terms & Conditions - LaravelRemote.com')

@section('content')
<div class="bg-white dark:bg-gray-900 min-h-screen py-16">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="prose prose-lg dark:prose-invert max-w-none">
            <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-2">Terms & Conditions</h1>
            <p class="text-muted-foreground mb-8">Last updated: {{ date('F j, Y') }}</p>

            <div class="space-y-8">
                <section>
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mt-8 mb-4">1. Agreement to Terms</h2>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                        By accessing or using LaravelRemote.com (the "Platform") operated by FooBar Dev ("we," "our," or "us"), you agree to be bound by these Terms & Conditions ("Terms"). If you do not agree to these Terms, you may not use the Platform.
                    </p>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed mt-4">
                        We reserve the right to modify these Terms at any time. Material changes will be notified by posting the updated Terms on this page. Your continued use of the Platform after changes constitutes acceptance of the updated Terms.
                    </p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mt-8 mb-4">2. Platform Description</h2>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                        LaravelRemote.com is a job board platform that connects remote Laravel developers with employers seeking to hire remote talent. The Platform facilitates job postings, applications, and professional networking.
                    </p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mt-8 mb-4">3. User Accounts</h2>
                    
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mt-6 mb-3">3.1 Account Creation</h3>
                    <ul class="list-disc pl-6 space-y-2 text-gray-700 dark:text-gray-300">
                        <li>You must be at least 18 years old to create an account</li>
                        <li>You must provide accurate, current, and complete information</li>
                        <li>You are responsible for maintaining the confidentiality of your account credentials</li>
                        <li>You are responsible for all activities that occur under your account</li>
                        <li>You must notify us immediately of any unauthorized use of your account</li>
                    </ul>

                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mt-6 mb-3">3.2 Account Types</h3>
                    <ul class="list-disc pl-6 space-y-2 text-gray-700 dark:text-gray-300">
                        <li><strong>Developer Accounts:</strong> For job seekers seeking remote Laravel positions</li>
                        <li><strong>HR Accounts:</strong> For employers posting job opportunities</li>
                        <li><strong>Admin Accounts:</strong> For platform administrators (by invitation only)</li>
                    </ul>

                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mt-6 mb-3">3.3 Account Termination</h3>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                        We reserve the right to suspend or terminate your account at any time for violation of these Terms, fraudulent activity, or any other reason we deem necessary to protect the Platform and its users.
                    </p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mt-8 mb-4">4. Acceptable Use</h2>
                    
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mt-6 mb-3">4.1 Permitted Use</h3>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed">You may use the Platform to:</p>
                    <ul class="list-disc pl-6 space-y-2 text-gray-700 dark:text-gray-300">
                        <li>Browse and search for job opportunities (developers)</li>
                        <li>Post legitimate job openings (HR users)</li>
                        <li>Apply for positions (developers)</li>
                        <li>Manage applications and candidates (HR users)</li>
                        <li>Build professional profiles</li>
                    </ul>

                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mt-6 mb-3">4.2 Prohibited Activities</h3>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed">You agree NOT to:</p>
                    <ul class="list-disc pl-6 space-y-2 text-gray-700 dark:text-gray-300">
                        <li>Post false, misleading, or fraudulent information</li>
                        <li>Impersonate any person or entity</li>
                        <li>Post jobs that discriminate based on race, gender, religion, age, disability, or other protected characteristics</li>
                        <li>Post spam, pyramid schemes, or multi-level marketing opportunities</li>
                        <li>Harvest or collect user information without consent</li>
                        <li>Use automated systems (bots, scrapers) to access the Platform</li>
                        <li>Interfere with or disrupt the Platform's operation</li>
                        <li>Attempt to gain unauthorized access to any part of the Platform</li>
                        <li>Post content that is illegal, harmful, or violates third-party rights</li>
                        <li>Reverse engineer or attempt to extract source code</li>
                        <li>Use the Platform for any commercial purpose other than job posting/applying</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mt-8 mb-4">5. Job Posting Terms (HR Users)</h2>
                    
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mt-6 mb-3">5.1 Job Posting Requirements</h3>
                    <ul class="list-disc pl-6 space-y-2 text-gray-700 dark:text-gray-300">
                        <li>Job postings must be for legitimate remote Laravel developer positions</li>
                        <li>Job descriptions must be accurate and complete</li>
                        <li>You must have authorization to hire for the position</li>
                        <li>Positions must comply with applicable employment laws</li>
                        <li>You must complete company profile setup before posting</li>
                    </ul>

                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mt-6 mb-3">5.2 Payment Terms</h3>
                    <ul class="list-disc pl-6 space-y-2 text-gray-700 dark:text-gray-300">
                        <li>Job postings require payment before publication</li>
                        <li>Pricing tiers: Regular ($49), Featured ($99), Top ($199)</li>
                        <li>All tiers have a 30-day duration from publication</li>
                        <li>Payments are processed through secure third-party payment processors</li>
                        <li>All fees are non-refundable except as required by law or as specified in our refund policy</li>
                    </ul>

                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mt-6 mb-3">5.3 Listing Duration and Expiration</h3>
                    <ul class="list-disc pl-6 space-y-2 text-gray-700 dark:text-gray-300">
                        <li>Listings expire automatically after 30 days</li>
                        <li>Expired listings are removed from public view</li>
                        <li>You may repost expired listings by creating a new posting and making payment</li>
                        <li>We reserve the right to remove listings that violate these Terms at any time</li>
                    </ul>

                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mt-6 mb-3">5.4 Tier Upgrades</h3>
                    <ul class="list-disc pl-6 space-y-2 text-gray-700 dark:text-gray-300">
                        <li>You may upgrade listings from Regular to Featured or Top at any time</li>
                        <li>Upgrades from Featured to Top are also permitted</li>
                        <li>Upgrade pricing is prorated based on remaining days in the listing period</li>
                        <li>Downgrades are not permitted</li>
                        <li>Upgrade payments are non-refundable</li>
                    </ul>

                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mt-6 mb-3">5.5 Application Management</h3>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                        HR users are responsible for reviewing applications in a timely manner and responding appropriately to candidates. You agree to treat all applicants with respect and professionalism.
                    </p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mt-8 mb-4">6. Application Terms (Developers)</h2>
                    
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mt-6 mb-3">6.1 Application Requirements</h3>
                    <ul class="list-disc pl-6 space-y-2 text-gray-700 dark:text-gray-300">
                        <li>You must complete your profile before applying to positions</li>
                        <li>Application information must be accurate and truthful</li>
                        <li>You may only apply to positions for which you are qualified</li>
                        <li>You may withdraw applications at any time</li>
                    </ul>

                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mt-6 mb-3">6.2 Application Process</h3>
                    <ul class="list-disc pl-6 space-y-2 text-gray-700 dark:text-gray-300">
                        <li>Applications are submitted directly to employers through the Platform</li>
                        <li>We are not responsible for employer hiring decisions</li>
                        <li>We do not guarantee job placement or interview opportunities</li>
                        <li>Employers may contact you directly using information provided in your application</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mt-8 mb-4">7. Payment Terms</h2>
                    
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mt-6 mb-3">7.1 Pricing Structure</h3>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed mb-4">Our pricing for job postings is as follows:</p>
                    <ul class="list-disc pl-6 space-y-2 text-gray-700 dark:text-gray-300">
                        <li><strong>Regular Listing:</strong> $49 for 30 days</li>
                        <li><strong>Featured Listing:</strong> $99 for 30 days</li>
                        <li><strong>Top Listing:</strong> $199 for 30 days</li>
                    </ul>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed mt-4">
                        All prices are in USD. Prices are subject to change, but changes will not affect listings already purchased.
                    </p>

                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mt-6 mb-3">7.2 Payment Processing</h3>
                    <ul class="list-disc pl-6 space-y-2 text-gray-700 dark:text-gray-300">
                        <li>Payments are processed through secure third-party payment processors</li>
                        <li>We accept major credit cards and other payment methods offered by our payment providers</li>
                        <li>Payment must be completed before a job posting is published</li>
                        <li>You authorize us to charge your payment method for all fees</li>
                    </ul>

                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mt-6 mb-3">7.3 Refund Policy</h3>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                        All payments are final and non-refundable, except as required by applicable law or in cases where we remove a listing due to our error. If you believe you are entitled to a refund, please contact us at <a href="mailto:hello@laravelremote.com" class="text-primary hover:underline">hello@laravelremote.com</a>.
                    </p>

                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mt-6 mb-3">7.4 Failed Payments</h3>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                        If payment fails, your listing will not be published. You may retry payment or contact support for assistance.
                    </p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mt-8 mb-4">8. Intellectual Property</h2>
                    
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mt-6 mb-3">8.1 Platform Ownership</h3>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                        The Platform, including its design, features, functionality, and content (excluding user-generated content), is owned by FooBar Dev and protected by copyright, trademark, and other intellectual property laws.
                    </p>

                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mt-6 mb-3">8.2 User Content</h3>
                    <ul class="list-disc pl-6 space-y-2 text-gray-700 dark:text-gray-300">
                        <li>You retain ownership of content you post on the Platform</li>
                        <li>By posting content, you grant us a worldwide, non-exclusive, royalty-free license to use, display, and distribute your content on the Platform</li>
                        <li>You represent that you have the right to post all content you submit</li>
                        <li>You agree not to post content that infringes third-party intellectual property rights</li>
                    </ul>

                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mt-6 mb-3">8.3 Trademarks</h3>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                        "LaravelRemote.com" and related marks are trademarks of FooBar Dev. You may not use our trademarks without our prior written consent.
                    </p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mt-8 mb-4">9. Disclaimers and Limitation of Liability</h2>
                    
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mt-6 mb-3">9.1 Platform Availability</h3>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                        We strive to maintain Platform availability but do not guarantee uninterrupted or error-free operation. The Platform is provided "as is" and "as available" without warranties of any kind.
                    </p>

                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mt-6 mb-3">9.2 Job Listings and Applications</h3>
                    <ul class="list-disc pl-6 space-y-2 text-gray-700 dark:text-gray-300">
                        <li>We do not verify the accuracy of job postings or employer information</li>
                        <li>We do not guarantee job placement or interview opportunities</li>
                        <li>We are not responsible for employer hiring decisions or practices</li>
                        <li>We are not responsible for the quality, safety, or legality of job opportunities</li>
                        <li>We are not responsible for interactions between users</li>
                    </ul>

                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mt-6 mb-3">9.3 Limitation of Liability</h3>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                        To the maximum extent permitted by law, FooBar Dev shall not be liable for any indirect, incidental, special, consequential, or punitive damages, or any loss of profits or revenues, whether incurred directly or indirectly, or any loss of data, use, goodwill, or other intangible losses resulting from your use of the Platform.
                    </p>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed mt-4">
                        Our total liability for any claims arising from or related to the Platform shall not exceed the amount you paid us in the 12 months preceding the claim.
                    </p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mt-8 mb-4">10. Indemnification</h2>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                        You agree to indemnify, defend, and hold harmless FooBar Dev, its officers, directors, employees, and agents from any claims, damages, losses, liabilities, and expenses (including legal fees) arising from your use of the Platform, violation of these Terms, or infringement of any third-party rights.
                    </p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mt-8 mb-4">11. Platform Modifications</h2>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                        We reserve the right to modify, suspend, or discontinue any aspect of the Platform at any time, with or without notice. We are not liable to you or any third party for any modification, suspension, or discontinuation.
                    </p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mt-8 mb-4">12. Termination</h2>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed mb-4">Either party may terminate this agreement at any time:</p>
                    <ul class="list-disc pl-6 space-y-2 text-gray-700 dark:text-gray-300">
                        <li><strong>By You:</strong> You may delete your account at any time through your account settings</li>
                        <li><strong>By Us:</strong> We may suspend or terminate your account for violation of these Terms or for any other reason</li>
                    </ul>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed mt-4">
                        Upon termination, your right to use the Platform ceases immediately. Sections of these Terms that by their nature should survive termination will survive.
                    </p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mt-8 mb-4">13. Dispute Resolution</h2>
                    
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mt-6 mb-3">13.1 Governing Law</h3>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                        These Terms shall be governed by and construed in accordance with the laws of [Jurisdiction], without regard to its conflict of law provisions.
                    </p>

                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mt-6 mb-3">13.2 Dispute Resolution Process</h3>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                        In the event of any dispute arising from these Terms or your use of the Platform, you agree to first contact us at <a href="mailto:hello@laravelremote.com" class="text-primary hover:underline">hello@laravelremote.com</a> to attempt to resolve the dispute informally. If we cannot resolve the dispute within 60 days, disputes shall be resolved through binding arbitration in accordance with the rules of [Arbitration Organization], except where prohibited by law.
                    </p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mt-8 mb-4">14. General Provisions</h2>
                    
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mt-6 mb-3">14.1 Entire Agreement</h3>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                        These Terms, together with our Privacy Policy, constitute the entire agreement between you and FooBar Dev regarding the Platform.
                    </p>

                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mt-6 mb-3">14.2 Severability</h3>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                        If any provision of these Terms is found to be unenforceable, the remaining provisions will remain in full effect.
                    </p>

                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mt-6 mb-3">14.3 Waiver</h3>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                        Our failure to enforce any provision of these Terms does not constitute a waiver of that provision.
                    </p>

                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mt-6 mb-3">14.4 Assignment</h3>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                        You may not assign these Terms without our prior written consent. We may assign these Terms at any time.
                    </p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mt-8 mb-4">15. Contact Information</h2>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed mb-4">
                        If you have questions about these Terms, please contact us:
                    </p>
                    <div class="bg-gray-50 dark:bg-gray-800 p-6 rounded-lg">
                        <p class="text-gray-700 dark:text-gray-300"><strong>FooBar Dev</strong></p>
                        <p class="text-gray-700 dark:text-gray-300">Email: <a href="mailto:hello@laravelremote.com" class="text-primary hover:underline">hello@laravelremote.com</a></p>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>
@endsection
