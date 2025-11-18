<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\PositionController as AdminPositionController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\Developer\DashboardController as DeveloperDashboardController;
use App\Http\Controllers\Developer\ProfileController as DeveloperProfileController;
use App\Http\Controllers\Hr\ApplicationController as HrApplicationController;
use App\Http\Controllers\Hr\DashboardController as HrDashboardController;
use App\Http\Controllers\Hr\PositionController as HrPositionController;
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
            ->where('is_featured', true)
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

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard - redirect based on role
    Route::get('dashboard', function () {
        $user = auth()->user();

        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->isHR()) {
            return redirect()->route('hr.dashboard');
        }

        return redirect()->route('developer.dashboard');
    })->name('dashboard');
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
    Route::get('dashboard', HrDashboardController::class)->name('dashboard');

    // Position management
    Route::resource('positions', HrPositionController::class)->except(['destroy']);
    Route::get('positions/{position}/preview', [HrPositionController::class, 'preview'])->name('positions.preview');
    Route::post('positions/{position}/archive', [HrPositionController::class, 'archive'])->name('positions.archive');

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
    Route::post('positions/{position}/feature', [AdminPositionController::class, 'feature'])->name('positions.feature');
    Route::post('positions/{position}/archive', [AdminPositionController::class, 'archive'])->name('positions.archive');
    Route::post('positions/bulk-action', [AdminPositionController::class, 'bulkAction'])->name('positions.bulk-action');
});

/*
|--------------------------------------------------------------------------
| Settings Routes
|--------------------------------------------------------------------------
*/

require __DIR__.'/settings.php';
