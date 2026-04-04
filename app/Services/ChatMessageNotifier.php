<?php

namespace App\Services;

use App\Events\UnreadNotificationsCountChanged;
use App\Events\UserNotificationCreated;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\User;
use App\Notifications\NewChatMessageNotification;

class ChatMessageNotifier
{
    public static function notifyRecipients(Chat $chat, User $sender, ChatMessage $message): void
    {
        $chat->loadMissing('office', 'citizen');

        $office = $chat->office;
        if (! $office) {
            return;
        }

        if ((int) $sender->id === (int) $chat->citizen_id) {
            $recipients = User::query()
                ->where('municipality_id', $office->municipality_id)
                ->where(function ($q) use ($office) {
                    $q->where('role', 'municipality')
                        ->orWhere(function ($q2) use ($office) {
                            $q2->where('role', 'office_staff')
                                ->where('office_id', $office->id);
                        });
                })
                ->get();
        } else {
            $recipients = User::query()
                ->where('id', $chat->citizen_id)
                ->get();
        }

        foreach ($recipients as $recipient) {
            if ((int) $recipient->id === (int) $sender->id) {
                continue;
            }

            $recipient->notify(new NewChatMessageNotification($message));

            $dbNotification = $recipient->notifications()->latest('created_at')->first();
            if ($dbNotification) {
                $openPath = $recipient->role === 'citizen'
                    ? route('citizen.notifications.chat', ['id' => $dbNotification->id], absolute: false)
                    : route('municipality.notifications.chat', ['id' => $dbNotification->id], absolute: false);

                broadcast(new UserNotificationCreated(
                    $recipient->id,
                    $dbNotification->id,
                    $dbNotification->data,
                    $dbNotification->created_at->toIso8601String(),
                    $openPath,
                ));
            }

            broadcast(new UnreadNotificationsCountChanged(
                $recipient->id,
                $recipient->unreadNotifications()->count()
            ));
        }
    }
}
