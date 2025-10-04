import '../../bootstrap';
import '../../../css/Themes/Cinema.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';

createInertiaApp({
  resolve: (name) => {
    const cinemaPages = import.meta.glob('./Pages/**/*.vue', { eager: true });
    const twilightPages = import.meta.glob('../Twilight/Pages/**/*.vue', { eager: true });
    const defaultPages = import.meta.glob('../Default/Pages/**/*.vue', { eager: true });

    const cinemaKey = `./Pages/${name}.vue`;
    const twilightKey = `../Twilight/Pages/${name}.vue`;
    const defaultKey = `../Default/Pages/${name}.vue`;

    return cinemaPages[cinemaKey] || twilightPages[twilightKey] || defaultPages[defaultKey];
  },
  setup({ el, App, props, plugin }) {
    const vueApp = createApp({ render: () => h(App, props) }).use(plugin);

    vueApp.config.performance = true;

    vueApp.mount(el);
  },
});
