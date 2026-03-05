/** @type {import('tailwindcss').Config} */
module.exports = {
    content: ["./*.{html,js}"],
    theme: {
        extend: {
            colors: {
                gold: {
                    DEFAULT: '#D4AF37',
                    light: '#F5D77A',
                    dark: '#B8860B',
                },
                surface: '#141419',
                bg: '#0B0B0F',
            },
            fontFamily: {
                heading: ['"Playfair Display"', 'serif'],
                body: ['Montserrat', 'sans-serif'],
            },
        },
    },
    plugins: [],
}
