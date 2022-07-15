<?php

namespace FluentQueryLogger\App\Hooks\Handlers;

use FluentQueryLogger\Framework\Foundation\Application;
use FluentQueryLogger\Database\DBMigrator;
use FluentQueryLogger\Database\DBSeeder;

class ActivationHandler
{
    protected $app = null;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }
    
    public function handle($network_wide = false)
    {
        DBMigrator::run($network_wide);
    }
}
