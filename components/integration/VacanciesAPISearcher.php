<?php

namespace app\components\integration;

/**
 * Поиск и постраничный перебор вакансий
 *
 * Class VacanciesAPISearcher
 */
class VacanciesAPISearcher extends ZarplataAPI
{
    const VACANCIES_URI = 'vacancies';

    const PERIOD_TODAY = 'today';
    const PERIOD_LAST_THREE_DAYS = 'three';
    const PERIOD_WEEK = 'week';
    const PERIOD_MONTH = 'month';

    /**
     * @var int
     */
    protected $offset = null;
    /**
     * @var array
     */
    protected $queryParams = [];
    /**
     * @var null
     */
    protected $commonCount = null;


    /**
     * VacanciesAPISearcher constructor.
     * @param array $queryParams Параметры запроса к АПИ
     */
    public function __construct(array $queryParams = [])
    {
        $this->queryParams = static::extractRequestParameters($queryParams);
    }

    /**
     * Поиск вакансий
     * @param int|null $offset
     * @param int|null $limit
     * @return array|bool
     * @throws ZarplataAPIException
     */
    public function find(int $offset = null, int $limit = null)
    {
        $queryParams = $this->queryParams;

        if (!is_null($offset)) {
            $queryParams['offset'] = $offset;
        }
        if (!is_null($limit)) {
            $queryParams['limit'] = $limit;
        }

        $response = static::query(static::apiUrl(), $queryParams);

        if (is_null($this->commonCount) && isset($response['metadata'])) {
            $this->commonCount = $response['metadata']['resultset']['count'];
        }

        $data = $response['vacancies'] ?? false;

        if (is_array($data)) {
            $this->offset = ($queryParams['offset'] ?? 0) + count($data);
        }

        if (defined('VERBOSE_LOG') && VERBOSE_LOG) {
            if (!is_null($this->commonCount) && !is_null($this->offset)) {
                echo "Loaded {$this->offset} vacancies of {$this->commonCount}" . PHP_EOL;
            }
        }

        return $data;
    }

    /**
     * Следующая страничка с вакансиями
     * @param int|null $limit
     * @return array|bool
     * @throws ZarplataAPIException
     */
    public function findNext(int $limit = null)
    {
        if (!is_null($this->commonCount) && $this->offset >= $this->commonCount) {
            return false;
        }
        return $this->find($this->offset, $limit);
    }

    /**
     * Формирование строки URL
     * @return string
     */
    public static function apiUrl(): string
    {
        return rtrim(static::API_URL, '/') . '/' . ltrim(static::VACANCIES_URI, '/');
    }

    /**
     * @return array
     */
    protected static function possibleQueryParameters(): array
    {
        return [
            'geo_id',
            'period',
            'limit',
            'offset',
            'district_id',
        ];
    }
}