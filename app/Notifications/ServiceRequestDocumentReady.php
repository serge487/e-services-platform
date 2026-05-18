<?php

namespace App\Notifications;

use App\Models\RequestDocument;
use App\Models\ServiceRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class ServiceRequestDocumentReady extends Notification
{
    use Queueable;

    public function __construct(
        private ServiceRequest $serviceRequest,
        private RequestDocument $document,
        private ?string $downloadUrl = null,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $serviceName = $this->serviceRequest->service->name ?? 'Service request';
        $filename = basename($this->document->file_path);

        return [
            'type' => 'service_document_ready',
            'service_request_id' => $this->serviceRequest->id,
            'request_document_id' => $this->document->id,
            'service_name' => $serviceName,
            'office_name' => $this->serviceRequest->service->office->name ?? null,
            'document_name' => $filename,
            'download_url' => $this->downloadUrl,
            'sender_name' => 'Response document ready',
            'preview' => 'A response document was sent for '.$serviceName.': '.Str::limit($filename, 80),
        ];
    }
}
