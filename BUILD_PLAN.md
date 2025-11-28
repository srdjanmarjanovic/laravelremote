# Remote Laravel Job Board Implementation Plan

## Overview

Build a comprehensive remote job board platform with role-based access (Developer, HR, Admin), social authentication, position posting/application system, simple ATS, SEO-optimized server-rendered pages, and responsive design with light/dark themes.

## 1. Database Architecture & Models

Create comprehensive database schema with migrations, models, factories, and seeders for:

**Core Models:**
- `Company` - name, slug, logo, description, website, social_links (JSON), created_by_user_id, timestamps
- `CompanyUser` - pivot for company-user many-to-many with role (owner/admin/member), invited_by, joined_at
- `Position` - title, slug, short_description, long_description (richtext), company_id, created_by_user_id, seniority, salary_min, salary_max, remote_type (global/timezone/country), location_restriction (nullable), status (draft/published/expired/archived), listing_type (regular/featured/top), is_external, external_apply_url (nullable), allow_platform_applications, expires_at, published_at, paid_at (nullable), timestamps
- `PositionTechnology` - pivot for position-technology many-to-many
- `Technology` - name, slug, icon/logo (nullable)
- `Application` - position_id, user_id, cover_letter (nullable), custom_answers (JSON), status (pending/reviewing/accepted/rejected), reviewed_by_user_id (nullable), applied_at, timestamps
- `CustomQuestion` - position_id, question_text, is_required, order
- `DeveloperProfile` - user_id, summary, cv_path, profile_photo_path, github_url, linkedin_url, portfolio_url, other_links (JSON)
- `CompanyInvitation` - company_id, email, role, invited_by_user_id, token, accepted_at, expires_at
- `PositionView` - position_id, ip_address (hashed for privacy), country_code, city (nullable), user_agent, referrer (nullable), viewed_at
- `Payment` - position_id, user_id, amount, tier (regular/featured/top), type (initial/upgrade), provider (lemon_squeezy/paddle/creem), provider_payment_id, status (pending/completed/failed/refunded), timestamps
- `Notification` - standard Laravel notifications table

**User Extensions:**
- Add `role` enum to users table (developer, hr, admin) - HR users are identified by company memberships
- Update User model with relationships and role checks

**Key Relationships:**
- User hasOne DeveloperProfile
- User belongsToMany Companies (through CompanyUser pivot with role)
- Company belongsToMany Users (HR team members through CompanyUser pivot)
- Company hasMany Positions
- Position belongsTo Company, belongsTo User (creator)
- Position belongsToMany Technologies, hasMany Applications, hasMany CustomQuestions, hasMany PositionViews
- Application belongsTo Position, User (applicant), optionally belongsTo User (reviewer)

Files: `database/migrations/*`, `app/Models/{Company,Position,Technology,Application,CustomQuestion,DeveloperProfile,SocialAccount,PositionView}.php`, factories and seeders

## 2. Authentication & Social Login

**Install Laravel Socialite:**
- `composer require laravel/socialite`
- Configure for GitHub, Google, LinkedIn providers
- Create `config/services.php` entries with placeholder env variables

**OAuth Controllers & Routes:**
- `SocialAuthController` with redirect/callback methods for each provider
- Handle user creation/linking for social logins
- Store provider info in `social_accounts` table (provider, provider_id, user_id)
- Update Fortify registration to include role selection

**Frontend Updates:**
- Add social login buttons to Login.vue and Register.vue
- Beautiful provider icons using lucide-vue-next
- Onboarding flow after social registration to set role and complete profile

Files: `app/Http/Controllers/Auth/SocialAuthController.php`, `database/migrations/*_create_social_accounts_table.php`, `routes/web.php`, update `resources/js/pages/auth/Login.vue` and `Register.vue`

## 3. Authorization & Policies

**Gates & Policies:**
- `PositionPolicy` - create (HR), update/delete (owner HR or admin), feature/archive (admin only), apply (developer with complete profile)
- `CompanyPolicy` - update (HR of company or admin)
- `ApplicationPolicy` - view (position owner HR or applicant or admin), update status (position owner HR or admin)

**Middleware:**
- `EnsureUserHasRole` middleware for role-based route protection
- `EnsureProfileComplete` for developers applying to positions

Files: `app/Policies/{PositionPolicy,CompanyPolicy,ApplicationPolicy}.php`, `app/Http/Middleware/EnsureUserHasRole.php`, register in `bootstrap/app.php`

## 4. HR Features - Position Posting & Management

