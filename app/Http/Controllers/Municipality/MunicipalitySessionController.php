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

        if ($user && $user->role === 'municipality') {
            return redirect()->route('municipality.dashboard');
        }

        return view('municipality.auth.login', [
            'otherPortalUser' => $user,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::check()) {
            $existing = Auth::user();

            if ($existing->role === 'municipality') {
                return redirect()->route('municipality.dashboard');
            }

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        if (! Auth::attempt([
            'email'    => $credentials['email'],
            'password' => $credentials['password'],
        ], $request->boolean('remember'))) {
            return back()->withErrors(['email' => __('auth.failed')])->onlyInput('email');
        }

        $request->session()->regenerate();

        $user = Auth::user();

        if ($user->role !== 'municipality') {
            Auth::logout();
            return back()->withErrors(['email' => 'Access denied.'])->onlyInput('email');
        }

        if (! $user->is_active) {
            Auth::logout();
            return back()->withErrors(['email' => 'Account deactivated. Please contact your administrator.'])->onlyInput('email');
        }

        return redirect()->intended(route('municipality.dashboard'));
    }
}
