import './bootstrap';

import Alpine from 'alpinejs';
import { initChatRealtime } from './chat-realtime';
import { initNotificationsRealtime } from './notifications-realtime';
import { initRevenueRealtime } from './revenue-realtime';

window.Alpine = Alpine;

Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
    initChatRealtime();
    initNotificationsRealtime();
    initRevenueRealtime();
});
