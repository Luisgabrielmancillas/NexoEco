import fs from 'node:fs/promises';
import path from 'node:path';
const root = path.resolve('.');
const walk = async dir => (await Promise.all((await fs.readdir(dir, { withFileTypes: true })).map(async entry => entry.isDirectory() ? walk(path.join(dir, entry.name)) : path.join(dir, entry.name)))).flat();
const views = (await walk('resources/views')).filter(file => file.endsWith('.blade.php')).sort();
const php = (await Promise.all(['app', 'routes', 'config', 'database', 'tests'].map(walk))).flat().filter(file => file.endsWith('.php'));
const templates = new Map(await Promise.all(views.map(async file => [file.replaceAll('\\', '/').replace('resources/views/', '').replace('.blade.php', '').replaceAll('/', '.'), await fs.readFile(file, 'utf8')])));
const routes = JSON.parse((await fs.readFile('storage/audit-routes.json', 'utf8')).replace(/^\uFEFF/, ''));
const pages = JSON.parse(await fs.readFile('storage/framework/audit-pages/manifest.json', 'utf8'));
const testSources = (await Promise.all((await walk('tests')).filter(file => file.endsWith('.php')).map(file => fs.readFile(file, 'utf8')))).join('\n');
const exactTestRoutes = new Set([...testSources.matchAll(/route\(['"]([a-zA-Z0-9_.-]+)['"]/g)].map(match => match[1]));
const dependencies = new Set();
const visit = name => {
    if (dependencies.has(name)) return;
    dependencies.add(name);
    const source = templates.get(name) || '';
    for (const match of source.matchAll(/@(?:extends|include(?:If|When|Unless|First)?)\(\s*['"]([\w.-]+)['"]/g)) visit(match[1]);
    for (const match of source.matchAll(/<x-([a-z0-9.-]+)/g)) visit(match[1] === 'guest-layout' ? 'layouts.guest' : match[1] === 'app-layout' ? 'layouts.app' : 'components.'+match[1]);
};
for (const item of pages) visit(item.view);
// These dynamic layout branches are explicitly exercised by the role fixtures.
for (const name of ['layouts.base', 'layouts.marketplace', 'layouts.admin-panel', 'layouts.seller-panel', 'layouts.moderator-panel']) visit(name);
const missing = [];
for (const file of php.filter(file => file.startsWith('app'))) {
    const source = await fs.readFile(file, 'utf8');
    for (const match of source.matchAll(/\bview\(\s*['"]([\w.-]+)['"]/g)) if (!templates.has(match[1])) missing.push({ file, view: match[1] });
}
const composer = JSON.parse(await fs.readFile('composer.lock', 'utf8'));
const npm = JSON.parse(await fs.readFile('package-lock.json', 'utf8'));
const versions = Object.fromEntries(['laravel/framework', 'laravel/breeze', 'munafio/chatify', 'pestphp/pest', 'phpunit/phpunit'].map(name => [name, [...composer.packages, ...(composer['packages-dev'] || [])].find(item => item.name === name)?.version]));
for (const name of ['vite', 'tailwindcss', 'alpinejs', 'axios', 'leaflet']) versions[name] = npm.packages['node_modules/'+name]?.version;
const lines = ['# Inventario reproducible de NexoEco', '', 'Fecha: 2026-11-04. Generado desde el árbol, route:list, lockfiles y fixtures de prueba. No consulta la base de datos de trabajo.', '', `- ${routes.length} rutas registradas; ${views.length} plantillas Blade; ${php.length} archivos PHP enumerados en app, routes, config, database y tests.`, `- ${pages.length} variantes de pantalla renderizadas mediante solicitudes HTTP en SQLite en memoria.`, '- “Referenciada en pruebas” identifica una referencia literal en el código de la suite, no es una medición de cobertura de todas las ramas.', '- Las vistas solo inventariadas no se presentan como revisadas visualmente. Las inclusiones condicionales requieren comprobar la rama correspondiente.', '', '## Versiones resueltas', '', '| Paquete | Versión del lockfile |', '|---|---|', ...Object.entries(versions).map(([key,value])=>`| ${key} | ${value ?? 'No encontrado'} |`), '', '## Rutas', '', '| Método | URI | Nombre | Acción | Cobertura demostrada o pendiente |', '|---|---|---|---|---|'];
for (const route of routes) {
    const coverage = pages.some(item => item.route === route.name) ? 'Render HTTP 200 + análisis responsive/axe en variantes del manifest' : exactTestRoutes.has(route.name) ? 'Referenciada en suite PHP ejecutada; revisar escenario en tests' : 'Inventariada; sin ejecución individual demostrada en esta auditoría';
    lines.push(`| ${route.method.replaceAll('|', '/')} | \`${route.uri}\` | ${route.name ?? '—'} | \`${route.action}\` | ${coverage} |`);
}
lines.push('', '## Variantes de pantalla', '', '| ID de evidencia | Rol | Ruta | Vista de entrada |', '|---|---|---|---|');
for (const item of pages) lines.push(`| ${item.id} | ${item.role ?? 'Público'} | ${item.route} | ${item.view} |`);
lines.push('', '## Todas las vistas', '', '| Vista | Evidencia |', '|---|---|');
for (const name of templates.keys()) lines.push(`| \`${name}\` | ${pages.some(item => item.view === name) ? 'Render HTTP demostrado; ver manifest' : dependencies.has(name) ? 'Dependencia estática de pantalla renderizada; rama condicional no garantizada' : 'Inventariada/compilable; sin renderizado visual demostrado'} |`);
lines.push('', '## Referencias literales a vistas inexistentes', '', missing.length ? JSON.stringify(missing, null, 2) : 'Ninguna en las llamadas view() literales de app/. Las referencias dinámicas no quedan demostradas por este análisis.', '', '## Árbol de código PHP inspeccionable', '', ...php.sort().map(file => '- `'+file.replaceAll('\\', '/')+'`'), '');
await fs.mkdir('docs/audit', { recursive: true });
await fs.writeFile('docs/audit/INVENTARIO.md', lines.join('\n'));
await fs.writeFile('docs/audit/inventory.json', JSON.stringify({ routes, pages, views: [...templates.keys()], versions, missingLiteralViews: missing }, null, 2));
console.log(JSON.stringify({ routes: routes.length, views: views.length, pageVariants: pages.length, phpFiles: php.length, versions, missingLiteralViews: missing }));
