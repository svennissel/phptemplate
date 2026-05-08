# Projekterstellung aus dem PHPTemplate

Dieses Projekt dient als Vorlage (Template) für neue PHP-Anwendungen. Um aus dieser Vorlage ein neues Projekt zu erstellen, müssen die folgenden Stellen angepasst werden:

### 1. Konfiguration
- **`public/config-template.php`**: Passen Sie hier den Standardwert für `SESSION_NAME` (Zeile 50) an, um Konflikte mit anderen Projekten auf demselben Server zu vermeiden.

### 2. Metadaten & Manifest
- **`public/site.webmanifest`**: Ändern Sie `name` (Zeile 2) und `short_name` (Zeile 3) auf den Namen Ihres neuen Projekts.
- **`composer.json`**: Passen Sie den Projektnamen `"name": "snissel/phptemplate"` (Zeile 2) und den Autor (Zeile 28) an.
- **`package.json`**: Ändern Sie den Namen `"name": "phpteamplate-tests"` (Zeile 2).

### 3. Design & Templates
- **`public/css/style.css`**: Passen Sie ggf. Farben und Stile an.

### 4. Tests & Entwicklung
- **`playwright.config.ts`**: Passen Sie die `baseURL` (Zeile 13) an Ihre lokale Entwicklungs-URL an.
- **`tests/e2e/app.spec.ts`**: Ändern Sie die Beschreibungen in den Tests (z. B. Zeile 3: `test.describe('PHP Template App', ...)`).
- **`run_playwright.sh`**: Falls Sie automatisierte E2E-Tests nutzen, passen Sie hier ggf. `DB_USER` und `DB_PASS` (Zeilen 6-7) für die Test-Datenbank an.

### 5. Datenbank
- **`public/sql/schema.sql`**: Definieren Sie hier Ihr initiales Datenbankschema für das neue Projekt.
