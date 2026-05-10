@extends('municipality.layouts.app')
@section('title', 'Request #' . $serviceRequest->id)
@section('page-title', 'Request Details')

@section('content')

<div class="mb-1">
    <a href="{{ route('municipality.requests', absolute: false) }}" class="text-decoration-none text-muted" style="font-size:0.85rem;">
        <i class="bi bi-arrow-left me-1"></i>Back
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" style="padding:0.5rem; margin-bottom:0.5rem; font-size:0.85rem;">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" style="padding:0.5rem; margin-bottom:0.5rem; font-size:0.85rem;">
        <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" style="padding:0.5rem; margin-bottom:0.5rem; font-size:0.85rem;">
        @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<style>
    .compact-cell { display: inline-block; padding: 0.3rem 0.5rem; border-right: 1px solid #eee; }
    .compact-cell:last-child { border-right: 0; }
    .info-row { display: flex; flex-wrap: wrap; font-size: 0.8rem; padding: 0.3rem 0; border-bottom: 1px solid #f0f0f0; }
    .info-label { font-weight: 600; color: #666; text-transform: uppercase; font-size: 0.65rem; width: 80px; }
    .info-val { flex: 1; }
</style>

{{-- REQUEST INFO --}}
<div class="card border-0 shadow-sm" style="margin-bottom:0.5rem;">
    <div class="card-header bg-light py-1" style="padding: 0.3rem 0.5rem;">
        <small class="fw-bold">Request #{{ $serviceRequest->id }} | 
        @php
            $badgeColor = match($serviceRequest->status) {
                'Pending'           => 'warning text-dark',
                'In Review'         => 'info text-dark',
                'Missing Documents' => 'secondary',
                'Approved'          => 'success',
                'Rejected'          => 'danger',
                'Completed'         => 'primary',
                default             => 'secondary',
            };
        @endphp
        <span class="badge bg-{{ $badgeColor }}" style="font-size:0.7rem;">{{ $serviceRequest->status }}</span>
        </small>
    </div>
    <div class="card-body" style="padding: 0.4rem;">
        <div class="info-row"><span class="info-label">Citizen:</span><span class="info-val"><strong>{{ $serviceRequest->citizen->name }}</strong> ({{ $serviceRequest->citizen->email }})</span></div>
        <div class="info-row"><span class="info-label">Service:</span><span class="info-val"><strong>{{ $serviceRequest->service->name }}</strong> - {{ $serviceRequest->service->category->name }}</span></div>
        <div class="info-row"><span class="info-label">Office:</span><span class="info-val">{{ $serviceRequest->service->office->name }}</span></div>
        <div class="info-row"><span class="info-label">Submitted:</span><span class="info-val">{{ $serviceRequest->created_at->format('d M Y H:i') }}</span></div>
        @if($serviceRequest->isAccepted())
            <div class="info-row"><span class="info-label">Taken By:</span><span class="info-val">{{ $serviceRequest->acceptedBy?->name ?? 'Staff' }} - {{ $serviceRequest->accepted_at->format('d M Y H:i') }}</span></div>
        @endif
        <div class="info-row" style="border-bottom:0;"><span class="info-label">QR:</span><span class="info-val"><div id="qrcode" style="display: inline-block; padding: 1px; border: 1px solid #ddd; line-height:0;"></div></span></div>
    </div>
</div>

{{-- ACTIONS --}}
@if($serviceRequest->status === 'Pending')
    <div class="card border-0 shadow-sm border-start border-success border-4" style="margin-bottom:0.5rem;">
        <div class="card-body" style="padding:0.4rem;">
            <form method="POST" action="{{ route('municipality.requests.accept', $serviceRequest, absolute: false) }}" style="display: inline;">
                @csrf
                <button type="submit" class="btn btn-success btn-sm" style="padding:0.2rem 0.5rem; font-size:0.75rem;">
                    <i class="bi bi-check-circle me-1"></i>Mark as Taken
                </button>
            </form>
        </div>
    </div>
@elseif($serviceRequest->status !== 'Completed')
    <div class="card border-0 shadow-sm" style="margin-bottom:0.5rem;">
        <div class="card-header bg-light py-1" style="padding: 0.3rem 0.5rem;">
            <small class="fw-bold">Update Status</small>
        </div>
        <div class="card-body" style="padding:0.4rem;">
            <form method="POST" action="{{ route('municipality.requests.update-status', $serviceRequest, absolute: false) }}">
                @csrf @method('PATCH')
                <div style="margin-bottom:0.3rem;">
                    <select id="request_status" name="status" class="form-select form-select-sm" style="font-size:0.75rem;" required>
                        @foreach(['In Review', 'Missing Documents', 'Approved', 'Rejected', 'Completed'] as $status)
                            <option value="{{ $status }}" {{ $serviceRequest->status === $status ? 'selected' : '' }}>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="margin-bottom:0.3rem;">
                    <textarea name="office_notes" rows="1" class="form-control form-control-sm" style="font-size:0.75rem;" placeholder="Notes...">{{ old('office_notes', $serviceRequest->office_notes) }}</textarea>
                </div>
                <button type="submit" class="btn btn-primary btn-sm" style="padding:0.2rem 0.5rem; font-size:0.75rem;">
                    <i class="bi bi-check me-1"></i>Update
                </button>
            </form>
        </div>
    </div>
@endif
{{-- Payment Status (municipality view) --}}
@if($serviceRequest->payment)
    @php $payment = $serviceRequest->payment; @endphp
    <div class="card border-0 shadow-sm" style="margin-bottom:0.5rem;">
        <div class="card-header bg-light py-1" style="padding: 0.3rem 0.5rem;">
            <small class="fw-bold">
                <i class="bi bi-credit-card me-1"></i>Payment
                @if($payment->isPaid())
                    <span class="badge bg-success ms-1" style="font-size:0.65rem;">Paid</span>
                @else
                    <span class="badge bg-warning text-dark ms-1" style="font-size:0.65rem;">Pending</span>
                @endif
            </small>
        </div>
        <div class="card-body" style="padding:0.4rem; font-size:0.78rem;">

            <div class="info-row">
                <span class="info-label">Amount:</span>
                <span class="info-val fw-bold">${{ number_format($payment->amount, 2) }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Method:</span>
                <span class="info-val">{{ $payment->methodLabel() }}</span>
            </div>

            @if($payment->payment_method === 'whish' && $payment->whish_phone)
                <div class="info-row">
                    <span class="info-label">Phone:</span>
                    <span class="info-val">{{ $payment->whish_phone }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Ref:</span>
                    <span class="info-val">
                        <code>{{ $payment->whish_reference ?? '—' }}</code>
                    </span>
                </div>
            @endif

            @if($payment->payment_method === 'crypto' && $payment->transaction_reference)
                <div class="info-row">
                    <span class="info-label">Coin:</span>
                    <span class="info-val">{{ $payment->crypto_coin }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">TX Hash:</span>
                    <span class="info-val">
                        <code style="font-size:0.7rem;word-break:break-all;">
                            {{ $payment->transaction_reference }}
                        </code>
                    </span>
                </div>
            @endif

            @if($payment->payment_method === 'cash')
                <div class="info-row" style="border-bottom:0;">
                    <span class="info-val text-muted">
                        <i class="bi bi-info-circle me-1"></i>
                        Citizen will pay cash on pickup.
                    </span>
                </div>
            @endif

            @if($payment->isPaid())
                <div class="mt-1 text-success" style="font-size:0.75rem;">
                    <i class="bi bi-check-circle me-1"></i>
                    Confirmed on {{ $payment->paid_at?->format('M d, Y \a\t g:i A') }}
                </div>
            @elseif(in_array($payment->payment_method, ['whish', 'crypto'])
                    && ($payment->whish_reference || $payment->transaction_reference))
                {{-- Show confirm button for whish/crypto with submitted details --}}
                <form method="POST"
                      action="{{ route('municipality.requests.confirm-payment', $serviceRequest, absolute: false) }}"
                      class="mt-2">
                    @csrf
                    <button type="submit"
                            class="btn btn-success btn-sm w-100"
                            style="font-size:0.75rem;"
                            onclick="return confirm('Confirm payment and auto-approve this request?')">
                        <i class="bi bi-check-circle me-1"></i>
                        Confirm Payment & Approve Request
                    </button>
                </form>
            @elseif(! $payment->isPaid())
                <div class="mt-1 text-warning" style="font-size:0.75rem;">
                    <i class="bi bi-clock me-1"></i>
                    Awaiting citizen payment submission.
                </div>
            @endif

        </div>
    </div>
@endif
{{-- DOCUMENTS --}}
<div class="row g-1">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light py-1" style="padding: 0.3rem 0.5rem;">
                <small class="fw-bold"><i class="bi bi-upload me-1"></i>Citizen Documents</small>
            </div>
            <div class="card-body" style="padding:0.4rem; max-height:150px; overflow-y:auto; font-size:0.75rem;">
                @php $citizenDocs = $serviceRequest->requestDocuments->where('type', 'citizen_upload'); @endphp
                @forelse($citizenDocs as $doc)
                    <div class="d-flex align-items-center gap-1 py-1 border-bottom">
                        <i class="bi bi-file-pdf text-danger"></i>
                        <span class="flex-grow-1 text-truncate">{{ basename($doc->file_path) }}</span>
                        <a href="{{ route('municipality.requests.download-document', [$serviceRequest, $doc], absolute: false) }}" class="btn btn-link btn-sm" style="padding:0;"><i class="bi bi-download"></i></a>
                    </div>
                @empty
                    <small class="text-muted">None</small>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light py-1" style="padding: 0.3rem 0.5rem;">
                <small class="fw-bold"><i class="bi bi-file-check me-1"></i>Response Documents</small>
            </div>
            <div class="card-body" style="padding:0.4rem; font-size:0.75rem;">
                <form method="POST" action="{{ route('municipality.requests.upload-document', $serviceRequest, absolute: false) }}" enctype="multipart/form-data" class="mb-1">
                    @csrf
                    <div class="input-group input-group-sm">
                        <input type="file" name="response_document" class="form-control form-control-sm @error('response_document') is-invalid @enderror" accept="application/pdf" style="font-size:0.75rem;">
                        <button type="submit" class="btn btn-primary btn-sm" style="padding:0.2rem 0.5rem;"><i class="bi bi-upload"></i></button>
                    </div>
                </form>
                @php $officialDocs = $serviceRequest->requestDocuments->where('type', 'official_response'); @endphp
                <div style="max-height:100px; overflow-y:auto;">
                    @forelse($officialDocs as $doc)
                        <div class="d-flex align-items-center gap-1 py-1 border-bottom">
                            <i class="bi bi-file-pdf text-danger"></i>
                            <span class="flex-grow-1 text-truncate">{{ basename($doc->file_path) }}</span>
                            <a href="{{ route('municipality.requests.download-document', [$serviceRequest, $doc], absolute: false) }}" class="btn btn-link btn-sm" style="padding:0;"><i class="bi bi-download"></i></a>
                            <form method="POST" action="{{ route('municipality.requests.delete-document', [$serviceRequest, $doc], absolute: false) }}" onsubmit="return confirm('Delete?')" style="display:inline;">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-link btn-sm text-danger" style="padding:0;"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    @empty
                        <small class="text-muted">None</small>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const qrToken = "{{ $serviceRequest->qr_code_token }}";
        const scanUrl = "{{ route('qr.scan', ['token' => '__TOKEN__'], absolute: true) }}".replace('__TOKEN__', qrToken);
        if (document.getElementById('qrcode')) {
            new QRCode(document.getElementById('qrcode'), {
                text: scanUrl,
                width: 60,
                height: 60,
                colorDark: "#000000",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.H
            });
        }
    });

    @if(auth()->check())
        if (typeof window.Echo !== 'undefined') {
            window.Echo.private('service-request.{{ $serviceRequest->id }}')
                .listen('service-request-status-changed', (event) => {
                    location.reload();
                });
        }
    @endif
</script>
@endsection

@endsection
