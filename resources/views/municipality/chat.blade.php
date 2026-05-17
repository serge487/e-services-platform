@extends('municipality.layouts.app')
@section('title', 'Chat')
@section('page-title', 'Chat & Support')
@push('styles')
    <style>
        /* Full-height chat layout */
        #chat-outer {
            display: flex;
            height: calc(100vh - var(--topbar-height, 60px) - 3.5rem);
            min-height: 0;
            border-radius: 0.5rem;
            overflow: hidden;
            border: 1px solid #dee2e6;
        }

        /* Left panel */
        #chat-left {
            width: 320px;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            background: #fff;
            border-right: 1px solid #dee2e6;
            min-height: 0;
        }

        #chat-left-list {
            flex: 1;
            overflow-y: auto;
            min-height: 0;
        }

        /* Right panel */
        #chat-right {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: #f8fafc;
            min-height: 0;
        }

        /* Messages area — takes all remaining space, scrolls */
        #messages {
            flex: 1;
            overflow-y: auto;
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            min-height: 0;
        }

        /* Input bar — always pinned to bottom */
        #chat-input-bar {
            flex-shrink: 0;
            background: #fff;
            border-top: 1px solid #dee2e6;
            padding: 0.75rem 1rem;
        }
    </style>
@endpush
@section('content')

<div id="chat-outer">

    <!-- Left Panel: Citizen Chats List -->
    <div id="chat-left">
        <div class="p-3 border-bottom">
            <h6 class="fw-bold mb-2">💬 Citizen Conversations</h6>
            <form method="GET" action="{{ route('municipality.chat') }}" class="d-flex gap-2">
                <input type="text" name="search" value="{{ $search ?? '' }}"
                       placeholder="Search citizen..."
                       class="form-control form-control-sm rounded-pill" />
                <button type="submit" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                    <i class="bi bi-search"></i>
                </button>
            </form>
        </div>

        <div id="chat-left-list">
            @forelse($chats as $c)
                <a href="{{ route('municipality.chat.show', $c->id) }}{{ ($search ?? '') ? '?search='.urlencode($search) : '' }}"
                    class="d-flex align-items-center gap-3 p-3 text-decoration-none border-bottom {{ isset($chat) && (int) $chat->id === (int) $c->id ? 'bg-primary bg-opacity-10' : '' }}">
                    <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold"
                        style="width:40px;height:40px;font-size:14px;flex-shrink:0;background:#1a3c5e;">
                        {{ strtoupper(substr($c->citizen->name, 0, 2)) }}
                    </div>
                    <div class="overflow-hidden flex-grow-1">
                        <p class="mb-0 fw-semibold text-dark small d-flex align-items-center gap-1">
                            {{ $c->citizen->name }}
                            @if($c->isMuted())
                                <i class="bi bi-bell-slash-fill text-warning" style="font-size:11px;" title="Muted"></i>
                            @endif
                        </p>
                        <p class="mb-0 text-muted" style="font-size:12px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                            {{ $c->latestMessage?->content ?? 'No messages yet' }}
                        </p>
                    </div>
                </a>
            @empty
                <div class="p-4 text-center text-muted small">
                    {{ ($search ?? '') ? 'No citizens found matching "'.$search.'".' : 'No citizen chats yet.' }}
                </div>
            @endforelse
        </div>
    </div>

    <!-- Right Panel: Chat Window -->
    <div id="chat-right">
        @if(isset($chat))
            <!-- Chat Header -->
            <div class="bg-white border-bottom px-4 py-3 d-flex align-items-center gap-3 flex-shrink-0">
                <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold"
                    style="width:40px;height:40px;font-size:14px;flex-shrink:0;background:#1a3c5e;">
                    {{ strtoupper(substr($chat->citizen->name, 0, 2)) }}
                </div>
                <div class="flex-grow-1">
                    <p class="mb-0 fw-semibold">{{ $chat->citizen->name }}</p>
                    <p class="mb-0 text-muted small">{{ $chat->citizen->email }}</p>
                </div>
                <button id="mute-btn"
                        onclick="handleMuteToggle()"
                        class="btn btn-sm {{ $chat->isMuted() ? 'btn-warning' : 'btn-outline-secondary' }} d-flex align-items-center gap-1">
                    <i id="mute-icon" class="bi {{ $chat->isMuted() ? 'bi-bell-slash-fill' : 'bi-bell-fill' }}"></i>
                    <span id="mute-label">{{ $chat->isMuted() ? 'Unmute' : 'Mute' }}</span>
                </button>
            </div>

            <div id="muted-banner" class="{{ $chat->isMuted() ? '' : 'd-none' }} alert alert-warning rounded-0 mb-0 py-2 text-center small flex-shrink-0">
                <i class="bi bi-bell-slash-fill me-1"></i> This citizen is muted. You won't receive notifications from them.
            </div>

            <!-- Messages (scrollable) -->
            <div id="messages">
                @forelse($chat->messages as $message)
                    <div class="d-flex {{ $message->sender_id === auth()->id() ? 'justify-content-end' : 'justify-content-start' }}">
                        <div class="px-3 py-2 rounded-3 shadow-sm"
                            data-message-id="{{ $message->id }}"
                            style="max-width:60%;
                            {{ $message->sender_id === auth()->id()
                                ? 'background:#1a3c5e;color:white;border-bottom-right-radius:4px!important;'
                                : 'background:white;color:#1f2937;border-bottom-left-radius:4px!important;' }}">
                            <p class="mb-1 small">{{ $message->content }}</p>
                            <p class="mb-0 opacity-75" style="font-size:11px;">
                                {{ $message->created_at->format('h:i A') }}
                            </p>
                        </div>
                    </div>
                @empty
                    <div id="messages-empty-hint" class="text-center text-muted mt-5">No messages yet.</div>
                @endforelse
            </div>

            <!-- Input bar — always visible, pinned to bottom -->
            <div id="chat-input-bar">
                <form id="chat-send-form" method="POST" action="{{ route('municipality.chat.send', $chat->id) }}" class="d-flex gap-2" autocomplete="off">
                    @csrf
                    <input type="text" name="content" placeholder="Type a message..."
                        required class="form-control rounded-pill" />
                    <button type="submit" class="btn rounded-pill px-4 text-white border-0"
                        style="background:#1a3c5e;">
                        <i class="bi bi-send-fill me-1"></i>Send
                    </button>
                </form>
            </div>

        @else
            <div class="flex-grow-1 d-flex flex-column align-items-center justify-content-center text-muted">
                <span style="font-size:60px;">💬</span>
                <p class="fw-semibold mt-3">Select a citizen conversation</p>
                <p class="small">Click on a citizen from the left to view the chat</p>
            </div>
        @endif
    </div>

