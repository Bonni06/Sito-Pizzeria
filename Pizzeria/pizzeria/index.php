<?php
/**
 * ######################################################################
 * # FILE: index.php
 * # Pagina di atterraggio principale del sito (pizzeria/)
 * # Reindirizza immediatamente al Menu Online
 * ######################################################################
 */

// URL di destinazione
$target_url = 'views/menu_online.php';

// Reindirizzamento tramite header HTTP (il modo più veloce e pulito)
header("Location: " . $target_url);
exit;

// In alternativa, se volessi un messaggio prima di reindirizzare:
/*
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta http-equiv="refresh" content="3;url=<?php echo $target_url; ?>">
    <title>Benvenuti alla Pizzeria</title>
</head>
<body>
    <h1>Benvenuti!</h1>
    <p>Stai per essere reindirizzato al nostro menu online. Clicca <a href="<?php echo $target_url; ?>">qui</a> se non succede nulla.</p>
</body>
</html>
*/
?>