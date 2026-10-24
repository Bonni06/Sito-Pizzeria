<?php
require_once '../core/db_config.php';
header('Content-Type: application/json');

$category = $_GET['category'] ?? '';

if (!$user_id) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$items = [];

if ($category === 'classiche') {
    $stmt = $pdo->prepare("SELECT id, name FROM pizzas WHERE user_id = ? AND type IN ('classica', 'famiglia') ORDER BY name");
    $stmt->execute([$user_id]);
    $items = $stmt->fetchAll();
} elseif ($category === 'speciali') {
    $stmt = $pdo->prepare("SELECT id, name FROM pizzas WHERE user_id = ? AND type IN ('gourmet', 'scrocchiarella') ORDER BY name");
    $stmt->execute([$user_id]);
    $items = $stmt->fetchAll();
} elseif ($category === 'stuzzicherie') {
    $stmt = $pdo->prepare("SELECT id, name FROM products WHERE user_id = ? AND category IN ('stuzzicheria', 'focaccia') ORDER BY name");
    $stmt->execute([$user_id]);
    $items = $stmt->fetchAll();
} else {
    echo json_encode([]);
    exit;
}

echo json_encode($items);
?>
