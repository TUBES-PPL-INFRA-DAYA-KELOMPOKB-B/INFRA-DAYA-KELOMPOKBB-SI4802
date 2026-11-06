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
                sans: ['Inter', 'Figtree', ...defaultTheme.fontFamily.sans],
                serif: ['Playfair Display', 'Georgia', 'serif'],
            },
            colors: {
                brand: {
                    navy: '#1F3A5F',
                    'navy-dark': '#142740',
                    'navy-light': '#2b4d7c',
                    teal: '#0E7C86',
                    'teal-dark': '#0a5b62',
                    'teal-light': '#149ca9',
                    amber: '#F2A93B',
                    'amber-dark': '#d48a1c',
                    'amber-light': '#f5bd63',
                    bg: '#EEF3F6',
                },
                navy: {
                    50: '#f0f5fa',
                    100: '#e1ebf4',
                    500: '#2b4d7c',
                    700: '#23416b',
                    800: '#1F3A5F',
                    900: '#152842',
                },
                teal: {
                    50: '#f0f9fa',
                    100: '#d7f2f5',
                    500: '#149ca9',
                    600: '#0E7C86',
                    700: '#0b626a',
                },
                amber: {
                    50: '#fef9ee',
                    100: '#fdf1d3',
                    400: '#f5bd63',
                    500: '#F2A93B',
                    600: '#d48a1c',
                },
                surface: {
                    DEFAULT: '#EEF3F6',
                    card: '#FFFFFF',
                },
            },
        },
    },

    plugins: [forms],
};
