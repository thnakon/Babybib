<?php

declare(strict_types=1);

namespace Babybib\Search;

final class SearchHttpClient
{
    private const USER_AGENT = 'Babybib/2.0 SmartSearch (Educational Tool; +https://babybib.app)';

    /** @var array<int, array{url: string, source: string, code: int}> */
    private array $errors = [];

    public function get(string $url, int $timeout = 8): ?string
    {
        if (function_exists('curl_init')) {
            return $this->getWithCurl($url, $timeout);
        }

        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => "User-Agent: " . self::USER_AGENT . "\r\nAccept: application/json\r\n",
                'timeout' => $timeout,
            ],
        ]);
        $result = @file_get_contents($url, false, $context);
        if ($result === false) {
            $this->recordError($url, 0);
            return null;
        }

        return $result;
    }

    /**
     * @param array<string, string> $requests
     * @return array<string, string|null>
     */
    public function getMulti(array $requests, int $timeout = 8): array
    {
        if (!function_exists('curl_multi_init')) {
            $responses = [];
            foreach ($requests as $key => $url) {
                $responses[$key] = $this->get($url, $timeout);
            }

            return $responses;
        }

        $multiHandle = curl_multi_init();
        $handles = [];

        foreach ($requests as $key => $url) {
            $handle = curl_init();
            curl_setopt_array($handle, $this->curlOptions($url, $timeout));
            curl_multi_add_handle($multiHandle, $handle);
            $handles[$key] = $handle;
        }

        $running = 0;
        $startedAt = time();
        $hardTimeout = $timeout + 2;

        do {
            curl_multi_exec($multiHandle, $running);
            curl_multi_select($multiHandle, 0.1);
        } while ($running > 0 && (time() - $startedAt) < $hardTimeout);

        $responses = [];
        foreach ($handles as $key => $handle) {
            $url = $requests[$key];
            $httpCode = (int) curl_getinfo($handle, CURLINFO_HTTP_CODE);
            if ($httpCode >= 200 && $httpCode < 300) {
                $responses[$key] = curl_multi_getcontent($handle);
            } else {
                $responses[$key] = null;
                $this->recordError($url, $httpCode);
            }

            curl_multi_remove_handle($multiHandle, $handle);
            curl_close($handle);
        }

        curl_multi_close($multiHandle);

        return $responses;
    }

    /**
     * @return array<int, array{url: string, source: string, code: int}>
     */
    public function errors(): array
    {
        return $this->errors;
    }

    private function getWithCurl(string $url, int $timeout): ?string
    {
        $handle = curl_init();
        curl_setopt_array($handle, $this->curlOptions($url, $timeout));

        $result = curl_exec($handle);
        $httpCode = (int) curl_getinfo($handle, CURLINFO_HTTP_CODE);
        curl_close($handle);

        if ($httpCode >= 200 && $httpCode < 300 && is_string($result)) {
            return $result;
        }

        $this->recordError($url, $httpCode);

        return null;
    }

    /**
     * @return array<int, mixed>
     */
    private function curlOptions(string $url, int $timeout): array
    {
        return [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_CONNECTTIMEOUT => min(5, $timeout),
            CURLOPT_USERAGENT => self::USER_AGENT,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_HTTPHEADER => ['Accept: application/json'],
        ];
    }

    private function recordError(string $url, int $httpCode): void
    {
        $host = parse_url($url, PHP_URL_HOST);
        if (!is_string($host) || $host === '') {
            $host = 'unknown';
        }

        $this->errors[] = [
            'url' => $host,
            'source' => $host,
            'code' => $httpCode,
        ];
    }
}
