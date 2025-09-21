import "../../bootstrap";
import "../../../css/Themes/Twilight.css";

import { createApp, h } from "vue";
import { createInertiaApp } from "@inertiajs/vue3";

createInertiaApp({
    resolve: (name) => {
        // Prefer Twilight pages; fall back to Default if not present
        const twilightPages = import.meta.glob("./Pages/**/*.vue", { eager: true });
        const defaultPages = import.meta.glob("../Default/Pages/**/*.vue", { eager: true });

        const twKey = `./Pages/${name}.vue`;
        const defKey = `../Default/Pages/${name}.vue`;
        return twilightPages[twKey] || defaultPages[defKey];
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
});
