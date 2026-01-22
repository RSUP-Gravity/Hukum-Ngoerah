import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    theme: {
        extend: {
            colors: {
                // Primary Colors from RS Ngoerah Logo
                primary: {
                    50: '#E6F7F8',
                    100: '#CCF0F2',
                    200: '#99E0E5',
                    300: '#66D1D8',
                    400: '#33C1CB',
                    500: '#00A0B0', // Main Primary Teal
                    600: '#008A99',
                    700: '#006D79',
                    800: '#00515A',
                    900: '#00343A',
                    950: '#001A1D',
                },
                lime: {
                    50: '#F5F9E8',
                    100: '#EBF3D1',
                    200: '#D7E7A3',
                    300: '#C3DB75',
                    400: '#B8D94D',
                    500: '#A4C639', // Main Lime
                    600: '#83A02D',
                    700: '#627822',
                    800: '#425016',
                    900: '#21280B',
                    950: '#101406',
                },
                // Surface colors for glassmorphism
                surface: {
                    light: 'rgba(255, 255, 255, 0.7)',
                    'light-elevated': 'rgba(255, 255, 255, 0.9)',
                    dark: 'rgba(30, 41, 59, 0.7)',
                    'dark-elevated': 'rgba(30, 41, 59, 0.9)',
                },
                // Document Status Colors
                status: {
                    active: '#10B981',      // Green - Active
                    attention: '#3B82F6',   // Blue - ≤ 6 months
                    warning: '#22C55E',     // Green - ≤ 3 months  
                    critical: '#EAB308',    // Yellow - ≤ 1 month
                    expired: '#EF4444',     // Red - Expired
                },
            },
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                mono: ['JetBrains Mono', ...defaultTheme.fontFamily.mono],
            },
            fontSize: {
                'xs': ['0.75rem', { lineHeight: '1rem' }],
                'sm': ['0.875rem', { lineHeight: '1.25rem' }],
                'base': ['0.875rem', { lineHeight: '1.5rem' }],
                'lg': ['1.125rem', { lineHeight: '1.75rem' }],
                'xl': ['1.25rem', { lineHeight: '1.75rem' }],
                '2xl': ['1.5rem', { lineHeight: '2rem' }],
                '3xl': ['2rem', { lineHeight: '2.25rem' }],
            },
            borderRadius: {
                'glass': '16px',
                'glass-sm': '12px',
                'glass-lg': '20px',
            },
            boxShadow: {
                'glass': '0 8px 32px rgba(0, 0, 0, 0.08)',
                'glass-dark': '0 8px 32px rgba(0, 0, 0, 0.3)',
                'glass-hover': '0 12px 40px rgba(0, 0, 0, 0.12)',
                'glass-elevated': '0 24px 64px rgba(0, 0, 0, 0.2)',
                'button': '0 2px 8px rgba(0, 0, 0, 0.08)',
                'dropdown': '0 12px 32px rgba(0, 0, 0, 0.12)',
            },
            backdropBlur: {
                'glass': '20px',
            },
            animation: {
                'fade-in': 'fadeIn 0.15s ease-out',
                'slide-up': 'slideUp 0.25s cubic-bezier(0.16, 1, 0.3, 1)',
                'slide-down': 'slideDown 0.25s cubic-bezier(0.16, 1, 0.3, 1)',
                'scale-in': 'scaleIn 0.2s cubic-bezier(0.16, 1, 0.3, 1)',
            },
            keyframes: {
                fadeIn: {
                    '0%': { opacity: '0' },
                    '100%': { opacity: '1' },
                },
                slideUp: {
                    '0%': { opacity: '0', transform: 'translateY(10px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
                slideDown: {
                    '0%': { opacity: '0', transform: 'translateY(-10px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
                scaleIn: {
                    '0%': { opacity: '0', transform: 'scale(0.95)' },
                    '100%': { opacity: '1', transform: 'scale(1)' },
                },
            },
            transitionTimingFunction: {
                'bounce-in': 'cubic-bezier(0.16, 1, 0.3, 1)',
            },
        },
    },
    plugins: [],
};
