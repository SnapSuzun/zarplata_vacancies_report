<?php

namespace app\components\reports;


/**
 * Отчет "Топ слов по упоминанию их в заголовках вакансий"
 * Class ReportTopWordsInVacancyTitle
 */
class ReportTopWordsInVacancyTitle extends VacanciesReport
{
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
    public function prepareRows(): array
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
    protected static function validateWord(string $word): bool
    {
        return !in_array($word, static::skippedWords()) && !is_numeric($word);
    }

    /**
     * Список слов, которые не стоит учитывать в отчете
     * @return array
     */
    protected static function skippedWords(): array
    {
        return [
            'на',
            'под',
            'без',
            'по',
            'из',
            'над',
            'ул',
            'ост',
            'ст',
            'со',
            'во',
            'до',
            'за',
            'кв',
            'от',
            'пл',
        ];
    }

    /**
     * @return string
     */
    public static function reportName(): string
    {
        return 'Top words in vacancy title';
    }
}