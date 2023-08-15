import { createApp, h } from 'vue'
import { createInertiaApp, Link } from '@inertiajs/vue3'
import InertiaTitle from 'inertia-title/vue3'
import Layout from '@/layouts/Layout.vue'
import '@/styles/index.css'

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
      .component('Link', Link)
      .mount(el)
  },
})
