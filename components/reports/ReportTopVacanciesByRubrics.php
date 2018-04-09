<?php

namespace app\components\reports;

/**
 * Отчет "Топ вакансий по рубрикам"
 *
 * Class ReportTopVacanciesByRubrics
 */
class ReportTopVacanciesByRubrics extends VacanciesReport
{
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

    /**
     * @return string
     */
    public static function reportName(): string
    {
        return 'Top vacancies by rubrics';
    }
}