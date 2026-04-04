/**
 * Chat thread: send + HTTP poll (no WebSockets). Works with `php artisan serve` only.
 * Loaded from public/ — no npm build required.
 */
(function () {
    function escapeHtml(text) {
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function formatChatTime(iso) {
        var d = new Date(iso);
        return d.toLocaleTimeString(undefined, {
            hour: 'numeric',
            minute: '2-digit',
            hour12: true,
        });
    }

    function appendMessageBubble(messagesEl, m, fromSelf, cfg) {
        var id = Number(m.id);
        var seenIds = messagesEl.dataset.seenIds ? JSON.parse(messagesEl.dataset.seenIds) : [];
        var seen = new Set(seenIds);
        if (seen.has(id)) {
            return;
        }
        seen.add(id);
        messagesEl.dataset.seenIds = JSON.stringify(Array.from(seen));

        var hint = document.getElementById('messages-empty-hint');
        if (hint) {
            hint.remove();
        }

        var row = document.createElement('div');
        row.className = 'd-flex ' + (fromSelf ? 'justify-content-end' : 'justify-content-start');

        var isCitizen = cfg.theme === 'citizen';
        var selfStyle = isCitizen
            ? 'background:#4f46e5;color:white;border-bottom-right-radius:4px!important;'
            : 'background:#198754;color:white;border-bottom-right-radius:4px!important;';
        var otherStyle =
            'background:white;color:#1f2937;border-bottom-left-radius:4px!important;';

        var bubble = document.createElement('div');
        bubble.className = 'px-3 py-2 rounded-3 shadow-sm';
        bubble.setAttribute('data-message-id', String(id));
        bubble.style.cssText =
            'max-width:60%;' + (fromSelf ? selfStyle : otherStyle);

        bubble.innerHTML =
            '<p class="mb-1 small">' +
            escapeHtml(m.content) +
            '</p><p class="mb-0 opacity-75" style="font-size:11px;">' +
            formatChatTime(m.created_at) +
            '</p>';

        row.appendChild(bubble);
        messagesEl.appendChild(row);
    }

    function scrollChatToBottom(messagesEl) {
        messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    function maxSeenMessageId(messagesEl) {
        var raw = messagesEl.dataset.seenIds;
        if (!raw) {
            return 0;
        }
        try {
            var ids = JSON.parse(raw);
            if (!ids.length) {
                return 0;
            }
            return Math.max.apply(null, ids);
        } catch (e) {
            return 0;
        }
    }

    function init() {
        var cfg = window.chatRealtimeConfig;
        if (!cfg || !cfg.pollUrl) {
            return;
        }

        var messagesEl = document.getElementById('messages');
        var form = document.getElementById('chat-send-form');
        if (!messagesEl || !form) {
            return;
        }

        if (form.getAttribute('data-chat-bound') === '1') {
            return;
        }
        form.setAttribute('data-chat-bound', '1');

        var initialIds = [];
        messagesEl.querySelectorAll('[data-message-id]').forEach(function (el) {
            initialIds.push(Number(el.getAttribute('data-message-id')));
        });
        messagesEl.dataset.seenIds = JSON.stringify(initialIds);

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var input = form.querySelector('input[name="content"]');
            var content = (input && input.value ? input.value : '').trim();
            if (!content) {
                return;
            }

            var fd = new FormData(form);
            fetch(form.action, {
                method: 'POST',
                body: fd,
                cache: 'no-store',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            })
                .then(function (res) {
                    if (res.status === 419) {
                        window.location.reload();
                        return null;
                    }
                    if (!res.ok) {
                        return null;
                    }
                    return res.json();
                })
                .then(function (data) {
                    if (!data || !data.message) {
                        return;
                    }
                    if (input) {
                        input.value = '';
                    }
                    appendMessageBubble(messagesEl, data.message, true, cfg);
                    scrollChatToBottom(messagesEl);
                })
                .catch(function () {});
        });

        function runPoll() {
            var after = maxSeenMessageId(messagesEl);
            var url = new URL(cfg.pollUrl, window.location.origin);
            url.searchParams.set('after', String(after));

            fetch(url.toString(), {
                cache: 'no-store',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            })
                .then(function (res) {
                    if (!res.ok) {
                        return null;
                    }
                    return res.json();
                })
                .then(function (data) {
                    if (!data || !data.messages) {
                        return;
                    }
                    var list = data.messages;
                    for (var i = 0; i < list.length; i++) {
                        var m = list[i];
                        var fromSelf = Number(m.sender_id) === Number(cfg.currentUserId);
                        appendMessageBubble(messagesEl, m, fromSelf, cfg);
                    }
                    if (list.length) {
                        scrollChatToBottom(messagesEl);
                    }
                })
                .catch(function () {});
        }

        setInterval(runPoll, 2000);
        runPoll();

        document.addEventListener('visibilitychange', function () {
            if (document.visibilityState === 'visible') {
                runPoll();
            }
        });

        scrollChatToBottom(messagesEl);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
