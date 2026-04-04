<?php

namespace App\Http\Controllers\Municipality;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\Office;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    /**
     * Show all citizen chats for this office
     */
    public function index()
    {
        $user = Auth::user();

        // Get the office for this municipality user
        $office = Office::where('municipality_id', $user->municipality_id)->first();

        if (!$office) {
            return view('municipality.chat', [
                'chats'  => collect(),
                'office' => null,
            ]);
        }

        $chats = Chat::where('office_id', $office->id)
            ->with(['citizen', 'latestMessage'])
            ->orderByDesc('updated_at')
            ->get();

        return view('municipality.chat', compact('chats', 'office'));
    }

    /**
     * Show a specific chat
     */
    public function show($chatId)
    {
        $user = Auth::user();

        $office = Office::where('municipality_id', $user->municipality_id)->first();

        $chat = Chat::where('id', $chatId)
            ->where('office_id', $office->id)
            ->with(['citizen', 'messages.sender'])
            ->firstOrFail();

        $chats = Chat::where('office_id', $office->id)
            ->with(['citizen', 'latestMessage'])
            ->orderByDesc('updated_at')
            ->get();

        // Mark messages as read
        ChatMessage::where('chat_id', $chatId)
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('municipality.chat', compact('chat', 'chats', 'office'));
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

        $office = Office::where('municipality_id', $user->municipality_id)->first();

        $chat = Chat::where('id', $chatId)
            ->where('office_id', $office->id)
            ->firstOrFail();

        ChatMessage::create([
            'chat_id'   => $chat->id,
            'sender_id' => $user->id,
            'content'   => $request->content,
        ]);

        $chat->touch();

        return back();
    }
}