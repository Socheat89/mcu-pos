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
          cyan: '#308AC6', // Brand Blue (from logo)
          violet: '#1F6896', // Dark Brand Blue
          success: '#2c8a3c',
          warning: '#ec9a29',
          danger: '#e05038',
          bgLight: '#f3f4f6',
          bgDark: '#080a10', // Dark background
          surfLight: '#FFFFFF',
          surfDark: '#0e1322', // Dark card surface
          surfDarkAlt: '#161b2e', // Dark elevated surface
          textLight: '#1f2937',
          textDark: '#f8fafc', // Light text for dark background
          muted: '#4b5563',
        }
      },
      fontFamily: {
        sans: ['Inter', 'Sora', 'Battambang', 'sans-serif'],
        mono: ['JetBrains Mono', 'monospace']
      },
      boxShadow: {
        'glass': '0 1px 3px rgba(0, 0, 0, 0.05)',
        'glass-lg': '0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.02)',
        'glow-cyan': '0 0 15px rgba(48, 138, 198, 0.2)',
        'glow-violet': '0 0 15px rgba(31, 104, 150, 0.2)',
        'card': '0 1px 3px rgba(0, 0, 0, 0.05)',
        'card-hover': '0 8px 24px rgba(0, 0, 0, 0.08)',
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
