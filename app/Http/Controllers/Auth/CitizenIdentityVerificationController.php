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
            'id_photo_front' => ['required', 'file', 'mimes:jpg,jpeg,png', 'max:5120'],
            'id_photo_back'  => ['required', 'file', 'mimes:jpg,jpeg,png', 'max:5120'],
        ]);

        $user = $request->user();

        if ($user->role !== 'citizen' || $user->identity_verified_at) {
            return redirect()->route('citizen.dashboard');
        }

        $pathFront = $request->file('id_photo_front')->store('id_documents', 'public');
        $pathBack = $request->file('id_photo_back')->store('id_documents', 'public');

        $absoluteFront = Storage::disk('public')->path($pathFront);
        $absoluteBack = Storage::disk('public')->path($pathBack);

        $ocrData = $this->ocrService->extractFromFrontAndBackPaths(
            $absoluteFront,
            $request->file('id_photo_front')->getClientOriginalName(),
            $absoluteBack,
            $request->file('id_photo_back')->getClientOriginalName(),
        );

        $meta = $ocrData['_meta'] ?? [];
        if (array_key_exists('_meta', $ocrData)) {
            unset($ocrData['_meta']);
        }

        $prefill = array_merge($ocrData, [
            '_id_card_front_path' => $pathFront,
            '_id_card_back_path'  => $pathBack,
        ]);
        $request->session()->put('identity_ocr_prefill', $prefill);

        CitizenIdentityProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'id_document_front_path' => $pathFront,
                'id_document_back_path'  => $pathBack,
                'id_document_path'       => $pathFront,
                'ocr_raw_text'           => ($ocrData['raw_text'] ?? '') !== '' ? $ocrData['raw_text'] : null,
                'ocr_raw_text_front'     => ($ocrData['raw_text_front'] ?? '') !== '' ? $ocrData['raw_text_front'] : null,
                'ocr_raw_text_back'      => ($ocrData['raw_text_back'] ?? '') !== '' ? $ocrData['raw_text_back'] : null,
                'extracted_at'           => now(),
                'full_name'              => $ocrData['name'] ?? null,
                'national_id_number'     => $ocrData['id_number'] ?? null,
                'date_of_birth'          => $ocrData['dob'] ?? null,
                'place_of_birth'         => $ocrData['place_of_birth'] ?? null,
                'father_name'            => $ocrData['father_name'] ?? null,
                'mother_name'            => $ocrData['mother_name'] ?? null,
                'grandfather_name'       => $ocrData['grandfather_name'] ?? null,
                'gender'                 => $ocrData['gender'] ?? null,
                'blood_type'             => $ocrData['blood_type'] ?? null,
                'registry_number'        => $ocrData['registry_number'] ?? null,
                'id_issue_date'          => $ocrData['issue_date'] ?? null,
                'id_expiry_date'         => $ocrData['expiry_date'] ?? null,
                'confirmed_at'           => null,
            ]
        );

        $rawLen = strlen(trim((string) ($ocrData['raw_text'] ?? '')));
        $nameLen = strlen(trim((string) ($ocrData['name'] ?? '')));
        $hasParsed = ($ocrData['id_number'] ?? '') !== ''
            || ($ocrData['dob'] ?? '') !== ''
            || $nameLen >= 3
            || ($ocrData['father_name'] ?? '') !== ''
            || ($ocrData['mother_name'] ?? '') !== ''
            || ($ocrData['place_of_birth'] ?? '') !== '';

        $reason = $meta['reason'] ?? null;
        $redirect = redirect()->route('citizen.identity-verification.show');

        if (($meta['source'] ?? '') === 'none' && $reason === 'missing_api_key') {
            return $redirect->with(
                'warning',
                'OCR is not configured. Add OCR_SPACE_API_KEY to your .env file, or type your ID details manually. Your photos were saved.'
            );
        }

        if (in_array($reason, ['http_error', 'api_error', 'unreadable_file'], true)) {
            $hint = is_string($meta['api_message'] ?? null) && $meta['api_message'] !== ''
                ? ' ('.$meta['api_message'].')'
                : '';

            return $redirect->with(
                'warning',
                'The OCR service could not read one or both images.'.$hint.' Try clearer photos, or fill the form manually.'
            );
        }

        if ($rawLen === 0 && ! $hasParsed) {
            return $redirect->with(
                'warning',
                'No text was detected on the front or back. Retake well-lit, straight photos, or enter details manually.'
            );
        }

        if ($rawLen > 0 && ! $hasParsed) {
            return $redirect->with(
                'warning',
                'Some text was read but main fields were not filled automatically. Use “Raw OCR text” below and complete the form.'
            );
        }

        $frontOk = $meta['front_ok'] ?? false;
        $backOk = $meta['back_ok'] ?? false;
        if ($frontOk && ! $backOk) {
            return $redirect->with(
                'warning',
                'Front read OK; back had little or no text. Check the back photo or type back-side fields manually.'
            );
        }
        if (! $frontOk && $backOk) {
            return $redirect->with(
                'warning',
                'Back read OK; front had little or no text. Check the front photo or type front-side fields manually.'
            );
        }

        return $redirect->with('status', 'Details were suggested from your ID—please review front and back fields before saving.');
    }

    public function confirm(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->role !== 'citizen' || $user->identity_verified_at) {
            return redirect()->route('citizen.dashboard');
        }

        $prefill = session('identity_ocr_prefill', []);
        $idCardFront = $prefill['_id_card_front_path'] ?? null;
        $idCardBack = $prefill['_id_card_back_path'] ?? null;

        $validated = $request->validate([
            'name'               => ['required', 'string', 'max:255'],
            'id_number'          => ['required', 'string', 'max:32'],
            'dob'                => ['required', 'date'],
            'place_of_birth'     => ['nullable', 'string', 'max:255'],
            'father_name'        => ['nullable', 'string', 'max:255'],
            'mother_name'        => ['nullable', 'string', 'max:255'],
            'grandfather_name'   => ['nullable', 'string', 'max:255'],
            'gender'             => ['nullable', 'string', 'max:64'],
            'blood_type'         => ['nullable', 'string', 'max:16'],
            'registry_number'    => ['nullable', 'string', 'max:64'],
            'issue_date'         => ['nullable', 'date'],
            'expiry_date'        => ['nullable', 'date'],
        ]);

        if (! $idCardFront || ! $idCardBack) {
            return back()->withErrors(['id_photo_front' => 'Please upload clear photos of both the front and back of your ID, then run OCR.']);
        }

        $user->update([
            'name'                 => $validated['name'],
            'id_number'            => $validated['id_number'],
            'dob'                  => $validated['dob'],
            'place_of_birth'       => $validated['place_of_birth'] ?? null,
            'father_name'          => $validated['father_name'] ?? null,
            'id_card_path'         => $idCardFront,
            'id_card_back_path'    => $idCardBack,
            'identity_verified_at' => now(),
        ]);

        $rawCombined = $prefill['raw_text'] ?? null;
        $rawFront = $prefill['raw_text_front'] ?? null;
        $rawBack = $prefill['raw_text_back'] ?? null;

        CitizenIdentityProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'full_name'              => $validated['name'],
                'national_id_number'     => $validated['id_number'],
                'date_of_birth'          => $validated['dob'],
                'place_of_birth'         => $validated['place_of_birth'] ?? null,
                'father_name'            => $validated['father_name'] ?? null,
                'mother_name'            => $validated['mother_name'] ?? null,
                'grandfather_name'       => $validated['grandfather_name'] ?? null,
                'gender'                 => $validated['gender'] ?? null,
                'blood_type'             => $validated['blood_type'] ?? null,
                'registry_number'        => $validated['registry_number'] ?? null,
                'id_issue_date'          => $validated['issue_date'] ?? null,
                'id_expiry_date'         => $validated['expiry_date'] ?? null,
                'id_document_front_path' => $idCardFront,
                'id_document_back_path'  => $idCardBack,
                'id_document_path'       => $idCardFront,
                'ocr_raw_text'           => $rawCombined,
                'ocr_raw_text_front'     => $rawFront,
                'ocr_raw_text_back'      => $rawBack,
                'confirmed_at'           => now(),
            ]
        );

        session()->forget('identity_ocr_prefill');
        $request->session()->put('citizen_session_unlocked', true);

        return redirect()
            ->intended(route('citizen.dashboard'))
            ->with('success', 'Identity verified. Welcome to your dashboard.');
    }
}
