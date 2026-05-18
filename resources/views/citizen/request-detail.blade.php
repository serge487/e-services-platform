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

/* Feedback */
.detail-page .star-rating {
    display: flex;
    flex-direction: row-reverse;
    justify-content: flex-end;
    gap: 0.15rem;
}
.detail-page .star-rating input { display: none; }
.detail-page .star-rating label {
    cursor: pointer;
    font-size: 1.75rem;
    color: #d1d5db;
    transition: color 0.1s;
}
.detail-page .star-rating label:hover,
.detail-page .star-rating label:hover ~ label,
.detail-page .star-rating input:checked ~ label {
    color: #f59e0b;
}
.detail-page .feedback-submitted {
    background: var(--g-soft);
    border: 1px solid var(--g-border);
    border-radius: 10px;
    padding: 1rem 1.1rem;
}
.detail-page .btn-submit-feedback {
    background: var(--g);
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 0.5rem 1.25rem;
    font-size: 0.85rem;
    font-weight: 700;
    transition: background 0.15s;
}
.detail-page .btn-submit-feedback:hover { background: var(--g-dark); color: #fff; }

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
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-3">
            <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($serviceRequest->isPaid())
        @if($serviceRequest->feedback?->office_response)
            <div class="alert alert-success alert-dismissible fade show mb-3">
                <i class="bi bi-reply-fill me-2"></i>
                <strong>Municipality replied to your review.</strong>
                <a href="#citizen-feedback-card" class="alert-link ms-1">Read the reply below</a>.
            </div>
        @elseif($serviceRequest->canLeaveFeedback())
            <div class="alert alert-warning alert-dismissible fade show mb-3">
                <i class="bi bi-star me-2"></i>
                <strong>Payment confirmed — you can rate this service.</strong>
                <a href="#citizen-feedback-card" class="alert-link ms-1">Leave your review below</a>.
            </div>
        @elseif($serviceRequest->feedback)
            <div class="alert alert-info alert-dismissible fade show mb-3">
                <i class="bi bi-check-circle me-2"></i>
                You submitted a review for this request.
                <a href="#citizen-feedback-card" class="alert-link ms-1">View your review</a>.
            </div>
        @endif
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
    <div class="d-flex align-items-center gap-2">
        <div class="fw-bold fs-5" style="color:var(--g);">
            ${{ number_format($pay->amount, 2) }}
        </div>
        <a href="{{ route('citizen.service-requests.invoice', $serviceRequest, absolute: false) }}"
           class="btn-dl" title="Download Invoice">
            <i class="bi bi-receipt me-1"></i>Invoice
        </a>
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

            {{-- Rate & review (after payment confirmed) --}}
            @if($serviceRequest->isPaid())
                <div class="d-card" id="citizen-feedback-card">
                    <div class="d-card-head">
                        <i class="bi bi-star"></i> Rate This Service
                    </div>
                    <div class="d-card-body">
                        @if($serviceRequest->feedback)
                            <div class="feedback-submitted">
                                <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap">
                                    <div>
                                        <div class="fw-bold" style="color:var(--g);">Your review</div>
                                        <div class="text-warning mt-1">
                                            @for($i = 0; $i < $serviceRequest->feedback->rating; $i++)
                                                <i class="bi bi-star-fill"></i>
                                            @endfor
                                        </div>
                                    </div>
                                    <small class="text-muted">
                                        {{ $serviceRequest->feedback->created_at->format('M d, Y') }}
                                    </small>
                                </div>
                                <p class="mb-0 mt-2">{{ $serviceRequest->feedback->citizen_comment }}</p>
                                @if($serviceRequest->feedback->is_private)
                                    <small class="text-muted d-block mt-2">
                                        <i class="bi bi-lock me-1"></i>Private review — visible in the municipality portal only (not on the public service page).
                                    </small>
                                @else
                                    <small class="text-muted d-block mt-2">
                                        <i class="bi bi-globe me-1"></i>Public review — visible on the service page.
                                    </small>
                                @endif

                                <div id="municipality-reply-slot">
                                @if($serviceRequest->feedback->office_response)
                                    <div class="mt-3 p-3 rounded municipality-reply-block" style="background:#f8fafc;border-left:4px solid var(--g);">
                                        <div class="fw-bold small mb-1" style="color:var(--g);">
                                            <i class="bi bi-reply me-1"></i>Municipality reply
                                        </div>
                                        <p class="mb-0 small">{{ $serviceRequest->feedback->office_response }}</p>
                                        @if($serviceRequest->feedback->office_response_is_private)
                                            <small class="text-muted d-block mt-2">
                                                <i class="bi bi-lock me-1"></i>Private reply — only you can see this message.
                                            </small>
                                        @else
                                            <small class="text-muted d-block mt-2">
                                                <i class="bi bi-globe me-1"></i>Public reply — also shown on the service page.
                                            </small>
                                        @endif
                                    </div>
                                @endif
                                </div>
                            </div>
                        @elseif($serviceRequest->canLeaveFeedback())
                            <p class="text-muted small mb-3">
                                Payment is confirmed. Share your experience to help others choose this service.
                            </p>
                            <form method="POST"
                                  action="{{ route('citizen.service-requests.feedback.store', $serviceRequest, absolute: false) }}">
                                @csrf
                                <div class="mb-3">
                                    <label class="field-lbl mb-2">Rating</label>
                                    <div class="star-rating">
                                        @for($star = 5; $star >= 1; $star--)
                                            <input type="radio"
                                                   name="rating"
                                                   id="rating-{{ $star }}"
                                                   value="{{ $star }}"
                                                   {{ (int) old('rating') === $star ? 'checked' : '' }}
                                                   required>
                                            <label for="rating-{{ $star }}" title="{{ $star }} stars">
                                                <i class="bi bi-star-fill"></i>
                                            </label>
                                        @endfor
                                    </div>
                                    @error('rating')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="citizen_comment" class="field-lbl mb-2">Your review</label>
                                    <textarea name="citizen_comment"
                                              id="citizen_comment"
                                              class="form-control @error('citizen_comment') is-invalid @enderror"
                                              rows="4"
                                              maxlength="2000"
                                              placeholder="Describe your experience with this office service (min. 10 characters)..."
                                              required>{{ old('citizen_comment') }}</textarea>
                                    @error('citizen_comment')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input"
                                           type="checkbox"
                                           name="is_private"
                                           id="is_private"
                                           value="1"
                                           {{ old('is_private') ? 'checked' : '' }}>
                                    <label class="form-check-label small" for="is_private">
                                        Private review (municipality portal only — not on the public service page)
                                    </label>
                                </div>
                                <button type="submit" class="btn-submit-feedback">
                                    <i class="bi bi-send me-1"></i>Submit Review
                                </button>
                            </form>
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
            @php
                $qrTrackingUrl = \App\Support\QrCodeSvg::trackingUrl($serviceRequest->qr_code_token);
                $qrCodeSvg = \App\Support\QrCodeSvg::render($qrTrackingUrl, 140);
            @endphp
            <div class="d-card">
                <div class="d-card-head">
                    <i class="bi bi-qr-code"></i> Track Request
                </div>
                <div class="d-card-body text-center">
                    <p class="small text-muted mb-3">
                        Scan this QR code to track your request status:
                    </p>
                    <div style="display:inline-block; padding:8px; border:1px solid var(--g-border); border-radius:10px; background:#fff; line-height:0;" class="mb-3">
                        {!! $qrCodeSvg !!}
                    </div>
                    <div class="text-muted small mb-2" style="word-break:break-all;">{{ $qrTrackingUrl }}</div>
                    <div class="text-muted small mb-2">Or use this token:</div>
                    <div class="token-box">{{ $serviceRequest->qr_code_token }}</div>
                </div>
            </div>

       {{-- Official Response Documents --}}
            @php $officialDocs = $serviceRequest->requestDocuments->where('type', 'official_response'); @endphp
            <div class="d-card" id="official-docs-card">
                <div class="d-card-head">
                    <i class="bi bi-cloud-download"></i> Response Documents
                </div>
                <div class="d-card-body" id="official-docs-list">
                    @forelse($officialDocs as $doc)
                        <div class="doc-row">
                            <div class="d-flex align-items-center gap-2 flex-grow-1">
                                <i class="bi bi-file-earmark-pdf text-danger fs-5 flex-shrink-0"></i>
                                <div>
                                    <div class="field-val" style="font-size:0.82rem;">{{ basename($doc->file_path) }}</div>
                                    <div class="small text-muted">Received {{ $doc->created_at->diffForHumans() }}</div>
                                </div>
                            </div>
                            <a href="{{ route('citizen.service-requests.download', [$serviceRequest, $doc], absolute: false) }}" class="btn-dl">
                                <i class="bi bi-download me-1"></i>Download
                            </a>
                        </div>
                    @empty
                        <p class="text-muted small mb-0">
                            <i class="bi bi-hourglass me-1"></i>No documents yet — the office will upload them here.
                        </p>
                    @endforelse
                </div>
            </div>

            {{-- Invoice --}}
            @if($serviceRequest->payment && $serviceRequest->payment->isPaid())
                <div class="d-card">
                    <div class="d-card-head">
                        <i class="bi bi-receipt"></i> Invoice
                    </div>
                    <div class="d-card-body">
                        <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                            <div>
                                <div class="field-val" style="color:var(--g);">
                                    ${{ number_format($serviceRequest->payment->amount, 2) }} {{ $serviceRequest->payment->currency }}
                                </div>
                                <div class="small text-muted">
                                    {{ $serviceRequest->payment->methodLabel() }}
                                    @if($serviceRequest->payment->paid_at)
                                        · {{ $serviceRequest->payment->paid_at->format('M d, Y') }}
                                    @endif
                                </div>
                            </div>
                            <span class="badge" style="background:var(--g-soft);color:var(--g);border:1px solid var(--g-border);font-size:0.7rem;">PAID</span>
                        </div>
                        <a href="{{ route('citizen.service-requests.invoice', $serviceRequest, absolute: false) }}"
                           class="btn-dl d-flex align-items-center justify-content-center gap-1 w-100"
                           style="padding:0.45rem;">
                            <i class="bi bi-download"></i> Download Invoice (PDF)
                        </a>
                    </div>
                </div>
            @endif

        </div> {{-- closes col-lg-4 --}}
    </div>     {{-- closes row --}}
</div>         {{-- closes detail-page --}}

@endsection

@push('scripts')
<script>
@if(auth()->check())
if (typeof window.Echo !== 'undefined') {
    const requestId = {{ $serviceRequest->id }};
    const citizenUserId = {{ auth()->id() }};

    function injectMunicipalityReply(payload) {
        if (Number(payload.service_request_id) !== requestId) {
            return;
        }
        const slot = document.getElementById('municipality-reply-slot');
        if (!slot || slot.querySelector('.municipality-reply-block')) {
            return;
        }
        const isPrivate = Boolean(payload.office_response_is_private);
        const visibility = isPrivate
            ? '<small class="text-muted d-block mt-2"><i class="bi bi-lock me-1"></i>Private reply — only you can see this message.</small>'
            : '<small class="text-muted d-block mt-2"><i class="bi bi-globe me-1"></i>Public reply — also shown on the service page.</small>';
        slot.innerHTML = `<div class="mt-3 p-3 rounded municipality-reply-block" style="background:#f8fafc;border-left:4px solid var(--g);">
            <div class="fw-bold small mb-1" style="color:var(--g);"><i class="bi bi-reply me-1"></i>Municipality reply</div>
            <p class="mb-0 small">${escapeHtml(String(payload.office_response || ''))}</p>
            ${visibility}
        </div>`;
        let alert = document.getElementById('feedback-reply-live-alert');
        if (!alert) {
            alert = document.createElement('div');
            alert.id = 'feedback-reply-live-alert';
            alert.className = 'alert alert-success alert-dismissible fade show mb-3';
            alert.innerHTML = '<i class="bi bi-reply-fill me-2"></i><strong>New municipality reply</strong> — shown below.';
            const page = document.querySelector('.detail-page');
            const row = page?.querySelector('.row.g-3');
            if (row) {
                page.insertBefore(alert, row);
            }
        }
    }

    function escapeHtml(text) {
        const d = document.createElement('div');
        d.textContent = text;
        return d.innerHTML;
    }

    window.Echo.private('App.Models.User.' + citizenUserId)
        .listen('.feedback.municipality-replied', injectMunicipalityReply);

   window.Echo.private('service-request.' + requestId)
    .listen('.feedback.municipality-replied', injectMunicipalityReply)
    .listen('.service-request-status-changed', () => {
        setTimeout(() => location.reload(), 1500);
    })
    .listen('.document.uploaded', (payload) => {
        injectOfficialDocument(payload);
    });

    function injectOfficialDocument(payload) {
    // Show the official docs card if it doesn't exist yet
    let card = document.getElementById('official-docs-card');
    if (!card) {
        const mainCol = document.querySelector('.col-lg-8');
        card = document.createElement('div');
        card.id = 'official-docs-card';
        card.className = 'd-card';
        card.innerHTML = `
            <div class="d-card-head">
                <i class="bi bi-cloud-download"></i> Official Response Documents
            </div>
            <div class="d-card-body" id="official-docs-list"></div>`;
        mainCol.appendChild(card);
    }

    const list = document.getElementById('official-docs-list');
    const filename = payload.filename || 'Response Document';
    const downloadUrl = payload.download_url;

    const row = document.createElement('div');
    row.className = 'doc-row';
    row.innerHTML = `
        <div class="d-flex align-items-center gap-2 flex-grow-1">
            <i class="bi bi-file-earmark-pdf text-danger fs-5 flex-shrink-0"></i>
            <div>
                <div class="field-val">${escapeHtml(filename)}</div>
                <div class="small text-muted">Just received</div>
            </div>
        </div>
        <a href="${downloadUrl}" class="btn-dl">
            <i class="bi bi-download me-1"></i>Download
        </a>`;
    list.appendChild(row);

    // Flash a top alert
    const page = document.querySelector('.detail-page');
    const existingAlert = document.getElementById('doc-live-alert');
    if (!existingAlert) {
        const alert = document.createElement('div');
        alert.id = 'doc-live-alert';
        alert.className = 'alert alert-success alert-dismissible fade show mb-3';
        alert.innerHTML = `<i class="bi bi-cloud-download me-2"></i><strong>New document received</strong> from the office — available below. <button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
        const row = page?.querySelector('.row.g-3');
        if (row) page.insertBefore(alert, row);
    }
}
}
@endif
</script>
@endpush
