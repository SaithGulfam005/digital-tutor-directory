<?php
/**
 * Avatar Fix Script
 * Ensures all users have proper avatar paths set
 * Run this once to fix existing database records
 */

declare(strict_types=1);

require_once __DIR__ . '/../components/config.php';
require_once __DIR__ . '/../components/require-admin.php';

if (!db_available()) {
    json_response(['error' => 'Database not available'], 503);
}

try {
    // Update users with null or empty avatar to use default
    $stmt = db()->prepare("UPDATE users SET avatar = 'assets/images/teachers/placeholder.jpg' 
                         WHERE avatar IS NULL OR avatar = '' OR avatar = '0'");
    $stmt->execute();
    $updated = db()->query("SELECT FOUND_ROWS()")->fetchColumn();

    // Ensure avatar field exists and has correct default
    db()->query("ALTER TABLE users MODIFY avatar VARCHAR(255) DEFAULT 'assets/images/teachers/placeholder.jpg'");

    json_response([
        'success' => true,
        'message' => 'Avatar paths fixed successfully',
        'updated_records' => $updated ?? 0,
    ]);
} catch (Throwable $e) {
    json_response(['error' => $e->getMessage()], 500);
}
