<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Municipality\DashboardController;
use App\Http\Controllers\Municipality\TwoFactorSetupController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Citizen Routes
Route::middleware(['auth'])->prefix('citizen')->name('citizen.')->group(function () {
    Route::get('/dashboard', function () {
        if (auth()->user()->role !== 'citizen') {
            abort(403);
        }
        if (!auth()->user()->is_active) {
            Auth::logout();
            return redirect()->route('login')->withErrors(['email' => 'Account deactivated.']);
        }
        return view('citizen.dashboard');
    })->name('dashboard');

    Route::get('/services', fn() => view('citizen.services'))->name('services');
    Route::get('/requests', fn() => view('citizen.requests'))->name('requests');
    Route::get('/appointments', fn() => view('citizen.appointments'))->name('appointments');
    Route::get('/notifications', fn() => view('citizen.notifications'))->name('notifications');
    Route::get('/history', fn() => view('citizen.history'))->name('history');
    Route::get('/profile', fn() => view('citizen.profile'))->name('profile');
});

// Social Auth Routes
Route::get('/auth/{provider}/redirect', [App\Http\Controllers\Auth\SocialAuthController::class, 'redirect'])->name('social.redirect');
Route::get('/auth/{provider}/callback', [App\Http\Controllers\Auth\SocialAuthController::class, 'callback'])->name('social.callback');

// Citizen 2FA Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/2fa/setup', [App\Http\Controllers\Auth\TwoFactorController::class, 'setup'])->name('2fa.setup');
    Route::post('/2fa/enable', [App\Http\Controllers\Auth\TwoFactorController::class, 'enable'])->name('2fa.enable');
    Route::get('/2fa/verify', [App\Http\Controllers\Auth\TwoFactorController::class, 'verify'])->name('2fa.verify');
    Route::post('/2fa/validate', [App\Http\Controllers\Auth\TwoFactorController::class, 'validateCode'])->name('2fa.validate');
});

// Override logout
Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/login');
})->name('logout');

// Municipality Routes
Route::prefix('municipality')
    ->middleware(['auth', 'municipality'])
    ->name('municipality.')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/2fa/setup', [TwoFactorSetupController::class, 'show'])->name('2fa.setup');
        Route::post('/2fa/enable', [TwoFactorSetupController::class, 'enable'])->name('2fa.enable');
        Route::post('/2fa/confirm', [TwoFactorSetupController::class, 'confirm'])->name('2fa.confirm');
        Route::delete('/2fa/disable', [TwoFactorSetupController::class, 'disable'])->name('2fa.disable');

        Route::get('/office-profile', fn() => view('municipality.office-profile'))->name('office-profile');
        Route::get('/services', fn() => view('municipality.services'))->name('services');
        Route::get('/requests', fn() => view('municipality.requests'))->name('requests');
        Route::get('/appointments', fn() => view('municipality.appointments'))->name('appointments');
        Route::get('/feedback', fn() => view('municipality.feedback'))->name('feedback');
        Route::get('/chat', fn() => view('municipality.chat'))->name('chat');
    });

require __DIR__.'/auth.php';