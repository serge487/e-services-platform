<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureMunicipalityRole
{
    public function handle(Request $request, Closure $next)
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        if ($user->role !== 'municipality') {
            Auth::logout();
            return redirect()->route('login')
                ->withErrors(['email' => 'Access denied.']);
        }

        if (! $user->is_active) {
            Auth::logout();
            return redirect()->route('login')
                ->withErrors(['email' => 'Account deactivated. Please contact your administrator.']);
        }

        return $next($request);
    }
}