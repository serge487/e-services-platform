<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CitizenNotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->role === 'citizen', 403);

        $user->unreadNotifications->markAsRead();

        $notifications = $user->notifications()->paginate(20);

        return view('citizen.notifications', compact('notifications'));
    }
}
