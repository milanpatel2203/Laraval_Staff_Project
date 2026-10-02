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
        charcoal: {
          DEFAULT: '#2D2D2D',
          hover: '#1a1a1a',
          light: '#3D3D3D',
        },
        lightgray: {
          DEFAULT: '#F5F5F5',
          dark: '#E0E0E0',
        }
      },
      fontFamily: {
        sans: ['Inter', 'sans-serif'],
      },
    },
  },
  plugins: [],
}
