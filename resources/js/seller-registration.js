export function initializeSellerRegistration(root) {
    const form = root.querySelector('[data-wizard-form]');
    if (!form) return;

    const steps = Array.from(form.querySelectorAll('[data-wizard-step]'));
    const back = form.querySelector('[data-wizard-back]');
    const next = form.querySelector('[data-wizard-next]');
    const submit = form.querySelector('[data-wizard-submit]');
    const progress = root.querySelector('[data-wizard-progress]');
    const firstStep = root.dataset.accountComplete === 'true' ? 1 : 0;
    let current = Math.max(firstStep, Number(root.dataset.initialStep) || 0);

    const showStep = (index, focus = true) => {
        current = Math.min(2, Math.max(firstStep, index));
        steps.forEach((step, position) => { step.hidden = position !== current; });
        root.querySelectorAll('[data-step-indicator]').forEach((indicator, position) => {
            indicator.classList.toggle('is-complete', position < current);
            if (position === current) indicator.setAttribute('aria-current', 'step');
            else indicator.removeAttribute('aria-current');
        });
        back.hidden = current === firstStep;
        next.hidden = current === 2;
        submit.hidden = current !== 2;
        progress.hidden = false;
        progress.querySelector('[data-progress-label]').textContent = `Paso ${current + 1} de 4`;
        progress.querySelector('progress').value = current + 1;

        if (current === 2) {
            for (const field of ['nombre_completo', 'email', 'rfc']) {
                const target = root.querySelector(`[data-summary-${field === 'nombre_completo' ? 'name' : field}]`);
                const input = form.elements.namedItem(field);
                if (target && input) target.textContent = input.value;
            }
            root.querySelector('[data-wizard-summary]').hidden = false;
        }
        if (focus) steps[current].querySelector('h2').focus();
    };

    const password = form.elements.namedItem('password');
    const confirmation = form.elements.namedItem('password_confirmation');
    const checkPasswords = () => {
        if (confirmation) confirmation.setCustomValidity(
            confirmation.value && confirmation.value !== password.value ? 'Las contraseñas no coinciden.' : '',
        );
    };
    password?.addEventListener('input', checkPasswords);
    confirmation?.addEventListener('input', checkPasswords);

    root.querySelectorAll('[data-seller-password]').forEach((button) => {
        button.addEventListener('click', () => {
            const input = form.elements.namedItem(button.dataset.sellerPassword);
            const showing = input.type === 'password';
            input.type = showing ? 'text' : 'password';
            button.textContent = showing ? 'Ocultar' : 'Ver';
            button.setAttribute('aria-label', showing ? 'Ocultar contraseña' : 'Mostrar contraseña');
        });
    });

    form.querySelectorAll('input[type="file"]').forEach((input) => {
        input.addEventListener('change', () => {
            const file = input.files[0];
            let message = '';
            if (file && file.size > 5 * 1024 * 1024) message = 'Cada archivo debe pesar como máximo 5 MB.';
            else if (file && !/\.(jpe?g|png|pdf)$/i.test(file.name)) message = 'Solo se permiten archivos JPG, PNG o PDF.';
            input.setCustomValidity(message);
            if (message) input.reportValidity();
        });
    });

    const validateStep = (index) => {
        checkPasswords();
        const invalid = Array.from(steps[index].querySelectorAll('input, textarea')).find((input) => !input.checkValidity());
        if (invalid) {
            showStep(index, false);
            invalid.reportValidity();
            invalid.focus();
            return false;
        }
        return true;
    };

    next.addEventListener('click', () => { if (validateStep(current)) showStep(current + 1); });
    back.addEventListener('click', () => showStep(current - 1));
    // Validate only visible controls while moving between steps, and all controls before submission.
    form.noValidate = true;
    form.addEventListener('submit', (event) => {
        if (current < 2) {
            event.preventDefault();
            if (validateStep(current)) showStep(current + 1);
            return;
        }
        for (let index = firstStep; index < steps.length; index++) {
            if (!validateStep(index)) {
                event.preventDefault();
                return;
            }
        }
        submit.disabled = true;
        submit.textContent = 'Enviando tu solicitud…';
    });
    window.addEventListener('pageshow', () => {
        submit.disabled = false;
        submit.textContent = firstStep ? 'Enviar solicitud →' : 'Crear cuenta y enviar documentos →';
    });
    showStep(current, false);
}
