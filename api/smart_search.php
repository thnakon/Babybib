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
 * Handle URL Metadata Extraction
 */
function handleUrlSearch($url)
{
    $opts = [
        'http' => [
            'method' => 'GET',
            'header' => 'User-Agent: Babybib/2.0 search agent\r\n',
            'timeout' => 10
        ]
    ];
    $context = stream_context_create($opts);
    $html = @file_get_contents($url, false, $context);

    if ($html === false) {
        throw new Exception("Unable to fetch content from URL");
    }

    $doc = new DOMDocument();
    @$doc->loadHTML('<?xml encoding="utf-8" ?>' . $html);
    $xpath = new DOMXPath($doc);

    $data = [
        'source' => 'url',
        'url' => $url,
        'title' => '',
        'author' => '',
        'publisher' => '', // Actually website name for URL
        'year' => date('Y'),
        'resource_type' => 'webpage'
    ];

    // Try Title
    $titleNode = $doc->getElementsByTagName('title')->item(0);
    if ($titleNode) $data['title'] = trim($titleNode->nodeValue);

    // Meta Tags
    $metas = [
        'og:title' => 'title',
        'og:site_name' => 'publisher',
        'author' => 'author',
        'article:author' => 'author',
        'pubdate' => 'year'
    ];

    foreach ($metas as $name => $key) {
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

    return ['success' => true, 'data' => [$data]];
}

/**
 * Handle DOI Search via CrossRef
 */
function handleDoiSearch($doi)
{
    $url = "https://api.crossref.org/works/" . urlencode($doi);
    $opts = [
        'http' => [
            'header' => 'User-Agent: Babybib/2.0 (mailto:admin@localhost)\r\n'
        ]
    ];
    $context = stream_context_create($opts);
    $response = @file_get_contents($url, false, $context);

    if ($response === false) {
        throw new Exception("DOI not found in CrossRef");
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
 * Handle ISBN/Keyword via Google Books (Reusing logic)
 */
function handleIbnSearch($q)
{
    if (empty($q)) return ['success' => true, 'data' => []];

    $search_q = is_numeric($q) ? "isbn:$q" : $q;
    $url = "https://www.googleapis.com/books/v1/volumes?q=" . urlencode($search_q);

    $opts = [
        'http' => [
            'method' => 'GET',
            'header' => "User-Agent: Babybib/2.0 search agent\r\n",
            'timeout' => 10
        ]
    ];
    $context = stream_context_create($opts);
    $response = @file_get_contents($url, false, $context);

    if ($response === false) throw new Exception("Google Books API Error (Check connection or allow_url_fopen)");

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
