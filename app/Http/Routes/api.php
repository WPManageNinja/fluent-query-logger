<?php

/**
 * @var $router FluentQueryLogger\App\Http\Router
 */

$router->prefix('logs')->withPolicy('AdminPolicy')->group(function ($router) {
    $router->get('/', 'LogsController@get');
    $router->put('/{id}', 'LogsController@update')->int('id');

    $router->get('/settings', 'LogsController@getSettings');
    $router->post('/settings', 'LogsController@saveSettings');

    $router->post('/truncate', 'LogsController@truncateLogs');
    $router->post('/install-dependencies', 'LogsController@installDependencies');
});

