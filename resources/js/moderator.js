export function initializeModerator() {
    const sidebar = document.querySelector('[data-mod-sidebar]');
    if (!sidebar) return;
    const toggle = document.querySelector('[data-mod-menu]');
    const mobile = matchMedia('(max-width: 760px)');
    const sync = () => { sidebar.inert = mobile.matches && !document.body.classList.contains('has-mod-menu'); };
    const close = () => {
        document.body.classList.remove('has-mod-menu');
        toggle.setAttribute('aria-expanded', 'false');
        sync();
    };
    sync();
    mobile.addEventListener('change', close);
    toggle.addEventListener('click', () => {
        const open = document.body.classList.toggle('has-mod-menu');
        toggle.setAttribute('aria-expanded', String(open));
        sync();
        if (open) sidebar.querySelector('a').focus();
    });
    document.querySelectorAll('[data-mod-close]').forEach(button => button.addEventListener('click', () => { close(); toggle.focus(); }));
    document.addEventListener('keydown', event => {
        if (!document.body.classList.contains('has-mod-menu')) return;
        if (event.key === 'Escape') { close(); toggle.focus(); }
        if (event.key === 'Tab') {
            const items = [...sidebar.querySelectorAll('a, button')].filter(item => item.getClientRects().length);
            if (event.shiftKey && document.activeElement === items[0]) { event.preventDefault(); items.at(-1).focus(); }
            else if (!event.shiftKey && document.activeElement === items.at(-1)) { event.preventDefault(); items[0].focus(); }
        }
    });
    document.querySelectorAll('[data-mod-delete]').forEach(form => {
        form.addEventListener('submit', event => {
            if (!window.confirm('¿Eliminar este contenido del marketplace? El usuario recibirá el motivo por notificación.')) event.preventDefault();
        });
    });
}