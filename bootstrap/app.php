<?php

use App\Http\Middleware\EnsureCitizenSessionGate;
use App\Http\Middleware\EnsureMunicipalityAdmin;
use App\Http\Middleware\EnsureMunicipalityStaff;
use App\Providers\FortifyServiceProvider;
use Filament\Facades\Filament;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'municipality.staff' => EnsureMunicipalityStaff::class,
            'municipality.admin' => EnsureMunicipalityAdmin::class,
            'citizen.gate' => EnsureCitizenSessionGate::class,
        ]);

        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->is('municipality') || $request->is('municipality/*')) {
                return route('municipality.login', absolute: false);
            }

            if ($request->is('admin') || $request->is('admin/*')) {
                return Filament::getLoginUrl() ?? '/admin/login';
            }

            return route('citizen.login', absolute: false);
        });

        $middleware->redirectUsersTo(function (Request $request) {
            $user = Auth::user();

            if ($user?->role === 'municipality') {
                return '/municipality/dashboard';
            }

            if ($user?->role === 'office_staff') {
                return '/municipality/dashboard';
            }

            if ($user?->role === 'admin') {
                return '/admin';
            }

            return '/citizen/dashboard';
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->withProviders([
        FortifyServiceProvider::class,
    ])
    ->create();
