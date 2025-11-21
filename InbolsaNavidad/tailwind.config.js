/** @type {import('tailwind.css').Config} */
export default {
  content: ['./src/**/*.{astro,html,js,jsx,ts,tsx,md,mdx}'],
  theme: {
    extend: {
      colors: {
        brand: {
          50:'#eef6ff',100:'#d8e9ff',200:'#b1d2ff',300:'#85b8fb',400:'#5a96e6',
          500:'#3e7dd2',600:'#2f65b0',700:'#285392',800:'#234876',900:'#213e63'
        },
        'christmas-red': {
          50:'#fef2f2',100:'#fee2e2',200:'#fecaca',300:'#fca5a5',400:'#f87171',
          500:'#ef4444',600:'#dc2626',700:'#b91c1c',800:'#991b1b',900:'#7f1d1d'
        },
        'christmas-green': {
          50:'#f0fdf4',100:'#dcfce7',200:'#bbf7d0',300:'#86efac',400:'#4ade80',
          500:'#22c55e',600:'#16a34a',700:'#15803d',800:'#166534',900:'#14532d'
        }
      }
    }
  },
  plugins: [],
}
