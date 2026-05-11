import { test, expect } from '@playwright/test';

test.describe('Einkaufsliste Word Wrap', () => {
    const testUserHash = 'test-user-hash-123456';

    test('sollte lange Wörter umbrechen', async ({ page }) => {
        // Login
        await page.goto(`/login.php?hash=${testUserHash}`);
        await page.goto('/start.php');
        
        // Falls keine Liste vorhanden ist, eine erstellen
        const noListMsg = page.locator('text=Noch kein Einkaufzettel vorhanden');
        if (await noListMsg.isVisible()) {
            await page.click('button[aria-label="Neuen Einkaufzettel erstellen"]');
            await page.fill('#list-name', 'Test Liste');
            await page.click('button:has-text("Erstellen")');
        }

        // Artikel mit sehr langem Wort hinzufügen
        const longWord = 'Donaudampfschifffahrtselektrizitätenhauptbetriebswerkbauunterbeamtengesellschaft';
        await page.fill('input[placeholder="Eintrag hinzufügen..."]', longWord);
        await page.click('button[aria-label="Hinzufügen"]');

        // Prüfen ob der Artikel da ist
        const item = page.locator('.shopping-item', { hasText: longWord });
        const itemTitle = item.locator('.card-title');
        await expect(itemTitle).toBeVisible();

        // Prüfen ob das Element die Breite seines Containers nicht überschreitet
        // Wir setzen die Viewport-Breite explizit auf mobile (z.B. 375px)
        await page.setViewportSize({ width: 375, height: 667 });

        const boundingBox = await itemTitle.boundingBox();
        const container = item.locator('.shopping-item-main');
        const containerBox = await container.boundingBox();

        if (boundingBox && containerBox) {
            console.log(`Item width: ${boundingBox.width}, Container width: ${containerBox.width}`);
            // Das Item sollte nicht signifikant breiter sein als der Container
            expect(boundingBox.width).toBeLessThanOrEqual(containerBox.width);
        }

        // Zusätzlich können wir den CSS-Wert prüfen
        const styles = await itemTitle.evaluate((el) => {
            const s = window.getComputedStyle(el);
            return {
                overflowWrap: s.overflowWrap,
                wordBreak: s.wordBreak
            };
        });
        console.log(`Current overflow-wrap: ${styles.overflowWrap}`);
        expect(styles.overflowWrap).toBe('break-word');
    });
});
