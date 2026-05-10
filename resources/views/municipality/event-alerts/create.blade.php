@extends('municipality.layouts.app')
@section('title', 'Send Event Alert')
@section('page-title', 'Send Event Alert')

@section('content')

<div class="mb-4">
    <a href="{{ route('municipality.event-alerts.index', absolute: false) }}"
       class="text-decoration-none text-muted small">
        <i class="bi bi-arrow-left me-1"></i>Back to Event Alerts
    </a>
</div>

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-4">
        <i class="bi bi-exclamation-triangle me-2"></i>Please fix the errors below.
        <ul class="mb-0 mt-2 small">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-4">

    {{-- Form --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-megaphone me-2 text-primary"></i>Event Details
                </h6>
            </div>
            <div class="card-body p-4">
                <form method="POST"
                      action="{{ route('municipality.event-alerts.store', absolute: false) }}"
                      id="event-alert-form">
                    @csrf

                    {{-- Office selector --}}
                    <div class="mb-3">
                        <label for="office_id" class="form-label fw-semibold">
                            Office <span class="text-danger">*</span>
                        </label>
                        <select id="office_id"
                                name="office_id"
                                class="form-select @error('office_id') is-invalid @enderror"
                                required>
                            <option value="">— Select office —</option>
                            @foreach($offices as $office)
                                <option value="{{ $office->id }}"
                                        data-count="{{ $eligibleCounts[$office->id] ?? 0 }}"
                                        {{ old('office_id') == $office->id ? 'selected' : '' }}>
                                    {{ $office->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('office_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        {{-- Eligible citizen count preview --}}
                        <div id="eligible-preview" class="mt-2 d-none">
                            <span class="badge bg-info bg-opacity-15 text-info">
                                <i class="bi bi-people me-1"></i>
                                <span id="eligible-count">0</span> citizen(s) will be notified
                            </span>
                            <small class="text-muted ms-1">
                                (citizens who booked appointments or chatted with this office)
                            </small>
                        </div>
                    </div>

                    {{-- Title --}}
                    <div class="mb-3">
                        <label for="title" class="form-label fw-semibold">
                            Event Title <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               id="title"
                               name="title"
                               class="form-control @error('title') is-invalid @enderror"
                               value="{{ old('title') }}"
                               placeholder="e.g. Office Closure Notice, National Day Celebration"
                               required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Occasion --}}
                    <div class="mb-3">
                        <label for="occasion" class="form-label fw-semibold">
                            Occasion
                            <span class="text-muted fw-normal small">(optional)</span>
                        </label>
                        <input type="text"
                               id="occasion"
                               name="occasion"
                               class="form-control @error('occasion') is-invalid @enderror"
                               value="{{ old('occasion') }}"
                               placeholder="e.g. National Day, Office Closure, Public Holiday">
                        @error('occasion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Date & Time --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="event_date" class="form-label fw-semibold">
                                Date & Time <span class="text-danger">*</span>
                            </label>
                            <input type="datetime-local"
                                   id="event_date"
                                   name="event_date"
                                   class="form-control @error('event_date') is-invalid @enderror"
                                   value="{{ old('event_date') }}"
                                   min="{{ now()->format('Y-m-d\TH:i') }}"
                                   required>
                            @error('event_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="place" class="form-label fw-semibold">
                                Location / Place <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   id="place"
                                   name="place"
                                   class="form-control @error('place') is-invalid @enderror"
                                   value="{{ old('place') }}"
                                   placeholder="e.g. Main Hall, City Center"
                                   required>
                            @error('place')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Description --}}
                    <div class="mb-4">
                        <label for="description" class="form-label fw-semibold">
                            Description <span class="text-danger">*</span>
                        </label>
                        <textarea id="description"
                                  name="description"
                                  rows="5"
                                  class="form-control @error('description') is-invalid @enderror"
                                  placeholder="Provide full details about this event..."
                                  required>{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-send me-2"></i>Send Alert
                        </button>
                        <a href="{{ route('municipality.event-alerts.index', absolute: false) }}"
                           class="btn btn-outline-secondary px-4">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Info panel --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-info-circle me-2 text-primary"></i>How it works
                </h6>
            </div>
            <div class="card-body p-4">
                <div class="d-flex gap-3 mb-3">
                    <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center
                                justify-content-center flex-shrink-0"
                         style="width:36px;height:36px;">
                        <i class="bi bi-people text-primary"></i>
                    </div>
                    <div>
                        <div class="fw-semibold small">Who gets notified</div>
                        <div class="text-muted small">
                            Citizens who booked appointments or chatted with the selected office.
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-3 mb-3">
                    <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center
                                justify-content-center flex-shrink-0"
                         style="width:36px;height:36px;">
                        <i class="bi bi-envelope text-success"></i>
                    </div>
                    <div>
                        <div class="fw-semibold small">Email + In-app</div>
                        <div class="text-muted small">
                            Each citizen receives an email and an in-app notification.
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-3">
                    <div class="rounded-circle bg-warning bg-opacity-10 d-flex align-items-center
                                justify-content-center flex-shrink-0"
                         style="width:36px;height:36px;">
                        <i class="bi bi-clock text-warning"></i>
                    </div>
                    <div>
                        <div class="fw-semibold small">Sent immediately</div>
                        <div class="text-muted small">
                            Notifications are queued and sent right after you submit.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm border-warning"
             style="border-left: 4px solid #ffc107 !important;">
            <div class="card-body p-3">
                <div class="fw-semibold small text-warning mb-1">
                    <i class="bi bi-exclamation-triangle me-1"></i>Before sending
                </div>
                <ul class="small text-muted mb-0 ps-3">
                    <li>Double-check the date and time.</li>
                    <li>Make sure the location is accurate.</li>
                    <li>This action cannot be undone.</li>
                </ul>
            </div>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script>
// Update eligible citizen count when office changes
document.getElementById('office_id').addEventListener('change', function () {
    const selected = this.options[this.selectedIndex];
    const count    = parseInt(selected.dataset.count || '0');
    const preview  = document.getElementById('eligible-preview');
    const countEl  = document.getElementById('eligible-count');

    if (this.value) {
        preview.classList.remove('d-none');
        countEl.textContent = count;
    } else {
        preview.classList.add('d-none');
    }
});

// Trigger on page load if office is pre-selected (old() value)
document.getElementById('office_id').dispatchEvent(new Event('change'));
</script>
@endpush