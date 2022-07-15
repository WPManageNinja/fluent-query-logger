<?php

use FluentQueryLogger\Framework\Foundation\Application;
use FluentQueryLogger\App\Hooks\Handlers\ActivationHandler;
use FluentQueryLogger\App\Hooks\Handlers\DeactivationHandler;

return function($file) {

    $app = new Application($file);

    register_activation_hook($file, function() use ($app) {
        ($app->make(ActivationHandler::class))->handle();
    });

    register_deactivation_hook($file, function() use ($app) {
        ($app->make(DeactivationHandler::class))->handle();
    });

    add_action('plugins_loaded', function() use ($app) {
        do_action('fluent-query-logger_loaded', $app);
    });
};
