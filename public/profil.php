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
$id      = isset($_GET['id']) ? (int)$_GET['id'] : (int)$current['id'];

// Nur Admins dürfen fremde Profile aufrufen
if ($id !== (int)$current['id'] && empty($current['is_admin'])) {
    $id = (int)$current['id'];
}

$stmt = $pdo->prepare('SELECT id, name, email, hash, is_admin, created_at FROM users WHERE id = ?');
$stmt->execute([$id]);
$user = $stmt->fetch();

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
