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

requireLogin();

$stmt = $pdo->query('SELECT id, name, email, hash, is_admin, created_at FROM users ORDER BY name');
$users = $stmt->fetchAll();

echo $twig->render('users.html.twig', [
    'currentUser' => getCurrentUser(),
    'users'       => $users,
    'activePage'  => 'users',
]);
