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
                const response = await fetch('{{ route('citizen.appointments.live', absolute: false) }}', {
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