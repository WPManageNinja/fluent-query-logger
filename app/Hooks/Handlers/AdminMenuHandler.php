<?php

namespace FluentQueryLogger\App\Hooks\Handlers;

use FluentQueryLogger\App\App;
use FluentQueryLogger\App\Models\DbLog;

class AdminMenuHandler
{
    public function add()
    {
        $capability = 'manage_options';
        add_submenu_page(
            'tools.php',
            __('Fluent Query Logs', 'fluent-query-logger'),
            __('Fluent Query Logs', 'fluent-query-logger'),
            $capability,
            'fluent-query-logger',
            [$this, 'render'],
            99
        );
    }

    public function render()
    {

        $this->enqueueAssets();

        $config = App::getInstance('config');

        $name = $config->get('app.name');

        $slug = $config->get('app.slug');

        $baseUrl = apply_filters('fluent_query_logger_base_url', admin_url('tools.php?page=' . $slug . '#/'));

        $menuItems = [
            [
                'key'       => 'logs',
                'label'     => __('DB Query Logs', 'fluent-query-builder'),
                'permalink' => $baseUrl
            ],
            [
                'key'       => 'settings',
                'label'     => __('Settings', 'fluent-query-builder'),
                'permalink' => $baseUrl . 'settings'
            ]
        ];

        $app = App::getInstance();
        $assets = $app['url.assets'];

        App::make('view')->render('admin.menu', [
            'name'      => $name,
            'slug'      => $slug,
            'menuItems' => $menuItems,
            'baseUrl'   => $baseUrl,
            'logo'      => $assets . 'images/logo.svg',
        ]);
    }

    public function enqueueAssets()
    {
        $app = App::getInstance();

        $assets = $app['url.assets'];

        $slug = $app->config->get('app.slug');

        do_action($slug . '_loading_app');

        wp_enqueue_script(
            $slug . '_global_admin',
            $assets . 'admin/js/app.js',
            array('jquery'),
            '1.0',
            true
        );

        $currentUser = get_user_by('ID', get_current_user_id());

        $settings = get_option('_fql_settings');

        $expensive = 0.05;
        if (defined('QM_DB_EXPENSIVE')) {
            $expensive = QM_DB_EXPENSIVE;
        }

        wp_localize_script($slug . '_global_admin', 'fluentFrameworkAdmin', [
            'slug'         => $slug = $app->config->get('app.slug'),
            'nonce'        => wp_create_nonce($slug),
            'rest'         => $this->getRestInfo($app),
            'asset_url'    => $assets,
            'me'           => [
                'id'        => $currentUser->ID,
                'full_name' => trim($currentUser->first_name . ' ' . $currentUser->last_name),
                'email'     => $currentUser->user_email
            ],
            'log_filters'  => [
                'statuses'   => [
                    'new', 'ignored'
                ],
                'components' => DbLog::groupBy('context')->get()->pluck('context')
            ],
            'qm_installed' => defined('QM_VERSION'),
            'is_active'    => $settings && $settings['active'] == 'yes' && !empty($settings['modules']),
            'db_expensive' => apply_filters('fluent_query_expensive', $expensive)
        ]);
    }

    protected function getRestInfo($app)
    {
        $ns = $app->config->get('app.rest_namespace');
        $ver = $app->config->get('app.rest_version');

        return [
            'base_url'  => esc_url_raw(rest_url()),
            'url'       => rest_url($ns . '/' . $ver),
            'nonce'     => wp_create_nonce('wp_rest'),
            'namespace' => $ns,
            'version'   => $ver
        ];
    }
}

