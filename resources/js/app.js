import './bootstrap';
import '../css/app.css';
import 'bootstrap-icons/font/bootstrap-icons.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import AlertNotification from './Components/AlertNotification.vue';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });

        app
            .use(plugin)
            .use(ZiggyVue)
            .component('AlertNotification', AlertNotification)
            .mount(el);

        return app;
    },
    progress: {
        color: '#4B5563',
    },
});
