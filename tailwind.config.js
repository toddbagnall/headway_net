/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ['./index.html'],
  safelist: ['nav-scrolled'],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
        serif: ['Playfair Display', 'Georgia', 'ui-serif', 'serif'],
      },
    },
  },
  plugins: [],
};
