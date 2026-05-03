@extends('layouts.public')
@section('title', 'Request Details')
@section('page-title', 'Request Details')
@push('styles')
<style>
    /* .request-detail-page {
        --detail-green: #0a5c4a;
        --detail-green-dark: #064e3b;
        --detail-green-soft: #ecfdf5;
        --detail-border: #d9eee7;
        --detail-muted: #64748b;
        --detail-ink: #1f2937;
    }

    .request-detail-page .btn-detail-outline {
        border-color: var(--detail-green);
        color: var(--detail-green);
        border-radius: 7px;
        font-weight: 700;
    }

    .request-detail-page .btn-detail-outline:hover,
    .request-detail-page .btn-detail-outline:focus,
    .request-detail-page .btn-detail-download:hover,
    .request-detail-page .btn-detail-download:focus {
        background: var(--detail-green);
        color: #fff;
    }

    .request-detail-page .detail-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        box-shadow: 0 1px 8px rgba(15, 23, 42, 0.06);
        overflow: hidden;
    }

    .request-detail-page .detail-card-header {
        background: #fff;
        border-bottom: 1px solid #eef2f7;
        color: var(--detail-green);
        font-size: 0.76rem;
        font-weight: 800;
        letter-spacing: 0.7px;
        padding: 0.9rem 1rem;
        text-transform: uppercase;
    }

    .request-detail-page .field-label {
        color: var(--detail-muted);
        display: block;
        font-size: 0.68rem;
        font-weight: 800;
        letter-spacing: 0.5px;
        margin-bottom: 0.2rem;
        text-transform: uppercase;
    }

    .request-detail-page .field-value {
        color: var(--detail-ink);
        font-size: 0.92rem;
        font-weight: 700;
        line-height: 1.4;
    }

    .request-detail-page .field-subtext {
        color: var(--detail-muted);
        font-size: 0.8rem;
    }

    .request-detail-page .status-badge {
        background: var(--detail-green-soft);
        border: 1px solid var(--detail-border);
        border-radius: 999px;
        color: var(--detail-green);
        display: inline-flex;
        font-size: 0.75rem;
        font-weight: 800;
        padding: 0.32rem 0.6rem;
    }

    .request-detail-page .detail-code {
        color: var(--detail-green);
        font-weight: 800;
    }

    .request-detail-page .service-description {
        color: #475569;
        font-size: 0.92rem;
        line-height: 1.55;
    }

    .request-detail-page .metric-panel {
        background: #f8fafc;
        border: 1px solid #edf2f7;
        border-radius: 7px;
        padding: 0.8rem;
        height: 100%;
    }

    .request-detail-page .metric-value {
        color: var(--detail-ink);
        display: block;
        font-size: 0.98rem;
        font-weight: 800;
        margin-top: 0.15rem;
    }

    .request-detail-page .metric-value.price {
        color: var(--detail-green);
    }

    .request-detail-page .document-row {
        align-items: center;
        border: 1px solid #edf2f7;
        border-radius: 7px;
        display: flex;
        gap: 0.75rem;
        justify-content: space-between;
        padding: 0.75rem;
    }

    .request-detail-page .document-row + .document-row {
        margin-top: 0.65rem;
    }

    .request-detail-page .document-icon {
        color: var(--detail-green);
        font-size: 1.15rem;
    }

    .request-detail-page .btn-detail-download {
        border: 1.5px solid var(--detail-green);
        color: var(--detail-green);
        border-radius: 7px;
        font-size: 0.8rem;
        font-weight: 800;
        white-space: nowrap;
    }

    .request-detail-page .note-panel {
        background: var(--detail-green-soft);
        border-left: 4px solid var(--detail-green);
        color: #164e3f;
        padding: 1rem;
    }

    .request-detail-page .qr-box {
        background: #fff;
        border: 1px solid var(--detail-border);
        border-radius: 8px;
        display: inline-block;
        padding: 10px;
    }

    .request-detail-page .token-box {
        background: var(--detail-green-soft);
        border: 1px solid var(--detail-border);
        border-radius: 7px;
        color: var(--detail-green);
        padding: 0.8rem;
        word-break: break-all;
    }

    .request-detail-page .timeline {
        position: relative;
        padding: 0;
    }

    .request-detail-page .timeline-item {
        display: flex;
        gap: 1rem;
        padding-bottom: 1.5rem;
        position: relative;
    }

    .request-detail-page .timeline-marker {
        background: #d8e4df;
        border: 2px solid #fff;
        border-radius: 50%;
        box-shadow: 0 0 0 2px #d8e4df;
        flex-shrink: 0;
        height: 12px;
        margin-top: 6px;
        width: 12px;
    }

    .request-detail-page .timeline-item.completed .timeline-marker,
    .request-detail-page .timeline-marker.completed {
        background: var(--detail-green);
        box-shadow: 0 0 0 2px var(--detail-green);
    }

    .request-detail-page .timeline-item:not(:last-child)::before {
        border-left: 2px solid #d8e4df;
        content: '';
        height: calc(100% + 20px);
        left: 5px;
        position: absolute;
        top: 24px;
        width: 0;
    }

    .request-detail-page .timeline-item.completed:not(:last-child)::before {
        border-left-color: var(--detail-green);
    }

    .request-detail-page .alert-info {
        background: var(--detail-green-soft);
        border-color: var(--detail-border);
        color: var(--detail-green);
    } */

    /* Fix for sidebar overlap on this page only */
