@extends('municipality.layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
@if(auth()->user()->isOfficeStaff())
    <p class="text-muted small mb-4 mb-sm-3">
        You use the same portal as municipality administrators. Office setup and the service catalog are <strong>view only</strong>;
        your day-to-day work is under <a href="{{ route('municipality.requests', absolute: false) }}">Requests</a>,
        <a href="{{ route('municipality.appointments', absolute: false) }}">Appointments</a>, and
        <a href="{{ route('municipality.chat', absolute: false) }}">Chat</a>.
    </p>
@endif
<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                    <i class="bi bi-file-earmark-text fs-4 text-primary"></i>
                </div>
                <div>
                    <div class="text-muted small">Total Requests</div>
                    <div class="fw-bold fs-4" id="stat-total-requests">{{ $stats['total_requests'] }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-warning bg-opacity-10 p-3">
                    <i class="bi bi-hourglass-split fs-4 text-warning"></i>
                </div>
                <div>
                    <div class="text-muted small">Pending</div>
                    <div class="fw-bold fs-4" id="stat-pending">{{ $stats['pending'] }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success bg-opacity-10 p-3">
                    <i class="bi bi-calendar-check fs-4 text-success"></i>
                </div>
                <div>
                    <div class="text-muted small">Appointments Today</div>
                    <div class="fw-bold fs-4" id="stat-appointments-today">{{ $stats['appointments_today'] }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success bg-opacity-10 p-3">
                    <i class="bi bi-cash-stack fs-4 text-success"></i>
                </div>
                <div>
                    <div class="text-muted small">Total Revenue</div>
                    <div class="fw-bold fs-4" id="stat-total-revenue">{{ $stats['total_revenue'] }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-info bg-opacity-10 p-3">
                    <i class="bi bi-chat-dots fs-4 text-info"></i>
                </div>
                <div>
                    <div class="text-muted small">Unread Messages</div>
                    <div class="fw-bold fs-4" id="stat-unread-messages">{{ $stats['unread_messages'] }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 pt-2"><i class="bi bi-exclamation-circle me-2"></i>Pending Service Requests</h6>
        <a href="{{ route('municipality.requests', ['status' => 'Pending'], absolute: false) }}" class="btn btn-sm btn-outline-primary">View All</a>
    </div>
    <div class="card-body p-0">
        @if($pendingRequests->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 table-sm">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Request ID</th>
                            <th>Citizen</th>
                            <th>Service</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingRequests as $request)
                            <tr>
                                <td class="ps-4 text-muted small">#{{ $request->id }}</td>
                                <td class="small">{{ $request->citizen->name }}</td>
                                <td class="small">{{ $request->service->name }}</td>
                                <td class="small">
                                    <span class="badge bg-{{ match($request->status) {
                                        'Pending' => 'warning text-dark',
                                        'In Review' => 'info text-dark',
                                        'Missing Documents' => 'secondary',
                                        default => 'secondary'
                                    } }}">{{ $request->status }}</span>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('municipality.requests.show', $request, absolute: false) }}" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-4 text-muted">
                <i class="bi bi-check-circle fs-2 d-block mb-2 text-success"></i>
                <p class="mb-0 small">All requests are up to date!</p>
            </div>
        @endif
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 pt-2">Today's Appointments</h6>
        <a href="{{ route('municipality.appointments', absolute: false) }}" class="btn btn-sm btn-outline-primary">View All</a>
    </div>
    <div class="card-body p-0" id="dashboard-today-appointments">
        @include('municipality.partials.dashboard-today-appointments', ['todayAppointments' => $todayAppointments])
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        let isLoading = false;

        const refresh = async () => {
            if (isLoading) {
                return;
            }

            isLoading = true;

            try {
                const response = await fetch('{{ route('municipality.dashboard.live', absolute: false) }}', {
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

                if (data.stats) {
                    const el = (id) => document.getElementById(id);
                    if (el('stat-total-requests')) el('stat-total-requests').textContent = data.stats.total_requests;
                    if (el('stat-pending')) el('stat-pending').textContent = data.stats.pending;
                    if (el('stat-appointments-today')) el('stat-appointments-today').textContent = data.stats.appointments_today;
                    if (el('stat-total-revenue')) el('stat-total-revenue').textContent = data.stats.total_revenue;
                    if (el('stat-unread-messages')) el('stat-unread-messages').textContent = data.stats.unread_messages;
                }

                if (data.today_appointments_html !== undefined) {
                    const container = document.getElementById('dashboard-today-appointments');
                    if (container) {
                        container.innerHTML = data.today_appointments_html;
                    }
                }
            } catch (error) {
                console.warn('Dashboard live refresh failed:', error);
            } finally {
                isLoading = false;
            }
        };

        refresh();
        setInterval(refresh, 2000);

        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                refresh();
            }
        });

        const userId = window.__notificationUserId;
        if (window.Echo && userId && !window.__municipalityDashboardEchoBound) {
            window.__municipalityDashboardEchoBound = true;
            window.Echo.private(`App.Models.User.${userId}`)
                .listen('.appointment.changed', () => {
                    refresh();
                })
                .listen('.revenue.updated', (payload) => {
                    const formatted = payload?.formatted_total;
                    if (formatted) {
                        const el = document.getElementById('stat-total-revenue');
                        if (el) {
                            el.textContent = formatted;
                        }
                    }
                    refresh();
                })
                .error((status) => {
                    console.warn('[Municipality Dashboard] Echo subscription error', status);
                });
        }
    })();
</script>
@endpush
