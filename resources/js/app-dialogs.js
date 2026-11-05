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

    // Keep validation next to the field, including inside an already-open dialog.
    // Opening another modal here would make the field and its context inaccessible.
    let invalidField;
    document.addEventListener('invalid', (event) => {
        event.preventDefault();
        const field = event.target;
        field.setAttribute('aria-invalid', 'true');
        const errorId = field.dataset.constraintErrorId ||= field.id ? `${field.id}-constraint-error` : `constraint-error-${++validationSequence}`;
        let hint = document.getElementById(errorId);
        if (!hint) {
            hint = document.createElement('p');
            hint.id = errorId;
            hint.className = 'field-constraint-error';
            hint.dataset.constraintError = '';
            field.insertAdjacentElement('afterend', hint);
        }
        hint.textContent = constraintMessage(field);
        const descriptions = new Set((field.getAttribute('aria-describedby') || '').split(/\s+/).filter(Boolean));
        descriptions.add(errorId);
        field.setAttribute('aria-describedby', [...descriptions].join(' '));
        if (invalidField) return;
        invalidField = field;
        requestAnimationFrame(() => {
            const field = invalidField;
            invalidField = null;
            field.focus();
        });
    }, true);
    document.addEventListener('input', (event) => {
        const field = event.target;
        const errorId = field.dataset.constraintErrorId;
        if (!errorId || !field.validity.valid) return;
        document.getElementById(errorId)?.remove();
        field.removeAttribute('aria-invalid');
        const remaining = (field.getAttribute('aria-describedby') || '').split(/\s+/).filter(id => id && id !== errorId);
        if (remaining.length) field.setAttribute('aria-describedby', remaining.join(' '));
        else field.removeAttribute('aria-describedby');
    });
}

let validationSequence = 0;

export function constraintMessage(field) {
    const validity = field.validity;
    if (validity.customError) return field.validationMessage;
    if (validity.valueMissing) return field.type === 'checkbox' ? 'Marca esta casilla para continuar.' : 'Completa este campo para continuar.';
    if (validity.typeMismatch) return field.type === 'email' ? 'Escribe un correo electrónico válido.' : 'Escribe una dirección válida.';
    if (validity.tooShort) return `Escribe al menos ${field.minLength} caracteres.`;
    if (validity.tooLong) return `Escribe como máximo ${field.maxLength} caracteres.`;
    if (validity.rangeUnderflow) return `El valor mínimo es ${field.min}.`;
    if (validity.rangeOverflow) return `El valor máximo es ${field.max}.`;
    if (validity.badInput || validity.stepMismatch) return 'Escribe un número válido con la precisión indicada.';
    return 'Revisa el formato de este campo.';
}
