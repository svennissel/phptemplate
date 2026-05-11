import { test, expect } from '@playwright/test';

const testUserHash = 'test-user-hash-123456';

test('localStorage login should work', async ({ page }) => {
    // 1. Initialer Login, um den Hash im localStorage zu speichern
    await page.goto(`/login.php?hash=${testUserHash}`);
    await expect(page).toHaveURL(/start.php/);

    // Prüfen, ob der Hash im localStorage gelandet ist
    const storedHash = await page.evaluate(() => localStorage.getItem('einkauf_hash'));
    expect(storedHash).toBe(testUserHash);

    // 2. Cookies löschen, um "ausgeloggt" zu sein (Session/Cookie weg)
    await page.context().clearCookies();

    // 3. Auf login.php gehen ohne Parameter
    await page.goto('/login.php');

    // 4. Das JavaScript sollte uns nun automatisch wieder einloggen
    await expect(page).toHaveURL(/start.php/);
});

test('logout should clear localStorage', async ({ page }) => {
    // 1. Login
    await page.goto(`/login.php?hash=${testUserHash}`);
    await expect(page).toHaveURL(/start.php/);

    // 2. Logout aufrufen
    await page.goto('/logout.php');
    await expect(page).toHaveURL(/login.php/);

    // 3. Prüfen, ob localStorage leer ist
    const storedHash = await page.evaluate(() => localStorage.getItem('einkauf_hash'));
    expect(storedHash).toBeNull();
});
