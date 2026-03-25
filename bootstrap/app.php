<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'municipality' => \App\Http\Middleware\EnsureMunicipalityRole::class,
            'citizen.gate' => \App\Http\Middleware\EnsureCitizenSessionGate::class,
        ]);

        $middleware->redirectUsersTo(function (Request $request) {
            $user = \Illuminate\Support\Facades\Auth::user();

            if ($user?->role === 'municipality') {
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
        App\Providers\FortifyServiceProvider::class,
    ])
    ->create();