<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'E-Services Portal')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>

    @stack('styles')

    <style>
        :root {
            --sidebar-width:    260px;
            --topbar-height:    54px;
            --citizen-bg:       #0a5c4a;
            --citizen-hover:    #0d6e5a;
            --citizen-active:   #065f46;
            --citizen-accent:   #6ee7b7;
            --portal-bg:        #f0f4f8;
        }

        * { box-sizing: border-box; }
        body { margin: 0; background: var(--portal-bg); font-family: 'Segoe UI', sans-serif; }

        /* ── Sidebar ── */
        #citizen-sidebar {
            position: fixed;
            top: 0; left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--citizen-bg);
            display: flex;
            flex-direction: column;
            z-index: 100;
            overflow-y: auto;
        }

        .sidebar-brand {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            text-decoration: none;
            display: block;
        }
        .sidebar-brand h5 {
            margin: 0;
            color: #fff;
            font-weight: 700;
            font-size: 1rem;
        }
        .sidebar-brand h5 span { color: var(--citizen-accent); }
        .sidebar-brand small {
            color: rgba(255,255,255,0.55);
            font-size: 0.75rem;
        }

        /* ── Nav labels ── */
        .sidebar-nav { flex: 1; padding: 1rem 0; }
        .nav-label {
            color: rgba(255,255,255,0.4);
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            padding: 0.75rem 1.5rem 0.25rem;
        }

        /* ── Nav links ── */
        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: rgba(255,255,255,0.78);
            text-decoration: none;
            padding: 0.6rem 1.5rem;
            font-size: 0.875rem;
            border-left: 3px solid transparent;
            transition: background 0.15s, color 0.15s;
        }
        .sidebar-nav a:hover {
            background: var(--citizen-hover);
            color: #fff;
        }
        .sidebar-nav a.active {
            background: var(--citizen-active);
            color: #fff;
            border-left-color: var(--citizen-accent);
            font-weight: 600;
        }
        .sidebar-nav a i { font-size: 1rem; width: 20px; text-align: center; }

        /* ── Badge on nav link ── */
        .nav-badge {
            margin-left: auto;
            background: var(--citizen-accent);
            color: #064e3b;
            border-radius: 10px;
            padding: 0.05rem 0.5rem;
            font-size: 0.68rem;
            font-weight: 700;
        }
        .nav-badge.alert { background: #fbbf24; color: #78350f; }

        /* ── Sidebar footer ── */
        .sidebar-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid rgba(255,255,255,0.1);
        }
        .sidebar-footer .user-name {
            color: #fff;
            font-size: 0.84rem;
            font-weight: 600;
        }
        .sidebar-footer .user-email {
            color: rgba(255,255,255,0.5);
            font-size: 0.72rem;
        }
        .sidebar-footer form button {
            margin-top: 0.6rem;
            width: 100%;
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.15);
            color: rgba(255,255,255,0.8);
            border-radius: 7px;
            padding: 0.35rem 0;
            font-size: 0.8rem;
            cursor: pointer;
            transition: background 0.15s;
        }
        .sidebar-footer form button:hover { background: rgba(255,255,255,0.2); color: #fff; }

        /* ── Guest sidebar footer ── */
        .sidebar-guest-footer {
            padding: 1rem 1.25rem;
            border-top: 1px solid rgba(255,255,255,0.1);
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        /* ── Main content area ── */
        #main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ── Topbar ── */
        #topbar {
            height: var(--topbar-height);
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.5rem;
            position: sticky;
            top: 0;
            z-index: 99;
        }
        #topbar .page-title {
            font-weight: 700;
            font-size: 0.95rem;
            color: var(--citizen-bg);
            margin: 0;
        }
        .topbar-right {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .topbar-stat {
            display: flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.78rem;
            color: #64748b;
            text-decoration: none;
            padding: 0.25rem 0.6rem;
            border-radius: 6px;
            transition: background 0.15s;
        }
        .topbar-stat:hover { background: #f1f5f9; color: #0a5c4a; }
        .topbar-stat .count {
            background: #e2e8f0;
            color: #374151;
            border-radius: 8px;
            padding: 0.05rem 0.4rem;
            font-size: 0.7rem;
            font-weight: 700;
        }
        .topbar-stat .count.has-items { background: #fbbf24; color: #78350f; }

        /* ── Page content ── */
        .page-content { flex: 1; padding: 1.75rem; }

        /* ── Responsive ── */
        @media (max-width: 768px) {
            #citizen-sidebar { width: 0; overflow: hidden; }
            #main-content { margin-left: 0; }
        }
    </style>
</head>
<body>

{{-- ── Sidebar ── --}}
<div id="citizen-sidebar">

    {{-- Brand --}}
    <a href="{{ route('portal', absolute: false) }}" class="sidebar-brand">
        <h5>🏛️ E-Services <span>Portal</span></h5>
        @auth
            <small>{{ Auth::user()->name }}</small>
        @else
            <small>Government Services</small>
        @endauth
    </a>

    {{-- Navigation --}}
    <nav class="sidebar-nav">

        <div class="nav-label">Browse</div>

        <a href="{{ route('portal', absolute: false) }}"
           class="{{ request()->routeIs('portal') && !request()->routeIs('portal.office') ? 'active' : '' }}">
            <i class="bi bi-building"></i> Government Offices
        </a>

        @auth
            @if(Auth::user()->role === 'citizen')

                <div class="nav-label">My Account</div>

                <a href="{{ route('citizen.requests', absolute: false) }}"
                   class="{{ request()->routeIs('citizen.requests') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-text"></i> My Requests
                    @if(($citizenStats['active_requests'] ?? 0) > 0)
                        <span class="nav-badge alert">{{ $citizenStats['active_requests'] }}</span>
                    @endif
                </a>

                <a href="{{ route('citizen.appointments', absolute: false) }}"
                   class="{{ request()->routeIs('citizen.appointments') ? 'active' : '' }}">
                    <i class="bi bi-calendar-check"></i> Appointments
                    @if(($citizenStats['upcoming_appointments'] ?? 0) > 0)
                        <span class="nav-badge alert">{{ $citizenStats['upcoming_appointments'] }}</span>
                    @endif
                </a>

                <a href="{{ route('citizen.notifications', absolute: false) }}"
                   class="{{ request()->routeIs('citizen.notifications') ? 'active' : '' }}">
                    <i class="bi bi-bell"></i> Notifications
                    @if(($citizenStats['unread_notifications'] ?? 0) > 0)
                        <span class="nav-badge alert">{{ $citizenStats['unread_notifications'] }}</span>
                    @endif
                </a>

                <div class="nav-label">More</div>

                <a href="{{ route('citizen.history', absolute: false) }}"
                   class="{{ request()->routeIs('citizen.history') ? 'active' : '' }}">
                    <i class="bi bi-clock-history"></i> History
                </a>

                <a href="{{ route('citizen.chat', absolute: false) }}"
                   class="{{ request()->routeIs('citizen.chat') ? 'active' : '' }}">
                    <i class="bi bi-chat-dots"></i> Chat
                </a>

                <a href="{{ route('citizen.profile', absolute: false) }}"
                   class="{{ request()->routeIs('citizen.profile') ? 'active' : '' }}">
                    <i class="bi bi-person"></i> Profile
                </a>

            @endif
        @endauth

    </nav>

    {{-- Footer --}}
    @auth
        @if(Auth::user()->role === 'citizen')
            <div class="sidebar-footer">
                <div class="user-name">{{ Auth::user()->name }}</div>
                <div class="user-email">{{ Auth::user()->email }}</div>
                <form method="POST" action="{{ route('logout', absolute: false) }}">
                    @csrf
                    <button type="submit">
                        <i class="bi bi-box-arrow-right me-1"></i> Logout
                    </button>
                </form>
            </div>
        @endif
    @else
        <div class="sidebar-guest-footer">
            <a href="{{ route('citizen.login', absolute: false) }}"
               class="btn btn-sm btn-outline-light w-100" style="border-radius:8px; font-size:0.82rem;">
                <i class="bi bi-person me-1"></i> Login
            </a>
            <a href="{{ route('citizen.register', absolute: false) }}"
               class="btn btn-sm w-100"
               style="background:#6ee7b7;color:#064e3b;border-radius:8px;font-size:0.82rem;font-weight:600;">
                <i class="bi bi-person-plus me-1"></i> Register
            </a>
        </div>
    @endauth

</div>

{{-- ── Main content ── --}}
<div id="main-content">

    {{-- Topbar --}}
    <div id="topbar">
        <h6 class="page-title">@yield('page-title', 'Government Offices')</h6>

        <div class="topbar-right">
            @auth
                @if(Auth::user()->role === 'citizen')
                    <a href="{{ route('citizen.requests', absolute: false) }}" class="topbar-stat">
                        <i class="bi bi-file-earmark-text"></i>
                        Requests
                        <span class="count {{ ($citizenStats['active_requests'] ?? 0) > 0 ? 'has-items' : '' }}">
                            {{ $citizenStats['active_requests'] ?? 0 }}
                        </span>
                    </a>
                    <a href="{{ route('citizen.appointments', absolute: false) }}" class="topbar-stat">
                        <i class="bi bi-calendar-check"></i>
                        Appointments
                        <span class="count {{ ($citizenStats['upcoming_appointments'] ?? 0) > 0 ? 'has-items' : '' }}">
                            {{ $citizenStats['upcoming_appointments'] ?? 0 }}
                        </span>
                    </a>
                    <a href="{{ route('citizen.notifications', absolute: false) }}" class="topbar-stat">
                        <i class="bi bi-bell"></i>
                        <span class="count {{ ($citizenStats['unread_notifications'] ?? 0) > 0 ? 'has-items' : '' }}">
                            {{ $citizenStats['unread_notifications'] ?? 0 }}
                        </span>
                    </a>
                @endif
            @endauth
        </div>
    </div>

    {{-- Page content --}}
    <div class="page-content">
        @yield('content')
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

@stack('scripts')
</body>
</html>