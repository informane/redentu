import './bootstrap';
import $ from "jquery";
window.$ = window.jQuery = $;

import { createApp, h } from 'vue/dist/vue.esm-bundler.js'
import { createInertiaApp } from '@inertiajs/vue3'

createInertiaApp({
    //progress: false,
  resolve: name => {
    const pages = import.meta.glob('./Pages/**/*.vue', { eager: true })
    return pages[`./Pages/${name}.vue`]
  },
  setup({ el, App, props, plugin }) {
    createApp({ render: () => h(App, props) })
      .use(plugin)
      .mount(el)
  },
})
