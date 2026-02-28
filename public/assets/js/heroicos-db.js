/**
 * HeroicosDB - IndexedDB Wrapper for Offline Support
 * Academia Heroicos PWA
 *
 * Provides promise-based access to IndexedDB with 4 object stores:
 * - sync_queue: Pending offline mutations (attendance, payments)
 * - cached_data: JSON snapshots for offline reading
 * - file_store: Binary files (comprobante images)
 * - user_session: Current user identity cache
 */
(function () {
    'use strict';

    const DB_NAME = 'heroicos_offline';
    const DB_VERSION = 1;

    let db = null;
    let initPromise = null;

    function generateId() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
            var r = Math.random() * 16 | 0;
            return (c === 'x' ? r : (r & 0x3 | 0x8)).toString(16);
        });
    }

    function openDB() {
        return new Promise(function (resolve, reject) {
            var request = indexedDB.open(DB_NAME, DB_VERSION);

            request.onupgradeneeded = function (event) {
                var database = event.target.result;

                // sync_queue
                if (!database.objectStoreNames.contains('sync_queue')) {
                    var syncStore = database.createObjectStore('sync_queue', { keyPath: 'id' });
                    syncStore.createIndex('type', 'type', { unique: false });
                    syncStore.createIndex('status', 'status', { unique: false });
                    syncStore.createIndex('createdAt', 'createdAt', { unique: false });
                    syncStore.createIndex('type_status', ['type', 'status'], { unique: false });
                }

                // cached_data
                if (!database.objectStoreNames.contains('cached_data')) {
                    var cacheStore = database.createObjectStore('cached_data', { keyPath: 'key' });
                    cacheStore.createIndex('type', 'type', { unique: false });
                    cacheStore.createIndex('updatedAt', 'updatedAt', { unique: false });
                    cacheStore.createIndex('roleScope', 'roleScope', { unique: false });
                }

                // file_store
                if (!database.objectStoreNames.contains('file_store')) {
                    database.createObjectStore('file_store', { keyPath: 'key' });
                }

                // user_session
                if (!database.objectStoreNames.contains('user_session')) {
                    database.createObjectStore('user_session', { keyPath: 'key' });
                }
            };

            request.onsuccess = function (event) {
                resolve(event.target.result);
            };

            request.onerror = function (event) {
                reject(event.target.error);
            };
        });
    }

    function getStore(storeName, mode) {
        if (!db) throw new Error('Database not initialized');
        var tx = db.transaction(storeName, mode || 'readonly');
        return tx.objectStore(storeName);
    }

    function promisifyRequest(request) {
        return new Promise(function (resolve, reject) {
            request.onsuccess = function () { resolve(request.result); };
            request.onerror = function () { reject(request.error); };
        });
    }

    // =========================================================================
    // PUBLIC API
    // =========================================================================

    window.HeroicosDB = {
        available: false,

        /**
         * Initialize the database. Safe to call multiple times.
         */
        init: function () {
            if (initPromise) return initPromise;

            initPromise = openDB().then(function (database) {
                db = database;
                window.HeroicosDB.available = true;
                console.log('[HeroicosDB] Initialized');
                return true;
            }).catch(function (err) {
                console.warn('[HeroicosDB] Not available:', err.message);
                window.HeroicosDB.available = false;
                initPromise = null;
                return false;
            });

            return initPromise;
        },

        // =====================================================================
        // SYNC QUEUE
        // =====================================================================

        /**
         * Add an item to the sync queue
         * @param {string} type - 'attendance' | 'payment'
         * @param {object} payload - Type-specific data
         * @param {object} meta - User context { userId, rolNombre, userName }
         * @returns {Promise<string>} The generated ID
         */
        addToQueue: function (type, payload, meta) {
            var record = {
                id: generateId(),
                type: type,
                status: 'pending',
                createdAt: Date.now(),
                attempts: 0,
                lastAttempt: null,
                errorMessage: null,
                serverResponse: null,
                conflictData: null,
                payload: payload,
                meta: meta || {}
            };
            var store = getStore('sync_queue', 'readwrite');
            return promisifyRequest(store.put(record)).then(function () {
                return record.id;
            });
        },

        /**
         * Get queue items with optional filters
         * @param {object} filter - { statusIn: [], type: '', maxAttempts: 3 }
         * @returns {Promise<Array>}
         */
        getQueueItems: function (filter) {
            var store = getStore('sync_queue', 'readonly');
            return promisifyRequest(store.getAll()).then(function (items) {
                if (!filter) return items;

                return items.filter(function (item) {
                    if (filter.statusIn && filter.statusIn.indexOf(item.status) === -1) return false;
                    if (filter.type && item.type !== filter.type) return false;
                    if (filter.maxAttempts && item.attempts >= filter.maxAttempts) return false;
                    return true;
                }).sort(function (a, b) {
                    return a.createdAt - b.createdAt;
                });
            });
        },

        /**
         * Update fields on a queue item
         */
        updateQueueItem: function (id, changes) {
            var store = getStore('sync_queue', 'readwrite');
            var request = store.get(id);
            return promisifyRequest(request).then(function (record) {
                if (!record) return;
                Object.keys(changes).forEach(function (key) {
                    record[key] = changes[key];
                });
                return promisifyRequest(store.put(record));
            });
        },

        /**
         * Delete a queue item
         */
        deleteQueueItem: function (id) {
            var store = getStore('sync_queue', 'readwrite');
            return promisifyRequest(store.delete(id));
        },

        /**
         * Count items with status 'pending' or 'failed' (attempts < 3)
         */
        getPendingCount: function () {
            var store = getStore('sync_queue', 'readonly');
            return promisifyRequest(store.getAll()).then(function (items) {
                return items.filter(function (item) {
                    return item.status === 'pending' ||
                        (item.status === 'failed' && item.attempts < 3);
                }).length;
            });
        },

        /**
         * Check if a pending queue item exists matching given criteria
         */
        findInQueue: function (type, matchFn) {
            var store = getStore('sync_queue', 'readonly');
            return promisifyRequest(store.getAll()).then(function (items) {
                return items.find(function (item) {
                    return item.type === type &&
                        (item.status === 'pending' || item.status === 'failed') &&
                        matchFn(item.payload);
                }) || null;
            });
        },

        // =====================================================================
        // CACHED DATA
        // =====================================================================

        /**
         * Store or update cached data
         */
        cacheData: function (key, type, roleScope, data, ttlHours) {
            var now = Date.now();
            var record = {
                key: key,
                type: type,
                roleScope: roleScope,
                data: data,
                updatedAt: now,
                expiresAt: now + ((ttlHours || 24) * 60 * 60 * 1000)
            };
            var store = getStore('cached_data', 'readwrite');
            return promisifyRequest(store.put(record));
        },

        /**
         * Get cached data by key. Returns null if expired or missing.
         */
        getCachedData: function (key) {
            var store = getStore('cached_data', 'readonly');
            return promisifyRequest(store.get(key)).then(function (record) {
                if (!record) return null;
                return record;
            });
        },

        /**
         * Clear expired cache entries
         */
        clearExpiredCache: function () {
            var store = getStore('cached_data', 'readwrite');
            var now = Date.now();
            return promisifyRequest(store.getAll()).then(function (items) {
                var deletes = items
                    .filter(function (item) { return item.expiresAt < now; })
                    .map(function (item) {
                        return promisifyRequest(
                            getStore('cached_data', 'readwrite').delete(item.key)
                        );
                    });
                return Promise.all(deletes);
            });
        },

        // =====================================================================
        // FILE STORE
        // =====================================================================

        /**
         * Store a file blob
         */
        storeFile: function (key, blob, fileName, mimeType) {
            var record = {
                key: key,
                blob: blob,
                fileName: fileName,
                mimeType: mimeType,
                size: blob.size,
                createdAt: Date.now()
            };
            var store = getStore('file_store', 'readwrite');
            return promisifyRequest(store.put(record));
        },

        /**
         * Get a file blob
         */
        getFile: function (key) {
            var store = getStore('file_store', 'readonly');
            return promisifyRequest(store.get(key));
        },

        /**
         * Delete a file blob
         */
        deleteFile: function (key) {
            var store = getStore('file_store', 'readwrite');
            return promisifyRequest(store.delete(key));
        },

        // =====================================================================
        // USER SESSION
        // =====================================================================

        /**
         * Save current user session data for offline identity
         */
        saveUserSession: function (sessionData) {
            var record = Object.assign({ key: 'current', cachedAt: Date.now() }, sessionData);
            var store = getStore('user_session', 'readwrite');
            return promisifyRequest(store.put(record));
        },

        /**
         * Get cached user session
         */
        getUserSession: function () {
            var store = getStore('user_session', 'readonly');
            return promisifyRequest(store.get('current'));
        },

        /**
         * Clear user session cache
         */
        clearUserSession: function () {
            var store = getStore('user_session', 'readwrite');
            return promisifyRequest(store.delete('current'));
        },

        // =====================================================================
        // CLEANUP
        // =====================================================================

        /**
         * Delete synced queue items older than 24h and expired cache
         */
        cleanup: function () {
            var cutoff = Date.now() - (24 * 60 * 60 * 1000);
            var self = this;

            return this.getQueueItems().then(function (items) {
                var deletes = items
                    .filter(function (item) {
                        return item.status === 'synced' && item.createdAt < cutoff;
                    })
                    .map(function (item) {
                        return self.deleteQueueItem(item.id);
                    });
                return Promise.all(deletes);
            }).then(function () {
                return self.clearExpiredCache();
            });
        }
    };

})();
