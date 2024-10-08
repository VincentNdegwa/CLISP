import "./bootstrap";
import "../css/app.css";
import "bootstrap-icons/font/bootstrap-icons.css";
import { createApp, h } from "vue";
import { createPinia } from "pinia";
import { createInertiaApp } from "@inertiajs/vue3";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { ZiggyVue } from "../../vendor/tightenco/ziggy";
import AlertNotification from "./Components/AlertNotification.vue";

import "vuetify/styles";
import { createVuetify } from "vuetify";
import * as components from "vuetify/components";
import * as directives from "vuetify/directives";
import PrimeVue from "primevue/config";
import Aura from "@primevue/themes/aura";
import { definePreset } from "@primevue/themes";

import "primeicons/primeicons.css";
import AuraTheme from "./Themes/AuraTheme";
import ToastService from "primevue/toastservice";

const appName = import.meta.env.VITE_APP_NAME || "Laravel";
const pinia = createPinia();

const vuetify = createVuetify({
    components,
    directives,
});
const MyPreset = definePreset(Aura, {
    AuraTheme,
});

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob("./Pages/**/*.vue")
        ),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });

        app.use(plugin)
            .use(ZiggyVue)
            .use(pinia)
            .use(vuetify)
            .use(ToastService)
            .use(PrimeVue, {
                theme: {
                    preset: MyPreset,
                    options: {
                        darkModeSelector: ".my-app-dark",
                    },
                },
            })
            .component("AlertNotification", AlertNotification)
            .mount(el);

        return app;
    },
    progress: {
        color: "#4B5563",
    },
});
