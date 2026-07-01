<?php

declare(strict_types=1);

namespace Babybib\Search;

final class SearchResultNormalizer
{
    public function normalizeTitle(?string $title): string
    {
        if ($title === null || $title === '') {
            return '';
        }

        $normalized = mb_strtolower($title, 'UTF-8');

        return preg_replace('/[^\p{L}\p{N}]+/u', '', $normalized) ?? '';
    }

    public function similarTitles(?string $firstTitle, ?string $secondTitle): bool
    {
        $first = $this->normalizeTitle($firstTitle);
        $second = $this->normalizeTitle($secondTitle);
        if ($first === '' || $second === '') {
            return false;
        }

        similar_text($first, $second, $percent);

        return $percent > 80;
    }

    public function confidence(array $result, int $baseScore = 80, bool $isThaiSearch = false): int
    {
        $score = $baseScore;

        if (empty($result['authors'])) {
            $score -= 15;
        }

        if (!empty($result['year'])) {
            $score += 3;
        }

        if (!empty($result['publisher'])) {
            $score += 2;
        }

        if (!empty($result['pages'])) {
            $score += 2;
        }

        if (!empty($result['doi'])) {
            $score += 3;
        }

        if (!empty($result['thumbnail'])) {
            $score += 2;
        }

        if ($isThaiSearch && !empty($result['title'])) {
            $score += $this->containsThai((string) $result['title']) ? 3 : -30;
        }

        return max(0, min(99, $score));
    }

    public function sortByConfidenceAndType(array &$results): void
    {
        usort($results, function ($first, $second) {
            $confidenceDiff = ($second['confidence'] ?? 0) - ($first['confidence'] ?? 0);
            if ($confidenceDiff !== 0) {
                return $confidenceDiff;
            }

            $typeOrder = [
                'book' => 0,
                'book_chapter' => 1,
                'thesis_unpublished' => 2,
                'journal_article' => 3,
            ];
            $firstOrder = $typeOrder[$first['resource_type'] ?? ''] ?? 5;
            $secondOrder = $typeOrder[$second['resource_type'] ?? ''] ?? 5;

            return $firstOrder - $secondOrder;
        });
    }

    private function containsThai(string $text): bool
    {
        return preg_match('/[\x{0E00}-\x{0E7F}]/u', $text) === 1;
    }
}
