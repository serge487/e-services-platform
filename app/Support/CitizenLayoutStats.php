<?php

namespace App\Support;

use App\Models\User;

class CitizenLayoutStats
{
    /**
     * @return array{active_requests: int, upcoming_appointments: int, unread_notifications: int}
     */
    public static function forCitizen(User $citizen): array
    {
        return [
            'active_requests' => $citizen->serviceRequests()
                ->whereNotIn('status', ['Completed', 'Rejected'])
                ->count(),
            'upcoming_appointments' => $citizen->appointments()
                ->where('status', 'scheduled')
                ->count(),
            'unread_notifications' => $citizen->unreadNotifications()->count(),
        ];
    }
}
