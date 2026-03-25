<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Two-Factor Authentication</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f0f4f8;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .challenge-card {
            width: 100%;
            max-width: 420px;
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.10);
        }
        .challenge-header {
            background: #1a3c5e;
            color: #fff;
            border-radius: 12px 12px 0 0;
            padding: 2rem;
            text-align: center;
        }
        .challenge-header h4 { margin: 0; font-weight: 600; }
        .challenge-header p { margin: 0.25rem 0 0; font-size: 0.85rem; opacity: 0.8; }
        .code-input {
            font-size: 1.5rem;
            letter-spacing: 0.5rem;
            text-align: center;
            font-weight: 600;
        }
        .btn-primary { background: #1a3c5e; border-color: #1a3c5e; }
        .btn-primary:hover { background: #14304d; border-color: #14304d; }
        .tab-link {
            color: #1a3c5e;
            cursor: pointer;
            text-decoration: underline;
            background: none;
            border: none;
            padding: 0;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="card challenge-card">
        <div class="challenge-header">
            <h4>🔐 Two-Factor Authentication</h4>
            <p>Enter the code from your authenticator app</p>
        </div>
        <div class="card-body p-4">

            @if($errors->any())
                <div class="alert alert-danger">
                    {{ $errors->first() }}
                </div>
            @endif

            {{-- TOTP Code Form --}}
            <div id="totp-form">
                <p class="text-muted small mb-3">
                    Open your authenticator app (Google Authenticator, Authy, etc.) and enter the 6-digit code.
                </p>
                <form method="POST" action="{{ route('two-factor.login', absolute: false) }}">
                    @csrf
                    <div class="mb-3">
                        <label for="code" class="form-label fw-semibold">Authentication Code</label>
                        <input
                            type="text"
                            inputmode="numeric"
                            pattern="[0-9]*"
                            maxlength="6"
                            class="form-control code-input"
                            id="code"
                            name="code"
                            required
                            autofocus
                            autocomplete="one-time-code"
                            placeholder="000000"
                        >
                    </div>
                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-primary btn-lg">Verify Code</button>
                    </div>
                </form>
                <div class="text-center">
                    <button class="tab-link" onclick="toggleRecovery()">
                        Lost access to your app? Use a recovery code
                    </button>
                </div>
            </div>

            {{-- Recovery Code Form (hidden by default) --}}
            <div id="recovery-form" style="display:none;">
                <p class="text-muted small mb-3">
                    Enter one of your emergency recovery codes.
                </p>
                <form method="POST" action="{{ route('two-factor.login', absolute: false) }}">
                    @csrf
                    <div class="mb-3">
                        <label for="recovery_code" class="form-label fw-semibold">Recovery Code</label>
                        <input
                            type="text"
                            class="form-control"
                            id="recovery_code"
                            name="recovery_code"
                            required
                            autocomplete="one-time-code"
                            placeholder="xxxx-xxxx-xxxx"
                        >
                    </div>
                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-primary btn-lg">Verify Recovery Code</button>
                    </div>
                </form>
                <div class="text-center">
                    <button class="tab-link" onclick="toggleRecovery()">
                        Back to authenticator code
                    </button>
                </div>
            </div>

        </div>
    </div>

    <script>
        function toggleRecovery() {
            const totp = document.getElementById('totp-form');
            const recovery = document.getElementById('recovery-form');
            totp.style.display = totp.style.display === 'none' ? 'block' : 'none';
            recovery.style.display = recovery.style.display === 'none' ? 'block' : 'none';
        }
    </script>
</body>
</html>