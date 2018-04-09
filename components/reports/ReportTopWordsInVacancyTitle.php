<?php

require_once 'VacanciesReport.php';

/**
 * Отчет "Топ слов по упоминанию их в заголовках вакансий"
 * Class ReportTopWordsInVacancyTitle
 */
class ReportTopWordsInVacancyTitle extends VacanciesReport
{
    /**
     * Генерация отчета
     * @param array $queryParams Параметры, которые используются для запроса в АПИ
     * @param string $filename Название файла, в который будет сохранен сгенерированный отчет
     * @throws ZarplataAPIException
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
     * Поиск слов в названии вакансии
     * @param array $vacancy
     */
    public function handleVacancy(array $vacancy)
    {
        if (preg_match_all("/[\wа-яА-ЯёЁ_&\/\-']{2,}/u", $vacancy['header'], $matches)) {
            foreach ($matches[0] as $word) {
                $word = mb_strtolower($word);
                if (static::validateWord($word)) {
                    if (!isset($this->data[$word])) {
                        $this->data[$word] = 1;
                    } else {
                        $this->data[$word]++;
                    }
                }
            }
        }
    }

    /**
     * Подготовка статистики для вывода
     * @return array
     */
    public function prepareRows():array
    {
        arsort($this->data);
        return array_map(function ($word, $quantity) {
            return [
                'title' => $word,
                'vacanciesQuantity' => $quantity,
            ];
        }, array_keys($this->data), $this->data);
    }

    /**
     * Проверка строки на то, что она является словом
     * @param string $word
     * @return bool
     */
    protected static function validateWord(string $word):bool
    {
        return !in_array($word, static::skippedWords()) && !is_numeric($word);
    }

    /**
     * Список слов, которые не стоит учитывать в отчете
     * @return array
     */
    protected static function skippedWords():array
    {
        return [
            'на',
            'под',
            'без',
            'по',
            'из',
            'над',
            'ул',
            'ост'
        ];
    }
}