<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    /**
     * Redirect the citizen to the social provider's authentication page.
     */
    public function redirect(string $provider)
    {
        $this->abortIfUnsupportedProvider($provider);

        return Socialite::driver($provider)->redirect();
    }

    /**
     * Handle the callback from the social provider.
     * Finds or creates a citizen user, then routes them through
     * the same gate as a normal login (identity verification → 2FA).
     */
    public function callback(string $provider)
    {
        $this->abortIfUnsupportedProvider($provider);

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            return redirect()->route('citizen.login')
                ->withErrors(['email' => 'Social login failed. Please try again or use email/password.']);
        }

        // Guard: social provider must return an email
        if (! $socialUser->getEmail()) {
            return redirect()->route('citizen.login')
                ->withErrors(['email' => 'No email address returned from ' . ucfirst($provider) . '. Please use email/password login.']);
        }

        // Find existing user by email or create a new citizen account
        $user = User::where('email', $socialUser->getEmail())->first();

        if ($user) {
            // Block non-citizen accounts from using citizen social login
            if ($user->role !== 'citizen') {
                return redirect()->route('citizen.login')
                    ->withErrors(['email' => 'This email is registered as a ' . $user->role . ' account.']);
            }

            // Block deactivated accounts
            if (! $user->is_active) {
                return redirect()->route('citizen.login')
                    ->withErrors(['email' => 'Your account has been deactivated. Contact support.']);
            }

            // Update provider info
            $user->update([
                'provider_name'  => $provider,
                'provider_id'    => $socialUser->getId(),
                'provider_token' => $socialUser->token,
            ]);
        } else {
            // Create a new citizen account from social data
            $user = User::create([
                'name'              => $socialUser->getName() ?? $socialUser->getNickname() ?? 'Citizen',
                'email'             => $socialUser->getEmail(),
                'password'          => bcrypt(Str::random(24)),
                'role'              => 'citizen',
                'is_active'         => true,
                'email_verified_at' => now(),
                'provider_name'     => $provider,
                'provider_id'       => $socialUser->getId(),
                'provider_token'    => $socialUser->token,
            ]);
        }

        Auth::login($user, remember: true);

        // Route through citizen gate — same as normal login
        return $this->redirectAfterSocialLogin($user);
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Redirect the citizen through the same verification flow as normal login.
     * Order: identity verification → 2FA setup/verify → dashboard
     */
    private function redirectAfterSocialLogin(User $user)
    {
        // Step 1: identity verification
        if (
            ! $user->identity_verified_at
            && ! config('citizen.skip_identity_verification_gate')
        ) {
            return redirect()->route('citizen.identity-verification.show');
        }

        // Step 2: 2FA — if not set up yet, send to setup
        if (! $user->two_factor_confirmed_at) {
            return redirect()->route('citizen.2fa.setup');
        }

        // Step 3: 2FA is set up — require verification this session
        session(['citizen_session_unlocked' => false]);

        return redirect()->route('citizen.2fa.verify');
    }

    /**
     * Only allow google and facebook as providers.
     */
    private function abortIfUnsupportedProvider(string $provider): void
    {
        if (! in_array($provider, ['google', 'facebook'])) {
            abort(404, 'Unsupported social provider.');
        }
    }
}