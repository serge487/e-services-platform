@extends('municipality.layouts.app')
@section('title', 'Edit Office — ' . $office->name)
@section('page-title', 'Edit Office Profile')

@section('content')

{{-- Back link --}}
<div class="mb-4">
    <a href="{{ route('municipality.office-profile', absolute: false) }}" class="text-decoration-none text-muted small">
        <i class="bi bi-arrow-left me-1"></i>Back to Office Profile
    </a>
</div>

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="bi bi-exclamation-triangle me-2"></i>
        Please fix the errors below before saving.
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<form method="POST" action="{{ route('municipality.office-profile.update', $office, absolute: false) }}">
    @csrf
    @method('PUT')

    <div class="row g-4">

        {{-- Left column: basic info --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-semibold"><i class="bi bi-info-circle me-2"></i>Basic Information</h6>
                </div>
                <div class="card-body p-4">

                    {{-- Office name --}}
                    <div class="mb-3">
                        <label for="office_name" class="form-label fw-semibold">Office Name <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            id="office_name"
                            name="name"
                            class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name', $office->name) }}"
                            placeholder="e.g. Civil Registry Office"
                            required
                        >
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Address --}}
                    <div class="mb-3">
                        <label for="office_address" class="form-label fw-semibold">Address <span class="text-danger">*</span></label>
                        <textarea
                            id="office_address"
                            name="address"
                            rows="3"
                            class="form-control @error('address') is-invalid @enderror"
                            placeholder="Full street address"
                            required
                        >{{ old('address', $office->address) }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Contact info --}}
                    <div class="mb-3">
                        <label for="office_contact_info" class="form-label fw-semibold">Contact Info <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            id="office_contact_info"
                            name="contact_info"
                            class="form-control @error('contact_info') is-invalid @enderror"
                            value="{{ old('contact_info', $office->contact_info) }}"
                            placeholder="e.g. +961 1 234 567 or email@office.gov.lb"
                            required
                        >
                        @error('contact_info')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>
            </div>
        </div>

        {{-- Right column: map & coordinates --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-semibold"><i class="bi bi-geo-alt me-2"></i>Location</h6>
                </div>
                <div class="card-body p-4">

                    <p class="text-muted small mb-3">
                        <i class="bi bi-info-circle me-1"></i>
                        Click on the map to set the office location, or enter coordinates manually.
                    </p>

                    {{-- Leaflet map --}}
                    <div id="office-map" style="height: 280px; border-radius: 8px; border: 1px solid #dee2e6; margin-bottom: 1rem;"></div>

                    {{-- Lat / Lng inputs --}}
                    <div class="row g-2">
                        <div class="col-6">
                            <label for="office_latitude" class="form-label fw-semibold small">Latitude <span class="text-danger">*</span></label>
                            <input
                                type="number"
                                step="any"
                                id="office_latitude"
                                name="latitude"
                                class="form-control form-control-sm @error('latitude') is-invalid @enderror"
                                value="{{ old('latitude', $office->latitude) }}"
                                placeholder="33.8938"
                                required
                            >
                            @error('latitude')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-6">
                            <label for="office_longitude" class="form-label fw-semibold small">Longitude <span class="text-danger">*</span></label>
                            <input
                                type="number"
                                step="any"
                                id="office_longitude"
                                name="longitude"
                                class="form-control form-control-sm @error('longitude') is-invalid @enderror"
                                value="{{ old('longitude', $office->longitude) }}"
                                placeholder="35.5018"
                                required
                            >
                            @error('longitude')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- Working hours --}}
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-semibold"><i class="bi bi-clock me-2"></i>Working Hours</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        @php
                            $days = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
                        @endphp
                        @foreach($days as $day)
                            @php
                                $dayData  = $workingHours[$day];
                                $isOpen   = old("working_hours.{$day}.is_open", $dayData['is_open']);
                                $openTime = old("working_hours.{$day}.open_time", $dayData['open_time']);
                                $closeTime = old("working_hours.{$day}.close_time", $dayData['close_time']);
                            @endphp
                            <div class="col-12 col-md-6 col-xl-4">
                                <div class="border rounded p-3 h-100" style="background: #f8fafc;">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <label class="fw-semibold text-capitalize mb-0" style="font-size:0.9rem;">
                                            {{ $day }}
                                        </label>
                                        <div class="form-check form-switch mb-0">
                                            <input
                                                class="form-check-input working-hours-toggle"
                                                type="checkbox"
                                                role="switch"
                                                id="toggle_{{ $day }}"
                                                name="working_hours[{{ $day }}][is_open]"
                                                value="1"
                                                {{ $isOpen ? 'checked' : '' }}
                                                data-day="{{ $day }}"
                                            >
                                            <label class="form-check-label small text-muted" for="toggle_{{ $day }}">
                                                {{ $isOpen ? 'Open' : 'Closed' }}
                                            </label>
                                        </div>
                                    </div>
                                    <div class="hours-inputs-{{ $day }}" style="{{ $isOpen ? '' : 'display:none;' }}">
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <label class="form-label small text-muted mb-1">Opens</label>
                                                <input
                                                    type="time"
                                                    name="working_hours[{{ $day }}][open_time]"
                                                    class="form-control form-control-sm"
                                                    value="{{ $openTime }}"
                                                >
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label small text-muted mb-1">Closes</label>
                                                <input
                                                    type="time"
                                                    name="working_hours[{{ $day }}][close_time]"
                                                    class="form-control form-control-sm"
                                                    value="{{ $closeTime }}"
                                                >
                                            </div>
                                        </div>
                                    </div>
                                    <div class="closed-label-{{ $day }} text-muted small {{ $isOpen ? 'd-none' : '' }}">
                                        <i class="bi bi-x-circle me-1"></i>Closed this day
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Save button --}}
        <div class="col-12">
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-floppy me-2"></i>Save Changes
                </button>
                <a href="{{ route('municipality.office-profile', absolute: false) }}" class="btn btn-outline-secondary px-4">
                    Cancel
                </a>
            </div>
        </div>

    </div>
