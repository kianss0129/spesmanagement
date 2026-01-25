// resources/js/app.js
import './bootstrap'
import '../css/app.css'

import { createApp, h } from 'vue'
import { createInertiaApp, Link, Head } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { InertiaProgress } from '@inertiajs/progress'

// Initialize progress bar
InertiaProgress.init()

const pages = import.meta.glob('./Pages/**/*.vue')

const resolvePageComponentCustom = (name, pages) => {
  const path = `./Pages/${name}.vue`
  const page = pages[path]
  if (page) {
    return page().then(module => module.default || module)
  }
  throw new Error(`Page not found: ${name}`)
}

createInertiaApp({
  // Correct page resolution
  resolve: name => resolvePageComponentCustom(name, pages),

  setup({ el, App, props, plugin }) {
    const app = createApp({ render: () => h(App, props) })

    app.use(plugin)

    // Register Inertia components globally
    app.component('InertiaLink', Link)
    app.component('InertiaHead', Head)

    app.mount(el)
  },

  title: title => title ? `${title} - SPES System` : 'SPES System',
})
