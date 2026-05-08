import { test, expect } from '@playwright/test';

test.describe('Benutzer hinzufügen', () => {
  const adminUserHash = 'admin-user-hash-123456';

  test('sollte einen neuen Benutzer erfolgreich anlegen', async ({ page }) => {
    // Login als Admin
    await page.goto(`/login.php?hash=${adminUserHash}`);
    await expect(page).toHaveURL(/users.php/);

    // Klick auf "Benutzer hinzufügen"
    await page.click('text=Benutzer hinzufügen');

    // Dialog prüfen
    const dialog = page.locator('#userModal');
    await expect(dialog).toBeVisible();
    await expect(page.locator('#modal-title')).toHaveText('Benutzer hinzufügen');
    
    // Login-Link sollte beim Hinzufügen ausgeblendet sein
    await expect(page.locator('#login-url-container')).not.toBeVisible();

    // Formular ausfüllen
    await page.fill('#user-name', 'Neu Angelegter User');
    await page.check('#user-is_admin');

    // Speichern
    await page.click('button:has-text("Speichern")');

    // Wir sollten wieder auf users.php sein
    await expect(page).toHaveURL(/users.php/);

    // Der neue Benutzer sollte in der Liste sein
    await expect(page.locator('.card-title', { hasText: 'Neu Angelegter User' })).toBeVisible();
    
    // Admin Badge sollte für den neuen User da sein (da wir ihn als Admin angelegt haben)
    // Wir suchen die Karte, die "Neu Angelegter User" enthält, und prüfen auf das Badge
    const userCard = page.locator('.card', { hasText: 'Neu Angelegter User' });
    await expect(userCard.locator('.badge')).toContainText('Admin');
  });
});
