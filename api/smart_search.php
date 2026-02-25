<?php

/**
 * Babybib API - Smart Search v2
 * ==============================
 * Unified search endpoint that auto-detects input type (ISBN, DOI, URL, Keyword)
 * and queries multiple external databases for accurate bibliography data.
 * 
 * Supported Sources:
 * - Open Library (ISBN + Keyword)
 * - Google Books (ISBN + Keyword)
 * - CrossRef (DOI)
 * - OpenAlex (DOI)
 * - Web Scraper (URL)
 * 
 * Usage: GET /api/smart_search.php?q=<query>
 */

header('Content-Type: application/json; charset=utf-8');

require_once '../includes/session.php';
require_once '../includes/config.php';
require_once '../includes/functions.php';

// ─── Rate Limiting (IP-based, file-backed for multi-user support) ────────────
$clientIp = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
$ipHash = md5($clientIp);
$rateLimit = 30; // max requests per minute
$ratePeriod = 60; // seconds
$rateLimitDir = sys_get_temp_dir() . '/babybib_rate';
if (!is_dir($rateLimitDir)) @mkdir($rateLimitDir, 0755, true);
$rateLimitFile = $rateLimitDir . '/rate_' . $ipHash . '.json';

$rateData = ['count' => 0, 'reset' => time() + $ratePeriod];
if (file_exists($rateLimitFile)) {
    $rateData = json_decode(file_get_contents($rateLimitFile), true) ?: $rateData;
}

if (time() > ($rateData['reset'] ?? 0)) {
    $rateData = ['count' => 0, 'reset' => time() + $ratePeriod];
}

$rateData['count']++;
@file_put_contents($rateLimitFile, json_encode($rateData), LOCK_EX);

if ($rateData['count'] > $rateLimit) {
    http_response_code(429);
    echo json_encode([
        'success' => false,
        'error'   => 'Rate limit exceeded. Please wait a moment.',
        'retry_after' => max(0, ($rateData['reset'] ?? time()) - time())
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// ─── Input ───────────────────────────────────────────────────────────────────
$query = trim($_GET['q'] ?? '');

if (empty($query) || mb_strlen($query) < 2) {
    jsonResponse(['success' => false, 'error' => 'Query is required (min 2 characters)'], 400);
}

// ─── Cache Check ─────────────────────────────────────────────────────────────
$cacheKey = 'ss_cache_' . md5($query);
$cacheTTL = 300; // 5 minutes

if (isset($_SESSION[$cacheKey]) && $_SESSION[$cacheKey]['expires'] > time()) {
    $cachedData = $_SESSION[$cacheKey]['data'];
    session_write_close(); // Release session lock for concurrency
    jsonResponse($cachedData);
}

// Release session lock early so concurrent requests from same user don't queue
session_write_close();

// ─── Type Detection ──────────────────────────────────────────────────────────
$type = detectInputType($query);

// ─── Execute Search ──────────────────────────────────────────────────────────
try {
    $results = [];

    switch ($type) {
        case 'isbn':
            $isbn = preg_replace('/[^0-9X]/i', '', $query);
            $results = searchByISBN($isbn);
            break;

        case 'doi':
            $doi = $query;
            // Strip common prefixes
            $doi = preg_replace('#^https?://(dx\.)?doi\.org/#', '', $doi);
            $results = searchByDOI($doi);
            break;

        case 'url':
            $results = searchByURL($query);
            break;

        case 'keyword':
        default:
            $results = searchByKeyword($query);
            break;
    }

    $response = [
        'success' => true,
        'type'    => $type,
        'query'   => $query,
        'count'   => count($results),
        'data'    => $results
    ];

    // Cache the result (re-open session briefly to save)
    @session_start();
    $_SESSION[$cacheKey] = [
        'data'    => $response,
        'expires' => time() + $cacheTTL
    ];
    session_write_close();

    jsonResponse($response);
} catch (Exception $e) {
    error_log("Smart Search v2 error: " . $e->getMessage());
    jsonResponse(['success' => false, 'error' => 'Search failed: ' . $e->getMessage()], 500);
}

// ═══════════════════════════════════════════════════════════════════════════════
// HELPER FUNCTIONS
// ═══════════════════════════════════════════════════════════════════════════════

/**
 * Detect input type from the query string
 */
function detectInputType(string $q): string
{
    $q = trim($q);

    // URL detection
    if (preg_match('#^https?://#i', $q)) {
        return 'url';
    }

    // DOI detection (10.xxxx/xxxx or doi.org URL)
    if (preg_match('#^10\.\d{4,}/#', $q) || preg_match('#doi\.org/10\.\d{4,}/#i', $q)) {
        return 'doi';
    }

    // ISBN detection (10 or 13 digits, possibly with hyphens)
    $cleaned = preg_replace('/[^0-9X]/i', '', $q);
    if (preg_match('/^(\d{10}|\d{13}|\d{9}X)$/i', $cleaned)) {
        return 'isbn';
    }

    return 'keyword';
}

/**
 * HTTP GET helper with timeout and user-agent
 */
function httpGet(string $url, int $timeout = 8): ?string
{
    if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT        => $timeout,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_USERAGENT      => 'Babybib/2.0 SmartSearch (Educational Tool; +https://babybib.app)',
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER     => ['Accept: application/json']
        ]);
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            return $result;
        }
        return null;
    }

    // Fallback
    $opts = [
        'http' => [
            'method'  => 'GET',
            'header'  => "User-Agent: Babybib/2.0 SmartSearch\r\nAccept: application/json\r\n",
            'timeout' => $timeout
        ]
    ];
    $context = stream_context_create($opts);
    $result = @file_get_contents($url, false, $context);
    return $result !== false ? $result : null;
}

