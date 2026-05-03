@extends('layouts.public')

@section('title', $office->name . ' — E-Services Portal')
@section('page-title', $office->name)

@push('styles')
<style>
    :root {
        --detail-primary: #0a5c4a;
        --detail-dark:    #065f46;
    }

    /* ── Detail card ── */
    .detail-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 1px 8px rgba(0,0,0,0.07);
        padding: 1.4rem;
        margin-bottom: 1.25rem;
        border: 1px solid #e2e8f0;
    }
    .detail-card-title {
        font-weight: 700;
        color: var(--detail-primary);
        font-size: 0.76rem;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.4rem;
        padding-bottom: 0.6rem;
        border-bottom: 1px solid #f1f5f9;
    }

    /* ── Back link ── */
    .back-link {
        color: #64748b;
        text-decoration: none;
        font-size: 0.83rem;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        margin-bottom: 1.1rem;
        transition: color 0.15s;
    }
    .back-link:hover { color: var(--detail-primary); }

    /* ── Office header ── */
    .office-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    .office-icon-box {
        background: var(--detail-primary);
        color: #fff;
        border-radius: 12px;
        width: 52px; height: 52px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.4rem; flex-shrink: 0;
        box-shadow: 0 4px 12px rgba(10,92,74,0.2);
    }
    .office-title { font-weight: 800; color: var(--detail-primary); margin: 0; font-size: 1.25rem; }
    .office-subtitle { color: #64748b; font-size: 0.83rem; margin-top: 0.1rem; }

    /* ── Info rows ── */
    .info-row {
        display: flex;
        gap: 0.65rem;
        margin-bottom: 0.65rem;
        font-size: 0.86rem;
        color: #374151;
        align-items: flex-start;
    }
    .info-row i { color: var(--detail-primary); flex-shrink: 0; margin-top: 2px; }
    .info-row:last-child { margin-bottom: 0; }

    /* ── Map ── */
    #office-detail-map {
        height: 260px;
        border-radius: 9px;
        border: 1px solid #e2e8f0;
    }

    /* ── Working hours ── */
    .hours-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.38rem 0;
        border-bottom: 1px solid #f8fafc;
        font-size: 0.83rem;
    }
    .hours-row:last-child { border-bottom: none; }
    .hours-day {
        font-weight: 600;
        text-transform: capitalize;
        width: 105px;
        color: #374151;
    }
    .hours-day.today { color: var(--detail-primary); }
    .today-tag {
        font-size: 0.64rem;
        background: var(--detail-primary);
        color: #fff;
        border-radius: 4px;
        padding: 0.05rem 0.38rem;
        margin-left: 0.3rem;
    }

    /* ── Services ── */
    .category-heading {
        font-size: 0.68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.9px;
        color: #94a3b8;
        margin: 1rem 0 0.5rem;
        padding-bottom: 0.3rem;
        border-bottom: 1px solid #f1f5f9;
    }
    .service-row {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        padding: 0.85rem 0;
        border-bottom: 1px solid #f8fafc;
    }
    .service-row:last-child { border-bottom: none; }
    .service-name { font-weight: 700; color: #1e293b; font-size: 0.87rem; }
    .service-desc { color: #64748b; font-size: 0.75rem; margin-top: 0.15rem; line-height: 1.45; }
    .service-meta-row {
        font-size: 0.72rem;
        color: #94a3b8;
        margin-top: 0.2rem;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }
    .service-right {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 0.4rem;
        flex-shrink: 0;
        min-width: 100px;
    }
    .service-price {
        font-weight: 800;
        color: var(--detail-primary);
        font-size: 0.9rem;
    }

    /* ── Buttons ── */
    .btn-request {
        background: var(--detail-primary);
        color: #fff;
        border: none;
        border-radius: 8px;
        padding: 0.36rem 0.85rem;
        font-size: 0.76rem;
        font-weight: 700;
        text-decoration: none;
        display: inline-block;
        transition: background 0.15s;
        white-space: nowrap;
    }
    .btn-request:hover { background: var(--detail-dark); color: #fff; }

    .btn-login-request {
        background: transparent;
        color: var(--detail-primary);
        border: 1.5px solid var(--detail-primary);
        border-radius: 8px;
        padding: 0.33rem 0.8rem;
        font-size: 0.76rem;
        font-weight: 700;
        text-decoration: none;
        display: inline-block;
        transition: all 0.15s;
        white-space: nowrap;
    }
    .btn-login-request:hover { background: var(--detail-primary); color: #fff; }

    /* ── No services ── */
    .no-services {
        text-align: center;
        padding: 2rem;
        color: #94a3b8;
    }
</style>
@endpush

@section('content')

{{-- Back --}}
<a href="{{ route('portal', absolute: false) }}" class="back-link">
    <i class="bi bi-arrow-left"></i> Back to all offices
</a>

{{-- Office header --}}
<div class="office-header">
    <div class="office-icon-box">🏛️</div>
    <div>
        <h1 class="office-title">{{ $office->name }}</h1>
        @if($office->municipality)
            <div class="office-subtitle">
                <i class="bi bi-bank me-1"></i>{{ $office->municipality->name }}
            </div>
        @endif
    </div>
</div>

<div class="row g-4">

    {{-- ── Left: info + map + hours ── --}}
    <div class="col-lg-4">

        {{-- Info --}}
        <div class="detail-card">
            <div class="detail-card-title"><i class="bi bi-info-circle"></i> Office Information</div>
            <div class="info-row">
                <i class="bi bi-geo-alt-fill"></i>
                <span>{{ $office->address }}</span>
            </div>
            <div class="info-row">
                <i class="bi bi-telephone-fill"></i>
                <span>{{ $office->contact_info }}</span>
            </div>
            <div class="info-row">
                <i class="bi bi-pin-map-fill"></i>
                <span>
                    {{ $office->latitude }}, {{ $office->longitude }}
                    <a href="https://www.openstreetmap.org/?mlat={{ $office->latitude }}&mlon={{ $office->longitude }}#map=17/{{ $office->latitude }}/{{ $office->longitude }}"
                       target="_blank" class="ms-1 text-primary small">
                        <i class="bi bi-box-arrow-up-right"></i>
                    </a>
                </span>
            </div>
        </div>

        {{-- Map --}}
        <div class="detail-card">
            <div class="detail-card-title"><i class="bi bi-map"></i> Location</div>
            <div id="office-detail-map"></div>
        </div>

        {{-- Working hours --}}
        <div class="detail-card">
            <div class="detail-card-title"><i class="bi bi-clock"></i> Working Hours</div>
            @php $todayKey = strtolower(now()->format('l')); @endphp
            @foreach($days as $day)
                @php $d = $workingHours[$day]; $isToday = ($day === $todayKey); @endphp
                <div class="hours-row">
                    <span class="hours-day {{ $isToday ? 'today' : '' }}">
                        {{ ucfirst($day) }}
                        @if($isToday)<span class="today-tag">today</span>@endif
                    </span>
                    @if($d['is_open'])
                        <span class="text-success fw-semibold" style="font-size:0.81rem;">
                            {{ $d['open_time'] }} – {{ $d['close_time'] }}
                        </span>
                    @else
                        <span class="text-muted" style="font-size:0.79rem;">Closed</span>
                    @endif
                </div>
            @endforeach
        </div>

    </div>

    {{-- ── Right: services ── --}}
    <div class="col-lg-8">
        <div class="detail-card">
            <div class="detail-card-title">
                <i class="bi bi-grid-3x3-gap"></i> Available Services
            </div>

            @php
                $hasServices = $office->categories->some(fn($c) => $c->services->isNotEmpty());
            @endphp

            @if(! $hasServices)
                <div class="no-services">
                    <i class="bi bi-grid fs-2 d-block mb-2 opacity-25"></i>
                    <p class="mb-0 small">No services listed for this office yet.</p>
                </div>
            @else
                @foreach($office->categories as $category)
                    @if($category->services->isNotEmpty())
                        <div class="category-heading">
                            <i class="bi bi-folder me-1"></i>{{ $category->name }}
                        </div>

                        @foreach($category->services as $service)
                            <div class="service-row">
                                <div class="flex-grow-1">
                                    <div class="service-name">{{ $service->name }}</div>

                                    @if($service->description)
                                        <div class="service-desc">
                                            {{ Str::limit($service->description, 110) }}
                                        </div>
                                    @endif

                                    <div class="service-meta-row">
                                        <i class="bi bi-hourglass-split"></i>
                                        {{ $service->duration_days }} day(s) processing time
                                    </div>

                                    @if(!empty($service->required_documents))
                                        <div class="service-meta-row">
                                            <i class="bi bi-paperclip"></i>
                                            Requires: {{ implode(', ', $service->required_documents) }}
                                        </div>
                                    @endif
                                </div>

                                <div class="service-right">
                                    <span class="service-price">
                                        ${{ number_format($service->price, 2) }}
                                    </span>
                                    @auth
                                        @if(Auth::user()->role === 'citizen')
                                            <a href="{{ route('portal.services.request', $service, absolute: false) }}"
                                               class="btn-request">
                                                <i class="bi bi-send me-1"></i>Request
                                            </a>
                                        @endif
                                    @else
                                        <a href="{{ route('portal.services.request', $service, absolute: false) }}"
                                           class="btn-login-request">
                                            <i class="bi bi-lock me-1"></i>Login to Request
                                        </a>
                                    @endauth
                                </div>
                            </div>
                        @endforeach
                    @endif
                @endforeach
            @endif
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
const officeLat = {{ (float) $office->latitude }};
const officeLng = {{ (float) $office->longitude }};

const detailMap = L.map('office-detail-map').setView([officeLat, officeLng], 16);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
    maxZoom: 19,
}).addTo(detailMap);

const detailIcon = L.divIcon({
    className: '',
    html: `<div style="
        background:#0a5c4a;color:#fff;
        border-radius:50% 50% 50% 0;transform:rotate(-45deg);
        width:30px;height:30px;
        display:flex;align-items:center;justify-content:center;
        box-shadow:0 2px 7px rgba(0,0,0,0.25);border:2px solid #fff;">
        <span style="transform:rotate(45deg);font-size:13px;">🏛️</span>
    </div>`,
    iconSize: [30, 30],
    iconAnchor: [15, 30],
    popupAnchor: [0, -33],
});

L.marker([officeLat, officeLng], { icon: detailIcon })
    .addTo(detailMap)
    .bindPopup(`
        <strong style="color:#0a5c4a;">{{ addslashes($office->name) }}</strong><br>
        <small style="color:#64748b;">{{ addslashes($office->address) }}</small>
    `)
    .openPopup();
</script>
@endpush
