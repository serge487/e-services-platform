<?php

namespace App\Http\Controllers;

use App\Events\UnreadNotificationsCountChanged;
use App\Models\Chat;
use Illuminate\Support\Facades\Auth;

class CitizenNotificationController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        abort_unless($user->role === 'citizen', 403);

        $notifications = $user->notifications()->paginate(20);

        return view('citizen.notifications', compact('notifications'));
    }

    /**
     * Mark this notification read and open the related chat (from notifications list).
     */
    public function openChat(string $id)
    {
        $user = Auth::user();
        abort_unless($user->role === 'citizen', 403);

        $notification = $user->notifications()->where('id', $id)->firstOrFail();
        $chatId = data_get($notification->data, 'chat_id');
        abort_unless($chatId, 404);

        Chat::where('id', $chatId)->where('citizen_id', $user->id)->firstOrFail();

        if ($notification->read_at === null) {
            $notification->markAsRead();
            broadcast(new UnreadNotificationsCountChanged(
                $user->id,
                $user->unreadNotifications()->count()
            ));
        }

        return redirect()->route('citizen.chat.show', $chatId);
    }
}
