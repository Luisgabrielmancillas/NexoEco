// Guard only native submissions. AJAX forms cancel their event and own their state.
export function initializeFormSubmission() {
    const pending = new Map();
    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (event.defaultPrevented || form.method.toLowerCase() !== 'post') return;
        if (pending.has(form)) { event.preventDefault(); return; }
        const button = event.submitter;
        pending.set(form, button);
        form.setAttribute('aria-busy', 'true');
        // aria-disabled preserves the submitter name/value in the request payload.
        button?.setAttribute('aria-disabled', 'true');
    });
    window.addEventListener('pageshow', () => {
        pending.forEach((button, form) => {
            form.removeAttribute('aria-busy');
            button?.removeAttribute('aria-disabled');
        });
        pending.clear();
    });
}
