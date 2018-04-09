<?php
require_once 'ReportTopVacanciesByRubrics.php';
require_once 'ReportTopWordsInVacancyTitle.php';

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

    public function save(string $filename)
    {
        // TODO: Implement save() method.
    }

    /**
     * Подготовка данных для вывода
     * @return array
     */
    public function prepareRows(): array
    {
        $this->data = [
            'Top vacancies by rubrics' => !is_null($this->reportTopVacanciesByRubrics) ? $this->reportTopVacanciesByRubrics->prepareRows() : [],
            'Top words in vacancy title' => !is_null($this->reportTopWordsInVacancyTitle) ? $this->reportTopWordsInVacancyTitle->prepareRows() : []
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
}