/**
 * Parse author name string into firstName / lastName
 */
function parseAuthorName(string $name): array
{
    $name = trim($name);
    if (empty($name)) return ['firstName' => '', 'lastName' => '', 'display' => ''];

    // Check if comma-separated (Last, First)
    if (strpos($name, ',') !== false) {
        $parts = array_map('trim', explode(',', $name, 2));
        return [
            'firstName' => $parts[1] ?? '',
            'lastName'  => $parts[0],
            'display'   => trim(($parts[1] ?? '') . ' ' . $parts[0])
        ];
    }

    // Space-separated (First Last)
    $parts = explode(' ', $name);
    if (count($parts) > 1) {
        $lastName  = array_pop($parts);
        $firstName = implode(' ', $parts);
        return [
            'firstName' => $firstName,
            'lastName'  => $lastName,
            'display'   => $name
        ];
    }

    return ['firstName' => $name, 'lastName' => '', 'display' => $name];
}

// ═══════════════════════════════════════════════════════════════════════════════
// SEARCH BY ISBN
// ═══════════════════════════════════════════════════════════════════════════════

function searchByISBN(string $isbn): array
{
    $results = [];

    // ─── Source 1: Open Library (Primary — most accurate for books) ───
    $olData = searchOpenLibraryByISBN($isbn);
    if ($olData) {
        $results[] = $olData;
    }

    // ─── Source 2: Google Books (Secondary — covers, pages) ───
    $gbData = searchGoogleBooksByISBN($isbn);
    if ($gbData) {
        // If we already have Open Library data, merge Google Books info
        if (!empty($results)) {
            $results[0] = mergeBookData($results[0], $gbData);
        } else {
            $results[] = $gbData;
        }
    }

    // ─── Fallback: Local data ───
    if (empty($results)) {
        $results = searchLocalFallback($isbn);
    }

    return $results;
}

