<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Verify 2FA</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-xl shadow-md w-full max-w-md">
        <h1 class="text-2xl font-bold text-gray-800 mb-2">Two-Factor Authentication</h1>
        <p class="text-gray-500 mb-6">Enter the 6-digit code from your authenticator app to continue.</p>

        <form method="POST" action="{{ route('citizen.2fa.validate', absolute: false) }}">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700">Authentication Code</label>
                <input type="number" name="code" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" placeholder="123456" required autofocus />
                @error('code')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="mt-4 w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 px-4 rounded-md font-medium">
                Verify Code
            </button>
        </form>

        @if (! empty($allow2faSkip))
            <form method="POST" action="{{ route('citizen.2fa.skip-testing', absolute: false) }}" class="mt-6 border-t border-gray-200 pt-4">
                @csrf
                <p class="text-xs text-amber-700 mb-2">Testing only: bypass 2FA when <code class="bg-amber-50 px-1 rounded">CITIZEN_ALLOW_2FA_SKIP=true</code> is set.</p>
                <button type="submit" class="text-sm text-gray-600 hover:text-gray-900 underline">
                    Skip 2FA (dev / QA)
                </button>
            </form>
        @endif

        <form method="POST" action="{{ route('web.logout', absolute: false) }}" class="mt-4">
            @csrf
            <button type="submit" class="text-sm text-gray-500 hover:underline">
                Logout and go back
            </button>
        </form>
    </div>
</body>
</html>