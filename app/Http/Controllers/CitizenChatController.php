<?php

namespace App\Http\Controllers;

use App\Events\ChatMessageSent;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\Office;
use App\Services\ChatMessageNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CitizenChatController extends Controller
{
    /**
     * Show the chat page with all offices
     */
    public function index()
    {
        $user = Auth::user();

        // Get all chats for this citizen
        $chats = Chat::where('citizen_id', $user->id)
            ->with(['office', 'latestMessage'])
            ->orderByDesc('updated_at')
            ->get();

        // Get all offices for starting a new chat
        $offices = Office::all();

        return view('citizen.chat', compact('chats', 'offices'));
    }

    /**
     * Start or get existing chat with an office
     */
    public function startOrGetChat(Request $request)
    {
        $request->validate([
            'office_id' => 'required|exists:offices,id',
        ]);

        $user = Auth::user();

        // Find existing chat or create new one
        $chat = Chat::where('citizen_id', $user->id)
            ->where('office_id', $request->office_id)
            ->first();

        if (! $chat) {
            $chat = new Chat;
            $chat->citizen_id = $user->id;
            $chat->office_id = $request->office_id;
            $chat->save();
        }

        return redirect()->route('citizen.chat.show', $chat->id);
    }

    /**
     * Show a specific chat
     */
    public function show($chatId)
    {
        $user = Auth::user();

        $chat = Chat::where('id', $chatId)
            ->where('citizen_id', $user->id)
            ->with(['office', 'messages.sender'])
            ->firstOrFail();

        // Get all chats for sidebar
        $chats = Chat::where('citizen_id', $user->id)
            ->with(['office', 'latestMessage'])
            ->orderByDesc('updated_at')
            ->get();

        $offices = Office::all();

        // Mark messages as read
        ChatMessage::where('chat_id', $chatId)
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('citizen.chat', compact('chat', 'chats', 'offices'));
    }

    /**
     * Return new messages after an id (HTTP fallback when WebSockets are unavailable).
     */
    public function poll(Request $request, int $chatId)
    {
        $afterId = max(0, (int) $request->query('after', 0));

        $user = Auth::user();

        $chat = Chat::where('id', $chatId)
            ->where('citizen_id', $user->id)
            ->firstOrFail();

        $messages = ChatMessage::query()
            ->where('chat_id', $chat->id)
            ->where('id', '>', $afterId)
            ->with('sender')
            ->orderBy('id')
            ->get();

        return response()->json([
            'messages' => $messages->map(fn (ChatMessage $m) => [
                'id' => $m->id,
                'chat_id' => $m->chat_id,
                'sender_id' => $m->sender_id,
                'content' => $m->content,
                'created_at' => $m->created_at->toIso8601String(),
                'sender_name' => $m->sender?->name ?? '',
            ])->values()->all(),
        ]);
    }

    /**
     * Send a message
     */
    public function sendMessage(Request $request, $chatId)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $user = Auth::user();

        $chat = Chat::where('id', $chatId)
            ->where('citizen_id', $user->id)
            ->firstOrFail();

        $message = ChatMessage::create([
            'chat_id' => $chat->id,
            'sender_id' => $user->id,
            'content' => $request->content,
        ]);

        $chat->touch();

        $message->load('sender');
        $chat->load('office');
        ChatMessageNotifier::notifyRecipients($chat, $user, $message);
        broadcast(new ChatMessageSent($message));

        if ($request->expectsJson()) {
            return response()->json([
                'message' => [
                    'id' => $message->id,
                    'chat_id' => $message->chat_id,
                    'sender_id' => $message->sender_id,
                    'content' => $message->content,
                    'created_at' => $message->created_at->toIso8601String(),
                    'sender_name' => $message->sender->name,
                ],
            ]);
        }

        return back();
    }
}
