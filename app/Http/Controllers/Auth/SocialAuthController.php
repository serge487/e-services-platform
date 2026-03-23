<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    /**
     * Redirect to social provider
     */
    public function redirect(string $provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    /**
     * Handle callback from social provider
     */
    public function callback(string $provider)
    {
        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            return redirect()->route('register')->withErrors(['email' => 'Social login failed. Please try again.']);
        }

        // Check if user already exists
        $user = User::where('provider_name', $provider)
                    ->where('provider_id', $socialUser->getId())
                    ->first();

        if (!$user) {
            // Create new user
            $user = User::create([
                'name'           => $socialUser->getName(),
                'email'          => $socialUser->getEmail(),
                'provider_name'  => $provider,
                'provider_id'    => $socialUser->getId(),
                'provider_token' => $socialUser->token,
                'role'           => 'citizen',
                'is_active'      => true,
                'password'       => bcrypt(str()->random(16)),
            ]);
        }

        // Check if account is active
        if (!$user->is_active) {
            return redirect()->route('login')->withErrors(['email' => 'Account deactivated.']);
        }

        Auth::login($user);

        return redirect()->route('citizen.dashboard');
    }
}