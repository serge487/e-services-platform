function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatChatTime(iso) {
    const d = new Date(iso);
    return d.toLocaleTimeString(undefined, {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true,
    });
}

function appendMessageBubble(messagesEl, m, fromSelf, cfg) {
    const id = Number(m.id);
    const seenIds = messagesEl.dataset.seenIds ? JSON.parse(messagesEl.dataset.seenIds) : [];
    const seen = new Set(seenIds);
    if (seen.has(id)) {
        return;
    }
    seen.add(id);
    messagesEl.dataset.seenIds = JSON.stringify([...seen]);

    const hint = document.getElementById('messages-empty-hint');
    if (hint) {
        hint.remove();
    }

    const row = document.createElement('div');
    row.className = `d-flex ${fromSelf ? 'justify-content-end' : 'justify-content-start'}`;

    const isCitizen = cfg.theme === 'citizen';
    const selfStyle = isCitizen
        ? 'background:#4f46e5;color:white;border-bottom-right-radius:4px!important;'
        : 'background:#198754;color:white;border-bottom-right-radius:4px!important;';
    const otherStyle =
        'background:white;color:#1f2937;border-bottom-left-radius:4px!important;';

    const bubble = document.createElement('div');
    bubble.className = 'px-3 py-2 rounded-3 shadow-sm';
    bubble.dataset.messageId = String(id);
    bubble.style.cssText = `max-width:60%;${fromSelf ? selfStyle : otherStyle}`;

    bubble.innerHTML = `<p class="mb-1 small">${escapeHtml(m.content)}</p><p class="mb-0 opacity-75" style="font-size:11px;">${formatChatTime(m.created_at)}</p>`;

    row.appendChild(bubble);
    messagesEl.appendChild(row);
}

function scrollChatToBottom(messagesEl) {
    messagesEl.scrollTop = messagesEl.scrollHeight;
}

function maxSeenMessageId(messagesEl) {
    const raw = messagesEl.dataset.seenIds;
    if (!raw) {
        return 0;
    }
    try {
        const ids = JSON.parse(raw);
        return ids.length ? Math.max(...ids) : 0;
    } catch {
        return 0;
    }
}

export function initChatRealtime() {
    const cfg = window.chatRealtimeConfig;
    if (!cfg) {
        return;
    }

    const messagesEl = document.getElementById('messages');
    const form = document.getElementById('chat-send-form');
    if (!messagesEl || !form) {
        return;
    }

    if (form.dataset.chatRealtimeBound === '1') {
        return;
    }
    form.dataset.chatRealtimeBound = '1';

    const initialIds = [];
    messagesEl.querySelectorAll('[data-message-id]').forEach((el) => {
        initialIds.push(Number(el.dataset.messageId));
    });
    messagesEl.dataset.seenIds = JSON.stringify(initialIds);

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const input = form.querySelector('input[name="content"]');
        const content = (input?.value || '').trim();
        if (!content) {
            return;
        }

        const fd = new FormData(form);
        const res = await fetch(form.action, {
            method: 'POST',
            body: fd,
            cache: 'no-store',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        });

        if (!res.ok) {
            if (res.status === 419) {
                window.location.reload();
            }
            return;
        }

        const data = await res.json();
        if (input) {
            input.value = '';
        }
        appendMessageBubble(messagesEl, data.message, true, cfg);
        scrollChatToBottom(messagesEl);
    });

    if (window.Echo) {
        window.Echo.private(`chat.${cfg.chatId}`)
            .listen('.message.sent', (payload) => {
                const fromSelf = Number(payload.sender_id) === Number(cfg.currentUserId);
                appendMessageBubble(messagesEl, payload, fromSelf, cfg);
                scrollChatToBottom(messagesEl);
            })
            .error((status) => {
                console.warn('[Chat] WebSocket subscription error', status);
            });
    }

    if (cfg.pollUrl) {
        const runPoll = async () => {
            try {
                const after = maxSeenMessageId(messagesEl);
                const url = new URL(cfg.pollUrl, window.location.origin);
                url.searchParams.set('after', String(after));
                const res = await fetch(url.toString(), {
                    cache: 'no-store',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                });
                if (!res.ok) {
                    return;
                }
                const data = await res.json();
                const list = data.messages ?? [];
                for (const m of list) {
                    const fromSelf = Number(m.sender_id) === Number(cfg.currentUserId);
                    appendMessageBubble(messagesEl, m, fromSelf, cfg);
                }
                if (list.length) {
                    scrollChatToBottom(messagesEl);
                }
            } catch {
                // ignore
            }
        };

        window.setInterval(runPoll, 3000);
        runPoll();

        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible') {
                runPoll();
            }
        });
    }

    scrollChatToBottom(messagesEl);
}
