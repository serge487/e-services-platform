@php
    $reverb = config('broadcasting.connections.reverb');
    $useReverb = config('broadcasting.default') === 'reverb' && ! empty($reverb['key']);
    $broadcastingConfig = $useReverb ? [
        'key' => $reverb['key'],
        'wsHost' => request()->getHost(),
        'wsPort' => (int) env('REVERB_PORT', 8080),
        'scheme' => env('REVERB_SCHEME', 'http'),
    ] : null;
@endphp
@if ($broadcastingConfig)
    {{-- Server-side Reverb settings so Echo works without VITE_* baked at build time (php artisan serve only). --}}
    <script>
        window.__broadcasting = @json($broadcastingConfig);
    </script>
@endif
@auth
    <script>
        window.__notificationUserId = {{ auth()->id() }};
    </script>
@endauth
