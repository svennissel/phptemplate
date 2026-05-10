<?php
/**
 * Startseite: Einkaufzettel.
 *
 * - Zeigt einen Einkaufzettel mit Suchfeld zum Hinzufügen von Einträgen.
 * - Klick auf einen Eintrag löscht ihn (mit 5-Sekunden-Undo, clientseitig).
 * - Mehrere Einkaufzettel werden in der Bottom-Nav angezeigt.
 * - Über das "+" in der Kopfzeile können neue Einkaufzettel erstellt werden.
 */
if (!file_exists(__DIR__ . '/config.php')) {
    header('Location: installer.php');
    exit;
}

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/twig.php';
require_once __DIR__ . '/includes/shopping.php';

$currentUser = requireLogin();

global $pdo;

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Ungültiger CSRF-Token.';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'create_list') {
            $name = trim($_POST['name'] ?? '');
            if ($name !== '') {
                $stmt = $pdo->prepare('INSERT INTO shopping_lists (name) VALUES (?)');
                $stmt->execute([$name]);
                $newId = (int)$pdo->lastInsertId();
                header('Location: start.php?id=' . $newId);
                exit;
            }
            $error = 'Name darf nicht leer sein.';
        } elseif ($action === 'add_item') {
            $listId = (int)($_POST['list_id'] ?? 0);
            $name   = trim($_POST['name'] ?? '');
            if ($listId > 0 && $name !== '' && getShoppingList($listId) !== null) {
                $stmt = $pdo->prepare('INSERT INTO shopping_list_items (list_id, name) VALUES (?, ?)');
                $stmt->execute([$listId, $name]);
            }
            header('Location: start.php?id=' . $listId);
            exit;
        } elseif ($action === 'delete_item') {
            $itemId = (int)($_POST['item_id'] ?? 0);
            $listId = (int)($_POST['list_id'] ?? 0);
            if ($itemId > 0) {
                $stmt = $pdo->prepare('DELETE FROM shopping_list_items WHERE id = ?');
                $stmt->execute([$itemId]);
            }
            header('Location: start.php?id=' . $listId);
            exit;
        } elseif ($action === 'update_item_amount') {
            $itemId = (int)($_POST['item_id'] ?? 0);
            $listId = (int)($_POST['list_id'] ?? 0);
            $delta  = (int)($_POST['delta'] ?? 0);
            if ($itemId > 0 && $delta !== 0) {
                $stmt = $pdo->prepare('UPDATE shopping_list_items SET amount = GREATEST(1, amount + ?) WHERE id = ?');
                $stmt->execute([$delta, $itemId]);
            }
            header('Location: start.php?id=' . $listId);
            exit;
        }
    }
}

$lists = getShoppingLists();

$selectedListId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$currentList    = null;
if ($selectedListId > 0) {
    $currentList = getShoppingList($selectedListId);
}
if ($currentList === null && !empty($lists)) {
    $currentList    = $lists[0];
    $selectedListId = (int)$currentList['id'];
}

$items = $currentList ? getShoppingListItems((int)$currentList['id']) : [];

echo $twig->render('start.html.twig', [
    'currentUser'    => $currentUser,
    'activePage'     => 'start',
    'activeListId'   => $selectedListId,
    'lists'          => $lists,
    'currentList'    => $currentList,
    'items'          => $items,
    'csrfToken'      => generateCsrfToken(),
    'error'          => $error,
]);
