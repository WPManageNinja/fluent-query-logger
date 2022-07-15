<?php

namespace FluentQueryLogger\App\Hooks\Handlers;

use FluentQueryLogger\Framework\Foundation\Application;

class DeactivationHandler
{
    protected $app = null;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function handle()
    {
        // Remove All Data on deativation
        global $wpdb;
        $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}fql_logs");
    }
}
