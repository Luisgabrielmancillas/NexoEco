// Run after NEXOECO_AUDIT_EXPORT=1 php artisan test --filter='all marketplace workspaces render'.
// QA tools are isolated: npm install --prefix storage/framework/audit-tools --no-save --package-lock=false playwright axe-core
import http from 'node:http';
import fs from 'node:fs/promises';
import path from 'node:path';
import { createRequire } from 'node:module';
const require = createRequire(import.meta.url);
const { chromium } = require('../storage/framework/audit-tools/node_modules/playwright');
const root = path.resolve('.');
const fixtures = path.join(root, 'storage/framework/audit-pages');
const manifest = JSON.parse(await fs.readFile(path.join(fixtures, 'manifest.json'), 'utf8'));
const phase = process.argv.includes('--baseline') ? 'baseline' : 'verified';
const output = path.join(root, 'docs/audit', phase);
await fs.mkdir(output, { recursive: true });
const mime = { '.js': 'text/javascript', '.css': 'text/css', '.svg': 'image/svg+xml', '.png': 'image/png', '.jpg': 'image/jpeg', '.pdf': 'application/pdf', '.html': 'text/html; charset=utf-8' };
const server = http.createServer(async (req, res) => {
    const url = new URL(req.url, 'http://127.0.0.1:4179');
    if (/\/mensajes\/\d+\/mensajes$/.test(url.pathname)) { res.setHeader('Content-Type', 'application/json'); res.end(JSON.stringify({ messages: [] })); return; }
    if (url.pathname.endsWith('/notificaciones/conteo')) { res.setHeader('Content-Type', 'application/json'); res.end(JSON.stringify({ unread: 0 })); return; }
    const id = url.pathname.startsWith('/audit/') ? url.pathname.slice(7) : null;
    const item = id ? manifest.find(item => item.id === id) : manifest.find(item => new URL(item.url).pathname === url.pathname);
    const file = item ? path.join(fixtures, item.id+'.html') : path.resolve(root, 'public', '.'+decodeURIComponent(url.pathname));
    if (!file.startsWith(root+path.sep)) { res.writeHead(403); res.end(); return; }
    try { res.setHeader('Content-Type', mime[path.extname(file)] || 'application/octet-stream'); res.end(await fs.readFile(file)); }
    catch { res.writeHead(404); res.end('Archivo de prueba no disponible'); }
});
await new Promise(resolve => server.listen(4179, '127.0.0.1', resolve));
const browser = await chromium.launch({ channel: 'msedge', headless: true });
const widths = [320, 375, 390, 430, 768, 1024, 1280, 1440, 1920];
const results = [];
try {
    const context = await browser.newContext({ reducedMotion: 'reduce' });
    context.setDefaultTimeout(10000);
    await context.route('**/*', route => new URL(route.request().url()).hostname === '127.0.0.1' ? route.continue() : route.abort());
    if (process.argv.includes('--interactions')) {
        const { runInteractions } = await import('./audit-interactions.mjs');
        await runInteractions({ context, manifest, output, root });
        await context.close();
        process.exitCode = 0;
    } else {
    for (const item of manifest) {
        if (process.argv.includes('--diagnose') && ![0, 4, 11, 18, 20, 39].some(index => item.id.startsWith(String(index).padStart(2, '0')+'-'))) continue;
        const page = await context.newPage();
        const errors = [];
        const networkErrors = [];
        page.on('pageerror', error => errors.push(error.message));
        page.on('response', response => { if (response.status() >= 400 && new URL(response.url()).hostname === '127.0.0.1') networkErrors.push({ url: response.url(), status: response.status() }); });
        const result = { ...item, errors, networkErrors, layouts: [], accessibility: [] };
        await page.goto('http://127.0.0.1:4179/audit/'+item.id);
        await page.evaluate(() => document.fonts.ready);
        for (const width of widths) {
            await page.setViewportSize({ width, height: width < 768 ? 844 : 900 });
            await page.evaluate(() => new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve))));
            if (process.argv.includes('--diagnose')) {
                if (width === 320) console.log(item.id, JSON.stringify(await page.evaluate(() => [...document.querySelectorAll('html, body, body *')].filter(el => (el.scrollWidth > el.clientWidth+1 || el.getBoundingClientRect().right > innerWidth+1) && !['STYLE', 'SCRIPT'].includes(el.tagName)).slice(0, 20).map(el => ({ tag: el.tagName, class: el.className?.baseVal ?? el.className, width: el.clientWidth, scrollWidth: el.scrollWidth, right: el.getBoundingClientRect().right, minWidth: getComputedStyle(el).minWidth, display: getComputedStyle(el).display, grid: getComputedStyle(el).gridTemplateColumns, overflow: getComputedStyle(el).overflowX })))));
                continue;
            }
            result.layouts.push(await page.evaluate(() => {
                const overflow = [...document.querySelectorAll('body *')].filter(el => {
                    const bounds = el.getBoundingClientRect();
                    if (!bounds.width || !bounds.height || getComputedStyle(el).visibility === 'hidden') return false;
                    if (el.closest('[inert], dialog:not([open])')) return false;
                    let parent = el.parentElement;
                    while (parent && parent !== document.body) {
                        if (['auto', 'scroll', 'hidden', 'clip'].includes(getComputedStyle(parent).overflowX)) return false;
                        parent = parent.parentElement;
                    }
                    return bounds.right > innerWidth+1 || bounds.left < -1;
                }).slice(0, 8).map(el => ({ tag: el.tagName, class: el.className?.baseVal ?? el.className, text: el.textContent.trim().slice(0, 60) }));
                return { width: innerWidth, documentWidth: document.documentElement.scrollWidth, overflow };
            }));
            if ([375, 1280].includes(width)) {
                await page.addScriptTag({ path: path.join(root, 'storage/framework/audit-tools/node_modules/axe-core/axe.min.js') });
                result.accessibility.push(await page.evaluate(async () => {
                    const report = await axe.run(document, { runOnly: { type: 'tag', values: ['wcag2a', 'wcag2aa', 'wcag21aa', 'wcag22aa'] } });
                    return { width: innerWidth, violations: report.violations.map(v => ({ id: v.id, impact: v.impact, help: v.help, nodes: v.nodes.map(n => ({ target: n.target, summary: n.failureSummary })) })), incomplete: report.incomplete.map(v => v.id) };
                }));
            }
        }
        if (!process.argv.includes('--diagnose') && ['marketplace.index', 'vender', 'conocenos', 'login', 'vendedor.dashboard', 'moderador.dashboard', 'moderador.publicaciones', 'admin.users.index', 'productos.show', 'chat.show'].includes(item.route)) {
            for (const width of [375, 1280]) {
                await page.setViewportSize({ width, height: 900 });
                await page.screenshot({ path: path.join(output, item.id+'-'+width+'.png'), fullPage: true });
            }
        }
        results.push(result);
        console.log(item.id, 'overflow:', result.layouts.filter(l => l.documentWidth > l.width+1 || l.overflow.length).length, 'axe:', result.accessibility.map(a => a.violations.length).join('/'), 'JS:', errors.length);
        await page.close();
    }
    if (!process.argv.includes('--diagnose')) await fs.writeFile(path.join(output, 'results.json'), JSON.stringify({ browser: browser.version(), widths, snapshotMode: true, externalNetworkBlocked: true, results }, null, 2));
    if (phase !== 'baseline' && !process.argv.includes('--diagnose') && results.some(item => item.errors.length || item.layouts.some(layout => layout.documentWidth > layout.width+1 || layout.overflow.length) || item.accessibility.some(check => check.violations.length))) process.exitCode = 1;
    }
} finally { await browser.close(); await new Promise(resolve => server.close(resolve)); }
