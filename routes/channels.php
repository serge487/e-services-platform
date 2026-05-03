<?php

use App\Models\Chat;
use App\Models\ServiceRequest;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat.{chatId}', function ($user, $chatId) {
    $chat = Chat::query()->find($chatId);

    if (! $chat) {
        return false;
    }

    if ((int) $chat->citizen_id === (int) $user->id) {
        return ['id' => $user->id, 'name' => $user->name];
    }

    if ($user->canAccessMunicipalityPortal()) {
        $officeIds = $user->accessibleOfficeIds();

        if (in_array((int) $chat->office_id, $officeIds, true)) {
            return ['id' => $user->id, 'name' => $user->name];
        }
    }

    return false;
});

/**
 * Service Request status updates — broadcast to citizen and office staff
 */
Broadcast::channel('service-request.{requestId}', function ($user, $requestId) {
    $serviceRequest = ServiceRequest::query()->find($requestId);

    if (! $serviceRequest) {
        return false;
    }

    // Citizen watching their own request
    if ((int) $serviceRequest->citizen_id === (int) $user->id) {
        return ['id' => $user->id, 'name' => $user->name];
    }

    // Office/Municipality staff watching their office's request
    if ($user->canAccessMunicipalityPortal()) {
        $officeIds = $user->accessibleOfficeIds();

        if (in_array((int) $serviceRequest->service->office_id, $officeIds, true)) {
            return ['id' => $user->id, 'name' => $user->name];
        }
    }

    return false;
});

/**
 * Citizen-specific notifications
 */
Broadcast::channel('citizen.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

/**
 * Office-specific notifications
 */
Broadcast::channel('office.{officeId}', function ($user, $officeId) {
    if ($user->canAccessMunicipalityPortal()) {
        $officeIds = $user->accessibleOfficeIds();

        return in_array((int) $officeId, $officeIds, true);
    }

    return false;
});
