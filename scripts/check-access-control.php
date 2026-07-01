<?php

/**
 * Babybib access-control readiness checker.
 *
 * Run before deployment:
 * php scripts/check-access-control.php
 */

$root = dirname(__DIR__);
$failures = [];
$warnings = [];

function access_pass(string $message): void
{
    echo "[PASS] {$message}\n";
}

function access_warn(array &$warnings, string $message): void
{
    $warnings[] = $message;
    echo "[WARN] {$message}\n";
}

function access_fail(array &$failures, string $message): void
{
    $failures[] = $message;
    echo "[FAIL] {$message}\n";
}

function access_php_files(string $dir): array
{
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    $files = [];
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $files[] = $file->getPathname();
        }
    }
    sort($files);

    return $files;
}

function access_relative(string $root, string $file): string
{
    return ltrim(str_replace($root, '', $file), DIRECTORY_SEPARATOR);
}

echo "Babybib access-control readiness check\n";
echo "======================================\n";

$apiFiles = access_php_files($root . '/api');

foreach (access_php_files($root . '/api/admin') as $file) {
    $relative = access_relative($root, $file);
    $source = file_get_contents($file) ?: '';
    if (str_contains($source, 'requireAdmin()') || str_contains($source, 'isAdmin()')) {
        access_pass("Admin endpoint guarded: {$relative}");
    } else {
        access_fail($failures, "Admin endpoint missing admin guard: {$relative}");
    }
}

$stateChangingMethods = ["'POST'", '"POST"', "'PUT'", '"PUT"', "'PATCH'", '"PATCH"', "'DELETE'", '"DELETE"'];
foreach ($apiFiles as $file) {
    $relative = access_relative($root, $file);
    $source = file_get_contents($file) ?: '';
    $isStateChanging = false;
    foreach ($stateChangingMethods as $method) {
        if (str_contains($source, $method)) {
            $isStateChanging = true;
            break;
        }
    }

    if (!$isStateChanging) {
        continue;
    }

    if (str_contains($source, 'includes/session.php')) {
        access_pass("State-changing endpoint uses session gate: {$relative}");
    } else {
        access_fail($failures, "State-changing endpoint does not include session gate: {$relative}");
    }
}

$ownershipPatterns = [
    '/api/projects/' => 'user_id',
    '/api/bibliography/' => 'user_id',
];

foreach ($ownershipPatterns as $pathPart => $requiredToken) {
    foreach ($apiFiles as $file) {
        if (!str_contains($file, $pathPart)) {
            continue;
        }

        $relative = access_relative($root, $file);
        $source = file_get_contents($file) ?: '';
        if (str_contains($source, $requiredToken)) {
            access_pass("Ownership token present: {$relative}");
        } else {
            access_warn($warnings, "Review ownership filter manually: {$relative}");
        }
    }
}

echo "======================================\n";
echo count($failures) . " failure(s), " . count($warnings) . " warning(s)\n";

if (!empty($failures)) {
    echo "Access-control readiness check failed.\n";
    exit(1);
}

echo "Access-control readiness check passed.\n";
exit(0);
