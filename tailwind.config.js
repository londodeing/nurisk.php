import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: {
                    50: '#E6F3EC', 100: '#C2E4D2', 200: '#99D3B5', 300: '#6FC297',
                    400: '#4CB47F', 500: '#2CA368', 600: '#0F6B3C', 700: '#0B5730',
                    800: '#084325', 900: '#052E19',
                },
                emergency: '#D7263D',
                warning: '#F2994A',
                safe: '#27AE60',
                info: '#2F80ED',
            },
            screens: {
                'tablet': '600px',
                'desktop': '1024px',
                'command': '1440px',
            },
        },
    },

    plugins: [forms],
};
