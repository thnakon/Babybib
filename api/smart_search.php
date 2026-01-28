<?php

/**
 * Babybib API - Unified Smart Search
 * ===================================
 * Handles URL, ISBN, DOI, and keyword searches.
 */

header('Content-Type: application/json; charset=utf-8');

require_once '../includes/session.php';
require_once '../includes/functions.php';

$q = $_GET['q'] ?? '';

if (empty($q)) {
    jsonResponse(['success' => false, 'error' => 'Search query is required'], 400);
}

// 1. Unified Cache System (Session + File)
$cache_key = md5(mb_strtolower(trim($q)));
$cache_file = __DIR__ . '/cache/' . $cache_key . '.json';

if (!isset($_SESSION['search_cache'])) $_SESSION['search_cache'] = [];

// Try Session Cache first
if (isset($_SESSION['search_cache'][$cache_key])) {
    $cached = $_SESSION['search_cache'][$cache_key];
    if (time() - $cached['time'] < 3600) jsonResponse($cached['data']);
}

// Try Persistent File Cache
if (file_exists($cache_file)) {
    $cached_data = json_decode(file_get_contents($cache_file), true);
    if ($cached_data && (time() - ($cached_data['timestamp'] ?? 0) < 86400 * 7)) { // 7 days
        jsonResponse($cached_data['result']);
    }
}

// Detection Logic
$type = 'keyword';
$clean_q = trim($q);

// 1. Check if URL
if (filter_var($clean_q, FILTER_VALIDATE_URL) || preg_match('/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/', $clean_q)) {
    $type = 'url';
    if (!preg_match('/^https?:\/\//', $clean_q)) {
        $clean_q = 'https://' . $clean_q;
    }
}
// 2. Check if DOI
else if (preg_match('/^10\.\d{4,9}\/[-._;()\/:\w]+$/', $clean_q)) {
    $type = 'doi';
}
// 3. Check if ISBN
else {
    $isbn_clean = str_replace(['-', ' '], '', $clean_q);
    if (preg_match('/^\d{10}(\d{3})?$/', $isbn_clean)) {
        $type = 'isbn';
        $clean_q = $isbn_clean;
    }
}
// 4. Default is 'keyword' which is already set

try {
    switch ($type) {
        case 'url':
            jsonResponse(handleUrlSearch($clean_q));
            break;
        case 'doi':
            jsonResponse(handleDoiSearch($clean_q));
            break;
        case 'isbn':
        case 'keyword':
            $result = handleIbnSearch($clean_q);
            // Cache successful result persistently
            if ($result['success'] && !empty($result['data'])) {
                $_SESSION['search_cache'][$cache_key] = ['time' => time(), 'data' => $result];
                file_put_contents($cache_file, json_encode([
                    'timestamp' => time(),
                    'query' => $q,
                    'result' => $result
                ], JSON_UNESCAPED_UNICODE));
            }
            jsonResponse($result);
            break;
    }
} catch (Exception $e) {
    error_log("Smart Search error: " . $e->getMessage());
    $code = $e->getCode() ?: 500;
    if ($code < 100 || $code > 599) $code = 500;
    jsonResponse(['success' => false, 'error' => $e->getMessage()], $code);
}

/**
 * Helper to fetch content via cURL
 */
function fetchUrl($url, $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36')
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($httpCode !== 200 || $response === false) {
        $error = curl_error($ch) ?: "HTTP $httpCode";
        curl_close($ch);
        return ['error' => true, 'code' => $httpCode, 'msg' => $error];
    }
    curl_close($ch);
    return $response;
}

/**
 * Handle URL Metadata Extraction
 */
