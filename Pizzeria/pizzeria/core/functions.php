<?php
/**
 * Funzione per il calcolo automatico del prezzo della pizza
 * Prezzo Totale = Base Price + Somma dei prezzi degli ingredienti aggiuntivi
 *
 * @param PDO $pdo Istanza di connessione al database.
 * @param int $pizza_id ID della pizza.
 * @param float $base_price Prezzo base impostato dall'utente.
 * @return string Prezzo totale formattato (es. "10.50").
 */
function calculatePizzaPrice(PDO $pdo, $pizza_id, $base_price) {
    try {
        // Query per sommare i prezzi degli ingredienti
        $stmt = $pdo->prepare("
            SELECT SUM(i.price) as total_ingredients 
            FROM ingredients i 
            JOIN pizza_ingredients pi ON i.id = pi.ingredient_id 
            WHERE pi.pizza_id = ?
        ");
        $stmt->execute([$pizza_id]);
        $ingredients_total = $stmt->fetchColumn() ?: 0;
        
        $total_price = (float)$base_price + (float)$ingredients_total;
        
        return number_format($total_price, 2, '.', '');
    } catch (\PDOException $e) {
        // Gestione errore
        error_log("Errore nel calcolo del prezzo della pizza ID $pizza_id: " . $e->getMessage());
        return number_format($base_price, 2, '.', ''); // Restituisce solo il prezzo base in caso di errore
    }
}
?>