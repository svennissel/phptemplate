<?php
/**
 * Übersichtsseite – mobiloptimierte Liste aller Benutzer.
 */
if (!file_exists(__DIR__ . '/config.php')) {
    header('Location: installer.php');
    exit;
}

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/twig.php';
require_once __DIR__ . '/includes/url.php';
require_once __DIR__ . '/includes/hash.php';

$currentUser = requireLogin();

if (empty($currentUser['is_admin'])) {
    header('Location: start.php');
    exit;
}

// Benutzer bearbeiten (nur Admins)
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($currentUser['is_admin'])) {
    global $pdo;
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Ungültiger CSRF-Token.';
    } else {
        $editId      = ($_POST['id'] !== '') ? (int)$_POST['id'] : null;
        $editName    = trim($_POST['name'] ?? '');
        $editIsAdmin = isset($_POST['is_admin']) ? 1 : 0;
        $action      = $_POST['action'] ?? 'save';

        if ($action === 'delete' && $editId) {
            if ($editId === (int)$currentUser['id']) {
                $error = 'Man kann sich nicht selbst löschen.';
            } else {
                try {
                    $stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
                    $stmt->execute([$editId]);
                    header('Location: users.php');
                    exit;
                } catch (PDOException $e) {
                    $error = 'Fehler beim Löschen: ' . $e->getMessage();
                }
            }
        } elseif ($editName !== '') {
            try {
                if ($editId) {
                    // Update bestehender Benutzer
                    $stmt = $pdo->prepare('UPDATE users SET name = ?, is_admin = ? WHERE id = ?');
                    $stmt->execute([$editName, $editIsAdmin, $editId]);
                } else {
                    // Neuer Benutzer
                    $hash = createHash();
                    $stmt = $pdo->prepare('INSERT INTO users (name, hash, is_admin) VALUES (?, ?, ?)');
                    $stmt->execute([$editName, $hash, $editIsAdmin]);
                }
                header('Location: users.php');
                exit;
            } catch (PDOException $e) {
                $error = 'Fehler beim Speichern: ' . $e->getMessage();
            }
        } else {
            $error = 'Name darf nicht leer sein.';
        }
    }
}

global $pdo, $twig;
$stmt = $pdo->query('SELECT id, name, hash, is_admin, created_at FROM users ORDER BY name');
$users = $stmt->fetchAll();

$loginBaseUrl = getLoginUrl();

echo $twig->render('users.html.twig', [
    'currentUser' => $currentUser,
    'users'       => $users,
    'activePage'  => 'users',
    'csrfToken'   => generateCsrfToken(),
    'error'       => $error,
    'loginBaseUrl' => $loginBaseUrl,
]);
