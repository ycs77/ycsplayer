import { createApp, h } from 'vue'
import { createInertiaApp, Link } from '@inertiajs/vue3'
import InertiaTitle from 'inertia-title/vue3'
import { vfmPlugin as VueFinalModal } from 'vue-final-modal'
import Toast from 'vue-toastification'
import { Notification } from '@/modules/notification'
import Layout from './layouts/Layout.vue'
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
    let page = await pages[`./pages/${name}.vue`]() as any
    page.default.layout = page.default.layout
      ? Array.isArray(page.default.layout)
        ? page.default.layout
        : [page.default.layout]
      : []
    if (!page.default.layout.includes(Layout)) {
      page.default.layout.unshift(Layout)
    }
    return page
  },
  setup({ el, App, props, plugin }) {
    createApp({ render: () => h(App, props) })
      .use(plugin)
      .use(InertiaTitle)
      .use(VueFinalModal())
      .use(Toast)
      .use(Notification)
      .component('Link', Link)
      .mount(el)
  },
})
