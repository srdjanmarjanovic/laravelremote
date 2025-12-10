<?php

use App\Enums\ListingType;
use App\Http\Controllers\Admin\ApplicationController as AdminApplicationController;
use App\Http\Controllers\Admin\CompanyController as AdminCompanyController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\PositionController as AdminPositionController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\Auth\AccountTypeController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\Developer\DashboardController as DeveloperDashboardController;
use App\Http\Controllers\Developer\ProfileController as DeveloperProfileController;
use App\Http\Controllers\Hr\ApplicationController as HrApplicationController;
use App\Http\Controllers\Hr\CompanySetupController;
use App\Http\Controllers\Hr\DashboardController as HrDashboardController;
use App\Http\Controllers\Hr\PaymentController as HrPaymentController;
use App\Http\Controllers\Hr\PositionController as HrPositionController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PublicPositionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

// Homepage - Browse positions
// Homepage
Route::get('/', function () {
    return view('welcome', [
        'stats' => [
            'total_positions' => \App\Models\Position::where('status', 'published')->count(),
            'total_companies' => \App\Models\Company::count(),
            'total_applications' => \App\Models\Application::count(),
        ],
        'featured_positions' => \App\Models\Position::query()
            ->where('status', 'published')
            ->whereIn('listing_type', [ListingType::Top, ListingType::Featured])
            ->with(['company', 'technologies'])
            ->withCount(['applications', 'views'])
            ->latest('published_at')
            ->limit(6)
            ->get(),
        'popular_technologies' => \App\Models\Technology::query()
            ->withCount('positions')
            ->orderByDesc('positions_count')
            ->limit(12)
            ->get(),
    ]);
})->name('home');

// Browse positions
Route::get('/positions', [PublicPositionController::class, 'index'])->name('positions.index');

// Position details
Route::get('/positions/{position:slug}', [PublicPositionController::class, 'show'])->name('positions.show');