function searchOpenLibraryByISBN(string $isbn): ?array
{
    $url = "https://openlibrary.org/api/books?bibkeys=ISBN:{$isbn}&format=json&jscmd=data";
    $response = httpGet($url);
    if (!$response) return null;

    $data = json_decode($response, true);
    if (empty($data)) return null;

    $key = "ISBN:{$isbn}";
    if (!isset($data[$key])) return null;

    $book = $data[$key];

    // Parse authors
    $authors = [];
    if (isset($book['authors'])) {
        foreach ($book['authors'] as $a) {
            $authors[] = parseAuthorName($a['name'] ?? '');
        }
    }

    // Parse year from publish_date
    $year = '';
    if (isset($book['publish_date'])) {
        if (preg_match('/(\d{4})/', $book['publish_date'], $m)) {
            $year = $m[1];
        }
    }

    return [
        'title'         => $book['title'] ?? '',
        'authors'       => $authors,
        'publisher'     => isset($book['publishers']) ? ($book['publishers'][0]['name'] ?? '') : '',
        'year'          => $year,
        'pages'         => isset($book['number_of_pages']) ? (string) $book['number_of_pages'] : '',
        'edition'       => '',
        'doi'           => '',
        'url'           => $book['url'] ?? '',
        'volume'        => '',
        'issue'         => '',
        'journal_name'  => '',
        'resource_type'  => 'book',
        'source'        => 'openlibrary',
        'confidence'    => 95,
        'thumbnail'     => $book['cover']['medium'] ?? ($book['cover']['small'] ?? '')
    ];
}

function searchGoogleBooksByISBN(string $isbn): ?array
{
    $url = "https://www.googleapis.com/books/v1/volumes?q=isbn:" . urlencode($isbn);
    $response = httpGet($url);
    if (!$response) return null;

    $data = json_decode($response, true);
    if (!isset($data['items'][0])) return null;

    return parseGoogleBooksItem($data['items'][0]);
}

function parseGoogleBooksItem(array $item): array
{
    $v = $item['volumeInfo'] ?? [];

    $authors = [];
    if (isset($v['authors'])) {
        foreach ($v['authors'] as $authorName) {
            $authors[] = parseAuthorName($authorName);
        }
    }

    $year = '';
    if (isset($v['publishedDate'])) {
        $year = substr($v['publishedDate'], 0, 4);
    }

    return [
        'title'         => ($v['title'] ?? '') . (isset($v['subtitle']) ? ': ' . $v['subtitle'] : ''),
        'authors'       => $authors,
        'publisher'     => $v['publisher'] ?? '',
        'year'          => $year,
        'pages'         => isset($v['pageCount']) ? (string) $v['pageCount'] : '',
        'edition'       => '',
        'doi'           => '',
        'url'           => '',
        'volume'        => '',
        'issue'         => '',
        'journal_name'  => '',
        'resource_type'  => 'book',
        'source'        => 'google_books',
        'confidence'    => 85,
        'thumbnail'     => $v['imageLinks']['thumbnail'] ?? ''
    ];
}

// ═══════════════════════════════════════════════════════════════════════════════
// SEARCH BY DOI
// ═══════════════════════════════════════════════════════════════════════════════

function searchByDOI(string $doi): array
{
    $results = [];

    // ─── Source 1: CrossRef (Primary — authoritative for journal articles) ───
    $crData = searchCrossRef($doi);
    if ($crData) {
        $results[] = $crData;
    }

    // ─── Source 2: OpenAlex (Secondary — additional metadata) ───
    $oaData = searchOpenAlex($doi);
    if ($oaData) {
        if (!empty($results)) {
            $results[0] = mergeBookData($results[0], $oaData);
        } else {
            $results[] = $oaData;
        }
    }

    return $results;
}

