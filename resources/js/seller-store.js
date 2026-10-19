import { initializeLocationMap } from './location-map';

export function initializeShopForms() {
    document.querySelectorAll('[data-shop-form]').forEach((form) => {
        form.querySelectorAll('[data-shop-image-input]').forEach((input) => {
            let objectUrl;
            input.addEventListener('change', () => {
                const preview = form.querySelector(`[data-shop-image-preview="${input.dataset.shopImageInput}"]`);
                if (!input.files[0] || !preview) return;
                if (objectUrl) URL.revokeObjectURL(objectUrl);
                objectUrl = URL.createObjectURL(input.files[0]);
                preview.src = objectUrl;
                preview.hidden = false;
                preview.parentElement.querySelector('[data-image-placeholder]').hidden = true;
            });
        });
        form.querySelectorAll('[data-hours-row]').forEach((row) => {
            const checkbox = row.querySelector('[data-hours-open]');
            const sync = () => {
                row.querySelectorAll('[data-hours-time]').forEach((time) => {
                    time.disabled = !checkbox.checked;
                    time.required = checkbox.checked;
                });
                row.querySelector('[data-hours-closed]').hidden = checkbox.checked;
            };
            checkbox.addEventListener('change', sync);
            sync();
        });
        if (form.querySelector('[data-shop-map]')) initializeMap(form);
    });
}

function initializeMap(form) {
    initializeLocationMap(form, {
        container: form.querySelector('[data-shop-map]'),
        status: form.querySelector('[data-map-status]'),
        locate: form.querySelector('[data-shop-geolocate]'),
        restricted: true,
    });
}