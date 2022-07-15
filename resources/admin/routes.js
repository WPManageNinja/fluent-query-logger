import Logs from './Components/Logs.vue';
import Settings from './Components/Setttings.vue';

export var routes = [
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
