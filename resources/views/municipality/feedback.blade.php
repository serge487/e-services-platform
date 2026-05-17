@extends('municipality.layouts.app')
@section('title', 'Feedback')
@section('page-title', 'Citizen Feedback')

@push('styles')
<style>
.feedback-page { --fb-primary: #1a3c5e; --fb-accent: #2563eb; --fb-warn: #d97706; --fb-success: #059669; }
.feedback-page .stat-card {
    border: 0;
    border-radius: 12px;
    box-shadow: 0 1px 8px rgba(26,60,94,0.08);
    height: 100%;
    overflow: hidden;
}
.feedback-page .stat-card .stat-icon {
    width: 48px; height: 48px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.35rem;
    flex-shrink: 0;
}
.feedback-page .stat-card .stat-value { font-size: 1.75rem; font-weight: 800; line-height: 1.1; color: #0f172a; }
.feedback-page .stat-card .stat-label { font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; }
.feedback-page .filter-bar {
    background: #fff;
    border-radius: 12px;
    padding: 1rem 1.15rem;
    box-shadow: 0 1px 8px rgba(26,60,94,0.06);
    border: 1px solid #e8eef4;
}
.feedback-page .filter-chip {
    display: inline-flex; align-items: center; gap: 0.35rem;
    padding: 0.4rem 0.85rem;
    border-radius: 999px;
    font-size: 0.8rem; font-weight: 600;
    text-decoration: none;
    border: 1.5px solid #e2e8f0;
    color: #475569;
    background: #fff;
    transition: all 0.15s;
}
.feedback-page .filter-chip:hover { border-color: var(--fb-accent); color: var(--fb-accent); }
.feedback-page .filter-chip.active {
    background: var(--fb-primary);
    border-color: var(--fb-primary);
    color: #fff;
}
.feedback-page .filter-chip .chip-count {
    background: rgba(0,0,0,0.12);
    padding: 0.05rem 0.45rem;
    border-radius: 999px;
    font-size: 0.7rem;
}
.feedback-page .filter-chip.active .chip-count { background: rgba(255,255,255,0.25); }
.feedback-page .review-card {
    background: #fff;
    border: 1px solid #e8eef4;
    border-radius: 14px;
    box-shadow: 0 1px 6px rgba(15,23,42,0.05);
    margin-bottom: 1rem;
    transition: box-shadow 0.15s;
}
.feedback-page .review-card:hover { box-shadow: 0 4px 16px rgba(26,60,94,0.1); }
.feedback-page .review-card.needs-reply { border-left: 4px solid var(--fb-warn); }
.feedback-page .review-card.has-reply { border-left: 4px solid var(--fb-success); }
.feedback-page .review-card-head {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #f1f5f9;
    display: flex;
    flex-wrap: wrap;
    align-items: flex-start;
    justify-content: space-between;
    gap: 0.75rem;
}
.feedback-page .review-citizen { font-weight: 700; color: #0f172a; font-size: 1rem; }
.feedback-page .review-meta { font-size: 0.78rem; color: #64748b; }
.feedback-page .review-rating {
    display: inline-flex; align-items: center; gap: 0.35rem;
    background: #fffbeb; color: #b45309;
    padding: 0.25rem 0.6rem; border-radius: 8px;
    font-weight: 700; font-size: 0.85rem;
}
.feedback-page .review-rating i { color: #f59e0b; }
.feedback-page .badge-pill {
    font-size: 0.68rem; font-weight: 700;
    padding: 0.2rem 0.55rem; border-radius: 6px;
}
.feedback-page .badge-review-public { background: #ecfdf5; color: #047857; }
.feedback-page .badge-review-private { background: #f1f5f9; color: #475569; }
.feedback-page .badge-reply-public { background: #eff6ff; color: #1d4ed8; }
.feedback-page .badge-reply-private { background: #f1f5f9; color: #475569; }
.feedback-page .review-body { padding: 1rem 1.25rem 0.75rem; }
.feedback-page .review-card-footer {
    padding: 0 1.25rem 1rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 0.5rem;
}
.feedback-page .btn-toggle-reply {
    font-weight: 600;
    font-size: 0.82rem;
    border-radius: 8px;
}
.feedback-page .citizen-quote {
    background: #f8fafc;
    border-radius: 10px;
    padding: 0.85rem 1rem;
    border-left: 3px solid #cbd5e1;
    font-size: 0.9rem;
    line-height: 1.55;
    color: #334155;
    margin: 0;
}
.feedback-page .reply-panel {
    background: #f0f7ff;
    border-top: 1px solid #dbeafe;
    padding: 1rem 1.25rem;
}
.feedback-page .reply-panel-title {
    font-size: 0.72rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--fb-primary);
    margin-bottom: 0.65rem;
}
.feedback-page .reply-panel .form-control {
    border-radius: 10px;
    border-color: #bfdbfe;
    font-size: 0.88rem;
}
.feedback-page .reply-panel .form-control:focus {
    border-color: var(--fb-accent);
    box-shadow: 0 0 0 3px rgba(37,99,235,0.15);
}
.feedback-page .privacy-toggle {
    background: #fff;
    border: 1px solid #dbeafe;
    border-radius: 10px;
    padding: 0.65rem 0.85rem;
    margin: 0.65rem 0;
}
.feedback-page .btn-send-reply {
    background: var(--fb-primary);
    border: none;
    font-weight: 700;
    padding: 0.45rem 1.1rem;
    border-radius: 8px;
}
.feedback-page .btn-send-reply:hover { background: #14304d; }
.feedback-page .existing-reply {
    background: #fff;
    border: 1px solid #dbeafe;
    border-radius: 10px;
    padding: 0.85rem 1rem;
    font-size: 0.88rem;
    line-height: 1.5;
}
.feedback-page .list-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-bottom: 1rem;
}
.feedback-page .list-header h6 { margin: 0; font-weight: 700; color: var(--fb-primary); }
</style>
@endpush

@section('content')
@php
    $renderStars = function (int $rating): string {
        $html = '';
        for ($i = 1; $i <= 5; $i++) {
            $html .= $i <= $rating
                ? '<i class="bi bi-star-fill"></i>'
                : '<i class="bi bi-star opacity-50"></i>';
        }
        return $html;
    };

    $filterParams = array_filter([
        'office_id' => $officeFilter ?: null,
        'reply' => ($replyFilter ?? 'all') !== 'all' ? $replyFilter : null,
    ]);

    $repliedCount = $stats['total'] - $stats['awaiting_response'];
@endphp

<div class="feedback-page">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show border-0 shadow-sm mb-4">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(! $canRespond)
        <div class="alert alert-info border-0 shadow-sm d-flex align-items-start gap-3 mb-4">
            <div class="rounded-circle bg-info bg-opacity-25 p-2 flex-shrink-0">
                <i class="bi bi-eye fs-5"></i>
            </div>
            <div>
                <strong>View-only access</strong>
                <p class="mb-0 small mt-1 opacity-90">Desk staff can read reviews. Only municipality administrators can send replies.</p>
            </div>
        </div>
    @endif

    {{-- Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-chat-square-quote"></i>
                    </div>
                    <div>
                        <div class="stat-label">Total reviews</div>
                        <div class="stat-value">{{ $stats['total'] }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                        <i class="bi bi-star-half"></i>
                    </div>
                    <div>
                        <div class="stat-label">Average rating</div>
                        <div class="stat-value d-flex align-items-center gap-2 flex-wrap">
                            @if($stats['total'] > 0)
                                {{ number_format($stats['average'], 1) }}
                                <span class="text-warning fs-6">{!! $renderStars((int) round($stats['average'])) !!}</span>
                            @else
                                <span class="text-muted fs-5">&mdash;</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div class="stat-icon bg-success bg-opacity-10 text-success">
                        <i class="bi bi-calendar3"></i>
                    </div>
                    <div>
                        <div class="stat-label">This month</div>
                        <div class="stat-value">{{ $stats['this_month'] }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                    <div>
                        <div class="stat-label">Awaiting reply</div>
                        <div class="stat-value text-danger">{{ $stats['awaiting_response'] }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="filter-bar mb-4">
        <div class="small fw-bold text-muted text-uppercase mb-2" style="letter-spacing:0.4px;">Show reviews</div>
        <div class="d-flex flex-wrap gap-2 mb-3">
            <a href="{{ route('municipality.feedback', array_filter(['office_id' => $officeFilter]), absolute: false) }}"
               class="filter-chip {{ ($replyFilter ?? 'all') === 'all' ? 'active' : '' }}">
                All <span class="chip-count">{{ $stats['total'] }}</span>
            </a>
            <a href="{{ route('municipality.feedback', array_merge(array_filter(['office_id' => $officeFilter]), ['reply' => 'awaiting']), absolute: false) }}"
               class="filter-chip {{ ($replyFilter ?? '') === 'awaiting' ? 'active' : '' }}">
                <i class="bi bi-hourglass"></i> Awaiting reply
                <span class="chip-count">{{ $stats['awaiting_response'] }}</span>
            </a>
            <a href="{{ route('municipality.feedback', array_merge(array_filter(['office_id' => $officeFilter]), ['reply' => 'replied']), absolute: false) }}"
               class="filter-chip {{ ($replyFilter ?? '') === 'replied' ? 'active' : '' }}">
                <i class="bi bi-check2-circle"></i> Replied
                <span class="chip-count">{{ $repliedCount }}</span>
            </a>
        </div>

        @if($offices->count() > 1)
            <div class="small fw-bold text-muted text-uppercase mb-2" style="letter-spacing:0.4px;">Office</div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('municipality.feedback', $filterParams['reply'] ? ['reply' => $filterParams['reply']] : [], absolute: false) }}"
                   class="filter-chip {{ ! $officeFilter ? 'active' : '' }}">All offices</a>
                @foreach($offices as $office)
                    <a href="{{ route('municipality.feedback', array_filter(['office_id' => $office->id, 'reply' => $filterParams['reply'] ?? null]), absolute: false) }}"
                       class="filter-chip {{ (int) $officeFilter === $office->id ? 'active' : '' }}">
                        {{ $office->name }}
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Reviews list --}}
    <div class="list-header">
        <h6><i class="bi bi-inbox me-2"></i>{{ $feedbacks->total() }} review(s)</h6>
        @if($canRespond)
            <span class="small text-muted"><i class="bi bi-broadcast me-1"></i>Citizens are notified live when you reply</span>
        @endif
    </div>

    @if($feedbacks->isEmpty())
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5 text-muted">
                <i class="bi bi-star fs-1 d-block mb-3 opacity-25"></i>
                <p class="mb-1 fw-semibold">No reviews match this filter</p>
                <p class="small mb-0">Try &ldquo;All&rdquo; or another office. Reviews appear after citizens pay and submit feedback.</p>
            </div>
        </div>
    @else
        @foreach($feedbacks as $feedback)
            <article class="review-card {{ $feedback->office_response ? 'has-reply' : 'needs-reply' }}">
                <div class="review-card-head">
                    <div>
                        <div class="review-citizen">{{ $feedback->citizen->name }}</div>
                        <div class="review-meta mt-1">
                            {{ $feedback->created_at->format('M d, Y') }} &middot; {{ $feedback->created_at->format('g:i A') }}
                            @if($feedback->serviceRequest)
                                &middot; Request #{{ $feedback->serviceRequest->id }}
                            @endif
                        </div>
                        <div class="review-meta mt-1">
                            <i class="bi bi-grid me-1"></i>{{ $feedback->service?->name ?? 'Service' }}
                            <span class="mx-1">&middot;</span>
                            <i class="bi bi-building me-1"></i>{{ $feedback->office->name }}
                        </div>
                    </div>
                    <div class="d-flex flex-column align-items-end gap-2">
                        <div class="review-rating">
                            <span>{!! $renderStars($feedback->rating) !!}</span>
                            {{ $feedback->rating }}/5
                        </div>
                        <div class="d-flex flex-wrap gap-1 justify-content-end">
                            <span class="badge-pill {{ $feedback->is_private ? 'badge-review-private' : 'badge-review-public' }}">
                                <i class="bi bi-{{ $feedback->is_private ? 'lock' : 'globe' }} me-1"></i>
                                Review {{ $feedback->is_private ? 'private' : 'public' }}
                            </span>
                            @if($feedback->office_response)
                                <span class="badge-pill {{ $feedback->office_response_is_private ? 'badge-reply-private' : 'badge-reply-public' }}">
                                    Reply {{ $feedback->office_response_is_private ? 'private' : 'public' }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="review-body">
                    <p class="citizen-quote">{{ $feedback->citizen_comment }}</p>
                </div>

                @php
                    $expandReply = $errors->any() && (int) old('_feedback_id') === $feedback->id;
                @endphp

                <div class="review-card-footer">
                    @if($feedback->office_response)
                        <span class="small text-success"><i class="bi bi-check2-circle me-1"></i>Replied</span>
                    @elseif($canRespond)
                        <span class="small text-warning"><i class="bi bi-hourglass me-1"></i>Awaiting reply</span>
                    @else
                        <span class="small text-muted"><i class="bi bi-clock me-1"></i>No reply yet</span>
                    @endif

                    @if($canRespond || $feedback->office_response)
                        <button type="button"
                                class="btn btn-sm btn-outline-primary btn-toggle-reply"
                                data-reply-toggle="reply-panel-{{ $feedback->id }}"
                                aria-expanded="{{ $expandReply ? 'true' : 'false' }}"
                                aria-controls="reply-panel-{{ $feedback->id }}">
                            <i class="bi bi-reply me-1"></i>
                            @if($canRespond)
                                {{ $feedback->office_response ? 'Edit reply' : 'Reply' }}
                            @else
                                View reply
                            @endif
                        </button>
                    @endif
                </div>

                <div class="reply-panel {{ $expandReply ? '' : 'd-none' }}"
                     id="reply-panel-{{ $feedback->id }}">
                    <div class="reply-panel-title">
                        <i class="bi bi-reply-fill me-1"></i>
                        Municipality reply
                    </div>

                    @if($canRespond)
                        <form method="POST"
                              action="{{ route('municipality.feedback.respond', $feedback, absolute: false) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="_feedback_id" value="{{ $feedback->id }}">
                            @if($officeFilter)
                                <input type="hidden" name="office_id" value="{{ $officeFilter }}">
                            @endif
                            @if(($replyFilter ?? 'all') !== 'all')
                                <input type="hidden" name="reply" value="{{ $replyFilter }}">
                            @endif
                            <textarea name="office_response"
                                      class="form-control"
                                      rows="3"
                                      maxlength="2000"
                                      placeholder="Write your reply to {{ $feedback->citizen->name }}&hellip;"
                                      required>{{ old('office_response', $feedback->office_response) }}</textarea>
                            <div class="privacy-toggle">
                                <div class="form-check mb-0">
                                    <input class="form-check-input"
                                           type="checkbox"
                                           name="office_response_is_private"
                                           id="reply-private-{{ $feedback->id }}"
                                           value="1"
                                           {{ old('office_response_is_private', $feedback->office_response_is_private) ? 'checked' : '' }}>
                                    <label class="form-check-label small" for="reply-private-{{ $feedback->id }}">
                                        <strong>Private reply</strong> &mdash; only this citizen sees it on their request
                                    </label>
                                </div>
                                <p class="small text-muted mb-0 ms-4">Unchecked = public (shown on the office reviews page)</p>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm btn-send-reply mt-2">
                                <i class="bi bi-send-fill me-1"></i>
                                {{ $feedback->office_response ? 'Update reply' : 'Send reply' }}
                            </button>
                        </form>
                    @elseif($feedback->office_response)
                        <div class="existing-reply">{{ $feedback->office_response }}</div>
                    @endif
                </div>
            </article>
        @endforeach

        <div class="d-flex justify-content-center mt-3">
            {{ $feedbacks->withQueryString()->links() }}
        </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('[data-reply-toggle]').forEach((btn) => {
    btn.addEventListener('click', () => {
        const panel = document.getElementById(btn.getAttribute('data-reply-toggle'));
        if (!panel) {
            return;
        }
        const willShow = panel.classList.contains('d-none');
        panel.classList.toggle('d-none');
        btn.setAttribute('aria-expanded', willShow ? 'true' : 'false');
    });
});
</script>
@endpush
