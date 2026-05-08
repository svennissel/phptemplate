<?php
/**
 * Hilfsfunktionen für URLs.
 */

/**
 * Erzeugt die vollständige Login-URL für einen gegebenen Hash.
 * Wenn kein Hash angegeben wird, wird die Basis-URL zurückgegeben.
 */
function getLoginUrl(string $hash = ''): string {
    $scheme   = isSecureServer() ? 'https' : 'http';
    $host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
    
    $url = $scheme . '://' . $host . $basePath . '/login.php?hash=';
    
    if ($hash !== '') {
        $url .= urlencode($hash);
    }
    
    return $url;
}
