<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureCitizenSessionGate
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user || $user->role !== 'citizen') {
            return $next($request);
        }

        if (! $user->is_active) {
            Auth::logout();

            return redirect()
                ->route('citizen.login')
                ->withErrors(['email' => 'Account deactivated.']);
        }

        if (! $user->identity_verified_at && ! config('citizen.skip_identity_verification_gate')) {
            return redirect()->route('citizen.identity-verification.show');
        }

        if (! $request->session()->get('citizen_session_unlocked')) {
            if ($user->two_factor_confirmed_at) {
                return redirect()->route('citizen.2fa.verify');
            }

            return redirect()->route('citizen.2fa.setup');
        }

        return $next($request);
    }
}
