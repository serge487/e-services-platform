<?php

namespace App\Http\Controllers\Citizen;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CitizenProfileController extends Controller
{
    public function update(Request $request)
    {
        $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'max:20'],
        ]);

        Auth::user()->update([
            'name'         => $request->name,
            'phone_number' => $request->phone_number,
        ]);

        return back()->with('success', 'Profile updated successfully.');
    }
}