<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Citizen Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
            <h1 class="text-2xl font-bold text-gray-800">Welcome, {{ auth()->user()->name }}! </h1>

            <!-- Settings icon button -->
            <button
                data-bs-toggle="modal" data-bs-target="#settings-modal"
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>