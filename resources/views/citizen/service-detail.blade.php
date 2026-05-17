@extends('layouts.public')
@section('title', $service->name)
@section('page-title', 'Service Details')
@push('styles')
<style>
    .service-page {
        --service-green: #0a5c4a;
        --service-green-dark: #064e3b;
        --service-green-soft: #ecfdf5;
        --service-mint: #6ee7b7;
        --service-border: #d9eee7;
    }

    .service-page .btn-service-outline {
        border-color: var(--service-green);
        color: var(--service-green);
    }

    .service-page .btn-service-outline:hover,
    .service-page .btn-service-outline:focus {
        background: var(--service-green);
        color: #fff;
    }

    .service-page .service-badge-office {
        background: var(--service-green);
        color: #fff;
    }

    .service-page .service-badge-category {
        background: #e2e8f0;
        color: #334155;
    }

    .service-page .service-card-title {
        color: var(--service-green);
        font-weight: 700;
    }

    .service-page .request-header,
    .service-page .btn-service-submit {
        background: var(--service-green);
        color: #fff;
    }

    .service-page .btn-service-submit {
        border: 0;
        font-weight: 700;
    }

    .service-page .btn-service-submit:hover,
    .service-page .btn-service-submit:focus {
        background: var(--service-green-dark);
        color: #fff;
    }

    .service-page .summary-box {
        background: var(--service-green-soft);
        border: 1px solid var(--service-border);
        color: #164e3f;
    }

    .service-page .document-upload {
        border: 1px solid var(--service-border);
        border-radius: 8px;
        padding: 0.85rem;
        background: #fbfffd;
    }

    .service-page .document-upload + .document-upload {
        margin-top: 0.85rem;
    }

    .service-page .document-upload.has-error {
        border-color: #dc3545;
        background: #fff7f7;
    }

    .service-page .document-title {
        color: var(--service-green);
        font-weight: 700;
        font-size: 0.86rem;
    }

    .service-page .document-status {
        color: #64748b;
        font-size: 0.76rem;
        margin-top: 0.35rem;
        word-break: break-word;
    }

    .service-page .document-status.has-file {
        color: var(--service-green);
        font-weight: 600;
    }

    .service-page .form-control:focus,
    .service-page .form-check-input:focus {
        border-color: var(--service-green);
        box-shadow: 0 0 0 0.2rem rgba(10, 92, 74, 0.15);
    }

    .service-page .form-check-input:checked {
        background-color: var(--service-green);
        border-color: var(--service-green);
    }
</style>
@endpush
@section('content')
@php
    $requiredDocuments = collect($service->required_documents ?? [])->values();
