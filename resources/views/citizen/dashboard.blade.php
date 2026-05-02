<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Citizen Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

<div class="flex min-h-screen">

    <!-- Sidebar -->
    <aside class="w-64 bg-indigo-800 text-white flex flex-col">
        <div class="p-6 text-xl font-bold border-b border-indigo-700">
            👤 {{ auth()->user()->name }}
        </div>
        <nav class="flex-1 p-4 space-y-2">
            <a href="{{ route('citizen.services') }}" class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-indigo-700">
                🏛️ Services
            </a>
            <a href="{{ route('citizen.requests') }}" class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-indigo-700">
                📋 My Requests
            </a>
            <a href="{{ route('citizen.appointments') }}" class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-indigo-700">
                📅 Appointments
            </a>
            <a href="{{ route('citizen.notifications') }}" class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-indigo-700">
                🔔 Notifications
            </a>
            <a href="{{ route('citizen.history') }}" class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-indigo-700">
                🕓 History
            </a>
            <a href="{{ route('citizen.profile') }}" class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-indigo-700">
                ⚙️ Profile
            </a>
        </nav>
        <div class="p-4 border-t border-indigo-700">
            <form method="POST" action="/logout">
                @csrf
                <button type="submit" class="w-full text-left px-4 py-2 rounded-lg hover:bg-indigo-700">
                    🚪 Logout
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 p-8">

        <!-- Header row with title + settings icon -->
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Welcome, {{ auth()->user()->name }}! 👋</h1>

            <!-- Settings icon button -->
            <button
                onclick="document.getElementById('settings-modal').classList.remove('hidden')"
                class="p-2 rounded-full hover:bg-gray-200 transition"
                title="Edit profile"
            >
                <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="3"/>
                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                </svg>
            </button>
        </div>

        <!-- Flash success message -->
        @if(session('success'))
            <div class="mb-6 px-4 py-3 bg-green-50 text-green-700 rounded-lg text-sm border border-green-200">
                ✅ {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white rounded-xl shadow p-6">
                <h2 class="text-lg font-semibold text-gray-700">My Requests</h2>
                <p class="text-3xl font-bold text-indigo-600 mt-2">0</p>
            </div>
            <div class="bg-white rounded-xl shadow p-6">
                <h2 class="text-lg font-semibold text-gray-700">Appointments</h2>
                <p class="text-3xl font-bold text-indigo-600 mt-2">0</p>
            </div>
            <div class="bg-white rounded-xl shadow p-6">
                <h2 class="text-lg font-semibold text-gray-700">Notifications</h2>
                <p class="text-3xl font-bold text-indigo-600 mt-2">0</p>
            </div>
        </div>
    </main>

</div>

<!-- ===== SETTINGS MODAL ===== -->
<div
    id="settings-modal"
    class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40"
    onclick="if(event.target===this) this.classList.add('hidden')"
>
    <div class="bg-white rounded-xl shadow-lg w-full max-w-md mx-4 p-6">

        <!-- Modal header -->
        <div class="flex items-center justify-between mb-5">
            <div>
                <h2 class="text-base font-semibold text-gray-900">Personal Information</h2>
                <p class="text-xs text-gray-500 mt-0.5">Update your name and phone number</p>
            </div>
            <button
                onclick="document.getElementById('settings-modal').classList.add('hidden')"
                class="text-gray-400 hover:text-gray-600 transition"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M18 6L6 18M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Avatar + identity -->
        <div class="flex items-center gap-3 pb-4 mb-4 border-b border-gray-100">
            <div class="w-11 h-11 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-semibold text-sm">
                {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
            </div>
            <div>
                <p class="text-sm font-medium text-gray-900">{{ auth()->user()->name }}</p>
                <p class="text-xs text-gray-400">Citizen ID #{{ auth()->id() }}</p>
            </div>
        </div>

        <!-- Form -->
        <form action="{{ route('citizen.profile.update') }}" method="POST">
            @csrf
            @method('PATCH')

            <div class="space-y-4">

                <!-- Name -->
                <div>
                    <label class="block text-xs text-gray-500 mb-1.5">Full name</label>
                    <input
                        type="text"
                        name="name"
                        value="{{ old('name', auth()->user()->name) }}"
                        required
                        class="w-full h-9 px-3 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                    @error('name')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Phone number -->
                <div>
                    <label class="block text-xs text-gray-500 mb-1.5">Phone number</label>
                    <input
                        type="tel"
                        name="phone_number"
                        value="{{ old('phone_number', auth()->user()->phone_number) }}"
                        required
                        class="w-full h-9 px-3 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                    @error('phone_number')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email (read-only) -->
                <div>
                    <label class="block text-xs text-gray-500 mb-1.5">
                        Email address
                        <span class="ml-1.5 text-[10px] bg-gray-100 text-gray-400 px-1.5 py-0.5 rounded border border-gray-200">read-only</span>
                    </label>
                    <input
                        type="email"
                        value="{{ auth()->user()->email }}"
                        readonly
                        class="w-full h-9 px-3 text-sm border border-gray-100 rounded-lg bg-gray-50 text-gray-400 cursor-not-allowed"
                    >
                </div>

            </div>

            <!-- Actions -->
            <div class="flex justify-end gap-2 mt-5">
                <button
                    type="button"
                    onclick="document.getElementById('settings-modal').classList.add('hidden')"
                    class="px-4 h-9 text-sm text-gray-500 border border-gray-200 rounded-lg hover:bg-gray-50 transition"
                >
                    Cancel
                </button>
                <button
                    type="submit"
                    class="px-4 h-9 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition"
                >
                    Save changes
                </button>
            </div>
        </form>

    </div>
</div>

<!-- Re-open modal if there were validation errors -->
@if($errors->any())
<script>
    document.getElementById('settings-modal').classList.remove('hidden');
</script>
@endif

</body>
</html>