/**
 * Offline-Datenbank-Management für die Einkaufsliste.
 * Verwendet IndexedDB zur lokalen Speicherung und Synchronisation.
 */

const DB_NAME = 'einkauf_db';
const DB_VERSION = 1;
const STORE_ITEMS = 'items';
const STORE_SYNC = 'sync_queue';

let db;

/**
 * Initialisiert die IndexedDB.
 */
async function initDB() {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open(DB_NAME, DB_VERSION);

        request.onupgradeneeded = (event) => {
            const db = event.target.result;
            if (!db.objectStoreNames.contains(STORE_ITEMS)) {
                db.createObjectStore(STORE_ITEMS, { keyPath: 'id' });
            }
            if (!db.objectStoreNames.contains(STORE_SYNC)) {
                db.createObjectStore(STORE_SYNC, { keyPath: 'id', autoIncrement: true });
            }
        };

        request.onsuccess = (event) => {
            db = event.target.result;
            resolve(db);
        };

        request.onerror = (event) => {
            console.error('IndexedDB Fehler:', event.target.error);
            reject(event.target.error);
        };
    });
}

/**
 * Speichert ein Item lokal.
 */
async function saveItemLocally(item) {
    if (!db) await initDB();
    return new Promise((resolve, reject) => {
        const transaction = db.transaction([STORE_ITEMS], 'readwrite');
        const store = transaction.objectStore(STORE_ITEMS);
        const request = store.put(item);
        request.onsuccess = () => resolve();
        request.onerror = () => reject(request.error);
    });
}

/**
 * Löscht ein Item lokal.
 */
async function deleteItemLocally(id) {
    if (!db) await initDB();
    return new Promise((resolve, reject) => {
        const transaction = db.transaction([STORE_ITEMS], 'readwrite');
        const store = transaction.objectStore(STORE_ITEMS);
        const request = store.delete(id);
        request.onsuccess = () => resolve();
        request.onerror = () => reject(request.error);
    });
}

/**
 * Fügt eine Änderung zur Sync-Queue hinzu.
 */
async function addToSyncQueue(action, data) {
    if (!db) await initDB();
    return new Promise((resolve, reject) => {
        const transaction = db.transaction([STORE_SYNC], 'readwrite');
        const store = transaction.objectStore(STORE_SYNC);
        const request = store.add({
            action,
            data,
            timestamp: Date.now()
        });
        request.onsuccess = () => {
        updateSyncIcon(true);
            resolve();
            triggerSync();
        };
        request.onerror = () => reject(request.error);
    });
}

/**
 * Zeigt oder versteckt das Sync-Icon.
 */
function updateSyncIcon(show) {
    const icon = document.getElementById('sync-indicator');
    if (icon) {
        icon.style.display = show ? 'flex' : 'none';
    }
}

/**
 * Startet den Synchronisationsprozess mit dem Server.
 */
let isSyncing = false;
async function triggerSync() {
    if (isSyncing) return;
    if (!db) await initDB();

    const transaction = db.transaction([STORE_SYNC], 'readonly');
    const store = transaction.objectStore(STORE_SYNC);
    const request = store.getAll();

    request.onsuccess = async () => {
        const queue = request.result;
        if (queue.length === 0) {
            updateSyncIcon(false);
            return;
        }

        isSyncing = true;
        try {
            const response = await fetch('api/sync.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(queue)
            });

            if (response.ok) {
                const resultData = await response.json();
                // Erfolgreich gesynct, Queue leeren
                const clearTransaction = db.transaction([STORE_SYNC], 'readwrite');
                const clearStore = clearTransaction.objectStore(STORE_SYNC);
                
                // Wir löschen nur die Items, die wir erfolgreich gesendet haben
                // (wichtig, falls in der Zwischenzeit neue hinzugekommen sind)
                queue.forEach(item => clearStore.delete(item.id));
                
                clearTransaction.oncomplete = async () => {
                    isSyncing = false;
                    // Event auslösen, dass Sync abgeschlossen wurde (für UI Updates)
                    window.dispatchEvent(new CustomEvent('sync-completed', { detail: { queue, results: resultData.results || [] } }));
                    // Prüfen ob neue Items dazu kamen
                    const checkTransaction = db.transaction([STORE_SYNC], 'readonly');
                    const checkStore = checkTransaction.objectStore(STORE_SYNC);
                    const checkRequest = checkStore.count();
                    checkRequest.onsuccess = () => {
                        if (checkRequest.result > 0) {
                            triggerSync();
                        } else {
                            updateSyncIcon(false);
                        }
                    };
                };
            } else {
                isSyncing = false;
            }
        } catch (error) {
            console.error('Sync fehlgeschlagen:', error);
            isSyncing = false;
        }
    };
}

// Beim Start initialisieren
initDB().then(() => {
    triggerSync();
});

// Periodisch syncen falls online
setInterval(triggerSync, 30000);

// Wenn wieder online, sofort syncen
window.addEventListener('online', triggerSync);