@endphp
<div class="container-fluid service-page">
    <!-- Back button -->
    <a href="{{ route('citizen.services') }}" class="btn btn-service-outline btn-sm mb-3">
        <i class="bi bi-arrow-left"></i> Back to Services
    </a>

    <!-- Service Details -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="mb-3">
                        <span class="badge service-badge-office me-2">{{ $service->office->name }}</span>
                        <span class="badge service-badge-category">{{ $service->category->name }}</span>
                    </div>

                    <h1 class="card-title">{{ $service->name }}</h1>
                    <p class="card-text text-muted">{{ $service->description }}</p>

                    <div class="row g-4 mt-4">
                        <div class="col-md-4">
                            <h6 class="text-muted mb-2">Service Fee</h6>
                            <h4 class="text-success">${{ number_format($service->price, 2) }}</h4>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-muted mb-2">Processing Time</h6>
                            <h4>{{ $service->duration_days }} days</h4>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-muted mb-2">Office Location</h6>
                            <p class="mb-0">{{ $service->office->address ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Required Documents -->
            @if($service->required_documents && count($service->required_documents) > 0)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-light border-0">
                        <h6 class="mb-0 service-card-title"><i class="bi bi-file-earmark-check"></i> Required Documents</h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            @foreach($service->required_documents as $doc)
                                <li class="list-group-item">
                                    <i class="bi bi-file-earmark-pdf"></i> {{ $doc }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <!-- Feedbacks -->
            @if($service->feedbacks->isNotEmpty())
                <div class="card border-0 shadow-sm">
                <div class="card-header bg-light border-0">
                        <h6 class="mb-0 service-card-title"><i class="bi bi-star-fill"></i> User Feedback</h6>
                    </div>
                    <div class="card-body">
                        @foreach($service->feedbacks->take(5) as $feedback)
                            <div class="mb-3 pb-3 border-bottom">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <strong>{{ $feedback->citizen->name }}</strong>
                                        <div class="text-warning small">
                                            @for($i = 0; $i < $feedback->rating; $i++)
                                                <i class="bi bi-star-fill"></i>
                                            @endfor
                                        </div>
                                    </div>
                                    <small class="text-muted">{{ $feedback->created_at->diffForHumans() }}</small>
                                </div>
                                <p class="mb-0 mt-2">{{ $feedback->citizen_comment }}</p>
                                @if($feedback->hasPublicOfficeResponse())
                                    <div class="mt-2 p-2 rounded bg-light border-start border-3 border-primary">
                                        <div class="small fw-bold text-primary mb-1">
                                            <i class="bi bi-reply me-1"></i>Municipality reply
                                        </div>
                                        <p class="mb-0 small">{{ $feedback->office_response }}</p>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Submit Request Form -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm sticky-top" style="top: 100px;">
                <div class="card-header request-header border-0">
                    <h6 class="mb-0"><i class="bi bi-plus-circle"></i> Submit Service Request</h6>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>Errors found:</strong>
                            <ul class="mb-0 mt-2">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('citizen.service-requests.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <!-- Service ID (hidden) -->
                        <input type="hidden" name="service_id" value="{{ $service->id }}">

                        <!-- Document Upload -->
                        <div class="mb-4">
                            <label class="form-label">
                                <strong>Upload Required Documents</strong>
                                @if($requiredDocuments->isNotEmpty())
                                    <span class="text-danger">*</span>
                                @endif
                            </label>
                            <p class="small text-muted mb-2">
                                Upload each document in its matching field. Accepted files: PDF, Word, JPG, PNG. Max 5MB each.
                            </p>
                            @error('documents')
                                <div class="text-danger small mb-2">{{ $message }}</div>
                            @enderror

                            @forelse($requiredDocuments as $index => $documentName)
                                <div class="document-upload @error('documents.' . $index) has-error @enderror">
                                    <label for="document_{{ $index }}" class="form-label document-title mb-2">
                                        <i class="bi bi-file-earmark-arrow-up me-1"></i>{{ $documentName }}
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input
                                        type="file"
                                        class="form-control document-input @error('documents.' . $index) is-invalid @enderror"
                                        id="document_{{ $index }}"
                                        name="documents[{{ $index }}]"
                                        accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                                        required
                                    >
                                    <div class="document-status" data-document-status="document_{{ $index }}">
                                        No file selected
                                    </div>
                                    @error('documents.' . $index)
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            @empty
                                <div class="alert summary-box small mb-0">
                                    No documents are required for this service.
                                </div>
                            @endforelse
                        </div>

                        <!-- Summary -->
                        <div class="alert summary-box small mb-3">
                            <strong>Summary:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Service: <strong>{{ $service->name }}</strong></li>
                                <li>Office: <strong>{{ $service->office->name }}</strong></li>
                                <li>Fee: <strong>${{ number_format($service->price, 2) }}</strong></li>
                            </ul>
                        </div>

                        <!-- Terms -->
                        <div class="form-check mb-3">
                            <input 
                                class="form-check-input @error('agree') is-invalid @enderror" 
                                type="checkbox" 
                                id="agree" 
                                name="agree" 
                                value="1"
                                required
                            >
                            <label class="form-check-label small" for="agree">
                                I agree that the information provided is accurate and complete
                            </label>
                            @error('agree')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-service-submit w-100">
                            <i class="bi bi-check-circle"></i> Submit Request
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.querySelectorAll('.document-input').forEach((input) => {
        input.addEventListener('change', function () {
            const status = document.querySelector(`[data-document-status="${this.id}"]`);
            const file = this.files && this.files.length > 0 ? this.files[0] : null;

            if (!status) {
                return;
            }

            if (!file) {
                status.textContent = 'No file selected';
                status.classList.remove('has-file');
                return;
            }

            const size = (file.size / 1024).toFixed(1);
            status.textContent = `${file.name} (${size} KB) selected`;
            status.classList.add('has-file');
        });
    });
</script>
@endpush
@endsection
