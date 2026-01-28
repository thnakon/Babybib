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

// Detection Logic
$type = 'keyword';
$clean_q = trim($q);

// 1. Check if URL
if (filter_var($clean_q, FILTER_VALIDATE_URL)) {
    $type = 'url';
}
// 2. Check if DOI (Generic DOI regex: 10.\d{4,9}/[-._;()/:a-zA-Z0-9]+)
else if (preg_match('/^10\.\d{4,9}\/[-._;()\/:\w]+$/', $clean_q)) {
    $type = 'doi';
}
// 3. Check if ISBN (10 or 13 digits, possible hyphens)
else {
    $isbn_clean = str_replace(['-', ' '], '', $clean_q);
    if (preg_match('/^\d{10}(\d{3})?$/', $isbn_clean)) {
        $type = 'isbn';
        $clean_q = $isbn_clean;
    }
}

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
            jsonResponse(handleIbnSearch($clean_q));
            break;
    }
} catch (Exception $e) {
    error_log("Smart Search error: " . $e->getMessage());
    jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
}

/**
 * Helper to fetch content via cURL
 */
function fetchUrl($url, $userAgent = 'Babybib/2.0 search agent')
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200 || $response === false) {
        $error = curl_error($ch);
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
 * Handle ISBN/Keyword via Google Books
 */
function handleIbnSearch($q)
{
    if (empty($q)) return ['success' => true, 'data' => []];

    $search_q = is_numeric($q) ? "isbn:$q" : $q;
    $url = "https://www.googleapis.com/books/v1/volumes?q=" . urlencode($search_q);
    $response = fetchUrl($url);

    if (is_array($response) && isset($response['error'])) {
        throw new Exception("Google Books API Error: " . $response['msg'] . " (Code: " . $response['code'] . ")");
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

        $results[] = [
            'source' => 'books',
            'title' => ($v['title'] ?? '') . (isset($v['subtitle']) ? ': ' . $v['subtitle'] : ''),
            'authors' => $authors,
            'publisher' => $v['publisher'] ?? '',
            'year' => isset($v['publishedDate']) ? substr($v['publishedDate'], 0, 4) : '',
            'pages' => $v['pageCount'] ?? '',
            'thumbnail' => $v['imageLinks']['thumbnail'] ?? '',
            'resource_type' => 'book'
        ];
    }

    return ['success' => true, 'data' => $results];
}
