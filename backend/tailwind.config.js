/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        "./assets/**/*.{js,jsx,ts,tsx}",
        "./templates/**/*.html.twig",
    ],
    theme: {
        extend: {
            colors: {
                primary: '#2563eb',
                secondary: '#0891b2',
                success: '#10b981',
                danger: '#ef4444',
                dark: '#1e293b',
                light: '#f1f5f9',
            },
            fontFamily: {
                sans: ['Inter', 'sans-serif'],
            },
        },
    },
    plugins: [],
}
