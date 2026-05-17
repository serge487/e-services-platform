/**
 * Subscribes to private user channel: unread-count + new notification rows (Reverb + Echo).
 * Same live pattern as chat: badge updates on every page; list + toast when applicable.
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

function getCsrfTokenFromDom() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
}

function deleteNotificationRow(deleteUrl, row) {
    fetch(deleteUrl, {
        method: 'DELETE',
        credentials: 'same-origin',
        headers: {
            'X-CSRF-TOKEN': getCsrfTokenFromDom(),
            Accept: 'application/json',
            'Content-Type': 'application/json',
        },
    })
        .then((res) => {
            if (res.ok) {
                row.remove();
                const list = document.getElementById('notifications-live-list');
                if (!list) {
                    return;
                }
                const hasItems = list.querySelector('.list-group-item[data-notification-id]');
                if (!hasItems) {
                    list.innerHTML = `
                        <div class="list-group-item border-0 text-center py-5 text-muted" id="empty-state">
                            <i class="bi bi-bell fs-1 d-block mb-2"></i>
                            <p class="mb-0">No notifications yet.</p>
                        </div>`;
                    document.getElementById('clear-all-btn')?.remove();
                }
            }
        })
        .catch(() => {});
}

function showLiveNotificationToast(payload) {
    const openPath = payload?.open_path;
    const data = payload?.data || {};
    if (!openPath) {
        return;
    }

    const isFeedback = data.type === 'feedback_reply';
    const title = isFeedback
        ? 'Municipality replied to your review'
        : String(data.sender_name || data.service_name || 'New notification');
    const preview = String(data.preview || '');

    let container = document.getElementById('live-notification-toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'live-notification-toast-container';
        container.style.cssText =
            'position:fixed;bottom:1.25rem;right:1.25rem;z-index:9999;display:flex;flex-direction:column;gap:0.5rem;max-width:360px;';
        document.body.appendChild(container);
    }

    const toast = document.createElement('a');
    toast.href = openPath;
    toast.className = 'text-decoration-none text-dark';
    toast.style.cssText =
        'display:block;background:#fff;border:1px solid #e2e8f0;border-left:4px solid #0a5c4a;' +
        'border-radius:10px;padding:0.75rem 1rem;box-shadow:0 4px 16px rgba(0,0,0,0.12);animation:liveNotifIn 0.25s ease;';
    toast.innerHTML = `
        <div class="fw-semibold small">${escapeHtml(title)}</div>
                <div class="text-muted" style="font-size:0.8rem;margin-top:0.25rem;">${escapeHtml(preview)}</div>
        <div style="font-size:0.72rem;color:#0a5c4a;margin-top:0.35rem;font-weight:600;">Tap to open →</div>
    `;

    container.prepend(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.3s';
        setTimeout(() => toast.remove(), 300);
    }, 8000);
}

function buildNotificationRowHtml(payload) {
    const data = payload.data || {};
    const isFeedback = data.type === 'feedback_reply';

    const title = escapeHtml(
        isFeedback
            ? `↩ ${data.sender_name || 'Municipality reply'}`
            : String(data.sender_name || data.service_name || 'Notification'),
    );
    const officeName = data.office_name ? escapeHtml(String(data.office_name)) : '';
    const officeHtml = officeName
        ? `<span class="text-muted fw-normal"> · ${officeName}</span>`
        : '';
    const preview = escapeHtml(String(data.preview || ''));
    const timeLabel = escapeHtml(formatShortTime(payload.created_at));

    return { title, officeHtml, preview, timeLabel };
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

    if (!document.getElementById('live-notification-toast-style')) {
        const style = document.createElement('style');
        style.id = 'live-notification-toast-style';
        style.textContent =
            '@keyframes liveNotifIn{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:translateY(0)}}';
        document.head.appendChild(style);
    }

    const ch = window.Echo.private(`App.Models.User.${userId}`);

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
        showLiveNotificationToast(payload);

        const list = document.getElementById('notifications-live-list');
        if (!list) {
            return;
        }

        const id = payload?.id;
        const openPath = payload?.open_path;
        if (!id || !openPath) {
            return;
        }

        if (list.querySelector(`[data-notification-id="${id}"]`)) {
            return;
        }

        const empty = list.querySelector('#empty-state');
        if (empty) {
            empty.remove();
        }

        const rowParts = buildNotificationRowHtml(payload);

        const row = document.createElement('div');
        row.className =
            'list-group-item py-3 px-3 notification-row-unread d-flex align-items-center gap-2';
        row.dataset.notificationId = String(id);

        const grow = document.createElement('div');
        grow.className = 'flex-grow-1';

        const link = document.createElement('a');
        link.href = openPath;
        link.className = 'text-decoration-none text-dark';
        link.innerHTML = `<div class="d-flex justify-content-between gap-2">
            <div>
                <div class="fw-semibold">${rowParts.title}${rowParts.officeHtml}</div>
                <div class="text-muted small mt-1">${rowParts.preview}</div>
            </div>
            <span class="text-muted small text-nowrap">${rowParts.timeLabel}</span>
        </div>`;

        grow.appendChild(link);
        row.appendChild(grow);

        const destroyBase = document
            .querySelector('meta[name="notifications-destroy-base"]')
            ?.getAttribute('content');
        if (destroyBase) {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className =
                'btn btn-sm btn-delete-notif text-danger border-0 bg-transparent';
            btn.title = 'Delete notification';
            btn.innerHTML = '<i class="bi bi-x-lg"></i>';
            const trimmedBase = destroyBase.replace(/\/+$/, '');
            const deleteUrl = `${trimmedBase}/${encodeURIComponent(String(id))}`;
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                deleteNotificationRow(deleteUrl, row);
            });
            row.appendChild(btn);
        }

        list.prepend(row);
    });

    ch.error((status) => {
        console.warn('[Notifications] Echo subscription error', status);
    });
}
