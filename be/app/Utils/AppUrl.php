<?php

namespace App\Utils;

class AppUrl
{
    public static function addParamUrl($url, array $params = []): ?string
    {
        if (is_null($url)) {
            return null;
        }

        $baseUrl = static::getBaseUrl($url);
        $paramUrl = static::getParamUrl($url);
        $paramStr = http_build_query(array_merge($paramUrl, $params));

        if ($paramStr !== '') {
            $baseUrl .= '?' . $paramStr;
        }

        return $baseUrl;
    }

    public static function getBaseUrl($url): string
    {
        return preg_replace('/\?.*$/', '', $url);
    }

    /**
     * @return array <string|mixed>
     */
    public static function getParamUrl($url): array
    {
        $query = parse_url($url, PHP_URL_QUERY);
        parse_str($query, $result);

        return $result ?: [];
    }
}
