<?php

/**
 * Babybib Environment Loader
 * ===========================
 * Loads .env file and makes values accessible via env() function
 */

/**
 * Load environment file
 */
function loadEnv($path)
{
    if (!file_exists($path)) {
        return false;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        // Parse KEY=value
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            // Remove surrounding quotes
            if (preg_match('/^"(.*)"$/', $value, $matches)) {
                $value = $matches[1];
            } elseif (preg_match("/^'(.*)'$/", $value, $matches)) {
                $value = $matches[1];
            }

            // Convert boolean strings
            if (strtolower($value) === 'true') {
                $value = true;
            } elseif (strtolower($value) === 'false') {
                $value = false;
            }

            // Store in $_ENV and putenv
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }

    return true;
}

/**
 * Get environment variable with default fallback
 */
function env($key, $default = null)
{
    $value = $_ENV[$key] ?? getenv($key);

    if ($value === false || $value === null) {
        return $default;
    }

    // Convert string booleans
    if (is_string($value)) {
        $lower = strtolower($value);
        if ($lower === 'true') return true;
        if ($lower === 'false') return false;
        if ($lower === 'null') return null;
    }

    return $value;
}

// Auto-load .env file from project root
$envPath = dirname(__DIR__) . '/.env';
if (file_exists($envPath)) {
    loadEnv($envPath);
}
