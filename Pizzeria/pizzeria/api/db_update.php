<?php
require_once '../core/db_config.php';

try {
    // 1. Add enable_second_screen to settings
    $pdo->exec("ALTER TABLE settings ADD COLUMN enable_second_screen TINYINT(1) DEFAULT 0");
    echo "Added enable_second_screen. ";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "enable_second_screen exists. ";
    } else {
        echo "Error settings screen: " . $e->getMessage() . " ";
    }
}

try {
    // 2. Add screen_stuzzicherie to settings
    $pdo->exec("ALTER TABLE settings ADD COLUMN screen_stuzzicherie INT DEFAULT 1");
    echo "Added screen_stuzzicherie. ";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "screen_stuzzicherie exists. ";
    } else {
        echo "Error stuzzicherie screen: " . $e->getMessage() . " ";
    }
}

try {
    // 3. Add screen_number to pizza_categories
    $pdo->exec("ALTER TABLE pizza_categories ADD COLUMN screen_number INT DEFAULT 1");
    echo "Added screen_number. ";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "screen_number exists. ";
    } else {
        echo "Error category screen: " . $e->getMessage() . " ";
    }
}

echo "DB Setup Complete.";
