<?php

namespace App\Events;

use App\Models\ServiceRequest;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ServiceRequestStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public ServiceRequest $serviceRequest,
        public string $previousStatus,
        public string $newStatus,
        public ?string $notes = null,
    ) {
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('service-request.' . $this->serviceRequest->id),
            new PrivateChannel('citizen.' . $this->serviceRequest->citizen_id),
            new PrivateChannel('office.' . $this->serviceRequest->service->office_id),
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->serviceRequest->id,
            'status' => $this->newStatus,
            'previous_status' => $this->previousStatus,
            'notes' => $this->notes,
            'updated_at' => $this->serviceRequest->updated_at,
            'qr_token' => $this->serviceRequest->qr_code_token,
        ];
    }

    public function broadcastAs(): string
    {
        return 'service-request-status-changed';
    }
}
