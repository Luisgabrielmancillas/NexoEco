import './bootstrap';
import '../css/chat-commerce.css';
import '../css/product-detail.css';
import { initializeChatCommerce } from './chat-commerce';

import Alpine from 'alpinejs';
import { initializeSellerRegistration } from './seller-registration';
import { initializeBuyerAccount } from './buyer-account';
import { initializeShopForms } from './seller-store';
import { initializeStoreDialogs } from './store-dialogs';
import { initializeBuyerLocation } from './buyer-location';
import { initializeLocalDiscovery } from './local-discovery';
import '../css/local-discovery.css';
import { initializeModerator } from './moderator';
import { initializeSellerExperience } from './seller-experience';
import { initializeAppDialogs } from './app-dialogs';
import '../css/app-dialogs.css';
import { initializeReviews } from './reviews';

window.Alpine = Alpine;

initializeAppDialogs();

Alpine.start();

document.querySelectorAll('[data-seller-wizard]').forEach(initializeSellerRegistration);
initializeBuyerAccount();
initializeShopForms();
initializeStoreDialogs();
initializeBuyerLocation();
initializeLocalDiscovery();
initializeModerator();
initializeChatCommerce();
initializeSellerExperience();
initializeReviews();

document.querySelectorAll('[data-document-preview]').forEach((documentPanel) => {
    documentPanel.addEventListener('toggle', () => {
        if (!documentPanel.open) return;
        documentPanel.querySelectorAll('[data-preview-src]').forEach((preview) => {
            if (!preview.hasAttribute('src')) preview.src = preview.dataset.previewSrc;
        });
    });
});
