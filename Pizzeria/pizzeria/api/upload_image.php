<?php
require_once '../core/db_config.php';
require_once '../core/functions.php';

header('Content-Type: application/json');

if (!$user_id) {
    echo json_encode(['status' => 'error', 'message' => 'Non autorizzato']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Metodo non consentito']);
    exit;
}

$category = $_POST['category'] ?? '';
$category_id = $_POST['category_id'] ?? null;

if (empty($category) && empty($category_id)) {
    echo json_encode(['status' => 'error', 'message' => 'Categoria mancante']);
    exit;
}

if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['status' => 'error', 'message' => 'Errore nel caricamento del file']);
    exit;
}

$file = $_FILES['image'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$allowed = ['jpg', 'jpeg', 'png', 'webp'];

if (!in_array($ext, $allowed)) {
    echo json_encode(['status' => 'error', 'message' => 'Formato file non supportato (solo JPG, PNG, WEBP)']);
    exit;
}

$upload_dir = '../uploads/categories/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Nome file unico: cat_userid_timestamp.ext
$prefix = $category_id ? "cat_{$category_id}" : $category;
$filename = $prefix . '_' . $user_id . '_' . time() . '.' . $ext;
$target_path = $upload_dir . $filename;

if (move_uploaded_file($file['tmp_name'], $target_path)) {
    $db_path = 'uploads/categories/' . $filename;
    
    if ($category_id) {
        // Aggiorna tabella pizza_categories
        $stmt = $pdo->prepare("UPDATE pizza_categories SET image_path = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$db_path, $category_id, $user_id]);
    } else {
        // Vecchio sistema: aggiorna settings
        $col_map = [
            'classiche' => 'img_classiche',
            'stuzzicherie' => 'img_stuzzicherie',
            'speciali' => 'img_speciali'
        ];
        
        if (isset($col_map[$category])) {
            $column = $col_map[$category];
            $stmt = $pdo->prepare("UPDATE settings SET $column = ? WHERE user_id = ?");
            $stmt->execute([$db_path, $user_id]);
        }
    }
    
    echo json_encode(['status' => 'success', 'message' => 'Immagine caricata con successo', 'path' => $db_path]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Errore nel salvataggio su disco']);
}
?>
