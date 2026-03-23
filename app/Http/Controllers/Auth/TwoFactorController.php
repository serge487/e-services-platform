<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PragmaRX\Google2FALaravel\Support\Authenticator;

class TwoFactorController extends Controller
{
    /**
     * Show 2FA setup page
     */
    public function setup()
    {
        $user = Auth::user();
        $google2fa = app('pragmarx.google2fa');

        if (!$user->two_factor_secret) {
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

    /**
     * Verify and enable 2FA
     */
    public function enable(Request $request)
    {
        $request->validate([
            'code' => 'required|numeric',
        ]);

        $user = Auth::user();
        $google2fa = app('pragmarx.google2fa');

        $valid = $google2fa->verifyKey($user->two_factor_secret, $request->code);

        if (!$valid) {
            return back()->withErrors(['code' => 'Invalid code. Please try again.']);
        }

        $user->update(['two_factor_confirmed_at' => now()]);

        return redirect()->route('citizen.dashboard')->with('success', '2FA enabled successfully!');
    }

    /**
     * Show 2FA verify page
     */
    public function verify()
    {
        return view('auth.two-factor-verify');
    }

    /**
     * Verify 2FA code on login
     */
    public function validateCode(Request $request)
    {
        $request->validate([
            'code' => 'required|numeric',
        ]);

        $user = Auth::user();
        $google2fa = app('pragmarx.google2fa');

        $valid = $google2fa->verifyKey($user->two_factor_secret, $request->code);

        if (!$valid) {
            return back()->withErrors(['code' => 'Invalid code. Please try again.']);
        }

        session(['2fa_verified' => true]);

        return redirect()->route('citizen.dashboard');
    }
}