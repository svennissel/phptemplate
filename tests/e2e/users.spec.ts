import { test, expect } from '@playwright/test';

test.describe('Benutzerseiten', () => {
  const testUserHash = 'test-user-hash-123456'; 
  const adminUserHash = 'admin-user-hash-123456';

  test('sollte sich erfolgreich per Hash einloggen', async ({ page }) => {
    // Zum Login navigieren
    await page.goto(`/login.php?hash=${testUserHash}`);
    
    // Wir sollten zur Übersichtsseite weitergeleitet werden
    await expect(page).toHaveURL(/users.php/);
    
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
    
    // URL sollte profil.php mit ID enthalten
    await expect(page).toHaveURL(/profil.php\?id=\d+/);
    
    // Details prüfen
    await expect(page.locator('.profile-name')).toContainText('Test User');
  });

  test('sollte Admin-Badge anzeigen', async ({ page }) => {
    // Login als Admin
    await page.goto(`/login.php?hash=${adminUserHash}`);
    await expect(page).toHaveURL(/users.php/);

    // Klick auf den Admin-User in der Liste
    await page.click('text=Admin User');

    // Admin-Badge sollte vorhanden sein
    await expect(page.locator('.badge')).toContainText('Admin');
  });

  test('Browser zurück Button sollte den Bearbeiten Dialog schließen', async ({ page }) => {
    // Login als Admin
    await page.goto(`/login.php?hash=${adminUserHash}`);
    await expect(page).toHaveURL(/users.php/);

    // Dialog öffnen
    await page.click('text=Admin User');
    
    // Dialog sollte sichtbar sein
    const dialog = page.locator('#editDialog');
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
    await expect(page).toHaveURL(/users.php/);

    // Dialog öffnen
    await page.click('text=Admin User');
    
    // Dialog sollte sichtbar sein
    const dialog = page.locator('#editDialog');
    await expect(dialog).toBeVisible();

    // Login URL Feld prüfen
    const loginUrlInput = page.locator('#edit-login-url');
    await expect(loginUrlInput).toBeVisible();
    
    const urlValue = await loginUrlInput.inputValue();
    
    // Die URL sollte das Schema, den Host und den Hash enthalten
    expect(urlValue).toMatch(/^http:\/\/.*\/login\.php\?hash=admin-user-hash-123456$/);
  });
});
