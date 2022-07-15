<?php

namespace FluentQueryLogger\App\Http\Controllers;

use FluentQueryLogger\App\Models\DbLog;
use FluentQueryLogger\Framework\Request\Request;

class LogsController extends Controller
{
    public function get(Request $request)
    {

        $logs = DbLog::orderBy($request->get('sortBy', 'updated_at'), $request->get('sortType', 'DESC'));

        $filters = $request->get('filters', []);

        foreach ($filters as $filterKey => $filter) {
            if ($filter) {
                $logs->where($filterKey, $filter);
            }
        }

        if ($request->get('show_ignores') == 'yes') {
            $logs->where('status', 'ignored');
        } else {
            $logs->where('status', '!=', 'ignored');
        }

        $logs = $logs->paginate();

        return [
            'logs' => $logs
        ];
    }

    public function update(Request $request, $id)
    {
        $log = DbLog::findOrFail($id);
        $data = $request->all();
        $data['updated_at'] = $log->updated_at;

        $log->fill($data)->save();

        return [
            'log' => $log
        ];
    }

    public function getSettings(Request $request)
    {
        $settings = get_option('_fql_settings');
        $activated = !!$settings;

        if (!$settings) {
            $settings = [
                'active'  => 'yes',
                'modules' => []
            ];
        } else if (!is_array($settings['modules'])) {
            $settings['modules'] = [];
        }


        $originalPlugins = $temps = $this->getFormattedPlugins();
        $temps[] = 'core';
        $settings['modules'] = array_intersect($settings['modules'], $temps);

        return [
            'activated' => $activated,
            'settings'  => $settings,
            'plugins'   => $originalPlugins
        ];
    }

    public function saveSettings(Request $request)
    {
        $settings = $request->get('settings');

        $originalPlugins = $this->getFormattedPlugins();
        $originalPlugins[] = 'core';
        $settings['modules'] = array_intersect($settings['modules'], $originalPlugins);

        $settings['active'] = sanitize_text_field($settings['active']);

        update_option('_fql_settings', [
            'active'  => $settings['active'],
            'modules' => $settings['modules']
        ]);

        return [
            'message'  => __('Settings has been updated successfully', 'fluent-query-logger'),
            'settings' => $settings
        ];
    }

    private function getFormattedPlugins()
    {
        $plugins = get_option('active_plugins');
        $formattedPlugins = [];
        foreach ($plugins as $plugin) {
            $arr = explode('/', $plugin);
            $formattedPlugins[] = $arr[0];
        }

        return $formattedPlugins;
    }

    public function truncateLogs(Request $request)
    {
        global $wpdb;
        $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}fql_logs");

        return [
            'message' => __('All Logs has been truncated', 'fluent-query-logger')
        ];
    }

    public function installDependencies(Request $request)
    {
        if (!current_user_can('install_plugins')) {
            return $this->sendError([
                'message' => __('Sorry, You do not have permissions to install plugins. Please install manually', 'fluent-query-logger')
            ], 423);
        }

        $this->installQueryMonitor();

        if (!defined('QM_VERSION')) {
            return $this->sendError([
                'message' => __('Sorry, We could not install Query Monitor. Please install manually', 'fluent-query-logger')
            ], 423);
        }

        return [
            'message' => __('Query Monitor has been installed successfully', 'fluent-query-logger')
        ];
    }

    private function installQueryMonitor()
    {
        $plugin_id = 'query-monitor';
        $plugin = [
            'name'      => 'Query Monitor',
            'repo-slug' => 'query-monitor',
            'file'      => 'query-monitor.php',
        ];
        $this->backgroundInstaller($plugin, $plugin_id);
    }

    private function backgroundInstaller($plugin_to_install, $plugin_id)
    {
        if (!empty($plugin_to_install['repo-slug'])) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
            require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
            require_once ABSPATH . 'wp-admin/includes/plugin.php';

            WP_Filesystem();

            $skin = new \Automatic_Upgrader_Skin();
            $upgrader = new \WP_Upgrader($skin);
            $installed_plugins = array_reduce(array_keys(\get_plugins()), array($this, 'associate_plugin_file'), array());
            $plugin_slug = $plugin_to_install['repo-slug'];
            $plugin_file = isset($plugin_to_install['file']) ? $plugin_to_install['file'] : $plugin_slug . '.php';
            $installed = false;
            $activate = false;

            // See if the plugin is installed already.
            if (isset($installed_plugins[$plugin_file])) {
                $installed = true;
                $activate = !is_plugin_active($installed_plugins[$plugin_file]);
            }

            // Install this thing!
            if (!$installed) {
                // Suppress feedback.
                ob_start();

                try {
                    $plugin_information = plugins_api(
                        'plugin_information',
                        array(
                            'slug'   => $plugin_slug,
                            'fields' => array(
                                'short_description' => false,
                                'sections'          => false,
                                'requires'          => false,
                                'rating'            => false,
                                'ratings'           => false,
                                'downloaded'        => false,
                                'last_updated'      => false,
                                'added'             => false,
                                'tags'              => false,
                                'homepage'          => false,
                                'donate_link'       => false,
                                'author_profile'    => false,
                                'author'            => false,
                            ),
                        )
                    );

                    if (is_wp_error($plugin_information)) {
                        throw new \Exception($plugin_information->get_error_message());
                    }

                    $package = $plugin_information->download_link;
                    $download = $upgrader->download_package($package);

                    if (is_wp_error($download)) {
                        throw new \Exception($download->get_error_message());
                    }

                    $working_dir = $upgrader->unpack_package($download, true);

                    if (is_wp_error($working_dir)) {
                        throw new \Exception($working_dir->get_error_message());
                    }

                    $result = $upgrader->install_package(
                        array(
                            'source'                      => $working_dir,
                            'destination'                 => WP_PLUGIN_DIR,
                            'clear_destination'           => false,
                            'abort_if_destination_exists' => false,
                            'clear_working'               => true,
                            'hook_extra'                  => array(
                                'type'   => 'plugin',
                                'action' => 'install',
                            ),
                        )
                    );

                    if (is_wp_error($result)) {
                        throw new \Exception($result->get_error_message());
                    }

                    $activate = true;

                } catch (\Exception $e) {
                }

                // Discard feedback.
                ob_end_clean();
            }

            wp_clean_plugins_cache();

            // Activate this thing.
            if ($activate) {
                try {
                    $result = activate_plugin($installed ? $installed_plugins[$plugin_file] : $plugin_slug . '/' . $plugin_file);

                    if (is_wp_error($result)) {
                        throw new \Exception($result->get_error_message());
                    }
                } catch (\Exception $e) {
                }
            }
        }
    }

    private function associate_plugin_file($plugins, $key)
    {
        $path = explode('/', $key);
        $filename = end($path);
        $plugins[$filename] = $key;
        return $plugins;
    }

}
