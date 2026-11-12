/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ['./index.html', './en/index.html', './assets/js/main.js'],
  safelist: ['nav-scrolled'],
  theme: {
    extend: {
      colors: {
        brand: {
          navy: '#2a2969',
          ink: '#17163b',
          gold: '#fbc85a',
          goldDeep: '#7a5900',
          cream: '#f8f5ec',
        },
      },
      fontFamily: {
        sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
        display: ['"Playfair Display"', 'Georgia', 'ui-serif', 'serif'],
      },
      boxShadow: {
        card: '0 24px 60px -32px rgba(23, 22, 59, 0.28)',
      },
    },
  },
  plugins: [],
};
