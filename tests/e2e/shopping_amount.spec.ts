import { test, expect } from '@playwright/test';

test.describe('Einkaufsliste Mengensteuerung', () => {
    const testUserHash = 'test-user-hash-123456';

    test.beforeEach(async ({ page }) => {
        // Login
        await page.goto(`/login.php?hash=${testUserHash}`);
    });

    test('sollte einen Artikel hinzufügen und die Menge ändern können', async ({ page }) => {
        // Falls keine Liste vorhanden ist, eine erstellen (sollte durch setup_test_db aber da sein oder wir erstellen eine)
        await page.goto('/start.php');
        
        // Prüfen ob wir auf der Startseite sind. Wenn keine Liste da ist, erstellen wir eine.
        const noListMsg = page.locator('text=Noch kein Einkaufzettel vorhanden');
        if (await noListMsg.isVisible()) {
            await page.click('button[aria-label="Neuen Einkaufzettel erstellen"]');
            await page.fill('#list-name', 'Test Liste');
            await page.click('button:has-text("Erstellen")');
        }

        // Artikel hinzufügen
        await page.fill('input[placeholder="Eintrag hinzufügen..."]', 'Bananen');
        await page.click('button[aria-label="Hinzufügen"]');

        // Prüfen ob Artikel da ist und Standardmenge 1 hat
        const item = page.locator('.shopping-item', { hasText: 'Bananen' });
        await expect(item).toBeVisible();
        await expect(item.locator('.amount-value')).toHaveText('1');

        // Menge erhöhen
        await item.locator('button[aria-label="Menge erhöhen"]').click();
        await expect(item.locator('.amount-value')).toHaveText('2');

        // Menge verringern
        await item.locator('button[aria-label="Menge verringern"]').click();
        await expect(item.locator('.amount-value')).toHaveText('1');

        // Menge kann nicht unter 1 fallen
        await item.locator('button[aria-label="Menge verringern"]').click();
        await expect(item.locator('.amount-value')).toHaveText('1');
    });
});
