@extends('layouts.public')
@section('title', 'Appointments')
@section('page-title', 'My Appointments')
@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('warning'))
    <div class="alert alert-warning alert-dismissible fade show">
        <i class="bi bi-exclamation-triangle me-2"></i>{{ session('warning') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger">
        <i class="bi bi-x-circle me-2"></i>{{ $errors->first() }}
    </div>
@endif

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('citizen.appointments', absolute: false) }}" class="row g-2 g-md-3 align-items-end">
            <div class="col-12 col-md-6 col-lg-4">
                <label for="filter-slot-office" class="form-label small text-muted mb-1">Office (available slots)</label>
                <select id="filter-slot-office" name="slot_office_id" class="form-select form-select-sm">
                    <option value="">All offices</option>
                    @foreach($officesForSlotFilter as $office)
                        <option value="{{ $office->id }}" @selected((string) request('slot_office_id') === (string) $office->id)>
                            {{ $office->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-sm-6 col-lg-2">
                <label for="filter-slot-date" class="form-label small text-muted mb-1">Slot date</label>
                <input id="filter-slot-date" type="date" name="slot_date" class="form-control form-control-sm" value="{{ request('slot_date') }}">
            </div>
            <div class="col-12 col-md-6 col-lg-3">
                <label for="filter-slot-q" class="form-label small text-muted mb-1">Search office or officer</label>
                <input id="filter-slot-q" type="search" name="slot_q" class="form-control form-control-sm" value="{{ request('slot_q') }}" placeholder="e.g. Tripoli, James…" autocomplete="off">
            </div>
            <div class="col-12 col-sm-6 col-lg-2">
                <label for="filter-booking-status" class="form-label small text-muted mb-1">My bookings status</label>
                <select id="filter-booking-status" name="booking_status" class="form-select form-select-sm">
                    <option value="">All statuses</option>
                    @foreach([
                        'scheduled' => 'Scheduled',
                        'confirmed' => 'Confirmed',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                        'no_show' => 'No show',
                    ] as $value => $label)
                        <option value="{{ $value }}" @selected(request('booking_status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-lg-2 d-flex flex-wrap gap-2">
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="bi bi-funnel me-1"></i>Apply
                </button>
                <a href="{{ route('citizen.appointments', absolute: false) }}" class="btn btn-sm btn-outline-secondary">Clear</a>
            </div>
        </form>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 pt-3">
                <h6 class="mb-0">Available Slots</h6>
            </div>
            <div class="card-body p-0" id="available-slots-container">
                @include('citizen.partials.appointments-available-slots', ['availableSlots' => $availableSlots])
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 pt-3">
                <h6 class="mb-0">My Bookings</h6>
            </div>
            <div class="card-body p-0" id="my-bookings-container">
                @include('citizen.partials.appointments-my-bookings', ['appointments' => $appointments])
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        const availableContainer = document.getElementById('available-slots-container');
        const bookingsContainer = document.getElementById('my-bookings-container');
        if (!availableContainer || !bookingsContainer) {
            return;
        }

        let isLoading = false;

        const refreshAppointments = async () => {
            if (isLoading) {
                return;
            }

            isLoading = true;

            try {
                const qs = window.location.search || '';
                const liveUrl = '{{ route('citizen.appointments.live', absolute: false) }}' + qs;

                const response = await fetch(liveUrl, {
                    credentials: 'same-origin',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) {
                    return;
                }

                const contentType = response.headers.get('content-type') || '';
                if (!contentType.includes('application/json')) {
                    return;
                }

                const data = await response.json();
                if (data.available_slots_html !== undefined) {
                    availableContainer.innerHTML = data.available_slots_html;
                }
                if (data.appointments_html !== undefined) {
                    bookingsContainer.innerHTML = data.appointments_html;
                }
            } catch (error) {
                console.warn('Appointment live refresh failed:', error);
            } finally {
                isLoading = false;
            }
        };

        refreshAppointments();
        setInterval(refreshAppointments, 2000);

        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                refreshAppointments();
            }
        });

        const userId = window.__notificationUserId;
        if (window.Echo && userId && !window.__citizenAppointmentsEchoBound) {
            window.__citizenAppointmentsEchoBound = true;
            window.Echo.private(`App.Models.User.${userId}`)
                .listen('.appointment.changed', () => {
                    refreshAppointments();
                })
                .error((status) => {
                    console.warn('[Citizen Appointments] Echo subscription error', status);
                });
        }
    })();
</script>
@endpush