body {
    padding-left: 260px !important;
}
    .request-detail-page {
        --detail-green: #0a5c4a;
        --detail-green-dark: #064e3b;
        --detail-green-soft: #ecfdf5;
        --detail-border: #d9eee7;
        --detail-muted: #64748b;
        --detail-ink: #1f2937;
        width: 100%;
        box-sizing: border-box;
    }

    .request-detail-page .btn-detail-outline {
        border-color: var(--detail-green);
        color: var(--detail-green);
        border-radius: 7px;
        font-weight: 700;
    }

    .request-detail-page .btn-detail-outline:hover,
    .request-detail-page .btn-detail-outline:focus,
    .request-detail-page .btn-detail-download:hover,
    .request-detail-page .btn-detail-download:focus {
        background: var(--detail-green);
        color: #fff;
    }

    .request-detail-page .detail-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        box-shadow: 0 1px 8px rgba(15, 23, 42, 0.06);
        overflow: hidden;
    }

    .request-detail-page .detail-card-header {
        background: #fff;
        border-bottom: 1px solid #eef2f7;
        color: var(--detail-green);
        font-size: 0.76rem;
        font-weight: 800;
        letter-spacing: 0.7px;
        padding: 0.9rem 1rem;
        text-transform: uppercase;
    }

    .request-detail-page .field-label {
        color: var(--detail-muted);
        display: block;
        font-size: 0.68rem;
        font-weight: 800;
        letter-spacing: 0.5px;
        margin-bottom: 0.2rem;
        text-transform: uppercase;
    }

    .request-detail-page .field-value {
        color: var(--detail-ink);
        font-size: 0.92rem;
        font-weight: 700;
        line-height: 1.4;
    }

    .request-detail-page .field-subtext {
        color: var(--detail-muted);
        font-size: 0.8rem;
    }

    .request-detail-page .status-badge {
        background: var(--detail-green-soft);
        border: 1px solid var(--detail-border);
        border-radius: 999px;
        color: var(--detail-green);
        display: inline-flex;
        font-size: 0.75rem;
        font-weight: 800;
        padding: 0.32rem 0.6rem;
    }

    .request-detail-page .detail-code {
        color: var(--detail-green);
        font-weight: 800;
    }

    .request-detail-page .service-description {
        color: #475569;
        font-size: 0.92rem;
        line-height: 1.55;
    }

    .request-detail-page .metric-panel {
        background: #f8fafc;
        border: 1px solid #edf2f7;
        border-radius: 7px;
        padding: 0.8rem;
        height: 100%;
    }

    .request-detail-page .metric-value {
        color: var(--detail-ink);
        display: block;
        font-size: 0.98rem;
        font-weight: 800;
        margin-top: 0.15rem;
    }

    .request-detail-page .metric-value.price {
        color: var(--detail-green);
    }

    .request-detail-page .document-row {
        align-items: center;
        border: 1px solid #edf2f7;
        border-radius: 7px;
        display: flex;
        gap: 0.75rem;
        justify-content: space-between;
        padding: 0.75rem;
    }

    .request-detail-page .document-row + .document-row {
        margin-top: 0.65rem;
    }

    .request-detail-page .document-icon {
        color: var(--detail-green);
        font-size: 1.15rem;
    }

    .request-detail-page .btn-detail-download {
        border: 1.5px solid var(--detail-green);
        color: var(--detail-green);
        border-radius: 7px;
        font-size: 0.8rem;
        font-weight: 800;
        white-space: nowrap;
    }

    .request-detail-page .note-panel {
        background: var(--detail-green-soft);
        border-left: 4px solid var(--detail-green);
        color: #164e3f;
        padding: 1rem;
    }

    .request-detail-page .qr-box {
        background: #fff;
        border: 1px solid var(--detail-border);
        border-radius: 8px;
        display: inline-block;
        padding: 10px;
    }

    .request-detail-page .token-box {
        background: var(--detail-green-soft);
        border: 1px solid var(--detail-border);
        border-radius: 7px;
        color: var(--detail-green);
        padding: 0.8rem;
        word-break: break-all;
    }

    .request-detail-page .timeline {
        position: relative;
        padding: 0;
    }

    .request-detail-page .timeline-item {
        display: flex;
        gap: 1rem;
        padding-bottom: 1.5rem;
        position: relative;
    }

    .request-detail-page .timeline-marker {
        background: #d8e4df;
        border: 2px solid #fff;
        border-radius: 50%;
        box-shadow: 0 0 0 2px #d8e4df;
        flex-shrink: 0;
        height: 12px;
        margin-top: 6px;
        width: 12px;
    }

    .request-detail-page .timeline-item.completed .timeline-marker,
    .request-detail-page .timeline-marker.completed {
        background: var(--detail-green);
        box-shadow: 0 0 0 2px var(--detail-green);
    }

    .request-detail-page .timeline-item:not(:last-child)::before {
        border-left: 2px solid #d8e4df;
        content: '';
        height: calc(100% + 20px);
        left: 5px;
        position: absolute;
        top: 24px;
        width: 0;
    }

    .request-detail-page .timeline-item.completed:not(:last-child)::before {
        border-left-color: var(--detail-green);
    }

    .request-detail-page .alert-info {
        background: var(--detail-green-soft);
        border-color: var(--detail-border);
        color: var(--detail-green);
    }

    /* ── FIX ── */
    .request-detail-page .detail-row {
        display: flex;
        flex-wrap: wrap;
        gap: 1.25rem;
        width: 100%;
        box-sizing: border-box;
        margin: 0;
    }

    .request-detail-page .detail-main {
        flex: 1 1 0;
        min-width: 0;
    }

    .request-detail-page .detail-aside {
        width: 300px;
        flex-shrink: 0;
        min-width: 0;
    }

    @media (max-width: 900px) {
        .request-detail-page .detail-aside {
            width: 100%;
        }
    }

