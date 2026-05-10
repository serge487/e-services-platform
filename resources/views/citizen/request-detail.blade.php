@extends('layouts.public')
@section('title', 'Request #' . $serviceRequest->id)
@section('page-title', 'Request Details')

@push('styles')
<style>
:root {
    --g:       #0a5c4a;
    --g-dark:  #064e3b;
    --g-soft:  #ecfdf5;
    --g-border:#d9eee7;
    --muted:   #64748b;
    --ink:     #1f2937;
}

.detail-page .d-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    box-shadow: 0 1px 6px rgba(15,23,42,0.06);
    overflow: hidden;
    margin-bottom: 1.25rem;
}
.detail-page .d-card-head {
    padding: 0.85rem 1.25rem;
    border-bottom: 1px solid #f1f5f9;
    font-size: 0.72rem;
    font-weight: 800;
    letter-spacing: 0.6px;
    text-transform: uppercase;
    color: var(--g);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.detail-page .d-card-body { padding: 1.25rem; }

/* Field pairs */
.detail-page .field-lbl {
    display: block;
    font-size: 0.65rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    color: var(--muted);
    margin-bottom: 0.2rem;
}
.detail-page .field-val {
    font-size: 0.9rem;
    font-weight: 600;
    color: var(--ink);
    line-height: 1.4;
}
.detail-page .field-val.price { color: var(--g); }
.detail-page .field-val code  { color: var(--g); font-weight: 700; }

/* Status badge */
.detail-page .status-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.3rem 0.75rem;
    border-radius: 999px;
    font-size: 0.72rem;
    font-weight: 700;
    border: 1px solid transparent;
}
.detail-page .status-pill.pending    { background:#fff7ed; color:#c2410c; border-color:#fed7aa; }
.detail-page .status-pill.review     { background:#eff6ff; color:#1d4ed8; border-color:#bfdbfe; }
.detail-page .status-pill.approved   { background:var(--g-soft); color:var(--g); border-color:var(--g-border); }
.detail-page .status-pill.completed  { background:#f0fdf4; color:#15803d; border-color:#bbf7d0; }
.detail-page .status-pill.rejected   { background:#fef2f2; color:#b91c1c; border-color:#fecaca; }
.detail-page .status-pill.missing    { background:#fffbeb; color:#92400e; border-color:#fde68a; }

/* Metric boxes */
.detail-page .metric-box {
    background: #f8fafc;
    border: 1px solid #edf2f7;
    border-radius: 9px;
    padding: 0.85rem 1rem;
    height: 100%;
}

/* Document rows */
.detail-page .doc-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    border: 1px solid #edf2f7;
    border-radius: 8px;
    padding: 0.75rem;
    margin-bottom: 0.5rem;
}
.detail-page .doc-row:last-child { margin-bottom: 0; }
.detail-page .btn-dl {
    border: 1.5px solid var(--g);
    color: var(--g);
    border-radius: 7px;
    padding: 0.28rem 0.7rem;
    font-size: 0.75rem;
    font-weight: 700;
    text-decoration: none;
    white-space: nowrap;
    transition: background 0.15s, color 0.15s;
}
.detail-page .btn-dl:hover { background: var(--g); color: #fff; }

/* Payment panel */
.detail-page .pay-required {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    background: #fffbeb;
    border: 1px solid #fde68a;
    border-radius: 10px;
    padding: 1rem 1.1rem;
    margin-bottom: 0;
    flex-wrap: wrap;
}
.detail-page .pay-confirmed {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    background: var(--g-soft);
    border: 1px solid var(--g-border);
    border-radius: 10px;
    padding: 1rem 1.1rem;
}
.detail-page .btn-pay-now {
    background: #d97706;
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 0.5rem 1.25rem;
    font-size: 0.85rem;
    font-weight: 700;
    text-decoration: none;
    white-space: nowrap;
    transition: background 0.15s;
    flex-shrink: 0;
}
.detail-page .btn-pay-now:hover { background: #b45309; color: #fff; }

/* Office note */
.detail-page .note-box {
    background: var(--g-soft);
    border-left: 4px solid var(--g);
    border-radius: 0 8px 8px 0;
    padding: 0.85rem 1rem;
    color: #164e3f;
    font-size: 0.88rem;
    line-height: 1.5;
}

/* Timeline */
.detail-page .timeline-item {
    display: flex;
    gap: 1rem;
    padding-bottom: 1.25rem;
    position: relative;
}
.detail-page .timeline-item:not(:last-child)::before {
    content: '';
    position: absolute;
    left: 5px; top: 18px;
    width: 0;
    height: calc(100% - 4px);
    border-left: 2px solid #e2e8f0;
}
.detail-page .timeline-item.done:not(:last-child)::before {
    border-left-color: var(--g);
}
.detail-page .t-dot {
    width: 12px; height: 12px;
    border-radius: 50%;
    background: #e2e8f0;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #e2e8f0;
    flex-shrink: 0;
    margin-top: 4px;
}
.detail-page .timeline-item.done .t-dot {
    background: var(--g);
    box-shadow: 0 0 0 2px var(--g);
}

/* Token box */
.detail-page .token-box {
    background: var(--g-soft);
    border: 1px solid var(--g-border);
    border-radius: 8px;
    padding: 0.75rem;
    word-break: break-all;
    font-size: 0.78rem;
    color: var(--g);
    font-family: monospace;
}

/* Back link */
.detail-page .back-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    border: 1.5px solid var(--g);
    color: var(--g);
    border-radius: 8px;
    padding: 0.38rem 0.85rem;
    font-size: 0.82rem;
    font-weight: 700;
    text-decoration: none;
    margin-bottom: 1.25rem;
    transition: background 0.15s, color 0.15s;
}
.detail-page .back-btn:hover { background: var(--g); color: #fff; }
</style>
@endpush

@section('content')
@php
    $statusClass = match($serviceRequest->status) {
        'Pending'           => 'pending',
        'In Review'         => 'review',
        'Approved'          => 'approved',
        'Completed'         => 'completed',
        'Rejected'          => 'rejected',
        'Missing Documents' => 'missing',
        default             => 'review',
    };
@endphp

<div class="detail-page">

    <a href="{{ route('citizen.requests', absolute: false) }}" class="back-btn">
        <i class="bi bi-arrow-left"></i> Back to Requests
    </a>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-3">

        {{-- ── Main column ── --}}
        <div class="col-lg-8">

            {{-- Request info --}}
            <div class="d-card">
                <div class="d-card-head">
                    <i class="bi bi-file-earmark-text"></i> Request Information
                </div>
                <div class="d-card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-sm-6">
                            <span class="field-lbl">Service</span>
                            <div class="field-val">{{ $serviceRequest->service->name }}</div>
                        </div>
                        <div class="col-sm-6">
                            <span class="field-lbl">Office</span>
                            <div class="field-val">{{ $serviceRequest->service->office->name }}</div>
                        </div>
                        <div class="col-sm-3">
                            <span class="field-lbl">Request ID</span>
                            <div class="field-val"><code>#{{ $serviceRequest->id }}</code></div>
                        </div>
                        <div class="col-sm-3">
                            <span class="field-lbl">Status</span>
                            <span class="status-pill {{ $statusClass }}">
                                {{ $serviceRequest->citizenDisplayStatus() }}
                            </span>
                        </div>
                        <div class="col-sm-3">
                            <span class="field-lbl">Submitted</span>
                            <div class="field-val" style="font-size:0.82rem;">
                                {{ $serviceRequest->created_at->format('M d, Y') }}
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <span class="field-lbl">Last Updated</span>
                            <div class="field-val" style="font-size:0.82rem;">
                                {{ $serviceRequest->updated_at->format('M d, Y') }}
                            </div>
                        </div>
                    </div>

                    {{-- Service metrics --}}
                    <div class="row g-2">
                        <div class="col-4">
                            <div class="metric-box">
                                <span class="field-lbl">Service Fee</span>
                                <div class="field-val price fs-5">
                                    ${{ number_format($serviceRequest->service->price, 2) }}
                                </div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="metric-box">
                                <span class="field-lbl">Processing Time</span>
                                <div class="field-val">
                                    {{ $serviceRequest->service->duration_days }} day(s)
                                </div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="metric-box">
                                <span class="field-lbl">Category</span>
                                <div class="field-val">
                                    {{ $serviceRequest->service->category->name }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Payment --}}
            @if($serviceRequest->payment)
                @php $pay = $serviceRequest->payment; @endphp
                <div class="d-card">
                    <div class="d-card-head">
                        <i class="bi bi-credit-card"></i> Payment
                    </div>
                    <div class="d-card-body">
                        @if($pay->isPaid())
                            <div class="pay-confirmed">
                                <i class="bi bi-check-circle-fill text-success fs-4 flex-shrink-0"></i>
                                <div class="flex-grow-1">
                                    <div class="fw-bold" style="color:var(--g);">Payment Confirmed</div>
                                    <div class="small text-muted">
                                        {{ $pay->methodLabel() }}
                                        @if($pay->paid_at)
                                            · {{ $pay->paid_at->format('M d, Y \a\t g:i A') }}
                                        @endif
                                    </div>
                                </div>
                                <div class="fw-bold fs-5" style="color:var(--g);">
                                    ${{ number_format($pay->amount, 2) }}
                                </div>
                            </div>

                        @elseif($serviceRequest->status === 'In Review')
                            <div class="pay-required">
                                <div>
                                    <div class="fw-bold" style="color:#92400e;">
                                        <i class="bi bi-exclamation-circle me-1"></i>Payment Required
                                    </div>
                                    <div class="small text-muted mt-1">
                                        Amount due: <strong>${{ number_format($pay->amount, 2) }}</strong>
                                        @if($pay->whish_reference || $pay->transaction_reference)
                                            · Submitted — awaiting office verification.
                                        @else
                                            · Choose a payment method to proceed.
                                        @endif
                                    </div>
                                </div>
                                @if($pay->isPending() && ! $pay->whish_reference && ! $pay->transaction_reference)
                                    <a href="{{ route('citizen.service-requests.payment', $serviceRequest, absolute: false) }}"
                                       class="btn-pay-now">
                                        <i class="bi bi-credit-card me-1"></i>Pay Now
                                    </a>
                                @elseif($pay->isPending())
                                    <span class="badge bg-warning text-dark">
                                        <i class="bi bi-clock me-1"></i>Verifying
                                    </span>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Office notes --}}
            @if($serviceRequest->office_notes)
                <div class="d-card">
                    <div class="d-card-head">
                        <i class="bi bi-chat-dots"></i> Office Notes
                    </div>
                    <div class="d-card-body">
                        <div class="note-box">{{ $serviceRequest->office_notes }}</div>
                    </div>
                </div>
            @endif

            {{-- Citizen documents --}}
            @if($serviceRequest->requestDocuments->where('type', 'citizen_upload')->count() > 0)
                <div class="d-card">
                    <div class="d-card-head">
                        <i class="bi bi-cloud-upload"></i> Your Submitted Documents
                    </div>
                    <div class="d-card-body">
                        @foreach($serviceRequest->requestDocuments->where('type', 'citizen_upload') as $doc)
                            <div class="doc-row">
                                <div class="d-flex align-items-center gap-2 min-width-0 flex-grow-1">
                                    <i class="bi bi-file-earmark fs-5 flex-shrink-0"
                                       style="color:var(--g);"></i>
                                    <div class="min-width-0">
                                        <div class="field-val text-truncate">
                                            {{ basename($doc->file_path) }}
                                        </div>
                                        <div class="small text-muted">
                                            {{ $doc->created_at->diffForHumans() }}
                                        </div>
                                    </div>
                                </div>
                                <a href="{{ route('citizen.service-requests.download', [$serviceRequest, $doc], absolute: false) }}"
                                   class="btn-dl">
                                    <i class="bi bi-download me-1"></i>Download
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Official response documents --}}
            @if($serviceRequest->requestDocuments->where('type', 'official_response')->count() > 0)
                <div class="d-card">
                    <div class="d-card-head">
                        <i class="bi bi-cloud-download"></i> Official Response Documents
                    </div>
                    <div class="d-card-body">
                        @foreach($serviceRequest->requestDocuments->where('type', 'official_response') as $doc)
                            <div class="doc-row">
                                <div class="d-flex align-items-center gap-2 flex-grow-1">
                                    <i class="bi bi-file-earmark-pdf text-danger fs-5 flex-shrink-0"></i>
                                    <div>
                                        <div class="field-val">{{ basename($doc->file_path) }}</div>
                                        <div class="small text-muted">
                                            Received {{ $doc->created_at->diffForHumans() }}
                                        </div>
                                    </div>
                                </div>
                                <a href="{{ route('citizen.service-requests.download', [$serviceRequest, $doc], absolute: false) }}"
                                   class="btn-dl">
                                    <i class="bi bi-download me-1"></i>Download
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>

        {{-- ── Sidebar ── --}}
        <div class="col-lg-4">

            {{-- Timeline --}}
            <div class="d-card">
                <div class="d-card-head">
                    <i class="bi bi-list-check"></i> Status Timeline
                </div>
                <div class="d-card-body">
                    <div>
                        @php
                            $steps = [
                                ['label' => 'Submitted',    'sub' => $serviceRequest->created_at->format('M d, Y'),
                                 'done'  => true],
                                ['label' => 'Under Review', 'sub' => $serviceRequest->isAccepted() ? 'Accepted by office' : 'Awaiting officer',
                                 'done'  => in_array($serviceRequest->status, ['In Review','Missing Documents','Approved','Rejected','Completed'])],
                                ['label' => 'Payment',      'sub' => $serviceRequest->isPaid() ? 'Payment confirmed' : 'Pending payment',
                                 'done'  => $serviceRequest->isPaid()],
                                ['label' => 'Approved',     'sub' => 'Request approved',
                                 'done'  => in_array($serviceRequest->status, ['Approved','Completed'])],
                                ['label' => 'Completed',    'sub' => 'Service ready for pickup',
                                 'done'  => $serviceRequest->status === 'Completed'],
                            ];
                        @endphp
                        @foreach($steps as $step)
                            <div class="timeline-item {{ $step['done'] ? 'done' : '' }}">
                                <div class="t-dot"></div>
                                <div>
                                    <div class="fw-semibold small" style="color:{{ $step['done'] ? 'var(--g)' : 'var(--muted)' }};">
                                        {{ $step['label'] }}
                                    </div>
                                    <div class="text-muted" style="font-size:0.75rem;">{{ $step['sub'] }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- QR Tracking --}}
            <div class="d-card">
                <div class="d-card-head">
                    <i class="bi bi-qr-code"></i> Track Request
                </div>
                <div class="d-card-body text-center">
                    <p class="small text-muted mb-3">
                        Scan this QR code to track your request status:
                    </p>
                    <div id="qrcode" style="display:inline-block; padding:8px; border:1px solid var(--g-border); border-radius:10px; background:#fff;" class="mb-3"></div>
                    <div class="text-muted small mb-2">Or use this token:</div>
                    <div class="token-box">{{ $serviceRequest->qr_code_token }}</div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const token  = "{{ $serviceRequest->qr_code_token }}";
    const scanUrl = "{{ route('qr.scan', ['token' => '__T__'], absolute: true) }}".replace('__T__', token);
    new QRCode(document.getElementById('qrcode'), {
        text: scanUrl, width: 140, height: 140,
        colorDark: '#000', colorLight: '#fff',
        correctLevel: QRCode.CorrectLevel.H
    });
});

@if(auth()->check())
if (typeof window.Echo !== 'undefined') {
    window.Echo.private('service-request.{{ $serviceRequest->id }}')
        .listen('service-request-status-changed', () => {
            setTimeout(() => location.reload(), 1500);
        });
}
@endif
</script>
@endpush