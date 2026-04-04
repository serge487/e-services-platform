@php
    $reverb = config('broadcasting.connections.reverb');
    $useReverb = config('broadcasting.default') === 'reverb' && ! empty($reverb['key']);
@endphp
@if ($useReverb)
    {{-- Server-side Reverb settings so Echo works without VITE_* baked at build time (php artisan serve only). --}}
    <script>
        window.__broadcasting = @json([
            'key' => $reverb['key'],
            'wsHost' => request()->getHost(),
            'wsPort' => (int) env('REVERB_PORT', 8080),
            'scheme' => env('REVERB_SCHEME', 'http'),
        ]);
    </script>
@endif