</style>
@endpush
@section('content')
<div class="request-detail-page" style="width:100%; overflow-x:hidden;">    <!-- Back button -->
    <a href="{{ route('citizen.requests') }}" class="btn btn-detail-outline btn-sm mb-3">
        <i class="bi bi-arrow-left"></i> Back to Requests
    </a>

<div class="detail-row">
<div class="detail-main">            <!-- Request Info Card -->
            <div class="detail-card mb-4">
                <div class="detail-card-header">
                    <i class="bi bi-file-earmark-text me-2"></i>Request Information
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <span class="field-label">Service Name</span>
                            <div class="field-value">{{ $serviceRequest->service->name }}</div>
                        </div>
                        <div class="col-md-6">
                            <span class="field-label">Office</span>
                            <div class="field-value">{{ $serviceRequest->service->office->name }}</div>
                        </div>
                        <div class="col-md-6">
                            <span class="field-label">Request ID</span>
                            <code class="detail-code">#{{ $serviceRequest->id }}</code>
                        </div>
                        <div class="col-md-6">
                            <span class="field-label">Status</span>
                            <span class="status-badge">
                                {{ $serviceRequest->citizenDisplayStatus() }}
                            </span>
                        </div>
                        <div class="col-md-6">
                            <span class="field-label">Submitted Date</span>
                            <span class="field-subtext">{{ $serviceRequest->created_at->format('M d, Y \a\t g:i A') }}</span>
                        </div>
                        <div class="col-md-6">
                            <span class="field-label">Last Updated</span>
                            <span class="field-subtext">{{ $serviceRequest->updated_at->format('M d, Y \a\t g:i A') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Service Details -->
            <div class="detail-card mb-4">
                <div class="detail-card-header">
                    <i class="bi bi-grid-3x3-gap me-2"></i>Service Details
                </div>
                <div class="card-body p-4">
                    <p class="service-description">{{ $serviceRequest->service->description }}</p>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="metric-panel">
                                <span class="field-label">Service Fee</span>
                                <span class="metric-value price">${{ number_format($serviceRequest->service->price, 2) }}</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="metric-panel">
                                <span class="field-label">Processing Time</span>
                                <span class="metric-value">{{ $serviceRequest->service->duration_days }} days</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="metric-panel">
                                <span class="field-label">Category</span>
                                <span class="metric-value">{{ $serviceRequest->service->category->name }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submitted Documents -->
            @if($serviceRequest->requestDocuments->where('type', 'citizen_upload')->count() > 0)
                <div class="detail-card mb-4">
                    <div class="detail-card-header">
                        <i class="bi bi-cloud-upload me-2"></i>Your Submitted Documents
                    </div>
                    <div class="card-body p-4">
                        <div>
                            @foreach($serviceRequest->requestDocuments->where('type', 'citizen_upload') as $doc)
                                <div class="document-row">
                                    <div class="d-flex align-items-start gap-2">
                                        <i class="bi bi-file-earmark document-icon"></i>
                                        <div>
                                            <div class="field-value">{{ basename($doc->file_path) }}</div>
                                            <small class="field-subtext">Uploaded {{ $doc->created_at->diffForHumans() }}</small>
                                        </div>
                                    </div>
                                    <a href="{{ route('citizen.service-requests.download', [$serviceRequest, $doc]) }}" 
                                       class="btn btn-sm btn-detail-download">
                                        <i class="bi bi-download"></i> Download
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Official Response Documents -->
            @if($serviceRequest->requestDocuments->where('type', 'official_response')->count() > 0)
                <div class="detail-card mb-4">
                    <div class="detail-card-header">
                        <i class="bi bi-cloud-download me-2"></i>Official Response Documents
                    </div>
                    <div class="card-body p-4">
                        <div>
                            @foreach($serviceRequest->requestDocuments->where('type', 'official_response') as $doc)
                                <div class="document-row">
                                    <div class="d-flex align-items-start gap-2">
                                        <i class="bi bi-file-earmark-pdf document-icon"></i>
                                        <div>
                                            <div class="field-value">{{ basename($doc->file_path) }}</div>
                                            <small class="field-subtext">Received {{ $doc->created_at->diffForHumans() }}</small>
                                        </div>
                                    </div>
                                    <a href="{{ route('citizen.service-requests.download', [$serviceRequest, $doc]) }}" 
                                       class="btn btn-sm btn-detail-download">
                                        <i class="bi bi-download"></i> Download
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Office Notes -->
            @if($serviceRequest->office_notes)
                <div class="detail-card mb-4">
                    <div class="detail-card-header">
                        <i class="bi bi-chat-dots me-2"></i>Office Notes
                    </div>
                    <div class="card-body p-4">
                        <p class="mb-0">{{ $serviceRequest->office_notes }}</p>
                    </div>
                </div>
            @endif

            <!-- Messages -->
            @if($serviceRequest->messages->count() > 0)
                <div class="detail-card mb-4">
                    <div class="detail-card-header">
                        <i class="bi bi-chat-left-text me-2"></i>Messages ({{ $serviceRequest->messages->count() }})
                    </div>
                    <div class="card-body p-4">
                        <div class="chat-messages" style="max-height: 300px; overflow-y: auto;">
                            @foreach($serviceRequest->messages as $message)
                                <div class="mb-3 pb-3 border-bottom last-child-no-border">
                                    <strong>{{ $message->sender->name }}</strong>
                                    <small class="text-muted d-block">{{ $message->created_at->diffForHumans() }}</small>
                                    <p class="mb-0 mt-1">{{ $message->content }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
         <div class="detail-aside">            <!-- QR Code Tracking -->
            <div class="detail-card mb-3">
                <div class="detail-card-header">
                    <i class="bi bi-qr-code me-2"></i>Track Request
                </div>
                <div class="card-body text-center p-4">
                    <p class="small text-muted mb-3">Scan this code to track your request status:</p>
                    <div id="qrcode" class="qr-box mb-3"></div>
                    <br>
                    <small class="text-muted d-block">Or use this token:</small>
                    <div class="token-box mt-2 mb-2">
                        <code class="text-break" style="word-break: break-all;">{{ $serviceRequest->qr_code_token }}</code>
                    </div>
                    <small class="text-muted d-block">This code can be used for tracking via email or mobile app.</small>
                </div>
            </div>

            <!-- Timeline -->
            <div class="detail-card">
                <div class="detail-card-header">
                    <i class="bi bi-list-check me-2"></i>Status Timeline
                </div>
                <div class="card-body p-4">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker completed"></div>
                            <div>
                                <strong>Request Submitted</strong>
                                <br>
                                <small class="text-muted">{{ $serviceRequest->created_at->format('M d, Y') }}</small>
                            </div>
                        </div>
                        <div class="timeline-item @if(in_array($serviceRequest->status, ['In Review', 'Missing Documents', 'Approved', 'Rejected', 'Completed'])) completed @endif">
                            <div class="timeline-marker"></div>
                            <div>
                                <strong>Under Review</strong>
                                <br>
                                <small class="text-muted">{{ $serviceRequest->isAccepted() ? 'Office has accepted your request' : 'Submitted to the office queue' }}</small>
                            </div>
                        </div>
                        <div class="timeline-item @if(in_array($serviceRequest->status, ['Approved', 'Completed'])) completed @endif">
                            <div class="timeline-marker"></div>
                            <div>
                                <strong>Approved</strong>
                                <br>
                                <small class="text-muted">Request approved and being processed</small>
                            </div>
                        </div>
                        <div class="timeline-item @if($serviceRequest->status === 'Completed') completed @endif">
                            <div class="timeline-marker"></div>
                            <div>
                                <strong>Completed</strong>
                                <br>
                                <small class="text-muted">Your service is ready</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    // Generate QR Code
    document.addEventListener('DOMContentLoaded', function() {
        const qrToken = "{{ $serviceRequest->qr_code_token }}";
        const scanUrl = "{{ route('qr.scan', ['token' => '__TOKEN__'], absolute: true) }}".replace('__TOKEN__', qrToken);
        
        // Generate QR code
        if (document.getElementById('qrcode')) {
            new QRCode(document.getElementById('qrcode'), {
                text: scanUrl,
                width: 150,
                height: 150,
                colorDark: "#000000",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.H
            });
        }
    });

    // Real-time status updates via Reverb/Broadcasting
    @if(auth()->check())
        if (typeof window.Echo !== 'undefined') {
            window.Echo.private('service-request.{{ $serviceRequest->id }}')
                .listen('service-request-status-changed', (event) => {
                    console.log('Status updated:', event);
                    
                    // Show notification
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-info alert-dismissible fade show';
                    alertDiv.innerHTML = `
                        <i class="bi bi-info-circle me-2"></i>Your request status has been updated!
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    `;
                    const container = document.querySelector('.container-fluid');
                    if (container) {
                        container.insertBefore(alertDiv, container.firstChild);
                    }
                    
                    // Reload page to show updated status
                    setTimeout(() => location.reload(), 2000);
                });
            
            // Also listen to citizen-specific notifications
            window.Echo.private('citizen.{{ auth()->id() }}')
                .notification((notification) => {
                    console.log('Notification received:', notification);
                    
                    // Show toast notification for status updates
                    if (notification.type === 'App\\\\Notifications\\\\ServiceRequestStatusUpdated') {
                        const alertDiv = document.createElement('div');
                        alertDiv.className = 'alert alert-success alert-dismissible fade show position-fixed top-0 end-0 m-3';
                        alertDiv.style.zIndex = '9999';
                        alertDiv.innerHTML = `
                            <i class="bi bi-bell me-2"></i><strong>Request Update</strong>: ${notification.data.status}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        `;
                        document.body.appendChild(alertDiv);
                        
                        // Auto-dismiss after 5 seconds
                        setTimeout(() => alertDiv.remove(), 5000);
                    }
                });
        }
    @endif
</script>
@endpush

