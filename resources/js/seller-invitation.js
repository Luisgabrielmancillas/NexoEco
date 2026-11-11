export function initializeSellerInvitation() {
    const dialog = document.querySelector('[data-seller-invitation]');
    const launcher = document.querySelector('[data-promo-open]');
    if (!dialog || !launcher) return;
    const reducedMotion = matchMedia('(prefers-reduced-motion: reduce)');
    let timer;
    let closing = false;
    const open = () => {
        clearTimeout(timer);
        if (closing || dialog.open) return;
        launcher.hidden = true;
        dialog.showModal();
        document.body.classList.add('has-seller-invitation');
    };
    const minimize = () => {
        if (closing) return;
        closing = true;
        // Measure the real destination so the flight also lands correctly on mobile.
        launcher.classList.add('is-arriving');
        launcher.hidden = false;
        const source = dialog.getBoundingClientRect();
        const target = launcher.getBoundingClientRect();
        dialog.style.setProperty('--minimize-x', `${target.left + target.width / 2 - source.left - source.width / 2}px`);
        dialog.style.setProperty('--minimize-y', `${target.top + target.height / 2 - source.top - source.height / 2}px`);
        dialog.classList.add('is-minimizing');
        setTimeout(() => dialog.close(), reducedMotion.matches ? 0 : 650);
    };
    dialog.addEventListener('close', () => {
        closing = false;
        dialog.classList.remove('is-minimizing');
        document.body.classList.remove('has-seller-invitation');
        launcher.hidden = false;
        launcher.classList.remove('is-arriving');
        launcher.focus({ preventScroll: true });
        try { sessionStorage.setItem('nexo-seller-invitation-minimized', '1'); } catch { /* Storage may be disabled. */ }
    });
    dialog.querySelector('[data-promo-close]').addEventListener('click', minimize);
    dialog.addEventListener('cancel', (event) => { event.preventDefault(); minimize(); });
    launcher.addEventListener('click', open);
    let minimized = false;
    try { minimized = sessionStorage.getItem('nexo-seller-invitation-minimized') === '1'; } catch { /* Keep the default delay. */ }
    if (minimized) launcher.hidden = false;
    else timer = setTimeout(() => {
        // Do not cover an active task or another dialog with an automatic invitation.
        if (document.hidden || document.querySelector('dialog[open]') || document.activeElement?.matches('input, textarea, select')) launcher.hidden = false;
        else open();
    }, 5000);
    window.addEventListener('pagehide', () => clearTimeout(timer));
    window.addEventListener('pageshow', (event) => {
        if (event.persisted && !dialog.open) launcher.hidden = false;
    });
}
