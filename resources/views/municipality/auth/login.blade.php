<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Municipality Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f0f4f8;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            width: 100%;
            max-width: 420px;
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.10);
        }
        .login-header {
            background: #1a3c5e;
            color: #fff;
            border-radius: 12px 12px 0 0;
            padding: 2rem;
            text-align: center;
        }
        .login-header h4 {
            margin: 0;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        .login-header p {
            margin: 0.25rem 0 0;
            font-size: 0.85rem;
            opacity: 0.8;
        }
        .btn-primary {
            background: #1a3c5e;
            border-color: #1a3c5e;
        }
        .btn-primary:hover {
            background: #14304d;
            border-color: #14304d;
        }
    </style>
</head>
<body>
    <div class="card login-card">
        <div class="login-header">
            <h4>🏛️ Municipality Portal</h4>
            <p>Sign in to your office account</p>
        </div>
        <div class="card-body p-4">

            {{-- Session-based deactivated error --}}
            @if(session('login_error') === 'deactivated')
                <div class="alert alert-warning d-flex align-items-center" role="alert">
                    <i class="me-2">⚠️</i>
                    <div>Account deactivated. Please contact your administrator.</div>
                </div>
            @endif

            {{-- Validation errors (wrong credentials, access denied) --}}
            @if($errors->any())
                <div class="alert alert-danger" role="alert">
                    {{ $errors->first() }}
                </div>
            @endif

            {{-- Status message (e.g. logged out) --}}
            @if(session('status'))
                <div class="alert alert-success" role="alert">
                    {{ session('status') }}
                </div>
            @endif

            @if(! empty($otherPortalUser))
                <div class="alert alert-info small" role="alert">
                    <p class="mb-2">You still have an active <strong>{{ $otherPortalUser->role }}</strong> session (e.g. citizen 2FA in progress). You can sign in here with a municipality account (that will end the current session), or sign out first.</p>
                    <form method="POST" action="{{ route('web.logout', absolute: false) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-secondary">Sign out of {{ $otherPortalUser->role }} account</button>
                    </form>
                </div>
            @endif

            <form method="POST" action="{{ route('municipality.login.store', absolute: false) }}">
                @csrf

                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold">Email Address</label>
                    <input
                        type="email"
                        class="form-control @error('email') is-invalid @enderror"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        autocomplete="email"
                        placeholder="office@municipality.gov"
                    >
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label fw-semibold">Password</label>
                    <input
                        type="password"
                        class="form-control @error('password') is-invalid @enderror"
                        id="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        placeholder="••••••••"
                    >
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                    <label class="form-check-label" for="remember">Remember me</label>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg">
                        Sign In
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>