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
        primary: '#8B5CF6',
      },
    },
  },
  plugins: [
    require('daisyui'),
  ],
  daisyui: {
    themes: [
      {
        babybibLight: {
          "primary": "#8B5CF6",
          "secondary": "#D946EF",
          "accent": "#10B981",
          "neutral": "#1F2937",
          "base-100": "#FFFFFF",
          "base-200": "#F9FAFB",
          "base-300": "#F3F4F6",
          "info": "#3B82F6",
          "success": "#10B981",
          "warning": "#F59E0B",
          "error": "#EF4444",
        },
        babybibDark: {
          "primary": "#A78BFA",
          "secondary": "#F472B6",
          "accent": "#34D399",
          "neutral": "#374151",
          "base-100": "#0F0F0F",
          "base-200": "#1A1A1A",
          "base-300": "#262626",
          "info": "#60A5FA",
          "success": "#34D399",
          "warning": "#FBBF24",
          "error": "#F87171",
        }
      }
    ],
    darkTheme: "babybibDark",
    base: true,
    utils: true,
    logs: false,
    themeRoot: ":root",
  },
};
