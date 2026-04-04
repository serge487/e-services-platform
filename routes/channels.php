<?php

use App\Models\Chat;
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
