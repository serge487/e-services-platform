<?php

namespace App\Services;

use App\Models\RequestDocument;
use App\Models\ServiceRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TwilioWhatsAppService
{
    public function sendDocumentReadyMessage(
        ServiceRequest $serviceRequest,
        RequestDocument $document,
        string $downloadUrl,
    ): void {
        $sid = config('services.twilio.account_sid');
        $token = config('services.twilio.auth_token');
        $from = config('services.twilio.whatsapp_from');
        $to = $this->whatsAppRecipient(
            config('services.twilio.whatsapp_to_override') ?: ($serviceRequest->citizen->phone_number ?? null)
        );

        if (! $sid || ! $token || ! $from || ! $to) {
            Log::info('Twilio WhatsApp document message skipped: missing config or phone number.', [
                'service_request_id' => $serviceRequest->id,
                'document_id' => $document->id,
                'has_sid' => (bool) $sid,
                'has_token' => (bool) $token,
                'has_from' => (bool) $from,
                'has_to' => (bool) $to,
            ]);

            return;
        }

        $message = 'Your response document for request #'.$serviceRequest->id.
            ' ('.$serviceRequest->service->name.') is ready. Download it here: '.$downloadUrl;

        try {
            Log::info('Twilio WhatsApp document message sending.', [
                'service_request_id' => $serviceRequest->id,
                'document_id' => $document->id,
                'to' => $this->maskedPhone($to),
                'from' => $this->maskedPhone($this->whatsAppAddress($from)),
            ]);

            $response = Http::asForm()
                ->withBasicAuth($sid, $token)
                ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                    'From' => $this->whatsAppAddress($from),
                    'To' => $to,
                    'Body' => $message,
                ]);

            if ($response->failed()) {
                Log::warning('Twilio WhatsApp document message rejected.', [
                    'service_request_id' => $serviceRequest->id,
                    'document_id' => $document->id,
                    'status' => $response->status(),
                    'response' => $response->json() ?: $response->body(),
                ]);

                return;
            }

            Log::info('Twilio WhatsApp document message sent.', [
                'service_request_id' => $serviceRequest->id,
                'document_id' => $document->id,
                'twilio_sid' => $response->json('sid'),
                'status' => $response->json('status'),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Twilio WhatsApp document message failed', [
                'service_request_id' => $serviceRequest->id,
                'document_id' => $document->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function whatsAppRecipient(?string $phoneNumber): ?string
    {
        if (! $phoneNumber) {
            return null;
        }

        return $this->whatsAppAddress($phoneNumber);
    }

    private function whatsAppAddress(string $phoneNumber): string
    {
        $phoneNumber = trim($phoneNumber);

        if (str_starts_with($phoneNumber, 'whatsapp:')) {
            return $phoneNumber;
        }

        return 'whatsapp:'.$phoneNumber;
    }

    private function maskedPhone(?string $phoneNumber): ?string
    {
        if (! $phoneNumber) {
            return null;
        }

        return substr($phoneNumber, 0, 10).'***'.substr($phoneNumber, -2);
    }
}
