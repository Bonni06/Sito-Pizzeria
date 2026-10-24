<?php
require_once '../../core/db_config.php';

try {
    $sql = "
    ALTER TABLE settings
    ADD COLUMN screen_rows INT DEFAULT 1;
    ";

    $pdo->exec($sql);
    echo "Column screen_rows added successfully.";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Column already exists.";
    } else {
        echo "Error: " . $e->getMessage();
    }
}
