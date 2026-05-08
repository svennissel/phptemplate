<?php
/**
 * Konfigurationsdatei
 *
 * Diese Datei enthält zentrale Konfigurationswerte und wird beim ersten Aufruf
 * vom Installer aus der config-template.php erzeugt. Die Platzhalter werden
 * dabei durch die vom Benutzer eingegebenen Werte ersetzt.
 */

/**
 * Hostname oder IP-Adresse der MySQL-Datenbank.
 */
const DATABASE_HOST = '{DATABASE_HOST}';

/**
 * Name der MySQL-Datenbank.
 */
const DATABASE = '{DATABASE}';

/**
 * Benutzername für die Datenbankverbindung.
 */
const DATABASE_USER = '{DATABASE_USER}';

/**
 * Passwort für die Datenbankverbindung.
 */
const DATABASE_PASSWORD = '{DATABASE_PASSWORD}';

/**
 * Verschlüsselungsschlüssel für CSRF-Tokens (AES-256-CBC).
 */
const CSRF_ENCRYPTION_KEY = '{CSRF_ENCRYPTION_KEY}';

/**
 * Lebensdauer von Cookies und Sessions in Sekunden.
 * Standard: 1 Jahr (365 * 24 * 60 * 60).
 */
const COOKIE_LIFETIME = 31536000;

/**
 * Verzeichnis für den Twig-Cache.
 * Wenn leer, wird kein Cache verwendet.
 */
const TWIG_CACHE_DIR = '{TWIG_CACHE_DIR}';

/**
 * Name des Login-Cookies.
 */
const LOGIN_COOKIE_NAME = 'phpteamplate_hash';

/**
 * Key unter dem der login hash in der session gespeichert wird
 */
const SESSION_HASH_KEY = LOGIN_COOKIE_NAME;
