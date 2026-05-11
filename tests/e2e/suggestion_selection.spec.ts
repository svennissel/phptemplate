import { test, expect } from '@playwright/test';

test.describe('Shopping List Suggestions Selection', () => {
    const testUserHash = 'test-user-hash-123456';

    test('should add item immediately when selecting from suggestions', async ({ page }) => {
        // Login
        await page.goto(`/login.php?hash=${testUserHash}`);
        await page.goto('/start.php');

        // Create a list
        await page.click('button[aria-label="Neuen Einkaufzettel erstellen"]');
        await page.fill('#list-name', 'Test Selection');
        await page.click('button:has-text("Erstellen")');

        // Add an item (Milch)
        const input = page.locator('#item-input');
        await input.fill('Milch');
        await page.keyboard.press('Enter');
        
        // Wait for sync
        await expect(page.locator('.shopping-item .card-title')).toHaveText('Milch');

        // Delete the item to make it a suggestion
        await page.click('button[aria-label="Löschen"]');
        // Wait for undo timer (3s) + buffer
        await page.waitForTimeout(4000);
        await expect(page.locator('.shopping-item')).toHaveCount(0);

        // Type "Mi" and wait for suggestions
        await input.fill('Mi');
        
        // Wait for datalist to be populated
        await page.waitForFunction(() => {
            const datalist = document.getElementById('suggestions');
            return datalist && datalist.options.length > 0;
        });

        // Trigger input with the exact value 'Milch' to simulate selection
        // In Playwright, selecting from datalist is tricky. 
        // We can simulate it by setting the value and firing the 'input' event.
        await input.evaluate((el) => {
            el.value = 'Milch';
            el.dispatchEvent(new Event('input', { bubbles: true }));
        });

        // Check if the item was added immediately (it should appear in the list)
        await expect(page.locator('.shopping-item .card-title')).toHaveText('Milch');
        
        // Wait a bit to see if duplicates appear
        await page.waitForTimeout(2000);
        await expect(page.locator('.shopping-item')).toHaveCount(1);
        
        // Verify input is reset
        await expect(input).toHaveValue('');
    });
});
