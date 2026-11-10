import assert from 'node:assert/strict';
import fs from 'node:fs/promises';
import path from 'node:path';

export async function runInteractions({ context, manifest, output, root }) {
    const passed = [];
    const page = await context.newPage();
    const errors = [];
    page.on('pageerror', error => errors.push(error.message));
    const open = async (name, role = null, width = 375, height = 844) => {
        const item = manifest.find(item => item.route === name && item.role === role);
        assert.ok(item, `${name} fixture exists`);
        await page.setViewportSize({ width, height });
        await page.goto('http://127.0.0.1:4179/audit/'+item.id);
        await page.evaluate(() => new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve))));
    };
    const pass = message => { passed.push(message); console.log('PASS', message); };
    try {
        await open('conocenos');
        const privacy = page.locator('[data-dialog-open="modal-privacidad"]');
        await privacy.focus();
        await page.keyboard.press('Enter');
        assert.equal(await page.locator('#modal-privacidad').evaluate(el => el.matches(':modal')), true);
        assert.ok(await page.locator('#modal-privacidad iframe').getAttribute('src'));
        await page.screenshot({ path: path.join(output, 'dialogo-legal-375.png') });
        await page.keyboard.press('Escape');
        assert.equal(await privacy.evaluate(el => el === document.activeElement), true);
        pass('Visor legal: Enter, diálogo modal, carga diferida, Escape y restitución del foco');

        await open('comprador.dashboard', 'comprador', 320);
        await page.locator('[data-mobile-nav-toggle]').click();
        assert.equal(await page.locator('[data-mobile-nav-toggle]').getAttribute('aria-expanded'), 'true');
        assert.equal(await page.locator('.buyer-nav-links').evaluate(el => el.contains(document.activeElement)), true);
        for (let index = 0; index < 30; index++) {
            await page.keyboard.press('Tab');
            assert.equal(await page.locator('.buyer-nav-links').evaluate(el => el.contains(document.activeElement)), true);
        }
        await page.keyboard.press('Escape');
        assert.equal(await page.locator('[data-mobile-nav-toggle]').getAttribute('aria-expanded'), 'false');
        pass('Menú comprador: foco contenido, Escape y estado aria-expanded a 320 px');

        await open('moderador.dashboard', 'moderador', 320);
        await page.locator('[data-mod-menu]').click();
        for (let index = 0; index < 20; index++) {
            await page.keyboard.press('Tab');
            assert.equal(await page.locator('[data-mod-sidebar]').evaluate(el => el.contains(document.activeElement)), true);
        }
        await page.keyboard.press('Escape');
        assert.equal(await page.locator('[data-mod-menu]').evaluate(el => el === document.activeElement), true);
        pass('Menú moderador: navegación con Tab y retorno del foco al cerrar');

        await open('login');
        await page.locator('.login-btn').click();
        await page.waitForFunction(() => document.activeElement?.id === 'email');
        assert.equal(await page.locator('#email').getAttribute('aria-invalid'), 'true');
        assert.match(await page.locator('#email-constraint-error').textContent(), /Completa/);
        assert.equal(await page.locator('dialog:modal').count(), 0);
        await page.locator('#email').fill('invalido');
        await page.locator('#email').fill('cuenta@example.test');
        assert.equal(await page.locator('#email-constraint-error').count(), 0);
        pass('Validación: error asociado en español, primer campo enfocado y limpieza tras corregirlo');

        await open('comprador.dashboard', 'comprador');
        await page.locator('[data-dialog-open="buyer-location-dialog"]:visible').first().click();
        await page.locator('[data-buyer-location-form]').evaluate(form => {
            const input = document.createElement('input'); input.id = 'audit-required-in-dialog'; input.required = true; input.setAttribute('aria-label', 'Campo obligatorio de prueba'); form.append(input); input.reportValidity();
        });
        await page.waitForFunction(() => document.activeElement?.id === 'audit-required-in-dialog');
        assert.equal(await page.locator('dialog:modal').count(), 1);
        assert.equal(await page.locator('#buyer-location-dialog').evaluate(el => el.matches(':modal')), true);
        pass('Validación dentro de un diálogo: mantiene un solo modal y enfoca el campo inválido');

        await open('vendedor.productos.create', 'vendedor');
        await page.locator('[data-reservation-toggle]').check();
        assert.equal(await page.locator('#apartado_monto').isEnabled(), true);
        assert.equal(await page.locator('#apartado_monto').getAttribute('required'), '');
        await page.locator('[data-reservation-toggle]').uncheck();
        assert.equal(await page.locator('#apartado_monto').isEnabled(), false);
        pass('Apartados opcionales: campos requeridos solo cuando la opción está activada');

        // Stop actual navigation while preserving and inspecting the real SubmitEvent.
        const duplicateState = await page.evaluate(() => {
            const form = document.createElement('form');
            form.method = 'post';
            const button = document.createElement('button'); button.name = 'decision'; button.value = 'guardar'; form.append(button); document.body.append(form);
            const first = new SubmitEvent('submit', { bubbles: true, cancelable: true, submitter: button });
            form.dispatchEvent(first);
            const second = new SubmitEvent('submit', { bubbles: true, cancelable: true, submitter: button });
            form.dispatchEvent(second);
            const state = { first: first.defaultPrevented, second: second.defaultPrevented, disabled: button.disabled, busy: form.getAttribute('aria-busy') };
            window.dispatchEvent(new PageTransitionEvent('pageshow', { persisted: true }));
            state.restored = !form.hasAttribute('aria-busy') && !button.hasAttribute('aria-disabled');
            return state;
        });
        assert.deepEqual(duplicateState, { first: false, second: true, disabled: false, busy: 'true', restored: true });
        pass('Envíos nativos: bloquea duplicados, conserva el submitter y restaura el estado al regresar');

        let sends = 0;
        let polls = 0;
        let failSend = false;
        await page.route(/\/mensajes\/\d+\/mensajes$/, async route => {
            if (route.request().method() === 'POST') {
                sends++;
                await new Promise(resolve => setTimeout(resolve, 100));
                await route.fulfill({ status: failSend ? 419 : 200, contentType: 'application/json', body: JSON.stringify(failSend ? { message: 'CSRF token mismatch.' } : { sent: true }) });
            } else {
                polls++;
                await route.fulfill({ contentType: 'application/json', body: JSON.stringify({ messages: [{ id: '00000000-0000-4000-8000-000000000001', body: '<img src=x onerror=alert(1)>', mine: false, time: '2026-11-04T12:00:00Z' }] }) });
            }
        });
        await open('chat.show', 'comprador');
        await page.waitForSelector('.chat-bubble');
        assert.equal(await page.locator('.chat-bubble img').count(), 0);
        await page.locator('#chat-body').fill('Mensaje de prueba');
        await page.locator('[data-chat-form]').evaluate(form => { form.requestSubmit(); form.requestSubmit(); });
        await page.waitForFunction(() => !document.querySelector('[data-chat-form] button').disabled);
        assert.equal(sends, 1);
        const previousPolls = polls;
        await page.evaluate(() => { window.dispatchEvent(new PageTransitionEvent('pagehide', { persisted: true })); window.dispatchEvent(new PageTransitionEvent('pageshow', { persisted: true })); });
        await page.waitForFunction(() => document.querySelectorAll('.chat-bubble').length === 1);
        await page.waitForTimeout(150);
        assert.ok(polls > previousPolls);
        failSend = true;
        await page.locator('#chat-body').fill('Borrador que debe conservarse');
        await page.locator('[data-chat-form] button').click();
        await page.waitForFunction(() => document.querySelector('[data-chat-status]').textContent.includes('Tu sesión expiró'));
        assert.equal(await page.locator('#chat-body').inputValue(), 'Borrador que debe conservarse');
        pass('Chat: evita doble envío, escapa HTML, reanuda polling tras volver y conserva borrador ante 419');

        await open('vender');
        await page.getByRole('link', { name: 'Ver planes', exact: true }).click();
        assert.match(await page.locator('#precios').textContent(), /149/);
        assert.match(await page.locator('#precios').textContent(), /299/);
        assert.equal(await page.locator('a[href="#"]').count(), 0);
        pass('Landing: navegación por anclas sin errores y planes derivados de la configuración');

        const landscape = [];
        for (const [name, role] of [['login', null], ['vendedor.dashboard', 'vendedor'], ['moderador.publicaciones', 'moderador'], ['productos.show', 'comprador']]) {
            await open(name, role, 844, 390);
            landscape.push({ name, width: await page.evaluate(() => document.documentElement.scrollWidth) });
            assert.ok(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth+1), name+' fits landscape');
        }
        pass('Orientación horizontal: login, vendedor, moderador y detalle a 844 × 390');
        await open('login', null, 1024, 375);
        await page.locator('.login-btn').scrollIntoViewIfNeeded();
        assert.equal(await page.locator('.login-btn').evaluate(el => { const r = el.getBoundingClientRect(); return r.top >= 0 && r.bottom <= innerHeight; }), true);
        pass('Login con poca altura: el botón de acceso permanece alcanzable a 1024 × 375');
        assert.deepEqual(errors, []);
        await fs.writeFile(path.join(output, 'interactions.json'), JSON.stringify({ passed, errors, landscape, note: 'HTML real renderizado con SQLite en memoria. Fetch de chat simulado, sin pagos ni datos de trabajo.' }, null, 2));
    } finally { await page.close(); }
}
