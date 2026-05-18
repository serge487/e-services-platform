<?php

namespace App\Services;

use App\Models\RequestDocument;
use App\Models\ServiceRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class ServiceRequestCompletionDocumentService
{
    public function createFor(ServiceRequest $serviceRequest): RequestDocument
    {
        $serviceRequest->loadMissing(['citizen', 'service.office', 'service.category', 'payment']);

        $existing = RequestDocument::query()
            ->where('service_request_id', $serviceRequest->id)
            ->where('type', 'official_response')
            ->where('file_path', 'like', 'official-responses/'.$serviceRequest->id.'/completion-response-%')
            ->first();

        if ($existing) {
            return $existing;
        }

        $filePath = 'official-responses/'.$serviceRequest->id.'/completion-response-'.$serviceRequest->id.'.pdf';

        $pdf = Pdf::loadView('municipality.documents.completion-response', [
            'serviceRequest' => $serviceRequest,
        ]);

        Storage::disk('private')->put($filePath, $pdf->output());

        return RequestDocument::create([
            'service_request_id' => $serviceRequest->id,
            'file_path' => $filePath,
            'type' => 'official_response',
        ]);
    }
}
