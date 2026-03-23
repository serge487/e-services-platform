<?php

namespace App\Http\Controllers\Municipality;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;

class TwoFactorSetupController extends Controller
{
    public function show(Request $request)
    {
        $user = Auth::user();
        $qrCode = null;
        $recoveryCodes = null;
        $setupKey = null;

        if ($user->two_factor_secret && ! $user->two_factor_confirmed_at) {
            // Secret exists but not yet confirmed — show QR for scanning
            $provider = app(TwoFactorAuthenticationProvider::class);
            $qrCode = $user->twoFactorQrCodeSvg();
            $setupKey = decrypt($user->two_factor_secret);
            $recoveryCodes = json_decode(decrypt($user->two_factor_recovery_codes), true);
        } elseif ($user->two_factor_confirmed_at) {
            // Already enabled — show recovery codes
            $recoveryCodes = json_decode(decrypt($user->two_factor_recovery_codes), true);
        }

        return view('municipality.two-factor-setup', compact('qrCode', 'recoveryCodes', 'setupKey'));
    }

    public function enable(Request $request, EnableTwoFactorAuthentication $enable)
    {
        $enable(Auth::user());

        return redirect()->route('municipality.2fa.setup')
            ->with('status', 'Scan the QR code with your authenticator app, then confirm below.');
    }

    public function confirm(Request $request, ConfirmTwoFactorAuthentication $confirm)
    {
        $request->validate([
            'code' => ['required', 'string'],
        ]);

        try {
            $confirm(Auth::user(), $request->code);
        } catch (\Exception $e) {
            return back()->withErrors(['code' => 'The code was invalid. Please try again.']);
        }

        return redirect()->route('municipality.2fa.setup')
            ->with('status', '✅ Two-factor authentication has been enabled successfully.');
    }

    public function disable(Request $request, DisableTwoFactorAuthentication $disable)
    {
        $disable(Auth::user());

        return redirect()->route('municipality.2fa.setup')
            ->with('status', 'Two-factor authentication has been disabled.');
    }
}