**Controllers & Requests:**
- `PositionController` - index (my positions), create, store, edit, update, destroy
- `StorePositionRequest` & `UpdatePositionRequest` with comprehensive validation
- Support for adding/removing technologies, custom questions
- **Note:** HR cannot control status or expiration dates - these are managed by the payment system (see Section 16)

**Views (Inertia Pages):**
- `resources/js/pages/hr/positions/Index.vue` - list my posted positions with filters
- `resources/js/pages/hr/positions/Create.vue` - rich form with RichTextEditor component for long_description (no status/expiration fields)
- `resources/js/pages/hr/positions/Edit.vue` - edit existing position (no status/expiration fields)
- `resources/js/pages/hr/positions/Payment.vue` - payment selection after position creation
- Implement multi-step form or single comprehensive form with sections

**Components:**
- `RichTextEditor.vue` - using Tiptap or similar for long_description
- `TechnologySelector.vue` - searchable multi-select
- `CustomQuestionBuilder.vue` - dynamic list to add/remove questions

Files: `app/Http/Controllers/Hr/PositionController.php`, `app/Http/Requests/Hr/{StorePositionRequest,UpdatePositionRequest}.php`, `resources/js/pages/hr/positions/*`, `resources/js/components/positions/*`

## 5. HR Features - Applicant Tracking System

**Controllers:**
- `ApplicationController` - index (all applications for my positions), show (single application), update (change status), bulk actions

**Views:**
- `resources/js/pages/hr/applications/Index.vue` - table/card view with filters (position, status, date)
- `resources/js/pages/hr/applications/Show.vue` - detailed view with applicant profile, answers, CV download, status change

**Analytics:**
- Simple anonymous metrics: total applications, by status, by position, over time
- Include position view analytics: views by country, total views, view trends
- `resources/js/pages/hr/analytics/Dashboard.vue` with Chart.js or similar

Files: `app/Http/Controllers/Hr/ApplicationController.php`, `resources/js/pages/hr/applications/*`, `resources/js/pages/hr/analytics/*`

## 6. Developer Features - Profile Management

**Controllers & Requests:**
- `DeveloperProfileController` - edit, update
- `ProfileUpdateRequest` with validation for URLs, file uploads

**File Uploads:**
- Configure storage disk for profile photos and CVs
- Implement secure file upload/download with validation
- Generate thumbnails for profile photos

**Views:**
- `resources/js/pages/developer/Profile.vue` - comprehensive profile editor
- Components for file uploads, link inputs, rich text summary

Files: `app/Http/Controllers/Developer/DeveloperProfileController.php`, `app/Http/Requests/Developer/ProfileUpdateRequest.php`, `resources/js/pages/developer/Profile.vue`, storage config in `config/filesystems.php`

## 7. Position Browsing & Search (Public - SEO Critical)

**Server-Side Rendered Pages:**
- Create Blade-based position listing and detail pages for SEO
- These pages will NOT use Inertia - they render traditional server-side HTML
- Use Alpine.js for interactive elements (filters, animations)
- Fully responsive with Tailwind CSS
- Track position views with PositionView model (country, IP, user agent, referrer)

**Controllers:**
- `PublicPositionController` - index (listings with filters), show (single position with view tracking)
- Implement robust filtering: remote_type, technologies, seniority, salary range, company
- Server-side pagination
- OpenGraph & Twitter Card meta tags in Blade layouts

**Blade Views:**
- `resources/views/positions/index.blade.php` - beautiful grid/list of positions with sidebar filters
- `resources/views/positions/show.blade.php` - detailed position page with apply button
- `resources/views/layouts/public.blade.php` - SEO-optimized layout with meta tags
- Support for structured data (JSON-LD schema for JobPosting - note: schema.org uses "JobPosting" which is fine)

**Search Implementation:**
- Basic: Eloquent with where clauses and relationships
- Consider Laravel Scout + database driver for simple full-text search

Files: `app/Http/Controllers/PublicPositionController.php`, `resources/views/positions/*`, `resources/views/layouts/public.blade.php`, `routes/web.php`

## 8. Position Application Flow

**Mixed Approach:**
- Application form can be rendered via Inertia for logged-in users OR Blade for SEO
- `ApplicationController` (public facing) - store application
- Validate user is logged in, profile is complete, position accepts applications
- Handle custom question answers (JSON)

**Views:**
- `resources/js/pages/positions/Apply.vue` (Inertia) OR `resources/views/positions/apply.blade.php` (Blade)
- Show custom questions dynamically
- Optional cover letter field

