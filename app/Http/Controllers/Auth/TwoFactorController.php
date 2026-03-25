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

        if (! $user->identity_verified_at && ! config('citizen.skip_identity_verification_gate')) {
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

        return view('auth.two-factor-setup', [
            'qrCodeUrl'     => $qrCodeUrl,
            'allow2faSkip'  => config('citizen.allow_2fa_skip'),
        ]);
    }

    public function enable(Request $request)
    {
        $request->validate([
            'code' => 'required|numeric',
        ]);

        $user = Auth::user();

        if (! $user->identity_verified_at && ! config('citizen.skip_identity_verification_gate')) {
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

        if (! $user->identity_verified_at && ! config('citizen.skip_identity_verification_gate')) {
            return redirect()->route('citizen.identity-verification.show');
        }

        return view('auth.two-factor-verify', [
            'allow2faSkip' => config('citizen.allow_2fa_skip'),
        ]);
    }

    /**
     * Bypass 2FA when {@see config('citizen.allow_2fa_skip')} is true (testing / QA only).
     */
    public function skipForTesting(Request $request)
    {
        if (! config('citizen.allow_2fa_skip')) {
            abort(403);
        }

        $user = $request->user();

        $identityOk = $user->identity_verified_at || config('citizen.skip_identity_verification_gate');

        if ($user->role !== 'citizen' || ! $identityOk) {
            abort(403);
        }

        $request->session()->put('citizen_session_unlocked', true);

        return redirect()
            ->route('citizen.dashboard')
            ->with('warning', '2FA was skipped. Enable CITIZEN_ALLOW_2FA_SKIP only for testing.');
    }

    public function validateCode(Request $request)
    {
        $request->validate([
            'code' => 'required|numeric',
        ]);

        $user = Auth::user();

        if (! $user->identity_verified_at && ! config('citizen.skip_identity_verification_gate')) {
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
