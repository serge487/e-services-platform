@extends('layouts.public')
@section('title', 'My Requests')
@section('page-title', 'My Service Requests')

@push('styles')
<style>
:root {
    --g:      #0a5c4a;
    --g-dark: #064e3b;
    --g-soft: #ecfdf5;
    --g-border: #d9eee7;
    --muted:  #64748b;
    --ink:    #1f2937;
}

.requests-page .req-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    box-shadow: 0 1px 6px rgba(15,23,42,0.06);
    height: 100%;
    transition: border-color 0.15s, box-shadow 0.15s, transform 0.12s;
    overflow: hidden;
}
.requests-page .req-card:hover {
    border-color: var(--g-border);
    box-shadow: 0 4px 16px rgba(10,92,74,0.1);
    transform: translateY(-2px);
}

/* Card header strip */
.requests-page .req-card-header {
    padding: 1rem 1.1rem 0.75rem;
    border-bottom: 1px solid #f1f5f9;
}
.requests-page .req-title {
    font-size: 0.95rem;
    font-weight: 700;
    color: var(--ink);
    margin: 0 0 0.15rem;
    line-height: 1.3;
}
.requests-page .req-office {
    font-size: 0.78rem;
    color: var(--muted);
}

/* Status badge */
.requests-page .status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.28rem 0.65rem;
    border-radius: 999px;
    font-size: 0.7rem;
    font-weight: 700;
    white-space: nowrap;
    border: 1px solid transparent;
}
.requests-page .status-badge.pending    { background:#fff7ed; color:#c2410c; border-color:#fed7aa; }
.requests-page .status-badge.review     { background:#eff6ff; color:#1d4ed8; border-color:#bfdbfe; }
.requests-page .status-badge.approved   { background:var(--g-soft); color:var(--g); border-color:var(--g-border); }
.requests-page .status-badge.completed  { background:#f0fdf4; color:#15803d; border-color:#bbf7d0; }
.requests-page .status-badge.rejected   { background:#fef2f2; color:#b91c1c; border-color:#fecaca; }
.requests-page .status-badge.missing    { background:#fffbeb; color:#92400e; border-color:#fde68a; }

/* Meta grid */
.requests-page .req-meta {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.6rem 0.5rem;
    padding: 0.85rem 1.1rem;
    border-bottom: 1px solid #f1f5f9;
}
.requests-page .meta-lbl {
    font-size: 0.65rem;
    font-weight: 700;
    color: var(--muted);
    text-transform: uppercase;
    letter-spacing: 0.4px;
    display: block;
    margin-bottom: 0.15rem;
}
.requests-page .meta-val {
    font-size: 0.84rem;
    font-weight: 600;
    color: var(--ink);
}
.requests-page .meta-val.price { color: var(--g); }
.requests-page .meta-val code  { color: var(--g); font-weight: 700; }

/* Card body */
.requests-page .req-card-body { padding: 0.85rem 1.1rem; }

/* Payment banner */
.requests-page .pay-banner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    background: #fffbeb;
    border: 1px solid #fde68a;
    border-radius: 9px;
    padding: 0.65rem 0.9rem;
    margin-bottom: 0.75rem;
}
.requests-page .pay-banner .pay-amount {
    font-weight: 700;
    font-size: 0.88rem;
    color: #92400e;
}
.requests-page .pay-banner .btn-pay-now {
    background: #d97706;
    color: #fff;
    border: none;
    border-radius: 7px;
    padding: 0.32rem 0.85rem;
    font-size: 0.78rem;
    font-weight: 700;
    white-space: nowrap;
    text-decoration: none;
    transition: background 0.15s;
}
.requests-page .pay-banner .btn-pay-now:hover { background: #b45309; color: #fff; }

.requests-page .paid-badge {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    background: var(--g-soft);
    border: 1px solid var(--g-border);
    border-radius: 9px;
    padding: 0.5rem 0.9rem;
    font-size: 0.78rem;
    font-weight: 700;
    color: var(--g);
    margin-bottom: 0.75rem;
}

/* Office note */
.requests-page .office-note {
    background: #f8fafc;
    border-left: 3px solid var(--g);
    border-radius: 0 7px 7px 0;
    padding: 0.55rem 0.75rem;
    font-size: 0.8rem;
    color: #334155;
    margin-bottom: 0.75rem;
}

/* Documents */
.requests-page .docs-panel {
    background: #f8fafc;
    border: 1px solid #edf2f7;
    border-radius: 8px;
    padding: 0.65rem 0.8rem;
    margin-bottom: 0.75rem;
}
.requests-page .docs-title {
    font-size: 0.68rem;
    font-weight: 700;
    color: var(--g);
    text-transform: uppercase;
    letter-spacing: 0.4px;
    margin-bottom: 0.4rem;
}
.requests-page .doc-link {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.78rem;
    color: #475569;
    text-decoration: none;
    padding: 0.25rem 0.35rem;
    border-radius: 5px;
    transition: background 0.12s, color 0.12s;
}
.requests-page .doc-link:hover { background: var(--g-soft); color: var(--g); }

/* Actions */
.requests-page .btn-view {
    display: block;
    text-align: center;
    border: 1.5px solid var(--g);
    color: var(--g);
    border-radius: 8px;
    padding: 0.45rem 0.75rem;
    font-size: 0.82rem;
    font-weight: 700;
    text-decoration: none;
    transition: background 0.15s, color 0.15s;
}
.requests-page .btn-view:hover { background: var(--g); color: #fff; }

/* Empty state */
.requests-page .empty-wrap {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 3.5rem 1rem;
    text-align: center;
    box-shadow: 0 1px 6px rgba(15,23,42,0.05);
}
.requests-page .empty-icon {
    width: 56px; height: 56px;
    background: var(--g-soft);
    border: 1px solid var(--g-border);
    border-radius: 14px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: var(--g);
    margin-bottom: 1rem;
}
.requests-page .btn-browse {
    background: var(--g);
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 0.5rem 1.25rem;
    font-size: 0.85rem;
    font-weight: 700;
    text-decoration: none;
    transition: background 0.15s;
}
.requests-page .btn-browse:hover { background: var(--g-dark); color: #fff; }

/* Pagination */
.requests-page .page-link { color: var(--g); border-color: #dbe7e2; }
.requests-page .active .page-link { background: var(--g); border-color: var(--g); color: #fff; }
</style>
@endpush

@section('content')
<div class="requests-page">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($requests->count() > 0)
        <div class="row g-3">
            @foreach($requests as $req)
                @php
                    $statusClass = match($req->status) {
                        'Pending'           => 'pending',
                        'In Review'         => 'review',
                        'Approved'          => 'approved',
                        'Completed'         => 'completed',
                        'Rejected'          => 'rejected',
                        'Missing Documents' => 'missing',
                        default             => 'review',
                    };
                    $statusDot = match($req->status) {
                        'Pending'           => 'bi-circle-fill',
                        'In Review'         => 'bi-arrow-repeat',
                        'Approved'          => 'bi-check-circle-fill',
                        'Completed'         => 'bi-check2-all',
                        'Rejected'          => 'bi-x-circle-fill',
                        'Missing Documents' => 'bi-exclamation-circle-fill',
                        default             => 'bi-circle',
                    };
                    $downloadableDocs = $req->requestDocuments->whereIn('type', ['citizen_upload', 'official_response']);
                @endphp

                <div class="col-lg-6">
                    <div class="req-card">

                        {{-- Header --}}
                        <div class="req-card-header d-flex align-items-start justify-content-between gap-2">
                            <div class="flex-grow-1 min-width-0">
                                <div class="req-title">{{ $req->service->name }}</div>
                                <div class="req-office">
                                    <i class="bi bi-building me-1"></i>{{ $req->service->office->name }}
                                </div>
                            </div>
                            <span class="status-badge {{ $statusClass }} flex-shrink-0">
                                <i class="bi {{ $statusDot }}" style="font-size:0.6rem;"></i>
                                {{ $req->citizenDisplayStatus() }}
                            </span>
                        </div>

                        {{-- Meta --}}
                        <div class="req-meta">
                            <div>
                                <span class="meta-lbl">Request ID</span>
                                <span class="meta-val"><code>#{{ $req->id }}</code></span>
                            </div>
                            <div>
                                <span class="meta-lbl">Submitted</span>
                                <span class="meta-val">{{ $req->created_at->format('M d, Y') }}</span>
                            </div>
                            <div>
                                <span class="meta-lbl">Service Fee</span>
                                <span class="meta-val price">${{ number_format($req->service->price, 2) }}</span>
                            </div>
                            <div>
                                <span class="meta-lbl">Processing</span>
                                <span class="meta-val">{{ $req->service->duration_days }} day(s)</span>
                            </div>
                        </div>

                        {{-- Card body --}}
                        <div class="req-card-body">

                            {{-- Payment banner --}}
                            @if($req->payment && $req->payment->isPending() && $req->status === 'In Review')
                                <div class="pay-banner">
                                    <div>
                                        <div class="pay-amount">
                                            <i class="bi bi-exclamation-circle me-1"></i>
                                            Payment required
                                        </div>
                                        <div style="font-size:0.72rem;color:#92400e;">
                                            ${{ number_format($req->payment->amount, 2) }} due
                                        </div>
                                    </div>
                                    <a href="{{ route('citizen.service-requests.payment', $req, absolute: false) }}"
                                       class="btn-pay-now">
                                        <i class="bi bi-credit-card me-1"></i>Pay Now
                                    </a>
                                </div>
                            @elseif($req->payment && $req->payment->isPaid())
                                <div class="paid-badge">
                                    <i class="bi bi-check-circle-fill"></i>
                                    Payment confirmed — {{ $req->payment->methodLabel() }}
                                    @if($req->payment->paid_at)
                                        <span class="ms-auto text-muted fw-normal" style="font-size:0.72rem;">
                                            {{ $req->payment->paid_at->format('M d, Y') }}
                                        </span>
                                    @endif
                                </div>
                            @endif

                            {{-- Office notes --}}
                            @if($req->office_notes)
                                <div class="office-note">
                                    <i class="bi bi-chat-dots me-1"></i>
                                    <strong>Office note:</strong> {{ Str::limit($req->office_notes, 80) }}
                                </div>
                            @endif

                            {{-- Documents --}}
                            @if($downloadableDocs->count() > 0)
                                <div class="docs-panel">
                                    <div class="docs-title">
                                        <i class="bi bi-paperclip me-1"></i>
                                        {{ $downloadableDocs->count() }} document(s)
                                    </div>
                                    @foreach($downloadableDocs->take(3) as $doc)
                                        <a href="{{ route('citizen.service-requests.download', [$req, $doc]) }}"
                                           class="doc-link">
                                            <i class="bi bi-file-earmark-pdf text-danger"></i>
                                            {{ Str::limit(basename($doc->file_path), 35) }}
                                        </a>
                                    @endforeach
                                    @if($downloadableDocs->count() > 3)
                                        <div class="text-muted mt-1" style="font-size:0.72rem; padding-left:0.35rem;">
                                            +{{ $downloadableDocs->count() - 3 }} more — view details
                                        </div>
                                    @endif
                                </div>
                            @endif

                            {{-- View details --}}
                            <a href="{{ route('citizen.service-requests.show', $req, absolute: false) }}"
                               class="btn-view">
                                <i class="bi bi-eye me-1"></i>View Details
                            </a>

                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $requests->withQueryString()->links() }}
        </div>

    @else
        <div class="empty-wrap">
            <div class="empty-icon"><i class="bi bi-inbox"></i></div>
            <h6 class="fw-bold mb-1" style="color:var(--ink);">No Service Requests Yet</h6>
            <p class="text-muted small mb-3">
                Once you submit a service request it will appear here with its status,
                documents and payment info.
            </p>
            <a href="{{ route('portal', absolute: false) }}" class="btn-browse">
                <i class="bi bi-building me-1"></i>Browse Offices
            </a>
        </div>
    @endif

</div>
@endsection