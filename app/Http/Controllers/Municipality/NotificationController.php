<?php

namespace App\Http\Controllers\Municipality;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->canAccessMunicipalityPortal(), 403);

        $user->unreadNotifications->markAsRead();

        $notifications = $user->notifications()->paginate(20);

        return view('municipality.notifications', compact('notifications'));
    }
}
