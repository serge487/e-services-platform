<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OcrService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    protected OcrService $ocrService;

    public function __construct(OcrService $ocrService)
    {
        $this->ocrService = $ocrService;
    }

    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'email'       => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password'    => ['required', 'confirmed', Rules\Password::defaults()],
            'id_document' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ]);

        // Store the ID document
        $idPath = $request->file('id_document')->store('id_documents', 'public');

        // Call OCR API to extract info from ID
        $ocrData = $this->ocrService->extractFromId($request->file('id_document'));

        // Use OCR extracted name if available, otherwise use form name
        $name = $ocrData['name'] ?? $request->name;

        // Create the user with role citizen
        $user = User::create([
            'name'         => $name,
            'email'        => $request->email,
            'password'     => Hash::make($request->password),
            'role'         => 'citizen',
            'is_active'    => true,
            'id_card_path' => $idPath,
            'id_number'    => $ocrData['id_number'] ?? null,
            'dob'          => $ocrData['dob'] ?? null,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('2fa.setup');
    }
}