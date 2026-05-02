<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Citizen Login — E-Services Portal</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        :root {
            --teal-dark:   #0a5c4a;
            --teal-main:   #0d6e5a;
            --teal-light:  #ecfdf5;
            --teal-accent: #6ee7b7;
        }

        body {
            background: linear-gradient(135deg, #0a5c4a 0%, #065f46 60%, #f0f4f8 60%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }

        .auth-card {
            width: 100%;
            max-width: 440px;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 40px rgba(0,0,0,0.18);
            overflow: hidden;
        }

        .auth-card-header {
            background: linear-gradient(135deg, #0a5c4a 0%, #0d6e5a 100%);
            padding: 1.75rem 2rem 1.5rem;
            color: #fff;
        }
        .auth-card-header .portal-brand {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--teal-accent);
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }
        .auth-card-header h4 {
            margin: 0 0 0.25rem;
            font-weight: 800;
            font-size: 1.4rem;
        }
        .auth-card-header p {
            margin: 0;
            font-size: 0.82rem;
            opacity: 0.75;
        }

        .auth-card-body { padding: 1.75rem 2rem 2rem; }

        /* ── Inputs ── */
        .form-label {
            font-size: 0.82rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.3rem;
        }
        .form-control {
            border-radius: 9px;
            border: 1.5px solid #e2e8f0;
            padding: 0.55rem 0.85rem;
            font-size: 0.875rem;
            transition: border-color 0.15s, box-shadow 0.15s;
        }
        .form-control:focus {
            border-color: var(--teal-main);
            box-shadow: 0 0 0 3px rgba(13,110,90,0.1);
        }

        /* ── Submit button ── */
        .btn-citizen {
            background: var(--teal-dark);
            color: #fff;
            border: none;
            border-radius: 9px;
            padding: 0.6rem 1rem;
            font-size: 0.88rem;
            font-weight: 700;
            width: 100%;
            transition: background 0.15s;
        }
        .btn-citizen:hover { background: #065f46; color: #fff; }

        /* ── Divider ── */
        .divider {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin: 1.25rem 0;
        }
        .divider hr { flex: 1; border-color: #e2e8f0; margin: 0; }
        .divider span { font-size: 0.75rem; color: #94a3b8; white-space: nowrap; }

        /* ── Social buttons ── */
        .btn-social {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.6rem;
            width: 100%;
            border-radius: 9px;
            padding: 0.55rem 1rem;
            font-size: 0.84rem;
            font-weight: 600;
            border: 1.5px solid #e2e8f0;
            background: #fff;
            color: #374151;
            text-decoration: none;
            transition: background 0.15s, border-color 0.15s, box-shadow 0.15s;
        }
        .btn-social:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
            color: #1e293b;
            box-shadow: 0 1px 4px rgba(0,0,0,0.07);
        }
        .btn-social svg { flex-shrink: 0; }

        /* ── Links ── */
        .auth-link { color: var(--teal-main); font-weight: 600; text-decoration: none; }
        .auth-link:hover { color: var(--teal-dark); text-decoration: underline; }

        .forgot-link {
            color: #64748b;
            font-size: 0.8rem;
            text-decoration: none;
        }
        .forgot-link:hover { color: var(--teal-main); }
    </style>
</head>
<body>

<div class="auth-card">

    {{-- Header --}}
    <div class="auth-card-header">
        <div class="portal-brand">
            <i class="bi bi-building"></i> E-Services Portal
        </div>
        <h4>Welcome back</h4>
        <p>Sign in to access government e-services.</p>
    </div>

    {{-- Body --}}
    <div class="auth-card-body">

        {{-- Status message --}}
        @if(session('status'))
            <div class="alert alert-success py-2 small mb-3">
                <i class="bi bi-check-circle me-1"></i>{{ session('status') }}
            </div>
        @endif

        {{-- Errors --}}
        @if($errors->any())
            <div class="alert alert-danger py-2 small mb-3">
                <i class="bi bi-exclamation-triangle me-1"></i>{{ $errors->first() }}
            </div>
        @endif

        {{-- Login form --}}
        <form method="POST" action="{{ route('citizen.login.store', absolute: false) }}">
            @csrf

            <div class="mb-3">
                <label for="email" class="form-label">Email address</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    class="form-control @error('email') is-invalid @enderror"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    autocomplete="email"
                    placeholder="you@example.com"
                >
            </div>

            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label for="password" class="form-label mb-0">Password</label>
                    @if(Route::has('citizen.password.request'))
                        <a href="{{ route('citizen.password.request', absolute: false) }}"
                           class="forgot-link">Forgot password?</a>
                    @endif
                </div>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="form-control @error('password') is-invalid @enderror"
                    required
                    autocomplete="current-password"
                    placeholder="••••••••"
                >
            </div>

            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                <label class="form-check-label small text-muted" for="remember">
                    Remember me
                </label>
            </div>

            <button type="submit" class="btn-citizen">
                <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
            </button>
        </form>

        {{-- Divider --}}
        <div class="divider">
            <hr><span>or continue with</span><hr>
        </div>

        {{-- Social buttons --}}
        <div class="d-flex flex-column gap-2">
            <a href="{{ route('social.redirect', 'google', absolute: false) }}" class="btn-social">
                {{-- Google SVG (official colors) --}}
                <svg width="18" height="18" viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                </svg>
                Continue with Google
            </a>

           
        </div>

        {{-- Register link --}}
        <p class="text-center text-muted small mt-4 mb-0">
            Don't have an account?
            <a href="{{ route('citizen.register', absolute: false) }}" class="auth-link">Create one</a>
        </p>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>