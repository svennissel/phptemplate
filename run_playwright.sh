#!/bin/bash
#npm install
#npx playwright install chromium
#npx playwright install firefox

DB_USER="einkauf"
DB_PASS='einkauf'

# Test-Datenbank aus Template erstellen
echo "Erstelle Test-Datenbank..."
mysql -u "$DB_USER" -p"$DB_PASS" < tests/setup_test_db.sql

# DB-Override-Datei für Test-Datenbank erstellen
echo "testdatabase" > .test_database

# Playwright Tests ausführen
npx playwright test "$@"
TEST_EXIT_CODE=$?

# DB-Override-Datei entfernen
rm -f .test_database

# Test-Datenbank löschen
echo "Lösche Test-Datenbank..."
mysql -u "$DB_USER" -p"$DB_PASS" -e "DROP DATABASE IF EXISTS testdatabase;"

exit $TEST_EXIT_CODE
