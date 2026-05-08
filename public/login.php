<?php
/**
 * Login per Hash-Link. Der Hash kann via GET-Parameter, Cookie
 * oder bestehender Session übergeben werden. Bei Erfolg wird ein
 * Cookie für ein Jahr gesetzt und auf die Übersichtsseite weitergeleitet.
 */
if (!file_exists(__DIR__ . '/config.php')) {
    header('Location: installer.php');
    exit;
}

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/twig.php';

$error = '';

$hash = $_GET['hash'] ?? $_COOKIE['hash'] ?? $_SESSION['hash'] ?? '';
if ($hash !== '') {
    if (loginByHash($hash)) {
        header('Location: users.php');
        exit;
    }
    $error = 'Ungültiger Login-Link.';
}

echo $twig->render('login.html.twig', ['error' => $error]);
