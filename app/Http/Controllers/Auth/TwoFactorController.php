<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TwoFactorController extends Controller
{
    public function setup()
    {
        $user = Auth::user();

        if ($user->role !== 'citizen') {
            abort(403);
        }

        if (! $user->identity_verified_at) {
            return redirect()->route('citizen.identity-verification.show');
        }

        $google2fa = app('pragmarx.google2fa');

        if (! $user->two_factor_secret) {
            $secret = $google2fa->generateSecretKey();
            $user->update(['two_factor_secret' => $secret]);
        }

        $qrCodeUrl = $google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $user->two_factor_secret
        );

        return view('auth.two-factor-setup', compact('qrCodeUrl'));
    }

    public function enable(Request $request)
    {
        $request->validate([
            'code' => 'required|numeric',
        ]);

        $user = Auth::user();

        if (! $user->identity_verified_at) {
            return redirect()->route('citizen.identity-verification.show');
        }
        $google2fa = app('pragmarx.google2fa');

        $valid = $google2fa->verifyKey($user->two_factor_secret, $request->code);

        if (! $valid) {
            return back()->withErrors(['code' => 'Invalid code. Please try again.']);
        }

        $user->update(['two_factor_confirmed_at' => now()]);

        $request->session()->put('citizen_session_unlocked', true);

        return redirect()->route('citizen.dashboard')->with('success', '2FA enabled successfully!');
    }

    public function verify()
    {
        $user = Auth::user();

        if (! $user->identity_verified_at) {
            return redirect()->route('citizen.identity-verification.show');
        }

        return view('auth.two-factor-verify');
    }

    public function validateCode(Request $request)
    {
        $request->validate([
            'code' => 'required|numeric',
        ]);

        $user = Auth::user();

        if (! $user->identity_verified_at) {
            return redirect()->route('citizen.identity-verification.show');
        }
        $google2fa = app('pragmarx.google2fa');

        $valid = $google2fa->verifyKey($user->two_factor_secret, $request->code);

        if (! $valid) {
            return back()->withErrors(['code' => 'Invalid code. Please try again.']);
        }

        $request->session()->put('citizen_session_unlocked', true);

        return redirect()->route('citizen.dashboard');
    }
}
