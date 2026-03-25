<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-xl shadow-md w-full max-w-md">

        <h1 class="text-2xl font-bold text-gray-800 mb-2">Create Account 🏛️</h1>
        <p class="text-gray-500 mb-6">Register to access government e-services</p>

        <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data">
            @csrf

            <!-- Name -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Full Name</label>
                <input type="text" name="name" value="{{ old('name') }}" required autofocus
                    class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" />
                @error('name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email -->
            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required
                    class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" />
                @error('email')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password -->
            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700">Password</label>
                <input type="password" name="password" required
                    class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" />
                @error('password')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Confirm Password -->
            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700">Confirm Password</label>
                <input type="password" name="password_confirmation" required
                    class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" />
            </div>

            <!-- ID Upload -->
            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700">Upload Your ID 🪪</label>
                <input type="file" name="id_document" accept="image/*,.pdf" required
                    class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                @error('id_document')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Register Button -->
            <button type="submit" class="mt-6 w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 px-4 rounded-md font-medium">
                Create Account
            </button>
        </form>

        <!-- Divider -->
        <div class="mt-6 flex items-center justify-center gap-2">
            <hr class="w-full border-gray-300">
            <span class="text-sm text-gray-500 whitespace-nowrap">OR</span>
            <hr class="w-full border-gray-300">
        </div>

        <!-- Social Login -->
        <div class="mt-4 flex flex-col gap-3">
            <a href="{{ route('social.redirect', 'google') }}" class="flex items-center justify-center gap-2 w-full py-2 px-4 bg-red-500 hover:bg-red-600 text-white rounded-md text-sm font-medium">
                Continue with Google
            </a>
            <a href="{{ route('social.redirect', 'facebook') }}" class="flex items-center justify-center gap-2 w-full py-2 px-4 bg-blue-600 hover:bg-blue-700 text-white rounded-md text-sm font-medium">
                Continue with Facebook
            </a>
        </div>

        <!-- Login Link -->
        <p class="mt-6 text-center text-sm text-gray-600">
            Already have an account?
            <a href="{{ route('login') }}" class="text-indigo-600 hover:underline font-medium">Sign in</a>
        </p>

    </div>
</body>
</html>