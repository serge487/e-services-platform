<?php

namespace App\Providers;

use App\Actions\Fortify\AuthenticateUser;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Features;

class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Custom authentication logic (role + is_active check)
        Fortify::authenticateUsing(function (Request $request) {
            $user = User::where('email', $request->email)->first();

            if (! $user) {
                return null;
            }

            if (! \Hash::check($request->password, $user->password)) {
                return null;
            }

            if ($user->role !== 'municipality') {
                return null; // silently fail — middleware will show proper error
            }

            if (! $user->is_active) {
                // Store flag so the controller can show the right message
                session(['login_error' => 'deactivated']);
                return null;
            }

            return $user;
        });
        Fortify::redirects('login', '/municipality/dashboard');
        Fortify::redirects('logout', '/login');
        // Custom Blade views
        Fortify::loginView(fn () => view('municipality.auth.login'));
        Fortify::twoFactorChallengeView(fn () => view('municipality.auth.two-factor-challenge'));

        // Rate limiting
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->email . $request->ip());
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });
    }

    
}