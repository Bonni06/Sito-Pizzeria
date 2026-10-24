<?php
// Avvia la sessione per la gestione dell'utente
session_start();

$host = 'localhost';
$db   = 'pizzeria'; // Nome del database che hai importato
$user = 'root'; 
$pass = ''; // Password di default per XAMPP
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // Gestione dell'errore (importante per XAMPP)
    die("Errore di connessione al Database: " . $e->getMessage());
}

// Ottieni l'ID dell'utente loggato (usato in tutte le query)
$user_id = $_SESSION['user_id'] ?? null;
?>