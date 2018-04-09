<?php
require_once 'ZarplataAPIException.php';

/**
 * Болванка для дальнейших интеграций с различными методами АПИ Zarplata.ru и т.д.
 *
 * Class ZarplataAPI
 */
abstract class ZarplataAPI
{
    const API_URL = 'https://api.zp.ru/v1';

    /**
     * Допустимые параметры запроса к АПИ
     * @return array
     */
    protected static function possibleQueryParameters(): array
    {
        return [];
    }

    /**
     * Извлечение параметров запроса к АПИ из передаваемых конфигов
     * @param array $queryParams
     * @return array
     */
    protected static function extractRequestParameters(array $queryParams)
    {
        $parameterNames = static::possibleQueryParameters();
        $extractedParameters = [];

        // @TODO Здесь бы еще сделать валидацию на необходимый тип параметра или на его возможные значения, но целесообразно ли тратить на это время в рамках текущей задачи
        foreach ($parameterNames as $name) {
            if (isset($queryParams[$name])) {
                $extractedParameters[$name] = $queryParams[$name];
            }
        }

        return $extractedParameters;
    }

    /**
     * Запрос к АПИ
     * @param string $url
     * @param array $params
     * @return array
     * @throws ZarplataAPIException
     */
    protected static function query(string $url, array $params)
    {
        $url = $url . '?' . http_build_query($params);
        $headers = [
            'Content-type: application/json',
        ];
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        if ($response === false) {
            $error = curl_error($ch);
            throw new ZarplataAPIException("Request was crashed with error: {$error}");
        }
        $decodedResponse = json_decode($response, true);
        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($responseCode != 200) {
            $errorMessage = '';
            if (isset($decodedResponse['errors']) && !empty($decodedResponse['errors'])) {
                $errorMessage = $decodedResponse['errors'][0]['message'];
            }
            throw new ZarplataAPIException("API responded with error '{$errorMessage}'. Response code = {$responseCode}");
        }

        return $decodedResponse;
    }
}