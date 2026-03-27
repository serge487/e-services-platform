<?php

namespace App\Http\Controllers\Municipality;

use App\Http\Controllers\Controller;
use App\Models\RequestDocument;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ServiceRequestController extends Controller
{
    // All valid statuses — single source of truth
    const STATUSES = [
        'Pending',
        'In Review',
        'Missing Documents',
        'Approved',
        'Rejected',
        'Completed',
    ];

    /**
     * List all incoming requests for this municipality's offices,
     * with optional status filter.
     */
    public function index(Request $request)
    {
        $officeIds = $this->getAccessibleOfficeIds();

        $statusFilter = $request->query('status');

        $serviceRequests = ServiceRequest::with([
            'citizen',
            'service.office',
            'service.category',
            'requestDocuments',
        ])
            ->whereHas('service', fn ($query) => $query->whereIn('office_id', $officeIds))
            ->when($statusFilter, fn ($query) => $query->where('status', $statusFilter))
            ->latest()
            ->paginate(20);

        return view('municipality.requests', compact('serviceRequests', 'statusFilter'));
    }

    /**
     * Show a single request with all its details, documents and messages.
     */
    public function show(ServiceRequest $serviceRequest)
    {
        $this->authorizeRequestAccess($serviceRequest);

        $serviceRequest->load([
            'citizen',
            'service.office',
            'service.category',
            'requestDocuments',
            'payment',
            'messages.sender',
        ]);

        return view('municipality.requests-show', compact('serviceRequest'));
    }

    /**
     * Update the status of a service request and optionally add office notes.
     */
    public function updateStatus(Request $request, ServiceRequest $serviceRequest)
    {
        $this->authorizeRequestAccess($serviceRequest);

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:'.implode(',', self::STATUSES)],
            'office_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $serviceRequest->update([
            'status' => $validated['status'],
            'office_notes' => $validated['office_notes'] ?? $serviceRequest->office_notes,
        ]);

        return back()->with('success', 'Request status updated to "'.$validated['status'].'".');
    }

    /**
     * Upload an official response PDF document for a request.
     */
    public function uploadDocument(Request $request, ServiceRequest $serviceRequest)
    {
        $this->authorizeRequestAccess($serviceRequest);

        $request->validate([
            'response_document' => [
                'required',
                'file',
                'mimes:pdf',
                'max:10240', // 10 MB
            ],
        ]);

        $uploadedFile = $request->file('response_document');

        // Store under a per-request folder: official-responses/{request_id}/filename
        $filePath = $uploadedFile->store(
            'official-responses/'.$serviceRequest->id,
            'private'
        );

        RequestDocument::create([
            'service_request_id' => $serviceRequest->id,
            'file_path' => $filePath,
            'type' => 'official_response',
        ]);

        return back()->with('success', 'Official response document uploaded successfully.');
    }

    /**
     * Delete an official response document.
     * Only official_response type documents can be deleted by municipality staff.
     */
    public function deleteDocument(ServiceRequest $serviceRequest, RequestDocument $document)
    {
        $this->authorizeRequestAccess($serviceRequest);

        if ($document->service_request_id !== $serviceRequest->id) {
            abort(403, 'Document does not belong to this request.');
        }

        if ($document->type !== 'official_response') {
            abort(403, 'Only official response documents can be deleted by staff.');
        }

        Storage::disk('private')->delete($document->file_path);
        $document->delete();

        return back()->with('success', 'Document deleted successfully.');
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Get all office IDs belonging to the authenticated user's municipality.
     */
    /**
     * @return list<int>
     */
    private function getAccessibleOfficeIds(): array
    {
        $ids = Auth::user()->accessibleOfficeIds();

        if ($ids === []) {
            abort(403, 'No office access is assigned to your account.');
        }

        return $ids;
    }

    /**
     * Abort 403 if the service request does not belong to this municipality's offices.
     */
    private function authorizeRequestAccess(ServiceRequest $serviceRequest): void
    {
        $officeIds = $this->getAccessibleOfficeIds();

        $belongs = in_array($serviceRequest->service->office_id, $officeIds, true);

        if (! $belongs) {
            abort(403, 'You do not have permission to manage this request.');
        }
    }
}
