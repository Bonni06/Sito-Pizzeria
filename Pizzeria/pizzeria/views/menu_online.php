<?php 
require_once '../core/db_config.php';
require_once '../core/functions.php';

// ... (logica PHP omessa per brevità, vedi messaggio precedente) ...

// Dati generali (Recupero)
$uid = 1; 
$settings_q = $pdo->prepare("SELECT * FROM settings WHERE user_id = ?");
$settings_q->execute([$uid]);
$s = $settings_q->fetch();
$pizzeria_name = htmlspecialchars($s['pizzeria_name'] ?? 'Pizzeria Digitale');

$pizzas_q = $pdo->prepare("SELECT * FROM pizzas WHERE user_id = ? AND is_visible=1 ORDER BY position");
$pizzas_q->execute([$uid]);
$menu_items = $pizzas_q->fetchAll();

$menu_sections = [];
foreach($menu_items as $item) {
    $type_name = ucfirst($item['type']);
    $menu_sections[$type_name][] = $item;
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Online | <?php echo $pizzeria_name; ?></title>
    <link rel="stylesheet" href="../assets/css/menu_public.css"> 
</head>
<body>

<div class="header">
    <h1><?php echo $pizzeria_name; ?></h1>
    <p>Il nostro menu digitale. Clicca sulla pizza per vedere i dettagli.</p>
</div>

<div class="menu-container">
<?php 
foreach ($menu_sections as $type => $items): 
    if ($type === 'Gourmet' && !($s['visibility_specials'] ?? 1)) continue;
?>
    <h2 class="section-title"><?php echo $type; ?></h2>
    <?php foreach($items as $p):
        $price = ($p['type']=='classica') 
                 ? calculatePizzaPrice($pdo, $p['id'], $p['base_price']) 
                 : number_format($p['base_price'], 2, '.', '');
        
        $ing_stmt = $pdo->prepare("SELECT name FROM ingredients i JOIN pizza_ingredients pi ON i.id = pi.ingredient_id WHERE pi.pizza_id = ?");
        $ing_stmt->execute([$p['id']]);
        $ings = implode(', ', $ing_stmt->fetchAll(PDO::FETCH_COLUMN));
        if(!$ings) $ings = $p['description'] ?? 'Ingredienti da definire.';

        $img = $p['image_path'] ? '../' . $p['image_path'] : '../uploads/images_pizza/default.jpg';
    ?>
        <div class='accordion-item'>
            <div class='accordion-header' onclick='toggleAccordion(this)'>
                <span class="pizza-name-header"><?php echo htmlspecialchars($p['name']); ?></span>
                <span class="pizza-price-header">€ <?php echo $price; ?></span>
            </div>
            <div class='accordion-content'>
                <img src='<?php echo $img; ?>' alt='<?php echo htmlspecialchars($p['name']); ?>'>
                <p><strong>Descrizione:</strong> <?php echo $ings; ?></p>
            </div>
        </div>
    <?php endforeach; ?>
<?php endforeach; ?>
</div>

<script src="../assets/js/menu_online.js"></script> 
</body>
</html>