@extends('layouts.public')
@section('title', 'Chat')
@section('page-title', 'Chat & Support')
@push('styles')
    <style>
        .citizen-chat-list-active { background: rgba(10, 92, 74, 0.12); }

        /* Full-height chat layout */
        #chat-outer {
            display: flex;
            height: calc(100vh - var(--topbar-height, 54px) - 3.5rem);
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

    <!-- Left Panel: Chat List -->
    <div id="chat-left">
        <div class="p-3 border-bottom">
            <h6 class="fw-bold mb-3">💬 My Conversations</h6>

            <!-- Start New Chat -->
            <form method="POST" action="{{ route('citizen.chat.start') }}">
                @csrf
                <select name="office_id" required class="form-select form-select-sm mb-2">
                    <option value="">Select an office...</option>
                    @foreach($offices as $office)
                        <option value="{{ $office->id }}">{{ $office->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-sm w-100 text-white border-0"
                    style="background:#0a5c4a;">
                    + Start New Chat
                </button>
            </form>
        </div>

        <!-- Chat History -->
        <div id="chat-left-list">
            @forelse($chats as $c)
                <a href="{{ route('citizen.chat.show', $c->id) }}"
                    class="d-flex align-items-center gap-3 p-3 text-decoration-none border-bottom {{ isset($chat) && (int) $chat->id === (int) $c->id ? 'citizen-chat-list-active' : '' }}">
                    <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold"
                        style="width:40px;height:40px;font-size:14px;flex-shrink:0;background:#0a5c4a;">
                        {{ strtoupper(substr($c->office->name, 0, 2)) }}
                    </div>
                    <div class="overflow-hidden">
                        <p class="mb-0 fw-semibold text-dark small">{{ $c->office->name }}</p>
                        <p class="mb-0 text-muted" style="font-size:12px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                            {{ $c->latestMessage?->content ?? 'No messages yet' }}
                        </p>
                    </div>
                </a>
            @empty
                <div class="p-4 text-center text-muted small">No chats yet. Start one above!</div>
            @endforelse
        </div>
    </div>

    <!-- Right Panel: Chat Window -->
    <div id="chat-right">
        @if(isset($chat))
            <!-- Chat Header -->
            <div class="bg-white border-bottom px-4 py-3 d-flex align-items-center gap-3 flex-shrink-0">
                <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold"
                    style="width:40px;height:40px;font-size:14px;flex-shrink:0;background:#0a5c4a;">
                    {{ strtoupper(substr($chat->office->name, 0, 2)) }}
                </div>
                <div>
                    <p class="mb-0 fw-semibold">{{ $chat->office->name }}</p>
                    <p class="mb-0 text-muted small">{{ $chat->office->address }}</p>
                </div>
            </div>

            <!-- Messages (scrollable) -->
            <div id="messages">
                @forelse($chat->messages as $message)
                    <div class="d-flex {{ $message->sender_id === auth()->id() ? 'justify-content-end' : 'justify-content-start' }}">
                        <div class="px-3 py-2 rounded-3 shadow-sm"
                            data-message-id="{{ $message->id }}"
                            style="max-width:60%;
                            {{ $message->sender_id === auth()->id()
                                ? 'background:#0a5c4a;color:white;border-bottom-right-radius:4px!important;'
                                : 'background:white;color:#1f2937;border-bottom-left-radius:4px!important;' }}">
                            <p class="mb-1 small">{{ $message->content }}</p>
                            <p class="mb-0 opacity-75" style="font-size:11px;">
                                {{ $message->created_at->format('h:i A') }}
                            </p>
                        </div>
                    </div>
                @empty
                    <div id="messages-empty-hint" class="text-center text-muted mt-5">No messages yet. Say hello! 👋</div>
                @endforelse
            </div>

            <!-- Input bar — always visible, pinned to bottom -->
            <div id="chat-input-bar">
                <form id="chat-send-form" method="POST" action="{{ route('citizen.chat.send', $chat->id) }}" class="d-flex gap-2" autocomplete="off">
                    @csrf
                    <input type="text" name="content" placeholder="Type a message..."
                        required class="form-control rounded-pill" />
                    <button type="submit" class="btn rounded-pill px-4 text-white border-0"
                        style="background:#0a5c4a;">
                        <i class="bi bi-send-fill me-1"></i>Send
                    </button>
                </form>
            </div>

        @else
            <!-- No chat selected -->
            <div class="flex-grow-1 d-flex flex-column align-items-center justify-content-center text-muted">
                <span style="font-size:60px;">💬</span>
                <p class="fw-semibold mt-3">Select a chat or start a new one</p>
                <p class="small">Choose an office from the left to begin chatting</p>
            </div>
        @endif
    </div>

</div>

@if(isset($chat))
    @push('scripts')
        <script>
            window.chatRealtimeConfig = {
                chatId: {{ (int) $chat->id }},
                currentUserId: {{ (int) auth()->id() }},
                theme: 'citizen',
                pollUrl: @json(route('citizen.chat.poll', $chat->id, absolute: false)),
            };
        </script>
    @endpush
@endif

@endsection