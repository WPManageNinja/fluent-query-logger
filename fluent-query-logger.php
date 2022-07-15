<?php defined('ABSPATH') or die;

/*
Plugin Name: Fluent Query Logger
Description: Query Logger Add-On for Query Monitor Plugin
Version: 1.0
Author: techjewel
Author URI: https://jewel.im
Plugin URI: https://github.com/WPManageNinja/fluent-query-logger
License: GPLv2 or later
Text Domain: fluent-query-logger
Domain Path: /language
*/

require __DIR__.'/vendor/autoload.php';

call_user_func(function($bootstrap) {
    $bootstrap(__FILE__);
}, require(__DIR__.'/boot/app.php'));
