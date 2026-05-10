@extends('municipality.layouts.app')
@section('title', $eventAlert->title)
@section('page-title', 'Event Alert Details')

@section('content')

<div class="mb-4">
    <a href="{{ route('municipality.event-alerts.index', absolute: false) }}"
       class="text-decoration-none text-muted small">
        <i class="bi bi-arrow-left me-1"></i>Back to Event Alerts
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3 d-flex align-items-center
                        justify-content-between">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-megaphone me-2 text-primary"></i>
                    {{ $eventAlert->title }}
                </h6>
                @if($eventAlert->occasion)
                    <span class="badge bg-primary bg-opacity-10 text-primary">
                        {{ $eventAlert->occasion }}
                    </span>
                @endif
            </div>
            <div class="card-body p-4">

                <div class="row g-3 mb-4">
                    <div class="col-sm-6">
                        <div class="text-muted small fw-semibold text-uppercase"
                             style="font-size:0.7rem; letter-spacing:0.5px;">
                            Office
                        </div>
                        <div class="fw-semibold">{{ $eventAlert->office->name }}</div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted small fw-semibold text-uppercase"
                             style="font-size:0.7rem; letter-spacing:0.5px;">
                            Created By
                        </div>
                        <div class="fw-semibold">{{ $eventAlert->creator->name }}</div>
                        <div class="text-muted small">
                            {{ $eventAlert->created_at->format('M j, Y \a\t g:i A') }}
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted small fw-semibold text-uppercase"
                             style="font-size:0.7rem; letter-spacing:0.5px;">
                            Event Date & Time
                        </div>
                        <div class="fw-semibold">
                            {{ $eventAlert->event_date->format('l, F j, Y') }}
                        </div>
                        <div class="text-muted small">
                            {{ $eventAlert->event_date->format('g:i A') }}
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted small fw-semibold text-uppercase"
                             style="font-size:0.7rem; letter-spacing:0.5px;">
                            Location
                        </div>
                        <div class="fw-semibold">{{ $eventAlert->place }}</div>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="text-muted small fw-semibold text-uppercase mb-2"
                         style="font-size:0.7rem; letter-spacing:0.5px;">
                        Description
                    </div>
                    <div class="bg-light rounded p-3 small" style="white-space:pre-wrap;">
                        {{ $eventAlert->description }}
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2 p-3 bg-success bg-opacity-10
                            rounded border border-success border-opacity-25">
                    <i class="bi bi-check-circle-fill text-success fs-5"></i>
                    <div>
                        <div class="fw-semibold small text-success">
                            Sent to {{ $eventAlert->notified_count }} citizen(s)
                        </div>
                        <div class="text-muted small">
                            Each received an email + in-app notification.
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-bar-chart me-2 text-primary"></i>Summary
                </h6>
            </div>
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center py-2
                            border-bottom">
                    <span class="small text-muted">Citizens notified</span>
                    <span class="fw-bold text-success">
                        {{ $eventAlert->notified_count }}
                    </span>
                </div>
                <div class="d-flex justify-content-between align-items-center py-2
                            border-bottom">
                    <span class="small text-muted">Event date</span>
                    <span class="fw-semibold small">
                        {{ $eventAlert->event_date->format('M j, Y') }}
                    </span>
                </div>
                <div class="d-flex justify-content-between align-items-center py-2
                            border-bottom">
                    <span class="small text-muted">Event time</span>
                    <span class="fw-semibold small">
                        {{ $eventAlert->event_date->format('g:i A') }}
                    </span>
                </div>
                <div class="d-flex justify-content-between align-items-center py-2">
                    <span class="small text-muted">Sent</span>
                    <span class="fw-semibold small">
                        {{ $eventAlert->created_at->diffForHumans() }}
                    </span>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection