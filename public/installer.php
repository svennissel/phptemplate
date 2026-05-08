<?php
/**
 * Installer: Erstellt die config.php aus der config-template.php,
 * testet die Datenbankverbindung, importiert das Schema und legt einen
 * ersten Admin-Benutzer an.
 */
if (file_exists(__DIR__ . '/config.php')) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/includes/hash.php';
require_once __DIR__ . '/../vendor/autoload.php';

// TWIG_CACHE_DIR als Dummy definieren, damit twig.php nicht fehlschlägt
if (!defined('TWIG_CACHE_DIR')) {
    define('TWIG_CACHE_DIR', '');
}
require_once __DIR__ . '/includes/twig.php';

global $twig;

use Twig\Environment;

$error   = '';
$context = [
    'database_host'     => $_POST['database_host'] ?? 'localhost',
    'database'          => $_POST['database'] ?? '',
    'database_user'     => $_POST['database_user'] ?? '',
    'database_password' => $_POST['database_password'] ?? '',
    'admin_name'        => $_POST['admin_name'] ?? '',
    'twig_cache_dir'    => $_POST['twig_cache_dir'] ?? '../var/cache/twig',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($context['database'] === '' || $context['database_user'] === '' || $context['admin_name'] === '') {
        $error = 'Datenbank, Datenbankbenutzer und Admin-Name sind Pflichtfelder.';
    } else {
        try {
            $dsn = 'mysql:host=' . $context['database_host'] . ';dbname=' . $context['database'] . ';charset=utf8mb4';
            $pdo = new PDO($dsn, $context['database_user'], $context['database_password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);

            $tableCheck = $pdo->query("SHOW TABLES LIKE 'meta_info'");
            if ($tableCheck->rowCount() === 0) {
                $schema = file_get_contents(__DIR__ . '/sql/schema.sql');
                $pdo->exec($schema);
            }

            // Admin anlegen, falls noch nicht vorhanden
            $adminHash = createHash();
            $stmt = $pdo->prepare('SELECT id FROM users WHERE is_admin = 1 LIMIT 1');
            $stmt->execute();
            if ($stmt->rowCount() === 0) {
                $stmt = $pdo->prepare('INSERT INTO users (name, hash, is_admin) VALUES (?, ?, 1)');
                $stmt->execute([$context['admin_name'], $adminHash]);
            } else {
                // Falls bereits ein Admin existiert, nehmen wir dessen Hash für den Login
                $stmt = $pdo->prepare('SELECT hash FROM users WHERE is_admin = 1 LIMIT 1');
                $stmt->execute();
                $adminHash = $stmt->fetchColumn();
            }

            // Validierung Twig-Cache-Verzeichnis
            $twigCacheDir = trim($context['twig_cache_dir']);
            if ($twigCacheDir !== '') {
                $absCacheDir = __DIR__ . '/' . $twigCacheDir;
                if (!is_dir($absCacheDir)) {
                    if (!@mkdir($absCacheDir, 0775, true)) {
                        throw new Exception('Das Twig-Cache-Verzeichnis konnte nicht erstellt werden: ' . $twigCacheDir);
                    }
                }
                if (!is_writable($absCacheDir)) {
                    throw new Exception('Das Twig-Cache-Verzeichnis ist nicht beschreibbar: ' . $twigCacheDir);
                }
            }

            // config.php aus Template erzeugen
            $template = file_get_contents(__DIR__ . '/config-template.php');
            $csrfKey  = bin2hex(random_bytes(16));
            $config   = str_replace(
                ['{DATABASE_HOST}', '{DATABASE}', '{DATABASE_USER}', '{DATABASE_PASSWORD}', '{CSRF_ENCRYPTION_KEY}', '{TWIG_CACHE_DIR}'],
                [
                    addcslashes($context['database_host'], "'\\"),
                    addcslashes($context['database'], "'\\"),
                    addcslashes($context['database_user'], "'\\"),
                    addcslashes($context['database_password'], "'\\"),
                    $csrfKey,
                    addcslashes($twigCacheDir, "'\\")
                ],
                $template
            );
            if (file_put_contents(__DIR__ . '/config.php', $config) === false) {
                throw new Exception('Die config.php konnte nicht geschrieben werden. Bitte prüfen Sie die Schreibberechtigungen im Ordner public/.');
            }

            // Admin direkt einloggen via Hash-Link
            header('Location: login.php?hash=' . urlencode($adminHash));
            exit;
        } catch (PDOException $e) {
            $error = 'Datenbankverbindung fehlgeschlagen: ' . $e->getMessage();
        } catch (Exception $e) {
            $error = 'Fehler: ' . $e->getMessage();
        }
    }
}

echo $twig->render('installer.html.twig', $context + ['error' => $error]);
