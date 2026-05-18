@extends('layouts.public')

@section('title', 'Request Tracking')
@section('page-title', 'Request Tracking')

@push('styles')
<style>
    .qr-track-card {
        background: #fff;
        border: 1px solid #d9eee7;
        border-radius: 12px;
        box-shadow: 0 1px 8px rgba(15, 23, 42, 0.07);
        max-width: 680px;
        margin: 0 auto;
        overflow: hidden;
    }

    .qr-track-head {
        background: #0a5c4a;
        color: #fff;
        padding: 1rem 1.25rem;
    }

    .qr-track-body {
        padding: 1.25rem;
    }

    .qr-track-status {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        background: #ecfdf5;
        color: #0a5c4a;
        border: 1px solid #bbf7d0;
        border-radius: 999px;
        padding: 0.35rem 0.75rem;
        font-weight: 700;
        font-size: 0.82rem;
    }

    .qr-track-row {
        border-bottom: 1px solid #f1f5f9;
        padding: 0.8rem 0;
    }

    .qr-track-row:last-child {
        border-bottom: 0;
    }

    .qr-track-label {
        display: block;
        color: #64748b;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        margin-bottom: 0.2rem;
    }

    .qr-track-value {
        color: #1f2937;
        font-weight: 600;
        word-break: break-word;
    }
</style>
@endpush

@section('content')
<div class="qr-track-card">
    <div class="qr-track-head">
        <div class="fw-bold">Request #{{ $tracking['id'] }}</div>
        <div class="small opacity-75">Live tracking from QR code</div>
    </div>
    <div class="qr-track-body">
        <div class="mb-3">
            <span class="qr-track-status">
                <i class="bi bi-activity"></i>{{ $tracking['status'] }}
            </span>
        </div>

        <div class="qr-track-row">
            <span class="qr-track-label">Service</span>
            <div class="qr-track-value">{{ $tracking['service'] }}</div>
        </div>
        <div class="qr-track-row">
            <span class="qr-track-label">Office</span>
            <div class="qr-track-value">{{ $tracking['office'] }}</div>
        </div>
        <div class="qr-track-row">
            <span class="qr-track-label">Citizen</span>
            <div class="qr-track-value">{{ $tracking['citizen'] }}</div>
        </div>
        <div class="qr-track-row">
            <span class="qr-track-label">Submitted</span>
            <div class="qr-track-value">{{ \Carbon\Carbon::parse($tracking['created_at'])->format('M d, Y h:i A') }}</div>
        </div>
        <div class="qr-track-row">
            <span class="qr-track-label">Last Updated</span>
            <div class="qr-track-value">{{ \Carbon\Carbon::parse($tracking['updated_at'])->format('M d, Y h:i A') }}</div>
        </div>
        <div class="qr-track-row">
            <span class="qr-track-label">Tracking Token</span>
            <div class="qr-track-value"><code>{{ $tracking['qr_token'] }}</code></div>
        </div>
    </div>
</div>
@endsection
