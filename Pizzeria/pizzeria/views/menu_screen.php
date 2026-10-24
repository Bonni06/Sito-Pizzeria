<?php 
require_once '../core/db_config.php';
require_once '../core/functions.php';

// Usiamo user_id=1 per il menu pubblico (assumendo che sia l'ID del tuo utente admin/menu)
$uid = 1; 

$screen_requested = (int)($_GET['screen'] ?? 1);
if ($screen_requested < 1) $screen_requested = 1;

// Recupera le impostazioni (inclusi i dati delle colonne)
$settings_q = $pdo->prepare("SELECT refresh_time, screen_columns, screen_rows, pizzeria_name, 
    col_classiche, row_classiche, span_classiche,
    col_stuzzicherie, row_stuzzicherie, span_stuzzicherie, 
    col_speciali, row_speciali, span_speciali, 
    visibility_specials, qr_path,
    img_classiche, img_stuzzicherie, img_speciali,
    inner_cols_classiche, inner_cols_stuzzicherie, inner_cols_speciali,
    enable_second_screen, screen_stuzzicherie
    FROM settings WHERE user_id = ?"
);
$settings_q->execute([$uid]);
$s = $settings_q->fetch();

$refresh_time = $s['refresh_time'] ?? 300; 
$screen_cols = $s['screen_columns'] ?? 2; 
$screen_rows = $s['screen_rows'] ?? 1; // [NEW]
$pizzeria_name = htmlspecialchars($s['pizzeria_name'] ?? 'Pizzeria Digitale');

// *************** VARIABILI COLONNE ESTESE ***************
$col_classiche = $s['col_classiche'] ?? 1; 
$row_classiche = $s['row_classiche'] ?? 1; 
$span_classiche = $s['span_classiche'] ?? 1; 

$col_stuzzicherie = $s['col_stuzzicherie'] ?? 1; 
$row_stuzzicherie = $s['row_stuzzicherie'] ?? 2; 
$span_stuzzicherie = $s['span_stuzzicherie'] ?? 1; 

$col_speciali = $s['col_speciali'] ?? 2; 
$row_speciali = $s['row_speciali'] ?? 1; 
$span_speciali = $s['span_speciali'] ?? 1; 

$show_specials = $s['visibility_specials'] ?? 1;
$qr_path = $s['qr_path'] ?? '';
// *************************************************

$enable_second_screen = $s['enable_second_screen'] ?? 0;
if (!$enable_second_screen && $screen_requested > 1) {
    $screen_requested = 1;
}

// Carica le categorie pizza dinamiche
$cat_q = $pdo->prepare("SELECT * FROM pizza_categories WHERE user_id = ? AND is_visible = 1 AND screen_number = ? ORDER BY sort_order, name");
$cat_q->execute([$uid, $screen_requested]);
$pizza_categories = $cat_q->fetchAll();

