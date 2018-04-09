<?php
require_once 'components/reports/CommonReport.php';
require_once 'components/integration/ZarplataAPIException.php';

$report = new CommonReport();

try {
    $report->build('test.csv', [
        'geo_id' => 826,
        'period' => VacanciesAPISearcher::PERIOD_TODAY,
        'limit' => 100
    ]);
} catch (ZarplataAPIException $e) {
    echo "Can't build a report. Error: " . $e->getMessage() . PHP_EOL;
}