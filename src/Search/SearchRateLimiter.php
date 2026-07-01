<?php

declare(strict_types=1);

namespace Babybib\Search;

final class SearchRateLimiter
{
    private string $rateDir;

    public function __construct(
        string $tmpDir,
        private readonly int $limit = 30,
        private readonly int $periodSeconds = 60,
    ) {
        $this->rateDir = rtrim($tmpDir, '/\\') . '/babybib_rate';
        $this->ensureDirectory($this->rateDir);
    }

    /**
     * @return array{allowed: bool, retry_after: int}
     */
    public function consume(string $clientIp): array
    {
        $file = $this->rateDir . '/rate_' . hash('sha256', $clientIp) . '.json';
        $handle = fopen($file, 'c+');
        if ($handle === false) {
            return ['allowed' => true, 'retry_after' => 0];
        }

        try {
            flock($handle, LOCK_EX);
            rewind($handle);
            $raw = stream_get_contents($handle);
            $data = is_string($raw) && $raw !== ''
                ? json_decode($raw, true)
                : null;

            if (!is_array($data)) {
                $data = ['count' => 0, 'reset' => time() + $this->periodSeconds];
            }

            if (time() > (int) ($data['reset'] ?? 0)) {
                $data = ['count' => 0, 'reset' => time() + $this->periodSeconds];
            }

            $data['count'] = (int) ($data['count'] ?? 0) + 1;
            $retryAfter = max(0, (int) ($data['reset'] ?? time()) - time());

            ftruncate($handle, 0);
            rewind($handle);
            fwrite($handle, json_encode($data, JSON_UNESCAPED_UNICODE) ?: '{}');
            fflush($handle);

            return [
                'allowed' => $data['count'] <= $this->limit,
                'retry_after' => $retryAfter,
            ];
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

    private function ensureDirectory(string $dir): void
    {
        if (is_dir($dir)) {
            return;
        }

        @mkdir($dir, 0755, true);
    }
}

