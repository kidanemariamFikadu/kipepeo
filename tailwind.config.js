import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';
import flowbitePlugin from 'flowbite/plugin';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'media',

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './node_modules/flowbite/**/*.js',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            // Ramp anchored on the same blue used for chart series 1, so UI
            // accents and chart colors agree. See resources/js/charts.js.
            colors: {
                primary: {
                    50: '#eff6fd',
                    100: '#cde2fb',
                    200: '#9ec5f4',
                    300: '#6da7ec',
                    400: '#3987e5',
                    500: '#2a78d6',
                    600: '#256abf',
                    700: '#184f95',
                    800: '#0d366b',
                    900: '#081f3f',
                },
            },
        },
    },

    plugins: [forms, typography, flowbitePlugin],
};
