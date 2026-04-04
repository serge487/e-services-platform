@extends('layouts.public')
@section('title', 'Chat')
@section('page-title', 'Chat & Support')
@section('content')

<div class="row g-0" style="height: calc(100vh - 200px);">

    <!-- Left Panel: Chat List -->
    <div class="col-md-4 border-end d-flex flex-column bg-white">
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
                <button type="submit" class="btn btn-primary btn-sm w-100">
                    + Start New Chat
                </button>
            </form>
        </div>

        <!-- Chat History -->
        <div class="overflow-auto flex-grow-1">
            @forelse($chats as $c)
                <a href="{{ route('citizen.chat.show', $c->id) }}"
                    class="d-flex align-items-center gap-3 p-3 text-decoration-none border-bottom {{ isset($chat) && $chat->id === $c->id ? 'bg-primary bg-opacity-10' : '' }} hover-bg-light">
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold"
                        style="width:40px;height:40px;font-size:14px;flex-shrink:0;">
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
    <div class="col-md-8 d-flex flex-column bg-light">
        @if(isset($chat))
            <!-- Chat Header -->
            <div class="bg-white border-bottom px-4 py-3 d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold"
                    style="width:40px;height:40px;font-size:14px;flex-shrink:0;">
                    {{ strtoupper(substr($chat->office->name, 0, 2)) }}
                </div>
                <div>
                    <p class="mb-0 fw-semibold">{{ $chat->office->name }}</p>
                    <p class="mb-0 text-muted small">{{ $chat->office->address }}</p>
                </div>
            </div>

            <!-- Messages -->
            <div class="flex-grow-1 overflow-auto p-4 d-flex flex-column gap-3" id="messages">
                @forelse($chat->messages as $message)
                    <div class="d-flex {{ $message->sender_id === auth()->id() ? 'justify-content-end' : 'justify-content-start' }}">
                        <div class="px-3 py-2 rounded-3 shadow-sm"
                            data-message-id="{{ $message->id }}"
                            style="max-width:60%;
                            {{ $message->sender_id === auth()->id()
                                ? 'background:#4f46e5;color:white;border-bottom-right-radius:4px!important;'
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

            <!-- Message Input -->
            <div class="bg-white border-top p-3">
                <form id="chat-send-form" method="POST" action="{{ route('citizen.chat.send', $chat->id) }}" class="d-flex gap-2" autocomplete="off">
                    @csrf
                    <input type="text" name="content" placeholder="Type a message..." required
                        class="form-control rounded-pill" />
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Send</button>
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