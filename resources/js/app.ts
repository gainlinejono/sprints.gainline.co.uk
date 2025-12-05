import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { createPinia } from 'pinia';
import VueApexCharts from 'vue3-apexcharts';

createInertiaApp({
    title: (title) => title ? `${title} - Sprint Planner` : 'Sprint Planner',
    resolve: (name) => {
        const pages = import.meta.glob('./Pages/**/*.vue', { eager: true }) as Record<string, any>;
        const page = pages[`./Pages/${name}.vue`];
        if (!page) {
            throw new Error(`Page not found: ${name}`);
        }
        return page.default || page;
    },
    setup({ el, App, props, plugin }) {
        const pinia = createPinia();

        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(pinia)
            .use(VueApexCharts)
            .mount(el);
    },
    progress: {
        color: '#6366f1',
        showSpinner: true,
    },
});
