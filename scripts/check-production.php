<?php

/**
 * Babybib production readiness checker.
 *
 * Run before deployment:
 * php scripts/check-production.php
 */

require_once dirname(__DIR__) . '/includes/env.php';

$root = dirname(__DIR__);
$failures = [];
$warnings = [];

function check_pass(string $message): void
{
    echo "[PASS] {$message}\n";
}

function check_warn(array &$warnings, string $message): void
{
    $warnings[] = $message;
    echo "[WARN] {$message}\n";
}

function check_fail(array &$failures, string $message): void
{
    $failures[] = $message;
    echo "[FAIL] {$message}\n";
}

function env_string(string $key, string $default = ''): string
{
    $value = env($key, $default);
    if (is_bool($value)) {
        return $value ? 'true' : 'false';
    }

    return trim((string) $value);
}

echo "Babybib production readiness check\n";
echo "==================================\n";

$siteEnv = env_string('SITE_ENV', 'development');
if ($siteEnv === 'production') {
    check_pass('SITE_ENV is production');
} else {
    check_fail($failures, 'SITE_ENV must be production before deploy');
}

$debugMode = strtolower(env_string('DEBUG_MODE', 'false'));
if (in_array($debugMode, ['0', 'false', 'no', 'off', ''], true)) {
    check_pass('DEBUG_MODE is disabled');
} else {
    check_fail($failures, 'DEBUG_MODE must be false before deploy');
}

$appKey = env_string('APP_KEY');
if ($appKey === '') {
    check_fail($failures, 'APP_KEY is missing');
} elseif ($appKey === 'babybib-change-this-app-key') {
    check_fail($failures, 'APP_KEY still uses the default development value');
} elseif (strlen($appKey) < 32) {
    check_fail($failures, 'APP_KEY should be at least 32 characters');
} else {
    check_pass('APP_KEY is present and non-default');
}

$siteUrl = env_string('SITE_URL');
if ($siteUrl === '') {
    check_fail($failures, 'SITE_URL is missing');
} elseif (stripos($siteUrl, 'https://') === 0) {
    check_pass('SITE_URL uses HTTPS');
} else {
    check_warn($warnings, 'SITE_URL does not use HTTPS. Only acceptable for local development');
}

$sessionSecure = env_string('SESSION_COOKIE_SECURE', '0');
if (stripos($siteUrl, 'https://') === 0 && !in_array($sessionSecure, ['1', 'true', 'yes', 'on'], true)) {
    check_fail($failures, 'SESSION_COOKIE_SECURE must be 1 when SITE_URL uses HTTPS');
} elseif (stripos($siteUrl, 'https://') === 0) {
    check_pass('SESSION_COOKIE_SECURE is enabled for HTTPS');
} else {
    check_warn($warnings, 'SESSION_COOKIE_SECURE is not required unless production uses HTTPS');
}

$dbUser = env_string('DB_USER', env_string('DB_USERNAME', ''));
if ($dbUser === '') {
    check_fail($failures, 'DB_USER is missing');
} elseif (strtolower($dbUser) === 'root') {
    check_fail($failures, 'DB_USER must not be root in production');
} else {
    check_pass('DB_USER is not root');
}

$requiredExtensions = ['pdo_mysql', 'curl', 'mbstring', 'json', 'dom', 'libxml', 'zip'];
foreach ($requiredExtensions as $extension) {
    if (extension_loaded($extension)) {
        check_pass("PHP extension loaded: {$extension}");
    } else {
        check_fail($failures, "Missing PHP extension: {$extension}");
    }
}

$writableDirs = ['tmp', 'logs', 'backups'];
foreach ($writableDirs as $dir) {
    $path = $root . '/' . $dir;
    if (!is_dir($path)) {
        check_fail($failures, "Required directory missing: {$dir}");
        continue;
    }

    if (!is_writable($path)) {
        check_fail($failures, "Required directory is not writable: {$dir}");
        continue;
    }

    check_pass("Directory is writable: {$dir}");
}

$protectedDirs = ['backups', 'logs', 'uploads'];
foreach ($protectedDirs as $dir) {
    $htaccess = $root . '/' . $dir . '/.htaccess';
    if (is_file($htaccess)) {
        check_pass("Directory has .htaccess protection: {$dir}");
    } else {
        check_fail($failures, "Directory is missing .htaccess protection: {$dir}");
    }
}

$envFile = $root . '/.env';
if (is_file($envFile)) {
    check_pass('.env file exists');
} else {
    check_fail($failures, '.env file is missing');
}

echo "==================================\n";
echo count($failures) . " failure(s), " . count($warnings) . " warning(s)\n";

if (!empty($failures)) {
    echo "Production readiness check failed.\n";
    exit(1);
}

echo "Production readiness check passed.\n";
exit(0);

