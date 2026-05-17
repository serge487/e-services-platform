<?php

namespace App\Providers;

use App\Mail\GmailOAuthTransport;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider;

class GmailMailServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Mail::extend('gmail-oauth', function () {
            return new GmailOAuthTransport();
        });
    }
}