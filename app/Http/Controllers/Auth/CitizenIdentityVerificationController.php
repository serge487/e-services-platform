<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\CitizenIdentityProfile;
use App\Services\OcrService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CitizenIdentityVerificationController extends Controller
{
    public function __construct(
        protected OcrService $ocrService
    ) {}

    /**
     * @return View|RedirectResponse
     */
    public function show(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'citizen') {
            abort(403);
        }

        if ($user->identity_verified_at || config('citizen.skip_identity_verification_gate')) {
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

        $user = $request->user();

        if ($user->role !== 'citizen' || $user->identity_verified_at) {
            return redirect()->route('citizen.dashboard');
        }

        $path = $request->file('id_photo')->store('id_documents', 'public');
        $absolutePath = Storage::disk('public')->path($path);

        $ocrData = $this->ocrService->extractFromImagePath(
            $absolutePath,
            $request->file('id_photo')->getClientOriginalName()
        );

        $meta = $ocrData['_meta'] ?? [];
        if (array_key_exists('_meta', $ocrData)) {
            unset($ocrData['_meta']);
        }

        $prefill = array_merge($ocrData, ['_id_card_path' => $path]);
        $request->session()->put('identity_ocr_prefill', $prefill);

        CitizenIdentityProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'id_document_path'       => $path,
                'ocr_raw_text'           => $ocrData['raw_text'] !== '' ? $ocrData['raw_text'] : null,
                'extracted_at'           => now(),
                'full_name'              => $ocrData['name'] ?? null,
                'national_id_number'     => $ocrData['id_number'] ?? null,
                'date_of_birth'          => $ocrData['dob'] ?? null,
                'place_of_birth'         => $ocrData['place_of_birth'] ?? null,
                'father_name'            => $ocrData['father_name'] ?? null,
                'confirmed_at'           => null,
            ]
        );

        $rawLen = strlen(trim((string) ($ocrData['raw_text'] ?? '')));
        $nameLen = strlen(trim((string) ($ocrData['name'] ?? '')));
        $hasParsed = ($ocrData['id_number'] ?? '') !== ''
            || ($ocrData['dob'] ?? '') !== ''
            || $nameLen >= 3;

        $reason = $meta['reason'] ?? null;
        $redirect = redirect()->route('citizen.identity-verification.show');

        if (($meta['source'] ?? '') === 'none' && $reason === 'missing_api_key') {
            return $redirect->with(
                'warning',
                'OCR is not configured. Add OCR_SPACE_API_KEY to your .env file, or type your ID details manually. Your photo was saved.'
            );
        }

        if (in_array($reason, ['http_error', 'api_error', 'unreadable_file'], true)) {
            $hint = is_string($meta['api_message'] ?? null) && $meta['api_message'] !== ''
                ? ' ('.$meta['api_message'].')'
                : '';

            return $redirect->with(
                'warning',
                'The OCR service could not read this image.'.$hint.' Try a clearer, well-lit photo, or fill the form manually.'
            );
        }

        if ($rawLen === 0 && ! $hasParsed) {
            return $redirect->with(
                'warning',
                'No text was detected. Center the whole card, avoid glare and blur, or enter your details manually.'
            );
        }

        if ($rawLen > 0 && ! $hasParsed) {
            return $redirect->with(
                'warning',
                'Some text was read but fields were not filled automatically. Expand “Raw OCR text” below and type values into the form.'
            );
        }

        return $redirect->with('status', 'Details were suggested from your ID—please review and correct them before saving.');
    }

    public function confirm(Request $request): RedirectResponse
    {
        $user = $request->user();

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

        $rawOcr = $prefill['raw_text'] ?? null;

        CitizenIdentityProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'full_name'            => $validated['name'],
                'national_id_number'   => $validated['id_number'],
                'date_of_birth'        => $validated['dob'],
                'place_of_birth'       => $validated['place_of_birth'] ?? null,
                'father_name'          => $validated['father_name'] ?? null,
                'id_document_path'     => $idCardPath,
                'ocr_raw_text'         => $rawOcr,
                'confirmed_at'         => now(),
            ]
        );

        session()->forget('identity_ocr_prefill');
        $request->session()->put('citizen_session_unlocked', true);

        return redirect()
            ->route('citizen.dashboard')
            ->with('success', 'Identity verified. Welcome to your dashboard.');
    }
}
