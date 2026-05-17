import axios from 'axios';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.axios = axios;
window.Pusher = Pusher;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.headers.common['X-CSRF-TOKEN'] =
    document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

const bc = typeof window !== 'undefined' ? window.__broadcasting : undefined;
// Prefer Blade-injected config: a baked VITE_REVERB_APP_KEY can be wrong/stale and break only some origins.
const reverbKey = bc?.key || import.meta.env.VITE_REVERB_APP_KEY || '';
const port = Number(bc?.wsPort ?? import.meta.env.VITE_REVERB_PORT ?? 8080);
const tls =
    typeof window !== 'undefined' && window.location?.protocol === 'https:';
// Always match the browser hostname so ws:// uses the same host as the page (localhost vs 127.0.0.1).
const wsHost =
    typeof window !== 'undefined' && window.location?.hostname
        ? window.location.hostname
        : import.meta.env.VITE_REVERB_HOST || bc?.wsHost || '127.0.0.1';

if (reverbKey) {
    const origin = typeof window !== 'undefined' ? window.location.origin : '';
    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: reverbKey,
        wsHost,
        wsPort: port,
        wssPort: port,
        forceTLS: tls,
        enabledTransports: ['ws', 'wss'],
        authEndpoint: origin ? `${origin}/broadcasting/auth` : '/broadcasting/auth',
        auth: {
            headers: {
                'X-CSRF-TOKEN':
                    document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '',
            },
        },
    });
}
