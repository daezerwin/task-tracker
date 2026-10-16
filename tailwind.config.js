import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        // Category colour classes live on App\Enums\TaskCategory.
        './app/Enums/**/*.php',
    ],

    theme: {
        extend: {
            // Material 3 dark colour roles (Google's GM3 dark palette).
            // Pair each fill with its "on-" colour, e.g. bg-primary text-on-primary.
            colors: {
                surface: {
                    DEFAULT: '#131314',
                    lowest: '#0e0e0e',
                    low: '#1b1b1b',
                    container: '#1e1f20',
                    high: '#282a2c',
                    highest: '#333537',
                    bright: '#37393b',
                },
                'on-surface': { DEFAULT: '#e3e3e3', variant: '#c4c7c5' },
                outline: { DEFAULT: '#8e918f', variant: '#444746' },
                primary: { DEFAULT: '#a8c7fa', container: '#0842a0' },
                'on-primary': { DEFAULT: '#062e6f', container: '#d3e3fd' },
                secondary: { DEFAULT: '#7fcfff', container: '#004a77' },
                'on-secondary': { DEFAULT: '#003355', container: '#c2e7ff' },
                tertiary: { DEFAULT: '#6dd58c', container: '#0f5223' },
                'on-tertiary': { DEFAULT: '#0a3818', container: '#c4eed0' },
                error: { DEFAULT: '#f2b8b5', container: '#8c1d18' },
                'on-error': { DEFAULT: '#601410', container: '#f9dedc' },
            },
            fontFamily: {
                sans: ['"Google Sans"', 'Roboto', ...defaultTheme.fontFamily.sans],
            },
            // Material 3 type scale: text-headline-sm, text-title-md, text-label-lg, …
            fontSize: {
                'display-sm': ['36px', { lineHeight: '44px' }],
                'headline-lg': ['32px', { lineHeight: '40px' }],
                'headline-md': ['28px', { lineHeight: '36px' }],
                'headline-sm': ['24px', { lineHeight: '32px' }],
                'title-lg': ['22px', { lineHeight: '28px' }],
                'title-md': ['16px', { lineHeight: '24px', letterSpacing: '0.15px', fontWeight: '500' }],
                'title-sm': ['14px', { lineHeight: '20px', letterSpacing: '0.1px', fontWeight: '500' }],
                'body-lg': ['16px', { lineHeight: '24px', letterSpacing: '0.5px' }],
                'body-md': ['14px', { lineHeight: '20px', letterSpacing: '0.25px' }],
                'body-sm': ['12px', { lineHeight: '16px', letterSpacing: '0.4px' }],
                'label-lg': ['14px', { lineHeight: '20px', letterSpacing: '0.1px', fontWeight: '500' }],
                'label-md': ['12px', { lineHeight: '16px', letterSpacing: '0.5px', fontWeight: '500' }],
                'label-sm': ['11px', { lineHeight: '16px', letterSpacing: '0.5px', fontWeight: '500' }],
            },
        },
    },

    plugins: [forms],
};
