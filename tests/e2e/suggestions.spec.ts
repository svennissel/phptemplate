import { test, expect } from '@playwright/test';

test.describe('Shopping List Suggestions', () => {
    const testUserHash = 'test-user-hash-123456';

    test('should show suggestions when typing and count usages', async ({ page }) => {
        // Login
        await page.goto(`/login.php?hash=${testUserHash}`);
        await page.goto('/start.php');

        // Create a list
        await page.click('button[aria-label="Neuen Einkaufzettel erstellen"]');
        await page.fill('#list-name', 'Test Liste Suggestions');
        await page.click('button:has-text("Erstellen")');

        // Add an item (Brot)
        const input = page.locator('#item-input');
        await input.fill('Brot');
        await page.keyboard.press('Enter');
        
        // Wait for sync or reload
        await expect(page.locator('.shopping-item .card-title')).toHaveText('Brot');

        // Delete the item to make it a suggestion (it shouldn't be in current list)
        await page.click('button[aria-label="Löschen"]');
        // Wait for undo timer (3s) + buffer
        await page.waitForTimeout(4000);
        await expect(page.locator('.shopping-item')).toHaveCount(0);

        // Type "Br" and check for suggestions
        await input.fill('Br');
        
        // Datalist suggestions are hard to test directly in Playwright across browsers, 
        // but we can check if the API returns them.
        // Actually, we can check if the datalist has options.
        await page.waitForTimeout(1000); // Wait for debounce and fetch
        
        const options = page.locator('#suggestions option');
        const count = await options.count();
        expect(count).toBeGreaterThan(0);
        
        const firstOptionValue = await options.first().getAttribute('value');
        expect(firstOptionValue).toBe('Brot');

        // Add it again and check if usage count increases (not directly visible but we verify no crash)
        await input.fill('Brot');
        await page.keyboard.press('Enter');
        await expect(page.locator('.shopping-item .card-title')).toHaveText('Brot');
    });
});
