export function initializeReviews() {
    document.querySelectorAll('[data-review-cancel]').forEach((button) => {
        button.addEventListener('click', () => {
            const editor = button.closest('.review-editor');
            button.closest('form').reset();
            editor.open = false;
            editor.querySelector('summary').focus();
        });
    });
}
