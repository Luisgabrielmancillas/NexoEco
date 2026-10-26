import tokens from '../design-system/tokens.json';

// Leaflet expects concrete colors, including when rendering to canvas.
export function themeColor(name) {
    const value = tokens.colors[name];
    if (!value) throw new Error(`Unknown color token: ${name}`);
    return value.startsWith('{') ? themeColor(value.slice(1, -1)) : value;
}
