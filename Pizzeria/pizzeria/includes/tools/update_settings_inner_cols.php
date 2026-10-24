<?php
require_once '../../core/db_config.php';

try {
    $sql = "
    ALTER TABLE settings
    ADD COLUMN inner_cols_classiche INT DEFAULT 1,
    ADD COLUMN inner_cols_stuzzicherie INT DEFAULT 1,
    ADD COLUMN inner_cols_speciali INT DEFAULT 1;
    ";

    $pdo->exec($sql);
    echo "Columns added successfully.";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Columns already exist.";
    } else {
        echo "Error: " . $e->getMessage();
    }
}