**Email Notifications:**
- Email to HR when new application received
- Email to applicant confirming application
- Create `NewApplicationNotification` and `ApplicationStatusChangedNotification`

Files: `app/Http/Controllers/ApplicationController.php`, `app/Notifications/{NewApplicationNotification,ApplicationStatusChangedNotification}.php`, views for application form

## 9. Admin Panel

**Controllers:**
- `AdminPositionController` - index (all positions), update (feature/unfeature, extend expiration, archive, approve)
- `AdminDashboardController` - overview statistics

**Views (Inertia):**
- `resources/js/pages/admin/positions/Index.vue` - comprehensive position management with bulk actions
- `resources/js/pages/admin/Dashboard.vue` - platform statistics

**Features:**
- Create positions at **no cost** (bypass payment system entirely)
- Change listing tiers freely (Regular/Featured/Top) without triggering payment
- Extend expiration dates on any position
- Feature/unfeature positions
- Archive or delete positions
- View all users, companies, applications

**Position Listing Specifics:**
- Shows only admin's own draft positions (not other companies' drafts)
- Shows all published/expired/archived positions from everyone
- **Expiration date column** visible in the table

Files: `app/Http/Controllers/Admin/{AdminPositionController,AdminDashboardController}.php`, `resources/js/pages/admin/*`

## 10. Notifications System

**Database Notifications:**
- Use Laravel's built-in notification system
- Store in `notifications` table

**Email Notifications:**
- All email templates must follow the consistent design system (modern, gradient headers, responsive)
- Templates in `resources/views/emails/*`

**Notification Types:**
- `NewApplicationNotification` - to HR
- `ApplicationStatusChangedNotification` - to developer
- `PositionExpiringNotification` - to HR (7 days before)
- `PositionExpiredNotification` - to HR
- `WelcomeNotification` - to new users
- `PositionPublishedNotification` - to Admin (when HR completes payment)
- `PositionUpgradedNotification` - to Admin (when HR upgrades tier: Regular→Featured, Regular→Top, Featured→Top)

**In-App Notifications:**
- Notification bell component in header
- Mark as read functionality
- `NotificationController` - index, markAsRead

Files: `app/Notifications/*`, `resources/views/emails/*`, `app/Http/Controllers/NotificationController.php`, `resources/js/components/NotificationBell.vue`

## 11. Position Expiration System

**Scheduled Command:**
- Create `ExpirePositionsCommand` to mark positions as expired when expires_at passes
- Register in `routes/console.php` to run daily
- Send notifications to HR before expiration

**Notification Queue:**
- Configure queue for sending notifications
- Update `.env.example` with QUEUE_CONNECTION

Files: `app/Console/Commands/ExpirePositionsCommand.php`, `routes/console.php`

## 12. UI/UX - Public Pages (Server-Rendered)

**Homepage:**
- `resources/views/welcome.blade.php` - beautiful landing with hero, featured positions, stats, search
- Fully responsive, light/dark theme support using Alpine.js + Tailwind
- SEO optimized with proper meta tags

**Position Listings:**
- Beautiful card-based grid layout
- Sidebar with filters (remote type, location, technologies, seniority, salary)
- Implement with Alpine.js for filter interactions
- Responsive: mobile = stacked, tablet/desktop = grid

**Position Detail:**
- Hero section with company logo, position title, key info
- Tabbed content: description, requirements, company info
- Apply button (prominent CTA)
- Social share buttons
- Schema.org JobPosting structured data (schema.org naming is fine for SEO)

**Shared Components:**
- Public navigation with light/dark toggle
- Footer with links
- Position card component (reusable)

Files: `resources/views/{welcome,positions/index,positions/show,layouts/public,partials/*}.blade.php`, `resources/css/public.css` for additional styles

## 13. UI/UX - Private Pages (Inertia/Vue)

**Layouts:**
- Extend existing `AppLayout.vue` for authenticated pages
- Role-specific sidebars (Developer, HR, Admin navigation)

**Dashboard Pages:**
- Developer: `resources/js/pages/developer/Dashboard.vue` - recommended positions, application status
- HR: `resources/js/pages/hr/Dashboard.vue` - quick stats, recent applications, position view analytics
- Admin: `resources/js/pages/admin/Dashboard.vue` - platform overview

**Responsive Design:**
- All components fully responsive using Tailwind breakpoints
- Mobile-first approach
- Existing shadcn-vue components provide good foundation

