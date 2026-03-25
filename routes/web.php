<?php

/*
|--------------------------------------------------------------------------
| Route map (single app, three audiences)
|--------------------------------------------------------------------------
|
| • Citizen users:   URLs under /citizen/... (login, register, ID verify, 2FA, app pages).
| • Municipality:  URLs under /municipality/... (separate login, staff dashboard).
| • Admin:          Filament panel at /admin (role admin; routes live in Filament config).
|
| Important: APP_URL must match the browser origin (host + port), e.g. http://127.0.0.1:8000.
| Relative links in Blade use route(..., absolute: false) so navigation still works if you switch host.
| Session persistence on restart is normal if SESSION_DRIVER=database (or file). Forced redirects to
| /citizen/identity-verification happen
| when a citizen is logged in but identity_verified_at is null—unless
| CITIZEN_SKIP_IDENTITY_VERIFICATION=true (local dev only).
|
*/

use App\Http\Controllers\Auth\CitizenIdentityVerificationController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\Municipality\DashboardController;
use App\Http\Controllers\Municipality\MunicipalitySessionController;
use App\Http\Controllers\Municipality\TwoFactorSetupController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// --------------------------------------------------------------------------
// Home: sends guests to citizen login; logged-in users to the right area.
// --------------------------------------------------------------------------
Route::get('/', function () {
    if (! Auth::check()) {
        return redirect()->route('citizen.login');
    }

    $user = Auth::user();

    if ($user->role === 'municipality') {
        return redirect()->route('municipality.dashboard');
    }

    if ($user->role === 'admin') {
        return redirect('/admin');
    }

    return redirect()->route('citizen.dashboard');
});

// Legacy paths → citizen portal
Route::permanentRedirect('/login', '/citizen/login');
Route::permanentRedirect('/register', '/citizen/register');
Route::permanentRedirect('/forgot-password', '/citizen/forgot-password');

// --------------------------------------------------------------------------
// Municipality — staff sign-in (not behind `guest`: a logged-in citizen mid-2FA
// would otherwise be redirected to /citizen/dashboard and bounced back to 2FA).
// --------------------------------------------------------------------------
Route::get('municipality/login', [MunicipalitySessionController::class, 'create'])->name('municipality.login');
Route::post('municipality/login', [MunicipalitySessionController::class, 'store'])->name('municipality.login.store');

// --------------------------------------------------------------------------
// Shared “Breeze” profile (auth users of any role can hit /profile).
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
// Citizen user — authenticated: ID verification, 2FA, then gated app pages.
// (Guest citizen routes: register/login/password in routes/auth.php)
// --------------------------------------------------------------------------
Route::middleware(['auth'])->prefix('citizen')->name('citizen.')->group(function () {
    Route::get('/identity-verification', [CitizenIdentityVerificationController::class, 'show'])
        ->name('identity-verification.show');
    Route::post('/identity-verification/extract', [CitizenIdentityVerificationController::class, 'extract'])
        ->name('identity-verification.extract');
    Route::post('/identity-verification/confirm', [CitizenIdentityVerificationController::class, 'confirm'])
        ->name('identity-verification.confirm');

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

        Route::get('/services', fn () => view('citizen.services'))->name('services');
        Route::get('/requests', fn () => view('citizen.requests'))->name('requests');
        Route::get('/appointments', fn () => view('citizen.appointments'))->name('appointments');
        Route::get('/notifications', fn () => view('citizen.notifications'))->name('notifications');
        Route::get('/history', fn () => view('citizen.history'))->name('history');
        Route::get('/profile', fn () => view('citizen.profile'))->name('profile');
    });
});

Route::get('/auth/{provider}/redirect', [App\Http\Controllers\Auth\SocialAuthController::class, 'redirect'])->name('social.redirect');
Route::get('/auth/{provider}/callback', [App\Http\Controllers\Auth\SocialAuthController::class, 'callback'])->name('social.callback');

// --------------------------------------------------------------------------
// Municipality user — authenticated + role municipality: staff area.
// --------------------------------------------------------------------------
Route::prefix('municipality')
    ->middleware(['auth', 'municipality'])
    ->name('municipality.')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/2fa/setup', [TwoFactorSetupController::class, 'show'])->name('2fa.setup');
        Route::post('/2fa/enable', [TwoFactorSetupController::class, 'enable'])->name('2fa.enable');
        Route::post('/2fa/confirm', [TwoFactorSetupController::class, 'confirm'])->name('2fa.confirm');
        Route::delete('/2fa/disable', [TwoFactorSetupController::class, 'disable'])->name('2fa.disable');

        Route::get('/office-profile', fn () => view('municipality.office-profile'))->name('office-profile');
        Route::get('/services', fn () => view('municipality.services'))->name('services');
        Route::get('/requests', fn () => view('municipality.requests'))->name('requests');
        Route::get('/appointments', fn () => view('municipality.appointments'))->name('appointments');
        Route::get('/feedback', fn () => view('municipality.feedback'))->name('feedback');
        Route::get('/chat', fn () => view('municipality.chat'))->name('chat');
    });

// --------------------------------------------------------------------------
// Citizen guest routes (register, login, reset password) + logout, email verify
// --------------------------------------------------------------------------
require __DIR__.'/auth.php';
