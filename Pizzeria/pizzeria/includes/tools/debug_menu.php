<?php
require_once '../../core/db_config.php';

$uid = 1;

// 1. Check Settings
$stmt = $pdo->prepare("SELECT * FROM settings WHERE user_id = ?");
$stmt->execute([$uid]);
$s = $stmt->fetch(PDO::FETCH_ASSOC);

echo "--- SETTINGS ---\n";
echo "Screen Rows: " . $s['screen_rows'] . "\n";
echo "Screen Cols: " . $s['screen_columns'] . "\n";
echo "Classiche: Row={$s['row_classiche']}, Col={$s['col_classiche']}, Span={$s['span_classiche']}, InnerCols={$s['inner_cols_classiche']}\n";
echo "Stuzzicherie: Row={$s['row_stuzzicherie']}, Col={$s['col_stuzzicherie']}, Span={$s['span_stuzzicherie']}, InnerCols={$s['inner_cols_stuzzicherie']}\n";
echo "Speciali: Row={$s['row_speciali']}, Col={$s['col_speciali']}, Span={$s['span_speciali']}, InnerCols={$s['inner_cols_speciali']}\n";

// 2. Check Pizzas Classiche
$stmt = $pdo->prepare("SELECT count(*) as count FROM pizzas WHERE user_id = ? AND type IN ('classica', 'famiglia') AND is_visible=1");
$stmt->execute([$uid]);
$count = $stmt->fetchColumn();
echo "\n--- PIZZAS CLASSICHE ---\n";
echo "Count Visible: " . $count . "\n";

// 3. Check Pizzas Speciali
$stmt = $pdo->prepare("SELECT count(*) as count FROM pizzas WHERE user_id = ? AND type IN ('gourmet', 'scrocchiarella') AND is_visible=1");
$stmt->execute([$uid]);
$countSpec = $stmt->fetchColumn();
echo "\n--- PIZZAS SPECIALI ---\n";
echo "Count Visible: " . $countSpec . "\n";

$stmt = $pdo->prepare("SELECT * FROM pizzas WHERE user_id = ? AND type IN ('classica', 'famiglia') AND is_visible=1 LIMIT 5");
$stmt->execute([$uid]);
$pizzas = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "\n--- SAMPLE PIZZAS ---\n";
print_r($pizzas);
