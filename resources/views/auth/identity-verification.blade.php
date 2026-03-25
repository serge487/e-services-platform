<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify identity</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen py-10">
    <div class="bg-white p-8 rounded-xl shadow-md w-full max-w-lg mx-4">
        <h1 class="text-2xl font-bold text-gray-800 mb-2">Lebanese national ID</h1>
        <p class="text-gray-500 mb-6">You are signed in as <span class="font-medium text-gray-700">{{ auth()->user()->email }}</span>. Upload a clear photo of your Lebanese ID card, then run OCR. Extracted details appear below—edit if needed, then save to continue.</p>

        @if (session('status'))
            <div class="mb-4 text-sm text-green-600">{{ session('status') }}</div>
        @endif
        @if (session('warning'))
            <div class="mb-4 text-sm text-amber-800 bg-amber-50 border border-amber-200 rounded-md px-3 py-2">{{ session('warning') }}</div>
        @endif

        <div class="border border-gray-200 rounded-lg p-4 mb-8">
            <h2 class="text-sm font-semibold text-gray-700 mb-3">1. Upload ID photo</h2>
            <form method="POST" action="{{ route('citizen.identity-verification.extract', absolute: false) }}" enctype="multipart/form-data">
                @csrf
                <input type="file" name="id_photo" accept="image/jpeg,image/png,image/jpg" required
                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                @error('id_photo')
                    <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                @enderror
                <button type="submit" class="mt-4 w-full bg-gray-800 hover:bg-gray-900 text-white py-2 px-4 rounded-md text-sm font-medium">
                    Extract with OCR
                </button>
            </form>
            @if (empty(config('services.ocr_space_api_key')))
                <p class="text-amber-600 text-xs mt-2">OCR is not configured (<code class="text-xs">OCR_SPACE_API_KEY</code>). Upload will still store your photo; fill the form manually.</p>
            @endif
        </div>

        @php
            $p = $prefill ?? [];
        @endphp
        <div class="border border-gray-200 rounded-lg p-4">
            <h2 class="text-sm font-semibold text-gray-700 mb-3">2. Confirm your details</h2>
            @if (! empty(trim($p['raw_text'] ?? '')))
                <details class="mb-4 text-sm border border-gray-200 rounded-md p-3 bg-gray-50">
                    <summary class="cursor-pointer font-medium text-gray-700">Raw OCR text</summary>
                    <pre class="mt-2 whitespace-pre-wrap break-words text-xs text-gray-600 max-h-48 overflow-y-auto">{{ $p['raw_text'] }}</pre>
                </details>
            @endif
            <form method="POST" action="{{ route('citizen.identity-verification.confirm', absolute: false) }}">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700">Full name (as on ID)</label>
                    <input type="text" name="name" value="{{ old('name', $p['name'] ?? '') }}" required lang="ar"
                        dir="auto"
                        class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" />
                    @if (! empty($p['name_en']))
                        <p class="mt-1 text-xs text-gray-500"><span class="font-medium text-gray-600">English (reference):</span> {{ $p['name_en'] }}</p>
                    @endif
                    @error('name')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700">National ID number</label>
                    <input type="text" name="id_number" value="{{ old('id_number', $p['id_number'] ?? '') }}" required inputmode="numeric" autocomplete="off"
                        class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" />
                    @error('id_number')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700">Date of birth</label>
                    <input type="date" name="dob" value="{{ old('dob', $p['dob'] ?? '') }}" required
                        class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" />
                    @error('dob')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700">Place of birth</label>
                    <input type="text" name="place_of_birth" value="{{ old('place_of_birth', $p['place_of_birth'] ?? '') }}" lang="ar" dir="auto"
                        class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" />
                    @if (! empty($p['place_of_birth_en']))
                        <p class="mt-1 text-xs text-gray-500"><span class="font-medium text-gray-600">English (reference):</span> {{ $p['place_of_birth_en'] }}</p>
                    @endif
                    @error('place_of_birth')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700">Father's name (if shown on ID)</label>
                    <input type="text" name="father_name" value="{{ old('father_name', $p['father_name'] ?? '') }}" lang="ar" dir="auto"
                        class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" />
                    @if (! empty($p['father_name_en']))
                        <p class="mt-1 text-xs text-gray-500"><span class="font-medium text-gray-600">English (reference):</span> {{ $p['father_name_en'] }}</p>
                    @endif
                    @error('father_name')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
                <button type="submit" class="mt-6 w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 px-4 rounded-md font-medium">
                    Save and continue to dashboard
                </button>
            </form>
        </div>

        <div class="mt-6 text-center text-sm text-gray-500">
            Wrong account?
            <form method="POST" action="{{ route('logout', absolute: false) }}" class="inline">
                @csrf
                <button type="submit" class="text-indigo-600 hover:underline font-medium">Sign out and start at login</button>
            </form>
        </div>
    </div>
</body>
</html>
