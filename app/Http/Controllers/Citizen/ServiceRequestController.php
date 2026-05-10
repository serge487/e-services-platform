<?php

namespace App\Http\Controllers\Citizen;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use App\Models\Service;
use App\Models\RequestDocument;
use App\Notifications\ServiceRequestCreated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ServiceRequestController extends Controller
{
    /**
     * List all service requests for the authenticated citizen.
     * Internal pending/review states are shown as "Under Review" to citizens.
     */
    public function index()
    {
        $citizenId = Auth::id();

        $requests = ServiceRequest::with([
            'service.office',
            'service.category',
            'requestDocuments',
            'payment', 

        ])
            ->where('citizen_id', $citizenId)
            ->latest()
            ->paginate(10);

        return view('citizen.requests', compact('requests'));
    }

    /**
     * Show details of a specific service request.
     */
    public function show(ServiceRequest $serviceRequest)
    {
        // Ensure citizen can only view their own requests
        if ($serviceRequest->citizen_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this request.');
        }

        $serviceRequest->load([
            'service.office',
            'service.category',
            'requestDocuments',
            'payment',
            'messages.sender',
        ]);

        return view('citizen.request-detail', compact('serviceRequest'));
    }

    /**
     * Store a new service request with documents.
     */
    public function store(Request $request)
    {
        $service = Service::findOrFail($request->input('service_id'));
        $requiredDocuments = collect($service->required_documents ?? [])->values();

        $rules = [
            'service_id' => ['required', 'exists:services,id'],
            'agree' => ['accepted'],
        ];

        $messages = [
            'agree.accepted' => 'Please confirm that the information provided is accurate and complete.',
        ];

        if ($requiredDocuments->isNotEmpty()) {
            $rules['documents'] = ['required', 'array'];

            foreach ($requiredDocuments as $index => $documentName) {
                $rules["documents.{$index}"] = ['required', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:5120'];
                $messages["documents.{$index}.required"] = "Please upload: {$documentName}.";
                $messages["documents.{$index}.mimes"] = "{$documentName} must be a PDF, Word document, JPG, or PNG file.";
                $messages["documents.{$index}.max"] = "{$documentName} must be 5MB or smaller.";
            }
        } else {
            $rules['documents'] = ['nullable', 'array'];
            $rules['documents.*'] = ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:5120'];
        }

        // Validate the request
        $validated = $request->validate($rules, $messages);

        $citizenId = Auth::id();

        // Create the service request
        $serviceRequest = ServiceRequest::create([
            'citizen_id' => $citizenId,
            'service_id' => $validated['service_id'],
            'status' => 'Pending',
            'qr_code_token' => Str::random(32),
        ]);

        // Upload documents
        foreach ($request->file('documents', []) as $index => $file) {
            if (! $file) {
                continue;
            }

            $documentName = $requiredDocuments->get((int) $index, 'Supporting Document');
            $filename = Str::slug($documentName) . '-' . Str::random(8) . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs(
                "service-requests/{$serviceRequest->id}",
                $filename,
                'private'
            );

            RequestDocument::create([
                'service_request_id' => $serviceRequest->id,
                'file_path' => $filePath,
                'type' => 'citizen_upload',
            ]);
        }

        // Notify the office staff
        try {
            $service->office->users()
                ->where('role', 'office_staff')
                ->each(function ($staff) use ($serviceRequest) {
                    $staff->notify(new ServiceRequestCreated($serviceRequest));
                });
        } catch (\Exception $e) {
            // Log error but don't fail the request creation
            \Log::error('Failed to send request notification', [
                'service_request_id' => $serviceRequest->id,
                'error' => $e->getMessage(),
            ]);
        }

        return redirect()->route('citizen.requests')
            ->with('success', 'Service request submitted successfully! You can track its status below.');
    }

    /**
     * Download a document (citizen can download their own documents and official responses).
     */
    public function downloadDocument(ServiceRequest $serviceRequest, RequestDocument $document)
    {
        // Verify citizen owns this request
        if ($serviceRequest->citizen_id !== Auth::id()) {
            abort(403, 'Unauthorized access.');
        }

        // Verify document belongs to this request
        if ($document->service_request_id !== $serviceRequest->id) {
            abort(404, 'Document not found.');
        }

        // Citizens can only download their submissions and official responses
        if (!in_array($document->type, ['citizen_upload', 'official_response'], true)) {
            abort(403, 'Cannot download this document.');
        }

        return Storage::disk('private')->download($document->file_path);
    }

    /**
     * Get real-time status updates via polling (used for dashboard updates).
     */
    public function pollStatus(ServiceRequest $serviceRequest)
    {
        if ($serviceRequest->citizen_id !== Auth::id()) {
            abort(403);
        }

        return response()->json([
            'id' => $serviceRequest->id,
            'status' => $serviceRequest->citizenDisplayStatus(),
            'actual_status' => $serviceRequest->status,
            'updated_at' => $serviceRequest->updated_at,
            'documents_count' => $serviceRequest->requestDocuments->count(),
        ]);
    }
}
