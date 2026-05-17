<?php
/**
 * Hilfsfunktionen für Einkaufzettel (shopping lists).
 */
require_once __DIR__ . '/db.php';

/**
 * Lädt alle Einkaufzettel (id, name) sortiert nach Erstellungsdatum.
 *
 * @return array<int, array{id:int, name:string}>
 */
function getShoppingLists(): array {
    global $pdo;
    $stmt = $pdo->query('SELECT id, name FROM shopping_lists ORDER BY created_at ASC, id ASC');
    return $stmt->fetchAll();
}

/**
 * Lädt einen Einkaufzettel anhand der ID.
 *
 * @return array{id:int, name:string}|null
 */
function getShoppingList(int $id): ?array {
    global $pdo;
    $stmt = $pdo->prepare('SELECT id, name FROM shopping_lists WHERE id = ?');
    $stmt->execute([$id]);
    $list = $stmt->fetch();
    return $list ?: null;
}

/**
 * Lädt alle Einträge eines Einkaufzettels.
 *
 * @return array<int, array{id:int, name:string, amount:int}>
 */
function getShoppingListItems(int $listId): array {
    global $pdo;
    $stmt = $pdo->prepare('SELECT id, name, amount FROM shopping_list_items WHERE list_id = ? ORDER BY created_at ASC, id ASC');
    $stmt->execute([$listId]);
    return $stmt->fetchAll();
}

/**
 * Fügt einen Eintrag zu einem Einkaufzettel hinzu und aktualisiert die Statistik.
 */
function addShoppingListItem(int $listId, string $name, ?int $userId = null): int {
    global $pdo;
    
    // Item hinzufügen
    $stmt = $pdo->prepare('INSERT INTO shopping_list_items (list_id, name, user_id) VALUES (?, ?, ?)');
    $stmt->execute([$listId, $name, $userId]);
    $itemId = (int)$pdo->lastInsertId();
    
    // Statistik aktualisieren
    $stmt = $pdo->prepare('
        INSERT INTO shopping_item_usage (list_id, name, usage_count, last_added_at)
        VALUES (?, ?, 1, CURRENT_TIMESTAMP)
        ON DUPLICATE KEY UPDATE 
            usage_count = usage_count + 1,
            last_added_at = CURRENT_TIMESTAMP
    ');
    $stmt->execute([$listId, $name]);
    
    return $itemId;
}

/**
 * Lädt alle Benutzer, die Einträge auf einem bestimmten Einkaufzettel haben.
 *
 * @return array<int, array{id:int, name:string, profile_image:string|null}>
 */
function getShoppingListUsers(int $listId): array {
    global $pdo;
    $stmt = $pdo->prepare('
        SELECT DISTINCT u.id, u.name, u.profile_image 
        FROM users u
        JOIN shopping_list_items sli ON u.id = sli.user_id
        WHERE sli.list_id = ?
    ');
    $stmt->execute([$listId]);
    return $stmt->fetchAll();
}
