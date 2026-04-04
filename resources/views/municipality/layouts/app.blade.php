<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.broadcasting')
    <title>@yield('title', 'Municipality Dashboard')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --sidebar-width: 260px;
            --sidebar-bg: #1a3c5e;
            --sidebar-active: #14304d;
            --sidebar-hover: #1f4a74;
            --topbar-height: 60px;
        }

        body { background: #f0f4f8; margin: 0; }

        /* Sidebar */
        #sidebar {
            position: fixed;
            top: 0; left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--sidebar-bg);
            display: flex;
            flex-direction: column;
            z-index: 100;
            transition: width 0.2s;
        }

        .sidebar-brand {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            color: #fff;
        }
        .sidebar-brand h5 { margin: 0; font-weight: 700; font-size: 1rem; }
        .sidebar-brand small { opacity: 0.7; font-size: 0.78rem; }

        .sidebar-nav { flex: 1; padding: 1rem 0; overflow-y: auto; }

        .sidebar-nav .nav-label {
            color: rgba(255,255,255,0.45);
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            padding: 0.75rem 1.5rem 0.25rem;
        }

        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            padding: 0.65rem 1.5rem;
            font-size: 0.9rem;
            transition: background 0.15s, color 0.15s;
            border-left: 3px solid transparent;
        }

        .sidebar-nav a:hover {
            background: var(--sidebar-hover);
            color: #fff;
        }

        .sidebar-nav a.active {
            background: var(--sidebar-active);
            color: #fff;
            border-left-color: #4da6ff;
            font-weight: 600;
        }

        .sidebar-nav a i { font-size: 1.1rem; width: 20px; text-align: center; }

        .sidebar-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid rgba(255,255,255,0.1);
        }
        .sidebar-footer .user-info { color: rgba(255,255,255,0.8); font-size: 0.82rem; }
        .sidebar-footer .user-info strong { display: block; color: #fff; }

        /* Main content */
        #main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Topbar */
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

        #topbar .page-title { font-weight: 600; font-size: 1rem; color: #1a3c5e; margin: 0; }

        .topbar-actions { display: flex; align-items: center; gap: 1rem; }
        .topbar-actions .btn-logout {
            background: none;
            border: 1px solid #dc3545;
            color: #dc3545;
            font-size: 0.82rem;
            padding: 0.3rem 0.8rem;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.15s;
        }
        .topbar-actions .btn-logout:hover { background: #dc3545; color: #fff; }

        /* Page content */
        .page-content { flex: 1; padding: 1.75rem; }

        /* 2FA warning banner */
        .twofa-banner {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.88rem;
        }

        @media (max-width: 768px) {
            #sidebar { width: 0; overflow: hidden; }
            #main-content { margin-left: 0; }
        }
    </style>
    @stack('styles')
</head>
<body>

    {{-- Sidebar --}}
    <div id="sidebar">
        <div class="sidebar-brand">
            <h5>🏛️ Municipality Portal</h5>
            <small>{{ auth()->user()->municipality?->name ?? 'Office Management' }}</small>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-label">Main</div>

            <a href="{{ route('municipality.dashboard', absolute: false) }}"
               class="{{ request()->routeIs('municipality.dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>

            <a href="{{ route('municipality.office-profile', absolute: false) }}"
               class="{{ request()->routeIs('municipality.office-profile*') ? 'active' : '' }}">
                <i class="bi bi-building"></i> Office Profile
            </a>

            <div class="nav-label">Services</div>

            <a href="{{ route('municipality.services', absolute: false) }}"
               class="{{ request()->routeIs('municipality.services*') ? 'active' : '' }}">
                <i class="bi bi-grid-3x3-gap"></i> Services
            </a>

            <a href="{{ route('municipality.requests', absolute: false) }}"
               class="{{ request()->routeIs('municipality.requests*') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-text"></i> Requests
            </a>

            <a href="{{ route('municipality.appointments', absolute: false) }}"
               class="{{ request()->routeIs('municipality.appointments') ? 'active' : '' }}">
                <i class="bi bi-calendar-check"></i> Appointments
            </a>

            <div class="nav-label">Communication</div>

            <a href="{{ route('municipality.feedback', absolute: false) }}"
               class="{{ request()->routeIs('municipality.feedback') ? 'active' : '' }}">
                <i class="bi bi-star"></i> Feedback
            </a>

            <a href="{{ route('municipality.chat', absolute: false) }}"
               class="{{ request()->routeIs('municipality.chat') ? 'active' : '' }}">
                <i class="bi bi-chat-dots"></i> Chat
            </a>

            <div class="nav-label">Security</div>

            <a href="{{ route('municipality.2fa.setup', absolute: false) }}"
               class="{{ request()->routeIs('municipality.2fa.setup') ? 'active' : '' }}">
                <i class="bi bi-shield-lock"></i> Two-Factor Auth
                @if(! auth()->user()->two_factor_confirmed_at)
                    <span class="badge bg-warning text-dark ms-auto">Off</span>
                @else
                    <span class="badge bg-success ms-auto">On</span>
                @endif
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="user-info">
                <strong>{{ auth()->user()->name }}</strong>
                {{ auth()->user()->email }}
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div id="main-content">
        <div id="topbar">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <h6 class="page-title mb-0">@yield('page-title', 'Dashboard')</h6>
                @if(auth()->user()->isOfficeStaff())
                    <span class="badge rounded-pill bg-secondary bg-opacity-25 text-secondary border" style="font-size:0.72rem; font-weight:600;">
                        Desk staff · catalog read-only
                    </span>
                @endif
            </div>
            <div class="topbar-actions">
                <form method="POST" action="{{ route('web.logout', absolute: false) }}">
                    @csrf
                    <button type="submit" class="btn-logout">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </button>
                </form>
            </div>
        </div>

        <div class="page-content">
            @if(session('warning'))
                <div class="alert alert-warning d-flex align-items-center gap-2 mb-3" role="alert">
                    <i class="bi bi-info-circle flex-shrink-0"></i>
                    <span>{{ session('warning') }}</span>
                </div>
            @endif

            @if(! auth()->user()->two_factor_confirmed_at)
                <div class="twofa-banner">
                    <span>⚠️ <strong>Two-factor authentication is not enabled.</strong> Secure your account now.</span>
                    <a href="{{ route('municipality.2fa.setup', absolute: false) }}" class="btn btn-sm btn-warning">Enable 2FA</a>
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</body>
</html>