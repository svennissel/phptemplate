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

    // Automatische Migration
    $stmt = $pdo->query("SELECT value FROM meta_info WHERE `key` = 'schema_version'");
    $version = $stmt->fetchColumn();
    while (true) {

        if ($version === false) {
            // Falls meta_info noch nicht existiert oder kein Eintrag vorhanden ist, abbrechen
            // Normalerweise wird das Schema durch installer.php oder initiales SQL erstellt.
            break;
        }

        $nextVersion = (int)$version + 1;
        $migrationFile = __DIR__ . '/../sql/migration_v' . $nextVersion . '.sql';

        if (file_exists($migrationFile)) {
            $sql = file_get_contents($migrationFile);
            $pdo->exec($sql);
            
            // Version in der Datenbank aktualisieren
            // Falls das Migrationsfile die Version nicht selbst aktualisiert, tun wir es hier sicherheitshalber.
            // Die meisten Migrationen im Projekt scheinen es aber selbst zu tun (siehe migration_v3.sql).
            // Um doppelte Updates zu vermeiden und sicherzustellen, dass die Schleife terminiert,
            // prüfen wir nach der Ausführung erneut die Version.
            
            $stmt = $pdo->query("SELECT value FROM meta_info WHERE `key` = 'schema_version'");
            $newVersion = $stmt->fetchColumn();
            
            if ((int)$newVersion === (int)$version) {
                // Migration hat Version nicht aktualisiert, wir machen es manuell
                $stmt = $pdo->prepare("UPDATE meta_info SET value = ? WHERE `key` = 'schema_version'");
                $stmt->execute([(string)$nextVersion]);
            }
        } else {
            break;
        }
    }
} catch (PDOException $e) {
    http_response_code(500);
    exit('Datenbankverbindung fehlgeschlagen.');
}
