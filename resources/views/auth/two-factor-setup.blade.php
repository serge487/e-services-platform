<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup 2FA</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-xl shadow-md w-full max-w-md">
        <h1 class="text-2xl font-bold text-gray-800 mb-2">Setup Two-Factor Authentication</h1>
        <p class="text-gray-500 mb-6">Scan the QR code below with your authenticator app (e.g. Google Authenticator)</p>

        <!-- QR Code -->
        <div class="flex justify-center mb-6">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode($qrCodeUrl) }}" alt="QR Code" />
        </div>

        <!-- Verify Code Form -->
        <form method="POST" action="{{ route('2fa.enable') }}">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700">Enter the 6-digit code from your app</label>
                <input type="number" name="code" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" placeholder="123456" required />
                @error('code')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="mt-4 w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 px-4 rounded-md font-medium">
                Enable 2FA
            </button>
        </form>

        <a href="{{ route('citizen.dashboard') }}" class="mt-4 inline-block text-sm text-gray-500 hover:underline">Skip for now</a>
    </div>
</body>
</html>