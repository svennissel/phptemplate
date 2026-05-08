<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../public/includes/hash.php';

class HashTest extends TestCase
{
    public function testCreateHashReturnsString()
    {
        $hash = createHash();
        $this->assertIsString($hash);
    }

    public function testCreateHashHasExpectedLength()
    {
        // 16 bytes base64 encoded without padding should be 22 characters
        // base64: ceil(n * 4 / 3)
        // 16 * 4 / 3 = 21.33 -> 22
        $hash = createHash();
        $this->assertEquals(22, strlen($hash));
    }

    public function testCreateHashIsUnique()
    {
        $hash1 = createHash();
        $hash2 = createHash();
        $this->assertNotEquals($hash1, $hash2);
    }

    public function testCreateHashIsUrlSafe()
    {
        $hash = createHash();
        $this->assertDoesNotMatchRegularExpression('/[\+\/]/', $hash);
        $this->assertDoesNotMatchRegularExpression('/=$/', $hash);
    }
}
