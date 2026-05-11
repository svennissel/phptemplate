<?php
/**
 * API Endpoint für Vorschläge beim Eintippen.
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

// Authentifizierung prüfen
$currentUser = requireLogin(); // Da es ein API call ist, evtl. besser getCurrentUser + 401, aber requireLogin leitet um.
// Da die App Single Page orientiert ist, sollte requireLogin hier funktionieren oder wir nutzen getCurrentUser.
// In sync.php wird getCurrentUser genutzt.

$listId = (int)($_GET['list_id'] ?? 0);
$query  = trim($_GET['q'] ?? '');

if ($listId <= 0 || strlen($query) < 1) {
    echo json_encode([]);
    exit;
}

global $pdo;

// Vorschläge:
// - Aus shopping_item_usage für diese Liste
// - Text enthält $query
// - Noch nicht in shopping_list_items für diese Liste vorhanden
// - Sortiert nach Häufigkeit und Datum
$stmt = $pdo->prepare('
    SELECT u.name 
    FROM shopping_item_usage u
    WHERE u.list_id = ? 
      AND u.name LIKE ?
      AND u.name NOT IN (SELECT name FROM shopping_list_items WHERE list_id = ?)
    ORDER BY u.usage_count DESC, u.last_added_at DESC
    LIMIT 10
');

$stmt->execute([$listId, '%' . $query . '%', $listId]);
$suggestions = $stmt->fetchAll(PDO::FETCH_COLUMN);

header('Content-Type: application/json');
echo json_encode($suggestions);
