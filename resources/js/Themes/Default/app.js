import '../../bootstrap';
import '../../../css/Themes/Default.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';

createInertiaApp({
  resolve: (name) => {
    const pages = import.meta.glob('./Pages/**/*.vue', { eager: true });
    const page = pages[`./Pages/${name}.vue`];
    return page;
  },
  setup({ el, App, props, plugin }) {
    const vueApp = createApp({ render: () => h(App, props) }).use(plugin);

    // vueApp.config.devtools = true;
    vueApp.config.performance = true;

    vueApp.mount(el);
  },
});
