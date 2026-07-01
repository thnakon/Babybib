<?php

declare(strict_types=1);

namespace Babybib\Search;

final class SearchCache
{
    private string $cacheDir;

    public function __construct(
        string $tmpDir,
        private readonly int $ttlSeconds = 300,
    ) {
        $this->cacheDir = rtrim($tmpDir, '/\\') . '/babybib_search_cache';
        $this->ensureDirectory($this->cacheDir);
    }

    public function getFresh(string $query): ?array
    {
        $file = $this->cacheFile($query);
        $data = $this->read($file);
        if ($data === null) {
            return null;
        }

        return $this->isFresh($file) ? $data : null;
    }

    public function getStale(string $query, int $maxAgeSeconds = 86400): ?array
    {
        $file = $this->cacheFile($query);
        $data = $this->read($file);
        if ($data === null) {
            return null;
        }

        $mtime = filemtime($file);
        if ($mtime === false || (time() - $mtime) > $maxAgeSeconds) {
            return null;
        }

        return $data;
    }

    public function put(string $query, array $response): void
    {
        $file = $this->cacheFile($query);
        $payload = json_encode($response, JSON_UNESCAPED_UNICODE);
        if ($payload === false) {
            return;
        }

        $tmpFile = $file . '.' . getmypid() . '.tmp';
        if (file_put_contents($tmpFile, $payload, LOCK_EX) === false) {
            return;
        }

        @rename($tmpFile, $file);
    }

    private function cacheFile(string $query): string
    {
        return $this->cacheDir . '/cache_' . hash('sha256', $query) . '.json';
    }

    private function isFresh(string $file): bool
    {
        $mtime = filemtime($file);

        return $mtime !== false && (time() - $mtime) < $this->ttlSeconds;
    }

    private function read(string $file): ?array
    {
        if (!is_file($file)) {
            return null;
        }

        $raw = file_get_contents($file);
        if ($raw === false || $raw === '') {
            return null;
        }

        $data = json_decode($raw, true);

        return is_array($data) ? $data : null;
    }

    private function ensureDirectory(string $dir): void
    {
        if (is_dir($dir)) {
            return;
        }

        @mkdir($dir, 0755, true);
    }
}
