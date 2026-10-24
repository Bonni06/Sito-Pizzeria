<?php
require_once 'core/db_config.php';
require_once 'core/functions.php';

// Controlla l'ID dell'utente loggato (ottenuto da db_config.php)
$user_id = $_SESSION['user_id'] ?? null;

// --- LOGICA DI AUTENTICAZIONE ---
if (isset($_GET['logout'])) {
    session_destroy();
    unset($_SESSION);
    header("Location: admin.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = $pdo->prepare("SELECT id, password FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        header("Location: admin.php");
        exit;
    } else {
        $login_error = "Credenziali non valide.";
    }
}

// Se l'utente NON è loggato, mostra la pagina di login
if (!$user_id) {
    echo '<!DOCTYPE html>
    <html lang="it">
    <head>
        <title>Login Admin</title>
        <style>
            body { font-family: sans-serif; background: #f4f6f9; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
            .login-container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); width: 300px; text-align: center; }
            h2 { margin-bottom: 20px; color: #333; }
            input[type="text"], input[type="password"] { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
            button { width: 100%; padding: 10px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
            button:hover { background: #0056b3; }
            .error { color: red; margin-top: 10px; }
        </style>
    </head>
    <body>
        <div class="login-container">
            <h2>Accesso Amministratore</h2>
            <form method="POST">
                <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" name="login">Accedi</button>
                ' . (isset($login_error) ? '<p class="error">' . $login_error . '</p>' : '') . '
            </form>
        </div>
    </body>
    </html>';
    exit;
}
// --- FINE LOGICA DI AUTENTICAZIONE ---

// Carica le impostazioni per l'utente loggato
$settings_q = $pdo->prepare("SELECT * FROM settings WHERE user_id = ?");
$settings_q->execute([$user_id]);
$s = $settings_q->fetch();
if (!$s) {
    // Se non esistono impostazioni, le crea (con valori di default)
    $pdo->prepare("INSERT INTO settings (user_id, pizzeria_name, refresh_time, screen_columns) VALUES (?, 'La Mia Pizzeria', 300, 2)")->execute([$user_id]);
    $settings_q->execute([$user_id]);
    $s = $settings_q->fetch();
}

$pizzeria_name = htmlspecialchars($s['pizzeria_name'] ?? 'La Mia Pizzeria');
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pannello Amministratore</title>
    <link rel="stylesheet" href="assets/css/admin.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
</head>
<body>

<div class="sidebar">
    <div class="logo">
        <i class="fas fa-pizza-slice"></i>
        <span>Admin Panel</span>
    </div>
    <ul class="menu">
        <li class="menu-item active" onclick="showSection('home')"><i class="fas fa-home"></i> Home</li>
        <li class="menu-item" onclick="showSection('menu')"><i class="fas fa-pizza-slice"></i> Gestione Pizze</li>
        <li class="menu-item" onclick="showSection('ingredients')"><i class="fas fa-cheese"></i> Gestione Ingredienti</li>
        <li class="menu-item" onclick="showSection('products')"><i class="fas fa-utensils"></i> Altri Prodotti</li>
        <li class="menu-item" onclick="showSection('settings')"><i class="fas fa-cogs"></i> Impostazioni</li>
        <li class="menu-item" onclick="window.location.href='?logout=1'"><i class="fas fa-sign-out-alt"></i> Logout</li>
    </ul>
    <button class="theme-toggle" onclick="toggleTheme()"><i class="fas fa-moon"></i> Tema</button>
</div>

<div class="main-content">
    
    <div id="home" class="section active">
        <h2>Dashboard</h2>
        
        <div class="stats-grid">
            <div class="stat-card">
                <i class="fas fa-pizza-slice"></i>
                <p>Pizze a Menu</p>
                <span id="pizzas-stat">0</span>
            </div>
            <div class="stat-card">
                <i class="fas fa-cheese"></i>
                <p>Ingredienti Totali</p>
                <span id="ingredients-stat">0</span>
            </div>
            <div class="stat-card">
                <i class="fas fa-clock"></i>
                <p>Ultimo Aggiornamento</p>
                <span id="last-update">--</span>
            </div>
        </div>

        <div class="card-grid">
            


        </div>
    </div>
    
    <div id="menu" class="section">
        <h2>Gestione Pizze</h2>
        <div style="margin-bottom: 20px;">
            <button class="btn btn-primary" onclick="openModal('pizza')"><i class="fas fa-plus"></i> Aggiungi Nuova Pizza</button>
            <button class="btn" style="background: #f39c12; color: #fff; margin-left:10px;" onclick="sortClassicPizzas()"><i class="fas fa-sort-alpha-down"></i> Ordina Classiche (A-Z)</button>
        </div>
        <div id="pizzas-list-container">
            <ul id="sortable-pizzas" class="sortable-list">
                <p>Caricamento pizze...</p>
            </ul>
        </div>
    </div>
    
    <div id="ingredients" class="section">
        <h2>Gestione Ingredienti</h2>
        <button class="btn btn-primary" onclick="openModal('ingredient')"><i class="fas fa-plus"></i> Aggiungi Nuovo Ingrediente</button>
        <table id="ingredients-table">
            <thead>
                <tr><th>Nome</th><th>Prezzo (€)</th><th>Azioni</th></tr>
            </thead>
            <tbody>
                <tr><td colspan="3">Caricamento ingredienti...</td></tr>
            </tbody>
        </table>
    </div>
    
    <div id="products" class="section">
        <h2>Gestione Altri Prodotti (Bibite, Stuzzicherie, ecc.)</h2>
        <button class="btn btn-primary" onclick="openModal('product')"><i class="fas fa-plus"></i> Aggiungi Nuovo Prodotto</button>
        <table id="products-table">
            <thead>
                <tr><th>Nome</th><th>Categoria</th><th>Prezzo (€)</th><th>Visibile</th><th>Azioni</th></tr>
            </thead>
            <tbody>
                <tr><td colspan="5">Caricamento prodotti...</td></tr>
            </tbody>
        </table>
    </div>
    
    <div id="settings" class="section">
        <h2>Impostazioni Generali & Schermo</h2>
        
        <div style="margin-bottom: 20px;">
            <button class="btn btn-save" onclick="saveSettings()"><i class="fas fa-save"></i> Salva Tutte le Impostazioni</button>
            <button class="btn" style="background:#8e44ad; color:white; margin-left:10px;" onclick="refreshPreviewMenu()"><i class="fas fa-sync-alt"></i> Aggiorna Schermi Menu</button>
        </div>
        
        <details class="settings-accordion">
            <summary><i class="fas fa-cogs"></i> Impostazioni di Base & Generali</summary>
            <div class="card-grid accordion-content">
                <div class="card">
                    <h3><i class="fas fa-tools"></i> Configurazione Pizzeria</h3>
                    <label>Nome Pizzeria</label>
                    <input type="text" id="pizzeriaName" value="<?php echo htmlspecialchars($s['pizzeria_name'] ?? 'La Mia Pizzeria'); ?>">
                    <button class="btn btn-save" onclick="savePizzeriaName()">Salva Nome Pizzeria</button>
                </div>

                <div class="card">
                    <h3><i class="fas fa-qrcode"></i> Impostazioni QR Code</h3>
                    <label>URL di Destinazione del QR</label>
                    <input type="text" id="qrUrl" value="<?php echo htmlspecialchars($s['qr_url'] ?? 'http://localhost/pizzeria/views/menu_online.php'); ?>">
                    <button class="btn btn-primary" onclick="generateQrCode()">Genera/Aggiorna QR</button>
                    <div id="qr-placeholder" style="display: none; margin-top: 15px;"></div>
                    <div style="margin-top: 15px;">
                        <p>QR Code corrente:</p>
                        <?php if (!empty($s['qr_path'])): ?>
                            <img id="current-qr-img" src="<?php echo htmlspecialchars($s['qr_path']); ?>?v=<?php echo time(); ?>" style="max-width: 150px; border: 1px solid #ddd; padding: 5px; display: block;">
                        <?php else: ?>
                            <div id="current-qr-placeholder" style="padding: 20px; border: 1px dashed #ccc; color: #777; font-size: 0.9em;">
                                Nessun QR Code generato. Clicca su "Genera/Aggiorna" per crearlo.
                            </div>
                            <img id="current-qr-img" src="" style="max-width: 150px; border: 1px solid #ddd; padding: 5px; display: none;">
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </details>

        <details class="settings-accordion">
            <summary><i class="fas fa-layer-group"></i> Gestione Categorie Pizze</summary>
            <div class="accordion-content">
                <p>Qui puoi gestire le categorie mostrate in <strong>Gestione Pizze</strong>. Puoi aggiungerne di nuove, nasconderle o eliminarle.</p>
                <div id="category-settings-container">
                    <p><i class="fas fa-spinner fa-spin"></i> Caricamento categorie...</p>
                </div>
            </div>
        </details>

        <details class="settings-accordion">
            <summary><i class="fas fa-tv"></i> Visualizzazione Menu Schermo</summary>
            <div class="card-grid accordion-content">
                <div class="card">
                    <h3><i class="fas fa-eye"></i> Visibilità Sezioni Menu</h3>
                    <p>Queste impostazioni sono incluse quando salvi le impostazioni dello Schermo/QR.</p>
                    <label class="switch" style="margin-right: 15px;">
                      <input type="checkbox" id="vis-specials" <?php echo ($s['visibility_specials'] ?? 1) ? 'checked' : ''; ?>>
                      <span class="slider"></span>
                    </label> Mostra Pizze Speciali
                    
                    <h3 style="margin-top: 20px;"><i class="fas fa-tv"></i> Multi-Schermo</h3>
                    <label class="switch" style="margin-right: 15px;">
                      <input type="checkbox" id="enableSecondScreen" <?php echo ($s['enable_second_screen'] ?? 0) ? 'checked' : ''; ?>>
                      <span class="slider"></span>
                    </label> Abilita Secondo Schermo
                    <br><br>
                    <button class="btn btn-save" onclick="saveSettings()">Salva Impostazioni</button>
                </div>
                
                <div class="card">
                    <h3><i class="fas fa-columns"></i> Dimensioni Griglia Generale</h3>
                    <label>Tempo Aggiornamento Schermi (secondi)</label>
                    <input type="number" id="refreshTime" value="<?php echo htmlspecialchars($s['refresh_time'] ?? 300); ?>">
                    
                    <label>Numero Colonne Griglia (2-10)</label>
                    <input type="number" id="screenColumns" min="2" max="10" value="<?php echo htmlspecialchars($s['screen_columns'] ?? 2); ?>">
                    
                    <label>Numero Righe Griglia (1-10)</label>
                    <input type="number" id="screenRows" min="1" max="10" value="<?php echo htmlspecialchars($s['screen_rows'] ?? 1); ?>">
                    
                    <button class="btn btn-save" onclick="saveSettings()">Salva Impostazioni Schermo</button>
                </div>
            </div>
        </details>

        <details class="settings-accordion">
            <summary><i class="fas fa-th-large"></i> Posizionamento Categorie (Griglia Estesa)</summary>
            <div class="card full-width accordion-content">
                <p>Definisci la posizione (Riga / Colonna) e la dimensione (Span / Colonne Interne) per ogni sezione del menu TV. Le righe si aggiornano in base alle categorie che hai creato.</p>
                <table class="pos-table">
                    <thead>
                        <tr>
                            <th>Categoria</th>
                            <th>Schermo</th>
                            <th>Col. Interne</th>
                            <th>Riga Inizio</th>
                            <th>Col. Inizio</th>
                            <th>Col. Occupate</th>
                            <th>Salva</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $cat_q = $pdo->prepare("SELECT * FROM pizza_categories WHERE user_id = ? ORDER BY sort_order, name");
                        $cat_q->execute([$user_id]);
                        while ($cat = $cat_q->fetch()):
                            $cid = (int)$cat['id'];
                        ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($cat['name']); ?></strong>
                                <?php if (!$cat['is_visible']): ?><span style="color:#e74c3c;font-size:0.8em;"> (nascosta)</span><?php endif; ?>
                            </td>
                            <td>
                                <select class="cat-screen-num" data-id="<?= $cid ?>">
                                    <option value="1" <?= (($cat['screen_number'] ?? 1) == 1) ? 'selected' : '' ?>>1</option>
                                    <option value="2" <?= (($cat['screen_number'] ?? 1) == 2) ? 'selected' : '' ?>>2</option>
                                </select>
                            </td>
                            <td><input type="number" class="cat-inner-cols" data-id="<?= $cid ?>" min="1" max="4" value="<?= (int)($cat['inner_cols'] ?? 1) ?>"></td>
                            <td><input type="number" class="cat-row-pos"   data-id="<?= $cid ?>" min="1" max="10" value="<?= (int)($cat['row_pos']   ?? 1) ?>"></td>
                            <td><input type="number" class="cat-col-pos"   data-id="<?= $cid ?>" min="1" max="6"  value="<?= (int)($cat['col_pos']   ?? 1) ?>"></td>
                            <td><input type="number" class="cat-col-span"  data-id="<?= $cid ?>" min="1" max="6"  value="<?= (int)($cat['col_span']  ?? 1) ?>"></td>
                            <td><button class="btn btn-save btn-sm" onclick="saveCategoryPosition(<?= $cid ?>)"><i class="fas fa-save"></i></button></td>
                        </tr>
                        <?php endwhile; ?>

                        <tr style="background:rgba(52,152,219,0.07);">
                            <td><strong>Stuzzicherie &amp; Focacce</strong> <span style="color:#3498db;font-size:0.8em;">(fisso)</span></td>
                            <td>
                                <select id="screenStuzzicherie">
                                    <option value="1" <?= (($s['screen_stuzzicherie'] ?? 1) == 1) ? 'selected' : '' ?>>1</option>
                                    <option value="2" <?= (($s['screen_stuzzicherie'] ?? 1) == 2) ? 'selected' : '' ?>>2</option>
                                </select>
                            </td>
                            <td><input type="number" id="innerColsStuzzicherie" min="1" max="4" value="<?php echo htmlspecialchars($s['inner_cols_stuzzicherie'] ?? 1); ?>"></td>
                            <td><input type="number" id="rowStuzzicherie" min="1" max="10" value="<?php echo htmlspecialchars($s['row_stuzzicherie'] ?? 2); ?>"></td>
                            <td><input type="number" id="colStuzzicherie" min="1" max="6" value="<?php echo htmlspecialchars($s['col_stuzzicherie'] ?? 1); ?>"></td>
                            <td><input type="number" id="spanStuzzicherie" min="1" max="6" value="<?php echo htmlspecialchars($s['span_stuzzicherie'] ?? 1); ?>"></td>
                            <td><button class="btn btn-save btn-sm" onclick="saveSettings()"><i class="fas fa-save"></i></button></td>
                        </tr>
                    </tbody>
                </table>
                <p style="margin-top:12px;font-size:0.85em;color:#888;"><i class="fas fa-info-circle"></i> Usa il pulsante <i class="fas fa-save"></i> a destra di ogni riga per salvare il posizionamento.</p>
            </div>
        </details>

        <details class="settings-accordion">
            <summary><i class="fas fa-images"></i> Gestione Immagini & Grafica Categorie</summary>
            <div class="card-grid accordion-content">
                <div class="card">
                    <h3><i class="fas fa-image"></i> Immagini Testata Categorie Menu</h3>
                    <p>Carica un'immagine da mostrare sopra ogni sezione del menu TV.</p>
                    
                    <?php
                    // Suddividiamo il caricamento: prima le categorie dinamiche (Pizze), poi quelle fisse (Stuzzicherie)
                    $image_cats_q = $pdo->prepare("SELECT id, name, slug, image_path FROM pizza_categories WHERE user_id = ? ORDER BY sort_order, name");
                    $image_cats_q->execute([$user_id]);
                    while ($icat = $image_cats_q->fetch()):
                    ?>
                        <label><?php echo htmlspecialchars($icat['name']); ?></label>
                        <div class="upload-group" style="margin-bottom: 15px;">
                            <input type="file" id="file-cat-<?= $icat['id'] ?>" accept="image/*">
                            <button class="btn btn-primary" onclick="uploadCategoryImage('<?= $icat['slug'] ?>', 'file-cat-<?= $icat['id'] ?>', <?= $icat['id'] ?>)">Carica</button>
                            <small style="display:block; margin-top:5px;">Attuale: <?php echo htmlspecialchars($icat['image_path'] ?? 'Nessuna'); ?></small>
                        </div>
                    <?php endwhile; ?>

                    <hr style="margin: 20px 0; border: 0; border-top: 1px solid #eee;">

                    <label>Stuzzicherie &amp; Focacce (Sezione Prodotto)</label>
                    <div class="upload-group">
                        <input type="file" id="file-stuzzicherie" accept="image/*">
                        <button class="btn btn-primary" onclick="uploadCategoryImage('stuzzicherie', 'file-stuzzicherie')">Carica</button>
                        <small style="display:block; margin-top:5px;">Attuale: <?php echo htmlspecialchars($s['img_stuzzicherie'] ?? 'Nessuna'); ?></small>
                    </div>
                </div>

                <div class="card">
                    <h3><i class="fas fa-camera"></i> Gestione Foto Singoli Prodotti</h3>
                    <p>Carica una foto specifica per ogni pizza o stuzzicheria.</p>
                    
                    <label>Seleziona Categoria</label>
                    <select id="upload-item-category" onchange="loadCategoryItems()">
                        <option value="">-- Seleziona --</option>
                        <option value="classiche">Pizze Classiche</option>
                        <option value="speciali">Pizze Speciali/Gourmet</option>
                        <option value="stuzzicherie">Stuzzicherie</option>
                    </select>

                    <div id="item-select-container" style="display:none; margin-top:10px;">
                        <label>Seleziona Prodotto</label>
                        <select id="upload-item-id">
                            <!-- Popolato via JS -->
                        </select>

                        <label style="margin-top:10px;">Immagine</label>
                        <div class="upload-group">
                            <input type="file" id="file-item-upload" accept="image/*">
                            <button class="btn btn-primary" onclick="uploadItemImage()">Carica Foto Prodotto</button>
                        </div>
                    </div>
                </div>
            </div>
        </details>
    </div>
    
</div>

<div class="preview-footer" onclick="downloadPdf(1)" style="right: 390px; background: #e74c3c;" title="Scarica PDF Schermo 1">
    <i class="fas fa-file-pdf"></i> PDF 1
</div>
<div class="preview-footer" onclick="toggleTvPreview(1)" style="right: 260px; background: #3498db;" title="Anteprima TV Schermo 1">
    <i class="fas fa-tv"></i> Schermo 1
</div>
<div class="preview-footer" onclick="downloadPdf(2)" style="right: 140px; background: #e74c3c;" title="Scarica PDF Schermo 2">
    <i class="fas fa-file-pdf"></i> PDF 2
</div>
<div class="preview-footer" onclick="toggleTvPreview(2)" style="right: 20px; background: #8e44ad;" title="Anteprima TV Schermo 2">
    <i class="fas fa-tv"></i> Schermo 2
</div>

<div id="tv-preview-container">
    <div class="preview-header">
        <span id="tv-preview-title">Anteprima TV</span>
        <button onclick="toggleTvPreview()">X</button>
    </div>
    <iframe id="tv-preview-frame" src=""></iframe>
</div>

<!-- Modal Pizza -->
<div id="pizza-modal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal('pizza')">&times;</span>
        <h2 id="pizza-modal-title">Aggiungi Pizza</h2>
        <form id="pizza-form" onsubmit="savePizza(event); return false;">
            <input type="hidden" id="pizza-id" name="pizza_id" value="">
            
            <label for="pizza-name">Nome Pizza *</label>
            <input type="text" id="pizza-name" name="name" required>
            
            <label for="pizza-type">Tipo *</label>
            <select id="pizza-type" name="type" required>
                <!-- Popolato dinamicamente da JS -->
            </select>
            
            <label for="pizza-price">Prezzo Base (€) *</label>
            <input type="number" id="pizza-price" name="base_price" step="0.50" min="0" required>
            
            <label for="pizza-ingredients-text">Ingredienti (Testo libero per ora)</label>
            <textarea id="pizza-ingredients-text" name="ingredients_text" rows="3" placeholder="Es: Pomodoro, Mozzarella, Basilico..."></textarea>
            
            <label class="switch" style="margin-top: 10px; display:flex; align-items:center; gap: 10px;">
                <input type="checkbox" id="pizza-visible" name="is_visible" checked>
                <span class="slider"></span>
                <span style="font-weight:bold;">Visibile nel menu</span>
            </label>
            
            <button type="submit" class="btn btn-save" style="width: 100%; margin-top: 20px;">Salva Pizza</button>
        </form>
    </div>
</div>

<!-- Modal Ingrediente -->
<div id="ingredient-modal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal('ingredient')">&times;</span>
        <h2 id="ingredient-modal-title">Aggiungi Ingrediente</h2>
        <form id="ingredient-form" onsubmit="saveIngredient(event); return false;">
            <input type="hidden" id="ingredient-id" name="ingredient_id" value="">
            
            <label for="ingredient-name">Nome Ingrediente *</label>
            <input type="text" id="ingredient-name" name="name" required>
            
            <label for="ingredient-price">Prezzo Aggiunta (€) *</label>
            <input type="number" id="ingredient-price" name="price" step="0.10" min="0" required>
            
            <button type="submit" class="btn btn-save" style="width: 100%; margin-top: 20px;">Salva Ingrediente</button>
        </form>
    </div>
</div>

<!-- Modal Prodotto -->
<div id="product-modal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal('product')">&times;</span>
        <h2 id="product-modal-title">Aggiungi Prodotto</h2>
        <form id="product-form" onsubmit="saveProduct(event); return false;">
            <input type="hidden" id="product-id" name="product_id" value="">
            
            <label for="product-name">Nome Prodotto *</label>
            <input type="text" id="product-name" name="name" required>
            
            <label for="product-category">Categoria *</label>
            <select id="product-category" name="category" required>
                <option value="bibita">Bibita</option>
                <option value="stuzzicheria">Stuzzicheria</option>
                <option value="focaccia">Focaccia</option>
            </select>
            
            <label for="product-price">Prezzo (€) *</label>
            <input type="number" id="product-price" name="price" step="0.50" min="0" required>
            
            <label class="switch" style="margin-top: 10px; display:flex; align-items:center; gap: 10px;">
                <input type="checkbox" id="product-visible" name="is_visible" checked>
                <span class="slider"></span>
                <span style="font-weight:bold;">Visibile nel menu</span>
            </label>
            
            <button type="submit" class="btn btn-save" style="width: 100%; margin-top: 20px;">Salva Prodotto</button>
        </form>
    </div>
</div>

<script src="assets/js/admin.js?v=<?= time() ?>"></script>
<!-- Scripts are handled in admin.js -->
</body>
</html>