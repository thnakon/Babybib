module.exports = {
  darkMode: 'class',
  content: [
    './*.php',
    './admin/**/*.php',
    './api/**/*.php',
    './errors/**/*.php',
    './includes/**/*.php',
    './users/**/*.php',
    './assets/js/**/*.js',
  ],
  safelist: [
    'translate-x-0',
    '-translate-x-full',
    'lg:ml-64',
    'ml-0',
    'hidden',
    'block',
    'flex',
    'grid',
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Tahoma', 'Inter', 'sans-serif'],
      },
      colors: {
        vercel: {
          black: '#000000',
          white: '#ffffff',
          gray: {
            100: '#fafafa',
            200: '#eaeaea',
            300: '#999999',
            400: '#888888',
            500: '#666666',
            600: '#404040',
            700: '#262626',
            800: '#1a1a1a',
            900: '#111111',
          },
          blue: '#0070f3',
          red: '#ee0000',
          amber: '#f5a623',
          emerald: '#50e3c2',
        },
        primary: '#000000',
      },
    },
  },
  plugins: [],
};
