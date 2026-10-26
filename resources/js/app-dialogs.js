// App-owned messages use the same accessible dialog across every account area.
export function initializeAppDialogs() {
    const recovery = document.querySelector('[data-session-recovery]');
    if (recovery) {
        // Keep the server-rendered open dialog usable even without JavaScript.
        recovery.close();
        recovery.showModal();
        recovery.addEventListener('cancel', (event) => {
            event.preventDefault();
            window.location.assign(recovery.dataset.exitUrl);
        });
    }

    const dialog = document.createElement('dialog');
    dialog.className = 'app-message-dialog';
    dialog.setAttribute('aria-labelledby', 'app-message-title');
    dialog.setAttribute('aria-describedby', 'app-message-description');
    dialog.innerHTML = `
        <div class="app-message-icon" aria-hidden="true">i</div>
        <h2 id="app-message-title"></h2>
        <p id="app-message-description"></p>
        <div class="app-message-actions">
            <button type="button" class="ds-secondary-button" data-dialog-close autofocus>Entendido</button>
            <a class="ds-primary-button" data-dialog-link hidden></a>
        </div>`;
    document.body.append(dialog);
    const title = dialog.querySelector('h2');
    const description = dialog.querySelector('p');
    const close = dialog.querySelector('[data-dialog-close]');
    const action = dialog.querySelector('[data-dialog-link]');
    let returnFocus;
    const show = (options) => {
        returnFocus = options.trigger || document.activeElement;
        title.textContent = options.title;
        description.textContent = options.message;
        close.textContent = options.url ? 'Seguir aquí' : 'Entendido';
        action.hidden = !options.url;
        if (options.url) {
            action.href = options.url;
            action.textContent = options.actionLabel;
        } else {
            action.removeAttribute('href');
        }
        if (!dialog.open) dialog.showModal();
    };
    close.addEventListener('click', () => dialog.close());
    dialog.addEventListener('close', () => {
        if (returnFocus?.isConnected) returnFocus.focus();
    });
    dialog.addEventListener('click', (event) => {
        if (event.target !== dialog) return;
        const bounds = dialog.getBoundingClientRect();
        if (event.clientX < bounds.left || event.clientX > bounds.right || event.clientY < bounds.top || event.clientY > bounds.bottom) dialog.close();
    });
    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-plan-locked]');
        if (!trigger) return;
        event.preventDefault();
        show({
            trigger,
            title: trigger.dataset.dialogTitle,
            message: trigger.dataset.dialogMessage,
            url: trigger.dataset.dialogAction,
            actionLabel: trigger.dataset.dialogActionLabel,
        });
    });

    // Prevent native validation popups while retaining constraint validation.
    // Coalesce a form's invalid events to show only its first invalid field.
    let invalidField;
    document.addEventListener('invalid', (event) => {
        event.preventDefault();
        if (invalidField) return;
        invalidField = event.target;
        queueMicrotask(() => {
            const field = invalidField;
            invalidField = null;
            const label = field.labels?.[0]?.textContent.trim() || field.getAttribute('aria-label');
            show({
                trigger: field,
                title: 'Revisa los datos del formulario',
                message: `${label ? `${label}: ` : ''}${field.validationMessage}`,
            });
        });
    }, true);
}
