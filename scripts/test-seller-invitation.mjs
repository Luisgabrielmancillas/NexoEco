import assert from 'node:assert/strict';
import { test } from 'node:test';
import { initializeSellerInvitation } from '../resources/js/seller-invitation.js';

function setup(t, minimized = false) {
    t.mock.timers.enable({ apis: ['setTimeout'] });
    const closeButton = new EventTarget();
    const dialog = new EventTarget();
    dialog.open = false;
    dialog.classList = { add() {}, remove() {} };
    dialog.style = { setProperty() {} };
    dialog.getBoundingClientRect = () => ({ left: 300, top: 200, width: 460, height: 350 });
    dialog.querySelector = () => closeButton;
    dialog.showModal = () => { dialog.open = true; };
    dialog.close = () => { dialog.open = false; dialog.dispatchEvent(new Event('close')); };
    const launcher = new EventTarget();
    launcher.hidden = true;
    launcher.classList = { add() {}, remove() {} };
    launcher.getBoundingClientRect = () => ({ left: 900, top: 700, width: 200, height: 48 });
    launcher.focus = () => { launcher.focused = true; };
    const saved = new Map(minimized ? [['nexo-seller-invitation-minimized', '1']] : []);
    const document = {
        hidden: false,
        activeElement: { matches: () => false },
        body: { classList: { add() {}, remove() {} } },
        querySelector: (selector) => selector === '[data-seller-invitation]' ? dialog : selector === '[data-promo-open]' ? launcher : dialog.open ? dialog : null,
    };
    const globals = { document, window: new EventTarget(), matchMedia: () => ({ matches: false }), sessionStorage: { getItem: key => saved.get(key), setItem: (key, value) => saved.set(key, value) } };
    const previous = Object.fromEntries(Object.keys(globals).map(key => [key, globalThis[key]]));
    Object.assign(globalThis, globals);
    t.after(() => {
        for (const key of Object.keys(globals)) {
            if (previous[key] === undefined) delete globalThis[key];
            else globalThis[key] = previous[key];
        }
    });
    initializeSellerInvitation();
    return { dialog, launcher, closeButton, saved, document };
}

test('waits five seconds, minimizes on X, and reopens from the floating button', t => {
    const { dialog, launcher, closeButton, saved } = setup(t);
    t.mock.timers.tick(4999);
    assert.equal(dialog.open, false);
    t.mock.timers.tick(1);
    assert.equal(dialog.open, true);
    closeButton.dispatchEvent(new Event('click'));
    t.mock.timers.tick(650);
    assert.equal(dialog.open, false);
    assert.equal(launcher.hidden, false);
    assert.equal(launcher.focused, true);
    assert.equal(saved.get('nexo-seller-invitation-minimized'), '1');
    launcher.dispatchEvent(new Event('click'));
    assert.equal(dialog.open, true);
    assert.equal(launcher.hidden, true);
});

test('a minimized invitation stays minimized during the session', t => {
    const { dialog, launcher } = setup(t, true);
    t.mock.timers.tick(6000);
    assert.equal(dialog.open, false);
    assert.equal(launcher.hidden, false);
});

test('does not interrupt typing and remains available through the floating button', t => {
    const { dialog, launcher, document } = setup(t);
    document.activeElement.matches = () => true;
    t.mock.timers.tick(5000);
    assert.equal(dialog.open, false);
    assert.equal(launcher.hidden, false);
});

test('Escape minimizes instead of losing access to the invitation', t => {
    const { dialog, launcher } = setup(t);
    t.mock.timers.tick(5000);
    const cancel = new Event('cancel', { cancelable: true });
    dialog.dispatchEvent(cancel);
    assert.equal(cancel.defaultPrevented, true);
    t.mock.timers.tick(650);
    assert.equal(dialog.open, false);
    assert.equal(launcher.hidden, false);
});
