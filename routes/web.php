<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
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
        // Check if user is citizen
        if (auth()->user()->role !== 'citizen') {
            abort(403);
        }
        // Check if user is active
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

// 2FA Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/2fa/setup', [App\Http\Controllers\Auth\TwoFactorController::class, 'setup'])->name('2fa.setup');
    Route::post('/2fa/enable', [App\Http\Controllers\Auth\TwoFactorController::class, 'enable'])->name('2fa.enable');
    Route::get('/2fa/verify', [App\Http\Controllers\Auth\TwoFactorController::class, 'verify'])->name('2fa.verify');
    Route::post('/2fa/validate', [App\Http\Controllers\Auth\TwoFactorController::class, 'validateCode'])->name('2fa.validate');
});

require __DIR__.'/auth.php';
