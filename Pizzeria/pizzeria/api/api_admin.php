<?php
require_once '../core/db_config.php';
require_once '../core/functions.php';

header('Content-Type: application/json');

// Controllo sessione utente
if (!$user_id) {
    echo json_encode(['status' => 'error', 'message' => 'Non autorizzato']);
    exit;
}

$action = $_GET['action'] ?? '';
$input = json_decode(file_get_contents('php://input'), true);

try {
    switch ($action) {
        case 'reorder':
            // Logica Drag & Drop per riordinare le pizze
            if (is_array($input)) {
                $pdo->beginTransaction();
                $stmt = $pdo->prepare("UPDATE pizzas SET position = ? WHERE id = ? AND user_id = ?");
                foreach ($input as $item) {
                    $stmt->execute([$item['pos'], $item['id'], $user_id]);
                }
                $pdo->commit();
                echo json_encode(['status' => 'success', 'message' => 'Ordine salvato.']);
            }
            break;

        case 'update_ing':
            // Aggiorna nome o prezzo di un ingrediente
            $allowed = ['name', 'price'];
            if (in_array($input['field'], $allowed) && isset($input['id'])) {
                $stmt = $pdo->prepare("UPDATE ingredients SET {$input['field']} = ? WHERE id = ? AND user_id = ?");
                $stmt->execute([$input['value'], $input['id'], $user_id]);
                echo json_encode(['status' => 'success', 'message' => 'Ingrediente aggiornato.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Campo non valido o ID mancante.']);
            }
            break;

        case 'save_settings':
            // Fetch existing settings to merge with partial updates
            $q = $pdo->prepare("SELECT * FROM settings WHERE user_id = ?");
            $q->execute([$user_id]);
            $current = $q->fetch();
            if (!$current) {
                // Initialize with defaults if missing
                $pdo->prepare("INSERT INTO settings (user_id) VALUES (?)")->execute([$user_id]);
                $current = [];
            }

            // Map input keys (camelCase from JS) to DB columns (snake_case)
            $map = [
                'enableSecondScreen' => 'enable_second_screen',
                'screenStuzzicherie' => 'screen_stuzzicherie',
                'refreshTime' => 'refresh_time',
                'qrUrl' => 'qr_url',
                'screenColumns' => 'screen_columns',
                'screenRows' => 'screen_rows',
                'visibilitySpecials' => 'visibility_specials',
                'colClassiche' => 'col_classiche',
                'rowClassiche' => 'row_classiche',
                'spanClassiche' => 'span_classiche',
                'innerColsClassiche' => 'inner_cols_classiche',
                'colStuzzicherie' => 'col_stuzzicherie',
                'rowStuzzicherie' => 'row_stuzzicherie',
                'spanStuzzicherie' => 'span_stuzzicherie',
                'innerColsStuzzicherie' => 'inner_cols_stuzzicherie',
                'colSpeciali' => 'col_speciali',
                'rowSpeciali' => 'row_speciali',
                'spanSpeciali' => 'span_speciali',
                'innerColsSpeciali' => 'inner_cols_speciali',
                'showClassica' => 'show_classica',
                'showFamiglia' => 'show_famiglia',
                'showGourmet' => 'show_gourmet',
                'showScrocchiarella' => 'show_scrocchiarella'
            ];

            $updates = [];
            $params = [];
            foreach ($map as $jsKey => $dbCol) {
                if (isset($input[$jsKey])) {
                    $updates[] = "$dbCol = ?";
                    $params[] = $input[$jsKey];
                }
            }

            if (!empty($updates)) {
                $sql = "UPDATE settings SET " . implode(', ', $updates) . " WHERE user_id = ?";
                $params[] = $user_id;
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
            }

            echo json_encode(['status' => 'success', 'message' => 'Impostazioni aggiornate.']);
            break;

        case 'save_pizzeria_name':
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($input['name'])) {
                $stmt = $pdo->prepare("UPDATE settings SET pizzeria_name = ? WHERE user_id = ?");
                $stmt->execute([$input['name'], $user_id]);
                echo json_encode(['status' => 'success', 'message' => 'Nome pizzeria aggiornato.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Nome mancante.']);
            }
            break;

        case 'sort_classic_pizzas':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $pdo->beginTransaction();
                // Prendi tutte le pizze classiche e famiglia, in ordine alfabetico
                $stmt = $pdo->prepare("SELECT id FROM pizzas WHERE user_id = ? AND type IN ('classica', 'famiglia') ORDER BY name ASC");
                $stmt->execute([$user_id]);
                $pizzas = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Aggiorna la posizione
                $updateStmt = $pdo->prepare("UPDATE pizzas SET position = ? WHERE id = ?");
                $pos = 1;
                foreach ($pizzas as $p) {
                    $updateStmt->execute([$pos, $p['id']]);
                    $pos++;
                }
                $pdo->commit();
                echo json_encode(['status' => 'success', 'message' => 'Pizze classiche ordinate.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Metodo non valido.']);
            }
            break;

        case 'increase_category_prices':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $category_slug = $input['category_slug'] ?? '';
                $amount = floatval($input['amount'] ?? 0);
                
                if (empty($category_slug) || $amount <= 0) {
                    echo json_encode(['status' => 'error', 'message' => 'Dati non validi.']);
                    exit;
                }
                
                $stmt = $pdo->prepare("UPDATE pizzas SET base_price = base_price + ? WHERE type = ? AND user_id = ?");
                $stmt->execute([$amount, $category_slug, $user_id]);
                
                echo json_encode(['status' => 'success', 'message' => 'Prezzi aggiornati con successo.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Metodo non valido.']);
            }
            break;

        case 'decrease_category_prices':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $category_slug = $input['category_slug'] ?? '';
                $amount = floatval($input['amount'] ?? 0);
                
                if (empty($category_slug) || $amount <= 0) {
                    echo json_encode(['status' => 'error', 'message' => 'Dati non validi.']);
                    exit;
                }
                
                // Evita prezzi negativi
                $stmt = $pdo->prepare("UPDATE pizzas SET base_price = GREATEST(0, base_price - ?) WHERE type = ? AND user_id = ?");
                $stmt->execute([$amount, $category_slug, $user_id]);
                
                echo json_encode(['status' => 'success', 'message' => 'Prezzi ridotti con successo.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Metodo non valido.']);
            }
            break;

        case 'set_category_prices':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $category_slug = $input['category_slug'] ?? '';
                $amount = floatval($input['amount'] ?? 0);
                
                if (empty($category_slug) || $amount < 0) {
                    echo json_encode(['status' => 'error', 'message' => 'Dati non validi.']);
                    exit;
                }
                
                $stmt = $pdo->prepare("UPDATE pizzas SET base_price = ? WHERE type = ? AND user_id = ?");
                $stmt->execute([$amount, $category_slug, $user_id]);
                
                echo json_encode(['status' => 'success', 'message' => 'Prezzi impostati con successo.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Metodo non valido.']);
            }
            break;

        case 'get_stats':
            // Restituisce le statistiche per la Dashboard Home
            $stats = [
                'pizzas_count' => $pdo->query("SELECT count(*) FROM pizzas WHERE user_id=$user_id")->fetchColumn(),
                'ingredients_count' => $pdo->query("SELECT count(*) FROM ingredients WHERE user_id=$user_id")->fetchColumn(),
                'last_update' => date('H:i:s'), // Placeholder
            ];
            echo json_encode(['status' => 'success', 'data' => $stats]);
            break;
            
        case 'get_pizza':
            if (isset($_GET['id'])) {
                $stmt = $pdo->prepare("SELECT * FROM pizzas WHERE id = ? AND user_id = ?");
                $stmt->execute([$_GET['id'], $user_id]);
                $pizza = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($pizza) {
                    $pizza['ingredients_text'] = $pizza['description'];
                    if (empty($pizza['ingredients_text'])) {
                        $ing_stmt = $pdo->prepare("SELECT i.name FROM ingredients i JOIN pizza_ingredients pi ON i.id = pi.ingredient_id WHERE pi.pizza_id = ?");
                        $ing_stmt->execute([$pizza['id']]);
                        $pizza['ingredients_text'] = implode(', ', $ing_stmt->fetchAll(PDO::FETCH_COLUMN));
                    }
                    echo json_encode(['status' => 'success', 'data' => $pizza]);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Pizza non trovata.']);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'ID mancante.']);
            }
            break;
            
        case 'save_pizza':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $id = $input['pizza_id'] ?? null;
                $name = $input['name'] ?? '';
                $type = $input['type'] ?? 'classica';
                $base_price = $input['base_price'] ?? 0;
                $description = $input['ingredients_text'] ?? '';
                $is_visible = $input['is_visible'] ?? 1;

                if (empty($name)) {
                    echo json_encode(['status' => 'error', 'message' => 'Il nome è obbligatorio.']);
                    exit;
                }

                if ($id) {
                    // Update
                    $stmt = $pdo->prepare("UPDATE pizzas SET name=?, type=?, base_price=?, description=?, is_visible=? WHERE id=? AND user_id=?");
                    $stmt->execute([$name, $type, $base_price, $description, $is_visible, $id, $user_id]);
                    echo json_encode(['status' => 'success', 'message' => 'Pizza aggiornata.']);
                } else {
                    // Insert
                    $posStmt = $pdo->prepare("SELECT MAX(position) FROM pizzas WHERE user_id=? AND type=?");
                    $posStmt->execute([$user_id, $type]);
                    $maxPos = $posStmt->fetchColumn() ?: 0;
                    
                    $stmt = $pdo->prepare("INSERT INTO pizzas (user_id, name, type, base_price, description, is_visible, position) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$user_id, $name, $type, $base_price, $description, $is_visible, $maxPos + 1]);
                    echo json_encode(['status' => 'success', 'message' => 'Pizza creata.']);
                }
            }
            break;

        case 'delete_pizza':
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($input['id'])) {
                try {
                    $pdo->beginTransaction();
                    $pdo->prepare("DELETE FROM pizza_ingredients WHERE pizza_id = ?")->execute([$input['id']]);
                    $stmt = $pdo->prepare("DELETE FROM pizzas WHERE id = ? AND user_id = ?");
                    $stmt->execute([$input['id'], $user_id]);
                    $pdo->commit();
                    echo json_encode(['status' => 'success', 'message' => 'Pizza eliminata.']);
                } catch (\PDOException $e) {
                    if ($pdo->inTransaction()) {
                        $pdo->rollBack();
                    }
                    echo json_encode(['status' => 'error', 'message' => 'Errore durante l\'eliminazione: ' . $e->getMessage()]);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Richiesta non valida.']);
            }
            break;

        case 'get_ingredient':
            if (isset($_GET['id'])) {
                $stmt = $pdo->prepare("SELECT * FROM ingredients WHERE id = ? AND user_id = ?");
                $stmt->execute([$_GET['id'], $user_id]);
                echo json_encode(['status' => 'success', 'data' => $stmt->fetch(PDO::FETCH_ASSOC)]);
            }
            break;

        case 'save_ingredient':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $id = $input['ingredient_id'] ?? null;
                $name = $input['name'] ?? '';
                $price = $input['price'] ?? 0;

                if ($id) {
                    $stmt = $pdo->prepare("UPDATE ingredients SET name=?, price=? WHERE id=? AND user_id=?");
                    $stmt->execute([$name, $price, $id, $user_id]);
                } else {
                    $stmt = $pdo->prepare("INSERT INTO ingredients (user_id, name, price) VALUES (?, ?, ?)");
                    $stmt->execute([$user_id, $name, $price]);
                }
                echo json_encode(['status' => 'success']);
            }
            break;

        case 'delete_ingredient':
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($input['id'])) {
                $stmt = $pdo->prepare("DELETE FROM ingredients WHERE id = ? AND user_id = ?");
                $stmt->execute([$input['id'], $user_id]);
                echo json_encode(['status' => 'success']);
            }
            break;

        case 'get_product':
            if (isset($_GET['id'])) {
                $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND user_id = ?");
                $stmt->execute([$_GET['id'], $user_id]);
                echo json_encode(['status' => 'success', 'data' => $stmt->fetch(PDO::FETCH_ASSOC)]);
            }
            break;

        case 'save_product':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $id = $input['product_id'] ?? null;
                $name = $input['name'] ?? '';
                $category = $input['category'] ?? 'stuzzicheria';
                $price = $input['price'] ?? 0;
                $is_visible = $input['is_visible'] ?? 1;

                if ($id) {
                    $stmt = $pdo->prepare("UPDATE products SET name=?, category=?, price=?, is_visible=? WHERE id=? AND user_id=?");
                    $stmt->execute([$name, $category, $price, $is_visible, $id, $user_id]);
                } else {
                    $stmt = $pdo->prepare("INSERT INTO products (user_id, name, category, price, is_visible) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$user_id, $name, $category, $price, $is_visible]);
                }
                echo json_encode(['status' => 'success']);
            }
            break;

        case 'delete_product':
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($input['id'])) {
                $stmt = $pdo->prepare("DELETE FROM products WHERE id = ? AND user_id = ?");
                $stmt->execute([$input['id'], $user_id]);
                echo json_encode(['status' => 'success']);
            }
            break;

        // --- NUOVE API PER CARICARE I DATI COMPLETI ---

        case 'get_pizzas':
            $pizzas_q = $pdo->prepare("SELECT id, name, type, base_price, is_visible, position, description FROM pizzas WHERE user_id = ? ORDER BY position");
            $pizzas_q->execute([$user_id]);
            $pizzas = $pizzas_q->fetchAll();

            foreach ($pizzas as &$pizza) {
                if (!empty($pizza['description'])) {
                    $pizza['ingredients'] = $pizza['description'];
                } else {
                    $ing_stmt = $pdo->prepare("SELECT i.name FROM ingredients i JOIN pizza_ingredients pi ON i.id = pi.ingredient_id WHERE pi.pizza_id = ?");
                    $ing_stmt->execute([$pizza['id']]);
                    $pizza['ingredients'] = implode(', ', $ing_stmt->fetchAll(PDO::FETCH_COLUMN));
                }
                $pizza['calculated_price'] = calculatePizzaPrice($pdo, $pizza['id'], $pizza['base_price']);
            }
            echo json_encode(['status' => 'success', 'data' => $pizzas]);
            break;

        case 'get_ingredients':
            $ingredients_q = $pdo->prepare("SELECT id, name, price FROM ingredients WHERE user_id = ? ORDER BY name");
            $ingredients_q->execute([$user_id]);
            echo json_encode(['status' => 'success', 'data' => $ingredients_q->fetchAll()]);
            break;

        case 'get_products':
            $products_q = $pdo->prepare("SELECT id, name, category, price, is_visible FROM products WHERE user_id = ? ORDER BY category, name");
            $products_q->execute([$user_id]);
            echo json_encode(['status' => 'success', 'data' => $products_q->fetchAll()]);
            break;

        case 'get_categories':
            $cat_q = $pdo->prepare("SELECT * FROM pizza_categories WHERE user_id = ? ORDER BY sort_order, name");
            $cat_q->execute([$user_id]);
            echo json_encode(['status' => 'success', 'data' => $cat_q->fetchAll()]);
            break;

        case 'save_category':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $id = $input['id'] ?? null;
                $name = trim($input['name'] ?? '');
                $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', $name));
                $is_visible = $input['is_visible'] ?? 1;

                if (empty($name)) {
                    echo json_encode(['status' => 'error', 'message' => 'Il nome è obbligatorio.']);
                    exit;
                }

                if ($id) {
                    $stmt = $pdo->prepare("UPDATE pizza_categories SET name=?, slug=?, is_visible=? WHERE id=? AND user_id=?");
                    $stmt->execute([$name, $slug, $is_visible, $id, $user_id]);
                    echo json_encode(['status' => 'success', 'message' => 'Categoria aggiornata.', 'slug' => $slug]);
                } else {
                    $maxOrd = $pdo->prepare("SELECT MAX(sort_order) FROM pizza_categories WHERE user_id=?");
                    $maxOrd->execute([$user_id]);
                    $stmt = $pdo->prepare("INSERT INTO pizza_categories (user_id, name, slug, is_visible, sort_order) VALUES (?,?,?,?,?)");
                    $stmt->execute([$user_id, $name, $slug, $is_visible, ($maxOrd->fetchColumn() ?? 0) + 1]);
                    echo json_encode(['status' => 'success', 'message' => 'Categoria creata.', 'id' => $pdo->lastInsertId(), 'slug' => $slug]);
                }
            }
            break;

        case 'delete_category':
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($input['id'])) {
                // Move pizzas from deleted category to first available category
                $first = $pdo->prepare("SELECT slug FROM pizza_categories WHERE user_id=? AND id != ? ORDER BY sort_order LIMIT 1");
                $first->execute([$user_id, $input['id']]);
                $firstSlug = $first->fetchColumn() ?: 'classica';
                $pdo->prepare("UPDATE pizzas SET type=? WHERE user_id=? AND type=(SELECT slug FROM pizza_categories WHERE id=?)")->execute([$firstSlug, $user_id, $input['id']]);
                $pdo->prepare("DELETE FROM pizza_categories WHERE id=? AND user_id=?")->execute([$input['id'], $user_id]);
                echo json_encode(['status' => 'success', 'message' => 'Categoria eliminata.']);
            }
            break;

        case 'toggle_category_visibility':
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($input['id'])) {
                $stmt = $pdo->prepare("UPDATE pizza_categories SET is_visible = ? WHERE id = ? AND user_id = ?");
                $stmt->execute([$input['is_visible'], $input['id'], $user_id]);
                echo json_encode(['status' => 'success']);
            }
            break;

        case 'save_category_position':
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($input['id'])) {
                $stmt = $pdo->prepare("UPDATE pizza_categories SET col_pos = ?, row_pos = ?, col_span = ?, inner_cols = ?, screen_number = ? WHERE id = ? AND user_id = ?");
                $stmt->execute([
                    $input['col_pos'] ?? 1,
                    $input['row_pos'] ?? 1,
                    $input['col_span'] ?? 1,
                    $input['inner_cols'] ?? 1,
                    $input['screen_number'] ?? 1,
                    $input['id'],
                    $user_id
                ]);
                echo json_encode(['status' => 'success', 'message' => 'Posizione salvata.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Dati mancanti.']);
            }
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Azione non riconosciuta.']);
            break;

    }
} catch (\PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("DB Error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Errore di database.']);
}