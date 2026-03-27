<?php

namespace App\Http\Controllers\Municipality;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        if (Auth::user()?->isOfficeStaff()) {
            return redirect()->route('municipality.requests');
        }

        return view('municipality.dashboard');
    }
}
