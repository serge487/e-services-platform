<?php

namespace App\Http\Controllers\Municipality;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MunicipalitySessionController extends Controller
{
    public function create(): View|RedirectResponse
    {
        $user = Auth::user();

        if ($user && $user->canAccessMunicipalityPortal()) {
            return redirect()->route(
                $user->isMunicipalityAdmin() ? 'municipality.dashboard' : 'municipality.requests'
            );
        }

        return view('municipality.auth.login', [
            'otherPortalUser' => $user,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::check()) {
            $existing = Auth::user();

            if ($existing->canAccessMunicipalityPortal()) {
                return redirect()->route(
                    $existing->isMunicipalityAdmin() ? 'municipality.dashboard' : 'municipality.requests'
                );
            }

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        if (! Auth::attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
        ], $request->boolean('remember'))) {
            return back()->withErrors(['email' => __('auth.failed')])->onlyInput('email');
        }

        $request->session()->regenerate();

        $user = Auth::user();

        if (! $user->canAccessMunicipalityPortal()) {
            Auth::logout();

            return back()->withErrors(['email' => 'Access denied.'])->onlyInput('email');
        }

        if (! $user->is_active) {
            Auth::logout();

            return back()->withErrors(['email' => 'Account deactivated. Please contact your administrator.'])->onlyInput('email');
        }

        $home = $user->isMunicipalityAdmin()
            ? route('municipality.dashboard')
            : route('municipality.requests');

        return redirect()->intended($home);
    }
}
