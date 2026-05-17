@extends('municipality.layouts.app')
@section('title', 'Two-Factor Authentication')
@section('page-title', 'Two-Factor Authentication')

@section('content')
<div class="row justify-content-center">
<div class="col-lg-7">

@if(session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
@endif

@if($errors->any())
    <div class="alert alert-danger">{{ $errors->first() }}</div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom py-3">
        <h5 class="mb-0">
            <i class="bi bi-shield-lock me-2"></i>Two-Factor Authentication
        </h5>
    </div>
    <div class="card-body p-4">

        @if(auth()->user()->two_factor_confirmed_at)
            {{-- 2FA is ENABLED --}}
            <div class="alert alert-success d-flex align-items-center gap-2">
                <i class="bi bi-shield-check fs-5"></i>
                <div><strong>Two-factor authentication is enabled.</strong> Your account is secured.</div>
            </div>

            @if($recoveryCodes)
            <h6 class="mt-4 mb-2">Recovery Codes</h6>
            <p class="text-muted small">
                Store these codes somewhere safe. Each can be used once to access your account if you lose your authenticator device.
            </p>
            <div class="bg-light rounded p-3 font-monospace small mb-4">
                @foreach($recoveryCodes as $code)
                    <div>{{ $code }}</div>
                @endforeach
            </div>
            @endif

            <form method="POST" action="{{ route('municipality.2fa.disable', absolute: false) }}">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger btn-sm"
                    onclick="return confirm('Are you sure you want to disable 2FA?')">
                    <i class="bi bi-shield-x me-1"></i>Disable Two-Factor Authentication
                </button>
            </form>

        @elseif(auth()->user()->two_factor_secret)
            {{-- Secret generated, awaiting confirmation --}}
            <p class="text-muted">
                Scan this QR code with your authenticator app (Google Authenticator, Authy, etc.),
                then enter the 6-digit code below to confirm.
            </p>

            <div class="text-center my-4">
                {!! $qrCode !!}
            </div>

            @if($setupKey)
            <p class="text-center text-muted small mb-4">
                Can't scan? Enter this key manually: <code>{{ $setupKey }}</code>
            </p>
            @endif

            <form method="POST" action="{{ route('municipality.2fa.confirm', absolute: false) }}">
                @csrf
                <div class="mb-3">
                    <label for="code" class="form-label fw-semibold">Confirmation Code</label>
                    <input
                        type="text"
                        inputmode="numeric"
                        maxlength="6"
                        class="form-control @error('code') is-invalid @enderror"
                        id="code"
                        name="code"
                        placeholder="000000"
                        autofocus
                        style="max-width: 180px; font-size: 1.2rem; letter-spacing: 0.4rem;"
                    >
                    @error('code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle me-1"></i>Confirm & Enable 2FA
                </button>
            </form>

        @else
            {{-- 2FA not started --}}
            <p class="text-muted">
                Two-factor authentication adds an extra layer of security to your account.
                Once enabled, you'll need your phone to sign in.
            </p>
            <form method="POST" action="{{ route('municipality.2fa.enable', absolute: false) }}">
                @csrf
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-shield-plus me-1"></i>Enable Two-Factor Authentication
                </button>
            </form>
        @endif

    </div>
</div>

</div>
</div>
@endsection