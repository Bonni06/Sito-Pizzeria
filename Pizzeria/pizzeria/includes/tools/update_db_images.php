<?php
require_once '../../core/db_config.php';

try {
    $sql = "ALTER TABLE settings 
            ADD COLUMN img_classiche VARCHAR(255) DEFAULT NULL,
            ADD COLUMN img_stuzzicherie VARCHAR(255) DEFAULT NULL,
            ADD COLUMN img_speciali VARCHAR(255) DEFAULT NULL";
    
    $pdo->exec($sql);
    echo "Database updated successfully: Added image columns.";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), "Duplicate column") !== false) {
        echo "Columns already exist. Skipping.";
    } else {
        echo "Error updating database: " . $e->getMessage();
    }
}
?>
