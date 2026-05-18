<?php

namespace App\Http\Controllers\Municipality;

use App\Events\ServiceRequestStatusChanged;
use App\Http\Controllers\Controller;
use App\Models\RequestDocument;
use App\Models\ServiceRequest;
use App\Models\Payment;
use App\Notifications\ServiceRequestStatusUpdated;
use App\Services\ServiceRequestCompletionDocumentService;
use App\Services\ServiceRequestDocumentNotifier;
use App\Services\NotificationRealtimeBroadcaster;
use App\Services\PaymentRevenueBroadcaster;
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

    public function index(Request $request)
    {
        $officeIds = $this->getAccessibleOfficeIds();

        $statusFilter = $request->query('status');

        $serviceRequests = ServiceRequest::with([
            'citizen',
            'service.office',
            'service.category',
            'requestDocuments',
            'acceptedBy',
        ])
            ->whereHas('service', fn ($query) => $query->whereIn('office_id', $officeIds))
            ->when($statusFilter, fn ($query) => $query->where('status', $statusFilter))
            ->latest()
            ->paginate(20);

        return view('municipality.requests', compact('serviceRequests', 'statusFilter'));
    }

    public function show(ServiceRequest $serviceRequest)
    {
        $this->authorizeRequestAccess($serviceRequest);

        $serviceRequest->load([
            'citizen',
            'service.office',
            'service.category',
            'requestDocuments',
            'acceptedBy',
            'payment',
            'messages.sender',
        ]);

        return view('municipality.requests-show', compact('serviceRequest'));
    }

    public function updateStatus(Request $request, ServiceRequest $serviceRequest)
    {
        $this->authorizeRequestAccess($serviceRequest);

        $previousStatus = $serviceRequest->status;

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:' . implode(',', self::STATUSES)],
            'office_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        // LOAD PAYMENT SAFELY
        $serviceRequest->loadMissing('payment');

        // ❗ FIX: check BEFORE updating
        if (
            $validated['status'] === 'Approved'
            && $serviceRequest->payment
            && ! $serviceRequest->payment->isPaid()
        ) {
            return back()->withErrors([
                'status' => 'Cannot approve this request — payment has not been completed yet.',
            ]);
        }

        if ($previousStatus === 'Pending' && $validated['status'] !== 'Pending') {
            return back()->withErrors([
                'status' => 'Pending requests must be marked as taken before changing their workflow status.',
            ]);
        }

        $serviceRequest->update([
            'status' => $validated['status'],
            'office_notes' => $validated['office_notes'] ?? $serviceRequest->office_notes,
        ]);

        if ($validated['status'] === ServiceRequest::STATUS_COMPLETED) {
            PaymentRevenueBroadcaster::broadcastForServiceRequest($serviceRequest);
        }

        if ($validated['status'] === ServiceRequest::STATUS_COMPLETED && $previousStatus !== ServiceRequest::STATUS_COMPLETED) {
            $document = app(ServiceRequestCompletionDocumentService::class)->createFor($serviceRequest->fresh());
            app(ServiceRequestDocumentNotifier::class)->broadcastDocumentUploaded($serviceRequest->fresh(), $document);
            app(ServiceRequestDocumentNotifier::class)->notify($serviceRequest->fresh(), $document);
        }

        ServiceRequestStatusChanged::dispatch(
            $serviceRequest,
            $previousStatus,
            $validated['status'],
            $validated['office_notes'] ?? null
        );

        $serviceRequest->citizen->notify(
            new ServiceRequestStatusUpdated(
                $serviceRequest,
                $validated['status'],
                $validated['office_notes'] ?? null
            )
        );

        NotificationRealtimeBroadcaster::broadcastLatest($serviceRequest->citizen);

        return back()->with('success', 'Request status updated to "' . $validated['status'] . '".');
    }

    public function acceptRequest(ServiceRequest $serviceRequest)
    {
        $this->authorizeRequestAccess($serviceRequest);

        $serviceRequest->loadMissing(['service', 'payment']);

        if ($serviceRequest->status !== 'Pending') {
            return back()->with('error', 'Only pending requests can be accepted.');
        }

        $previousStatus = $serviceRequest->status;

        $serviceRequest->update([
            'status' => 'In Review',
            'accepted_by' => Auth::id(),
            'accepted_at' => now(),
        ]);

        // ✅ FIXED: prevents duplicate payments
        Payment::firstOrCreate(
            ['service_request_id' => $serviceRequest->id],
            [
                'amount' => $serviceRequest->service->price,
                'currency' => 'USD',
                'payment_method' => 'cash',
                'status' => 'pending',
            ]
        );

        ServiceRequestStatusChanged::dispatch(
            $serviceRequest,
            $previousStatus,
            'In Review',
            'Request marked as taken by office staff.'
        );

        $serviceRequest->citizen->notify(
            new ServiceRequestStatusUpdated(
                $serviceRequest,
                'In Review',
                'Your request is now under review by the office.'
            )
        );

        NotificationRealtimeBroadcaster::broadcastLatest($serviceRequest->citizen);

        return back()->with('success', 'Request marked as taken and moved to "In Review".');
    }

   public function uploadDocument(Request $request, ServiceRequest $serviceRequest)
{
    $this->authorizeRequestAccess($serviceRequest);

    $request->validate([
        'response_document' => [
            'required',
            'file',
            'mimes:pdf',
            'max:10240',
        ],
    ]);

    $uploadedFile = $request->file('response_document');

    $filePath = $uploadedFile->store(
        'official-responses/' . $serviceRequest->id,
        'private'
    );

    $doc = RequestDocument::create([
        'service_request_id' => $serviceRequest->id,
        'file_path' => $filePath,
        'type' => 'official_response',
    ]);

    app(ServiceRequestDocumentNotifier::class)->broadcastDocumentUploaded($serviceRequest, $doc);
    app(ServiceRequestDocumentNotifier::class)->notify($serviceRequest, $doc);

    return back()->with('success', 'Official response document uploaded successfully.');
}

    public function deleteDocument(ServiceRequest $serviceRequest, RequestDocument $document)
    {
        $this->authorizeRequestAccess($serviceRequest);

        if ($document->service_request_id !== $serviceRequest->id) {
            abort(403);
        }

        if ($document->type !== 'official_response') {
            abort(403);
        }

        Storage::disk('private')->delete($document->file_path);
        $document->delete();

        return back()->with('success', 'Document deleted successfully.');
    }

    public function downloadDocument(ServiceRequest $serviceRequest, RequestDocument $document)
    {
        $this->authorizeRequestAccess($serviceRequest);

        if ($document->service_request_id !== $serviceRequest->id) {
            abort(404);
        }

        return Storage::disk('private')->download($document->file_path);
    }

    private function getAccessibleOfficeIds(): array
    {
        $ids = Auth::user()->accessibleOfficeIds();

        if ($ids === []) {
            abort(403);
        }

        return $ids;
    }

    private function authorizeRequestAccess(ServiceRequest $serviceRequest): void
    {
        $officeIds = $this->getAccessibleOfficeIds();

        if (! in_array($serviceRequest->service->office_id, $officeIds, true)) {
            abort(403);
        }
    }

    public function confirmPayment(ServiceRequest $serviceRequest)
    {
        $this->authorizeRequestAccess($serviceRequest);

        $serviceRequest->loadMissing('payment');

        $payment = $serviceRequest->payment;

        if (! $payment || $payment->isPaid()) {
            return back()->with('error', 'Payment already confirmed or not found.');
        }

        $payment->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        $serviceRequest->update([
            'status' => 'Approved',
            'office_notes' => 'Payment verified and confirmed by office staff on ' .
                now()->format('M d, Y \a\t g:i A') . '.',
        ]);

        $serviceRequest->citizen->notify(
            new ServiceRequestStatusUpdated(
                $serviceRequest,
                'Approved',
                'Your payment has been verified. Your request is now approved.'
            )
        );

        NotificationRealtimeBroadcaster::broadcastLatest($serviceRequest->citizen);

        return back()->with('success', 'Payment confirmed. Request automatically approved.');
    }
}
