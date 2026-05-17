<?php

namespace App\Mail;

use Google\Client as GoogleClient;
use Google\Service\Gmail;
use RuntimeException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;

class GmailOAuthTransport extends AbstractTransport
{
    private GoogleClient $googleClient;

    public function __construct()
    {
        parent::__construct();

        $this->googleClient = new GoogleClient();
        $this->googleClient->setClientId(config('mail.gmail.client_id'));
        $this->googleClient->setClientSecret(config('mail.gmail.client_secret'));
        $this->googleClient->setAccessType('offline');
        $this->googleClient->addScope(Gmail::GMAIL_SEND);
    }

    protected function doSend(SentMessage $message): void
    {
        $this->ensureAccessToken();

        $rawEmail = $message->toString();
        $encodedMessage = rtrim(strtr(base64_encode($rawEmail), '+/', '-_'), '=');

        $gmailService = new Gmail($this->googleClient);
        $gmailMessage = new Gmail\Message();
        $gmailMessage->setRaw($encodedMessage);

        $gmailService->users_messages->send('me', $gmailMessage);
    }

    private function ensureAccessToken(): void
    {
        $accessToken = $this->googleClient->getAccessToken();

        if (is_array($accessToken) && ! $this->googleClient->isAccessTokenExpired()) {
            return;
        }

        $refreshToken = config('mail.gmail.refresh_token');

        if (! is_string($refreshToken) || $refreshToken === '') {
            throw new RuntimeException('Gmail OAuth refresh token is not configured.');
        }

        $token = $this->googleClient->fetchAccessTokenWithRefreshToken($refreshToken);

        if (isset($token['error'])) {
            throw new RuntimeException(
                'Gmail OAuth failed: '.($token['error_description'] ?? $token['error'])
            );
        }

        $this->googleClient->setAccessToken($token);
    }

    public function __toString(): string
    {
        return 'gmail-oauth';
    }
}