function searchCrossRef(string $doi): ?array
{
    $url = "https://api.crossref.org/works/" . urlencode($doi);
    $response = httpGet($url);
    if (!$response) return null;

    $data = json_decode($response, true);
    if (!isset($data['message'])) return null;

    $msg = $data['message'];

    // Parse authors
    $authors = [];
    if (isset($msg['author'])) {
        foreach ($msg['author'] as $a) {
            $authors[] = [
                'firstName' => $a['given'] ?? '',
                'lastName'  => $a['family'] ?? '',
                'display'   => trim(($a['given'] ?? '') . ' ' . ($a['family'] ?? ''))
            ];
        }
    }

    // Parse year
    $year = '';
    if (isset($msg['published']['date-parts'][0][0])) {
        $year = (string) $msg['published']['date-parts'][0][0];
    } elseif (isset($msg['published-print']['date-parts'][0][0])) {
        $year = (string) $msg['published-print']['date-parts'][0][0];
    } elseif (isset($msg['published-online']['date-parts'][0][0])) {
        $year = (string) $msg['published-online']['date-parts'][0][0];
    }

    // Parse title (CrossRef stores as array)
    $title = '';
    if (isset($msg['title'])) {
        $title = is_array($msg['title']) ? ($msg['title'][0] ?? '') : $msg['title'];
    }

    // Determine resource type
    $resourceType = 'journal_article';
    $crType = $msg['type'] ?? '';
    if (in_array($crType, ['book', 'monograph', 'edited-book'])) {
        $resourceType = 'book';
    } elseif ($crType === 'book-chapter') {
        $resourceType = 'book_chapter';
    } elseif (in_array($crType, ['proceedings-article', 'posted-content'])) {
        $resourceType = 'conference_paper';
    }

    // Journal name
    $journalName = '';
    if (isset($msg['container-title'])) {
        $journalName = is_array($msg['container-title']) ? ($msg['container-title'][0] ?? '') : $msg['container-title'];
    }

    return [
        'title'         => $title,
        'authors'       => $authors,
        'publisher'     => $msg['publisher'] ?? '',
        'year'          => $year,
        'pages'         => $msg['page'] ?? '',
        'edition'       => '',
        'doi'           => 'https://doi.org/' . $doi,
        'url'           => $msg['URL'] ?? ('https://doi.org/' . $doi),
        'volume'        => $msg['volume'] ?? '',
        'issue'         => $msg['issue'] ?? '',
        'journal_name'  => $journalName,
        'resource_type'  => $resourceType,
        'source'        => 'crossref',
        'confidence'    => 98,
        'thumbnail'     => ''
    ];
}

function searchOpenAlex(string $doi): ?array
{
    $url = "https://api.openalex.org/works/doi:" . urlencode($doi);
    $response = httpGet($url);
    if (!$response) return null;

    $data = json_decode($response, true);
    if (empty($data) || isset($data['error'])) return null;

    // Parse authors
    $authors = [];
    if (isset($data['authorships'])) {
        foreach ($data['authorships'] as $ship) {
            $name = $ship['author']['display_name'] ?? '';
            if ($name) {
                $authors[] = parseAuthorName($name);
            }
        }
    }

    // Parse year
    $year = isset($data['publication_year']) ? (string) $data['publication_year'] : '';

    // Journal
    $journalName = '';
    if (isset($data['primary_location']['source']['display_name'])) {
        $journalName = $data['primary_location']['source']['display_name'];
    }

    // Resource type
    $resourceType = 'journal_article';
    $oaType = $data['type'] ?? '';
    if ($oaType === 'book') $resourceType = 'book';
    elseif ($oaType === 'book-chapter') $resourceType = 'book_chapter';

    return [
        'title'         => $data['title'] ?? '',
        'authors'       => $authors,
        'publisher'     => $data['primary_location']['source']['host_organization_name'] ?? '',
        'year'          => $year,
        'pages'         => '',
        'edition'       => '',
        'doi'           => 'https://doi.org/' . $doi,
        'url'           => $data['primary_location']['landing_page_url'] ?? ('https://doi.org/' . $doi),
        'volume'        => (string)($data['biblio']['volume'] ?? ''),
        'issue'         => (string)($data['biblio']['issue'] ?? ''),
        'journal_name'  => $journalName,
        'resource_type'  => $resourceType,
        'source'        => 'openalex',
        'confidence'    => 90,
        'thumbnail'     => ''
    ];
}

