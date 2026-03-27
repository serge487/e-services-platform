@extends('municipality.layouts.app')
@section('title', 'Request #' . $serviceRequest->id)
@section('page-title', 'Request Details')

@section('content')

<div class="mb-4">
    <a href="{{ route('municipality.requests', absolute: false) }}" class="text-decoration-none text-muted small">
        <i class="bi bi-arrow-left me-1"></i>Back to Requests
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show">
        @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-4">

    {{-- Left: Request info + status update --}}
    <div class="col-lg-7">

        {{-- Request summary --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-file-earmark-text me-2"></i>Request #{{ $serviceRequest->id }}</h6>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <div class="text-muted small fw-semibold text-uppercase" style="font-size:0.7rem;">Citizen</div>
                        <div class="fw-semibold">{{ $serviceRequest->citizen->name }}</div>
                        <div class="text-muted small">{{ $serviceRequest->citizen->email }}</div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted small fw-semibold text-uppercase" style="font-size:0.7rem;">Service</div>
                        <div class="fw-semibold">{{ $serviceRequest->service->name }}</div>
                        <div class="text-muted small">{{ $serviceRequest->service->category->name }}</div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted small fw-semibold text-uppercase" style="font-size:0.7rem;">Office</div>
                        <div>{{ $serviceRequest->service->office->name }}</div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted small fw-semibold text-uppercase" style="font-size:0.7rem;">Submitted</div>
                        <div>{{ $serviceRequest->created_at->format('d M Y, H:i') }}</div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted small fw-semibold text-uppercase" style="font-size:0.7rem;">Current Status</div>
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
                        <span class="badge bg-{{ $badgeColor }} fs-6">{{ $serviceRequest->status }}</span>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted small fw-semibold text-uppercase" style="font-size:0.7rem;">QR Token</div>
                        <code class="small">{{ $serviceRequest->qr_code_token }}</code>
                    </div>
                </div>
            </div>
        </div>

        {{-- Update status form --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-arrow-repeat me-2"></i>Update Status</h6>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('municipality.requests.update-status', $serviceRequest, absolute: false) }}">
                    @csrf @method('PATCH')
                    <div class="mb-3">
                        <label for="request_status" class="form-label fw-semibold">New Status <span class="text-danger">*</span></label>
                        <select id="request_status" name="status" class="form-select" required>
                            @foreach(['Pending', 'In Review', 'Missing Documents', 'Approved', 'Rejected', 'Completed'] as $status)
                                <option value="{{ $status }}"
                                    {{ $serviceRequest->status === $status ? 'selected' : '' }}>
                                    {{ $status }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="office_notes" class="form-label fw-semibold">Office Notes</label>
                        <textarea id="office_notes" name="office_notes" rows="3"
                                  class="form-control"
                                  placeholder="Add notes visible to the citizen (optional)">{{ old('office_notes', $serviceRequest->office_notes) }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-2"></i>Update Status
                    </button>
                </form>
            </div>
        </div>

    </div>

    {{-- Right: Documents --}}
    <div class="col-lg-5">

        {{-- Citizen uploaded documents --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-upload me-2"></i>Citizen Documents</h6>
            </div>
            <div class="card-body p-3">
                @php
                    $citizenDocs = $serviceRequest->requestDocuments->where('type', 'citizen_upload');
                @endphp
                @if($citizenDocs->isEmpty())
                    <p class="text-muted small mb-0">No documents uploaded by citizen.</p>
                @else
                    @foreach($citizenDocs as $doc)
                        <div class="d-flex align-items-center gap-2 py-2 border-bottom">
                            <i class="bi bi-file-earmark-pdf text-danger fs-5"></i>
                            <span class="small flex-grow-1">{{ basename($doc->file_path) }}</span>
                            <a href="{{ Storage::url($doc->file_path) }}" target="_blank"
                               class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-download"></i>
                            </a>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>

        {{-- Official response documents --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-file-earmark-check me-2"></i>Official Response Documents</h6>
            </div>
            <div class="card-body p-3">

                {{-- Upload form --}}
                <form method="POST"
                      action="{{ route('municipality.requests.upload-document', $serviceRequest, absolute: false) }}"
                      enctype="multipart/form-data"
                      class="mb-3">
                    @csrf
                    <label class="form-label fw-semibold small">Upload PDF Response</label>
                    <div class="input-group input-group-sm">
                        <input type="file"
                               name="response_document"
                               class="form-control @error('response_document') is-invalid @enderror"
                               accept="application/pdf">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-upload me-1"></i>Upload
                        </button>
                        @error('response_document')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="text-muted mt-1" style="font-size:0.75rem;">PDF only · Max 10 MB</div>
                </form>

                {{-- Existing official response docs --}}
                @php
                    $officialDocs = $serviceRequest->requestDocuments->where('type', 'official_response');
                @endphp
                @if($officialDocs->isEmpty())
                    <p class="text-muted small mb-0">No official response documents uploaded yet.</p>
                @else
                    @foreach($officialDocs as $doc)
                        <div class="d-flex align-items-center gap-2 py-2 border-bottom">
                            <i class="bi bi-file-earmark-pdf text-danger fs-5"></i>
                            <span class="small flex-grow-1 text-truncate">{{ basename($doc->file_path) }}</span>
                            <a href="{{ Storage::url($doc->file_path) }}" target="_blank"
                               class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-download"></i>
                            </a>
                            <form method="POST"
                                  action="{{ route('municipality.requests.delete-document', [$serviceRequest, $doc], absolute: false) }}"
                                  onsubmit="return confirm('Delete this document?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>

    </div>
</div>

@endsection