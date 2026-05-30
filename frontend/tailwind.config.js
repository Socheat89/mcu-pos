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
          primary: '#2563EB',
          secondary: '#7C3AED',
          success: '#10B981',
          warning: '#F59E0B',
          danger: '#EF4444',
          bgLight: '#F8FAFC',
          bgDark: '#090D16',
          surfLight: '#FFFFFF',
          surfDark: 'rgba(17, 24, 39, 0.75)',
        }
      },
      fontFamily: {
        sans: ['Outfit', 'Inter', 'Battambang', 'sans-serif']
      },
      boxShadow: {
        'premium-light': '0 10px 30px -10px rgba(15, 23, 42, 0.04), 0 1px 3px rgba(15, 23, 42, 0.02)',
        'premium-dark': '0 25px 50px -12px rgba(0, 0, 0, 0.5), inset 0 1px 1px rgba(255, 255, 255, 0.05)',
      }
    }
  },
  plugins: []
}
