@extends('municipality.layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="row g-4 mb-4">
    {{-- Stats cards --}}
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                    <i class="bi bi-file-earmark-text fs-4 text-primary"></i>
                </div>
                <div>
                    <div class="text-muted small">Total Requests</div>
                    <div class="fw-bold fs-4">—</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-warning bg-opacity-10 p-3">
                    <i class="bi bi-hourglass-split fs-4 text-warning"></i>
                </div>
                <div>
                    <div class="text-muted small">Pending</div>
                    <div class="fw-bold fs-4">—</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success bg-opacity-10 p-3">
                    <i class="bi bi-calendar-check fs-4 text-success"></i>
                </div>
                <div>
                    <div class="text-muted small">Appointments Today</div>
                    <div class="fw-bold fs-4">—</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-info bg-opacity-10 p-3">
                    <i class="bi bi-chat-dots fs-4 text-info"></i>
                </div>
                <div>
                    <div class="text-muted small">Unread Messages</div>
                    <div class="fw-bold fs-4">—</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body text-center py-5 text-muted">
        <i class="bi bi-speedometer2 fs-1 d-block mb-2"></i>
        <p class="mb-0">Dashboard widgets will be populated as features are built.</p>
    </div>
</div>
@endsection