</form>

{{-- Leaflet CSS & JS --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    // -------------------------------------------------------------------------
    // Leaflet map: initialise at current office coordinates
    // -------------------------------------------------------------------------
    const initialLat = parseFloat(document.getElementById('office_latitude').value) || 33.8938;
    const initialLng = parseFloat(document.getElementById('office_longitude').value) || 35.5018;

    const officeMap = L.map('office-map').setView([initialLat, initialLng], 15);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(officeMap);

    // Draggable marker
    const officeMarker = L.marker([initialLat, initialLng], { draggable: true }).addTo(officeMap);

    // Update coordinate inputs when marker is dragged
    officeMarker.on('dragend', function (event) {
        const position = event.target.getLatLng();
        document.getElementById('office_latitude').value  = position.lat.toFixed(8);
        document.getElementById('office_longitude').value = position.lng.toFixed(8);
    });

    // Click on map to move marker
    officeMap.on('click', function (event) {
        officeMarker.setLatLng(event.latlng);
        document.getElementById('office_latitude').value  = event.latlng.lat.toFixed(8);
        document.getElementById('office_longitude').value = event.latlng.lng.toFixed(8);
    });

    // Update marker when coordinates are typed manually
    ['office_latitude', 'office_longitude'].forEach(function (fieldId) {
        document.getElementById(fieldId).addEventListener('change', function () {
            const lat = parseFloat(document.getElementById('office_latitude').value);
            const lng = parseFloat(document.getElementById('office_longitude').value);
            if (! isNaN(lat) && ! isNaN(lng)) {
                officeMarker.setLatLng([lat, lng]);
                officeMap.setView([lat, lng], 15);
            }
        });
    });

    // -------------------------------------------------------------------------
    // Working hours: toggle open/closed per day
    // -------------------------------------------------------------------------
    document.querySelectorAll('.working-hours-toggle').forEach(function (toggle) {
        toggle.addEventListener('change', function () {
            const day         = this.dataset.day;
            const hoursInputs = document.querySelector('.hours-inputs-' + day);
            const closedLabel = document.querySelector('.closed-label-' + day);
            const switchLabel = this.nextElementSibling;

            if (this.checked) {
                hoursInputs.style.display = '';
                closedLabel.classList.add('d-none');
                switchLabel.textContent = 'Open';
            } else {
                hoursInputs.style.display = 'none';
                closedLabel.classList.remove('d-none');
                switchLabel.textContent = 'Closed';
            }
        });
    });
</script>

@endsection