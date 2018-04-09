<?php

namespace app\components\reports;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Общий отчет, содержащий данные о "Топ вакансий по рубрикам" и "Топ слов по упоминанию их в заголовках вакансий"
 * Class CommonReport
 */
class CommonReport extends VacanciesReport
{
    /**
     * @var ReportTopVacanciesByRubrics
     */
    protected $reportTopVacanciesByRubrics = null;
    /**
     * @var ReportTopWordsInVacancyTitle
     */
    protected $reportTopWordsInVacancyTitle = null;

    /**
     * Сохранение статистики в файл
     * @param string $filename
     */
    public function save(string $filename)
    {
        $spreadsheet = new Spreadsheet();
        if (!is_null($this->reportTopVacanciesByRubrics) && !is_null($this->reportTopWordsInVacancyTitle)) {
            $spreadsheet->createSheet();
        }
        if (!is_null($this->reportTopVacanciesByRubrics)) {
            if (defined('VERBOSE_LOG') && VERBOSE_LOG) {
                echo "Start preparing worksheet for report '" . ReportTopVacanciesByRubrics::reportName() . "'" . PHP_EOL;
            }
            $sheet = $spreadsheet->getActiveSheet();
            $this->reportTopVacanciesByRubrics->prepareExcelWorksheet($sheet);
            if ($spreadsheet->getSheetCount() > 1) {
                $spreadsheet->setActiveSheetIndex(1);
            }
        }

        if (!is_null($this->reportTopWordsInVacancyTitle)) {
            if (defined('VERBOSE_LOG') && VERBOSE_LOG) {
                echo "Start preparing worksheet for report '" . ReportTopWordsInVacancyTitle::reportName() . "'" . PHP_EOL;
            }
            $sheet = $spreadsheet->getActiveSheet();
            $this->reportTopWordsInVacancyTitle->prepareExcelWorksheet($sheet);
        }

        $spreadsheet->setActiveSheetIndex(0);
        $writer = new Xlsx($spreadsheet);
        $writer->save($filename);
    }

    /**
     * Генерация данных для отчета
     * @param array $queryParams
     * @return array
     */
    public function generateData(array $queryParams = [])
    {
        $this->reportTopVacanciesByRubrics = new ReportTopVacanciesByRubrics();
        $this->reportTopWordsInVacancyTitle = new ReportTopWordsInVacancyTitle();
        return parent::generateData($queryParams);
    }

    /**
     * Подготовка данных для вывода
     * @return array
     */
    public function prepareRows(): array
    {
        $this->data = [
            ReportTopVacanciesByRubrics::reportName() => !is_null($this->reportTopVacanciesByRubrics) ? $this->reportTopVacanciesByRubrics->prepareRows() : [],
            ReportTopWordsInVacancyTitle::reportName() => !is_null($this->reportTopWordsInVacancyTitle) ? $this->reportTopWordsInVacancyTitle->prepareRows() : []
        ];

        return $this->data;
    }

    /**
     * Обработка данных из одной вакансии
     * @param array $vacancy
     */
    public function handleVacancy(array $vacancy)
    {
        if (is_null($this->reportTopWordsInVacancyTitle)) {
            $this->reportTopWordsInVacancyTitle = new ReportTopWordsInVacancyTitle();
        }
        if (is_null($this->reportTopVacanciesByRubrics)) {
            $this->reportTopVacanciesByRubrics = new ReportTopVacanciesByRubrics();
        }
        $this->reportTopVacanciesByRubrics->handleVacancy($vacancy);
        $this->reportTopWordsInVacancyTitle->handleVacancy($vacancy);
    }

    /**
     * @return string
     */
    public static function reportName(): string
    {
        return 'Common report';
    }
}