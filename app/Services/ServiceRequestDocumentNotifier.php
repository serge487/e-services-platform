<?php

namespace App\Services;

use App\Models\RequestDocument;
use App\Models\ServiceRequest;
use App\Notifications\ServiceRequestDocumentReady;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class ServiceRequestDocumentNotifier
{
    public function notify(ServiceRequest $serviceRequest, RequestDocument $document): void
    {
        $serviceRequest->loadMissing(['citizen', 'service.office']);

        $downloadUrl = $this->downloadUrl($serviceRequest, $document);

        $serviceRequest->citizen->notify(
            new ServiceRequestDocumentReady($serviceRequest, $document, $downloadUrl)
        );

        NotificationRealtimeBroadcaster::broadcastLatest($serviceRequest->citizen);

        app(TwilioWhatsAppService::class)->sendDocumentReadyMessage(
            $serviceRequest,
            $document,
            $downloadUrl
        );
    }

    public function downloadUrl(ServiceRequest $serviceRequest, RequestDocument $document): string
    {
        $path = URL::temporarySignedRoute(
            'public.service-documents.download',
            now()->addDays(7),
            ['document' => $document->id],
            false
        );
        $baseUrl = rtrim((string) config('qr.public_url', ''), '/');

        if ($baseUrl === '') {
            return url($path);
        }

        return $baseUrl.$path;
    }

    public function broadcastDocumentUploaded(ServiceRequest $serviceRequest, RequestDocument $document): void
    {
        try {
            broadcast(new \App\Events\OfficialDocumentUploaded(
                $serviceRequest->citizen_id,
                $serviceRequest->id,
                basename($document->file_path),
                $this->downloadUrl($serviceRequest, $document),
            ))->toOthers();
        } catch (\Exception $e) {
            Log::warning('Official document realtime broadcast failed', [
                'service_request_id' => $serviceRequest->id,
                'document_id' => $document->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
