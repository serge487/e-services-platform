<?php

/*
|--------------------------------------------------------------------------
| Route map (single app, three audiences)
|--------------------------------------------------------------------------
|
| • Citizen users:   URLs under /citizen/... (login, register, ID verify, 2FA, app pages).
| • Municipality:  URLs under /municipality/... (municipality admins + office desk staff).
| • Admin:          Filament panel at /admin (role admin; routes live in Filament config).
|
*/

use App\Http\Controllers\Auth\CitizenIdentityVerificationController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\Municipality\CategoryController;
use App\Http\Controllers\Municipality\DashboardController;
use App\Http\Controllers\Municipality\MunicipalitySessionController;
use App\Http\Controllers\Municipality\OfficeProfileController;
use App\Http\Controllers\Municipality\ServiceController;
use App\Http\Controllers\Municipality\ServiceRequestController;
use App\Http\Controllers\Municipality\TwoFactorSetupController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// --------------------------------------------------------------------------
// Home
// --------------------------------------------------------------------------
Route::get('/', function () {
    if (! Auth::check()) {
        return redirect()->route('citizen.login');
    }

    $user = Auth::user();

    if ($user->role === 'municipality') {
        return redirect()->route('municipality.dashboard');
    }

    if ($user->role === 'office_staff') {
        return redirect()->route('municipality.dashboard');
    }

    if ($user->role === 'admin') {
        return redirect('/admin');
    }

    if (
        $user->role === 'citizen'
        && ! $user->identity_verified_at
        && ! config('citizen.skip_identity_verification_gate')
    ) {
        return redirect()->route('citizen.identity-verification.show');
    }

    return redirect()->route('citizen.dashboard');
});

// Legacy
Route::permanentRedirect('/login', '/citizen/login');
Route::permanentRedirect('/register', '/citizen/register');
Route::permanentRedirect('/forgot-password', '/citizen/forgot-password');

// --------------------------------------------------------------------------
// Municipality login
// --------------------------------------------------------------------------
Route::get('municipality/login', [MunicipalitySessionController::class, 'create'])->name('municipality.login');
Route::post('municipality/login', [MunicipalitySessionController::class, 'store'])->name('municipality.login.store');

// --------------------------------------------------------------------------
// Shared profile
// --------------------------------------------------------------------------
Route::get('/dashboard', function () {
    return redirect()->route('citizen.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// --------------------------------------------------------------------------
// Citizen
// --------------------------------------------------------------------------
Route::middleware(['auth'])->prefix('citizen')->name('citizen.')->group(function () {

    Route::get('/identity-verification', [CitizenIdentityVerificationController::class, 'show'])->name('identity-verification.show');
    Route::post('/identity-verification/extract', [CitizenIdentityVerificationController::class, 'extract'])->name('identity-verification.extract');
    Route::post('/identity-verification/confirm', [CitizenIdentityVerificationController::class, 'confirm'])->name('identity-verification.confirm');

    Route::get('/2fa/setup', [TwoFactorController::class, 'setup'])->name('2fa.setup');
    Route::post('/2fa/enable', [TwoFactorController::class, 'enable'])->name('2fa.enable');
    Route::get('/2fa/verify', [TwoFactorController::class, 'verify'])->name('2fa.verify');
    Route::post('/2fa/validate', [TwoFactorController::class, 'validateCode'])->name('2fa.validate');
    Route::post('/2fa/skip-testing', [TwoFactorController::class, 'skipForTesting'])->name('2fa.skip-testing');

    Route::middleware(['citizen.gate'])->group(function () {

        Route::get('/dashboard', function () {
            if (auth()->user()->role !== 'citizen') {
                abort(403);
            }

            return view('citizen.dashboard');
        })->name('dashboard');

        // Citizen app pages
        Route::get('/services', fn () => view('citizen.services'))->name('services');
        // Backward-compatible alias in case older templates/cache use singular route name.
        Route::get('/service', fn () => redirect()->route('citizen.services'))->name('service');
        Route::get('/requests', fn () => view('citizen.requests'))->name('requests');
        Route::get('/appointments', fn () => view('citizen.appointments'))->name('appointments');
        // ── Categories ──────────────────────────────────────────────
        Route::resource('categories', CategoryController::class)
            ->only(['store', 'update', 'destroy'])
            ->names('categories');

        Route::get('/notifications', fn () => view('citizen.notifications'))->name('notifications');
        Route::get('/history', fn () => view('citizen.history'))->name('history');
        Route::get('/profile', fn () => view('citizen.profile'))->name('profile');
    });
});

// Social
Route::get('/auth/{provider}/redirect', [SocialAuthController::class, 'redirect'])->name('social.redirect');
Route::get('/auth/{provider}/callback', [SocialAuthController::class, 'callback'])->name('social.callback');

// --------------------------------------------------------------------------
// Municipality
// --------------------------------------------------------------------------
Route::prefix('municipality')
    ->middleware(['auth', 'municipality.staff'])
    ->name('municipality.')
    ->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/2fa/setup', [TwoFactorSetupController::class, 'show'])->name('2fa.setup');
        Route::post('/2fa/enable', [TwoFactorSetupController::class, 'enable'])->name('2fa.enable');
        Route::post('/2fa/confirm', [TwoFactorSetupController::class, 'confirm'])->name('2fa.confirm');
        Route::delete('/2fa/disable', [TwoFactorSetupController::class, 'disable'])->name('2fa.disable');

        Route::get('/office-profile', [OfficeProfileController::class, 'index'])->name('office-profile');

        Route::get('/services', [ServiceController::class, 'index'])->name('services');

        Route::get('/feedback', fn () => view('municipality.feedback'))->name('feedback');

        Route::get('/requests', [ServiceRequestController::class, 'index'])->name('requests');
        Route::get('/requests/{serviceRequest}', [ServiceRequestController::class, 'show'])->name('requests.show');
        Route::patch('/requests/{serviceRequest}/status', [ServiceRequestController::class, 'updateStatus'])->name('requests.update-status');
        Route::post('/requests/{serviceRequest}/documents', [ServiceRequestController::class, 'uploadDocument'])->name('requests.upload-document');
        Route::delete('/requests/{serviceRequest}/documents/{document}', [ServiceRequestController::class, 'deleteDocument'])->name('requests.delete-document');

        Route::get('/appointments', fn () => view('municipality.appointments'))->name('appointments');
        Route::get('/chat', fn () => view('municipality.chat'))->name('chat');

        // Municipality administrators: change office profile, catalog (services / categories)
        Route::middleware(['municipality.admin'])->group(function () {
            Route::get('/office-profile/{office}/edit', [OfficeProfileController::class, 'edit'])->name('office-profile.edit');
            Route::put('/office-profile/{office}', [OfficeProfileController::class, 'update'])->name('office-profile.update');

            Route::resource('categories', CategoryController::class)
                ->only(['store', 'update', 'destroy'])
                ->names('categories');

            Route::get('/services/create', [ServiceController::class, 'create'])->name('services.create');
            Route::post('/services', [ServiceController::class, 'store'])->name('services.store');
            Route::get('/services/{service}/edit', [ServiceController::class, 'edit'])->name('services.edit');
            Route::put('/services/{service}', [ServiceController::class, 'update'])->name('services.update');
            Route::delete('/services/{service}', [ServiceController::class, 'destroy'])->name('services.destroy');
        });
    });

require __DIR__.'/auth.php';
