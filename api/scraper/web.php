<?php
header('Content-Type: application/json');
require_once '../../includes/session.php';

/**
 * Babybib - Web Scraper API
 * ==========================
 * Fetches metadata from a URL to auto-fill bibliography forms.
 */

$url = $_GET['url'] ?? '';

if (empty($url)) {
    echo json_encode(['success' => false, 'message' => 'URL is required']);
    exit;
}

if (!filter_var($url, FILTER_VALIDATE_URL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid URL']);
    exit;
}

function fetchUrl($url)
{
    if (!function_exists('curl_init')) {
        return @file_get_contents($url);
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36 BabybibScraper/1.0');
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For local testing and broader compatibility
    $html = curl_exec($ch);
    curl_close($ch);
    return $html;
}

$html = fetchUrl($url);

if (!$html) {
    echo json_encode(['success' => false, 'message' => 'Could not fetch content']);
    exit;
}

// Set up DOM document for parsing
libxml_use_internal_errors(true);
$doc = new DOMDocument();
// Use mb_convert_encoding to handle Thai/UTF-8 correctly if needed
$doc->loadHTML('<?xml encoding="UTF-8">' . $html);
libxml_clear_errors();

$metadata = [
    'title' => '',
    'author' => '',
    'year' => '',
    'month' => '',
    'day' => '',
    'website_name' => '',
    'url' => $url
];

// 1. Try JSON-LD (Most accurate for modern sites)
$xpath = new DOMXPath($doc);
$scripts = $xpath->query('//script[@type="application/ld+json"]');
foreach ($scripts as $script) {
    if (empty($script->nodeValue)) continue;
    $json = json_decode($script->nodeValue, true);
    if ($json) {
        // Flatten if it's a @graph
        if (isset($json['@graph'])) {
            foreach ($json['@graph'] as $g) {
                if (isset($g['@type']) && in_array($g['@type'], ['Article', 'WebPage', 'VideoObject', 'BlogPosting', 'NewsArticle'])) {
                    $json = $g;
                    break;
                }
            }
        }

        if (isset($json['headline']) || isset($json['name'])) {
            $metadata['title'] = $json['headline'] ?? $json['name'];

            // Authors
            if (isset($json['author'])) {
                if (isset($json['author']['name'])) {
                    $metadata['author'] = $json['author']['name'];
                } else if (is_array($json['author']) && isset($json['author'][0]['name'])) {
                    $metadata['author'] = $json['author'][0]['name'];
                } else if (is_string($json['author'])) {
                    $metadata['author'] = $json['author'];
                }
            }

            // Publisher / Site Name
            if (isset($json['publisher']['name'])) {
                $metadata['website_name'] = $json['publisher']['name'];
            }

            // Date
            $dateField = $json['datePublished'] ?? $json['dateModified'] ?? $json['uploadDate'] ?? '';
            if ($dateField) {
                $timestamp = strtotime($dateField);
                if ($timestamp) {
                    $metadata['year'] = date('Y', $timestamp);
                    $metadata['month'] = date('m', $timestamp);
                    $metadata['day'] = date('d', $timestamp);
                }
            }

            if (!empty($metadata['title'])) break; // Found enough data
        }
    }
}

// 2. Fallback to Meta Tags if JSON-LD missing or incomplete
if (empty($metadata['title'])) {
    $titleNodes = $doc->getElementsByTagName('title');
    if ($titleNodes->length > 0) {
        $metadata['title'] = trim($titleNodes->item(0)->nodeValue);
    }
}

$metas = $doc->getElementsByTagName('meta');
foreach ($metas as $meta) {
    $property = strtolower($meta->getAttribute('property'));
    $name = strtolower($meta->getAttribute('name'));
    $content = $meta->getAttribute('content');

    if (empty($content)) continue;

    // Title (Social tags often have cleaner titles)
    if (empty($metadata['title']) || $property == 'og:title' || $name == 'twitter:title') {
        if ($property == 'og:title' || $name == 'twitter:title') {
            $metadata['title'] = $content;
        }
    }

    // Author
    if (empty($metadata['author'])) {
        if ($name == 'author' || $property == 'article:author' || $name == 'citation_author' || $name == 'twitter:creator') {
            $metadata['author'] = $content;
        }
    }

    // Date / Publication Time
    if (empty($metadata['year'])) {
        if ($property == 'article:published_time' || $name == 'pubdate' || $name == 'date' || $name == 'citation_date' || $property == 'og:updated_time') {
            $timestamp = strtotime($content);
            if ($timestamp) {
                $metadata['year'] = date('Y', $timestamp);
                $metadata['month'] = date('m', $timestamp);
                $metadata['day'] = date('d', $timestamp);
            }
        }
    }

    // Site Name
    if (empty($metadata['website_name'])) {
        if ($property == 'og:site_name' || $name == 'application-name') {
            $metadata['website_name'] = $content;
        }
    }
}

// 3. Fallback for Site Name from Domain
if (empty($metadata['website_name'])) {
    $domain = parse_url($url, PHP_URL_HOST);
    $metadata['website_name'] = str_replace('www.', '', $domain);
}

// Clean up title (remove site name suffixes like " - The Standard")
if (!empty($metadata['website_name']) && !empty($metadata['title'])) {
    $metadata['title'] = preg_replace('/ [|-] ' . preg_quote($metadata['website_name'], '/') . '$/i', '', $metadata['title']);
}

echo json_encode([
    'success' => true,
    'data' => $metadata
]);
