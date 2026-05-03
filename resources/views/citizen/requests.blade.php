@extends('layouts.public')
@section('title', 'My Requests')
@section('page-title', 'My Service Requests')
@push('styles')
<style>
    .requests-page {
        --requests-green: #0a5c4a;
        --requests-green-dark: #064e3b;
        --requests-green-soft: #ecfdf5;
        --requests-border: #d9eee7;
        --requests-muted: #64748b;
        --requests-ink: #1f2937;
    }

    .requests-page .request-card,
    .requests-page .empty-panel {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        box-shadow: 0 1px 8px rgba(15, 23, 42, 0.06);
    }

    .requests-page .request-card {
        height: 100%;
        transition: border-color 0.15s, box-shadow 0.15s, transform 0.15s;
    }

    .requests-page .request-card:hover {
        border-color: var(--requests-border);
        box-shadow: 0 4px 14px rgba(10, 92, 74, 0.12);
        transform: translateY(-1px);
    }

    .requests-page .request-title {
        color: var(--requests-ink);
        font-size: 0.98rem;
        font-weight: 750;
        line-height: 1.35;
    }

    .requests-page .request-office {
        color: var(--requests-muted);
        font-size: 0.8rem;
    }

    .requests-page .status-badge {
        background: var(--requests-green-soft);
        border: 1px solid var(--requests-border);
        color: var(--requests-green);
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 800;
        padding: 0.3rem 0.55rem;
        white-space: nowrap;
    }

    .requests-page .request-meta {
        border-top: 1px solid #eef2f7;
        border-bottom: 1px solid #eef2f7;
        padding: 0.8rem 0;
        margin-bottom: 0.9rem;
    }

    .requests-page .meta-label {
        color: var(--requests-muted);
        display: block;
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }

    .requests-page .meta-value {
        color: #0f172a;
        font-size: 0.86rem;
        font-weight: 750;
    }

    .requests-page .meta-value.price {
        color: var(--requests-green);
    }

    .requests-page .request-code {
        color: var(--requests-green);
        font-weight: 800;
    }

    .requests-page .documents-panel,
    .requests-page .qr-panel,
    .requests-page .office-note {
        background: #f8fafc;
        border: 1px solid #edf2f7;
        border-radius: 7px;
    }

    .requests-page .documents-panel {
        padding: 0.75rem;
    }

    .requests-page .documents-title {
        color: var(--requests-green);
        font-size: 0.76rem;
        font-weight: 800;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }

    .requests-page .document-link {
        border: 1px solid transparent;
        border-radius: 6px;
        color: #475569;
        font-size: 0.8rem;
        padding: 0.45rem 0.55rem;
    }

    .requests-page .document-link:hover {
        background: var(--requests-green-soft);
        border-color: var(--requests-border);
        color: var(--requests-green);
    }

    .requests-page .office-note {
        border-left: 4px solid var(--requests-green);
        color: #334155;
        padding: 0.75rem;
    }

    .requests-page .qr-panel {
        color: var(--requests-muted);
        padding: 0.8rem;
        text-align: center;
    }

    .requests-page .qr-panel code {
        color: var(--requests-green);
        font-size: 0.74rem;
        word-break: break-all;
    }

    .requests-page .btn-green,
    .requests-page .btn-green-outline:hover,
    .requests-page .btn-green-outline:focus {
        background: var(--requests-green);
        border-color: var(--requests-green);
        color: #fff;
    }

    .requests-page .btn-green:hover,
    .requests-page .btn-green:focus {
        background: var(--requests-green-dark);
        border-color: var(--requests-green-dark);
        color: #fff;
    }

    .requests-page .btn-green-outline {
        border: 1.5px solid var(--requests-green);
        color: var(--requests-green);
        border-radius: 7px;
        font-size: 0.82rem;
        font-weight: 800;
        padding: 0.48rem 0.75rem;
    }

    .requests-page .empty-panel {
        min-height: 235px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2.5rem 1rem;
        text-align: center;
    }

    .requests-page .empty-icon {
        align-items: center;
        background: var(--requests-green-soft);
        border: 1px solid var(--requests-border);
        border-radius: 14px;
        color: var(--requests-green);
        display: inline-flex;
        height: 54px;
        justify-content: center;
        margin-bottom: 1rem;
        width: 54px;
        font-size: 1.45rem;
    }

    .requests-page .empty-title {
        color: var(--requests-ink);
        font-weight: 750;
        margin-bottom: 0.35rem;
    }

    .requests-page .empty-text {
        color: var(--requests-muted);
        font-size: 0.9rem;
        margin-bottom: 1rem;
    }

    .requests-page .pagination .page-link {
        color: var(--requests-green);
        border-color: #dbe7e2;
    }

    .requests-page .pagination .active .page-link {
        background: var(--requests-green);
        border-color: var(--requests-green);
        color: #fff;
    }
