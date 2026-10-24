<?php
require_once '../../core/db_config.php';
$stmt = $pdo->query("SHOW COLUMNS FROM pizzas");
foreach($stmt as $row) {
    echo $row['Field'] . "\n";
}
?>
