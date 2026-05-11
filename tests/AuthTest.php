<?php

use PHPUnit\Framework\TestCase;

// Wir müssen sicherstellen, dass auth.php geladen werden kann, ohne dass session_start() 
// oder DB-Verbindung Probleme machen.
// Da auth.php session_start() direkt aufruft, müssen wir es eventuell abfangen oder 
// die Header-Ausgabe unterdrücken.

class AuthTest extends TestCase
{
    private $pdoMock;

    protected function setUp(): void
    {
        // Wir setzen die Superglobals zurück
        $_SESSION = [];
        $_COOKIE = [];
        $_SERVER = [];
        
        // Da auth.php db.php einbindet, welches $pdo global setzt, 
        // müssen wir $pdo mocken bevor wir auth.php laden.
        // Das ist schwierig, da require_once in auth.php db.php lädt.
        
        $this->pdoMock = $this->createMock(PDO::class);
        $GLOBALS['pdo'] = $this->pdoMock;

        // Um isSecureServer zu testen
        $_SERVER['HTTPS'] = 'off';
        
        // Wir laden die Datei erst hier, um sicherzustellen, dass globale Variablen gesetzt sind
        require_once __DIR__ . '/../public/includes/auth.php';
    }

    public function testIsSecureServerReturnsFalseByDeafult()
    {
        $_SERVER['HTTPS'] = 'off';
        $this->assertFalse(isSecureServer());
    }

    public function testIsSecureServerReturnsTrueWhenHttpsOn()
    {
        $_SERVER['HTTPS'] = 'on';
        $this->assertTrue(isSecureServer());
    }

    public function testIsSecureServerReturnsTrueWhenHttps1()
    {
        $_SERVER['HTTPS'] = '1';
        $this->assertTrue(isSecureServer());
    }

    public function testIsSecureServerReturnsTrueWhenForwardedHttps()
    {
        $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';
        $this->assertTrue(isSecureServer());
    }

    public function testIsLoggedInReturnsFalseWhenNotSet()
    {
        $this->assertFalse(isLoggedIn());
    }

    public function testIsLoggedInReturnsTrueWhenSet()
    {
        $_SESSION[SESSION_HASH_KEY] = 'test_hash';
        $this->assertTrue(isLoggedIn());
    }

    public function testGetLoginHashReturnsNullWhenNotSet()
    {
        $this->assertNull(getLoginHash());
    }

    public function testGetLoginHashReturnsHashWhenSet()
    {
        $_SESSION[SESSION_HASH_KEY] = 'test_hash';
        $this->assertEquals('test_hash', getLoginHash());
    }


    public function testGetCurrentUserReturnsNullWhenNotLoggedIn()
    {
        $this->assertNull(getCurrentUser());
    }

    public function testGetCurrentUserReturnsUserWhenLoggedIn()
    {
        $_SESSION[SESSION_HASH_KEY] = 'my_hash';
        
        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->method('fetch')->willReturn(['id' => 1, 'name' => 'Test User', 'hash' => 'my_hash']);
        
        $this->pdoMock->method('prepare')->with($this->stringContains('SELECT id, name, hash, is_admin, created_at FROM users WHERE hash = ?'))
                      ->willReturn($stmtMock);
        
        $user = getCurrentUser();
        $this->assertIsArray($user);
        $this->assertEquals('Test User', $user['name']);
    }

    public function testLoginByHashFailsOnEmptyHash()
    {
        $this->assertFalse(loginByHash(''));
    }

    public function testLoginByHashFailsOnInvalidHash()
    {
        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->method('fetch')->willReturn(false);
        
        $this->pdoMock->method('prepare')->willReturn($stmtMock);
        
        $this->assertFalse(loginByHash('invalid'));
    }

    public function testLoginByHashSuccess()
    {
        $hash = 'valid_hash';
        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->method('fetch')->willReturn(['id' => 1, 'hash' => $hash]);
        
        $this->pdoMock->method('prepare')->willReturn($stmtMock);
        
        // Wir müssen setcookie unterdrücken oder mocken, falls möglich.
        // In PHPUnit Tests werden Header oft gesammelt.
        
        $this->assertTrue(loginByHash($hash));
        $this->assertEquals($hash, $_SESSION[SESSION_HASH_KEY]);
    }
}