Files: Update existing layout files, create new dashboard and feature pages in `resources/js/pages/*`

## 14. Dark Mode Implementation

**Global Theme Management:**
- Extend existing `useAppearance.ts` composable to work with public pages
- Use CSS variables and Tailwind `dark:` classes
- Store preference in localStorage
- System preference detection

**Public Pages:**
- Implement theme toggle in public layout
- Use Alpine.js for theme switching
- Ensure all colors work in both modes

**Private Pages:**
- Already has appearance settings in `resources/js/pages/settings/Appearance.vue`
- Extend to all new pages

Files: Update `resources/js/composables/useAppearance.ts`, add theme toggle to public layout

## 15. SEO & Meta Tags

**Meta Tag Management:**
- Use `spatie/laravel-sitemap` for sitemap generation
- Implement meta tags in Blade layout for public pages
- OpenGraph tags for social sharing
- Twitter Card tags
- Structured data (JSON-LD) for JobPosting schema (schema.org uses "JobPosting" which is standard)

**Sitemap:**
- Generate sitemap including all public position listings
- Schedule daily regeneration

**Robots.txt:**
- Configure appropriate robots.txt

Files: `app/Http/Controllers/SitemapController.php`, `resources/views/layouts/partials/meta.blade.php`, `public/robots.txt`

## 16. Paid Position Publishing & Monetization

**Pricing Structure:**

| Tier | Price | Duration | Display Priority |
|------|-------|----------|------------------|
| Regular | $49 | 30 days | Standard listing |
| Featured | $99 | 30 days | Highlighted, above regular |
| Top | $199 | 30 days | Pinned at top, maximum visibility |

### HR Position Flow

**Creation:**
- HR creates position → saves as `draft` status
- HR has **no control** over status or expiration date (system-managed)
- After creation → presented with payment options (Regular/Featured/Top)
- After successful payment → position becomes `published`, expiration auto-set to +30 days

**Tier Upgrades:**
- Can upgrade at any time: Regular→Featured, Regular→Top, Featured→Top
- **Prorated pricing** based on remaining days
  - Example: 15 days remaining on Regular ($49), upgrade to Featured ($99)
  - Prorated cost = ($99 - $49) × (15/30) = $25
- **No downgrades allowed** - Featured cannot go to Regular, Top cannot be demoted

### Admin Position Flow

**Privileges:**
- Create positions at **no cost** (bypass payment entirely)
- Change tiers freely without triggering payment mechanisms
- Extend expiration dates on any position
- Edit any aspect of any position

**Position Listing View:**
- Shows only admin's own draft positions (not other companies' drafts)
- Shows all published/expired/archived positions from everyone
- **Expiration date column** visible in the table

### Admin Notifications (Payment Events)

Admin is notified when:
- New position is published (payment completed by HR)
- Position upgraded: Regular → Featured
- Position upgraded: Regular → Top
- Position upgraded: Featured → Top

### Database Changes

**Positions table additions:**
- `paid_at` - timestamp when payment was completed
- `payment_id` / `transaction_id` - reference to payment provider

**New `payments` table:**
```
- id
- position_id - foreign key
- user_id - who made the payment
- amount - decimal, amount paid
- tier - what tier was purchased (regular/featured/top)
- type - enum: initial, upgrade
- provider - lemon_squeezy, paddle, creem
- provider_payment_id - external reference
- status - pending, completed, failed, refunded
- created_at, updated_at
```

### Payment Integration

**Preferred providers (TBD):**
- Lemon Squeezy
- Paddle
- Creem.io

**Provider-agnostic design:**
- `PaymentProviderInterface` with methods:
  - `createCheckout(Position $position, string $tier): CheckoutSession`
  - `handleWebhook(Request $request): WebhookResult`
  - `getPaymentStatus(string $paymentId): PaymentStatus`
  - `calculateUpgradePrice(Position $position, string $newTier): float`
- Concrete implementations: `LemonSqueezyProvider`, `PaddleProvider`, `CreemProvider`
- Config-driven provider selection via `config/payments.php`
- Easy to swap providers without code changes

### Implementation Files

**Backend:**
- `app/Services/Payment/PaymentProviderInterface.php`
- `app/Services/Payment/LemonSqueezyProvider.php` (or Paddle/Creem)
- `app/Services/Payment/PositionPaymentService.php` - prorated pricing logic
- `app/Http/Controllers/PaymentController.php` - checkout, webhooks
- `app/Models/Payment.php`
- `database/migrations/*_create_payments_table.php`
- `database/migrations/*_add_payment_fields_to_positions_table.php`