</style>
@endpush
@section('content')
<div class="container-fluid requests-page">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($requests->count() > 0)
        <div class="row g-4">
            @foreach($requests as $request)
                <div class="col-lg-6">
                    <div class="request-card">
                        <div class="card-body">
                            <!-- Header with Status Badge -->
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h6 class="request-title mb-0">{{ $request->service->name }}</h6>
                                    <small class="request-office">{{ $request->service->office->name }}</small>
                                </div>
                                <span class="status-badge">
                                    {{ $request->citizenDisplayStatus() }}
                                </span>
                            </div>

                            <!-- Request Details -->
                            <div class="row g-2 request-meta">
                                <div class="col-6">
                                    <span class="meta-label">Request ID</span>
                                    <code class="request-code">#{{ $request->id }}</code>
                                </div>
                                <div class="col-6">
                                    <span class="meta-label">Submitted</span>
                                    <span class="meta-value">{{ $request->created_at->format('M d, Y') }}</span>
                                </div>
                                <div class="col-6">
                                    <span class="meta-label">Service Fee</span>
                                    <span class="meta-value price">${{ number_format($request->service->price, 2) }}</span>
                                </div>
                                <div class="col-6">
                                    <span class="meta-label">Expected Duration</span>
                                    <span class="meta-value">{{ $request->service->duration_days }} days</span>
                                </div>
                            </div>

                            <!-- Documents Section -->
                            @php
                                $downloadableDocs = $request->requestDocuments->whereIn('type', ['citizen_upload', 'official_response']);
                            @endphp
                            @if($downloadableDocs->count() > 0)
                                <div class="documents-panel mb-3">
                                    <small class="documents-title d-block mb-2">
                                        <i class="bi bi-file-earmark"></i> Documents ({{ $downloadableDocs->count() }})
                                    </small>
                                    <div class="d-grid gap-1">
                                        @foreach($downloadableDocs as $doc)
                                            <a href="{{ route('citizen.service-requests.download', [$request, $doc]) }}" 
                                               class="document-link text-decoration-none">
                                                <i class="bi bi-download"></i> {{ basename($doc->file_path) }}
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Office Notes (if any) -->
                            @if($request->office_notes)
                                <div class="office-note mb-3">
                                    <small class="meta-label mb-1">Office Notes</small>
                                    <small>{{ $request->office_notes }}</small>
                                </div>
                            @endif

                            <!-- QR Code Tracking -->
                            <div class="qr-panel mb-3">
                                <small class="d-block mb-2">Track this request with your QR code token:</small>
                                <code class="small">{{ $request->qr_code_token }}</code>
                            </div>

                            <!-- Action Button -->
                            <a href="{{ route('citizen.service-requests.show', $request) }}" class="btn btn-green-outline btn-sm w-100">
                                <i class="bi bi-eye"></i> View Details
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $requests->render() }}
        </div>
    @else
        <div class="empty-panel">
            <div>
                <div class="empty-icon"><i class="bi bi-inbox"></i></div>
                <h6 class="empty-title">No Service Requests Yet</h6>
                <p class="empty-text">Submitted service requests will appear here with their status, documents, and QR tracking token.</p>
                <a href="{{ route('citizen.services') }}" class="btn btn-green btn-sm">
                    <i class="bi bi-plus-circle"></i> Browse Services
                </a>
            </div>
        </div>
    @endif
</div>
@endsection
