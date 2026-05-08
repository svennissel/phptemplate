<?php
/**
 * Einstiegspunkt: Wenn keine config.php existiert, zum Installer leiten.
 * Andernfalls Login prüfen und zur Übersichtsseite weiterleiten.
 */
if (!file_exists(__DIR__ . '/config.php')) {
    header('Location: installer.php');
    exit;
}

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/twig.php';

// Auto-Login per Cookie, falls noch keine Session aktiv ist
if (!isLoggedIn() && isset($_COOKIE['hash'])) {
    loginByHash($_COOKIE['hash']);
}

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

header('Location: users.php');
exit;
