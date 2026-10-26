import assert from 'node:assert/strict';
import { readFileSync, readdirSync, writeFileSync } from 'node:fs';
import path from 'node:path';
import postcss from 'postcss';

const source = JSON.parse(readFileSync('resources/design-system/tokens.json', 'utf8'));
const color = name => {
    const value = source.colors[name];
    assert(value, `Unknown color: ${name}`);
    return value.startsWith('{') ? color(value.slice(1, -1)) : value;
};
const luminance = hex => {
    const channels = [1, 3, 5].map(index => parseInt(hex.slice(index, index + 2), 16) / 255)
        .map(value => value <= .04045 ? value / 12.92 : ((value + .055) / 1.055) ** 2.4);
    return channels.reduce((sum, value, index) => sum + value * [.2126, .7152, .0722][index], 0);
};
const contrast = (foreground, background) => {
    const [low, high] = [luminance(foreground), luminance(background)].sort((a, b) => a - b);
    return (high + .05) / (low + .05);
};
const combinations = [
    ['on-primary', 'primary', 4.5], ['on-primary', 'primary-hover', 4.5], ['on-primary', 'primary-active', 4.5],
    ['on-primary', 'danger', 4.5], ['text-primary', 'surface', 4.5], ['text-secondary', 'background', 4.5],
    ['text-secondary', 'background-secondary', 4.5], ['text-muted', 'surface', 4.5],
    ['primary-text', 'primary-soft', 4.5], ['primary-text', 'primary-subtle', 4.5],
    ['success-text', 'success-soft', 4.5], ['warning-text', 'warning-soft', 4.5],
    ['danger-text', 'danger-soft', 4.5], ['info-text', 'info-soft', 4.5],
    ['dark-text', 'dark-surface', 4.5], ['dark-muted', 'dark-surface', 4.5],
    ['border-strong', 'surface', 3], ['primary', 'surface', 3],
    ['store-terracotta', 'store-terracotta-soft', 4.5], ['store-forest', 'store-forest-soft', 4.5], ['store-ocean', 'store-ocean-soft', 4.5],
];
const contrastResults = combinations.map(([foreground, background, minimum]) => {
    const ratio = contrast(color(foreground), color(background));
    assert(ratio >= minimum, `${foreground}/${background}: ${ratio.toFixed(2)} < ${minimum}`);
    return { foreground, background, ratio: Number(ratio.toFixed(2)), minimum };
});
const walk = directory => readdirSync(directory, { withFileTypes: true }).flatMap(entry => {
    const file = path.join(directory, entry.name);
    return entry.isDirectory() ? walk(file) : [file];
});
const files = walk('resources').filter(file => /\.(css|js|blade\.php)$/.test(file));
const declared = new Set();
const references = [];
const inventory = [];
const literals = [];
const directPairs = [];
const variables = Object.fromEntries(Object.keys(source.colors).map(name => [`--color-${name}`, color(name)]));
for (const [alias, name] of Object.entries(source.aliases)) variables[`--${alias}`] = color(name);
const resolve = (value, local) => {
    for (let pass = 0; pass < 8; pass++) value = value.replace(/var\((--[\w-]+)\)/g, (match, name) => local[name] ?? variables[name] ?? match);
    return value;
};
for (const file of files) {
    const text = readFileSync(file, 'utf8');
    const generated = file.endsWith('design-tokens.css');
    inventory.push({ file: file.replaceAll(path.sep, '/'), lines: text.split('\n').length, embeddedStyles: (text.match(/<style\b/g) || []).length });
    for (const match of text.matchAll(/(--[\w-]+)\s*:/g)) declared.add(match[1]);
    for (const match of text.matchAll(/var\((--[\w-]+)(\s*,)?/g)) references.push({ file, name: match[1], fallback: Boolean(match[2]) });
    const styles = file.endsWith('.css') ? [text] : [...text.matchAll(/<style\b[^>]*>([\s\S]*?)<\/style>/g)].map(match => match[1]);
    if (!generated) {
        const inline = [...text.matchAll(/(?:style|fill|stroke|bgcolor)="([^"]*)"/g)].map(match => match[1]);
        const coloredCode = file.endsWith('.js') ? [text] : [...styles, ...inline];
        for (const code of coloredCode) {
            for (const match of code.matchAll(/#[\da-f]{3,8}\b|\b(?:rgb|rgba|hsl|hsla)\(\s*[\d.]/gi)) literals.push({ file, value: match[0] });
        }
    }
    for (const css of styles) {
        const root = postcss.parse(css, { from: file });
        const local = {};
        root.walkDecls(declaration => { if (declaration.prop.startsWith('--')) local[declaration.prop] = declaration.value; });
        root.walkRules(rule => {
            const declarations = {};
            rule.nodes?.filter(node => node.type === 'decl').forEach(node => { declarations[node.prop] = node.value; });
            const foreground = resolve(declarations.color || '', local);
            const background = resolve(declarations['background-color'] || declarations.background || '', local);
            if (/^#[\da-f]{6}$/i.test(foreground) && /^#[\da-f]{6}$/i.test(background)) {
                const ratio = contrast(foreground, background);
                assert(ratio >= 4.5, `${file}: ${rule.selector} has ${ratio.toFixed(2)}:1 text contrast`);
                directPairs.push({ file: file.replaceAll(path.sep, '/'), selector: rule.selector, ratio: Number(ratio.toFixed(2)) });
            }
        });
    }
}
assert.equal(literals.length, 0, `Hardcoded colors outside the token source: ${JSON.stringify(literals)}`);
const unknown = references.filter(reference => !declared.has(reference.name) && !reference.name.startsWith('--tw-') && !reference.fallback);
assert.equal(unknown.length, 0, `Undefined CSS tokens: ${JSON.stringify(unknown)}`);
const residualWarmColors = ['warning', 'warning-text', 'warning-soft', 'warning-border', 'store-terracotta', 'store-terracotta-soft'].map(name => ({
    token: name,
    value: color(name),
    classification: name.startsWith('warning') ? 'functional-warning' : 'seller-selected-store-theme',
}));
const report = {
    method: 'Static source, CSS syntax and token-contrast audit. No browser screenshots or layout measurements.',
    paletteSource: 'resources/design-system/tokens.json',
    templatesReviewed: inventory.filter(item => item.file.endsWith('.blade.php')).length,
    inventory,
    contrast: contrastResults,
    directColorPairs: directPairs,
    residualWarmColors,
    hardcodedStyleColors: literals,
    undefinedVariables: unknown,
    visualVerification: 'Unavailable: the browser inventory was empty and the in-app browser was unavailable.',
};
writeFileSync('docs/design-system-audit.json', JSON.stringify(report, null, 2) + '\n');
console.log(`Design system: ${report.templatesReviewed} templates, ${inventory.length} source files, ${contrastResults.length} token combinations and ${directPairs.length} explicit CSS text/background pairs checked.`);
console.log('No hardcoded style colors or undefined CSS variables. Responsive rendering and inherited/overlaid colors still require browser verification.');
