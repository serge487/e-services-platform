@extends('municipality.layouts.app')
@section('title', 'Notifications')
@section('page-title', 'Notifications')
@push('styles')
    <style>
        .notification-row-unread { border-left: 3px solid #fbbf24; background: rgba(251, 191, 36, 0.06); }
    </style>
@endpush
@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom py-3">
        <h6 class="mb-0 fw-semibold"><i class="bi bi-bell me-2"></i>Notifications</h6>
        <p class="mb-0 mt-1 small text-muted">Click a message to open that conversation in Chat.</p>
    </div>
    <div class="list-group list-group-flush">
        @forelse($notifications as $notification)
            @php
                $chatId = data_get($notification->data, 'chat_id');
            @endphp
            @if($chatId)
                <a href="{{ route('municipality.notifications.chat', $notification->id) }}"
                   class="list-group-item list-group-item-action py-3 text-decoration-none {{ $notification->read_at ? '' : 'notification-row-unread' }}">
                    <div class="d-flex justify-content-between gap-2">
                        <div>
                            <div class="fw-semibold text-dark">
                                {{ $notification->data['sender_name'] ?? 'Message' }}
                                @if(! empty($notification->data['office_name']))
                                    <span class="text-muted fw-normal">· {{ $notification->data['office_name'] }}</span>
                                @endif
                            </div>
                            <div class="text-muted small mt-1">{{ $notification->data['preview'] ?? '' }}</div>
                        </div>
                        <span class="text-muted small text-nowrap">{{ $notification->created_at->diffForHumans() }}</span>
                    </div>
                </a>
            @else
                <div class="list-group-item py-3">
                    <div class="d-flex justify-content-between gap-2">
                        <div>
                            <div class="fw-semibold text-dark">
                                {{ $notification->data['sender_name'] ?? 'Message' }}
                                @if(! empty($notification->data['office_name']))
                                    <span class="text-muted fw-normal">· {{ $notification->data['office_name'] }}</span>
                                @endif
                            </div>
                            <div class="text-muted small mt-1">{{ $notification->data['preview'] ?? '' }}</div>
                        </div>
                        <span class="text-muted small text-nowrap">{{ $notification->created_at->diffForHumans() }}</span>
                    </div>
                </div>
            @endif
        @empty
            <div class="card-body text-center py-5 text-muted">
                <i class="bi bi-bell fs-1 d-block mb-2"></i>
                <p class="mb-0">No notifications yet.</p>
            </div>
        @endforelse
    </div>
    @if($notifications->hasPages())
        <div class="card-footer bg-white border-top">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
@endsection
