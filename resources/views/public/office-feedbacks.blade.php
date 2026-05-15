@extends('layouts.public')

@section('title', 'Reviews — ' . $office->name)
@section('page-title', 'Citizen Reviews')

@push('styles')
<style>
    :root { --detail-primary: #0a5c4a; }
    .back-link {
        color: #64748b; text-decoration: none; font-size: 0.83rem;
        display: inline-flex; align-items: center; gap: 0.3rem; margin-bottom: 1.1rem;
    }
    .back-link:hover { color: var(--detail-primary); }
    .reviews-header {
        display: flex; flex-wrap: wrap; align-items: center;
        justify-content: space-between; gap: 1rem; margin-bottom: 1.5rem;
    }
    .reviews-title { font-weight: 800; color: var(--detail-primary); margin: 0; font-size: 1.35rem; }
    .reviews-subtitle { color: #64748b; font-size: 0.85rem; margin-top: 0.2rem; }
    .reviews-summary {
        background: #fffbeb; border: 1px solid #fde68a; border-radius: 10px;
        padding: 0.65rem 1rem; font-size: 0.9rem; color: #92400e;
        display: flex; align-items: center; gap: 0.5rem;
    }
    .reviews-summary .stars { color: #f59e0b; }
    .review-card {
        background: #fff; border: 1px solid #e2e8f0; border-radius: 12px;
        padding: 1.15rem 1.25rem; margin-bottom: 1rem;
        box-shadow: 0 1px 6px rgba(15,23,42,0.05);
    }
    .review-meta { font-size: 0.78rem; color: #64748b; }
    .review-stars { color: #f59e0b; font-size: 0.85rem; }
    .muni-reply {
        margin-top: 0.85rem; padding: 0.75rem 1rem;
        background: #f8fafc; border-left: 4px solid var(--detail-primary);
        border-radius: 0 8px 8px 0; font-size: 0.88rem;
    }
    .muni-reply-label {
        font-size: 0.68rem; font-weight: 700; text-transform: uppercase;
        color: var(--detail-primary); margin-bottom: 0.35rem;
    }
</style>
@endpush

@section('content')

<a href="{{ route('portal.office', $office, absolute: false) }}" class="back-link">
    <i class="bi bi-arrow-left"></i> Back to {{ $office->name }}
</a>

<div class="reviews-header">
    <div>
        <h1 class="reviews-title">{{ $office->name }}</h1>
        <div class="reviews-subtitle">
            @if($office->municipality)
                <i class="bi bi-bank me-1"></i>{{ $office->municipality->name }}
            @endif
            · Public citizen reviews
        </div>
    </div>
    @if($stats['count'] > 0)
        <div class="reviews-summary">
            <span class="stars">
                @for($i = 0; $i < (int) round($stats['average']); $i++)
                    <i class="bi bi-star-fill"></i>
                @endfor
            </span>
            <strong>{{ number_format($stats['average'], 1) }}</strong>
            <span class="text-muted">({{ $stats['count'] }} {{ Str::plural('review', $stats['count']) }})</span>
        </div>
    @endif
</div>

@if($feedbacks->isEmpty())
    <div class="text-center py-5 text-muted bg-white rounded-3 border">
        <i class="bi bi-star fs-1 d-block mb-2 opacity-25"></i>
        <p class="mb-0">No public reviews for this office yet.</p>
        <p class="small mt-2">Reviews appear after citizens complete payment and submit feedback.</p>
    </div>
@else
    @foreach($feedbacks as $feedback)
        <article class="review-card">
            <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap mb-2">
                <div>
                    <strong>{{ $feedback->citizen->name }}</strong>
                    @if($feedback->service)
                        <span class="review-meta"> · {{ $feedback->service->name }}</span>
                    @endif
                    <div class="review-stars mt-1">
                        @for($i = 0; $i < $feedback->rating; $i++)
                            <i class="bi bi-star-fill"></i>
                        @endfor
                        @for($i = $feedback->rating; $i < 5; $i++)
                            <i class="bi bi-star text-muted opacity-50"></i>
                        @endfor
                    </div>
                </div>
                <span class="review-meta">{{ $feedback->created_at->format('M d, Y') }}</span>
            </div>
            <p class="mb-0">{{ $feedback->citizen_comment }}</p>
            @if($feedback->hasPublicOfficeResponse())
                <div class="muni-reply">
                    <div class="muni-reply-label"><i class="bi bi-reply me-1"></i>Municipality reply</div>
                    {{ $feedback->office_response }}
                </div>
            @endif
        </article>
    @endforeach

    <div class="mt-3">
        {{ $feedbacks->links() }}
    </div>
@endif

@endsection
