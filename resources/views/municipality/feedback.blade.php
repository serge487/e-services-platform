@extends('municipality.layouts.app')
@section('title', 'Feedback')
@section('page-title', 'Feedback')

@section('content')

@php
    $renderStars = function (int $rating): string {
        $html = '';
        for ($i = 1; $i <= 5; $i++) {
            $html .= $i <= $rating
                ? '<i class="bi bi-star-fill text-warning"></i>'
                : '<i class="bi bi-star text-warning opacity-50"></i>';
        }
        return $html;
    };
@endphp

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('warning'))
    <div class="alert alert-warning alert-dismissible fade show">
        <i class="bi bi-exclamation-triangle me-2"></i>{{ session('warning') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(! $canRespond)
    <div class="alert alert-info d-flex align-items-start gap-2 mb-4">
        <i class="bi bi-eye fs-5 flex-shrink-0"></i>
        <div>
            <strong>View-only access</strong> — desk staff can read all citizen reviews for their office.
            Only <strong>municipality administrators</strong> can post public or private replies.
        </div>
    </div>
@endif

{{-- Summary --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small text-uppercase fw-bold mb-1">Total reviews</div>
                <div class="fs-3 fw-bold">{{ $stats['total'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small text-uppercase fw-bold mb-1">Average rating</div>
                <div class="fs-3 fw-bold d-flex align-items-center gap-2">
                    @if($stats['total'] > 0)
                        {{ $stats['average'] }}
                        <span class="fs-6">{!! $renderStars((int) round($stats['average'])) !!}</span>
                    @else
                        <span class="text-muted fs-6">—</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small text-uppercase fw-bold mb-1">This month</div>
                <div class="fs-3 fw-bold">{{ $stats['this_month'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small text-uppercase fw-bold mb-1">Awaiting reply</div>
                <div class="fs-3 fw-bold">{{ $stats['awaiting_response'] }}</div>
            </div>
        </div>
    </div>
</div>

@if($offices->count() > 1)
    <div class="d-flex flex-wrap gap-2 mb-4">
        <a href="{{ route('municipality.feedback', absolute: false) }}"
           class="btn btn-sm {{ ! $officeFilter ? 'btn-dark' : 'btn-outline-secondary' }}">
            All offices
        </a>
        @foreach($offices as $office)
            <a href="{{ route('municipality.feedback', ['office_id' => $office->id], absolute: false) }}"
               class="btn btn-sm {{ (int) $officeFilter === $office->id ? 'btn-dark' : 'btn-outline-secondary' }}">
                {{ $office->name }}
            </a>
        @endforeach
    </div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        @if($feedbacks->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bi bi-star fs-1 d-block mb-2"></i>
                <p class="mb-0">No citizen reviews yet{{ $officeFilter ? ' for this office' : '' }}.</p>
                <p class="small mt-2 mb-0">Reviews appear after citizens complete payment for a service.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Date</th>
                            <th>Citizen</th>
                            <th>Service</th>
                            <th>Handling office</th>
                            <th>Rating</th>
                            <th>Citizen review</th>
                            <th style="min-width:260px;">Municipality reply</th>
                            <th class="pe-4">Visibility</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($feedbacks as $feedback)
                            <tr>
                                <td class="ps-4 small text-muted">
                                    {{ $feedback->created_at->format('d M Y') }}
                                    <div style="font-size:0.72rem;">{{ $feedback->created_at->format('g:i A') }}</div>
                                </td>
                                <td>
                                    <div class="fw-semibold small">{{ $feedback->citizen->name }}</div>
                                    @if($feedback->serviceRequest)
                                        <div class="text-muted" style="font-size:0.72rem;">
                                            Request #{{ $feedback->serviceRequest->id }}
                                        </div>
                                    @endif
                                </td>
                                <td class="small">{{ $feedback->service?->name ?? '—' }}</td>
                                <td class="small">
                                    <span class="badge bg-light text-dark border">
                                        <i class="bi bi-building me-1"></i>{{ $feedback->office->name }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-1">
                                        <span class="fw-bold small">{{ $feedback->rating }}</span>
                                        <span class="small">{!! $renderStars($feedback->rating) !!}</span>
                                    </div>
                                </td>
                                <td class="small" style="max-width:220px;">
                                    <p class="mb-0 text-break">{{ $feedback->citizen_comment }}</p>
                                </td>
                                <td class="small">
                                    @if($canRespond)
                                        <form method="POST"
                                              action="{{ route('municipality.feedback.respond', $feedback, absolute: false) }}">
                                            @csrf
                                            @method('PATCH')
                                            @if($officeFilter)
                                                <input type="hidden" name="office_id" value="{{ $officeFilter }}">
                                            @endif
                                            <textarea name="office_response"
                                                      class="form-control form-control-sm mb-2"
                                                      rows="3"
                                                      maxlength="2000"
                                                      placeholder="Write a reply to this citizen…"
                                                      required>{{ old('office_response', $feedback->office_response) }}</textarea>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input"
                                                       type="checkbox"
                                                       name="office_response_is_private"
                                                       id="reply-private-{{ $feedback->id }}"
                                                       value="1"
                                                       {{ old('office_response_is_private', $feedback->office_response_is_private) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="reply-private-{{ $feedback->id }}" style="font-size:0.75rem;">
                                                    Private reply (citizen only — not on public service page)
                                                </label>
                                            </div>
                                            <button type="submit" class="btn btn-primary btn-sm">
                                                <i class="bi bi-reply me-1"></i>
                                                {{ $feedback->office_response ? 'Update reply' : 'Send reply' }}
                                            </button>
                                        </form>
                                    @else
                                        @if($feedback->office_response)
                                            <p class="mb-1 text-break">{{ $feedback->office_response }}</p>
                                            <span class="badge {{ $feedback->office_response_is_private ? 'bg-secondary' : 'bg-primary' }}">
                                                Reply: {{ $feedback->office_response_is_private ? 'Private' : 'Public' }}
                                            </span>
                                        @else
                                            <span class="text-muted">No reply yet</span>
                                        @endif
                                    @endif
                                </td>
                                <td class="pe-4">
                                    <div class="d-flex flex-column gap-1">
                                        <span class="badge {{ $feedback->is_private ? 'bg-secondary' : 'bg-success-subtle text-success border border-success-subtle' }}">
                                            Review: {{ $feedback->is_private ? 'Private' : 'Public' }}
                                        </span>
                                        @if($feedback->office_response)
                                            <span class="badge {{ $feedback->office_response_is_private ? 'bg-secondary' : 'bg-primary' }}">
                                                Reply: {{ $feedback->office_response_is_private ? 'Private' : 'Public' }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-top">
                {{ $feedbacks->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>

@endsection
