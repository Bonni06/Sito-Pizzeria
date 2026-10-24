<?php
require_once '../core/db_config.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

$user_id = 1;

$stmt = $pdo->prepare("SELECT id, name FROM pizzas WHERE name LIKE '%prova%'");
$stmt->execute();
$pizzas = $stmt->fetchAll();

if (empty($pizzas)) {
    echo "Nessuna pizza 'prova' trovata.";
    exit;
}

foreach ($pizzas as $pizza) {
    $id = $pizza['id'];
    echo "Trovata pizza '{$pizza['name']}' (ID: $id). Tento l'eliminazione...<br>\n";
    try {
        $pdo->beginTransaction();
        $pdo->prepare("DELETE FROM pizza_ingredients WHERE pizza_id = ?")->execute([$id]);
        $stmt = $pdo->prepare("DELETE FROM pizzas WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $user_id]);
        $pdo->commit();
        echo "Pizza eliminata con successo.<br>\n";
    } catch (\PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo "Errore PDO durante l'eliminazione: " . $e->getMessage() . "<br>\n";
    } catch (\Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo "Altro Errore durante l'eliminazione: " . $e->getMessage() . "<br>\n";
    }
}
?>
