@extends('municipality.layouts.app')
@section('title', 'Appointments')
@section('page-title', 'Appointments')
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

<div id="appointments-flash-container"></div>

@if($errors->any())
    <div class="alert alert-danger">
        <i class="bi bi-x-circle me-2"></i>{{ $errors->first() }}
    </div>
@endif

<div class="row g-4 mb-4">
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 pt-3">
                <h6 class="mb-0">Add Officer Time Slot</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('municipality.appointments.slots.store', absolute: false) }}" class="row g-3">
                    @csrf
                    <div class="col-12">
                        <label class="form-label small">Office</label>
                        <select name="office_id" class="form-select" required>
                            @foreach($offices as $office)
                                <option value="{{ $office->id }}" @selected((string) old('office_id') === (string) $office->id)>{{ $office->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label small">Officer</label>
                        <select name="officer_id" class="form-select" required>
                            @foreach($officers as $officer)
                                <option value="{{ $officer->id }}" @selected((string) old('officer_id') === (string) $officer->id)>{{ $officer->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small">Date</label>
                        <input type="date" name="slot_date" class="form-control" value="{{ old('slot_date') }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">From</label>
                        <input type="time" name="start_time" class="form-control" value="{{ old('start_time') }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">To</label>
                        <input type="time" name="end_time" class="form-control" value="{{ old('end_time') }}" required>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-calendar-plus me-1"></i> Add Slot
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 pt-3">
                <h6 class="mb-0">Upcoming Time Slots</h6>
            </div>
            <div class="card-body p-0" id="municipality-time-slots-container">
                @include('municipality.partials.appointments-time-slots', ['timeSlots' => $timeSlots])
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h6 class="mb-0 pt-2">Booked Appointments</h6>
            <form method="GET" action="{{ route('municipality.appointments', absolute: false) }}" class="d-flex gap-2" id="appointments-search-form">
                @if($statusFilter)
                    <input type="hidden" name="status" value="{{ $statusFilter }}">
                @endif
                <input
                    type="text"
                    name="search"
                    value="{{ $search ?? '' }}"
                    id="appointments-search-input"
                    class="form-control form-control-sm"
                    style="min-width: 230px;"
                    placeholder="Search citizen by name or phone number..."
                >
                <button type="submit" class="btn btn-sm btn-outline-primary">Search</button>
                @if(!empty($search))
                    <a href="{{ $statusFilter ? route('municipality.appointments', ['status' => $statusFilter], absolute: false) : route('municipality.appointments', absolute: false) }}" class="btn btn-sm btn-outline-secondary">Clear</a>
                @endif
            </form>
        </div>
        <div class="d-flex flex-wrap gap-2 mt-2">
            <a href="{{ !empty($search) ? route('municipality.appointments', ['search' => $search], absolute: false) : route('municipality.appointments', absolute: false) }}" class="btn btn-sm {{ !$statusFilter ? 'btn-dark' : 'btn-outline-secondary' }}">All</a>
            @foreach(['scheduled', 'confirmed', 'completed', 'cancelled', 'no_show'] as $status)
                @php
                    $statusLabel = match($status) {
                        'no_show' => 'No Show',
                        default => ucfirst($status),
                    };
                @endphp
                <a href="{{ route('municipality.appointments', array_filter(['status' => $status, 'search' => $search ?? null]), absolute: false) }}"
                   class="btn btn-sm {{ $statusFilter === $status ? 'btn-dark' : 'btn-outline-secondary' }}">
                    {{ $statusLabel }}
                </a>
            @endforeach
        </div>
    </div>
    <div class="card-body p-0" id="municipality-booked-appointments-container">
        @include('municipality.partials.appointments-booked-table', ['appointments' => $appointments, 'statusFilter' => $statusFilter])
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        const slotsContainer = document.getElementById('municipality-time-slots-container');
        const bookingsContainer = document.getElementById('municipality-booked-appointments-container');
        const searchInput = document.getElementById('appointments-search-input');
        const searchForm = document.getElementById('appointments-search-form');
        if (!slotsContainer || !bookingsContainer) {
            return;
        }

        let isLoading = false;
        let debounceId = null;
        let isStatusInteracting = false;
        let isRemindSending = false;
        const flashContainer = document.getElementById('appointments-flash-container');

        const showFlash = (type, message) => {
            if (!flashContainer || !message) {
                return;
            }

            const alertClass = type === 'success' ? 'alert-success' : 'alert-warning';
            const iconClass = type === 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle';

            flashContainer.innerHTML =
                '<div class="alert ' + alertClass + ' alert-dismissible fade show">' +
                    '<i class="bi ' + iconClass + ' me-2"></i>' + message +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                '</div>';
        };

        const refreshData = async () => {
            if (isLoading || isStatusInteracting || isRemindSending) {
                return;
            }

            isLoading = true;

            try {
                const currentUrl = new URL(window.location.href);
                if (searchInput) {
                    const trimmed = searchInput.value.trim();
                    if (trimmed !== '') {
                        currentUrl.searchParams.set('search', trimmed);
                    } else {
                        currentUrl.searchParams.delete('search');
                    }
                }
                const params = currentUrl.searchParams.toString();
                const endpoint = '{{ route('municipality.appointments.live', absolute: false) }}' + (params ? ('?' + params) : '');
                const response = await fetch(endpoint, {
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
                if (data.time_slots_html !== undefined) {
                    slotsContainer.innerHTML = data.time_slots_html;
                }
                if (data.booked_appointments_html !== undefined) {
                    bookingsContainer.innerHTML = data.booked_appointments_html;
                }

                if (searchInput) {
                    const nextUrl = '{{ route('municipality.appointments', absolute: false) }}' + (params ? ('?' + params) : '');
                    window.history.replaceState({}, '', nextUrl);
                }
            } catch (error) {
                console.warn('Appointment live refresh failed:', error);
            } finally {
                isLoading = false;
            }
        };

        refreshData();
        setInterval(refreshData, 3000);

        if (searchInput && searchForm) {
            searchInput.addEventListener('input', () => {
                if (debounceId) {
                    clearTimeout(debounceId);
                }

                debounceId = setTimeout(() => {
                    refreshData();
                }, 250);
            });

            searchForm.addEventListener('submit', (event) => {
                event.preventDefault();
                refreshData();
            });
        }

        const statusStyles = {
            scheduled: 'warning text-dark',
            confirmed: 'info text-dark',
            completed: 'success',
            cancelled: 'danger',
            no_show: 'secondary'
        };

        const statusLabel = (value) => {
            if (value === 'no_show') {
                return 'No show';
            }

            return value.charAt(0).toUpperCase() + value.slice(1);
        };

        bookingsContainer.addEventListener('change', (event) => {
            const select = event.target.closest('[data-status-select]');
            if (!select) {
                return;
            }

            const appointmentId = select.getAttribute('data-appointment-id');
            const badge = bookingsContainer.querySelector('[data-status-badge][data-appointment-id="' + appointmentId + '"]');
            const value = select.value;

            if (badge) {
                badge.className = 'badge bg-' + (statusStyles[value] || 'secondary');
                badge.textContent = statusLabel(value);
            }

            const form = select.closest('form[data-status-form]');
            if (form) {
                form.requestSubmit();
            }
        });

        bookingsContainer.addEventListener('focusin', (event) => {
            if (event.target.closest('[data-status-select]')) {
                isStatusInteracting = true;
            }
        });

        bookingsContainer.addEventListener('focusout', (event) => {
            if (event.target.closest('[data-status-select]')) {
                setTimeout(() => {
                    isStatusInteracting = false;
                }, 150);
            }
        });

        bookingsContainer.addEventListener('submit', async (event) => {
            const form = event.target.closest('[data-remind-form]');
            if (!form) {
                return;
            }

            event.preventDefault();

            const button = form.querySelector('[data-remind-button]');
            if (button?.disabled) {
                return;
            }

            const originalHtml = button ? button.innerHTML : '';
            const csrfToken = form.querySelector('input[name="_token"]')?.value
                || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                || '';

            isRemindSending = true;

            if (button) {
                button.disabled = true;
                button.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span>Sending...';
            }

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: new FormData(form),
                });

                const data = await response.json().catch(() => ({}));
                const message = data.message || (response.ok
                    ? 'Reminder email sent.'
                    : 'Could not send reminder email.');

                showFlash(data.success ? 'success' : 'warning', message);
            } catch (error) {
                console.warn('Appointment reminder failed:', error);
                showFlash('warning', 'Could not send reminder email. Please try again.');
            } finally {
                isRemindSending = false;

                if (button) {
                    button.disabled = false;
                    button.innerHTML = originalHtml;
                }
            }
        });

        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                refreshData();
            }
        });

        const userId = window.__notificationUserId;
        if (window.Echo && userId && !window.__municipalityAppointmentsEchoBound) {
            window.__municipalityAppointmentsEchoBound = true;
            window.Echo.private(`App.Models.User.${userId}`)
                .listen('.appointment.changed', () => {
                    refreshData();
                })
                .error((status) => {
                    console.warn('[Municipality Appointments] Echo subscription error', status);
                });
        }
    })();
</script>
@endpush