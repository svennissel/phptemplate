<?php
/**
 * Benutzerseite – zeigt das eigene Profil bzw. das eines Benutzers
 * (für Admins) und stellt den persönlichen Login-Link dar.
 */
if (!file_exists(__DIR__ . '/config.php')) {
    header('Location: installer.php');
    exit;
}

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/twig.php';

requireLogin();

$current = getCurrentUser();
$id      = (int)$current['id'];

global $pdo;
$stmt = $pdo->prepare('SELECT id, name, hash, is_admin, created_at FROM users WHERE id = ?');
$stmt->execute([$id]);
$user = $stmt->fetch();

global $twig;
if (!$user) {
    http_response_code(404);
    echo $twig->render('profil.html.twig', [
        'currentUser' => $current,
        'user'        => null,
        'loginUrl'    => null,
        'activePage'  => 'profil',
    ]);
    exit;
}

$scheme   = isSecureServer() ? 'https' : 'http';
$host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
$basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
$loginUrl = $scheme . '://' . $host . $basePath . '/login.php?hash=' . urlencode($user['hash']);

echo $twig->render('profil.html.twig', [
    'currentUser' => $current,
    'user'        => $user,
    'loginUrl'    => $loginUrl,
    'activePage'  => 'profil',
]);
