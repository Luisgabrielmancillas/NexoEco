export function initializeChatCommerce() {
    document.querySelectorAll('[data-reservation-toggle]').forEach((toggle) => {
        const fields = document.querySelector('[data-reservation-fields]');
        if (!fields) return;
        const update = () => {
            fields.hidden = !toggle.checked;
            fields.querySelectorAll('input, textarea').forEach((field) => {
                field.disabled = !toggle.checked;
                field.required = toggle.checked;
            });
        };
        toggle.addEventListener('change', update);
        update();
    });

    document.querySelectorAll('[data-product-chat]').forEach((room) => {
        const list = room.querySelector('[data-chat-messages]');
        const form = room.querySelector('[data-chat-form]');
        const status = room.querySelector('[data-chat-status]');
        const history = room.querySelector('[data-chat-history]');
        const entries = new Map();
        const nodes = new Map();
        let firstId = null;
        let loading = false;
        let stopped = false;
        let initial = true;
        const request = async (url, options = {}) => {
            const response = await fetch(url, { credentials: 'same-origin', ...options, headers: {
                'Accept': 'application/json', 'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                ...options.headers,
            } });
            const data = await response.json().catch(() => ({}));
            if (!response.ok) {
                if ([401, 403, 404, 419].includes(response.status)) stopped = true;
                throw new Error(Object.values(data.errors ?? {}).flat()[0] ?? data.message ?? 'No pudimos conectar. Reintenta en unos segundos.');
            }
            return data;
        };
        const render = (messages, older) => {
            // After a long pause, restart from the latest page so history remains contiguous.
            if (!older && entries.size && messages.length === 50 && !messages.some((message) => entries.has(message.id))) {
                entries.clear();
                nodes.clear();
                list.replaceChildren();
                initial = true;
            }
            const nearBottom = list.scrollHeight - list.scrollTop - list.clientHeight < 90;
            const oldHeight = list.scrollHeight;
            messages.forEach((message) => entries.set(message.id, message));
            const sorted = [...entries.values()].sort((a, b) => a.time.localeCompare(b.time) || a.id.localeCompare(b.id));
            sorted.forEach((message, index) => {
                if (nodes.has(message.id)) return;
                const item = document.createElement('article');
                item.className = `chat-bubble ${message.mine ? 'mine' : ''}`;
                const body = document.createElement('p');
                body.textContent = message.body;
                const time = document.createElement('time');
                time.dateTime = message.time;
                time.textContent = new Date(message.time).toLocaleString('es-MX', { dateStyle: 'short', timeStyle: 'short' });
                item.append(body, time);
                const next = sorted.slice(index + 1).map((entry) => nodes.get(entry.id)).find(Boolean);
                list.insertBefore(item, next ?? null);
                nodes.set(message.id, item);
            });
            firstId = sorted[0]?.id;
            if (older) list.scrollTop += list.scrollHeight - oldHeight;
            else if (nearBottom || initial) list.scrollTop = list.scrollHeight;
            if (initial || older) history.hidden = messages.length < 50;
            initial = false;
        };
        const refresh = async (older = false) => {
            if (loading || stopped || (document.hidden && !older)) return;
            loading = true;
            try {
                const url = new URL(room.dataset.messagesUrl, window.location.origin);
                if (older && firstId) url.searchParams.set('before', firstId);
                const data = await request(url);
                render(data.messages, older);
                status.textContent = entries.size ? 'Conversación privada · se actualiza automáticamente' : 'Envía el primer mensaje para iniciar la conversación.';
            } catch (error) { status.textContent = error.message; }
            finally { loading = false; }
        };
        history.addEventListener('click', () => refresh(true));
        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            const text = form.elements.body.value.trim();
            if (!text || stopped) return;
            const button = form.querySelector('button');
            button.disabled = true;
            const textarea = form.elements.body;
            textarea.readOnly = true;
            try {
                await request(room.dataset.sendUrl, { method: 'POST', body: JSON.stringify({ body: text }) });
                textarea.value = '';
                await refresh();
            } catch (error) { status.textContent = error.message; }
            finally { button.disabled = false; textarea.readOnly = false; textarea.focus(); }
        });
        refresh();
        const timer = window.setInterval(() => refresh(), 3000);
        document.addEventListener('visibilitychange', () => { if (!document.hidden) refresh(); });
        window.addEventListener('pagehide', () => window.clearInterval(timer), { once: true });
    });
}
