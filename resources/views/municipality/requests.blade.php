@extends('municipality.layouts.app')
@section('title', 'Service Requests')
@section('page-title', 'Service Requests')

@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Status filter tabs --}}
@php
    $statuses = ['Pending', 'In Review', 'Missing Documents', 'Approved', 'Rejected', 'Completed'];
    $statusColors = [
        'Pending'           => 'warning',
        'In Review'         => 'info',
        'Missing Documents' => 'orange',
        'Approved'          => 'success',
        'Rejected'          => 'danger',
        'Completed'         => 'primary',
    ];
@endphp

<div class="d-flex flex-wrap gap-2 mb-4">
    <a href="{{ route('municipality.requests', absolute: false) }}"
       class="btn btn-sm {{ !$statusFilter ? 'btn-dark' : 'btn-outline-secondary' }}">
        All
    </a>
    @foreach($statuses as $status)
        <a href="{{ route('municipality.requests', ['status' => $status], absolute: false) }}"
           class="btn btn-sm {{ $statusFilter === $status ? 'btn-dark' : 'btn-outline-secondary' }}">
            {{ $status }}
        </a>
    @endforeach
</div>

{{-- Requests table --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        @if($serviceRequests->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                <p class="mb-0">No requests found{{ $statusFilter ? ' with status "' . $statusFilter . '"' : '' }}.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">#</th>
                            <th>Citizen</th>
                            <th>Service</th>
                            <th>Office</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($serviceRequests as $serviceRequest)
                            <tr>
                                <td class="ps-4 text-muted small">{{ $serviceRequest->id }}</td>
                                <td>
                                    <div class="fw-semibold small">{{ $serviceRequest->citizen->name }}</div>
                                    <div class="text-muted" style="font-size:0.78rem;">{{ $serviceRequest->citizen->email }}</div>
                                </td>
                                <td class="small">{{ $serviceRequest->service->name }}</td>
                                <td class="small text-muted">{{ $serviceRequest->service->office->name }}</td>
                                <td>
                                    @php
                                        $badgeColor = match($serviceRequest->status) {
                                            'Pending'           => 'warning text-dark',
                                            'In Review'         => 'info text-dark',
                                            'Missing Documents' => 'secondary',
                                            'Approved'          => 'success',
                                            'Rejected'          => 'danger',
                                            'Completed'         => 'primary',
                                            default             => 'secondary',
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $badgeColor }}">{{ $serviceRequest->status }}</span>
                                </td>
                                <td class="small text-muted">{{ $serviceRequest->created_at->format('d M Y') }}</td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('municipality.requests.show', $serviceRequest, absolute: false) }}"
                                       class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-eye me-1"></i>View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-top">
                {{ $serviceRequests->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>

@endsection