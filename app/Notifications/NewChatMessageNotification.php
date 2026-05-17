<?php

namespace App\Notifications;

use App\Models\ChatMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class NewChatMessageNotification extends Notification
{
    public function __construct(public ChatMessage $message) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $this->message->loadMissing('sender', 'chat.office');

        return [
            'chat_id' => $this->message->chat_id,
            'message_id' => $this->message->id,
            'sender_name' => $this->message->sender?->name ?? '',
            'preview' => Str::limit((string) $this->message->content, 120),
            'office_name' => $this->message->chat?->office?->name ?? '',
        ];
    }
}
