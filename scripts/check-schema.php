<?php

/**
 * Babybib schema readiness checker.
 *
 * Run before deployment:
 * php scripts/check-schema.php
 */

require_once dirname(__DIR__) . '/includes/env.php';

$failures = [];
$warnings = [];

function schema_pass(string $message): void
{
    echo "[PASS] {$message}\n";
}

function schema_warn(array &$warnings, string $message): void
{
    $warnings[] = $message;
    echo "[WARN] {$message}\n";
}

function schema_fail(array &$failures, string $message): void
{
    $failures[] = $message;
    echo "[FAIL] {$message}\n";
}

function schema_table_exists(PDO $db, string $table): bool
{
    $stmt = $db->prepare(
        "SELECT COUNT(*)
         FROM information_schema.TABLES
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = ?"
    );
    $stmt->execute([$table]);

    return (int) $stmt->fetchColumn() > 0;
}

function schema_column(PDO $db, string $table, string $column): ?array
{
    $stmt = $db->prepare(
        "SELECT COLUMN_NAME, DATA_TYPE, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT
         FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = ?
           AND COLUMN_NAME = ?"
    );
    $stmt->execute([$table, $column]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return is_array($row) ? $row : null;
}

function schema_index_exists(PDO $db, string $table, string $index): bool
{
    $stmt = $db->prepare(
        "SELECT COUNT(*)
         FROM information_schema.STATISTICS
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = ?
           AND INDEX_NAME = ?"
    );
    $stmt->execute([$table, $index]);

    return (int) $stmt->fetchColumn() > 0;
}

echo "Babybib schema readiness check\n";
echo "==============================\n";

try {
    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=%s',
        env('DB_HOST', 'localhost'),
        env('DB_NAME', 'babybib_db'),
        env('DB_CHARSET', 'utf8mb4')
    );
    $db = new PDO($dsn, env('DB_USER', 'root'), env('DB_PASS', ''), [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (Throwable $e) {
    schema_fail($failures, 'Database connection failed');
    echo "==============================\n";
    echo count($failures) . " failure(s), " . count($warnings) . " warning(s)\n";
    exit(1);
}

$requiredTables = [
    'users',
    'email_verifications',
    'projects',
    'bibliographies',
    'activity_logs',
    'system_settings',
    'user_ratings',
    'support_reports',
    'page_visits',
];

foreach ($requiredTables as $table) {
    if (schema_table_exists($db, $table)) {
        schema_pass("Table exists: {$table}");
    } else {
        schema_fail($failures, "Missing table: {$table}");
    }
}

$requiredColumns = [
    'users' => [
        'profile_picture',
        'is_lis_cmu',
        'student_id',
        'is_verified',
        'token_expiry',
    ],
    'email_verifications' => [
        'user_id',
        'email',
        'code',
        'expires_at',
        'used',
        'verified_at',
        'created_at',
    ],
    'user_ratings' => [
        'user_id',
        'rating',
        'page_url',
        'user_agent',
        'ip_address',
        'session_id',
        'created_at',
        'updated_at',
    ],
    'support_reports' => [
        'user_id',
        'issue_type',
        'subject',
        'description',
        'status',
        'created_at',
        'updated_at',
        'resolved_at',
    ],
    'page_visits' => [
        'visit_date',
        'visit_count',
        'unique_visitors',
        'created_at',
        'updated_at',
    ],
];

foreach ($requiredColumns as $table => $columns) {
    if (!schema_table_exists($db, $table)) {
        continue;
    }

    foreach ($columns as $column) {
        $info = schema_column($db, $table, $column);
        if ($info === null) {
            schema_fail($failures, "Missing column: {$table}.{$column}");
            continue;
        }

        schema_pass("Column exists: {$table}.{$column}");
    }
}

$codeColumn = schema_column($db, 'email_verifications', 'code');
if ($codeColumn !== null && stripos((string) $codeColumn['COLUMN_TYPE'], 'varchar(255)') === false) {
    schema_fail($failures, 'email_verifications.code must support hashed verification codes: VARCHAR(255)');
}

$emailColumn = schema_column($db, 'email_verifications', 'email');
if ($emailColumn !== null && strtoupper((string) $emailColumn['IS_NULLABLE']) !== 'YES') {
    schema_warn($warnings, 'email_verifications.email should be nullable for legacy verification rows');
}

$requiredIndexes = [
    ['email_verifications', 'idx_email_verify_user'],
    ['email_verifications', 'idx_email_verify_code'],
    ['email_verifications', 'idx_email_verify_expires'],
];

foreach ($requiredIndexes as [$table, $index]) {
    if (schema_index_exists($db, $table, $index)) {
        schema_pass("Index exists: {$table}.{$index}");
    } else {
        schema_fail($failures, "Missing index: {$table}.{$index}");
    }
}

echo "==============================\n";
echo count($failures) . " failure(s), " . count($warnings) . " warning(s)\n";

if (!empty($failures)) {
    echo "Schema readiness check failed. Apply database/migrations/20260701_001_production_schema_hardening.sql before deploy.\n";
    exit(1);
}

echo "Schema readiness check passed.\n";
exit(0);
