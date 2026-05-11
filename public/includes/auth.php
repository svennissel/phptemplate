<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/db.php';

/**
 * Erkennt, ob die Anfrage über HTTPS ausgeliefert wird.
 */
function isSecureServer(): bool {
    if ((isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] === 'on' || $_SERVER['HTTPS'] == 1)) ||
        (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')) {
        return true;
    }
    return false;
}

/**
 * Ermittelt die Hauptdomain aus dem Servernamen.
 * Entfernt Subdomains, sodass z.B. phptemplate.nissel.it zu nissel.it wird.
 */
function getMainDomain(): string {
    $host = $_SERVER['SERVER_NAME'] ?? '';
    if (filter_var($host, FILTER_VALIDATE_IP)) {
        return $host;
    }
    $parts = explode('.', $host);
    $numParts = count($parts);
    if ($numParts <= 2) {
        return $host;
    }
    // Spezialfall für localhost oder ähnliches
    if ($numParts === 1) {
        return $host;
    }
    // Letzte zwei Teile nehmen (z.B. nissel.it)
    return $parts[$numParts - 2] . '.' . $parts[$numParts - 1];
}

$mainDomain = getMainDomain();

session_start([
    'cookie_lifetime' => COOKIE_LIFETIME,
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax',
    'cookie_secure'   => isSecureServer(),
    'cookie_domain'   => $mainDomain,
]);

function isLoggedIn(): bool {
    return isset($_SESSION[SESSION_HASH_KEY]);
}

function getLoginHash(): ?string {
    return $_SESSION[SESSION_HASH_KEY] ?? null;
}

/**
 * Lädt den aktuell eingeloggten Benutzer aus der Datenbank.
 */
function getCurrentUser(): ?array {
    global $pdo;
    if (!isLoggedIn()) return null;
    $stmt = $pdo->prepare('SELECT id, name, hash, is_admin, created_at FROM users WHERE hash = ?');
    $stmt->execute([getLoginHash()]);
    $user = $stmt->fetch();
    return $user ?: null;
}

/**
 * Login per Hash. Setzt Session und einen Cookie für 1 Jahr.
 */
function loginByHash(string $hash): bool {
    global $pdo;
    if ($hash === '') return false;

    $stmt = $pdo->prepare('SELECT id, name, hash, is_admin, created_at FROM users WHERE hash = ?');
    $stmt->execute([$hash]);
    $user = $stmt->fetch();
    if (!$user) return false;

    $_SESSION[SESSION_HASH_KEY] = $user['hash'];
    setcookie(LOGIN_COOKIE_NAME, $user['hash'], [
        'expires'  => time() + COOKIE_LIFETIME,
        'path'     => '/',
        'domain'   => getMainDomain(),
        'secure'   => isSecureServer(),
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    return true;
}

function logout(): void {
    session_destroy();
    unset($_COOKIE[LOGIN_COOKIE_NAME]);
    setcookie(LOGIN_COOKIE_NAME, '', [
        'expires'  => 1,
        'path'     => '/',
        'domain'   => getMainDomain(),
        'secure'   => isSecureServer(),
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
}

/**
 * Erfordert einen eingeloggten Benutzer; leitet sonst zur Login-Seite weiter.
 */
function requireLogin(): array {
    $currentUser = getCurrentUser();
    if ($currentUser != null)
        return $currentUser;
    if (isset($_COOKIE[LOGIN_COOKIE_NAME]) && loginByHash($_COOKIE[LOGIN_COOKIE_NAME]))
        return getCurrentUser();
    header('Location: login.php');
    exit;
}

