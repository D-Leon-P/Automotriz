/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{vue,js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        // Sleek dark-mode & automotive premium palette
        brand: {
          light: '#f59e0b', // Amber/Gold for primary accents
          DEFAULT: '#d97706',
          dark: '#b45309',
        },
        slate: {
          700: '#2e364f',
          800: '#202638',
          850: '#171c2b',
          900: '#101420',
          950: '#080c14',
        }
      },
      fontFamily: {
        sans: ['Outfit', 'Inter', 'sans-serif'],
      }
    },
  },
  plugins: [],
}
