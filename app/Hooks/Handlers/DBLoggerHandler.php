<?php

namespace FluentQueryLogger\App\Hooks\Handlers;

use FluentQueryLogger\App\Models\DbLog;
use FluentQueryLogger\Framework\Foundation\Application;
use FluentQueryLogger\Database\DBMigrator;
use FluentQueryLogger\Database\DBSeeder;
use FluentQueryLogger\Framework\Support\Arr;

class DBLoggerHandler
{
    public function catchQueries()
    {
        if (!defined('QM_VERSION')) {
            return false;
        }

        if (apply_filters('fql_disable_logging', false)) {
            return false;
        }

        $settings = get_option('_fql_settings');

        if (!$settings) {
            return false;
        }

        if (Arr::get($settings, 'active') != 'yes' || empty($settings['modules'])) {
            return false;
        }

        $dbq = \QM_Collectors::get('db_queries');
        $data = $dbq->get_data();

        if (empty($data['dbs']['$wpdb']) || empty($data['dbs']['$wpdb']->rows)) {
            return false;
        }

        $rows = $data['dbs']['$wpdb']->rows;

        $requestData = $_POST;
        $current_url = (is_ssl() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

        $formattedRows = [];
        foreach ($rows as $row) {
            $component = (array)$row['component'];

            if (!in_array($component['context'], $settings['modules'])) {
                continue;
            }

            $formattedRow = Arr::only($row, ['caller', 'sql', 'ltime', 'type', 'result']);
            $formattedRow['context_type'] = $component['type'];
            $formattedRow['context'] = $component['context'];
            $formattedRow['hash'] = md5($formattedRow['context'] . '-' . $formattedRow['sql']);
            $formattedRow['counter'] = 1;
            $formattedRow['trace'] = $this->getFormattedTrace($row['trace']);
            if (isset($formattedRows[$formattedRow['hash']])) {
                $formattedRow['counter'] += 1;
            }

            $formattedRow['total_counter'] = $formattedRow['counter'];
            $formattedRow['last_url'] = $current_url;
            $formattedRow['last_data'] = $requestData;
            $formattedRows[$formattedRow['hash']] = $formattedRow;
        }

        $this->insertRows($formattedRows);
    }

    /**
     * @param $backTrace \QM_Backtrace
     * @return string
     */
    private function getFormattedTrace($backTrace)
    {
        $items = $backTrace->get_filtered_trace();

        $formatted = [];

        foreach ($items as $item) {
            if (!empty($item['calling_file']) && !empty($item['calling_line'])) {
                $formatted[] = str_replace(ABSPATH, '', $item['calling_file']) . ' #' . $item['calling_line'] . ' => ' . $item['display'];
            }
        }

        return implode(PHP_EOL, $formatted);
    }

    private function insertRows($rows)
    {
        foreach ($rows as $row) {
            $exist = DbLog::where('hash', $row['hash'])->first();
            if ($exist) {
                $row['total_counter'] = $exist->total_counter + $row['counter'];
                $row = Arr::only($row, ['total_counter', 'ltime', 'counter', 'last_url', 'last_data', 'trace']);
                $exist->fill($row)->save();
            } else {
                DbLog::create($row);
            }
        }

        return true;
    }
}
