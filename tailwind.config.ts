import type { Config } from 'tailwindcss'
import { fontFamily } from 'tailwindcss/defaultTheme'
import Forms from '@tailwindcss/forms'

export default {
  content: [
    './resources/views/app.blade.php',
    './resources/js/components/**/*.vue',
    './resources/js/layouts/**/*.vue',
    './resources/js/pages/**/*.vue',
  ],
  theme: {
    screens: {
      'sm': '640px',
      'md': '768px',
      'lg': '1024px',
      'xl': '1280px',
      '2xl': '1536px',
    },
    extend: {
      fontFamily: {
        sans: ['"jf open 粉圓"', ...fontFamily.sans],
      },
    },
  },
  plugins: [
    Forms({
      strategy: 'class',
    }),
  ],
} satisfies Config
