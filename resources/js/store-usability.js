document.querySelectorAll('[data-character-count]').forEach((input) => {
    const output = document.getElementById(input.getAttribute('aria-describedby'));
    const update = () => { output.textContent = `${input.value.length} / ${input.maxLength} caracteres`; };
    input.addEventListener('input', update);
    update();
});

document.querySelectorAll('[data-clamp-description]').forEach((description) => {
    const button = document.querySelector(`[aria-controls="${description.id}"][data-description-toggle]`);
    description.classList.add('description-collapsed');
    const measure = () => {
        if (button.getAttribute('aria-expanded') === 'true') return;
        button.hidden = description.scrollHeight <= description.clientHeight + 1;
    };
    button.addEventListener('click', () => {
        const expanded = button.getAttribute('aria-expanded') !== 'true';
        description.classList.toggle('description-collapsed', !expanded);
        button.setAttribute('aria-expanded', String(expanded));
        button.textContent = expanded ? 'Ver menos' : 'Ver más';
    });
    new ResizeObserver(measure).observe(description);
    document.fonts.ready.then(measure);
    measure();
});

document.querySelectorAll('[data-confirm-account-deletion]').forEach((form) => {
    const dialog = document.createElement('dialog');
    dialog.className = 'app-message-dialog';
    dialog.setAttribute('aria-labelledby', 'delete-account-title');
    dialog.setAttribute('aria-describedby', 'delete-account-description');
    dialog.innerHTML = `<h2 id="delete-account-title">¿Eliminar tu cuenta definitivamente?</h2>
        <p id="delete-account-description">Se eliminarán tu cuenta, favoritos y opiniones. Esta acción no se puede deshacer.</p>
        <div class="app-message-actions"><button type="button" class="buyer-button secondary" data-cancel autofocus>Conservar mi cuenta</button><button type="button" class="buyer-button danger" data-confirm>Eliminar definitivamente</button></div>`;
    document.body.append(dialog);
    let confirmed = false;
    let trigger;
    form.addEventListener('submit', (event) => {
        if (confirmed) return;
        event.preventDefault();
        trigger = event.submitter;
        dialog.showModal();
    });
    dialog.querySelector('[data-cancel]').addEventListener('click', () => dialog.close());
    dialog.addEventListener('close', () => trigger?.focus());
    dialog.querySelector('[data-confirm]').addEventListener('click', () => {
        confirmed = true;
        dialog.close();
        if (trigger) form.requestSubmit(trigger);
        else form.requestSubmit();
    });
    window.addEventListener('pageshow', () => { confirmed = false; });
});
