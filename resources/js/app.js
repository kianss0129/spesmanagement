import './bootstrap'
import '../css/app.css'

import { createApp, h } from 'vue'
import { createInertiaApp, Link, Head } from '@inertiajs/vue3'
import { InertiaProgress } from '@inertiajs/progress'
import { ZiggyVue } from 'ziggy-js' // JS helper for route()

InertiaProgress.init()

const pages = import.meta.glob('./Pages/**/*.vue')

createInertiaApp({
  resolve: name => {
    const path = `./Pages/${name}.vue`
    const page = pages[path]
    if (page) return page().then(module => module.default || module)
    throw new Error(`Page not found: ${name}`)
  },

  setup({ el, App, props, plugin }) {
    const app = createApp({ render: () => h(App, props) })

    app.use(plugin)
    app.use(ZiggyVue, window.Ziggy) // ✅ Use global Ziggy object from Blade

    app.component('InertiaLink', Link)
    app.component('InertiaHead', Head)

    app.mount(el)
  },

  title: title => title ? `${title} - SPES System` : 'SPES System',
})
