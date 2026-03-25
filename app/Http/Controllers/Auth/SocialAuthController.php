<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    public function redirect(string $provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function callback(string $provider)
    {
        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            return redirect()->route('citizen.register')->withErrors(['email' => 'Social login failed. Please try again.']);
        }

        $user = User::where('provider_name', $provider)
            ->where('provider_id', $socialUser->getId())
            ->first();

        if (! $user) {
            $user = User::create([
                'name'            => $socialUser->getName(),
                'email'           => $socialUser->getEmail(),
                'provider_name'   => $provider,
                'provider_id'     => $socialUser->getId(),
                'provider_token'  => $socialUser->token,
                'role'            => 'citizen',
                'is_active'       => true,
                'password'        => bcrypt(str()->random(16)),
            ]);
        }

        if (! $user->is_active) {
            return redirect()->route('citizen.login')->withErrors(['email' => 'Account deactivated.']);
        }

        Auth::login($user);

        request()->session()->regenerate();
        request()->session()->forget('citizen_session_unlocked');

        if (! $user->identity_verified_at && ! config('citizen.skip_identity_verification_gate')) {
            return redirect()->route('citizen.identity-verification.show');
        }

        if (! $user->two_factor_confirmed_at) {
            return redirect()->route('citizen.2fa.setup');
        }

        session(['citizen_session_unlocked' => false]);

        return redirect()->route('citizen.2fa.verify');
    }
}
