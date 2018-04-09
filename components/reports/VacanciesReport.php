<?php

namespace app\components\reports;

use app\components\integration\VacanciesAPISearcher;
use app\components\integration\ZarplataAPIException;
use ErrorException;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Общая болванка для отчетов, строящихся на основе данных о вакансиях
 *
 * Class VacanciesReport
 */
abstract class VacanciesReport
{
    /**
     * @var array
     */
    protected $data = [];

    /**
     * Генерация отчета
     * @param array $queryParams Параметры, которые используются для запроса в АПИ
     * @param string $filename Название файла, в который будет сохранен сгенерированный отчет
     * @throws ErrorException
     */
    public function build(string $filename, array $queryParams = [])
    {
        if (empty($filename)) {
            throw new \ErrorException('Filename for saving report is empty.');
        }
        if (defined('VERBOSE_LOG') && VERBOSE_LOG) {
            echo "Start generating data..." . PHP_EOL;
        }
        $this->generateData($queryParams);
        if (defined('VERBOSE_LOG') && VERBOSE_LOG) {
            echo "Start saving data..." . PHP_EOL;
        }
        $this->save($filename);
    }

    /**
     * Подготовка полученных данных для вывода
     * @return array
     */
    public abstract function prepareRows(): array;

    /**
     * Обработка данных об очередной вакансии
     * @param array $vacancy
     */
    public abstract function handleVacancy(array $vacancy);

    /**
     * Наиманование отчета
     * @return string
     */
    public static abstract function reportName(): string;

    /**
     * Генерация данных для отчета
     * @param array $queryParams
     * @return array
     * @throws ErrorException
     */
    public function generateData(array $queryParams = [])
    {
        $vacanciesSearcher = new VacanciesAPISearcher($queryParams);

        try {
            while ($vacancies = $vacanciesSearcher->findNext()) {
                $this->handleVacanciesBatch($vacancies);
            }
        } catch (ZarplataAPIException $e) {
            throw new ErrorException("Zarplata API request failed: " . $e->getMessage());
        }

        return $this->data;
    }

    /**
     * Обработка данных в очередной пачке вакансий
     * @param array $vacancies
     */
    public function handleVacanciesBatch(array $vacancies)
    {
        foreach ($vacancies as $vacancy) {
            $this->handleVacancy($vacancy);
        }
    }

    /**
     * Сохранение сгенерированного отчета
     * @param string $filename Название файла, в который будет сохранен сгенерированный отчет
     */
    public function save(string $filename)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $this->prepareExcelWorksheet($sheet);

        $writer = new Xlsx($spreadsheet);
        $writer->save($filename);
    }

    /**
     * Вывод данных в лист электронной таблицы
     * @param Worksheet $sheet
     */
    public function prepareExcelWorksheet(Worksheet $sheet)
    {
        $sheet->setTitle(static::reportName());
        $rows = $this->prepareRows();
        if (!empty($rows)) {
            foreach (range(1, count($rows[0])) as $columnIndex) {
                $sheet->getColumnDimensionByColumn($columnIndex)->setAutoSize(true);
            }
            $rowIndex = 1;
            foreach ($rows as $row) {
                $columnIndex = 1;
                foreach ($row as $value) {
                    $sheet->setCellValueByColumnAndRow($columnIndex++, $rowIndex, $value);
                }
                $rowIndex++;
            }
        }
    }
}