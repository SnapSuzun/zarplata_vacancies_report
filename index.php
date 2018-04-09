<?php

/** Включаем вывод подробного лога действий */
defined('VERBOSE_LOG') or define('VERBOSE_LOG', true);

require(__DIR__ . '/vendor/autoload.php');

use app\components\reports\CommonReport;
use app\components\integration\VacanciesAPISearcher;

$filename = 'test.xlsx';
$report = new CommonReport();

try {
    $report->build($filename, [
        'geo_id' => 826,
        'period' => VacanciesAPISearcher::PERIOD_TODAY,
        'limit' => 100
    ]);
    echo "Report was saved to {$filename}";
} catch (\ErrorException $e) {
    echo "Can't build a report. Error: " . $e->getMessage() . PHP_EOL;
}