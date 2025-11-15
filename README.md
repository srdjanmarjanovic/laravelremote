# Remote Laravel Job Board

A modern, full-featured job board platform specifically designed for remote Laravel positions. Built with Laravel 12, Inertia.js v2, Vue 3, and Tailwind CSS v4.

## ✨ Features

### 🎯 Core Functionality
- **Multi-Role System**: Admin, HR, and Developer roles with specific permissions
- **Social Authentication**: Login with GitHub, Google, or LinkedIn
- **Job Posting Management**: Full CRUD operations with rich descriptions, custom questions, and technology tags
- **Application System**: Cover letters, custom question answers, and status tracking
- **Developer Profiles**: CV uploads, profile photos, portfolio links, and social profiles
- **Company Management**: Multiple HR users per company with owner/admin/member roles
- **View Tracking**: Anonymized IP-based tracking for position views
- **Advanced Search & Filtering**: By technology, seniority, remote type, and salary range

### 🔐 Security & Authorization
- Comprehensive authorization policies for all major actions
- Role-based access control middleware
- Profile completeness checks before job applications
- Secure file storage for CVs (private) and profile photos (public)

### 📊 Analytics & Insights
- Position view statistics
- Application metrics and status tracking
- HR dashboard with company-wide analytics
- Admin dashboard for platform oversight

### 🎨 Modern UI/UX
- Beautiful, responsive design with Tailwind CSS v4
- Dark mode support
- Comprehensive component library (shadcn/ui)
- Smooth animations and transitions

## 🚀 Tech Stack

- **Backend**: Laravel 12, PHP 8.3
- **Frontend**: Inertia.js v2, Vue 3, TypeScript
- **Styling**: Tailwind CSS v4
- **Database**: MySQL/PostgreSQL
- **Authentication**: Laravel Fortify, Laravel Socialite
- **Testing**: Pest v4 (with browser testing support)
- **Code Quality**: Laravel Pint, ESLint, Prettier

## 📋 Prerequisites

- PHP 8.3+
- Composer 2.x
- Node.js 18+ & npm
- MySQL 8.0+ or PostgreSQL 13+
- Git

## 🛠️ Installation

### 1. Clone the Repository

```bash
git clone <repository-url>
cd laravelremote
```

### 2. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### 3. Environment Configuration

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Configure Database

Edit `.env` and set your database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravelremote
DB_USERNAME=root
DB_PASSWORD=
```

### 5. Configure OAuth Providers (Optional)

To enable social login, you'll need to create OAuth applications:

#### GitHub OAuth:
1. Go to GitHub Settings → Developer settings → OAuth Apps
2. Create a new OAuth App
3. Set Authorization callback URL to: `http://localhost:8000/auth/github/callback`
4. Add credentials to `.env`:

```env
GITHUB_CLIENT_ID=your_client_id
GITHUB_CLIENT_SECRET=your_client_secret
```

#### Google OAuth:
1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project and enable Google+ API
3. Create OAuth 2.0 credentials
4. Add authorized redirect URI: `http://localhost:8000/auth/google/callback`
5. Add credentials to `.env`:

```env
GOOGLE_CLIENT_ID=your_client_id
GOOGLE_CLIENT_SECRET=your_client_secret
```