**Frontend:**
- Update `Hr/Positions/Create.vue` - remove status/expiration fields
- Update `Hr/Positions/Edit.vue` - remove status/expiration fields
- Create `Hr/Positions/Payment.vue` - payment selection after creation
- Add "Upgrade Tier" button to `Hr/Positions/Show.vue`
- Update `Admin/Positions/Index.vue` - add expiration column, filter drafts

**Notifications:**
- `app/Notifications/PositionPublishedNotification.php` - to admin
- `app/Notifications/PositionUpgradedNotification.php` - to admin

## 17. Email Templates

**Consistent Design:**
- Create base email layout matching existing welcome/password reset style
- Modern responsive HTML emails with Tailwind-inspired inline styles
- Gradient headers (vary colors by email type)

**Templates Needed:**
- Welcome email (already exists, ensure it follows design)
- Application received (to HR)
- Application status changed (to developer)
- Position expiring soon (to HR)
- Password reset (already exists)

Files: `resources/views/emails/*`, extend existing templates

## 18. Testing

**Feature Tests:**
- Position CRUD operations with authorization
- Position application flow
- Social authentication flow
- Role-based access control
- File uploads (CV, profile photo)
- Position expiration command
- Notification sending
- Position view tracking

**Browser Tests (Pest v4):**
- Public position browsing and filtering
- Position application submission
- HR creating and managing positions
- Admin managing platform

Files: `tests/Feature/*`, `tests/Browser/*`

## 19. Seeding & Demo Data

**Seeders:**
- `DatabaseSeeder` orchestrates all seeders
- `UserSeeder` - create admin, sample HR users, sample developers
- `CompanySeeder` - create sample companies
- `TechnologySeeder` - seed common Laravel ecosystem technologies
- `PositionSeeder` - create diverse position postings (various remote types, seniorities, featured/not)
- `ApplicationSeeder` - create sample applications
- `PositionViewSeeder` - create sample position views with various countries

Files: `database/seeders/*`, update factories

## 20. Routes Organization

**Public Routes:**
- `/` - homepage
- `/positions` - position listings
- `/positions/{position:slug}` - position detail
- `/positions/{position:slug}/apply` - application form

**Developer Routes (auth + role:developer):**
- `/developer/dashboard`
- `/developer/profile`
- `/developer/applications` - my applications

**HR Routes (auth + role:hr):**
- `/hr/dashboard`
- `/hr/positions` - manage my positions
- `/hr/positions/create`
- `/hr/positions/{position}/edit`
- `/hr/applications` - all applications to my positions
- `/hr/analytics`

**Admin Routes (auth + role:admin):**
- `/admin/dashboard`
- `/admin/positions` - manage all positions
- `/admin/users`
- `/admin/companies`

Files: `routes/web.php`, organize into separate route files if needed (`routes/hr.php`, `routes/admin.php`, etc.)

## 21. Final Polish

**Code Quality:**
- Run Laravel Pint for code formatting
- Ensure all PHP files follow PSR-12
- Run ESLint and Prettier on frontend files

**Configuration:**
- Update `.env.example` with all required variables (social OAuth, mail, queue)
- Add helpful comments to config files

**Documentation:**
- Update README with setup instructions, features, screenshots
- Document OAuth setup process
- Document seeding process

Files: `README.md`, `.env.example`, run Pint and linters

## Implementation Order

The todos below follow a logical order for building the platform:

1. Database foundation first
2. Auth extensions second
3. Core features (position posting, browsing, applications)
4. Role-specific dashboards
5. Admin tools
6. **Payment system & monetization** (Section 16)
7. Notifications and polish
8. Testing throughout

## Important Notes

- **Model Naming**: Using "Position" instead of "Job" to avoid conflicts with Laravel's queue Jobs table
- **SEO First**: Position listings and details are server-rendered Blade pages, not Inertia
- **Team-based Companies**: Multiple HR users can manage positions for the same company
- **Analytics**: Track position views with country, referrer, and user agent data
- **Dark Mode**: Full support across all pages (public and private)
- **Responsive**: Mobile-first design using Tailwind CSS v4
- **Payment-Gated Publishing**: Positions require payment to become public; HR cannot control status/expiration directly
- **Tier System**: Regular ($49), Featured ($99), Top ($199) - all for 30 days; upgrades are prorated, no downgrades
- **Payment Providers**: Designed to support Lemon Squeezy, Paddle, or Creem.io with provider-agnostic interface

