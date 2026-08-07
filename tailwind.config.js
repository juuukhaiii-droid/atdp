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
                body: ['Inter', '"Noto Sans Khmer"', ...defaultTheme.fontFamily.sans],
                khmer: ['"Noto Sans Khmer"', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    dark: '#111827',
                    primary: '#dc2626',
                    'primary-soft': '#fee2e2',
                    success: '#16a34a',
                    warning: '#f59e0b',
                    danger: '#ef4444',
                },
                ink: {
                    DEFAULT: '#0f172a',
                    soft: '#64748b',
                },
            },
            borderRadius: {
                'brand-lg': '18px',
                'brand-md': '14px',
            },
            boxShadow: {
                card: '0 10px 30px rgba(15, 23, 42, 0.06)',
                'card-lg': '0 15px 40px rgba(15, 23, 42, 0.1)',
            },
        },
    },

    corePlugins: {
        // Tailwind's `.collapse` utility (visibility: collapse) collides
        // with Bootstrap's own `.collapse` class, which the navbar toggle
        // relies on for show/hide. Both stylesheets load on every page, so
        // Tailwind was silently making Bootstrap-collapsed content
        // permanently invisible even after Bootstrap expanded it. Unused
        // elsewhere in this app (checked: no `visible`/`invisible`/`collapse`
        // Tailwind usage exists outside Bootstrap's own class).
        visibility: false,
    },

    plugins: [forms],
};
