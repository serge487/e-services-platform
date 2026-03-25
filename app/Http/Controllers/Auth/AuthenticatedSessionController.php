<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();

        if ($user->role !== 'citizen') {
            Auth::logout();
            return redirect()->route('citizen.login')->withErrors(['email' => 'Unauthorized access.']);
        }

        if (! $user->is_active) {
            Auth::logout();
            return redirect()->route('citizen.login')->withErrors(['email' => 'Account deactivated.']);
        }

        $request->session()->forget('citizen_session_unlocked');

        if (! $user->identity_verified_at) {
            return redirect()->route('citizen.identity-verification.show');
        }

        if (! $user->two_factor_confirmed_at) {
            return redirect()->route('citizen.2fa.setup');
        }

        session(['citizen_session_unlocked' => false]);

        return redirect()->route('citizen.2fa.verify');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $wasMunicipality = Auth::user()?->role === 'municipality';

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return $wasMunicipality
            ? redirect()->route('municipality.login')
            : redirect()->route('citizen.login');
    }
}