// ═══════════════════════════════════════════════════════════════════════════════
// SEARCH BY URL
// ═══════════════════════════════════════════════════════════════════════════════

function searchByURL(string $url): array
{
    // Use existing web scraper
    $scraperUrl = SITE_URL . '/api/scraper/web.php?url=' . urlencode($url);
    $response = httpGet($scraperUrl, 12);
    if (!$response) return [];

    $data = json_decode($response, true);
    if (!$data || !$data['success']) return [];

    $meta = $data['data'];

    // Parse author if available
    $authors = [];
    if (!empty($meta['author'])) {
        $authors[] = parseAuthorName($meta['author']);
    }

    return [[
        'title'         => $meta['title'] ?? '',
        'authors'       => $authors,
        'publisher'     => $meta['website_name'] ?? '',
        'year'          => $meta['year'] ?? '',
        'month'         => $meta['month'] ?? '',
        'day'           => $meta['day'] ?? '',
        'pages'         => '',
        'edition'       => '',
        'doi'           => '',
        'url'           => $url,
        'volume'        => '',
        'issue'         => '',
        'journal_name'  => '',
        'website_name'  => $meta['website_name'] ?? '',
        'resource_type'  => 'website',
        'source'        => 'web',
        'confidence'    => 75,
        'thumbnail'     => ''
    ]];
}

// ═══════════════════════════════════════════════════════════════════════════════
// SEARCH BY KEYWORD
// ═══════════════════════════════════════════════════════════════════════════════

function searchByKeyword(string $query): array
{
    $results = [];

    // ─── Source 1: Open Library Search ───
    $olResults = searchOpenLibraryByKeyword($query);
    $results = array_merge($results, $olResults);

    // ─── Source 2: Google Books Search ───
    $gbResults = searchGoogleBooksByKeyword($query);

    // Merge Google Books results (avoid duplicates by title similarity)
    foreach ($gbResults as $gb) {
        $isDuplicate = false;
        foreach ($results as &$existing) {
            if (similarTitles($existing['title'], $gb['title'])) {
                $existing = mergeBookData($existing, $gb);
                $isDuplicate = true;
                break;
            }
        }
        unset($existing);
        if (!$isDuplicate) {
            $results[] = $gb;
        }
    }

    // ─── Fallback: Local data ───
    if (empty($results)) {
        $results = searchLocalFallback($query);
    }

    // Sort by confidence (highest first) and limit to 15 for pagination
    usort($results, function ($a, $b) {
        return ($b['confidence'] ?? 0) - ($a['confidence'] ?? 0);
    });

    return array_slice($results, 0, 15);
}

function searchOpenLibraryByKeyword(string $query): array
{
    $url = "https://openlibrary.org/search.json?q=" . urlencode($query) . "&limit=8&fields=key,title,author_name,publisher,first_publish_year,number_of_pages_median,cover_i,edition_key";
    $response = httpGet($url);
    if (!$response) return [];

    $data = json_decode($response, true);
    if (!isset($data['docs'])) return [];

    $results = [];
    foreach ($data['docs'] as $doc) {
        $authors = [];
        if (isset($doc['author_name'])) {
            foreach ($doc['author_name'] as $authorName) {
                $authors[] = parseAuthorName($authorName);
            }
        }

        $coverId = $doc['cover_i'] ?? null;
        $thumbnail = $coverId ? "https://covers.openlibrary.org/b/id/{$coverId}-M.jpg" : '';

        $results[] = [
            'title'         => $doc['title'] ?? '',
            'authors'       => $authors,
            'publisher'     => isset($doc['publisher']) ? ($doc['publisher'][0] ?? '') : '',
            'year'          => isset($doc['first_publish_year']) ? (string) $doc['first_publish_year'] : '',
            'pages'         => isset($doc['number_of_pages_median']) ? (string) $doc['number_of_pages_median'] : '',
            'edition'       => '',
            'doi'           => '',
            'url'           => '',
            'volume'        => '',
            'issue'         => '',
            'journal_name'  => '',
            'resource_type'  => 'book',
            'source'        => 'openlibrary',
            'confidence'    => 88,
            'thumbnail'     => $thumbnail
        ];
    }

    return $results;
}

