<?php
require_once '../../core/db_config.php';
try {
    $stmt = $pdo->query("SELECT screen_rows FROM settings LIMIT 1");
    echo "Column screen_rows exists.";
} catch (PDOException $e) {
    echo "Column screen_rows MISSING: " . $e->getMessage();
    // Try to add it if missing
    $pdo->exec("ALTER TABLE settings ADD COLUMN screen_rows INT DEFAULT 1");
    echo " - ATTEMPTED ADD.";
}
