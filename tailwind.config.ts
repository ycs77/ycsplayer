import type { Config } from 'tailwindcss'
import defaultTheme from 'tailwindcss/defaultTheme'
import Forms from '@tailwindcss/forms'

export default {
  content: [
    './resources/views/app.blade.php',
    './resources/js/**/*.vue',
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
        sans: ['"jf open 粉圓"', ...defaultTheme.fontFamily.sans],
      },
    },
  },
  plugins: [
    Forms({
      strategy: 'class',
    }),
  ],
} satisfies Config

