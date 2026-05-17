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
require_once __DIR__ . '/includes/shopping.php';
require_once __DIR__ . '/includes/image_processing.php';

$currentUser = requireLogin();
$id      = (int)$currentUser['id'];

global $pdo;

// Profilbild-Upload verarbeiten
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_image'])) {
    $cropData = $_POST['crop_data'] ?? null;
    $crop = null;
    if ($cropData) {
        $crop = explode(',', $cropData);
    }
    $logo = processProfileImage($_FILES['profile_image'], $crop);
    if ($logo) {
        $stmt = $pdo->prepare('UPDATE users SET profile_image = ? WHERE id = ?');
        $stmt->execute([$logo, $id]);
        
        // Session aktualisieren, falls nötig (getCurrentUser nutzt die DB, aber wir könnten hier einen Redirect machen)
        header('Location: profil.php');
        exit;
    }
}

$stmt = $pdo->prepare('SELECT id, name, hash, is_admin, profile_image, created_at FROM users WHERE id = ?');
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
        'lists'       => getShoppingLists(),
    ]);
    exit;
}

$loginUrl = getLoginUrl($user['hash'] ?? '');

echo $twig->render('profil.html.twig', [
    'currentUser' => $currentUser,
    'user'        => $user,
    'loginUrl'    => $loginUrl,
    'activePage'  => 'profil',
    'lists'       => getShoppingLists(),
]);
