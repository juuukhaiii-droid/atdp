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
