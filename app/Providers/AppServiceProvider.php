<?php

namespace App\Providers;

use App\Support\CitizenLayoutStats;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ResetPassword::createUrlUsing(function ($user, string $token) {
            return url(route('citizen.password.reset', [
                'token' => $token,
                'email' => $user->getEmailForPasswordReset(),
            ], absolute: false));
        });

        View::composer('layouts.public', function ($view) {
            if (Auth::check() && Auth::user()->role === 'citizen') {
                $view->with('citizenStats', CitizenLayoutStats::forCitizen(Auth::user()));
            }
        });

        View::composer('municipality.layouts.app', function ($view) {
            $user = Auth::user();
            if ($user && $user->canAccessMunicipalityPortal()) {
                $view->with('unread_notifications', $user->unreadNotifications()->count());
            }
        });
    }
}
