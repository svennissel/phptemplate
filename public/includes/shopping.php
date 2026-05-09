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
 * @return array<int, array{id:int, name:string}>
 */
function getShoppingListItems(int $listId): array {
    global $pdo;
    $stmt = $pdo->prepare('SELECT id, name FROM shopping_list_items WHERE list_id = ? ORDER BY created_at ASC, id ASC');
    $stmt->execute([$listId]);
    return $stmt->fetchAll();
}
