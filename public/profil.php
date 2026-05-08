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
require_once __DIR__ . '/includes/url.php';

$currentUser = requireLogin();
$id      = (int)$currentUser['id'];

global $pdo;
$stmt = $pdo->prepare('SELECT id, name, hash, is_admin, created_at FROM users WHERE id = ?');
$stmt->execute([$id]);
$user = $stmt->fetch();

global $twig;
if (!$user) {
    http_response_code(404);
    echo $twig->render('profil.html.twig', [
        'currentUser' => $currentUser,
        'user'        => null,
        'loginUrl'    => null,
        'activePage'  => 'profil',
    ]);
    exit;
}

$loginUrl = getLoginUrl($user['hash'] ?? '');

echo $twig->render('profil.html.twig', [
    'currentUser' => $currentUser,
    'user'        => $user,
    'loginUrl'    => $loginUrl,
    'activePage'  => 'profil',
]);
