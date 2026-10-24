<?php
require_once '../core/db_config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid method']);
    exit;
}

$category = $_POST['category'] ?? '';
$item_id = $_POST['item_id'] ?? 0;

if (!$item_id || !$category) {
    echo json_encode(['status' => 'error', 'message' => 'Missing parameters']);
    exit;
}

if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['status' => 'error', 'message' => 'File upload error']);
    exit;
}

$file = $_FILES['image'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$allowed = ['jpg', 'jpeg', 'png', 'webp'];

if (!in_array($ext, $allowed)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid file type']);
    exit;
}

$upload_dir = '../uploads/items/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Filename: type_id_timestamp.ext
$type_prefix = ($category === 'stuzzicherie') ? 'prod' : 'pizza';
$filename = $type_prefix . '_' . $item_id . '_' . time() . '.' . $ext;
$target_path = $upload_dir . $filename;
$db_path = 'uploads/items/' . $filename;

if (move_uploaded_file($file['tmp_name'], $target_path)) {
    // Update DB
    $table = ($category === 'stuzzicherie') ? 'products' : 'pizzas';
    
    $stmt = $pdo->prepare("UPDATE $table SET image_path = ? WHERE id = ? AND user_id = ?");
    $stmt->execute([$db_path, $item_id, $user_id]);
    
    echo json_encode(['status' => 'success', 'message' => 'Image uploaded successfully']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to move uploaded file']);
}
?>
