<?php
/**
 * Erzeugt einen zufälligen URL-sicheren Hash (Base64 ohne Padding) aus 16 Bytes.
 * Wird zur Authentifizierung von Benutzern über einen Login-Link verwendet.
 *
 * @return string
 * @throws \Random\RandomException
 */
function createHash(): string {
    return rtrim(strtr(base64_encode(random_bytes(16)), '+/', '-_'), '=');
}
