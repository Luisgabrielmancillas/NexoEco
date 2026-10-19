export function initializeStoreDialogs() {
    document.querySelectorAll('[data-store-dialog]').forEach((dialog) => {
        let opener;
        const updateScrollLock = () => document.body.classList.toggle('has-store-dialog', !!document.querySelector('[data-store-dialog][open]'));
        document.querySelectorAll(`[data-dialog-open="${dialog.id}"]`).forEach((button) => {
            button.addEventListener('click', () => {
                opener = button;
                dialog.querySelectorAll('[data-map-src]').forEach((map) => { if (!map.hasAttribute('src')) map.src = map.dataset.mapSrc; });
                dialog.showModal();
                dialog.dispatchEvent(new Event('store-dialog-open'));
                updateScrollLock();
            });
        });
        dialog.querySelectorAll('[data-dialog-close]').forEach((button) => button.addEventListener('click', () => dialog.close()));
        dialog.addEventListener('close', () => { updateScrollLock(); opener?.focus({ preventScroll: true }); });
        let outsidePointer = false;
        const isOutside = (event) => {
            const rect = dialog.getBoundingClientRect();
            return event.target === dialog && (event.clientX < rect.left || event.clientX > rect.right || event.clientY < rect.top || event.clientY > rect.bottom);
        };
        dialog.addEventListener('pointerdown', (event) => { outsidePointer = isOutside(event); });
        dialog.addEventListener('click', (event) => { if (outsidePointer && isOutside(event)) dialog.close(); outsidePointer = false; });
    });
}