function handleUrlSearch($url)
{
    $html = fetchUrl($url);
    if (is_array($html) && isset($html['error'])) {
        throw new Exception("Unable to fetch content from URL: " . $html['msg']);
    }

    $doc = new DOMDocument();
    @$doc->loadHTML('<?xml encoding="utf-8" ?>' . $html);
    $xpath = new DOMXPath($doc);

    $data = [
        'source' => 'url',
        'url' => $url,
        'title' => '',
        'author' => '',
        'publisher' => parse_url($url, PHP_URL_HOST),
        'year' => date('Y'),
        'resource_type' => 'webpage'
    ];

    // 1. Try JSON-LD first (Most accurate for modern sites)
    $scripts = $xpath->query('//script[@type="application/ld+json"]');
    foreach ($scripts as $script) {
        $json = json_decode($script->nodeValue, true);
        if ($json) {
            // Flatten if it's a @graph
            if (isset($json['@graph'])) {
                foreach ($json['@graph'] as $g) {
                    if (in_array($g['@type'], ['Article', 'WebPage', 'VideoObject'])) {
                        $json = $g;
                        break;
                    }
                }
            }

            if (isset($json['headline']) || isset($json['name'])) {
                $data['title'] = $json['headline'] ?? $json['name'];
                if (isset($json['author']['name'])) $data['author'] = $json['author']['name'];
                if (isset($json['publisher']['name'])) $data['publisher'] = $json['publisher']['name'];
                if (isset($json['datePublished'])) $data['year'] = substr($json['datePublished'], 0, 4);

                if (isset($json['@type'])) {
                    if ($json['@type'] === 'VideoObject') $data['resource_type'] = 'youtube_video';
                    if (strpos($json['@type'], 'Article') !== false) $data['resource_type'] = 'journal_article';
                }
                break; // Found good data
            }
        }
    }

    // 2. Fallback to Meta Tags if JSON-LD missing or incomplete
    if (empty($data['title'])) {
        $titleNode = $doc->getElementsByTagName('title')->item(0);
        if ($titleNode) $data['title'] = trim($titleNode->nodeValue);
    }

    $metas = [
        'og:title' => 'title',
        'og:site_name' => 'publisher',
        'author' => 'author',
        'article:author' => 'author',
        'pubdate' => 'year',
        'article:published_time' => 'year'
    ];

    foreach ($metas as $name => $key) {
        if (!empty($data[$key]) && $key !== 'year') continue;
        $node = $xpath->query("//meta[@property='$name']/@content | //meta[@name='$name']/@content")->item(0);
        if ($node) {
            $val = trim($node->nodeValue);
            if ($key === 'year') {
                $data[$key] = substr($val, 0, 4);
            } else {
                $data[$key] = $val;
            }
        }
    }

    // Special Check for YouTube
    if (strpos($url, 'youtube.com') !== false || strpos($url, 'youtu.be') !== false) {
        $data['resource_type'] = 'youtube_video';
        if (empty($data['publisher'])) $data['publisher'] = 'YouTube';

        // Try to get channel name as author
        if (empty($data['author'])) {
            $channelNode = $xpath->query("//link[@itemprop='name']/@content | //meta[@property='og:video:tag']/@content")->item(0);
            if ($channelNode) $data['author'] = trim($channelNode->nodeValue);
        }
    }

    return ['success' => true, 'data' => [$data]];
}

/**
 * Handle DOI Search via CrossRef
 */
function handleDoiSearch($doi)
{
    $url = "https://api.crossref.org/works/" . urlencode($doi);
    $response = fetchUrl($url, 'Babybib/2.0 (mailto:admin@localhost)');

    if (is_array($response) && isset($response['error'])) {
        throw new Exception("CrossRef API Error: " . $response['msg'] . " (Code: " . $response['code'] . ")");
    }

    $res = json_decode($response, true);
    if (!isset($res['message'])) return ['success' => true, 'data' => []];

    $item = $res['message'];
    $authors = [];
    if (isset($item['author'])) {
        foreach ($item['author'] as $a) {
            $authors[] = [
                'firstName' => $a['given'] ?? '',
                'lastName' => $a['family'] ?? '',
                'display' => ($a['given'] ?? '') . ' ' . ($a['family'] ?? '')
            ];
        }
    }

    return ['success' => true, 'data' => [[
        'source' => 'doi',
        'title' => $item['title'][0] ?? '',
        'authors' => $authors,
        'publisher' => $item['container-title'][0] ?? $item['publisher'] ?? '',
        'year' => $item['published-print']['date-parts'][0][0] ?? $item['published-online']['date-parts'][0][0] ?? '',
        'doi' => $doi,
        'volume' => $item['volume'] ?? '',
        'issue' => $item['issue'] ?? '',
        'pages' => $item['page'] ?? '',
        'resource_type' => 'journal_article'
    ]]];
}

