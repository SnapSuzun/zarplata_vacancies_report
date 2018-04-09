<?php
require_once __DIR__ . '/../integration/VacanciesAPISearcher.php';
require_once __DIR__ . '/../integration/ZarplataAPIException.php';

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
     */
    public abstract function build(string $filename, array $queryParams = []);

    /**
     * Сохранение сгенерированного отчета
     * @param string $filename Название файла, в который будет сохранен сгенерированный отчет
     */
    public abstract function save(string $filename);

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
}