<?php
require_once '../../core/db_config.php';

try {
    $sql = "ALTER TABLE products ADD COLUMN image_path VARCHAR(255) DEFAULT NULL";
    $pdo->exec($sql);
    echo "Column image_path added to products table.";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), "Duplicate column") !== false) {
        echo "Column already exists.";
    } else {
        echo "Error: " . $e->getMessage();
    }
}
?>
