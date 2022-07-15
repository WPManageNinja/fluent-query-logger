<?php

namespace FluentQueryLogger\Database\Migrations;

class LogMigrator
{
    static $tableName = 'fql_logs';

    public static function migrate()
    {
        global $wpdb;

        $charsetCollate = $wpdb->get_charset_collate();

        $table = $wpdb->prefix . static::$tableName;

        $indexPrefix = $wpdb->prefix . 'fql_';

        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
            $sql = "CREATE TABLE $table (
                `id` BIGINT(20) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
                `hash` VARCHAR(100) NOT NULL,
                `sql` LONGTEXT NULL,
                `trace` LONGTEXT NULL,
                `caller` TEXT NULL,
                `last_url` TEXT NULL,
                `last_data` LONGTEXT NULL,
                `ltime` DOUBLE (10, 8) NULL,
                `type` VARCHAR (192) NULL,
                `status` VARCHAR (100) DEFAULT 'new',
                `result` INT (11) DEFAULT 0,
                `counter` INT (11) DEFAULT 1,
                `total_counter` INT (11) DEFAULT 1,
                `context_type` VARCHAR (192) NULL,
                `context` VARCHAR (192) NULL,
                `created_at` TIMESTAMP NULL,
                `updated_at` TIMESTAMP NULL,
                INDEX `{$indexPrefix}_hash` (`hash`),
                INDEX `{$indexPrefix}_type` (`type`),
                INDEX `{$indexPrefix}_context` (`context`),
                INDEX `{$indexPrefix}_context_type` (`context_type`),
                INDEX `{$indexPrefix}_status` (`status`)
            ) $charsetCollate;";
            dbDelta($sql);
        }
    }
}
