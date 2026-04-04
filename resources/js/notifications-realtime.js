/**
 * Subscribes to private user channel unread-count events (Reverb + Echo).
 * Updates all elements with [data-unread-notifications] (citizen + municipality UIs).
 */
export function initNotificationsRealtime() {
    const userId = typeof window !== 'undefined' ? window.__notificationUserId : undefined;
    if (!userId || !window.Echo) {
        return;
    }

    const channelName = `App.Models.User.${userId}`;

    window.Echo.private(channelName).listen('.unread-count', (payload) => {
        const n = Number(payload?.unread_count ?? 0);
        if (Number.isNaN(n)) {
            return;
        }

        document.querySelectorAll('[data-unread-notifications]').forEach((el) => {
            el.textContent = String(n);
            if (el.classList.contains('nav-badge')) {
                el.classList.toggle('d-none', n === 0);
            }
            if (el.classList.contains('count')) {
                el.classList.toggle('has-items', n > 0);
            }
            const wc = el.closest('.wc-stat');
            if (wc) {
                wc.classList.toggle('has-items', n > 0);
            }
        });
    });
}
