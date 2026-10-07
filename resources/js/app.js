import './bootstrap';

import Alpine from 'alpinejs';
import { initializeSellerRegistration } from './seller-registration';
import { initializeBuyerAccount } from './buyer-account';
import { initializeShopForms } from './seller-store';
import { initializeStoreDialogs } from './store-dialogs';
import { initializeBuyerLocation } from './buyer-location';
import { initializeModerator } from './moderator';

window.Alpine = Alpine;

Alpine.start();

document.querySelectorAll('[data-seller-wizard]').forEach(initializeSellerRegistration);
initializeBuyerAccount();
initializeShopForms();
initializeStoreDialogs();
initializeBuyerLocation();
initializeModerator();

document.querySelectorAll('[data-document-preview]').forEach((documentPanel) => {
    documentPanel.addEventListener('toggle', () => {
        if (!documentPanel.open) return;
        documentPanel.querySelectorAll('[data-preview-src]').forEach((preview) => {
            if (!preview.hasAttribute('src')) preview.src = preview.dataset.previewSrc;
        });
    });
});
