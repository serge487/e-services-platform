/**
 * Subscribes to private user channel: unread-count + new notification rows (Reverb + Echo).
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatShortTime(iso) {
    if (!iso) {
        return '';
    }
    const d = new Date(iso);
    if (Number.isNaN(d.getTime())) {
        return '';
    }
    const sec = Math.floor((Date.now() - d.getTime()) / 1000);
    if (sec < 45) {
        return 'Just now';
    }
    if (sec < 3600) {
        return `${Math.floor(sec / 60)} min ago`;
    }
    if (sec < 86400) {
        return `${Math.floor(sec / 3600)} h ago`;
    }
    return d.toLocaleDateString(undefined, { month: 'short', day: 'numeric' });
}

export function initNotificationsRealtime() {
    const userId = typeof window !== 'undefined' ? window.__notificationUserId : undefined;
    if (!userId || !window.Echo) {
        return;
    }

    if (window.__notificationsRealtimeBound) {
        return;
    }
    window.__notificationsRealtimeBound = true;

    const channelName = `App.Models.User.${userId}`;
    const ch = window.Echo.private(channelName);

    ch.listen('.unread-count', (payload) => {
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

    ch.listen('.notification.created', (payload) => {
        const list = document.getElementById('notifications-live-list');
        if (!list) {
            return;
        }

        const id = payload?.id;
        if (!id) {
            return;
        }

        if (list.querySelector(`[data-notification-id="${id}"]`)) {
            return;
        }

        const data = payload.data || {};
        if (!data.chat_id) {
            return;
        }

        const empty = document.getElementById('notifications-empty-state');
        if (empty) {
            empty.remove();
        }

        const a = document.createElement('a');
        a.href = payload.open_path || '#';
        a.className =
            'list-group-item list-group-item-action py-3 text-decoration-none notification-row-unread';
        a.dataset.notificationId = id;

        const sender = escapeHtml(String(data.sender_name || 'Message'));
        const officeName = data.office_name ? escapeHtml(String(data.office_name)) : '';
        const officeHtml = officeName
            ? `<span class="text-muted fw-normal"> · ${officeName}</span>`
            : '';
        const preview = escapeHtml(String(data.preview || ''));
        const timeLabel = formatShortTime(payload.created_at);

        a.innerHTML = `<div class="d-flex justify-content-between gap-2">
            <div>
                <div class="fw-semibold text-dark">${sender}${officeHtml}</div>
                <div class="text-muted small mt-1">${preview}</div>
            </div>
            <span class="text-muted small text-nowrap">${escapeHtml(timeLabel)}</span>
        </div>`;

        list.prepend(a);
    });

    ch.error((status) => {
        console.warn('[Notifications] Echo subscription error', status);
    });
}
