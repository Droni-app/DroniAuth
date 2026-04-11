import '../css/app.css';
import './bootstrap';
import '@dronico/droni-kit/dist/droni-kit.css';

import * as DroniKitComponents from '@dronico/droni-kit';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

const appName = import.meta.env.VITE_APP_NAME || 'DroniAuth';

// Plugin que registra globalmente todos los componentes de droni-kit
const DroniKit = {
    install(app) {
        Object.entries(DroniKitComponents).forEach(([name, component]) => {
            app.component(name, component);
        });
    },
};

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(DroniKit)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