/**
 * Handle ISBN/Keyword via Multiple APIs (OpenAlex Primary, Google Books Secondary)
 */
function handleIbnSearch($q)
{
    if (empty($q)) return ['success' => true, 'data' => []];

    $search_q = trim($q);
    $is_isbn = preg_match('/^\d{10}(\d{3})?$/', str_replace(['-', ' '], '', $search_q));

    // Check local fallback first for common demo queries
    $fallbackFile = __DIR__ . '/search_fallback.json';
    if (file_exists($fallbackFile)) {
        $fallbackData = json_decode(file_get_contents($fallbackFile), true);
        $qLower = mb_strtolower(trim($q));
        if ($fallbackData && isset($fallbackData[$qLower])) {
            return ['success' => true, 'data' => $fallbackData[$qLower], 'from_fallback' => true];
        }
    }

    // For ISBN: Use Open Library (best for ISBN)
    if ($is_isbn) {
        $olResults = handleOpenLibrarySearch($search_q);
        if ($olResults['success'] && !empty($olResults['data'])) {
            return $olResults;
        }
    }

    // PRIMARY: OpenAlex API (unlimited, academic focused)
    $oaResults = handleOpenAlexSearch($search_q);
    if ($oaResults['success'] && !empty($oaResults['data'])) {
        return $oaResults;
    }

    // SECONDARY: Open Library (books, unlimited)
    $olResults = handleOpenLibrarySearch($search_q);
    if ($olResults['success'] && !empty($olResults['data'])) {
        return $olResults;
    }

    // TERTIARY: Google Books (has rate limits but good for general books)
    $query = $is_isbn ? "isbn:" . str_replace(['-', ' '], '', $search_q) : $search_q;
    $url = "https://www.googleapis.com/books/v1/volumes?q=" . urlencode($query);
    $response = fetchUrl($url);

    if (is_array($response) && isset($response['error'])) {
        // All APIs failed
        return ['success' => true, 'data' => []];
    }

    $data = json_decode($response, true);
    if (!isset($data['items'])) return ['success' => true, 'data' => []];

    $results = [];
    foreach ($data['items'] as $item) {
        $v = $item['volumeInfo'];
        $authors = [];
        if (isset($v['authors'])) {
            foreach ($v['authors'] as $a) {
                $parts = explode(' ', trim($a));
                $lastName = count($parts) > 1 ? array_pop($parts) : '';
                $firstName = implode(' ', $parts) ?: $a;
                $authors[] = ['firstName' => $firstName, 'lastName' => $lastName, 'display' => $a];
            }
        }

        $rType = 'book';
        $titleLower = mb_strtolower($v['title'] ?? '');
        $descLower = mb_strtolower($v['description'] ?? '');
        $mediaKeywords = ['movie', 'film', 'video', 'cinema', 'ภาพยนตร์', 'หนัง', 'วิดีโอ'];
        foreach ($mediaKeywords as $kw) {
            if (mb_strpos($titleLower, $kw) !== false || mb_strpos($descLower, $kw) !== false) {
                $rType = 'film_video';
                break;
            }
        }

        $results[] = [
            'source' => 'books',
            'title' => ($v['title'] ?? '') . (isset($v['subtitle']) ? ': ' . $v['subtitle'] : ''),
            'authors' => $authors,
            'publisher' => $v['publisher'] ?? '',
            'year' => isset($v['publishedDate']) ? substr($v['publishedDate'], 0, 4) : '',
            'pages' => $v['pageCount'] ?? '',
            'thumbnail' => $v['imageLinks']['thumbnail'] ?? '',
            'resource_type' => $rType
        ];
    }

    return ['success' => true, 'data' => $results];
}

