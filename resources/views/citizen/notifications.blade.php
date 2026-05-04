@extends('layouts.public')
@section('title', 'Notifications')
@section('page-title', 'Notifications')
@push('styles')
    <style>
        .notification-row-unread { border-left: 3px solid #fbbf24; background: rgba(251, 191, 36, 0.06); }
        .btn-delete-notif { opacity: 0; transition: opacity 0.15s; }
        .list-group-item:hover .btn-delete-notif { opacity: 1; }
    </style>
@endpush
@section('content')
{{-- Store CSRF token and route URLs in meta tags so JS can always read them --}}
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="route-notifications-destroy-all" content="{{ route('citizen.notifications.destroyAll') }}">
<meta name="notifications-destroy-base" content="{{ route('citizen.notifications', absolute: false) }}">

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-start">
        <div>
            <h6 class="mb-0 fw-semibold"><i class="bi bi-bell me-2"></i>Your notifications</h6>
            <p class="mb-0 mt-1 small text-muted">Click a notification to open the chat or service request.</p>
        </div>
        @if($notifications->total() > 0)
            <button type="button" id="clear-all-btn" class="btn btn-sm btn-outline-danger" onclick="handleClearAll()">
                <i class="bi bi-trash me-1"></i>Clear all
            </button>
        @endif
    </div>

    <div class="list-group list-group-flush" id="notifications-live-list">
        @forelse($notifications as $notification)
            @php $chatId = data_get($notification->data, 'chat_id'); @endphp

            <div class="list-group-item py-3 px-3 {{ $notification->read_at ? '' : 'notification-row-unread' }} d-flex align-items-center gap-2"
                 data-notification-id="{{ $notification->id }}">

                <div class="flex-grow-1">
                    @if($chatId)
                        <a href="{{ route('citizen.notifications.chat', $notification->id) }}"
                           class="text-decoration-none text-dark">
                            <div class="d-flex justify-content-between gap-2">
                                <div>
                                    <div class="fw-semibold">
                                        {{ $notification->data['sender_name'] ?? 'Message' }}
                                        @if(!empty($notification->data['office_name']))
                                            <span class="text-muted fw-normal">· {{ $notification->data['office_name'] }}</span>
                                        @endif
                                    </div>
                                    <div class="text-muted small mt-1">{{ $notification->data['preview'] ?? '' }}</div>
                                </div>
                                <span class="text-muted small text-nowrap">{{ $notification->created_at->diffForHumans() }}</span>
                            </div>
                        </a>
                    @elseif(data_get($notification->data, 'service_request_id'))
                      <a href="{{ route('citizen.notifications.service-request', $notification->id) }}"
                           class="text-decoration-none text-dark">
                            <div class="d-flex justify-content-between gap-2">
                                <div>
                                    <div class="fw-semibold">
                                        {{ $notification->data['sender_name'] ?? $notification->data['service_name'] ?? __('Service request') }}
                                        @if(!empty($notification->data['office_name']))
                                            <span class="text-muted fw-normal">· {{ $notification->data['office_name'] }}</span>
                                        @endif
                                    </div>
                                    <div class="text-muted small mt-1">{{ $notification->data['preview'] ?? '' }}</div>
                                </div>
                                <span class="text-muted small text-nowrap">{{ $notification->created_at->diffForHumans() }}</span>
                            </div>
                        </a>
                    @else
                        <div class="d-flex justify-content-between gap-2">
                            <div>
                                <div class="fw-semibold text-dark">
                                    {{ $notification->data['sender_name'] ?? $notification->data['service_name'] ?? __('Notification') }}
                                    @if(!empty($notification->data['office_name']))
                                        <span class="text-muted fw-normal">· {{ $notification->data['office_name'] }}</span>
                                    @endif
                                </div>
                                <div class="text-muted small mt-1">{{ $notification->data['preview'] ?? '' }}</div>
                            </div>
                            <span class="text-muted small text-nowrap">{{ $notification->created_at->diffForHumans() }}</span>
                        </div>
                    @endif
                </div>

                {{-- Delete button — no form needed, URL stored in data attribute --}}
                <button type="button"
                        class="btn btn-sm btn-delete-notif text-danger border-0 bg-transparent"
                        title="Delete notification"
                        data-delete-url="{{ route('citizen.notifications.destroy', $notification->id) }}"
                        onclick="handleDeleteNotif(this)">
                    <i class="bi bi-x-lg"></i>
                </button>

            </div>
        @empty
            <div class="list-group-item border-0 text-center py-5 text-muted" id="empty-state">
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

<script>
    // Read CSRF token from meta tag — always available, never inside a removed form
    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    }

    function showEmptyState() {
        const list = document.getElementById('notifications-live-list');
        const hasItems = list.querySelector('.list-group-item[data-notification-id]');
        if (!hasItems) {
            list.innerHTML = `
                <div class="list-group-item border-0 text-center py-5 text-muted" id="empty-state">
                    <i class="bi bi-bell fs-1 d-block mb-2"></i>
                    <p class="mb-0">No notifications yet.</p>
                </div>`;
            // Also hide the Clear All button since there's nothing left
            const clearBtn = document.getElementById('clear-all-btn');
            if (clearBtn) clearBtn.remove();
        }
    }

    function handleDeleteNotif(btn) {
        const url = btn.getAttribute('data-delete-url');
        const row = btn.closest('.list-group-item[data-notification-id]');

        fetch(url, {
            method: 'DELETE',
             credentials: 'same-origin',
            headers: {
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            }
        })
        .then(res => {
            if (res.ok) {
                row.remove();
                showEmptyState();
            } else {
                console.error('Delete failed:', res.status, res.statusText);
            }
        })
        .catch(err => console.error('Delete error:', err));
    }

    function handleClearAll() {
        if (!confirm('Clear all notifications?')) return;

        const url = document.querySelector('meta[name="route-notifications-destroy-all"]').getAttribute('content');

        fetch(url, {
            method: 'DELETE',
             credentials: 'same-origin',
            headers: {
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            }
        })
        .then(res => {
            if (res.ok) {
                document.getElementById('notifications-live-list').innerHTML = `
                    <div class="list-group-item border-0 text-center py-5 text-muted" id="empty-state">
                        <i class="bi bi-bell fs-1 d-block mb-2"></i>
                        <p class="mb-0">No notifications yet.</p>
                    </div>`;
                const clearBtn = document.getElementById('clear-all-btn');
                if (clearBtn) clearBtn.remove();
            } else {
                console.error('Clear all failed:', res.status, res.statusText);
            }
        })
        .catch(err => console.error('Clear all error:', err));
    }
</script>
@endsection