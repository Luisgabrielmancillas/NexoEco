/** @type {import('tailwindcss').Config} */
module.exports = {
    darkMode: 'class', // 👈 IMPORTANTE

    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
    ],

    theme: {
        extend: {},
    },

    plugins: [
        require('@tailwindcss/forms'),
    ],
}