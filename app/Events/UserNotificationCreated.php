<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserNotificationCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        public int $userId,
        public string $notificationId,
        public array $data,
        public string $createdAtIso,
        public string $openPath,
    ) {}

    /**
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('App.Models.User.'.$this->userId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'notification.created';
    }

    /**
     * @return array{id: string, data: array<string, mixed>, created_at: string, open_path: string}
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->notificationId,
            'data' => $this->data,
            'created_at' => $this->createdAtIso,
            'open_path' => $this->openPath,
        ];
    }
}
