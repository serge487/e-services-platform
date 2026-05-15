<?php

namespace App\Events;

use App\Models\Feedback;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FeedbackMunicipalityReplyReceived implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Feedback $feedback) {}

    /**
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('App.Models.User.'.$this->feedback->citizen_id),
        ];

        if ($this->feedback->service_request_id) {
            $channels[] = new PrivateChannel('service-request.'.$this->feedback->service_request_id);
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'feedback.municipality-replied';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'feedback_id' => $this->feedback->id,
            'service_request_id' => $this->feedback->service_request_id,
            'office_id' => $this->feedback->office_id,
            'office_response' => $this->feedback->office_response,
            'office_response_is_private' => (bool) $this->feedback->office_response_is_private,
        ];
    }
}
