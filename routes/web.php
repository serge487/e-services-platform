<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Municipality\DashboardController;
use App\Http\Controllers\Municipality\TwoFactorSetupController;

Route::get('/', function () {
    return view('welcome');
});

// Municipality protected routes
Route::prefix('municipality')
    ->middleware(['auth', 'municipality'])
    ->name('municipality.')
    ->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');

        // 2FA setup (enable/show QR)
        Route::get('/2fa/setup', [TwoFactorSetupController::class, 'show'])
            ->name('2fa.setup');
        Route::post('/2fa/enable', [TwoFactorSetupController::class, 'enable'])
            ->name('2fa.enable');
        Route::post('/2fa/confirm', [TwoFactorSetupController::class, 'confirm'])
            ->name('2fa.confirm');
        Route::delete('/2fa/disable', [TwoFactorSetupController::class, 'disable'])
            ->name('2fa.disable');

        // Dashboard sections (shells — filled in later)
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