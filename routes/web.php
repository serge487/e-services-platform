<?php

use App\Http\Controllers\Auth\CitizenIdentityVerificationController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\Municipality\DashboardController;
use App\Http\Controllers\Municipality\MunicipalitySessionController;
use App\Http\Controllers\Municipality\TwoFactorSetupController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('citizen.login');
});

Route::permanentRedirect('/login', '/citizen/login');
Route::permanentRedirect('/register', '/citizen/register');
Route::permanentRedirect('/forgot-password', '/citizen/forgot-password');

Route::middleware('guest')->group(function () {
    Route::get('municipality/login', [MunicipalitySessionController::class, 'create'])->name('municipality.login');
    Route::post('municipality/login', [MunicipalitySessionController::class, 'store'])->name('municipality.login.store');
});

Route::get('/dashboard', function () {
    return redirect()->route('citizen.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

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

require __DIR__.'/auth.php';
