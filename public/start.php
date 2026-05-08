<?php
/**
 * Startseite.
 */
if (!file_exists(__DIR__ . '/config.php')) {
    header('Location: installer.php');
    exit;
}

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/twig.php';

requireLogin();

echo $twig->render('start.html.twig', [
    'currentUser' => getCurrentUser(),
    'activePage'  => 'start',
]);
