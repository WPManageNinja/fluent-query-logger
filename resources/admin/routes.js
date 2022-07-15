import Logs from './Components/Logs.vue';
import Settings from './Components/Setttings.vue';

export default [
    {
        path: '/',
        name: 'logs',
        component: Logs,
        meta: {
            active: 'logs'
        }
    },
    {
        path: '/settings',
        name: 'settings',
        component: Settings,
        meta: {
            active: 'settings'
        }
    }
];