#### LinkedIn OAuth:
1. Go to [LinkedIn Developers](https://www.linkedin.com/developers/)
2. Create a new app
3. Add redirect URL: `http://localhost:8000/auth/linkedin/callback`
4. Add credentials to `.env`:

```env
LINKEDIN_CLIENT_ID=your_client_id
LINKEDIN_CLIENT_SECRET=your_client_secret
```

### 6. Run Migrations & Seeders

```bash
# Run migrations
php artisan migrate

# Seed database with sample data
php artisan db:seed
```

**Seeded Test Accounts:**
- **Admin**: `admin@example.com` / `password`
- **HR Users**: 5 accounts (check `database/seeders/UserSeeder.php`)
- **Developers**: 20 accounts with complete profiles

### 7. Create Storage Link

```bash
php artisan storage:link
```

### 8. Build Frontend Assets

```bash
# Development
npm run dev

# Production
npm run build
```

### 9. Start Development Server

```bash
php artisan serve
```

Visit: `http://localhost:8000`

## 📁 Project Structure

```
laravelremote/
├── app/
│   ├── Console/Commands/
│   │   └── ExpirePositionsCommand.php    # Daily position expiration check
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/                    # Admin controllers
│   │   │   ├── Auth/                     # Authentication controllers
│   │   │   ├── Developer/                # Developer-specific controllers
│   │   │   ├── Hr/                       # HR controllers
│   │   │   ├── ApplicationController.php # Public application submission
│   │   │   └── PublicPositionController.php
│   │   ├── Middleware/
│   │   │   ├── EnsureUserHasRole.php
│   │   │   └── EnsureProfileComplete.php
│   │   ├── Requests/                     # Form request validation
│   │   └── Policies/                     # Authorization policies
│   └── Models/                           # Eloquent models
├── database/
│   ├── factories/                        # Model factories
│   ├── migrations/                       # Database migrations
│   └── seeders/                          # Database seeders
├── resources/
│   ├── js/
│   │   ├── components/                   # Vue components
│   │   ├── Pages/
│   │   │   ├── Admin/                    # Admin pages
│   │   │   ├── Developer/                # Developer pages
│   │   │   ├── Hr/                       # HR pages
│   │   │   └── auth/                     # Authentication pages
│   │   └── layouts/                      # Layout components
│   └── views/                            # Blade views (for public pages)
├── routes/
│   ├── console.php                       # Scheduled commands
│   └── web.php                           # Web routes
└── tests/                                # Pest tests
```

## 🗄️ Database Schema

### Core Tables:
- `users` - User accounts with roles (admin, hr, developer)
- `companies` - Company profiles
- `company_user` - Pivot table for company team members
- `positions` - Job postings
- `technologies` - Technology tags (PHP, Laravel, Vue, etc.)
- `position_technology` - Position-technology relationships
- `applications` - Job applications
- `custom_questions` - Position-specific questions
- `developer_profiles` - Developer profile information
- `position_views` - View tracking (anonymized)
- `social_accounts` - OAuth provider linkages
- `company_invitations` - Company team invitations
- `notifications` - In-app notifications

## 🧪 Testing

### Run Tests

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/PositionTest.php

# Run with coverage
php artisan test --coverage
```

### Browser Testing (Pest v4)

```bash
# Run browser tests
php artisan test tests/Browser
```

## 📅 Scheduled Tasks

The application includes a scheduled command to automatically expire positions:

```bash
# Manually run expiration check
php artisan positions:expire

# In production, ensure cron is configured:
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

## 🎨 Code Quality

### PHP Formatting

```bash
# Format code with Laravel Pint
vendor/bin/pint

# Check without fixing
vendor/bin/pint --test
```

### Frontend Linting

```bash
# Run ESLint
npm run lint

# Run Prettier
npm run format
```

## 📝 Key Endpoints

### Public Routes:
- `GET /` - Homepage
- `GET /positions` - Browse positions
- `GET /positions/{slug}` - Position details
- `GET /auth/{provider}/redirect` - OAuth redirect
- `GET /auth/{provider}/callback` - OAuth callback

### Developer Routes (requires `developer` role):
- `GET /developer/dashboard` - Developer dashboard
- `GET /developer/profile` - Profile management
- `POST /developer/profile` - Update profile
- `GET /developer/applications` - View applications
- `POST /positions/{position}/apply` - Submit application

### HR Routes (requires `hr` role):
- `GET /hr/dashboard` - HR dashboard
- `GET /hr/positions` - Manage positions
- `POST /hr/positions` - Create position
- `PUT /hr/positions/{position}` - Update position
- `DELETE /hr/positions/{position}` - Delete position
- `GET /hr/applications` - View applications
- `PATCH /hr/applications/{application}` - Update application status

### Admin Routes (requires `admin` role):
- `GET /admin/dashboard` - Admin dashboard
- `GET /admin/positions` - All positions
- `POST /admin/positions/{position}/feature` - Feature position
- `POST /admin/positions/bulk-action` - Bulk actions

## 🔧 Configuration

### File Storage

- **CVs**: Stored privately in `storage/app/private/cvs/`
- **Profile Photos**: Stored publicly in `storage/app/public/profile-photos/`

### Position Expiration

Positions automatically expire based on the `expires_at` field. The `ExpirePositionsCommand` runs daily to:
- Mark expired positions
- Send expiration notifications
- Send warnings for positions expiring within 3 days

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License.

## 🙏 Acknowledgments

- Laravel team for the amazing framework
- Inertia.js for seamless SPA development
- Vue.js community
- Tailwind CSS team
- shadcn/ui for the component library

## 📞 Support

For issues, questions, or contributions, please open an issue on GitHub.

---

Built with ❤️ using Laravel & Vue.js

