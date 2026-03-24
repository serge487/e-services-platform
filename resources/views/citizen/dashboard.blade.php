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
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Welcome, {{ auth()->user()->name }}! 👋</h1>

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

</body>
</html>