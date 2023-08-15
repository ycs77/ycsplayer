import type { Config } from 'tailwindcss'
import { tailwindcssOriginSafelist } from '@headlessui-float/vue'
import Forms from '@tailwindcss/forms'

export default {
  content: [
    './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    './storage/framework/views/*.php',
    './resources/views/**/*.blade.php',
    './resources/js/**/*.vue',
  ],
  safelist: [...tailwindcssOriginSafelist],
  theme: {
    screens: {
      sm: '640px',
      md: '768px',
      lg: '1024px',
      xl: '1280px',
      // '2xl': '1536px',
    },
    extend: {},
  },
  plugins: [
    Forms({
      strategy: 'class',
    }),
  ],
} satisfies Config

