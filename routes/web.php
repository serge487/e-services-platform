<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Municipality\DashboardController;
use App\Http\Controllers\Municipality\TwoFactorSetupController;

Route::get('/', function () {
    return view('welcome');
});

// Override Fortify's logout to redirect to /login
Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/login');
})->name('logout');

// Municipality protected routes
Route::prefix('municipality')
    ->middleware(['auth', 'municipality'])
    ->name('municipality.')
    ->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');

        Route::get('/2fa/setup', [TwoFactorSetupController::class, 'show'])
            ->name('2fa.setup');
        Route::post('/2fa/enable', [TwoFactorSetupController::class, 'enable'])
            ->name('2fa.enable');
        Route::post('/2fa/confirm', [TwoFactorSetupController::class, 'confirm'])
            ->name('2fa.confirm');
        Route::delete('/2fa/disable', [TwoFactorSetupController::class, 'disable'])
            ->name('2fa.disable');

        Route::get('/office-profile', fn() => view('municipality.office-profile'))
            ->name('office-profile');
        Route::get('/services', fn() => view('municipality.services'))
            ->name('services');
        Route::get('/requests', fn() => view('municipality.requests'))
            ->name('requests');
        Route::get('/appointments', fn() => view('municipality.appointments'))
            ->name('appointments');
        Route::get('/feedback', fn() => view('municipality.feedback'))
            ->name('feedback');
        Route::get('/chat', fn() => view('municipality.chat'))
            ->name('chat');
    });