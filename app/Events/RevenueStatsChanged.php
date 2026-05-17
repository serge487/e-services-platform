<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RevenueStatsChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $userId,
        public float $totalRevenue,
        public string $formattedTotal,
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
        return 'revenue.updated';
    }

    /**
     * @return array{total_revenue: float, formatted_total: string}
     */
    public function broadcastWith(): array
    {
        return [
            'total_revenue' => $this->totalRevenue,
            'formatted_total' => $this->formattedTotal,
        ];
    }
}
