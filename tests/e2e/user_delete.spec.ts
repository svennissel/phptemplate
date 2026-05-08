import { test, expect } from '@playwright/test';

test.describe('Benutzer löschen', () => {
  const adminUserHash = 'admin-user-hash-123456';
  const testUserHash = 'test-user-hash-123456';

  test('Admin sollte einen anderen Benutzer löschen können', async ({ page }) => {
    // Login als Admin
    await page.goto(`/login.php?hash=${adminUserHash}`);
    await expect(page).toHaveURL(/start.php/);
    
    await page.goto('/users.php');
    await expect(page).toHaveURL(/users.php/);

    const deleteUserName = 'Test User for delete';

    // Neuen Benutzer erstellen
    await page.click('button:has-text("Benutzer hinzufügen")');
    await page.fill('#user-name', deleteUserName);
    await page.click('button:has-text("Speichern")');

    // Sicherstellen, dass der neue Benutzer in der Liste ist
    const userCard = page.locator('.card', { hasText: deleteUserName });
    await expect(userCard).toBeVisible();

    // Dialog für den neuen Benutzer öffnen
    await userCard.click();
    
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

    // Der Benutzer sollte weg sein
    await expect(page.locator('.card-title', { hasText: deleteUserName })).not.toBeVisible();
  });

  test('Admin sollte sich nicht selbst löschen können', async ({ page }) => {
    // Login als Admin
    await page.goto(`/login.php?hash=${adminUserHash}`);
    await expect(page).toHaveURL(/start.php/);

    await page.goto('/users.php');

    // Dialog für Admin User öffnen (sich selbst)
    await page.click('.card:has-text("Admin User")');
    
    const dialog = page.locator('#userModal');
    await expect(dialog).toBeVisible();

    // Löschen-Button sollte NICHT sichtbar sein (JS-Ebene)
    const deleteBtn = page.locator('button:has-text("Benutzer löschen")');
    await expect(deleteBtn).not.toBeVisible();
  });
});
