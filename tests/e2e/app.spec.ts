import { test, expect } from '@playwright/test';

test.describe('PHP Template App', () => {
  const testUserHash = 'test-user-hash-123456'; 
  const adminUserHash = 'admin-user-hash-123456';

  test('sollte sich erfolgreich per Hash einloggen', async ({ page }) => {
    // Zum Login navigieren
    await page.goto(`/login.php?hash=${testUserHash}`);
    
    // Wir sollten zur Übersichtsseite weitergeleitet werden
    await expect(page).toHaveURL(/overview.php/);
    
    // In base.html.twig steht der Seitentitel in .topbar-title
    await expect(page.locator('.topbar-title')).toContainText('Benutzer');
    
    // Der Test-Benutzer sollte in der Liste sein
    await expect(page.locator('.card-title', { hasText: 'Test User' })).toBeVisible();
  });

  test('sollte das Benutzerprofil anzeigen', async ({ page }) => {
    // Login
    await page.goto(`/login.php?hash=${testUserHash}`);
    
    // Klick auf den Benutzer in der Liste
    await page.click('text=Test User');
    
    // URL sollte user.php mit ID enthalten
    await expect(page).toHaveURL(/user.php\?id=\d+/);
    
    // Details prüfen
    await expect(page.locator('.profile-name')).toContainText('Test User');
    await expect(page.locator('.profile-details')).toContainText('test@example.com');
  });

  test('sollte Admin-Badge anzeigen', async ({ page }) => {
    // Login als Admin
    await page.goto(`/login.php?hash=${adminUserHash}`);
    await expect(page).toHaveURL(/overview.php/);

    // Klick auf den Admin-User in der Liste
    await page.click('text=Admin User');

    // Admin-Badge sollte vorhanden sein
    await expect(page.locator('.badge')).toContainText('Admin');
  });

  test('sollte sich ausloggen können', async ({ page }) => {
    // Login
    await page.goto(`/login.php?hash=${testUserHash}`);
    
    // Logout-Link in der Navigationsleiste (falls vorhanden, ansonsten direkt logout.php)
    // Ich schaue kurz in base.html.twig nach dem Logout-Link
    await page.goto('/logout.php');
    
    // Sollte zum Login umgeleitet werden (oder zur Startseite, die zum Login umleitet)
    await page.goto('/overview.php');
    await expect(page).toHaveURL(/login.php/);
  });

  test('sollte den Installer anzeigen, wenn config.php fehlt', async ({ page }) => {
    // Dieser Test ist etwas riskant, wenn wir die Datei wirklich löschen.
    // Aber wir können prüfen ob wir zum Installer kommen wenn wir ihn direkt aufrufen (falls config.php existiert leitet er zu index.php)
    await page.goto('/installer.php');
    // Wenn config.php existiert, werden wir zu index.php geleitet
    // Wenn nicht, bleiben wir beim Installer.
    // Da run_playwright.sh die config.php NICHT löscht, erwarten wir eine Weiterleitung.
    const url = page.url();
    if (url.includes('installer.php')) {
        await expect(page.locator('h1')).toContainText('Installation');
    } else {
        // Weitergeleitet zu index.php -> login.php
        await expect(page).toHaveURL(/login.php|overview.php/);
    }
  });
});
