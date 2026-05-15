<?php

namespace App\Http\Controllers;

use App\Events\UnreadNotificationsCountChanged;
use App\Models\Chat;
use App\Models\Office;
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

public function destroy(string $id)
{
    $user = Auth::user();
    abort_unless($user->role === 'citizen', 403); // or canAccessMunicipalityPortal()
    
    $notification = $user->notifications()->where('id', $id)->firstOrFail();
    $notification->delete();

    try {
        broadcast(new UnreadNotificationsCountChanged(
            $user->id,
            $user->unreadNotifications()->count()
        ));
    } catch (\Exception $e) {
        // WebSocket server not running in dev — non-fatal
    }

    return response()->json(['success' => true]);
}

public function destroyAll()
{
    $user = Auth::user();
    abort_unless($user->role === 'citizen', 403); // or canAccessMunicipalityPortal()

    $user->notifications()->delete();

    try {
        broadcast(new UnreadNotificationsCountChanged($user->id, 0));
    } catch (\Exception $e) {
        // WebSocket server not running in dev — non-fatal
    }

    return response()->json(['success' => true]);
}
public function openServiceRequest(string $id)
{
    $user = Auth::user();
    abort_unless($user->role === 'citizen', 403);

    $notification = $user->notifications()->where('id', $id)->firstOrFail();

    if (data_get($notification->data, 'type') === 'feedback_reply') {
        return $this->openFeedbackReply($id);
    }

    $serviceRequestId = data_get($notification->data, 'service_request_id');
    abort_unless($serviceRequestId, 404);

    if ($notification->read_at === null) {
        $notification->markAsRead();
        try {
            broadcast(new UnreadNotificationsCountChanged(
                $user->id,
                $user->unreadNotifications()->count()
            ));
        } catch (\Exception $e) {
            // WebSocket not running — non-fatal
        }
    }

    return redirect()->route('citizen.service-requests.show', $serviceRequestId);
}

    /**
     * Open a municipality feedback-reply notification (marks read, updates badge via Reverb).
     * Public replies → office reviews page; private replies → request detail feedback section.
     */
    public function openFeedbackReply(string $id)
    {
        $user = Auth::user();
        abort_unless($user->role === 'citizen', 403);

        $notification = $user->notifications()->where('id', $id)->firstOrFail();
        abort_unless(data_get($notification->data, 'type') === 'feedback_reply', 404);

        if ($notification->read_at === null) {
            $notification->markAsRead();

            try {
                broadcast(new UnreadNotificationsCountChanged(
                    $user->id,
                    $user->unreadNotifications()->count()
                ));
            } catch (\Exception $e) {
                // WebSocket not running — non-fatal
            }
        }

        $isPrivate = (bool) data_get($notification->data, 'office_response_is_private', false);
        $officeId = data_get($notification->data, 'office_id');
        $serviceRequestId = data_get($notification->data, 'service_request_id');

        if (! $isPrivate && $officeId) {
            Office::query()->findOrFail($officeId);

            return redirect()->route('portal.office.feedbacks', $officeId);
        }

        abort_unless($serviceRequestId, 404);

        return redirect()->route('citizen.service-requests.show', $serviceRequestId)
            ->withFragment('citizen-feedback-card');
    }
}