// Social Authentication
Route::prefix('auth')->group(function () {
    Route::get('{provider}/redirect', [SocialAuthController::class, 'redirect'])->name('auth.social.redirect');
    Route::get('{provider}/callback', [SocialAuthController::class, 'callback'])->name('auth.social.callback');
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

// Account type selection (for users without a role)
Route::middleware(['auth'])->group(function () {
    Route::get('select-account-type', [AccountTypeController::class, 'show'])->name('account-type.show');
    Route::post('select-account-type', [AccountTypeController::class, 'store'])->name('account-type.store');
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard - redirect based on role
    Route::get('dashboard', function () {
        $user = auth()->user();

        // If user hasn't selected a role yet, redirect to selection
        if (! $user->role) {
            return redirect()->route('account-type.show');
        }

        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->isHR()) {
            return redirect()->route('hr.dashboard');
        }

        return redirect()->route('developer.dashboard');
    })->name('dashboard');

    // Notifications
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
});

/*
|--------------------------------------------------------------------------
| Developer Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'role:developer'])->prefix('developer')->name('developer.')->group(function () {
    Route::get('dashboard', [DeveloperDashboardController::class, '__invoke'])->name('dashboard');

    // Profile management
    Route::get('profile', [DeveloperProfileController::class, 'edit'])->name('profile.edit');
    Route::post('profile', [DeveloperProfileController::class, 'update'])->name('profile.update');
    Route::get('profile/cv/download', [DeveloperProfileController::class, 'downloadCv'])->name('profile.cv.download');
    Route::delete('profile/cv', [DeveloperProfileController::class, 'deleteCv'])->name('profile.cv.delete');
    Route::delete('profile/photo', [DeveloperProfileController::class, 'deletePhoto'])->name('profile.photo.delete');

    // Applications
    Route::get('applications', [ApplicationController::class, 'index'])->name('applications.index');
    Route::get('applications/{application}', [ApplicationController::class, 'show'])->name('applications.show');
});

// Application submission (requires complete profile)
Route::middleware(['auth', 'verified', 'profile.complete'])->group(function () {
    Route::get('/jobs/{position}/apply', [ApplicationController::class, 'create'])->name('positions.apply');
    Route::post('/jobs/{position}/apply', [ApplicationController::class, 'store'])->name('positions.apply.store');
});

/*
|--------------------------------------------------------------------------
| HR Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'role:hr'])->prefix('hr')->name('hr.')->group(function () {
    // Company setup and management (accessible without complete company profile)
    Route::get('company/setup', [CompanySetupController::class, 'show'])->name('company.setup');
    Route::get('company/edit', [CompanySetupController::class, 'edit'])->name('company.edit');
    Route::post('company/setup', [CompanySetupController::class, 'store'])->name('company.store');
    Route::put('company', [CompanySetupController::class, 'update'])->name('company.update');
    Route::delete('company/logo', [CompanySetupController::class, 'deleteLogo'])->name('company.logo.delete');

    Route::get('dashboard', HrDashboardController::class)->name('dashboard');

    // Position management (requires complete company profile)
    Route::middleware(['company.complete'])->group(function () {
        Route::resource('positions', HrPositionController::class)->except(['destroy']);
        Route::get('positions/{position}/preview', [HrPositionController::class, 'preview'])->name('positions.preview');
        Route::post('positions/{position}/archive', [HrPositionController::class, 'archive'])->name('positions.archive');
        Route::post('positions/{position}/toggle-applications', [HrPositionController::class, 'toggleApplications'])->name('positions.toggle-applications');

        // Payment routes
        Route::get('positions/{position}/payment', [PaymentController::class, 'show'])->name('positions.payment');
        Route::post('positions/{position}/payment/checkout', [PaymentController::class, 'checkout'])->name('positions.payment.checkout');
        Route::post('positions/{position}/payment/upgrade', [PaymentController::class, 'upgrade'])->name('positions.payment.upgrade');
        Route::get('positions/{position}/payment/success', [PaymentController::class, 'success'])->name('positions.payment.success');
    });

    // Payment history
    Route::get('payments', [HrPaymentController::class, 'index'])->name('payments.index');

    // Application management
    Route::get('applications', [HrApplicationController::class, 'index'])->name('applications.index');
    Route::get('applications/{application}', [HrApplicationController::class, 'show'])->name('applications.show');
    Route::patch('applications/{application}', [HrApplicationController::class, 'update'])->name('applications.update');
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('dashboard', AdminDashboardController::class)->name('dashboard');

    // Position management
    Route::get('positions', [AdminPositionController::class, 'index'])->name('positions.index');
    Route::get('positions/create', [AdminPositionController::class, 'create'])->name('positions.create');
    Route::post('positions', [AdminPositionController::class, 'store'])->name('positions.store');
    Route::get('positions/{position}/edit', [AdminPositionController::class, 'edit'])->name('positions.edit');
    Route::put('positions/{position}', [AdminPositionController::class, 'update'])->name('positions.update');
    Route::post('positions/{position}/feature', [AdminPositionController::class, 'feature'])->name('positions.feature');
    Route::post('positions/{position}/tier', [AdminPositionController::class, 'updateTier'])->name('positions.tier');
    Route::post('positions/{position}/extend-expiration', [AdminPositionController::class, 'extendExpiration'])->name('positions.extend-expiration');
    Route::post('positions/{position}/archive', [AdminPositionController::class, 'archive'])->name('positions.archive');
    Route::post('positions/bulk-action', [AdminPositionController::class, 'bulkAction'])->name('positions.bulk-action');

    // User management
    Route::get('users', [AdminUserController::class, 'index'])->name('users.index');

    // Company management
    Route::get('companies', [AdminCompanyController::class, 'index'])->name('companies.index');
    Route::get('companies/create', [AdminCompanyController::class, 'create'])->name('companies.create');
    Route::post('companies', [AdminCompanyController::class, 'store'])->name('companies.store');
    Route::get('companies/{company}', [AdminCompanyController::class, 'show'])->name('companies.show');
    Route::get('companies/{company}/edit', [AdminCompanyController::class, 'edit'])->name('companies.edit');
    Route::put('companies/{company}', [AdminCompanyController::class, 'update'])->name('companies.update');
    Route::delete('companies/{company}', [AdminCompanyController::class, 'destroy'])->name('companies.destroy');

    // Company user management
    Route::get('companies/{company}/users/search', [AdminCompanyController::class, 'searchUsers'])->name('companies.users.search');
    Route::post('companies/{company}/users', [AdminCompanyController::class, 'attachUser'])->name('companies.users.attach');
    Route::delete('companies/{company}/users/{user}', [AdminCompanyController::class, 'detachUser'])->name('companies.users.detach');
    Route::put('companies/{company}/users/{user}/role', [AdminCompanyController::class, 'updateUserRole'])->name('companies.users.role');

    // Application management
    Route::get('applications', [AdminApplicationController::class, 'index'])->name('applications.index');
});

/*
|--------------------------------------------------------------------------
| Settings Routes
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| Payment Webhooks
|--------------------------------------------------------------------------
*/

Route::post('webhooks/payment', [PaymentController::class, 'webhook'])->name('payment.webhook');

/*
|--------------------------------------------------------------------------
| Settings Routes
|--------------------------------------------------------------------------
*/

require __DIR__.'/settings.php';
