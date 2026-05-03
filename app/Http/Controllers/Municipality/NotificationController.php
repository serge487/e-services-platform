<?php
namespace App\Http\Controllers\Municipality;
use App\Events\UnreadNotificationsCountChanged;
use App\Http\Controllers\Controller;
use App\Models\Chat;
use Illuminate\Support\Facades\Auth;
class NotificationController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        abort_unless($user->canAccessMunicipalityPortal(), 403);
        $notifications = $user->notifications()->paginate(20);
        return view('municipality.notifications', compact('notifications'));
    }

    public function openChat(string $id)
    {
        $user = Auth::user();
        abort_unless($user->canAccessMunicipalityPortal(), 403);
        $notification = $user->notifications()->where('id', $id)->firstOrFail();
        $chatId = data_get($notification->data, 'chat_id');
        abort_unless($chatId, 404);
        $officeIds = $user->accessibleOfficeIds();
        Chat::where('id', $chatId)->whereIn('office_id', $officeIds)->firstOrFail();
        if ($notification->read_at === null) {
            $notification->markAsRead();
            broadcast(new UnreadNotificationsCountChanged(
                $user->id,
                $user->unreadNotifications()->count()
            ));
        }
        return redirect()->route('municipality.chat.show', $chatId);
    }

    public function destroy(string $id)
    {
        $user = Auth::user();
        abort_unless($user->canAccessMunicipalityPortal(), 403);

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
        abort_unless($user->canAccessMunicipalityPortal(), 403);

        $user->notifications()->delete();

        try {
            broadcast(new UnreadNotificationsCountChanged($user->id, 0));
        } catch (\Exception $e) {
            // WebSocket server not running in dev — non-fatal
        }

        return response()->json(['success' => true]);
    }
}