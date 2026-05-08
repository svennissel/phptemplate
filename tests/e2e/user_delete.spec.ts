import { test, expect } from '@playwright/test';

test.describe('Benutzer löschen', () => {
  const adminUserHash = 'admin-user-hash-123456';
  const testUserHash = 'test-user-hash-123456';

  test('Admin sollte einen anderen Benutzer löschen können', async ({ page }) => {
    // Login als Admin
    await page.goto(`/login.php?hash=${adminUserHash}`);
    await expect(page).toHaveURL(/users.php/);

    // Sicherstellen, dass der Test-Benutzer da ist
    await expect(page.locator('.card-title', { hasText: 'Test User' })).toBeVisible();

    // Dialog für Test User öffnen
    await page.click('.card:has-text("Test User")');
    
    const dialog = page.locator('#userModal');
    await expect(dialog).toBeVisible();

    // Löschen-Button sollte sichtbar sein
    const deleteBtn = page.locator('button:has-text("Benutzer löschen")');
    await expect(deleteBtn).toBeVisible();

    // Löschen klicken (Confirm Dialog abfangen)
    page.once('dialog', dialog => dialog.accept());
    await deleteBtn.click();

    // Nach dem Löschen sollten wir wieder auf users.php sein
    await expect(page).toHaveURL(/users.php/);

    // Test User sollte weg sein
    await expect(page.locator('.card-title', { hasText: 'Test User' })).not.toBeVisible();
  });

  test('Admin sollte sich nicht selbst löschen können', async ({ page }) => {
    // Login als Admin
    await page.goto(`/login.php?hash=${adminUserHash}`);
    await expect(page).toHaveURL(/users.php/);

    // Dialog für Admin User öffnen (sich selbst)
    await page.click('.card:has-text("Admin User")');
    
    const dialog = page.locator('#userModal');
    await expect(dialog).toBeVisible();

    // Löschen-Button sollte NICHT sichtbar sein (JS-Ebene)
    const deleteBtn = page.locator('button:has-text("Benutzer löschen")');
    await expect(deleteBtn).not.toBeVisible();
  });
});
