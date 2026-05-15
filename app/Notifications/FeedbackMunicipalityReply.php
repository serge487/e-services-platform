<?php

namespace App\Notifications;

use App\Models\Feedback;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

/**
 * In-app notification only (database), like NewChatMessageNotification.
 * Reverb live delivery is triggered via NotificationRealtimeBroadcaster after notify().
 */
class FeedbackMunicipalityReply extends Notification
{
    public function __construct(private Feedback $feedback) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $this->feedback->loadMissing('service', 'office');

        $officeName = $this->feedback->office?->name;

        return [
            'type' => 'feedback_reply',
            'service_request_id' => $this->feedback->service_request_id,
            'feedback_id' => $this->feedback->id,
            'office_id' => $this->feedback->office_id,
            'office_response_is_private' => (bool) $this->feedback->office_response_is_private,
            'service_name' => $this->feedback->service?->name,
            'office_name' => $officeName,
            'sender_name' => $officeName ? 'Municipality · '.$officeName : 'Municipality',
            'preview' => 'Reply to your review: '.Str::limit((string) $this->feedback->office_response, 120),
        ];
    }
}
