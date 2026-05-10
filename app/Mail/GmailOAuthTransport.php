<?php

namespace App\Mail;

use Google\Client as GoogleClient;
use Google\Service\Gmail;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\MessageConverter;

class GmailOAuthTransport extends AbstractTransport
{
    private GoogleClient $googleClient;

    public function __construct()
    {
        parent::__construct();

        $this->googleClient = new GoogleClient();
        $this->googleClient->setClientId(config('mail.gmail.client_id'));
        $this->googleClient->setClientSecret(config('mail.gmail.client_secret'));
        $this->googleClient->refreshToken(config('mail.gmail.refresh_token'));
        $this->googleClient->setAccessType('offline');
        $this->googleClient->addScope(Gmail::GMAIL_SEND);
    }

    /**
     * Send the email via Gmail API using OAuth2.
     */
    protected function doSend(SentMessage $message): void
    {
        $rawEmail = $message->toString();

        // Gmail API requires base64url encoding (not standard base64)
        $encodedMessage = rtrim(strtr(base64_encode($rawEmail), '+/', '-_'), '=');

        $gmailService = new Gmail($this->googleClient);

        $gmailMessage = new Gmail\Message();
        $gmailMessage->setRaw($encodedMessage);

        $gmailService->users_messages->send('me', $gmailMessage);
    }

    public function __toString(): string
    {
        return 'gmail-oauth';
    }
}