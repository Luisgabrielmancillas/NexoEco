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
    const dialog = document.querySelector('[data-mod-confirm-dialog]');
    const confirmButton = dialog.querySelector('[data-mod-confirm-submit]');
    let pendingForm;
    let trigger;
    let confirmed = false;
    document.querySelectorAll('[data-mod-delete]').forEach(form => {
        form.addEventListener('submit', event => {
            if (confirmed && pendingForm === form) return;
            event.preventDefault();
            pendingForm = form;
            trigger = event.submitter || form.querySelector('button[type="submit"]');
            dialog.querySelector('#mod-delete-title').textContent = `¿Eliminar este ${form.dataset.contentType === 'producto' ? 'producto' : 'contenido'}?`;
            dialog.querySelector('[data-mod-confirm-name]').textContent = form.dataset.contentName;
            dialog.querySelector('[data-mod-confirm-reason]').textContent = form.elements.namedItem('motivo').value;
            confirmButton.disabled = false;
            dialog.showModal();
        });
    });
    dialog.querySelectorAll('[data-mod-confirm-cancel]').forEach(button => button.addEventListener('click', () => dialog.close()));
    dialog.addEventListener('click', event => {
        if (event.target !== dialog) return;
        const bounds = dialog.getBoundingClientRect();
        if (event.clientX < bounds.left || event.clientX > bounds.right || event.clientY < bounds.top || event.clientY > bounds.bottom) dialog.close();
    });
    dialog.addEventListener('close', () => {
        pendingForm = null;
        confirmed = false;
        trigger?.focus();
    });
    confirmButton.addEventListener('click', () => {
        if (!pendingForm) return;
        if (!pendingForm.checkValidity()) {
            dialog.close();
            return;
        }
        confirmed = true;
        confirmButton.disabled = true;
        pendingForm.requestSubmit(trigger);
        dialog.close();
    });
}
