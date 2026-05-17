<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class GmailTestCommand extends Command
{
    protected $signature = 'gmail:test {email : Recipient email address}';

    protected $description = 'Send a test email using gmail-oauth';

    public function handle(): int
    {
        $email = (string) $this->argument('email');

        try {
            Mail::raw(
                'Gmail OAuth test from E-Services Portal.',
                function ($message) use ($email): void {
                    $message->to($email)->subject('Gmail OAuth Test');
                }
            );
        } catch (\Throwable $exception) {
            $this->error('Failed: '.$exception->getMessage());

            if (str_contains($exception->getMessage(), 'expired or revoked')
                || str_contains($exception->getMessage(), 'invalid_grant')) {
                $this->line('Run: php artisan gmail:authorize');
            }

            return self::FAILURE;
        }

        $this->info('Test email sent to '.$email);

        return self::SUCCESS;
    }
}
