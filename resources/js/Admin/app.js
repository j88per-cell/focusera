import "../bootstrapp";
import "../../css/admin.css";

import { createApp, h } from "vue";
import { createInertiaApp } from "@inertiajs/vue3";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";

const appName = import.meta.env.VITE_APP_NAME || "Laravel";

createInertiaApp({
    resolve: (name) => {
        console.log("[Inertia Admin Resolver] Trying to resolve page:", name);

        const pages = import.meta.glob("./Pages/**/*.vue", { eager: true });
        const match = pages[`./Pages/${name}.vue`];

        if (!match) {
            console.error("[Inertia Admin Resolver] Could not resolve:", name);
            console.log("[Inertia Admin Resolver] Available pages:", Object.keys(pages));
        }

        return match;
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
});
