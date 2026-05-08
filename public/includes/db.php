<?php
require_once __DIR__ . '/../config.php';

/**
 * Globale PDO-Instanz für die MySQL-Datenbankverbindung.
 *
 * @var PDO $pdo
 */
try {
    $database = DATABASE;
    $overrideFile = __DIR__ . '/../../.test_database';
    if (file_exists($overrideFile)) {
        $overrideDb = trim(file_get_contents($overrideFile));
        if (!empty($overrideDb)) {
            $database = $overrideDb;
        }
    }

    $dsn = 'mysql:host=' . DATABASE_HOST . ';dbname=' . $database . ';charset=utf8mb4';
    $pdo = new PDO($dsn, DATABASE_USER, DATABASE_PASSWORD, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    exit('Datenbankverbindung fehlgeschlagen.');
}
