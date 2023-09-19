import { createApp, h } from 'vue'
import { Link, createInertiaApp } from '@inertiajs/vue3'
import InertiaTitle from 'inertia-title/vue3'
import { createVfm } from 'vue-final-modal'
import Toast from 'vue-toastification'
import Layout from './layouts/Layout.vue'
import { Notification } from '@/plugins/notification'
import 'vue-final-modal/style.css'
import '../css/app.css'

declare global {
  interface Window {
    HELP_IMPROVE_VIDEOJS: boolean
  }
}

window.HELP_IMPROVE_VIDEOJS = false

createInertiaApp({
  resolve: async name => {
    const pages = import.meta.glob('./pages/**/*.vue')
    const page = await pages[`./pages/${name}.vue`]() as any
    if (page.default.layout !== false) {
      page.default.layout = page.default.layout
        ? Array.isArray(page.default.layout)
          ? page.default.layout
          : [page.default.layout]
        : []
      if (!page.default.layout.includes(Layout)) {
        page.default.layout.unshift(Layout)
      }
    }
    return page
  },
  setup({ el, App, props, plugin }) {
    const VueFinalModal = createVfm()

    createApp({ render: () => h(App, props) })
      .use(plugin)
      .use(InertiaTitle)
      .use(VueFinalModal)
      .use(Toast)
      .use(Notification)
      .component('Link', Link)
      .mount(el)
  },
})
