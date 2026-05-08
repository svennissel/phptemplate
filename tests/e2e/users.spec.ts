import { test, expect } from '@playwright/test';

test.describe('Benutzerseiten', () => {
  const testUserHash = 'test-user-hash-123456'; 
  const adminUserHash = 'admin-user-hash-123456';

  test('sollte sich erfolgreich per Hash einloggen', async ({ page }) => {
    // Zum Login navigieren
    await page.goto(`/login.php?hash=${testUserHash}`);
    
    // Wir sollten zur Übersichtsseite weitergeleitet werden
    await expect(page).toHaveURL(/start.php/);
    
    // In base.html.twig steht der Seitentitel in .topbar-title
    // Wir prüfen hier auf start.php, ob die Navigation da ist oder ein Element von start.php
    await expect(page.locator('.nav-item.active')).toContainText('Start');
    
    // Der Test-Benutzer sollte in der Liste sein
    // Wir navigieren zu users.php als Admin, um die Liste zu sehen, oder wir prüfen was anderes auf start.php
    await page.goto('/users.php');
    await expect(page).toHaveURL(/start.php/); // Sollte redirecten, da testUser kein Admin ist
    
    // Admin Login um User Liste zu prüfen
    await page.goto(`/login.php?hash=${adminUserHash}`);
    await page.goto('/users.php');
    await expect(page.locator('.card-title', { hasText: 'Test User' })).toBeVisible();
  });

  test('sollte das Benutzerprofil anzeigen', async ({ page }) => {
    // Login
    await page.goto(`/login.php?hash=${testUserHash}`);
    await expect(page).toHaveURL(/start.php/);

    // Da wir nicht mehr auf users.php landen, müssen wir profil.php direkt aufrufen oder einen anderen Weg finden.
    // Aber der Test wollte wohl den Klick in der Liste prüfen.
    // Da normale User die Liste nicht mehr sehen, passen wir den Test an:
    await page.goto('/profil.php'); 
    
    // URL sollte profil.php sein
    await expect(page).toHaveURL(/profil.php/);
    
    // Details prüfen
    await expect(page.locator('.profile-name')).toContainText('Test User');
  });

  test('sollte Admin-Badge anzeigen', async ({ page }) => {
    // Login als Admin
    await page.goto(`/login.php?hash=${adminUserHash}`);
    await expect(page).toHaveURL(/start.php/);

    await page.goto('/users.php');
    await expect(page).toHaveURL(/users.php/);

    // Klick auf den Admin-User in der Liste
    await page.click('text=Admin User');

    // Admin-Badge sollte vorhanden sein
    const adminCard = page.locator('.card', { hasText: 'Admin User' });
    await expect(adminCard.locator('.badge')).toContainText('Admin');
  });

  test('Browser zurück Button sollte den Bearbeiten Dialog schließen', async ({ page }) => {
    // Login als Admin
    await page.goto(`/login.php?hash=${adminUserHash}`);
    await expect(page).toHaveURL(/start.php/);

    await page.goto('/users.php');

    // Dialog öffnen
    await page.click('text=Admin User');
    
    // Dialog sollte sichtbar sein
    const dialog = page.locator('#userModal');
    await expect(dialog).toBeVisible();

    // Browser zurück Button betätigen
    await page.goBack();

    // Dialog sollte geschlossen sein
    await expect(dialog).not.toBeVisible();
    
    // URL sollte immer noch users.php sein (keine tatsächliche Navigation weg von der Seite)
    await expect(page).toHaveURL(/users.php/);
  });

  test('sollte die komplette Login URL im Bearbeiten-Dialog anzeigen', async ({ page }) => {
    // Login als Admin
    await page.goto(`/login.php?hash=${adminUserHash}`);
    await expect(page).toHaveURL(/start.php/);

    await page.goto('/users.php');

    // Dialog öffnen
    await page.click('text=Admin User');
    
    // Dialog sollte sichtbar sein
    const dialog = page.locator('#userModal');
    await expect(dialog).toBeVisible();

    // Login URL Feld prüfen
    const loginUrlInput = page.locator('#user-login-url');
    await expect(loginUrlInput).toBeVisible();
    
    const urlValue = await loginUrlInput.inputValue();
    
    // Die URL sollte das Schema, den Host und den Hash enthalten
    expect(urlValue).toMatch(/^http:\/\/.*\/login\.php\?hash=admin-user-hash-123456$/);
  });
});
