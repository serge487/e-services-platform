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

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Account deactivated.'], 403);
            }

            return redirect()
                ->route('citizen.login')
                ->withErrors(['email' => 'Account deactivated.']);
        }

        if (! $user->identity_verified_at && ! config('citizen.skip_identity_verification_gate')) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Identity not verified.'], 403);
            }

            $request->session()->put('url.intended', $request->fullUrl());

            return redirect()->route('citizen.identity-verification.show');
        }

        if (! $request->session()->get('citizen_session_unlocked')) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Session not unlocked.'], 401);
            }

            $request->session()->put('url.intended', $request->fullUrl());

            if ($user->two_factor_confirmed_at) {
                return redirect()->route('citizen.2fa.verify');
            }

            return redirect()->route('citizen.2fa.setup');
        }

        return $next($request);
    }
}
