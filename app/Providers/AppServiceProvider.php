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

        $citizenStatsComposer = function ($view) {
            if (! Auth::check() || Auth::user()->role !== 'citizen') {
                return;
            }
            static $stats = null;
            $stats ??= CitizenLayoutStats::forCitizen(Auth::user());
            $view->with('citizenStats', $stats);
        };

        // Layout + named child views: composing the layout alone can miss stats for @extends children on some stacks.
        View::composer('layouts.public', $citizenStatsComposer);
        View::composer('citizen.chat', $citizenStatsComposer);

        $municipalityUnreadComposer = function ($view) {
            $user = Auth::user();
            if (! $user || ! $user->canAccessMunicipalityPortal()) {
                return;
            }
            static $unreadCount = null;
            $unreadCount ??= $user->unreadNotifications()->count();
            $view->with('unread_notifications', $unreadCount);
        };

        View::composer('municipality.layouts.app', $municipalityUnreadComposer);
        View::composer('municipality.chat', $municipalityUnreadComposer);
    }
}
