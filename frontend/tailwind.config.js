/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{js,ts,jsx,tsx}"
  ],
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        brand: {
          cyan: '#06B6D4',
          violet: '#6366F1',
          success: '#10B981',
          warning: '#F59E0B',
          danger: '#F43F5E',
          bgLight: '#F8FAFC',
          bgDark: '#080A10',
          surfLight: '#FFFFFF',
          surfDark: '#0E1322',
          surfDarkAlt: '#182035',
          textLight: '#0F172A',
          textDark: '#F1F5F9',
          muted: '#64748B',
        }
      },
      fontFamily: {
        sans: ['Sora', 'Battambang', 'sans-serif'],
        mono: ['JetBrains Mono', 'monospace']
      },
      boxShadow: {
        'glass': '0 8px 32px rgba(0, 0, 0, 0.24), inset 0 1px 0 rgba(255, 255, 255, 0.05)',
        'glass-lg': '0 16px 48px rgba(0, 0, 0, 0.35), inset 0 1px 0 rgba(255, 255, 255, 0.05)',
        'glow-cyan': '0 0 20px rgba(6, 182, 212, 0.35)',
        'glow-violet': '0 0 20px rgba(99, 102, 241, 0.35)',
        'card': '0 4px 12px rgba(0, 0, 0, 0.05), 0 0 1px rgba(0, 0, 0, 0.1)',
        'card-hover': '0 16px 32px rgba(0, 0, 0, 0.15), 0 0 1px rgba(0, 0, 0, 0.12)',
      },
      animation: {
        'slide-up': 'slideUp 0.35s cubic-bezier(0.16, 1, 0.3, 1)',
        'slide-right': 'slideRight 0.3s cubic-bezier(0.16, 1, 0.3, 1)',
        'scale-in': 'scaleIn 0.25s cubic-bezier(0.16, 1, 0.3, 1)',
        'fade-in': 'fadeIn 0.25s ease-out',
        'shimmer': 'shimmer 2s infinite',
        'float': 'float 6s ease-in-out infinite',
        'scanner-laser': 'laserMove 2s infinite ease-in-out',
      },
      keyframes: {
        slideUp: {
          '0%': { opacity: '0', transform: 'translateY(16px)' },
          '100%': { opacity: '1', transform: 'translateY(0)' },
        },
        slideRight: {
          '0%': { opacity: '0', transform: 'translateX(24px)' },
          '100%': { opacity: '1', transform: 'translateX(0)' },
        },
        scaleIn: {
          '0%': { opacity: '0', transform: 'scale(0.95)' },
          '100%': { opacity: '1', transform: 'scale(1)' },
        },
        fadeIn: {
          '0%': { opacity: '0' },
          '100%': { opacity: '1' },
        },
        shimmer: {
          '0%': { backgroundPosition: '-200% 0' },
          '100%': { backgroundPosition: '200% 0' },
        },
        float: {
          '0%, 100%': { transform: 'translateY(0)' },
          '50%': { transform: 'translateY(-8px)' },
        },
        laserMove: {
          '0%, 100%': { transform: 'translateY(0)' },
          '50%': { transform: 'translateY(155px)' }
        }
      },
      backdropBlur: {
        xs: '2px',
      }
    }
  },
  plugins: []
}
