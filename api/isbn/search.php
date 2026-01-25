<?php

/**
 * Babybib API - Search by ISBN
 * ===========================
 */

header('Content-Type: application/json; charset=utf-8');

require_once '../../includes/session.php';
require_once '../../includes/functions.php';

$isbn = $_GET['isbn'] ?? '';

if (empty($isbn)) {
    jsonResponse(['success' => false, 'error' => 'ISBN is required'], 400);
}

// Clean ISBN (remove hyphens and spaces)
$isbn = str_replace(['-', ' '], '', $isbn);

// Validate ISBN length (approximate)
if (strlen($isbn) < 10) {
    jsonResponse(['success' => false, 'error' => 'Invalid ISBN length'], 400);
}

// Google Books API URL
$url = "https://www.googleapis.com/books/v1/volumes?q=isbn:" . urlencode($isbn);

try {
    // Using file_get_contents with a timeout and user agent
    $opts = [
        'http' => [
            'method' => 'GET',
            'header' => 'User-Agent: Babybib/2.0 search agent\r\n',
            'timeout' => 5
        ]
    ];
    $context = stream_context_create($opts);
    $response = @file_get_contents($url, false, $context);

    if ($response === false) {
        // Fallback or handle error
        throw new Exception("Unable to connect to Google Books API");
    }

    $data = json_decode($response, true);

    if (!isset($data['items']) || count($data['items']) === 0) {
        jsonResponse(['success' => true, 'data' => []]);
    }

    $results = [];
    foreach ($data['items'] as $item) {
        $volumeInfo = $item['volumeInfo'];

        $authors = [];
        if (isset($volumeInfo['authors'])) {
            foreach ($volumeInfo['authors'] as $authorName) {
                // Try to split name into first and last
                $parts = explode(' ', trim($authorName));
                if (count($parts) > 1) {
                    $lastName = array_pop($parts);
                    $firstName = implode(' ', $parts);
                } else {
                    $firstName = $authorName;
                    $lastName = '';
                }
                $authors[] = [
                    'firstName' => $firstName,
                    'lastName' => $lastName,
                    'display' => $authorName
                ];
            }
        }

        $results[] = [
            'id' => $item['id'],
            'title' => ($volumeInfo['title'] ?? '') . (isset($volumeInfo['subtitle']) ? ': ' . $volumeInfo['subtitle'] : ''),
            'authors' => $authors,
            'publisher' => $volumeInfo['publisher'] ?? '',
            // publishedDate can be 2024 or 2024-05-12
            'year' => isset($volumeInfo['publishedDate']) ? substr($volumeInfo['publishedDate'], 0, 4) : '',
            'pages' => $volumeInfo['pageCount'] ?? '',
            'description' => $volumeInfo['description'] ?? '',
            'thumbnail' => $volumeInfo['imageLinks']['thumbnail'] ?? ''
        ];
    }

    jsonResponse(['success' => true, 'data' => $results]);
} catch (Exception $e) {
    error_log("ISBN Search API error: " . $e->getMessage());
    jsonResponse(['success' => false, 'error' => 'เกิดข้อผิดพลาดในการเชื่อมต่อ: ' . $e->getMessage()], 500);
}
