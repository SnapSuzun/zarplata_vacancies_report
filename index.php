<?php

/** Включаем вывод подробного лога действий */
defined('VERBOSE_LOG') or define('VERBOSE_LOG', true);

require(__DIR__ . '/vendor/autoload.php');

use app\components\reports\CommonReport;
use app\components\integration\VacanciesAPISearcher;

if (count($argv) < 2 || empty($argv[1])) {
    echo 'Error!!! File for saving results is not set.';
    exit(1);
}

$filename = $argv[1];
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