/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        // Primary brand colors
        primary: {
          50: '#eff6ff',
          100: '#dbeafe',
          200: '#bfdbfe',
          300: '#93c5fd',
          400: '#60a5fa',
          500: '#3b82f6',  // Main primary
          600: '#2563eb',
          700: '#1d4ed8',
          800: '#1e40af',
          900: '#1e3a8a',
          950: '#172554'
        },
        // Secondary colors
        secondary: {
          50: '#f0f9ff',
          100: '#e0f2fe',
          200: '#bae6fd',
          300: '#7dd3fc',
          400: '#38bdf8',
          500: '#0ea5e9',  // Main secondary
          600: '#0284c7',
          700: '#0369a1',
          800: '#075985',
          900: '#0c4a6e',
          950: '#082f49'
        },
        // Success/Health colors
        success: {
          50: '#f0fdf4',
          100: '#dcfce7',
          200: '#bbf7d0',
          300: '#86efac',
          400: '#4ade80',
          500: '#22c55e',  // Main success
          600: '#16a34a',
          700: '#15803d',
          800: '#166534',
          900: '#14532d',
          950: '#052e16'
        },
        // Warning colors
        warning: {
          50: '#fffbeb',
          100: '#fef3c7',
          200: '#fde68a',
          300: '#fcd34d',
          400: '#fbbf24',
          500: '#f59e0b',  // Main warning
          600: '#d97706',
          700: '#b45309',
          800: '#92400e',
          900: '#78350f',
          950: '#451a03'
        },
        // Danger/Critical colors
        danger: {
          50: '#fef2f2',
          100: '#fee2e2',
          200: '#fecaca',
          300: '#fca5a5',
          400: '#f87171',
          500: '#ef4444',  // Main danger
          600: '#dc2626',
          700: '#b91c1c',
          800: '#991b1b',
          900: '#7f1d1d',
          950: '#450a0a'
        },
        // Info colors
        info: {
          50: '#f0f9ff',
          100: '#e0f2fe',
          200: '#bae6fd',
          300: '#7dd3fc',
          400: '#38bdf8',
          500: '#0ea5e9',  // Main info
          600: '#0284c7',
          700: '#0369a1',
          800: '#075985',
          900: '#0c4a6e',
          950: '#082f49'
        },
        // Medical specific colors
        medical: {
          green: '#10b981',
          blue: '#3b82f6',
          red: '#ef4444',
          yellow: '#f59e0b',
          purple: '#8b5cf6',
          teal: '#14b8a6'
        },
        // Neutral grays
        neutral: {
          50: '#fafafa',
          100: '#f5f5f5',
          200: '#e5e5e5',
          300: '#d4d4d4',
          400: '#a3a3a3',
          500: '#737373',
          600: '#525252',
          700: '#404040',
          800: '#262626',
          900: '#171717',
          950: '#0a0a0a'
        }
      },
      fontFamily: {
        sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
        mono: ['JetBrains Mono', 'ui-monospace', 'monospace'],
      },
      fontSize: {
        'xs': ['0.75rem', { lineHeight: '1rem' }],
        'sm': ['0.875rem', { lineHeight: '1.25rem' }],
        'base': ['1rem', { lineHeight: '1.5rem' }],
        'lg': ['1.125rem', { lineHeight: '1.75rem' }],
        'xl': ['1.25rem', { lineHeight: '1.75rem' }],
        '2xl': ['1.5rem', { lineHeight: '2rem' }],
        '3xl': ['1.875rem', { lineHeight: '2.25rem' }],
        '4xl': ['2.25rem', { lineHeight: '2.5rem' }],
        '5xl': ['3rem', { lineHeight: '1' }],
        '6xl': ['3.75rem', { lineHeight: '1' }],
      },
      spacing: {
        '18': '4.5rem',
        '88': '22rem',
        '112': '28rem',
        '128': '32rem',
      },
      borderRadius: {
        '4xl': '2rem',
      },
      boxShadow: {
        'card': '0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06)',
        'card-hover': '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)',
        'medical': '0 10px 15px -3px rgba(59, 130, 246, 0.1), 0 4px 6px -2px rgba(59, 130, 246, 0.05)',
      },
      animation: {
        'fade-in': 'fadeIn 0.5s ease-in-out',
        'slide-up': 'slideUp 0.3s ease-out',
        'pulse-slow': 'pulse 3s infinite',
      },
    },
  },
  plugins: [
    require('@tailwindcss/forms'),
    function({ addUtilities, addComponents, theme }) {
      // Custom component classes
      addComponents({
        // Button variants
        '.btn': {
          '@apply inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors duration-200': {},
        },
        '.btn-primary': {
          '@apply btn bg-primary-600 text-white hover:bg-primary-700 focus:ring-primary-500': {},
        },
        '.btn-secondary': {
          '@apply btn bg-secondary-600 text-white hover:bg-secondary-700 focus:ring-secondary-500': {},
        },
        '.btn-success': {
          '@apply btn bg-success-600 text-white hover:bg-success-700 focus:ring-success-500': {},
        },
        '.btn-warning': {
          '@apply btn bg-warning-600 text-white hover:bg-warning-700 focus:ring-warning-500': {},
        },
        '.btn-danger': {
          '@apply btn bg-danger-600 text-white hover:bg-danger-700 focus:ring-danger-500': {},
        },
        '.btn-outline': {
          '@apply btn border-primary-600 text-primary-600 hover:bg-primary-50 focus:ring-primary-500': {},
        },
        '.btn-ghost': {
          '@apply btn text-neutral-600 hover:bg-neutral-100 focus:ring-neutral-500': {},
        },
        
        // Card variants
        '.card': {
          '@apply bg-white rounded-lg shadow-card border border-neutral-200': {},
        },
        '.card-hover': {
          '@apply card hover:shadow-card-hover transition-shadow duration-200': {},
        },
        '.card-medical': {
          '@apply card shadow-medical border-primary-200': {},
        },
        
        // Status badges
        '.badge': {
          '@apply inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium': {},
        },
        '.badge-success': {
          '@apply badge bg-success-100 text-success-800': {},
        },
        '.badge-warning': {
          '@apply badge bg-warning-100 text-warning-800': {},
        },
        '.badge-danger': {
          '@apply badge bg-danger-100 text-danger-800': {},
        },
        '.badge-info': {
          '@apply badge bg-info-100 text-info-800': {},
        },
        '.badge-neutral': {
          '@apply badge bg-neutral-100 text-neutral-800': {},
        },
        
        // Form elements
        '.form-input': {
          '@apply block w-full rounded-md border-neutral-300 shadow-sm focus:border-primary-500 focus:ring-primary-500': {},
        },
        '.form-select': {
          '@apply block w-full rounded-md border-neutral-300 shadow-sm focus:border-primary-500 focus:ring-primary-500': {},
        },
        '.form-textarea': {
          '@apply block w-full rounded-md border-neutral-300 shadow-sm focus:border-primary-500 focus:ring-primary-500': {},
        },
        
        // Navigation
        '.nav-link': {
          '@apply text-neutral-600 hover:text-primary-600 px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200': {},
        },
        '.nav-link-active': {
          '@apply nav-link text-primary-600 bg-primary-50': {},
        },
        
        // Medical status indicators
        '.status-low-stock': {
          '@apply badge-warning': {},
        },
        '.status-expired': {
          '@apply badge-danger': {},
        },
        '.status-healthy': {
          '@apply badge-success': {},
        },
        '.status-critical': {
          '@apply badge-danger': {},
        },
        
        // Page headers
        '.page-header': {
          '@apply mb-8': {},
        },
        '.page-title': {
          '@apply text-3xl font-bold text-neutral-900': {},
        },
        '.page-subtitle': {
          '@apply mt-2 text-neutral-600': {},
        },
        
        // Grid layouts
        '.stats-grid': {
          '@apply grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6': {},
        },
        '.content-grid': {
          '@apply grid grid-cols-1 lg:grid-cols-2 gap-8': {},
        },
      });

      // Custom utility classes
      addUtilities({
        '.bg-primary': { backgroundColor: theme('colors.primary.600') },
        '.bg-secondary': { backgroundColor: theme('colors.secondary.600') },
        '.bg-success': { backgroundColor: theme('colors.success.600') },
        '.bg-warning': { backgroundColor: theme('colors.warning.600') },
        '.bg-danger': { backgroundColor: theme('colors.danger.600') },
        '.bg-info': { backgroundColor: theme('colors.info.600') },
        
        '.text-primary': { color: theme('colors.primary.600') },
        '.text-secondary': { color: theme('colors.secondary.600') },
        '.text-success': { color: theme('colors.success.600') },
        '.text-warning': { color: theme('colors.warning.600') },
        '.text-danger': { color: theme('colors.danger.600') },
        '.text-info': { color: theme('colors.info.600') },
        
        '.border-primary': { borderColor: theme('colors.primary.600') },
        '.border-secondary': { borderColor: theme('colors.secondary.600') },
        '.border-success': { borderColor: theme('colors.success.600') },
        '.border-warning': { borderColor: theme('colors.warning.600') },
        '.border-danger': { borderColor: theme('colors.danger.600') },
        '.border-info': { borderColor: theme('colors.info.600') },
      });
    }
  ],
}

