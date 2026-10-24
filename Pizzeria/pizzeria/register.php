<?php
require_once 'core/db_config.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // 1. Validazione base
    if (empty($username) || empty($password)) {
        $message = "Nome utente e password sono obbligatori.";
    } 
    // 2. Controllo se l'utente esiste già
    else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        
        if ($stmt->fetch()) {
            $message = "Nome utente già esistente. Scegline un altro.";
        } else {
            // 3. Hash della password
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            
            // 4. Inserimento Utente
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $stmt->execute([$username, $hashed_password]);
            
            $new_user_id = $pdo->lastInsertId();
            
            // 5. Inserimento Impostazioni di Default per il nuovo utente
            $stmt_settings = $pdo->prepare("INSERT INTO settings (user_id, pizzeria_name) VALUES (?, ?)");
            $stmt_settings->execute([$new_user_id, "La Pizzeria di " . $username]);
            
            $pdo->commit();
            
            $message = "Registrazione completata! Puoi ora accedere.";
            // Reindirizza al login dopo 3 secondi
            header("refresh:3;url=admin.php"); 
        }
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <title>Registrazione Admin</title>
    <style>
        body { font-family: sans-serif; background: #f4f6f9; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .register-box { background: white; padding: 40px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); width: 350px; text-align: center; }
        input { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        button { background: #2ecc71; color: white; padding: 12px; border: none; border-radius: 4px; cursor: pointer; width: 100%; font-size: 16px; }
        .message { color: green; margin-bottom: 10px; }
        .error { color: red; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="register-box">
        <h2>Registrazione Nuova Pizzeria</h2>
        <?php if ($message): ?>
            <p class="message <?php echo strpos($message, 'completata') !== false ? '' : 'error'; ?>"><?php echo $message; ?></p>
        <?php endif; ?>
        <form method="POST">
            <input type="hidden" name="register" value="1">
            <input type="text" name="username" placeholder="Nome Utente Desiderato" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Registrati</button>
        </form>
        <p style="margin-top: 15px; font-size: 0.9em;"><a href="admin.php">Torna al Login</a></p>
    </div>
</body>
</html>