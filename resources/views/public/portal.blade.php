@extends('layouts.public')

@section('title', 'Browse Government Offices')
@section('page-title', 'Government Offices')

@push('styles')
<style>
    /* ── Two-column layout: list left, map right ── */
    .offices-layout {
        display: flex;
        gap: 1.5rem;
        align-items: flex-start;
    }

    /* ── Left: office list ── */
    .offices-column {
        flex: 1;
        min-width: 0;
    }

    /* ── Right: map widget ── */
    .map-column {
        width: 380px;
        flex-shrink: 0;
        position: sticky;
        top: calc(54px + 1.75rem);
    }
    #portal-map {
        height: 480px;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 8px rgba(0,0,0,0.07);
    }

    /* ── Search bar ── */
    .search-wrapper { position: relative; margin-bottom: 1.25rem; }
    .search-icon {
        position: absolute;
        left: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        pointer-events: none;
    }
    .search-input {
        width: 100%;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 0.6rem 1rem 0.6rem 2.25rem;
        font-size: 0.87rem;
        background: #fff;
        box-shadow: 0 1px 4px rgba(0,0,0,0.05);
        transition: border-color 0.15s;
    }
    .search-input:focus {
        outline: none;
        border-color: #0a5c4a;
        box-shadow: 0 0 0 3px rgba(10,92,74,0.08);
    }

    /* ── Citizen welcome card ── */
    .welcome-card {
        background: linear-gradient(135deg, #0a5c4a 0%, #065f46 100%);
        border-radius: 12px;
        padding: 1.1rem 1.4rem;
        color: #fff;
        margin-bottom: 1.25rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 0.75rem;
    }
    .welcome-card .wc-title {
        font-weight: 700;
        font-size: 0.95rem;
        margin-bottom: 0.15rem;
    }
    .welcome-card .wc-sub { font-size: 0.78rem; opacity: 0.8; }
    .wc-stats { display: flex; gap: 0.6rem; flex-wrap: wrap; }
    .wc-stat {
        background: rgba(255,255,255,0.15);
        border: 1px solid rgba(255,255,255,0.2);
        border-radius: 8px;
        padding: 0.3rem 0.75rem;
        font-size: 0.76rem;
        font-weight: 600;
        text-align: center;
    }
    .wc-stat.has-items { background: rgba(251,191,36,0.3); border-color: rgba(251,191,36,0.4); }
    .wc-stat .wc-count {
        display: block;
        font-size: 1.1rem;
        font-weight: 800;
        line-height: 1.2;
    }

    /* ── Section heading ── */
    .section-heading {
        font-size: 0.78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        color: #64748b;
        margin-bottom: 0.75rem;
    }

    /* ── Office card ── */
    .office-card {
        display: block;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 1rem 1.1rem;
        margin-bottom: 0.75rem;
        text-decoration: none;
        color: inherit;
        transition: border-color 0.15s, box-shadow 0.15s, transform 0.1s;
    }
    .office-card:hover {
        border-color: #0a5c4a;
        box-shadow: 0 3px 14px rgba(10,92,74,0.1);
        transform: translateY(-1px);
        color: inherit;
    }
    .office-card.highlighted {
        border-color: #0a5c4a;
        background: #f0fdf4;
        box-shadow: 0 3px 14px rgba(10,92,74,0.14);
    }
    .office-card-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.5rem;
        margin-bottom: 0.5rem;
    }
    .office-card-name {
        font-weight: 700;
        font-size: 0.9rem;
        color: #0a5c4a;
    }
    .office-card-meta {
        font-size: 0.77rem;
        color: #64748b;
        display: flex;
        align-items: flex-start;
        gap: 0.35rem;
        margin-bottom: 0.2rem;
        line-height: 1.4;
    }
    .office-card-meta i { flex-shrink: 0; margin-top: 1px; }
    .office-card-footer {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-top: 0.6rem;
        flex-wrap: wrap;
    }
    .badge-service {
        font-size: 0.68rem;
        background: #ecfdf5;
        color: #065f46;
        padding: 0.12rem 0.55rem;
        border-radius: 20px;
        font-weight: 600;
    }
    .badge-hours-open {
        font-size: 0.68rem;
        background: #dcfce7;
        color: #15803d;
        padding: 0.12rem 0.55rem;
        border-radius: 20px;
        font-weight: 600;
    }
    .badge-hours-closed {
        font-size: 0.68rem;
        background: #f1f5f9;
        color: #64748b;
        padding: 0.12rem 0.55rem;
        border-radius: 20px;
    }

    /* ── Map card ── */
    .map-card {
        background: #fff;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 8px rgba(0,0,0,0.07);
        overflow: hidden;
        margin-bottom: 1rem;
    }
    .map-card-header {
        padding: 0.75rem 1rem;
        border-bottom: 1px solid #e2e8f0;
        font-size: 0.8rem;
        font-weight: 700;
        color: #0a5c4a;
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }

    /* ── Map popup ── */
    .leaflet-popup-content { font-size: 0.83rem; min-width: 175px; }
    .popup-name { font-weight: 700; color: #0a5c4a; margin-bottom: 0.2rem; }
    .popup-addr { color: #64748b; font-size: 0.75rem; margin-bottom: 0.5rem; line-height: 1.35; }
    .popup-btn {
        display: block;
        text-align: center;
        background: #0a5c4a;
        color: #fff !important;
        border-radius: 7px;
        padding: 0.32rem 0.6rem;
        font-size: 0.76rem;
        font-weight: 600;
        text-decoration: none;
    }
    .popup-btn:hover { background: #065f46; }

    /* ── Empty state ── */
    .empty-state {
        text-align: center;
        padding: 3rem 1rem;
        color: #94a3b8;
        background: #fff;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
    }

    @media (max-width: 992px) {
        .offices-layout { flex-direction: column; }
        .map-column { width: 100%; position: static; }
        #portal-map { height: 300px; }
    }
</style>
@endpush

@section('content')

{{-- ── Citizen welcome card ── --}}
@auth
    @if(Auth::user()->role === 'citizen')
        <div class="welcome-card">
            <div>
                <div class="wc-title">Welcome back, {{ Auth::user()->name }} 👋</div>
                <div class="wc-sub">Browse offices and request services below.</div>
            </div>
            <div class="wc-stats">
                <div class="wc-stat {{ ($citizenStats['active_requests'] ?? 0) > 0 ? 'has-items' : '' }}">
                    <span class="wc-count">{{ $citizenStats['active_requests'] ?? 0 }}</span>
                    Active Requests
                </div>
                <div class="wc-stat {{ ($citizenStats['upcoming_appointments'] ?? 0) > 0 ? 'has-items' : '' }}">
                    <span class="wc-count">{{ $citizenStats['upcoming_appointments'] ?? 0 }}</span>
                    Appointments
                </div>
                <div class="wc-stat {{ ($citizenStats['unread_notifications'] ?? 0) > 0 ? 'has-items' : '' }}">
                    <span class="wc-count" data-unread-notifications>{{ $citizenStats['unread_notifications'] ?? 0 }}</span>
                    Notifications
                </div>
            </div>
        </div>
    @endif
@endauth

{{-- ── Search ── --}}
<form method="GET" action="{{ route('portal', absolute: false) }}">
    <div class="search-wrapper">
        <i class="bi bi-search search-icon"></i>
        <input
            type="text"
            name="search"
            class="search-input"
            placeholder="Search offices, municipalities, addresses..."
            value="{{ $searchQuery }}"
            autocomplete="off"
        >
    </div>
</form>

{{-- ── Results header ── --}}
<div class="section-heading">
    @if($searchQuery)
        {{ $filteredOffices->count() }} result(s) for "{{ $searchQuery }}"
        <a href="{{ route('portal', absolute: false) }}" class="ms-2 text-muted fw-normal text-lowercase text-decoration-none">
            <i class="bi bi-x-circle me-1"></i>clear
        </a>
    @else
        {{ $offices->count() }} office(s) available
    @endif
</div>

{{-- ── Two-column layout ── --}}
<div class="offices-layout">

    {{-- Office list --}}
    <div class="offices-column">
        @forelse($filteredOffices as $office)
            @php
                $serviceCount = $office->categories->sum(fn($cat) => $cat->services->count());
                $todayKey     = strtolower(now()->format('l'));
                $todayHours   = is_array($office->working_hours)
                    ? ($office->working_hours[$todayKey] ?? null)
                    : null;
                $todayIsOpen = is_array($todayHours)
                    ? ($todayHours['is_open'] ?? false)
                    : ($todayHours && $todayHours !== 'closed');
                $todayLabel = is_array($todayHours)
                    ? (($todayHours['open_time'] ?? '') . ' - ' . ($todayHours['close_time'] ?? ''))
                    : $todayHours;
            @endphp

            <a href="{{ route('portal.office', $office, absolute: false) }}"
               class="office-card"
               id="office-card-{{ $office->id }}"
               data-office-id="{{ $office->id }}"
               data-lat="{{ $office->latitude }}"
               data-lng="{{ $office->longitude }}">

                <div class="office-card-top">
                    <div>
                        <div class="office-card-name">
                            <i class="bi bi-building me-1"></i>{{ $office->name }}
                        </div>
                        @if($office->municipality)
                            <div class="office-card-meta">
                                <i class="bi bi-bank"></i>
                                <span>{{ $office->municipality->name }}</span>
                            </div>
                        @endif
                        <div class="office-card-meta">
                            <i class="bi bi-geo-alt"></i>
                            <span>{{ $office->address }}</span>
                        </div>
                    </div>
                    <i class="bi bi-chevron-right text-muted mt-1" style="font-size:0.8rem;"></i>
                </div>

                <div class="office-card-footer">
                    @if($serviceCount > 0)
                        <span class="badge-service">
                            <i class="bi bi-grid me-1"></i>{{ $serviceCount }} service(s)
                        </span>
                    @endif

                    {{-- @if($todayHours)
                        @if($todayHours['is_open'])
                            <span class="badge-hours-open">
                                <i class="bi bi-clock me-1"></i>
                                Today: {{ $todayHours['open_time'] }} – {{ $todayHours['close_time'] }}
                            </span>
                        @else
                            <span class="badge-hours-closed">Closed today</span>
                        @endif --}}
                        @if($todayIsOpen)
    <span class="badge-hours-open">
        <i class="bi bi-clock me-1"></i>
        Today: {{ $todayLabel }}
    </span>
@elseif($todayHours)
    <span class="badge-hours-closed">
        <i class="bi bi-clock me-1"></i>
        Closed today
    </span>
@endif
              
                </div>
            </a>
        @empty
            <div class="empty-state">
                <i class="bi bi-building fs-1 d-block mb-2 opacity-25"></i>
                <p class="mb-1">No offices found{{ $searchQuery ? ' matching "' . $searchQuery . '"' : '' }}.</p>
                @if($searchQuery)
                    <a href="{{ route('portal', absolute: false) }}" class="btn btn-sm btn-outline-secondary mt-2">
                        Clear search
                    </a>
                @endif
            </div>
        @endforelse
    </div>

    {{-- Map widget --}}
    <div class="map-column">
        <div class="map-card">
            <div class="map-card-header">
                <i class="bi bi-map"></i> Office Locations
            </div>
            <div id="portal-map"></div>
        </div>
        <p class="text-muted small text-center">
            <i class="bi bi-info-circle me-1"></i>
            Click a pin or office card to interact.
        </p>
    </div>

</div>
@endsection

@push('scripts')
<script>
const officesData = @json($officesForMap);

const portalMap = L.map('portal-map').setView([33.8938, 35.5018], 8);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
    maxZoom: 19,
}).addTo(portalMap);

const officeIcon = L.divIcon({
    className: '',
    html: `<div style="
        background:#0a5c4a;color:#fff;
        border-radius:50% 50% 50% 0;transform:rotate(-45deg);
        width:28px;height:28px;
        display:flex;align-items:center;justify-content:center;
        box-shadow:0 2px 5px rgba(0,0,0,0.25);border:2px solid #fff;">
        <span style="transform:rotate(45deg);font-size:12px;">🏛️</span>
    </div>`,
    iconSize: [28, 28],
    iconAnchor: [14, 28],
    popupAnchor: [0, -30],
});

const markerMap = {};

officesData.forEach(function (office) {
    const marker = L.marker([office.latitude, office.longitude], { icon: officeIcon })
        .addTo(portalMap);

    marker.bindPopup(`
        <div class="popup-name">${office.name}</div>
        <div class="popup-addr">${office.address}</div>
        <a href="${office.url}" class="popup-btn">View Office →</a>
    `);

    marker.on('click', () => highlightCard(office.id));
    markerMap[office.id] = marker;
});

// Card click → pan map to office
document.querySelectorAll('.office-card').forEach(function (card) {
    card.addEventListener('click', function (e) {
        const id  = parseInt(this.dataset.officeId);
        const lat = parseFloat(this.dataset.lat);
        const lng = parseFloat(this.dataset.lng);
        if (markerMap[id]) {
            portalMap.setView([lat, lng], 15);
            markerMap[id].openPopup();
        }
    });
});

// Marker click → highlight card in list
function highlightCard(officeId) {
    document.querySelectorAll('.office-card').forEach(c => c.classList.remove('highlighted'));
    const card = document.getElementById('office-card-' + officeId);
    if (card) {
        card.classList.add('highlighted');
        card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
}

// Search on Enter
document.querySelector('.search-input').addEventListener('keydown', function (e) {
    if (e.key === 'Enter') this.closest('form').submit();
});
</script>
@endpush
