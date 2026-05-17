<?php

namespace App\Services;

use App\Events\RevenueStatsChanged;
use App\Models\ServiceRequest;
use App\Models\User;

class PaymentRevenueBroadcaster
{
    public static function broadcastForServiceRequest(ServiceRequest $serviceRequest): void
    {
        $serviceRequest->loadMissing(['service.office', 'payment']);

        $office = $serviceRequest->service?->office;
        if (! $office) {
            return;
        }

        $officeId = (int) $office->id;
        $municipalityId = (int) $office->municipality_id;

        $recipientIds = User::query()
            ->where('is_active', true)
            ->where(function ($query) use ($municipalityId, $officeId): void {
                $query->where('role', 'admin')
                    ->orWhere(function ($sub) use ($municipalityId): void {
                        $sub->where('role', 'municipality')
                            ->where('municipality_id', $municipalityId);
                    })
                    ->orWhere(function ($sub) use ($officeId): void {
                        $sub->where('role', 'office_staff')
                            ->where('office_id', $officeId);
                    });
            })
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->all();

        foreach ($recipientIds as $userId) {
            self::broadcastToUser($userId);
        }
    }

    public static function broadcastToUser(int $userId): void
    {
        $user = User::query()->find($userId);
        if (! $user) {
            return;
        }

        $officeIds = match ($user->role) {
            'admin' => null,
            'municipality', 'office_staff' => $user->accessibleOfficeIds(),
            default => [],
        };

        if ($officeIds === []) {
            return;
        }

        $total = RevenueService::totalAmount($officeIds);

        try {
            broadcast(new RevenueStatsChanged(
                $userId,
                $total,
                RevenueService::formattedTotal($officeIds),
            ));
        } catch (\Exception $e) {
            // Reverb offline — non-fatal
        }
    }
}
