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
        <p class="text-gray-500 mb-6">Upload a clear photo of your ID. We will read the text automatically—please review and correct every field before saving.</p>

        @if (session('status'))
            <div class="mb-4 text-sm text-green-600">{{ session('status') }}</div>
        @endif

        <div class="border border-gray-200 rounded-lg p-4 mb-8">
            <h2 class="text-sm font-semibold text-gray-700 mb-3">1. Upload ID photo</h2>
            <form method="POST" action="{{ route('citizen.identity-verification.extract') }}" enctype="multipart/form-data">
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
            <form method="POST" action="{{ route('citizen.identity-verification.confirm') }}">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700">Full name</label>
                    <input type="text" name="name" value="{{ old('name', $p['name'] ?? auth()->user()->name) }}" required
                        class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" />
                    @error('name')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700">National ID number</label>
                    <input type="text" name="id_number" value="{{ old('id_number', $p['id_number'] ?? auth()->user()->id_number) }}" required
                        class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" />
                    @error('id_number')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700">Date of birth</label>
                    <input type="date" name="dob" value="{{ old('dob', $p['dob'] ?? optional(auth()->user()->dob)->format('Y-m-d')) }}" required
                        class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" />
                    @error('dob')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700">Place of birth</label>
                    <input type="text" name="place_of_birth" value="{{ old('place_of_birth', $p['place_of_birth'] ?? auth()->user()->place_of_birth) }}"
                        class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" />
                    @error('place_of_birth')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700">Father's name (if shown on ID)</label>
                    <input type="text" name="father_name" value="{{ old('father_name', $p['father_name'] ?? auth()->user()->father_name) }}"
                        class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" />
                    @error('father_name')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
                <button type="submit" class="mt-6 w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 px-4 rounded-md font-medium">
                    Save and continue to dashboard
                </button>
            </form>
        </div>
    </div>
</body>
</html>
