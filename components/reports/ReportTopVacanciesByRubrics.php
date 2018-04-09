<?php

require_once 'VacanciesReport.php';

/**
 * Отчет "Топ вакансий по рубрикам"
 *
 * Class ReportTopVacanciesByRubrics
 */
class ReportTopVacanciesByRubrics extends VacanciesReport
{
    /**
     * Генерация отчета
     * @param array $queryParams Параметры, которые используются для запроса в АПИ
     * @param string $filename Название файла, в который будет сохранен сгенерированный отчет
     * @throws ErrorException
     */
    public function build(string $filename, array $queryParams = [])
    {
        $this->generateData($queryParams);
        $this->save($filename);
    }

    /**
     * @param string $filename
     */
    public function save(string $filename)
    {
        $rows = $this->prepareRows();
        $f = fopen($filename, 'w');
        fprintf($f, implode("\r\n", array_map(function ($item) {
            return implode(',', $item);
        }, $rows)));
        fclose($f);
    }

    /**
     * Подсчет статистики по рубрикам в очередной вакансии
     * @param array $vacancy
     */
    public function handleVacancy(array $vacancy)
    {
        if (isset($vacancy['rubrics']) && is_array($vacancy['rubrics'])) {
            foreach ($vacancy['rubrics'] as $rubric) {
                if (!isset($this->data[$rubric['title']])) {
                    $this->data[$rubric['title']] = 1;
                } else {
                    $this->data[$rubric['title']]++;
                }
            }
        }
    }

    /**
     * Подготовка статистики для вывода
     * @return array
     */
    public function prepareRows(): array
    {
        arsort($this->data);
        return array_map(function ($rubric, $quantity) {
            return [
                'title' => $rubric,
                'vacanciesQuantity' => $quantity,
            ];
        }, array_keys($this->data), $this->data);
    }
}