<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureMunicipalityStaff
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return redirect()->route('municipality.login');
        }

        $user = Auth::user();

        if (! $user->canAccessMunicipalityPortal()) {
            Auth::logout();

            return redirect()->route('municipality.login')
                ->withErrors(['email' => 'Access denied.']);
        }

        if (! $user->is_active) {
            Auth::logout();

            return redirect()->route('municipality.login')
                ->withErrors(['email' => 'Account deactivated. Please contact your administrator.']);
        }

        if ($user->isOfficeStaff()) {
            if (! $user->municipality_id || ! $user->office_id) {
                Auth::logout();

                return redirect()->route('municipality.login')
                    ->withErrors(['email' => 'Account misconfigured. Contact your administrator.']);
            }
        }

        return $next($request);
    }
}
