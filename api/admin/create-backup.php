<?php

/**
 * Babybib API - Admin: Create Backup
 * ===================================
 * Uses PHP-based backup for compatibility
 */
header('Content-Type: application/json; charset=utf-8');
require_once '../../includes/session.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
requireAdmin();

$backupDir = __DIR__ . '/../../backups';
$logsDir = __DIR__ . '/../../logs';

// Ensure directories exist
if (!is_dir($backupDir)) {
    if (!mkdir($backupDir, 0755, true)) {
        jsonResponse(['success' => false, 'error' => 'Cannot create backup directory'], 500);
    }
}
if (!is_dir($logsDir)) {
    mkdir($logsDir, 0755, true);
}

// Check if directory is writable
if (!is_writable($backupDir)) {
    jsonResponse(['success' => false, 'error' => 'Backup directory is not writable'], 500);
}

try {
    $db = getDB();

    $date = date('Ymd_His');
    $filename = "babybib_db_{$date}.sql";
    $filepath = "{$backupDir}/{$filename}";

    // Use PHP-based backup (more reliable than exec)
    $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

    if (empty($tables)) {
        throw new Exception('No tables found in database');
    }

    $sqlDump = "-- Babybib Database Backup\n";
    $sqlDump .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
    $sqlDump .= "-- Database: " . DB_NAME . "\n";
    $sqlDump .= "-- Tables: " . count($tables) . "\n\n";
    $sqlDump .= "SET NAMES utf8mb4;\n";
    $sqlDump .= "SET FOREIGN_KEY_CHECKS=0;\n";
    $sqlDump .= "SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\n\n";

    foreach ($tables as $table) {
        // Get CREATE TABLE statement
        $createStmt = $db->query("SHOW CREATE TABLE `{$table}`")->fetch();
        $sqlDump .= "-- Table structure for `{$table}`\n";
        $sqlDump .= "DROP TABLE IF EXISTS `{$table}`;\n";
        $sqlDump .= $createStmt['Create Table'] . ";\n\n";

        // Get data
        $rows = $db->query("SELECT * FROM `{$table}`")->fetchAll(PDO::FETCH_ASSOC);

        if (count($rows) > 0) {
            $sqlDump .= "-- Data for `{$table}`\n";
            $columns = array_keys($rows[0]);
            $columnList = '`' . implode('`, `', $columns) . '`';

            // Batch inserts for efficiency
            $batchSize = 100;
            $batches = array_chunk($rows, $batchSize);

            foreach ($batches as $batch) {
                $values = [];
                foreach ($batch as $row) {
                    $rowValues = array_map(function ($val) use ($db) {
                        if ($val === null) return 'NULL';
                        return $db->quote($val);
                    }, array_values($row));
                    $values[] = '(' . implode(', ', $rowValues) . ')';
                }
                $sqlDump .= "INSERT INTO `{$table}` ({$columnList}) VALUES\n" . implode(",\n", $values) . ";\n";
            }
            $sqlDump .= "\n";
        }
    }

    $sqlDump .= "SET FOREIGN_KEY_CHECKS=1;\n";
    $sqlDump .= "\n-- Backup completed successfully\n";

    // Write to file
    $bytesWritten = file_put_contents($filepath, $sqlDump);

    if ($bytesWritten === false || $bytesWritten === 0) {
        throw new Exception('Failed to write backup file');
    }

    // Compress the backup
    $gzFilepath = $filepath . '.gz';
    $gz = gzopen($gzFilepath, 'w9');

    if ($gz === false) {
        // If gzip fails, keep uncompressed file
        $finalFile = basename($filepath);
        $fileSize = filesize($filepath);
    } else {
        gzwrite($gz, file_get_contents($filepath));
        gzclose($gz);
        unlink($filepath); // Remove uncompressed file

        $finalFile = basename($gzFilepath);
        $fileSize = filesize($gzFilepath);
    }

    logActivity(getCurrentUserId(), 'create_backup', "Created backup: {$finalFile} (" . round($fileSize / 1024, 2) . " KB)");

    jsonResponse([
        'success' => true,
        'message' => 'สร้าง Backup สำเร็จ (' . count($tables) . ' ตาราง)',
        'filename' => $finalFile,
        'size' => $fileSize,
        'tables' => count($tables)
    ]);
} catch (Exception $e) {
    error_log("Backup error: " . $e->getMessage());
    jsonResponse(['success' => false, 'error' => 'Backup failed: ' . $e->getMessage()], 500);
}
