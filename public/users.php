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

requireLogin();
$currentUser = getCurrentUser();

// Benutzer bearbeiten (nur Admins)
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($currentUser['is_admin'])) {
    global $pdo;
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Ungültiger CSRF-Token.';
    } else {
        $editId      = (int)($_POST['id'] ?? 0);
        $editName    = trim($_POST['name'] ?? '');
        $editIsAdmin = isset($_POST['is_admin']) ? 1 : 0;

        if ($editId > 0 && $editName !== '') {
            $stmt = $pdo->prepare('UPDATE users SET name = ?, is_admin = ? WHERE id = ?');
            try {
                $stmt->execute([$editName, $editIsAdmin, $editId]);
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
