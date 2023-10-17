import type { Config } from 'tailwindcss'
import baseConfig from './tailwind.config'

export default {
  content: [
    './resources/views/home.blade.php',
  ],
  theme: baseConfig.theme,
  plugins: baseConfig.plugins,
} satisfies Config
