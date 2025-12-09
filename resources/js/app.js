// resources/js/app.js

import "./bootstrap";
import "../css/app.css";

import { createApp, h } from "vue";
import { createInertiaApp } from "@inertiajs/vue3";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { ZiggyVue } from "ziggy-js";

import VCalendar from "v-calendar";
import "v-calendar/style.css";
import { registerSW } from "virtual:pwa-register";

registerSW({ immediate: true });

const appName = import.meta.env.VITE_APP_NAME || "OmoideAlbum";

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob("./Pages/**/*.vue")
        ),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(VCalendar, {})
            .mount(el);
    },
    progress: {
        color: "#4B5563",
    },
});
