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
            colors: {
                primary: {
                    100: 'var(--colors-primary-g100)',
                    500: 'var(--colors-primary-g500)',
                    600: 'var(--colors-primary-g600)',
                },
                mono: {
                    white: 'var(--colors-mono-white)',
                    black: 'var(--colors-mono-black)',
                    50: 'var(--colors-mono-g50)',
                    100: 'var(--colors-mono-g100)',
                    200: 'var(--colors-mono-g200)',
                    300: 'var(--colors-mono-g300)',
                    600: 'var(--colors-mono-g600)',
                    900: 'var(--colors-mono-g900)',
                },
                success: 'var(--colors-success)',
                'success-bg': 'var(--colors-success-bg)',
                error: 'var(--colors-error)',
                up: 'var(--colors-up)',
                'up-bg': 'var(--colors-up-bg)',
                down: 'var(--colors-down)',
                'down-bg': 'var(--colors-down-bg)',
                info: 'var(--colors-info)',
                'info-bg': 'var(--colors-info-bg)',
            },
            fontFamily: {
                sans: ['Reddit Sans', ...defaultTheme.fontFamily.sans],
            },
            borderRadius: {
                pill: '999px',
            },
            spacing: {
                'xxxs': '0.25rem',
                'xxs': '0.5rem',
                'xs': '0.75rem',
            },
            fontSize: {
                'xxs': '0.625rem',
            },
            boxShadow: {
                card: '0 2px 8px rgba(0,0,0,.06)',
                dropdown: '0 4px 20px 0 hsla(0,0%,54%,.16), 0 4px 20px 0 rgba(0,0,0,.1)',
                elevated: '0 8px 32px rgba(0,0,0,.12)',
            },
            zIndex: {
                dropdown: '100',
                modal: '1000',
            },
        },
    },

    plugins: [forms],
};