// Se non ci sono categorie nella nuova tabella, usa le categorie di default legacy
if (empty($pizza_categories) && $screen_requested == 1) {
    $pizza_categories = [
        ['name' => 'Pizze Classiche', 'slug' => 'classica', 'is_visible' => 1],
        ['name' => 'Pizze Gourmet',   'slug' => 'gourmet',  'is_visible' => 1],
        ['name' => 'Scrocchiarelle',  'slug' => 'scrocchiarella', 'is_visible' => 1],
    ];
}

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Menu Schermo | <?php echo $pizzeria_name; ?></title>
    <meta http-equiv="refresh" content="<?php echo $refresh_time; ?>">
    <link rel="stylesheet" href="../assets/css/menu_public.css"> 
    <style>
        /* ================================================= */
        /* Stili specifici per il layout a schermo (TV/Monitor) - WHITE THEME */
        /* ================================================= */
        :root {
            /* Variabili per il ridimensionamento dinamico */
            --base-font-size: 26px; /* Aumentato da 19px */
            --header-img-height: 150px; 
            --content-padding-top: 20px; /* Ridotto */
            --col-gap: 20px; /* Ridotto da 40px */
            --cat-title-size: 2.2em; /* Più grande */
            --cat-margin-bottom: 20px;
            --cat-img-height: 160px; /* Più grande */
            --item-row-margin: 10px;
            --item-name-size: 1.2em; /* Più grande */
            --item-price-size: 1.3em; /* Più grande */
            --ing-size: 0.95em; /* Più grande */
            --ing-margin-bottom: 15px;
        }

        body { 
            background-color: #fff; /* Sfondo Bianco */
            color: #333; /* Testo Scuro */
            margin: 0;
            padding: 0;
            width: 100vw;
            height: 100vh;
            overflow: hidden; /* NIENTE SCROLLBAR */
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            display: flex; /* Centra il wrapper nella pagina */
            align-items: center;
            justify-content: center;
        }

        /* Wrapper fluido */
        #scale-wrapper {
            width: 100vw;
            height: 100vh;
            background: #fff;
            position: relative;
            padding: 15px; /* Minimo padding per non incollare le scritte alla cornice fisica del display */
            box-sizing: border-box;
            overflow: hidden; /* Contenuto limitato allo schermo reale */
            font-size: var(--base-font-size);
            display: flex;
            flex-direction: column;
        }

        /* Watermark centrale - Ora Logo Principale */
        #scale-wrapper::after {
            content: "";
            position: absolute;
            top: 50%; 
            left: 50%;
            transform: translate(-50%, -50%);
            width: 60%; /* Leggermente ridotto per non toccare i bordi */
            height: 60%;
            background: url('../assets/img/logo_background.png') no-repeat center center;
            background-size: contain;
            opacity: 0.15; /* Riportato a un valore più basso per non disturbare il testo */
            z-index: 0;
            pointer-events: none;
        }
        
        .header {
            display: none; /* Header rimosso completamente */
        }
        
        h2 { 
            color: #d35400; 
            border-bottom: 2px solid #eee;
            padding-bottom: 10px; 
            margin-top: 0;
            margin-bottom: var(--cat-margin-bottom);
            font-size: var(--cat-title-size);
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 800;
            position: relative;
            z-index: 1;
        }

        /* Griglia */
        .grid-container { 
            display: grid; 
            grid-template-columns: repeat(<?php echo $screen_cols; ?>, 1fr); 
            grid-template-rows: repeat(<?php echo $screen_rows; ?>, 1fr);
            gap: var(--col-gap, 10px); 
            width: 100%; /* Si adatta dinamicamente allo schermo al netto del padding */
            height: 100%; /* Occupa tutta l'altezza disponibile */
            position: relative;
            z-index: 1;
            flex-grow: 1; /* Occupa lo spazio rimanente in altezza */
        }

        /* Box Categoria */
        /* Box Categoria */
        .menu-content-box {
            padding: 5px; /* Piccola spaziatura interna */
            background-color: transparent; 
            display: flex;
            flex-direction: column;
            height: 100%; /* Occupa tutta la cella della griglia */
            /* overflow: hidden; REMOVED - Evitiamo di tagliare il contenuto */
        }
        
        .cat-img {
            width: 100%;
            height: var(--cat-img-height);
            object-fit: cover;
            display: block;
            margin-bottom: 20px;
            border-radius: 6px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        /* Inner Grid sostituita da Flex per colonne verticali */
        .inner-flex-container {
            flex-grow: 1;
            display: flex; /* Flex row per le colonne */
            gap: var(--col-gap, 10px); /* Usa il gap dinamico */
            align-items: flex-start;
            width: 100%; /* Ensure full width */
        }

        .inner-flex-column {
            flex: 1; /* Larghezza equa per ogni colonna */
            min-width: 0; /* Fix for flex item overflow */
            display: flex;
            flex-direction: column;
        }

        /* Righe Elementi */
        .item-row {
            display: flex;
            justify-content: space-between;
            align-items: center; 
            margin-bottom: var(--item-row-margin);
            position: relative;
            padding-bottom: 5px;
            border-bottom: 1px dotted #bbb; /* Schiarito il bordo */
        }
        .item-row strong {
            font-size: var(--item-name-size);
            color: #222; /* Grigio scuro ma non nero assoluto */
            text-transform: uppercase;
            font-weight: 700; /* Riportato a 700 invece di 800 */
            padding-right: 80px; 
            text-shadow: 1px 1px 2px rgba(255,255,255,0.7); /* Ombra leggermente più debole */
        }
        .item-price {
            font-weight: 700; /* Riportato a 700 invece di 800 */
            color: #d35400; /* Riportato al colore arancio/rosso originale ma leggibile */
            font-size: var(--item-price-size);
            position: absolute; 
            right: 0;
            bottom: 5px;
            text-shadow: 1px 1px 2px rgba(255,255,255,0.7);
        }
        .ingredients-list {
            color: #444; /* Grigio medio-scuro, non troppo nero */
            font-size: var(--ing-size);
            margin-top: -8px; 
            margin-bottom: var(--ing-margin-bottom);
            font-weight: 500; /* Spessore intermedio */
            line-height: 1.3;
            width: 90%; 
            text-shadow: 1px 1px 2px rgba(255,255,255,0.7);
        }
        
    </style>
    <script>
        // Non serve più forzare dimensioni e trasformazioni fisse.
        // Il layout è nativamente width: 100vw e height: 100vh.
        function resizeToFitScreen() {
            // Placeholder vuoto, la logica è passata al CSS e a fitContent()
        }

        // 2. Auto-Scale Content (Font Size/Spacing) basato sulle dimensioni REALI
        function fitContent() {
            const wrapper = document.getElementById('scale-wrapper');
            const root = document.documentElement;
            
            // Calcola la grandezza iniziale del testo basandosi sull'altezza reale dello schermo
            // Questo garantisce partenze corrette sia su mobile che su TV.
            let fontSize = window.innerHeight * 0.045; // 4.5% dell'altezza dello schermo (molto grande)
            if (fontSize > 50) fontSize = 50; // limite massimo sensato
            
            let catImgHeight = window.innerHeight * 0.15;
            let headerImgHeight = window.innerHeight * 0.15;
            let ingMargin = window.innerHeight * 0.015;
            let catMargin = window.innerHeight * 0.02;
            let colGap = window.innerWidth * 0.015; // Gap orizzontale in proporzione alla larghezza (1.5%)
            
            // Imposta i valori di partenza
            root.style.setProperty('--base-font-size', fontSize + 'px');
            root.style.setProperty('--cat-img-height', catImgHeight + 'px');
            root.style.setProperty('--header-img-height', headerImgHeight + 'px');
            root.style.setProperty('--ing-margin-bottom', ingMargin + 'px');
            root.style.setProperty('--cat-margin-bottom', catMargin + 'px');
            root.style.setProperty('--col-gap', colGap + 'px'); 

            // Loop di calibrazione visibile 
            let iterations = 0;
            //scrollHeight può essere impreciso su alcuni browser se c'è margin collapsing, ma usiamo una tolleranza
            while (wrapper.scrollHeight > wrapper.clientHeight + 5 && iterations < 100) {
                // Riduciamo del 3% ad ogni passo per una calibrazione fluida
                fontSize *= 0.97;
                catImgHeight *= 0.97;
                ingMargin *= 0.97;
                catMargin *= 0.97;
                
                root.style.setProperty('--base-font-size', fontSize + 'px');
                root.style.setProperty('--cat-img-height', catImgHeight + 'px');
                root.style.setProperty('--ing-margin-bottom', ingMargin + 'px');
                root.style.setProperty('--cat-margin-bottom', catMargin + 'px');
                
                iterations++;
            }
            console.log('Fit Content Iterations:', iterations, 'Final font size:', fontSize, 'Screen:', window.innerWidth, window.innerHeight);
        }

        window.addEventListener('resize', () => {
            resizeToFitScreen();
        });
        
        document.addEventListener('DOMContentLoaded', () => {
            resizeToFitScreen();
            setTimeout(fitContent, 100); 
            // Lanciamo fitContent anche dopo il caricamento delle immagini se possibile, o usiamo onload sulle img
        });
    </script>
</head>
<body>
    <div id="scale-wrapper">
    <!-- Header Rimosso -->
    
    <div class="grid-container">
        
        <?php 
        // *************** VARIABILI COLONNE INTERNE ***************
        $inner_cols_classiche = $s['inner_cols_classiche'] ?? 1;
        $inner_cols_stuzzicherie = $s['inner_cols_stuzzicherie'] ?? 1;
        $inner_cols_speciali = $s['inner_cols_speciali'] ?? 1;

        // Funzione per renderizzare le pizze di una categoria specifica
        function renderPizzaCategory($pdo, $uid, $slug, $inner_cols) {
            $stmt = $pdo->prepare("SELECT * FROM pizzas WHERE user_id = ? AND type = ? AND is_visible = 1 ORDER BY position, name");
            $stmt->execute([$uid, $slug]);
            $items = [];
            while ($p = $stmt->fetch()) {
                $ing_list = $p['description'];
                if (empty($ing_list)) {
                    $ing_stmt = $pdo->prepare("SELECT name FROM ingredients i JOIN pizza_ingredients pi ON i.id = pi.ingredient_id WHERE pi.pizza_id = ?");
                    $ing_stmt->execute([$p['id']]);
                    $ing_list = implode(', ', $ing_stmt->fetchAll(PDO::FETCH_COLUMN));
                }
                $price = calculatePizzaPrice($pdo, $p['id'], $p['base_price']);
                $items[] = ['name' => $p['name'], 'price' => $price, 'desc' => $ing_list];
            }

            if (empty($items)) return;

            $items_per_col = max(1, ceil(count($items) / $inner_cols));
            $chunks = array_chunk($items, $items_per_col);

            echo '<div class="inner-flex-container">';
            foreach ($chunks as $chunk) {
                echo '<div class="inner-flex-column">';
                foreach ($chunk as $item) {
                    echo '<div class="menu-item-container">';
                    echo '<div class="item-row">';
                    echo '<span class="item-price">€ ' . $item['price'] . '</span>';
                    echo '<strong>' . htmlspecialchars($item['name']) . '</strong>';
                    echo '</div>';
                    if (!empty($item['desc'])) {
                        echo '<div class="ingredients-list">' . htmlspecialchars($item['desc']) . '</div>';
                    }
                    echo '</div>';
                }
                echo '</div>';
            }
            echo '</div>';
        }

        // Funzione per Stuzzicherie (prodotti dal DB products)
        function renderStuzzicherie($pdo, $uid, $inner_cols) {
            $stmt = $pdo->prepare("SELECT * FROM products WHERE user_id = ? AND category IN ('stuzzicheria', 'focaccia') AND is_visible=1");
            $stmt->execute([$uid]);
            $items = [];
            while ($prod = $stmt->fetch()) {
                $items[] = ['name' => $prod['name'], 'price' => number_format($prod['price'], 2, '.', ''), 'desc' => ''];
            }

            if (empty($items)) return;

            $items_per_col = max(1, ceil(count($items) / $inner_cols));
            $chunks = array_chunk($items, $items_per_col);

            echo '<div class="inner-flex-container">';
            foreach ($chunks as $chunk) {
                echo '<div class="inner-flex-column">';
                foreach ($chunk as $item) {
                    echo '<div class="menu-item-container">';
                    echo '<div class="item-row">';
                    echo '<span class="item-price">€ ' . $item['price'] . '</span>';
                    echo '<strong>' . htmlspecialchars($item['name']) . '</strong>';
                    echo '</div>';
                    echo '</div>';
                }
                echo '</div>';
            }
            echo '</div>';
        }
        // *************************************************

        // grid-area: riga_inizio / colonna_inizio / riga_fine / colonna_fine
        // row_end = row_start + row_span (qui span riga non lo abbiamo salvato esplicitamente come input, ma useremo 1 o adatteremo se serve)
        // Per ora manteniamo row_end auto o row_start + 1. Se l'utente vuole span riga, bisognerebbe aggiungerlo al db. 
        // ASSUMO che l'utente voglia controllare Row e Col start, e Col Span. 
        // Se serve Row Span, possiamo aggiungerlo. Per ora uso 'auto'.
        
        $col_end_classiche = $col_classiche + $span_classiche;
        $grid_area_classiche = "grid-area: {$row_classiche} / {$col_classiche} / span 1 / {$col_end_classiche};"; 
        // Nota: span 1 per altezza riga, a meno che non vogliamo span riga. 
        // Se la griglia ha righe definite, e l'utente mette row 2, occuperà solo quella riga.
        // Se il contenuto è troppo lungo, il fitContent ridurrà il font, oppure potremmo voler dare span riga > 1.
        // Per ora mettiamo span 1 di default sulla RIGA, ma lasciamo che il contenuto si espanda se non usiamo overflow hidden rigido.
        // Modifica: usiamo 'span 1' ma se servono più righe l'utente dovrebbe poterlo dire. 
        // Dato che non abbiamo 'row_span' nel DB (c'è row_classiche ma è la start row), assumiamo 1.
        ?>

        <?php
        // ============================================================
        // SEZIONI PIZZA DINAMICHE - generate dalla tabella pizza_categories
        // Ogni categoria come definita dall'admin nella dashboard
        // ============================================================
        foreach ($pizza_categories as $cat):
            // Controlla se questa categoria ha almeno una pizza visibile
            $check = $pdo->prepare("SELECT COUNT(*) FROM pizzas WHERE user_id = ? AND type = ? AND is_visible = 1");
            $check->execute([$uid, $cat['slug']]);
            if ($check->fetchColumn() == 0) continue;

            // Coordinate della griglia per questa specifica categoria
            $row_pos = $cat['row_pos'] ?? 1;
            $col_pos = $cat['col_pos'] ?? 1;
            $col_span = $cat['col_span'] ?? 1;
            $inner_cols = $cat['inner_cols'] ?? 1;
            
            $col_end = $col_pos + $col_span;
            $grid_area = "grid-area: {$row_pos} / {$col_pos} / span 1 / {$col_end};";
        ?>
            <div class="menu-content-box" style="<?php echo $grid_area; ?>">
                <?php if (!empty($cat['image_path'])): ?>
                    <img src="../<?php echo htmlspecialchars($cat['image_path']); ?>" class="cat-img">
                <?php endif; ?>
                <h2><?php echo htmlspecialchars($cat['name']); ?></h2>
                <?php renderPizzaCategory($pdo, $uid, $cat['slug'], $inner_cols); ?>
            </div>
        <?php endforeach; ?>

        <?php
        // ============================================================
        // SEZIONE STUZZICHERIE & FOCACCE (prodotti, non pizze)
        // ============================================================
        $screen_stuzzicherie = $s['screen_stuzzicherie'] ?? 1;
        if ($screen_stuzzicherie == $screen_requested):
            $check_stuzz = $pdo->prepare("SELECT count(*) FROM products WHERE user_id = ? AND category IN ('stuzzicheria', 'focaccia') AND is_visible=1");
            $check_stuzz->execute([$uid]);
            if ($check_stuzz->fetchColumn() > 0):
                $col_end_stuzzicherie = $col_stuzzicherie + $span_stuzzicherie;
                $grid_area_stuzzicherie = "grid-area: {$row_stuzzicherie} / {$col_stuzzicherie} / span 1 / {$col_end_stuzzicherie};";
        ?>
            <div class="menu-content-box" style="<?php echo $grid_area_stuzzicherie; ?>">
                <?php if (!empty($s['img_stuzzicherie'])): ?>
                    <img src="../<?php echo htmlspecialchars($s['img_stuzzicherie']); ?>" class="cat-img">
                <?php endif; ?>
                <h2>Stuzzicherie &amp; Focacce</h2>
                <?php renderStuzzicherie($pdo, $uid, $inner_cols_stuzzicherie); ?>
            </div>
        <?php 
            endif;
        endif; 
        ?>


    </div>
    </div>
<?php if (isset($_GET['download_pdf']) && $_GET['download_pdf'] == 1): ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
window.addEventListener('load', function() {
    var element = document.body;
    var opt = {
      margin:       0,
      filename:     'Menu_Schermo_<?php echo $screen_requested; ?>.pdf',
      image:        { type: 'jpeg', quality: 1 },
      html2canvas:  { scale: 2, useCORS: true, backgroundColor: '#ffffff', windowWidth: 1920, windowHeight: 1080 },
      jsPDF:        { unit: 'in', format: 'a4', orientation: 'portrait' }
    };
    
    // Overlay di sicurezza durante l'acquisizione
    var banner = document.createElement('div');
    banner.style.cssText = 'position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.9);color:white;z-index:999999;display:flex;align-items:center;justify-content:center;font-size:35px;font-family:sans-serif; text-transform:uppercase;';
    banner.innerText = 'Generazione PDF in corso... (1 Pagina)';
    document.body.appendChild(banner);

    // Attesa per stabilizzare i font
    setTimeout(function(){
        banner.remove();
        document.body.style.width = '1920px';
        document.body.style.height = '1080px'; // Torniamo a forzare la VERA dimensione dello schermo per evitare di distruggere le spaziature del menu
        document.body.style.overflow = 'hidden'; 
        
        html2pdf().set(opt).from(element).save().then(function() {
            // Fallback visivo, prima che provi a chiudere
            document.body.innerHTML = '<div style="display:flex; align-items:center; justify-content:center; width:100vw; height:100vh; background:#111; color:#2ecc71; font-size:40px; font-family:sans-serif; text-align:center;">PDF SCARICATO!<br>Chiusura automatica della pagina a breve...</div>';
            
            // Ritarda la chiusura per dare tempo al browser di salvare fisicamente il file
            setTimeout(function() {
                window.close();
            }, 3000);
        });
    }, 2000);
});
</script>
<?php endif; ?>
</body>
</html>