<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\OcrService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CitizenIdentityVerificationController extends Controller
{
    public function __construct(
        protected OcrService $ocrService
    ) {}

    public function show(): View|RedirectResponse
    {
        $user = Auth::user();

        if ($user->role !== 'citizen') {
            abort(403);
        }

        if ($user->identity_verified_at) {
            return redirect()->route('citizen.dashboard');
        }

        $prefill = session('identity_ocr_prefill', []);

        return view('auth.identity-verification', [
            'prefill' => $prefill,
        ]);
    }

    public function extract(Request $request): RedirectResponse
    {
        $request->validate([
            'id_photo' => ['required', 'file', 'mimes:jpg,jpeg,png', 'max:5120'],
        ]);

        $user = Auth::user();

        if ($user->role !== 'citizen' || $user->identity_verified_at) {
            return redirect()->route('citizen.dashboard');
        }

        $ocrData = $this->ocrService->extractFromId($request->file('id_photo'));

        $path = $request->file('id_photo')->store('id_documents', 'public');

        session([
            'identity_ocr_prefill' => array_merge($ocrData, [
                '_id_card_path' => $path,
            ]),
        ]);

        return redirect()
            ->route('citizen.identity-verification.show')
            ->with('status', 'Details were read from your ID. Please review and edit if needed.');
    }

    public function confirm(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if ($user->role !== 'citizen' || $user->identity_verified_at) {
            return redirect()->route('citizen.dashboard');
        }

        $prefill = session('identity_ocr_prefill', []);
        $idCardPath = $prefill['_id_card_path'] ?? $user->id_card_path;

        $validated = $request->validate([
            'name'            => ['required', 'string', 'max:255'],
            'id_number'       => ['required', 'string', 'max:32'],
            'dob'             => ['required', 'date'],
            'place_of_birth'  => ['nullable', 'string', 'max:255'],
            'father_name'     => ['nullable', 'string', 'max:255'],
        ]);

        if (! $idCardPath) {
            return back()->withErrors(['id_photo' => 'Please upload a photo of your ID first.']);
        }

        $user->update([
            'name'                  => $validated['name'],
            'id_number'             => $validated['id_number'],
            'dob'                   => $validated['dob'],
            'place_of_birth'        => $validated['place_of_birth'] ?? null,
            'father_name'           => $validated['father_name'] ?? null,
            'id_card_path'          => $idCardPath,
            'identity_verified_at'  => now(),
        ]);

        session()->forget('identity_ocr_prefill');
        $request->session()->put('citizen_session_unlocked', true);

        return redirect()
            ->route('citizen.dashboard')
            ->with('success', 'Identity verified. Welcome to your dashboard.');
    }
}
