<?php
/**
 * Twig-Bootstrap. Initialisiert eine globale Twig-Environment-Instanz,
 * die in allen Seiten zum Rendern der Templates verwendet wird.
 */
require_once __DIR__ . '/../vendor/autoload.php';

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

/**
 * @var Environment $twig
 */
$twig = (function (): Environment {
    $loader = new FilesystemLoader(__DIR__ . '/../templates');
    
    $cache = false;
    if (defined('TWIG_CACHE_DIR') && TWIG_CACHE_DIR !== '') {
        $cachePath = __DIR__ . '/../' . TWIG_CACHE_DIR;
        // Wenn das Verzeichnis nicht existiert, versuchen wir es zu erstellen
        if (!is_dir($cachePath)) {
            @mkdir($cachePath, 0775, true);
        }
        // Nur verwenden, wenn es beschreibbar ist
        if (is_writable($cachePath)) {
             $cache = $cachePath;
        }
    }

    $twig = new Environment($loader, [
        'cache'      => $cache,
        'autoescape' => 'html',
        'debug'      => false,
    ]);

    // Cache-Busting: Eine Funktion zum Generieren von Asset-URLs mit Zeitstempel hinzufügen
    $twig->addFunction(new \Twig\TwigFunction('asset', function ($path) {
        $fullPath = __DIR__ . '/../' . ltrim($path, '/');
        if (file_exists($fullPath)) {
            $version = filemtime($fullPath);
            return $path . '?v=' . $version;
        }
        return $path;
    }));

    return $twig;
})();
