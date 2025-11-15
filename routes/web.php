<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\PositionController as AdminPositionController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\Developer\ProfileController as DeveloperProfileController;
use App\Http\Controllers\Hr\ApplicationController as HrApplicationController;
use App\Http\Controllers\Hr\PositionController as HrPositionController;
use App\Http\Controllers\PublicPositionController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

// Homepage - Browse positions
Route::get('/', [PublicPositionController::class, 'index'])->name('home');

// Position details
Route::get('/jobs/{slug}', [PublicPositionController::class, 'show'])->name('positions.show');

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
    Route::get('dashboard', function () {
        return Inertia::render('Developer/Dashboard');
    })->name('dashboard');

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
    Route::get('dashboard', function () {
        return Inertia::render('Hr/Dashboard');
    })->name('dashboard');

    // Position management
    Route::resource('positions', HrPositionController::class);

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
