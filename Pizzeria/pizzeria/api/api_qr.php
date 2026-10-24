<?php
require_once '../core/db_config.php';

header('Content-Type: application/json');

if (!$user_id) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Non autorizzato']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (isset($input['image'])) {
    // 1. Decodifica Base64
    $data = $input['image'];
    list($type, $data) = explode(';', $data);
    list(, $data)      = explode(',', $data);
    $data = base64_decode($data);
    
    $rel_path = 'uploads/qrcodes/menu_qr_' . time() . '.png';
    $filename = '../' . $rel_path;

    // 2. Salvataggio del file PNG
    if (file_put_contents($filename, $data)) {
        
        // 3. Aggiunta del Logo Personalizzato (richiede una libreria di manipolazione immagini come GD o ImageMagick)
        // Poiché non possiamo assumere librerie complesse in XAMPP, usiamo una soluzione semplice che necessita di GD (solitamente incluso in XAMPP)
        
        $logo_path = '../uploads/logo_pizzeria.png';
        // Controllo se l'estensione GD è attiva per la manipolazione immagini
        if (file_exists($logo_path) && function_exists('imagecreatefrompng')) {
            $qr_image = @imagecreatefrompng($filename);
            $logo_image = @imagecreatefrompng($logo_path);
            
            if ($qr_image && $logo_image) {
                $qr_width = imagesx($qr_image);
                $qr_height = imagesy($qr_image);
                $logo_width = imagesx($logo_image);
                $logo_height = imagesy($logo_image);

                // Calcola le dimensioni e la posizione del logo (es. 20% della dimensione QR)
                $target_size = $qr_width * 0.20;
                $scale = $target_size / $logo_width;
                $new_logo_width = $target_size;
                $new_logo_height = $logo_height * $scale;
                
                $dest_x = ($qr_width - $new_logo_width) / 2;
                $dest_y = ($qr_height - $new_logo_height) / 2;

                // Copia il logo ridimensionato sul QR code
                imagecopyresampled($qr_image, $logo_image, $dest_x, $dest_y, 0, 0, $new_logo_width, $new_logo_height, $logo_width, $logo_height);

                // Sovrascrivi il file PNG con il logo
                imagepng($qr_image, $filename);
                imagedestroy($qr_image);
                imagedestroy($logo_image);
            }
        }
        
        // 4. Aggiorna DB con il percorso del QR e l'URL utilizzato
        $stmt = $pdo->prepare("UPDATE settings SET qr_path = ?, qr_url = ? WHERE user_id = ?");
        $stmt->execute([$rel_path, $input['qrUrl'], $user_id]);

        // 5. Pulizia vecchi QR Code (mantieni solo i 5 più recenti)
        $dir = '../uploads/qrcodes/';
        $files = glob($dir . 'menu_qr_*.png');
        if (count($files) > 5) {
            usort($files, function($a, $b) {
                return filemtime($b) - filemtime($a);
            });
            $to_delete = array_slice($files, 5);
            foreach ($to_delete as $f) {
                @unlink($f);
            }
        }
        
        echo json_encode(['status' => 'success', 'path' => $rel_path, 'url' => $input['qrUrl']]);
        
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Impossibile salvare il file QR.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Dati immagine mancanti.']);
}
?>