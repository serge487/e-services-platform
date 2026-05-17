<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OfficialDocumentUploaded implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int    $citizenUserId,
        public int    $serviceRequestId,
        public string $filename,
        public string $downloadUrl,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('service-request.' . $this->serviceRequestId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'document.uploaded';
    }

    public function broadcastWith(): array
    {
        return [
            'service_request_id' => $this->serviceRequestId,
            'filename'           => $this->filename,
            'download_url'       => $this->downloadUrl,
        ];
    }
}