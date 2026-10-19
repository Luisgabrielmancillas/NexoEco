export function initializeBuyerAccount() {
    const photoInput = document.querySelector('[data-photo-input]');
    let previewUrl;
    photoInput?.addEventListener('change', () => {
        if (previewUrl) URL.revokeObjectURL(previewUrl);
        const photo = photoInput.files[0];
        if (!photo || !['image/jpeg', 'image/png', 'image/webp'].includes(photo.type) || photo.size > 3 * 1024 * 1024) return;
        previewUrl = URL.createObjectURL(photo);
        const preview = document.querySelector('[data-photo-preview]');
        preview.src = previewUrl;
        preview.hidden = false;
        const initials = document.querySelector('[data-photo-initials]');
        if (initials) initials.hidden = true;
    });
    const bell = document.querySelector('[data-notification-bell]');
    if (bell) {
        let refreshing = false;
        let sessionActive = true;
        let latestId = bell.dataset.latestId || null;
        let previousUnread = Number(bell.dataset.unreadCount) || 0;
        let notificationTimer;
        const announce = (latest) => {
            let notice = document.querySelector('[data-notification-toast]');
            if (!notice) {
                notice = document.createElement('aside');
                notice.className = 'notification-toast';
                notice.dataset.notificationToast = '';
                notice.setAttribute('role', 'status');
                notice.setAttribute('aria-live', 'polite');
                const link = document.createElement('a');
                const close = document.createElement('button');
                close.type = 'button';
                close.textContent = '×';
                close.setAttribute('aria-label', 'Cerrar aviso');
                close.addEventListener('click', () => { notice.hidden = true; });
                notice.append(link, close);
                document.body.append(notice);
            }
            const link = notice.querySelector('a');
            link.textContent = latest?.title || 'Tienes nuevas notificaciones';
            const destination = new URL(latest?.url || bell.href, window.location.origin);
            link.href = destination.origin === window.location.origin ? destination.href : bell.href;
            notice.hidden = false;
            clearTimeout(notificationTimer);
            notificationTimer = setTimeout(() => { notice.hidden = true; }, 8000);
        };
        const refreshUnread = async () => {
            if (document.hidden || refreshing || !sessionActive) return;
            refreshing = true;
            try {
                const response = await fetch(bell.dataset.countUrl, { credentials: 'same-origin', cache: 'no-store', headers: { Accept: 'application/json' } });
                if ([401, 403, 419].includes(response.status)) sessionActive = false;
                if (!response.ok) return;
                const { unread, latest } = await response.json();
                if (!Number.isInteger(unread) || unread < 0) return;
                const newLatest = latest?.id && latest.id !== latestId && latest.unread;
                if (newLatest || unread > previousUnread) announce(newLatest ? latest : null);
                latestId = latest?.id || latestId;
                previousUnread = unread;
                const badge = bell.querySelector('.notification-count');
                badge.textContent = unread > 99 ? '99+' : String(unread);
                badge.hidden = unread === 0;
                bell.setAttribute('aria-label', `Notificaciones${unread ? ': ' + unread + ' sin leer' : ''}`);
            } catch {
                // Keep the last confirmed count if the connection is interrupted.
            } finally {
                refreshing = false;
            }
        };
        setInterval(refreshUnread, 8000);
        window.addEventListener('focus', refreshUnread);
        document.addEventListener('visibilitychange', refreshUnread);
    }
    let toastTimer;
    const feedback = (message) => {
        let toast = document.querySelector('[data-favorite-feedback]');
        if (!toast) {
            toast = document.createElement('div');
            toast.className = 'favorite-feedback';
            toast.dataset.favoriteFeedback = '';
            toast.setAttribute('role', 'status');
            toast.setAttribute('aria-live', 'polite');
            document.body.append(toast);
        }
        toast.textContent = message;
        toast.hidden = false;
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => { toast.hidden = true; }, 4500);
    };

    document.querySelectorAll('[data-favorite-form]').forEach((form) => {
        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            const matching = [...document.querySelectorAll('[data-favorite-form]')].filter((item) => item.action === form.action);
            if (matching.some((item) => item.querySelector('button').disabled)) return;
            matching.forEach((item) => { item.querySelector('button').disabled = true; });
            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    credentials: 'same-origin',
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (!response.ok || !response.headers.get('content-type')?.includes('application/json')) {
                    throw new Error([401, 419].includes(response.status) ? 'Tu sesión expiró. Inicia sesión de nuevo para guardar favoritos.' : response.status === 403 ? 'Verifica tu correo para guardar favoritos.' : 'No se pudo actualizar el favorito. Inténtalo de nuevo.');
                }
                const result = await response.json();
                matching.forEach((item) => {
                    item.dataset.saved = String(result.saved);
                    item.querySelector('[name="_method"]').value = result.saved ? 'DELETE' : 'POST';
                    const button = item.querySelector('button');
                    button.setAttribute('aria-pressed', String(result.saved));
                    const label = `${result.saved ? 'Quitar' : 'Guardar'} ${item.dataset.name} ${result.saved ? 'de' : 'en'} favoritos`;
                    button.setAttribute('aria-label', label);
                    button.title = label;
                });
                if (!result.saved && document.querySelector('[data-favorites-page]')) {
                    window.location.reload();
                    return;
                }
                feedback(result.message);
            } catch (error) {
                feedback(error.message || 'No se pudo actualizar el favorito. Inténtalo de nuevo.');
            } finally {
                matching.forEach((item) => { item.querySelector('button').disabled = false; });
            }
        });
    });

    const dropdowns = [...document.querySelectorAll('.buyer-nav details')];
    dropdowns.forEach((item) => {
        item.addEventListener('toggle', () => {
            if (item.open) dropdowns.forEach((other) => { if (other !== item) other.open = false; });
        });
    });
    const nav = document.querySelector('[data-buyer-nav]');
    nav?.setAttribute('data-enhanced', '');
    const mobileToggle = document.querySelector('[data-mobile-nav-toggle]');
    const drawer = nav?.querySelector('.buyer-nav-links');
    const mobileViewport = window.matchMedia('(max-width: 900px)');
    const syncDrawer = () => {
        if (!drawer) return;
        const closed = mobileViewport.matches && !nav.classList.contains('is-open');
        drawer.inert = closed;
        if (closed) drawer.setAttribute('aria-hidden', 'true');
        else drawer.removeAttribute('aria-hidden');
    };
    syncDrawer();
    const closeMobileNav = () => {
        nav?.classList.remove('is-open');
        document.body.classList.remove('has-mobile-nav');
        mobileToggle?.setAttribute('aria-expanded', 'false');
        dropdowns.forEach((item) => { item.open = false; });
        syncDrawer();
    };
    mobileToggle?.addEventListener('click', () => {
        const open = nav.classList.toggle('is-open');
        mobileToggle.setAttribute('aria-expanded', String(open));
        document.body.classList.toggle('has-mobile-nav', open);
        syncDrawer();
        if (open) nav.querySelector('.buyer-nav-links a')?.focus();
        if (!open) dropdowns.forEach((item) => { item.open = false; });
    });
    document.querySelectorAll('[data-mobile-nav-close]').forEach((button) => button.addEventListener('click', () => { closeMobileNav(); mobileToggle?.focus(); }));
    nav?.querySelectorAll('a').forEach((link) => link.addEventListener('click', closeMobileNav));
    mobileViewport.addEventListener('change', closeMobileNav);
    document.addEventListener('click', (event) => dropdowns.forEach((item) => { if (!item.contains(event.target)) item.open = false; }));
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Tab' && nav?.classList.contains('is-open') && window.matchMedia('(max-width: 900px)').matches) {
            const focusable = [...nav.querySelectorAll('.buyer-nav-links a, .buyer-nav-links button, .buyer-nav-links summary')].filter((item) => item.getClientRects().length);
            const first = focusable[0];
            const last = focusable.at(-1);
            if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last?.focus(); }
            else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first?.focus(); }
        }
        if (event.key !== 'Escape') return;
        dropdowns.forEach((item) => { if (item.open) { item.open = false; item.querySelector('summary').focus(); } });
        if (nav?.classList.contains('is-open')) {
            closeMobileNav();
            mobileToggle.focus();
        }
    });
}
