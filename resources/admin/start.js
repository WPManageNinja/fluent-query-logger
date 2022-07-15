import routes from './routes';
import { createWebHashHistory, createRouter } from 'vue-router'
import FluentFramework from './Bits/FluentFramework';
import GlobalNotice from './Components/GlobalNotice';

const router = createRouter({
    history: createWebHashHistory(),
    routes
});

const framework = new FluentFramework();

framework.app.config.globalProperties.appVars = window.fluentFrameworkAdmin;

framework.app.component('global-notice', GlobalNotice);

window.fluentFrameworkApp = framework.app.use(router).mount(
    '#' + window.fluentFrameworkAdmin.slug + '-app'
);

router.afterEach((to, from) => {
    jQuery('.fframe_menu_item').removeClass('active');
    let active = to.meta.active;
    if(active) {
        jQuery('.fframe_main-menu-items').find('li[data-key='+active+']').addClass('active');
    }
});