function searchGoogleBooksByKeyword(string $query): array
{
    $url = "https://www.googleapis.com/books/v1/volumes?q=" . urlencode($query) . "&maxResults=8&printType=books";
    $response = httpGet($url);
    if (!$response) return [];

    $data = json_decode($response, true);
    if (!isset($data['items'])) return [];

    $results = [];
    foreach ($data['items'] as $item) {
        $results[] = parseGoogleBooksItem($item);
    }

    return $results;
}

// ═══════════════════════════════════════════════════════════════════════════════
// UTILITY FUNCTIONS
// ═══════════════════════════════════════════════════════════════════════════════

/**
 * Merge two book data arrays, preferring non-empty values from primary
 */
function mergeBookData(array $primary, array $secondary): array
{
    $merged = $primary;

    foreach ($secondary as $key => $value) {
        if ($key === 'source' || $key === 'confidence') continue;

        if (empty($merged[$key]) && !empty($value)) {
            $merged[$key] = $value;
        }

        // Special: merge authors if primary has none
        if ($key === 'authors' && empty($merged['authors']) && !empty($value)) {
            $merged['authors'] = $value;
        }

        // Special: prefer higher page count
        if ($key === 'pages' && !empty($value) && (int)$value > (int)($merged['pages'] ?? 0)) {
            $merged['pages'] = $value;
        }

        // Special: prefer thumbnail from Google Books (better quality)
        if ($key === 'thumbnail' && !empty($value) && strpos($value, 'googleapis') !== false) {
            $merged['thumbnail'] = $value;
        }
    }

    // Update source info
    if ($primary['source'] !== $secondary['source']) {
        $merged['source'] = $primary['source'] . '+' . $secondary['source'];
        $merged['confidence'] = min(99, max($primary['confidence'] ?? 85, $secondary['confidence'] ?? 85) + 5);
    }

    return $merged;
}

/**
 * Check if two titles are similar enough to be considered the same work
 */
function similarTitles(string $a, string $b): bool
{
    $a = mb_strtolower(trim($a));
    $b = mb_strtolower(trim($b));

    if ($a === $b) return true;
    if (empty($a) || empty($b)) return false;

    // Check if one contains the other
    if (mb_strpos($a, $b) !== false || mb_strpos($b, $a) !== false) {
        return true;
    }

    // Use similar_text percentage
    similar_text($a, $b, $percent);
    return $percent > 80;
}

/**
 * Search local fallback JSON file
 */
function searchLocalFallback(string $query): array
{
    $fallbackFile = __DIR__ . '/search_fallback.json';
    if (!file_exists($fallbackFile)) return [];

    $data = json_decode(file_get_contents($fallbackFile), true);
    if (!$data) return [];

    $queryLower = mb_strtolower(trim($query));
    $results = [];

    foreach ($data as $key => $items) {
        if (mb_strpos(mb_strtolower($key), $queryLower) !== false ||
            mb_strpos($queryLower, mb_strtolower($key)) !== false) {
            foreach ($items as $item) {
                $item['source']     = 'local_fallback';
                $item['confidence'] = 70;
                $item['doi']        = $item['doi'] ?? '';
                $item['url']        = $item['url'] ?? '';
                $item['volume']     = $item['volume'] ?? '';
                $item['issue']      = $item['issue'] ?? '';
                $item['journal_name'] = $item['journal_name'] ?? '';
                $item['edition']    = $item['edition'] ?? '';
                $item['resource_type'] = $item['resource_type'] ?? 'book';
                $results[] = $item;
            }
        }
    }

    return $results;
}
