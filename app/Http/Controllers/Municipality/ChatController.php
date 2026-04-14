<?php

namespace App\Http\Controllers\Municipality;

use App\Events\ChatMessageSent;
use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\Office;
use App\Services\ChatMessageNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $office = Office::where('municipality_id', $user->municipality_id)->first();

        if (! $office) {
            return view('municipality.chat', [
                'chats' => collect(),
                'office' => null,
                'search' => '',
            ]);
        }

        $search = $request->query('search', '');

        $chats = Chat::where('office_id', $office->id)
            ->with(['citizen', 'latestMessage'])
            ->when($search, function ($query) use ($search) {
                $query->whereHas('citizen', function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%');
                });
            })
            ->orderByDesc('updated_at')
            ->get();

        return view('municipality.chat', compact('chats', 'office', 'search'));
    }

    public function show(Request $request, $chatId)
    {
        $user = Auth::user();

        $office = Office::where('municipality_id', $user->municipality_id)->first();

        $chat = Chat::where('id', $chatId)
            ->where('office_id', $office->id)
            ->with(['citizen', 'messages.sender'])
            ->firstOrFail();

        $search = $request->query('search', '');

        $chats = Chat::where('office_id', $office->id)
            ->with(['citizen', 'latestMessage'])
            ->when($search, function ($query) use ($search) {
                $query->whereHas('citizen', function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%');
                });
            })
            ->orderByDesc('updated_at')
            ->get();

        ChatMessage::where('chat_id', $chatId)
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('municipality.chat', compact('chat', 'chats', 'office', 'search'));
    }

    public function mute($chatId)
    {
        $user = Auth::user();
        $office = Office::where('municipality_id', $user->municipality_id)->firstOrFail();
        $chat = Chat::where('id', $chatId)->where('office_id', $office->id)->firstOrFail();
        $chat->update(['muted_at' => now()]);
        return response()->json(['muted' => true]);
    }

    public function unmute($chatId)
    {
        $user = Auth::user();
        $office = Office::where('municipality_id', $user->municipality_id)->firstOrFail();
        $chat = Chat::where('id', $chatId)->where('office_id', $office->id)->firstOrFail();
        $chat->update(['muted_at' => null]);
        return response()->json(['muted' => false]);
    }

    public function poll(Request $request, int $chatId)
    {
        $afterId = max(0, (int) $request->query('after', 0));

        $user = Auth::user();

        $office = Office::where('municipality_id', $user->municipality_id)->firstOrFail();

        $chat = Chat::where('id', $chatId)
            ->where('office_id', $office->id)
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

    public function sendMessage(Request $request, $chatId)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $user = Auth::user();

        $office = Office::where('municipality_id', $user->municipality_id)->first();

        $chat = Chat::where('id', $chatId)
            ->where('office_id', $office->id)
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