</div>

@if(isset($chat))
@push('scripts')
<script>
const muteUrl = @json(route('municipality.chat.mute', $chat->id, absolute: false));
const unmuteUrl = @json(route('municipality.chat.unmute', $chat->id, absolute: false));
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
let isMuted = {{ $chat->isMuted() ? 'true' : 'false' }};

function handleMuteToggle() {
    const url = isMuted ? unmuteUrl : muteUrl;
    fetch(url, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        }
    }).then(res => res.json()).then(data => {
        isMuted = data.muted;
        const btn = document.getElementById('mute-btn');
        const label = document.getElementById('mute-label');
        const icon = document.getElementById('mute-icon');
        const banner = document.getElementById('muted-banner');
        if (isMuted) {
            btn.classList.remove('btn-outline-secondary');
            btn.classList.add('btn-warning');
            icon.className = 'bi bi-bell-slash-fill';
            label.textContent = 'Unmute';
            banner.classList.remove('d-none');
        } else {
            btn.classList.remove('btn-warning');
            btn.classList.add('btn-outline-secondary');
            icon.className = 'bi bi-bell-fill';
            label.textContent = 'Mute';
            banner.classList.add('d-none');
        }
    }).catch(err => console.error(err));
}

window.chatRealtimeConfig = {
    chatId: {{ (int) $chat->id }},
    currentUserId: {{ (int) auth()->id() }},
    theme: 'municipality',
    pollUrl: @json(route('municipality.chat.poll', $chat->id, absolute: false)),
};
</script>
@endpush
@endif

@endsection