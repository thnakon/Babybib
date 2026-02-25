<?php

/**
 * Babybib - Security Headers
 * ==========================
 * Important security headers for production deployment
 * Include this file at the top of header.php or config.php
 */

// Skip security headers for API endpoints (they set their own headers)
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
if (strpos($requestUri, '/api/') !== false) {
    return;
}

// Prevent MIME type sniffing
header("X-Content-Type-Options: nosniff");

// Prevent clickjacking attacks
header("X-Frame-Options: SAMEORIGIN");

// Enable XSS filtering
header("X-XSS-Protection: 1; mode=block");

// Control referrer information
header("Referrer-Policy: strict-origin-when-cross-origin");

// Permissions Policy (formerly Feature-Policy)
header("Permissions-Policy: geolocation=(), microphone=(), camera=()");

// Content Security Policy (adjust as needed for your resources)
// This is a basic policy - modify based on your actual resource sources
$cspPolicy = [
    "default-src 'self'",
    "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://www.google.com https://www.gstatic.com https://cdn.tailwindcss.com https://unpkg.com",
    "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://cdn.tailwindcss.com",
    "font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com",
    "img-src 'self' data: https:",
    "connect-src 'self' https://ka-f.fontawesome.com",
    "frame-src 'self' https://www.google.com",
    "object-src 'none'",
    "base-uri 'self'",
    "form-action 'self'"
];
header("Content-Security-Policy: " . implode("; ", $cspPolicy));

// HTTPS only (uncomment when using SSL)
// header("Strict-Transport-Security: max-age=31536000; includeSubDomains");

// Additional security settings for cookies (PHP configuration)
if (session_status() === PHP_SESSION_NONE) {
    // Set secure cookie parameters before starting session
    $cookieParams = session_get_cookie_params();
    session_set_cookie_params([
        'lifetime' => $cookieParams['lifetime'],
        'path' => $cookieParams['path'],
        'domain' => $cookieParams['domain'],
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
}
