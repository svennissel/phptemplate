<?php
/**
 * API Endpoint für die Synchronisation von Änderungen aus der IndexedDB.
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

// Authentifizierung prüfen
$currentUser = getCurrentUser();
if (!$currentUser) {
    if (isset($_COOKIE[LOGIN_COOKIE_NAME]) && loginByHash($_COOKIE[LOGIN_COOKIE_NAME])) {
        $currentUser = getCurrentUser();
    }
}

if (!$currentUser) {
    http_response_code(401);
    echo json_encode(['error' => 'Nicht eingeloggt']);
    exit;
}

// JSON Input lesen
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['error' => 'Ungültige Daten']);
    exit;
}

global $pdo;
$pdo->beginTransaction();

try {
    $results = [];
    foreach ($data as $change) {
        $action = $change['action'] ?? '';
        $payload = $change['data'] ?? [];

        switch ($action) {
            case 'add_item':
                $listId = (int)($payload['list_id'] ?? 0);
                $name = trim($payload['name'] ?? '');
                $tempId = $payload['temp_id'] ?? null;
                if ($listId > 0 && $name !== '') {
                    $stmt = $pdo->prepare('INSERT INTO shopping_list_items (list_id, name) VALUES (?, ?)');
                    $stmt->execute([$listId, $name]);
                    if ($tempId) {
                        $results[] = [
                            'action' => 'add_item',
                            'temp_id' => $tempId,
                            'server_id' => (int)$pdo->lastInsertId()
                        ];
                    }
                }
                break;

            case 'delete_item':
                $itemId = (int)($payload['item_id'] ?? 0);
                if ($itemId > 0) {
                    $stmt = $pdo->prepare('DELETE FROM shopping_list_items WHERE id = ?');
                    $stmt->execute([$itemId]);
                }
                break;

            case 'update_item_amount':
                $itemId = (int)($payload['item_id'] ?? 0);
                $delta = (int)($payload['delta'] ?? 0);
                if ($itemId > 0 && $delta !== 0) {
                    $stmt = $pdo->prepare('UPDATE shopping_list_items SET amount = GREATEST(1, amount + ?) WHERE id = ?');
                    $stmt->execute([$delta, $itemId]);
                }
                break;
        }
    }
    $pdo->commit();
    echo json_encode(['success' => true, 'results' => $results]);
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
