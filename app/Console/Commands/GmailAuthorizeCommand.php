<?php

namespace App\Console\Commands;

use Google\Client as GoogleClient;
use Google\Service\Gmail;
use Illuminate\Console\Command;

class GmailAuthorizeCommand extends Command
{
    protected $signature = 'gmail:authorize
                            {--code= : Authorization code or full redirect URL from Google}';

    protected $description = 'Generate a new GMAIL_REFRESH_TOKEN for gmail-oauth mail delivery';

    public function handle(): int
    {
        $clientId = config('mail.gmail.client_id');
        $clientSecret = config('mail.gmail.client_secret');

        if (! is_string($clientId) || $clientId === '' || ! is_string($clientSecret) || $clientSecret === '') {
            $this->error('Set GMAIL_CLIENT_ID and GMAIL_CLIENT_SECRET in .env first.');

            return self::FAILURE;
        }

        $client = new GoogleClient();
        $client->setClientId($clientId);
        $client->setClientSecret($clientSecret);
        $client->setRedirectUri('http://localhost');
        $client->setAccessType('offline');
        $client->setPrompt('consent');
        $client->addScope(Gmail::GMAIL_SEND);

        $code = $this->option('code');

        if (! is_string($code) || trim($code) === '') {
            $this->line('In Google Cloud Console → Credentials → your OAuth client, add redirect URI:');
            $this->line('  http://localhost');
            $this->newLine();
            $this->line('Open this URL, sign in with the Gmail account that sends mail, and approve:');
            $this->line($client->createAuthUrl());
            $this->newLine();
            $code = $this->ask('Paste the authorization code (or full redirect URL)');
        }

        $code = $this->extractAuthorizationCode((string) $code);

        if ($code === '') {
            $this->error('Authorization code is required.');

            return self::FAILURE;
        }

        $token = $client->fetchAccessTokenWithAuthCode($code);

        if (isset($token['error'])) {
            $this->error('Authorization failed: '.($token['error_description'] ?? $token['error']));

            return self::FAILURE;
        }

        if (empty($token['refresh_token'])) {
            $this->warn('No refresh token was returned.');
            $this->line('Revoke this app at https://myaccount.google.com/permissions, then run this command again.');

            return self::FAILURE;
        }

        $this->newLine();
        $this->info('Update your .env with:');
        $this->line('GMAIL_REFRESH_TOKEN='.$token['refresh_token']);
        $this->newLine();
        $this->line('Then run: php artisan config:clear');
        $this->line('Test with: php artisan gmail:test your@email.com');

        return self::SUCCESS;
    }

    private function extractAuthorizationCode(string $input): string
    {
        $input = trim($input);

        if ($input === '' || ! str_contains($input, 'code=')) {
            return $input;
        }

        $query = parse_url($input, PHP_URL_QUERY);

        if (! is_string($query)) {
            return $input;
        }

        parse_str($query, $params);

        return is_string($params['code'] ?? null) ? $params['code'] : $input;
    }
}
