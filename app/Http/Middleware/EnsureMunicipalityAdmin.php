<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureMunicipalityAdmin
{
    /**
     * Full office / municipality management (per brief: “Municipality User”), not desk staff.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user || ! $user->isMunicipalityAdmin()) {
            return redirect()->route('municipality.dashboard')
                ->with('warning', 'Desk staff can open all portal pages to review the catalog, but only municipality administrators can change office setup, services, or categories.');
        }

        return $next($request);
    }
}
