<?php

namespace App\Services;

use App\Events\UnreadNotificationsCountChanged;
use App\Events\UserNotificationCreated;
use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;

class NotificationRealtimeBroadcaster
{
    public static function broadcastLatest(User $user): void
    {
        $notification = $user->notifications()->latest('created_at')->first();

        if (! $notification instanceof DatabaseNotification) {
            return;
        }

        $openPath = self::openPathFor($user, $notification);

       try {
    broadcast(new UserNotificationCreated(
        $user->id,
        $notification->id,
        $notification->data,
        $notification->created_at->toIso8601String(),
        $openPath,
    ));

    broadcast(new UnreadNotificationsCountChanged(
        $user->id,
        $user->unreadNotifications()->count(),
    ));
} catch (\Exception $e) {
    // WebSocket server not running — non-fatal, notification still saved to DB
}
    }

    private static function openPathFor(User $user, DatabaseNotification $notification): string
    {
        $data = $notification->data ?? [];

        if (data_get($data, 'chat_id')) {
            return $user->role === 'citizen'
                ? route('citizen.notifications.chat', ['id' => $notification->id], absolute: false)
                : route('municipality.notifications.chat', ['id' => $notification->id], absolute: false);
        }

        if (data_get($data, 'type') === 'feedback_reply' && $user->role === 'citizen') {
            return route('citizen.notifications.feedback-reply', ['id' => $notification->id], absolute: false);
        }

        $serviceRequestId = data_get($data, 'service_request_id');
        if ($serviceRequestId !== null && $serviceRequestId !== '') {
            if ($user->role === 'citizen') {
                return route('citizen.service-requests.show', ['serviceRequest' => $serviceRequestId], absolute: false);
            }

            if ($user->canAccessMunicipalityPortal()) {
                return route('municipality.requests.show', ['serviceRequest' => $serviceRequestId], absolute: false);
            }
        }

        return $user->role === 'citizen'
            ? route('citizen.notifications', absolute: false)
            : route('municipality.notifications', absolute: false);
    }
}
