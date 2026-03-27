@extends('municipality.layouts.app')
@section('title', 'Office Profile')
@section('page-title', 'Office Profile')

@section('content')

{{-- Success message --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Page header --}}
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h5 class="mb-0 fw-semibold">{{ $municipality->name }}</h5>
        <small class="text-muted">{{ $offices->count() }} office(s) registered</small>
    </div>
</div>

{{-- No offices state --}}
@if($offices->isEmpty())
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5 text-muted">
            <i class="bi bi-building fs-1 d-block mb-3"></i>
            <p class="mb-0">No offices have been assigned to your municipality yet.</p>
            <small>Contact your administrator to add offices.</small>
        </div>
    </div>
@else
    <div class="row g-4">
        @foreach($offices as $office)
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between py-3">
                        <h6 class="mb-0 fw-semibold">
                            <i class="bi bi-building me-2 text-primary"></i>{{ $office->name }}
                        </h6>
                        <a href="{{ route('municipality.office-profile.edit', $office) }}"
                           class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-pencil me-1"></i>Edit
                        </a>
                    </div>
                    <div class="card-body">

                        {{-- Address --}}
                        <div class="d-flex gap-2 mb-3">
                            <i class="bi bi-geo-alt text-secondary mt-1"></i>
                            <div>
                                <div class="text-muted small fw-semibold text-uppercase" style="font-size:0.7rem; letter-spacing:0.5px;">Address</div>
                                <div class="small">{{ $office->address }}</div>
                            </div>
                        </div>

                        {{-- Contact --}}
                        <div class="d-flex gap-2 mb-3">
                            <i class="bi bi-telephone text-secondary mt-1"></i>
                            <div>
                                <div class="text-muted small fw-semibold text-uppercase" style="font-size:0.7rem; letter-spacing:0.5px;">Contact</div>
                                <div class="small">{{ $office->contact_info }}</div>
                            </div>
                        </div>

                        {{-- Location --}}
                        <div class="d-flex gap-2 mb-3">
                            <i class="bi bi-map text-secondary mt-1"></i>
                            <div>
                                <div class="text-muted small fw-semibold text-uppercase" style="font-size:0.7rem; letter-spacing:0.5px;">Coordinates</div>
                                <div class="small">
                                    {{ $office->latitude }}, {{ $office->longitude }}
                                    <a href="https://www.openstreetmap.org/?mlat={{ $office->latitude }}&mlon={{ $office->longitude }}#map=16/{{ $office->latitude }}/{{ $office->longitude }}"
                                       target="_blank" class="ms-1 text-primary small">
                                        <i class="bi bi-box-arrow-up-right"></i> View on map
                                    </a>
                                </div>
                            </div>
                        </div>

                        {{-- Working hours summary --}}
                        <div class="d-flex gap-2">
                            <i class="bi bi-clock text-secondary mt-1"></i>
                            <div class="w-100">
                                <div class="text-muted small fw-semibold text-uppercase mb-1" style="font-size:0.7rem; letter-spacing:0.5px;">Working Hours</div>
                                @php
                                    $days = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
                                    $hours = is_array($office->working_hours) ? $office->working_hours : [];
                                @endphp
                                <div class="row g-1">
                                    @foreach($days as $day)
                                        @php $dayData = $hours[$day] ?? null; @endphp
                                        <div class="col-12">
                                            <div class="d-flex justify-content-between align-items-center py-1 border-bottom border-light">
                                                <span class="small text-capitalize" style="width:90px;">{{ $day }}</span>
                                                @if($dayData && $dayData['is_open'])
                                                    <span class="small text-success fw-semibold">
                                                        {{ $dayData['open_time'] }} – {{ $dayData['close_time'] }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary bg-opacity-25 text-secondary small">Closed</span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif

@endsection