@extends('municipality.layouts.app')
@section('title', 'Event Alerts')
@section('page-title', 'Event Alerts')

@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h5 class="fw-semibold mb-0">Event Alerts</h5>
        <small class="text-muted">Broadcast events to citizens who interacted with your office.</small>
    </div>
    <a href="{{ route('municipality.event-alerts.create', absolute: false) }}"
       class="btn btn-primary btn-sm">
        <i class="bi bi-megaphone me-1"></i>Send New Alert
    </a>
</div>

@if($events->isEmpty())
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5 text-muted">
            <i class="bi bi-megaphone fs-1 d-block mb-2 opacity-25"></i>
            <p class="mb-1 fw-semibold">No event alerts sent yet.</p>
            <p class="small mb-3">Create your first event alert to notify citizens.</p>
            <a href="{{ route('municipality.event-alerts.create', absolute: false) }}"
               class="btn btn-primary btn-sm">
                <i class="bi bi-plus me-1"></i>Create Event Alert
            </a>
        </div>
    </div>
@else
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Event</th>
                        <th>Office</th>
                        <th>Date & Time</th>
                        <th>Place</th>
                        <th>Notified</th>
                        <th>Sent By</th>
                        <th>Created</th>
                        <th class="pe-4"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($events as $event)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-semibold small">{{ $event->title }}</div>
                                @if($event->occasion)
                                    <span class="badge bg-primary bg-opacity-10 text-primary"
                                          style="font-size:0.68rem;">
                                        {{ $event->occasion }}
                                    </span>
                                @endif
                            </td>
                            <td class="small text-muted">{{ $event->office->name }}</td>
                            <td class="small">
                                {{ $event->event_date->format('M j, Y') }}<br>
                                <span class="text-muted">{{ $event->event_date->format('g:i A') }}</span>
                            </td>
                            <td class="small">{{ $event->place }}</td>
                            <td>
                                <span class="badge bg-success bg-opacity-10 text-success fw-semibold">
                                    <i class="bi bi-people me-1"></i>{{ $event->notified_count }}
                                </span>
                            </td>
                            <td class="small text-muted">{{ $event->creator->name }}</td>
                            <td class="small text-muted">{{ $event->created_at->diffForHumans() }}</td>
                            <td class="pe-4">
                                <a href="{{ route('municipality.event-alerts.show', $event, absolute: false) }}"
                                   class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($events->hasPages())
            <div class="px-4 py-3 border-top">
                {{ $events->links() }}
            </div>
        @endif
    </div>
@endif

@endsection