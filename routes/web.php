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
| • Fortify:        MFA/recovery routes under /fortify/... (prefix in config/fortify.php); primary
|                    municipality sign-in is /municipality/login, not Fortify’s /fortify/login.
|
| Important: APP_URL must match the browser origin (host + port), e.g. http://127.0.0.1:8000.
| Relative links in Blade use route(..., absolute: false) so navigation still works if you
| switch host. Session persistence on restart is normal if SESSION_DRIVER=database (or file).
|
*/

use App\Http\Controllers\Auth\CitizenIdentityVerificationController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\CitizenChatController;
use App\Http\Controllers\Citizen\AppointmentController as CitizenAppointmentController;
use App\Http\Controllers\Citizen\ServiceController as CitizenServiceController;
use App\Http\Controllers\Citizen\ServiceRequestController as CitizenServiceRequestController;
use App\Http\Controllers\CitizenNotificationController;
use App\Http\Controllers\Municipality\AppointmentController as MunicipalityAppointmentController;
use App\Http\Controllers\Municipality\CategoryController;
use App\Http\Controllers\Municipality\ChatController;
use App\Http\Controllers\Municipality\DashboardController;
use App\Http\Controllers\Municipality\MunicipalitySessionController;
use App\Http\Controllers\Municipality\NotificationController as MunicipalityNotificationController;
use App\Http\Controllers\Municipality\EventAlertController;
use App\Http\Controllers\Municipality\OfficeProfileController;
use App\Http\Controllers\Municipality\ServiceController;
use App\Http\Controllers\Municipality\ServiceRequestController;
use App\Http\Controllers\Municipality\TwoFactorSetupController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicPortalController;
use App\Http\Controllers\QrCodeScanController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Citizen\PaymentController;

// --------------------------------------------------------------------------
// Public portal — no authentication required
// --------------------------------------------------------------------------
Route::get('/', [PublicPortalController::class, 'index'])->name('portal');
Route::get('/offices/{office}', [PublicPortalController::class, 'show'])->name('portal.office');
Route::get('/services/{service}/request', [PublicPortalController::class, 'requestService'])->name('portal.services.request');

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
    $user = auth()->user();

    if (in_array($user->role, ['municipality', 'office_staff'], true)) {
        return redirect()->route('municipality.dashboard');
    }

    if ($user->role === 'admin') {
        return redirect('/admin');
    }

    if (! $user->hasVerifiedEmail()) {
        return redirect()->route('verification.notice');
    }

    return redirect()->route('citizen.dashboard');
})->middleware(['auth'])->name('dashboard');

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

        // ── Services: Browse & Request ────────────────────────────────────
        Route::get('/services', [CitizenServiceController::class, 'index'])->name('services');
        Route::get('/services/{service}', [CitizenServiceController::class, 'show'])->name('services.show');

        // Backward-compatible alias — older links using singular route name still work
        Route::get('/service', fn () => redirect()->route('citizen.services'))->name('service');
        Route::get('/chat', [CitizenChatController::class, 'index'])->name('chat');
        Route::post('/chat/start', [CitizenChatController::class, 'startOrGetChat'])->name('chat.start');
        Route::get('/chat/{chatId}', [CitizenChatController::class, 'show'])->name('chat.show');
        Route::get('/chat/{chatId}/poll', [CitizenChatController::class, 'poll'])
            ->middleware('prevent.cache')
            ->name('chat.poll');
        Route::post('/chat/{chatId}/send', [CitizenChatController::class, 'sendMessage'])->name('chat.send');

        // ── Service Requests: Submit, Track & Download ────────────────────
        Route::get('/requests', [CitizenServiceRequestController::class, 'index'])->name('requests');
        Route::post('/service-requests', [CitizenServiceRequestController::class, 'store'])->name('service-requests.store');
        Route::get('/service-requests/{serviceRequest}', [CitizenServiceRequestController::class, 'show'])->name('service-requests.show');
        Route::get('/service-requests/{serviceRequest}/poll', [CitizenServiceRequestController::class, 'pollStatus'])->name('service-requests.poll');
        Route::get('/service-requests/{serviceRequest}/documents/{document}/download', [CitizenServiceRequestController::class, 'downloadDocument'])->name('service-requests.download');

         // Payment routes
Route::get('/service-requests/{serviceRequest}/payment', [PaymentController::class, 'show'])
    ->name('service-requests.payment');
Route::post('/service-requests/{serviceRequest}/payment', [PaymentController::class, 'store'])
    ->name('service-requests.payment.store');
    
        Route::get('/appointments', [CitizenAppointmentController::class, 'index'])->name('appointments');
        Route::get('/appointments/live', [CitizenAppointmentController::class, 'live'])->name('appointments.live');
        Route::post('/appointments', [CitizenAppointmentController::class, 'store'])->name('appointments.store');
        Route::patch('/appointments/{appointment}/cancel', [CitizenAppointmentController::class, 'cancel'])
            ->name('appointments.cancel');
        Route::get('/notifications', [CitizenNotificationController::class, 'index'])->name('notifications');
        Route::get('/notifications/{id}/chat', [CitizenNotificationController::class, 'openChat'])->name('notifications.chat');
        Route::delete('/notifications/{id}', [CitizenNotificationController::class, 'destroy'])->name('notifications.destroy');
        Route::delete('/notifications', [CitizenNotificationController::class, 'destroyAll'])->name('notifications.destroyAll');
        Route::get('/history', fn () => view('citizen.history'))->name('history');
        Route::get('/profile', fn () => view('citizen.profile'))->name('profile');
        Route::get('/profile', fn () => view('citizen.profile'))->name('profile');
Route::patch('/profile/update', [\App\Http\Controllers\Citizen\CitizenProfileController::class, 'update'])->name('profile.update');
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
        Route::get('/dashboard/live', [DashboardController::class, 'live'])->name('dashboard.live');

        // ── Event Alerts ──────────────────────────────────────────────────────────────
Route::get('/event-alerts', [\App\Http\Controllers\Municipality\EventAlertController::class, 'index'])
    ->name('event-alerts.index');
Route::get('/event-alerts/create', [\App\Http\Controllers\Municipality\EventAlertController::class, 'create'])
    ->name('event-alerts.create');
Route::post('/event-alerts', [\App\Http\Controllers\Municipality\EventAlertController::class, 'store'])
    ->name('event-alerts.store');
Route::get('/event-alerts/{eventAlert}', [\App\Http\Controllers\Municipality\EventAlertController::class, 'show'])
    ->name('event-alerts.show');

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
        Route::post('/requests/{serviceRequest}/accept', [ServiceRequestController::class, 'acceptRequest'])
            ->name('requests.accept');
        Route::post('/requests/{serviceRequest}/documents', [ServiceRequestController::class, 'uploadDocument'])
            ->name('requests.upload-document');
        Route::get('/requests/{serviceRequest}/documents/{document}/download', [ServiceRequestController::class, 'downloadDocument'])
            ->name('requests.download-document');
        Route::delete('/requests/{serviceRequest}/documents/{document}', [ServiceRequestController::class, 'deleteDocument'])
            ->name('requests.delete-document');
        Route::post('/requests/{serviceRequest}/confirm-payment', 
    [ServiceRequestController::class, 'confirmPayment'])
    ->name('requests.confirm-payment');

        // ── Appointments, feedback, chat (shells) ─────────────────────────
        Route::get('/appointments', [MunicipalityAppointmentController::class, 'index'])->name('appointments');
        Route::get('/appointments/live', [MunicipalityAppointmentController::class, 'live'])->name('appointments.live');
        Route::post('/appointments/slots', [MunicipalityAppointmentController::class, 'storeSlot'])
            ->name('appointments.slots.store');
        Route::delete('/appointments/slots/{officerTimeSlot}', [MunicipalityAppointmentController::class, 'destroySlot'])
            ->name('appointments.slots.destroy');
        Route::patch('/appointments/{appointment}/status', [MunicipalityAppointmentController::class, 'updateStatus'])
            ->name('appointments.update-status');
        Route::delete('/appointments/{appointment}', [MunicipalityAppointmentController::class, 'destroy'])
            ->name('appointments.destroy');
        Route::post('/appointments/{appointment}/remind', [MunicipalityAppointmentController::class, 'sendReminder'])
            ->name('appointments.remind');
        Route::get('/feedback', fn () => view('municipality.feedback'))->name('feedback');
        Route::get('/notifications', [MunicipalityNotificationController::class, 'index'])->name('notifications');
        Route::get('/notifications/{id}/chat', [MunicipalityNotificationController::class, 'openChat'])->name('notifications.chat');
        Route::delete('/notifications/{id}', [MunicipalityNotificationController::class, 'destroy'])->name('notifications.destroy');
        Route::delete('/notifications', [MunicipalityNotificationController::class, 'destroyAll'])->name('notifications.destroyAll');
        Route::get('/chat', [ChatController::class, 'index'])->name('chat');
        Route::get('/chat/{chatId}', [ChatController::class, 'show'])->name('chat.show');
        Route::get('/chat/{chatId}/poll', [ChatController::class, 'poll'])
            ->middleware('prevent.cache')
            ->name('chat.poll');
            Route::post('/chat/{chatId}/mute', [ChatController::class, 'mute'])->name('chat.mute');
            Route::post('/chat/{chatId}/unmute', [ChatController::class, 'unmute'])->name('chat.unmute');
        Route::post('/chat/{chatId}/send', [ChatController::class, 'sendMessage'])->name('chat.send');

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
// QR Code scanning — public endpoint for tracking via QR code
// --------------------------------------------------------------------------
Route::get('/qr/scan/{token}', [QrCodeScanController::class, 'scan'])
    ->name('qr.scan');
Route::get('/qr/data/{token}', [QrCodeScanController::class, 'data'])
    ->name('qr.data');


   
// --------------------------------------------------------------------------
// Citizen guest routes (register, login, password reset) + logout
// Defined in routes/auth.php (Breeze scaffold under /citizen prefix)
// --------------------------------------------------------------------------
require __DIR__.'/auth.php';
