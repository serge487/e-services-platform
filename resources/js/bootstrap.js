import axios from 'axios';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.axios = axios;
window.Pusher = Pusher;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.headers.common['X-CSRF-TOKEN'] =
    document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

const bc = typeof window !== 'undefined' ? window.__broadcasting : undefined;
const reverbKey = import.meta.env.VITE_REVERB_APP_KEY || bc?.key;
const port = Number(import.meta.env.VITE_REVERB_PORT ?? bc?.wsPort ?? 8080);
const scheme = import.meta.env.VITE_REVERB_SCHEME ?? bc?.scheme ?? 'http';
const tls = scheme === 'https';
const wsHost =
    import.meta.env.VITE_REVERB_HOST || bc?.wsHost || window.location.hostname;

if (reverbKey) {
    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: reverbKey,
        wsHost,
        wsPort: port,
        wssPort: port,
        forceTLS: tls,
        enabledTransports: ['ws', 'wss'],
        auth: {
            headers: {
                'X-CSRF-TOKEN':
                    document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '',
            },
        },
    });
}
