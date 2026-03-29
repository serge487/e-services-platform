<?php

/*
|--------------------------------------------------------------------------
| Route map (single app, three audiences)
|--------------------------------------------------------------------------
|
| • Public portal:   / and /offices/{office} — no auth required.
| • Citizen users:   URLs under /citizen/... (login, register, ID verify, 2FA, app pages).
| • Municipality:    URLs under /municipality/... (municipality admins + office desk staff).
| • Admin:           Filament panel at /admin (role admin; routes live in Filament config).
|
| Important: APP_URL must match the browser origin (host + port), e.g. http://127.0.0.1:8000.
| Relative links in Blade use route(..., absolute: false) so navigation still works if you
| switch host. Session persistence on restart is normal if SESSION_DRIVER=database (or file).
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
use App\Http\Controllers\PublicPortalController;
use Illuminate\Support\Facades\Route;

// --------------------------------------------------------------------------
// Public portal — no authentication required
// --------------------------------------------------------------------------
Route::get('/', [PublicPortalController::class, 'index'])->name('portal');
Route::get('/offices/{office}', [PublicPortalController::class, 'show'])->name('portal.office');

// --------------------------------------------------------------------------
// Legacy redirects — keep old paths working
// --------------------------------------------------------------------------
Route::permanentRedirect('/login', '/citizen/login');
Route::permanentRedirect('/register', '/citizen/register');
Route::permanentRedirect('/forgot-password', '/citizen/forgot-password');

// --------------------------------------------------------------------------
// Municipality — staff sign-in (separate from citizen login)
// --------------------------------------------------------------------------
Route::get('municipality/login', [MunicipalitySessionController::class, 'create'])
    ->name('municipality.login');
Route::post('municipality/login', [MunicipalitySessionController::class, 'store'])
    ->name('municipality.login.store');

// --------------------------------------------------------------------------
// Shared Breeze profile — any authenticated role can access /profile
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
// Citizen — authenticated routes
// --------------------------------------------------------------------------
Route::middleware(['auth'])->prefix('citizen')->name('citizen.')->group(function () {

    // ── Identity verification ─────────────────────────────────────────────
    Route::get('/identity-verification', [CitizenIdentityVerificationController::class, 'show'])
        ->name('identity-verification.show');
    Route::post('/identity-verification/extract', [CitizenIdentityVerificationController::class, 'extract'])
        ->name('identity-verification.extract');
    Route::post('/identity-verification/confirm', [CitizenIdentityVerificationController::class, 'confirm'])
        ->name('identity-verification.confirm');

    // ── 2FA ──────────────────────────────────────────────────────────────
    Route::get('/2fa/setup', [TwoFactorController::class, 'setup'])->name('2fa.setup');
    Route::post('/2fa/enable', [TwoFactorController::class, 'enable'])->name('2fa.enable');
    Route::get('/2fa/verify', [TwoFactorController::class, 'verify'])->name('2fa.verify');
    Route::post('/2fa/validate', [TwoFactorController::class, 'validateCode'])->name('2fa.validate');
    Route::post('/2fa/skip-testing', [TwoFactorController::class, 'skipForTesting'])
        ->name('2fa.skip-testing');

    // ── Gated citizen app pages (identity verified + 2FA unlocked) ────────
    Route::middleware(['citizen.gate'])->group(function () {
Route::get('/dashboard', function () {
    if (auth()->user()->role !== 'citizen') {
        abort(403);
    }
    // Citizens land on the public portal with their stats unlocked
    return redirect()->route('portal');
})->name('dashboard');

        Route::get('/services', fn () => view('citizen.services'))->name('services');

        // Backward-compatible alias — older links using singular route name still work
        Route::get('/service', fn () => redirect()->route('citizen.services'))->name('service');
        Route::get('/chat', fn () => view('citizen.chat'))->name('chat');
        Route::get('/requests', fn () => view('citizen.requests'))->name('requests');
        Route::get('/appointments', fn () => view('citizen.appointments'))->name('appointments');
        Route::get('/notifications', fn () => view('citizen.notifications'))->name('notifications');
        Route::get('/history', fn () => view('citizen.history'))->name('history');
        Route::get('/profile', fn () => view('citizen.profile'))->name('profile');
    });
});

// --------------------------------------------------------------------------
// Social OAuth
// --------------------------------------------------------------------------
Route::get('/auth/{provider}/redirect', [SocialAuthController::class, 'redirect'])
    ->name('social.redirect');
Route::get('/auth/{provider}/callback', [SocialAuthController::class, 'callback'])
    ->name('social.callback');

// --------------------------------------------------------------------------
// Municipality — authenticated + role check (municipality or office_staff)
// --------------------------------------------------------------------------
Route::prefix('municipality')
    ->middleware(['auth', 'municipality.staff'])
    ->name('municipality.')
    ->group(function () {

        // ── Dashboard ─────────────────────────────────────────────────────
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // ── 2FA ───────────────────────────────────────────────────────────
        Route::get('/2fa/setup', [TwoFactorSetupController::class, 'show'])->name('2fa.setup');
        Route::post('/2fa/enable', [TwoFactorSetupController::class, 'enable'])->name('2fa.enable');
        Route::post('/2fa/confirm', [TwoFactorSetupController::class, 'confirm'])->name('2fa.confirm');
        Route::delete('/2fa/disable', [TwoFactorSetupController::class, 'disable'])->name('2fa.disable');

        // ── Office profile (read-only for staff) ──────────────────────────
        Route::get('/office-profile', [OfficeProfileController::class, 'index'])
            ->name('office-profile');

        // ── Services (read-only for staff) ────────────────────────────────
        Route::get('/services', [ServiceController::class, 'index'])->name('services');

        // ── Service requests ──────────────────────────────────────────────
        Route::get('/requests', [ServiceRequestController::class, 'index'])->name('requests');
        Route::get('/requests/{serviceRequest}', [ServiceRequestController::class, 'show'])
            ->name('requests.show');
        Route::patch('/requests/{serviceRequest}/status', [ServiceRequestController::class, 'updateStatus'])
            ->name('requests.update-status');
        Route::post('/requests/{serviceRequest}/documents', [ServiceRequestController::class, 'uploadDocument'])
            ->name('requests.upload-document');
        Route::delete('/requests/{serviceRequest}/documents/{document}', [ServiceRequestController::class, 'deleteDocument'])
            ->name('requests.delete-document');

        // ── Appointments, feedback, chat (shells) ─────────────────────────
        Route::get('/appointments', fn () => view('municipality.appointments'))->name('appointments');
        Route::get('/feedback', fn () => view('municipality.feedback'))->name('feedback');
        Route::get('/chat', fn () => view('municipality.chat'))->name('chat');

        // ── Municipality admin only: edit office profile + manage catalog ──
        Route::middleware(['municipality.admin'])->group(function () {

            // Office profile edit
            Route::get('/office-profile/{office}/edit', [OfficeProfileController::class, 'edit'])
                ->name('office-profile.edit');
            Route::put('/office-profile/{office}', [OfficeProfileController::class, 'update'])
                ->name('office-profile.update');

            // Categories
            Route::resource('categories', CategoryController::class)
                ->only(['store', 'update', 'destroy'])
                ->names('categories');

            // Services
            Route::get('/services/create', [ServiceController::class, 'create'])
                ->name('services.create');
            Route::post('/services', [ServiceController::class, 'store'])
                ->name('services.store');
            Route::get('/services/{service}/edit', [ServiceController::class, 'edit'])
                ->name('services.edit');
            Route::put('/services/{service}', [ServiceController::class, 'update'])
                ->name('services.update');
            Route::delete('/services/{service}', [ServiceController::class, 'destroy'])
                ->name('services.destroy');
        });
    });

// --------------------------------------------------------------------------
// Citizen guest routes (register, login, password reset) + logout
// Defined in routes/auth.php (Breeze scaffold under /citizen prefix)
// --------------------------------------------------------------------------
require __DIR__.'/auth.php';