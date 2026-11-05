// Export with NEXOECO_CAROUSEL_EXPORT=1 php artisan test --filter=StoreCarouselTest, after npm run build.
import fs from 'node:fs/promises';
import path from 'node:path';
import http from 'node:http';
import assert from 'node:assert/strict';
import { createRequire } from 'node:module';
const require = createRequire(import.meta.url);
const { chromium } = require('../storage/framework/audit-tools/node_modules/playwright');
const root = path.resolve('.');
const output = path.join(root, 'docs/carousel');
await fs.mkdir(output, { recursive: true });
const fixtures = path.join(root, 'storage/framework/carousel-pages');
const server = http.createServer(async (req, res) => {
    const url = new URL(req.url, 'http://127.0.0.1:4180');
    if (url.pathname.endsWith('/notificaciones/conteo')) { res.setHeader('Content-Type', 'application/json'); res.end('{"unread":0}'); return; }
    const file = url.pathname === '/comprador/dashboard' ? path.join(fixtures, 'home.html') : url.pathname === '/comprador/tiendas' ? path.join(fixtures, url.searchParams.has('cercanas') ? 'nearby.html' : 'all.html') : path.resolve(root, 'public', '.'+decodeURIComponent(url.pathname));
    if (!file.startsWith(root+path.sep)) { res.writeHead(403); res.end(); return; }
    try { res.setHeader('Content-Type', ({ '.html': 'text/html; charset=utf-8', '.js': 'text/javascript', '.css': 'text/css', '.svg': 'image/svg+xml', '.png': 'image/png' })[path.extname(file)] || 'application/octet-stream'); res.end(await fs.readFile(file)); }
    catch { res.writeHead(404); res.end(); }
});
await new Promise(resolve => server.listen(4180, '127.0.0.1', resolve));
const browser = await chromium.launch({ channel: 'msedge', headless: true });
const report = { passed: [], layouts: [], accessibility: [], errors: [] };
const pass = message => { report.passed.push(message); console.log('PASS', message); };
try {
    const context = await browser.newContext({ viewport: { width: 1440, height: 1000 }, reducedMotion: 'no-preference' });
    context.setDefaultTimeout(12000);
    await context.route('**/*', route => new URL(route.request().url()).hostname === '127.0.0.1' ? route.continue() : route.abort());
    const page = await context.newPage();
    page.on('pageerror', error => report.errors.push(error.message));
    await page.goto('http://127.0.0.1:4180/comprador/dashboard');
    const nearby = page.locator('.store-carousel-nearby');
    const track = nearby.locator('[data-carousel-track]');
    await track.scrollIntoViewIfNeeded();
    await page.mouse.move(0, 0);
    await page.waitForFunction(() => document.querySelector('#nearby-store-track').scrollLeft > 20, null, { timeout: 8000 });
    pass('Avance automático real a los cinco segundos');
    assert.equal(await page.locator('[data-carousel-controls]').count(), 0);
    await track.focus();
    await page.keyboard.press('Space');
    await track.evaluate(el => el.blur());
    await page.mouse.move(0, 0);
    await page.waitForTimeout(500);
    const paused = await track.evaluate(el => el.scrollLeft);
    await page.waitForTimeout(5200);
    assert.ok(Math.abs(await track.evaluate(el => el.scrollLeft) - paused) < 2);
    pass('Sin selector visible; Espacio permite pausar el avance durante más de cinco segundos');
    await track.focus();
    await page.keyboard.press('End');
    await page.waitForFunction(() => { const el = document.querySelector('#nearby-store-track'); return Math.abs(el.scrollLeft - (el.scrollWidth-el.clientWidth)) < 2; });
    await page.keyboard.press('Home');
    await page.waitForFunction(() => document.querySelector('#nearby-store-track').scrollLeft < 2);
    await page.keyboard.press('ArrowRight');
    await page.waitForFunction(() => document.querySelector('#nearby-store-track').scrollLeft > 20);
    pass('Teclado: Inicio, Fin y flechas recorren el carrusel');
    await nearby.getByRole('link', { name: /Ver todas/ }).click();
    await page.getByRole('heading', { name: 'Todas las tiendas cerca de ti', exact: true }).waitFor({ state: 'visible' });
    assert.ok(page.url().includes('/comprador/tiendas?cercanas=1'));
    assert.equal(await page.getByRole('heading', { name: 'Todas las tiendas cerca de ti', exact: true }).count(), 1);
    assert.equal(await page.locator('.store-directory .nearby-store-card').count(), 24);
    pass('Ver todas abre el directorio de cercanas con 24 resultados y paginación');
    for (const route of ['/comprador/dashboard', '/comprador/tiendas', '/comprador/tiendas?cercanas=1']) {
        await page.goto('http://127.0.0.1:4180'+route);
        for (const width of [320, 375, 390, 430, 768, 1024, 1280, 1440, 1920]) {
            await page.setViewportSize({ width, height: 1000 });
            await page.evaluate(() => new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve))));
            const layout = await page.evaluate(() => ({ width: innerWidth, document: document.documentElement.scrollWidth, carousels: [...document.querySelectorAll('[data-carousel-track]')].map(el => ({ rows: new Set([...el.children].map(item => Math.round(item.offsetTop))).size, overflowing: el.scrollWidth > el.clientWidth, scrollbar: getComputedStyle(el).scrollbarWidth })) }));
            report.layouts.push({ route, ...layout });
            assert.ok(layout.document <= width + 1, `${route} fits ${width}`);
            assert.ok(layout.carousels.every(item => item.rows === 1), 'Cards stay in one row');
            assert.ok(layout.carousels.every(item => item.scrollbar === 'none'), 'Both carousels hide scrollbars');
            if ([375, 1440].includes(width)) {
                await page.addScriptTag({ path: path.join(root, 'storage/framework/audit-tools/node_modules/axe-core/axe.min.js') });
                const result = await page.evaluate(async () => {
                    const result = await axe.run(document, { runOnly: { type: 'tag', values: ['wcag2a', 'wcag2aa', 'wcag21aa', 'wcag22aa'] } });
                    return result.violations.map(v => ({ id: v.id, nodes: v.nodes.map(n => ({ target: n.target, summary: n.failureSummary })) }));
                });
                report.accessibility.push({ route, width, violations: result });
                const name = route.includes('cercanas') ? 'cercanas' : route.endsWith('dashboard') ? 'inicio' : 'todas';
                if (route.endsWith('dashboard')) await page.locator('#tiendas-cercanas').scrollIntoViewIfNeeded();
                else await page.evaluate(() => scrollTo(0, 0));
                await page.screenshot({ path: path.join(output, `${name}-${width}.png`) });
            }
        }
    }
    pass('Una fila y documento sin desbordamiento en nueve anchos, en inicio y ambos directorios');
    await page.emulateMedia({ reducedMotion: 'reduce' });
    await page.goto('http://127.0.0.1:4180/comprador/dashboard');
    await track.scrollIntoViewIfNeeded();
    await page.mouse.move(0, 0);
    await page.waitForTimeout(5200);
    assert.equal(await track.evaluate(el => el.scrollLeft), 0);
    pass('Movimiento reducido: comienza pausado y no gira automáticamente');
    assert.deepEqual(report.errors, []);
    assert.ok(report.accessibility.every(item => item.violations.length === 0), 'axe must pass');
    pass('Sin errores JavaScript ni violaciones automáticas de axe en las pantallas probadas');
} finally {
    await fs.writeFile(path.join(output, 'results.json'), JSON.stringify(report, null, 2));
    await browser.close();
    await new Promise(resolve => server.close(resolve));
}
