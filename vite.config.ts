import { URL, fileURLToPath } from 'node:url'
import fs from 'node:fs'
import { defineConfig, loadEnv } from 'vite'
import Laravel from 'laravel-vite-plugin'
import Vue from '@vitejs/plugin-vue'
import Components from 'unplugin-vue-components/vite'
import { HeadlessUiResolver } from 'unplugin-vue-components/resolvers'
import AutoImport from 'unplugin-auto-import/vite'
import Icons from 'unplugin-icons/vite'
import IconsResolver from 'unplugin-icons/resolver'
import DefineOptions from 'unplugin-vue-define-options/vite'

export default defineConfig(({ mode }) => {
  process.env = {
    ...process.env,
    ...loadEnv(mode, process.cwd()),
  }

  return {
    plugins: [
      Laravel({
        input: 'resources/js/app.ts',
        refresh: true,
      }),
      Vue({
        template: {
          transformAssetUrls: {
            base: null,
            includeAbsolute: false,
          },
        },
      }),
      Components({
        dirs: [
          'resources/js/components',
          'resources/js/layouts',
        ],
        resolvers: [
          IconsResolver({
            prefix: false,
          }),
          HeadlessUiResolver(),
        ],
        types: [{
          from: '@inertiajs/vue3',
          names: ['Link'],
        }],
        dts: 'resources/js/shims/auto-components.d.ts',
      }),
      AutoImport({
        dirs: ['resources/js/composables'],
        imports: [
          'vue',
          '@vueuse/core',
          {
            '@inertiajs/vue3': ['router', 'useForm', 'usePage'],
          },
        ],
        dts: 'resources/js/shims/auto-imports.d.ts',
      }),
      Icons(),
      DefineOptions(),
    ],
    server: {
      host: true,
      https: process.env.VITE_DEV_SERVER_KEY && process.env.VITE_DEV_SERVER_CERT
        ? {
          key: fs.readFileSync(process.env.VITE_DEV_SERVER_KEY),
          cert: fs.readFileSync(process.env.VITE_DEV_SERVER_CERT),
        }
        : undefined,
      hmr: {
        host: 'localhost',
      },
      watch: {
        ignored: ['**/vendor/**'],
        usePolling: true,
      },
    },
    resolve: {
      alias: {
        '@': fileURLToPath(new URL('./resources/js', import.meta.url)),
      },
    },
  }
})