<?php

namespace App\Services;

use App\Events\FeedbackMunicipalityReplyReceived;
use App\Models\Feedback;
use App\Models\User;
use App\Notifications\FeedbackMunicipalityReply;

/**
 * Same pattern as ChatMessageNotifier: database notification + Reverb broadcast.
 */
class FeedbackReplyNotifier
{
    public static function notifyCitizen(Feedback $feedback): void
    {
        $feedback->loadMissing(['citizen', 'service', 'office', 'serviceRequest']);

        $citizen = $feedback->citizen;

        if (! $citizen instanceof User) {
            return;
        }

        $citizen->notify(new FeedbackMunicipalityReply($feedback));

        NotificationRealtimeBroadcaster::broadcastLatest($citizen);

        try {
            broadcast(new FeedbackMunicipalityReplyReceived($feedback));
        } catch (\Exception $e) {
            // Reverb optional — DB notification still saved
        }
    }
}
