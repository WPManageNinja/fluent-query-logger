<?php

namespace FluentQueryLogger\Database;

use FluentQueryLogger\Database\Migrations\LogMigrator;

require_once(ABSPATH.'wp-admin/includes/upgrade.php');
class DBMigrator
{
    public static function run($network_wide = false)
    {
        LogMigrator::migrate();
    }
}
