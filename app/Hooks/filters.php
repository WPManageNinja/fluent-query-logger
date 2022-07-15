<?php

/**
 * All registered filter's handlers should be in app\Hooks\Handlers,
 * addFilter is similar to add_filter and addCustomFlter is just a
 * wrapper over add_filter which will add a prefix to the hook name
 * using the plugin slug to make it unique in all wordpress plugins,
 * ex: $app->addCustomFilter('foo', ['FooHandler', 'handleFoo']) is
 * equivalent to add_filter('slug-foo', ['FooHandler', 'handleFoo']).
 */

/**
 * $app
 * @var $app FluentQueryLogger\Framework\Foundation\Application
 */

add_filter( 'plugin_action_links_fluent-query-logger/fluent-query-logger.php', function ($actions) {
    $baseUrl = apply_filters('fluent_query_logger_base_url', admin_url('tools.php?page=fluent-query-logger#/'));
    return array_merge( array(
        'settings' => '<a href="'.esc_url($baseUrl).'">' . esc_html__( 'View DB Logs', 'fluent-query-logger' ) . '</a>'
    ), $actions );
} );
