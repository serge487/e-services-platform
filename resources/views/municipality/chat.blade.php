@extends('municipality.layouts.app')
@section('title', 'Chat')
@section('page-title', 'Chat & Support')
@section('content')

<div class="row g-0 border rounded-3 overflow-hidden h-100" style="height: calc(100vh - 200px); min-height: 0;">

    <!-- Left Panel: Citizen Chats List -->
    <div class="col-md-4 border-end d-flex flex-column bg-white h-100" style="min-height: 0;">
        <div class="p-3 border-bottom">
            <h6 class="fw-bold mb-0">💬 Citizen Conversations</h6>
        </div>

        <!-- Chat History -->
        <div class="overflow-auto flex-grow-1" style="min-height: 0;">
            @forelse($chats as $c)
                <a href="{{ route('municipality.chat.show', $c->id) }}"
                    class="d-flex align-items-center gap-3 p-3 text-decoration-none border-bottom {{ isset($chat) && $chat->id === $c->id ? 'bg-primary bg-opacity-10' : '' }}">
                    <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center fw-bold"
                        style="width:40px;height:40px;font-size:14px;flex-shrink:0;">
                        {{ strtoupper(substr($c->citizen->name, 0, 2)) }}
                    </div>
                    <div class="overflow-hidden">
                        <p class="mb-0 fw-semibold text-dark small">{{ $c->citizen->name }}</p>
                        <p class="mb-0 text-muted" style="font-size:12px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                            {{ $c->latestMessage?->content ?? 'No messages yet' }}
                        </p>
                    </div>
                </a>
            @empty
                <div class="p-4 text-center text-muted small">No citizen chats yet.</div>
            @endforelse
        </div>
    </div>

    <!-- Right Panel: Chat Window -->
    <div class="col-md-8 d-flex flex-column bg-light h-100" style="min-height: 0;">
        @if(isset($chat))
            <!-- Chat Header -->
            <div class="bg-white border-bottom px-4 py-3 d-flex align-items-center gap-3 flex-shrink-0">
                <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center fw-bold"
                    style="width:40px;height:40px;font-size:14px;flex-shrink:0;">
                    {{ strtoupper(substr($chat->citizen->name, 0, 2)) }}
                </div>
                <div>
                    <p class="mb-0 fw-semibold">{{ $chat->citizen->name }}</p>
                    <p class="mb-0 text-muted small">{{ $chat->citizen->email }}</p>
                </div>
            </div>

            <!-- Messages (min-height:0 so this scrolls inside the flex column instead of pushing the input off-screen) -->
            <div class="flex-grow-1 overflow-auto p-4 d-flex flex-column gap-3" id="messages" style="min-height: 0;">
                @forelse($chat->messages as $message)
                    <div class="d-flex {{ $message->sender_id === auth()->id() ? 'justify-content-end' : 'justify-content-start' }}">
                        <div class="px-3 py-2 rounded-3 shadow-sm"
                            data-message-id="{{ $message->id }}"
                            style="max-width:60%;
                            {{ $message->sender_id === auth()->id()
                                ? 'background:#198754;color:white;border-bottom-right-radius:4px!important;'
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

            <!-- Message Input -->
            <div class="bg-white border-top p-3 flex-shrink-0">
                <form id="chat-send-form" method="POST" action="{{ route('municipality.chat.send', $chat->id) }}" class="d-flex gap-2" autocomplete="off">
                    @csrf
                    <input type="text" name="content" placeholder="Type a message..." required
                        class="form-control rounded-pill" />
                    <button type="submit" class="btn btn-success rounded-pill px-4">Send</button>
                </form>
            </div>

        @else
            <!-- No chat selected -->
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
            window.chatRealtimeConfig = {
                chatId: {{ (int) $chat->id }},
                currentUserId: {{ (int) auth()->id() }},
                theme: 'municipality',
                pollUrl: @json(route('municipality.chat.poll', $chat->id, absolute: false)),
            };
        </script>
        <script src="{{ asset('js/chat-thread.js') }}?v=2" defer></script>
    @endpush
@endif

@endsection