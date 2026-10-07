import { initializeLocationMap } from './location-map';

export function initializeBuyerLocation() {
    const form = document.querySelector('[data-buyer-location-form]');
    if (!form) return;
    const dialog = form.closest('dialog');
    const error = form.querySelector('[data-location-error]');
    const submit = form.querySelector('[data-location-submit]');
    let mapStarted = false;
    const startMap = () => {
        if (mapStarted) return;
        mapStarted = true;
        initializeLocationMap(form, { container: form.querySelector('[data-buyer-map]'), status: form.querySelector('[data-location-map-status]'), locate: form.querySelector('[data-buyer-geolocate]') });
    };
    dialog.addEventListener('store-dialog-open', startMap);
    if (!error.hidden) startMap();
    if (!error.hidden) { dialog.showModal(); document.body.classList.add('has-store-dialog'); }
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (submit.disabled) return;
        submit.disabled = true;
        submit.textContent = 'Guardando…';
        error.hidden = true;
        try {
            const response = await fetch(form.action, { method: 'POST', body: new FormData(form), credentials: 'same-origin', headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
            const result = response.headers.get('content-type')?.includes('application/json') ? await response.json() : null;
            if (!response.ok || !result?.label) {
                throw new Error(result?.errors ? Object.values(result.errors).flat().join(' ') : [401, 419].includes(response.status) ? 'Tu sesión expiró. Recarga la página e inténtalo de nuevo.' : 'No se pudo guardar la ubicación. Inténtalo de nuevo.');
            }
            document.querySelectorAll('.market-location-value').forEach((label) => { label.textContent = result.label; });
            document.querySelectorAll('[data-dialog-open="buyer-location-dialog"]').forEach((button) => button.setAttribute('aria-label', `Cambiar ubicación: ${result.label}`));
            dialog.close();
            let feedback = document.querySelector('[data-location-feedback]');
            if (!feedback) { feedback = document.createElement('p'); feedback.className = 'favorite-feedback'; feedback.dataset.locationFeedback = ''; feedback.setAttribute('role', 'status'); document.body.append(feedback); }
            feedback.textContent = result.message;
            feedback.hidden = false;
            setTimeout(() => { feedback.hidden = true; }, 3500);
        } catch (exception) {
            error.textContent = exception.message;
            error.hidden = false;
        } finally { submit.disabled = false; submit.textContent = 'Guardar ubicación'; }
    });
}