/**
 * OpenAlex API - Primary Academic Search (Unlimited!)
 * Covers: Authors, Papers, DOI, Institutions
 */
function handleOpenAlexSearch($q)
{
    $url = "https://api.openalex.org/works?search=" . urlencode($q) . "&per-page=5&mailto=babybib@localhost";
    $response = fetchUrl($url);

    if (is_array($response) && isset($response['error'])) return ['success' => true, 'data' => []];

    $data = json_decode($response, true);
    if (!isset($data['results']) || empty($data['results'])) return ['success' => true, 'data' => []];

    $results = [];
    foreach ($data['results'] as $work) {
        $authors = [];
        if (isset($work['authorships'])) {
            foreach ($work['authorships'] as $auth) {
                $name = $auth['author']['display_name'] ?? '';
                if ($name) {
                    $parts = explode(' ', trim($name));
                    $lastName = count($parts) > 1 ? array_pop($parts) : '';
                    $firstName = implode(' ', $parts) ?: $name;
                    $authors[] = ['firstName' => $firstName, 'lastName' => $lastName, 'display' => $name];
                }
            }
        }

        $year = $work['publication_year'] ?? '';
        $journal = '';
        if (isset($work['primary_location']['source']['display_name'])) {
            $journal = $work['primary_location']['source']['display_name'];
        }

        $results[] = [
            'source' => 'openalex',
            'title' => $work['title'] ?? '',
            'authors' => $authors,
            'publisher' => $journal,
            'year' => $year,
            'doi' => $work['doi'] ?? '',
            'resource_type' => 'journal_article'
        ];
    }

    return ['success' => true, 'data' => $results];
}

/**
 * Secondary Fallback: Open Library Search
 */
function handleOpenLibrarySearch($q)
{
    // Open Library uses a different query style
    $is_isbn = preg_match('/^\d{10}(\d{3})?$/', str_replace(['-', ' '], '', $q));
    $url = $is_isbn
        ? "https://openlibrary.org/api/volumes/brief/isbn/" . str_replace(['-', ' '], '', $q) . ".json"
        : "https://openlibrary.org/search.json?q=" . urlencode($q) . "&limit=5";

    $response = fetchUrl($url);
    if (is_array($response) && isset($response['error'])) return ['success' => true, 'data' => []];

    $data = json_decode($response, true);
    $results = [];

    if ($is_isbn && isset($data['records'])) {
        // ISBN record format
        foreach ($data['records'] as $rec) {
            $v = $rec['data'];
            $results[] = [
                'source' => 'openlibrary',
                'title' => $v['title'] ?? 'Unknown Title',
                'authors' => array_map(fn($a) => ['display' => $a['name']], $v['authors'] ?? []),
                'publisher' => $v['publishers'][0]['name'] ?? '',
                'year' => isset($v['publish_date']) ? substr($v['publish_date'], -4) : '',
                'resource_type' => 'book'
            ];
        }
    } else if (isset($data['docs'])) {
        // Keyword Search format
        foreach ($data['docs'] as $doc) {
            $results[] = [
                'source' => 'openlibrary',
                'title' => $doc['title'] ?? '',
                'authors' => array_map(fn($name) => ['display' => $name], $doc['author_name'] ?? []),
                'publisher' => $doc['publisher'][0] ?? '',
                'year' => $doc['first_publish_year'] ?? '',
                'resource_type' => 'book'
            ];
        }
    }

    return ['success' => true, 'data' => $results];
}
