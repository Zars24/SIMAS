const defaultTheme = require('tailwindcss/defaultTheme');
const forms = require('@tailwindcss/forms');

/** @type {import('tailwindcss').Config} */
module.exports = {
    darkMode: 'class',

    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './app/**/*.php',
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter Variable', 'Inter', ...defaultTheme.fontFamily.sans],
            },

            colors: {
                // Primary green SIMAS (#537963) dengan skala lengkap.
                primary: {
                    50: '#F3F7F4',
                    100: '#E4EBE5',
                    200: '#C9D8CD',
                    300: '#A8C0AF',
                    400: '#7DA08A',
                    500: '#537963',
                    600: '#476956',
                    700: '#3B5748',
                    800: '#314739',
                    900: '#263C32',
                    950: '#18271F',
                },
                // Deep green identitas.
                deep: '#263C32',
                // Latar krem terang.
                cream: {
                    DEFAULT: '#F5F6F2',
                    soft: '#FAFBF8',
                },
                // Soft sage.
                sage: '#E4EBE5',
                // Gold accent.
                gold: {
                    50: '#FBF6EC',
                    100: '#F5EAD2',
                    200: '#EBD6A6',
                    300: '#E0BF74',
                    400: '#D5A85C',
                    500: '#C69344',
                    600: '#A87736',
                    700: '#855C2E',
                    800: '#6D4A2A',
                    900: '#5C3F27',
                },
            },

            boxShadow: {
                card: '0 1px 2px 0 rgb(38 60 50 / 0.06), 0 1px 3px 1px rgb(38 60 50 / 0.08)',
                'card-hover': '0 4px 12px -2px rgb(38 60 50 / 0.12), 0 2px 6px -2px rgb(38 60 50 / 0.08)',
                pop: '0 12px 32px -8px rgb(24 39 31 / 0.24)',
            },

            borderRadius: {
                xl: '0.875rem',
                '2xl': '1.125rem',
            },

            keyframes: {
                'fade-up': {
                    from: { opacity: '0', transform: 'translateY(12px)' },
                    to: { opacity: '1', transform: 'translateY(0)' },
                },
                'fade-in': {
                    from: { opacity: '0' },
                    to: { opacity: '1' },
                },
                'scale-in': {
                    from: { opacity: '0', transform: 'scale(0.96)' },
                    to: { opacity: '1', transform: 'scale(1)' },
                },
                'slide-in-right': {
                    from: { opacity: '0', transform: 'translateX(16px)' },
                    to: { opacity: '1', transform: 'translateX(0)' },
                },
                shimmer: {
                    '100%': { transform: 'translateX(100%)' },
                },
            },

            animation: {
                'fade-up': 'fade-up 0.45s cubic-bezier(0.22, 1, 0.36, 1) both',
                'fade-in': 'fade-in 0.3s ease-out both',
                'scale-in': 'scale-in 0.18s ease-out both',
                'slide-in-right': 'slide-in-right 0.25s cubic-bezier(0.22, 1, 0.36, 1) both',
            },

            transitionTimingFunction: {
                'out-soft': 'cubic-bezier(0.22, 1, 0.36, 1)',
            },
        },
    },

    plugins: [forms],
};
