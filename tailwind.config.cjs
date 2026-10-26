const designTokens = require('./resources/design-system/tokens.json');
const color = name => `rgb(var(--color-${name}-rgb) / <alpha-value>)`;
const primary = Object.fromEntries(Object.keys(designTokens.colors).filter(name => /^primary-\d+$/.test(name)).map(name => [name.split('-')[1], color(name)]));

/** @type {import('tailwindcss').Config} */
module.exports = {
    darkMode: 'class', // 👈 IMPORTANTE

    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
    ],

    theme: {
        extend: {
            colors: {
                primary,
                gray: {
                    50: color('background'),
                    100: color('background-secondary'),
                    200: color('border'),
                    300: color('dark-muted'),
                    400: color('text-muted'),
                    500: color('text-muted'),
                    600: color('text-secondary'),
                    700: color('text-secondary'),
                    800: color('dark-raised'),
                    900: color('dark-surface'),
                    950: color('text-primary'),
                },
                surface: color('surface'),
                canvas: color('background'),
                ink: color('text-primary'),
                muted: color('text-secondary'),
                line: color('border'),
                control: color('border-strong'),
                success: { DEFAULT: color('success'), text: color('success-text'), soft: color('success-soft'), border: color('success-border') },
                warning: { DEFAULT: color('warning'), text: color('warning-text'), soft: color('warning-soft'), border: color('warning-border') },
                danger: { DEFAULT: color('danger'), text: color('danger-text'), soft: color('danger-soft'), border: color('danger-border') },
                info: { DEFAULT: color('info'), text: color('info-text'), soft: color('info-soft'), border: color('info-border') },
            },
        },
    },

    plugins: [
        require('@tailwindcss/forms'),
    ],
}
