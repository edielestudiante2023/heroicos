/**
 * HeroicosSync - Sync Queue Engine for Offline Support
 * Academia Heroicos PWA
 *
 * Manages offline data saving and synchronization with the server.
 * Depends on: heroicos-db.js (window.HeroicosDB)
 */
(function () {
    'use strict';

    var SYNC_TAG = 'heroicos-sync';
    var MAX_ATTEMPTS = 3;
    var BACKOFF_BASE_MS = 5000; // 5s, 10s, 20s
    var _isSyncing = false;
    var _syncChannel = null;

    // Multi-tab coordination via BroadcastChannel
    try {
        _syncChannel = new BroadcastChannel('heroicos-sync-coord');
        _syncChannel.onmessage = function (event) {
            if (event.data === 'SYNC_START') _isSyncing = true;
            if (event.data === 'SYNC_END') _isSyncing = false;
        };
    } catch (e) {
        // BroadcastChannel not supported
    }

    /**
     * Get a stable device identifier
     */
    function getDeviceId() {
        var id = localStorage.getItem('heroicos-device-id');
        if (!id) {
            id = 'dev_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            localStorage.setItem('heroicos-device-id', id);
        }
        return id;
    }

    /**
     * Get user meta from cached session
     */
    function getUserMeta() {
        return HeroicosDB.getUserSession().then(function (session) {
            return {
                userId: session ? session.user_id : null,
                rolNombre: session ? session.rol_nombre : null,
                userName: session ? (session.nombre + ' ' + session.apellido) : 'Desconocido',
                deviceId: getDeviceId()
            };
        });
    }

    /**
     * Register Background Sync (with fallback)
     */
    function registerSync() {
        if ('serviceWorker' in navigator && 'SyncManager' in window) {
            navigator.serviceWorker.ready.then(function (reg) {
                return reg.sync.register(SYNC_TAG);
            }).catch(function () {
                // Background Sync not supported
            });
        }
    }

    /**
     * Compress an image file if > 2MB
     */
    function compressImage(file, maxWidth, quality) {
        maxWidth = maxWidth || 1920;
        quality = quality || 0.8;

        return new Promise(function (resolve) {
            if (file.type === 'application/pdf' || file.size <= 2 * 1024 * 1024) {
                resolve(file);
                return;
            }

            var img = new Image();
            img.onload = function () {
                var canvas = document.createElement('canvas');
                var ratio = Math.min(maxWidth / img.width, 1);
                canvas.width = img.width * ratio;
                canvas.height = img.height * ratio;
                var ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                canvas.toBlob(function (blob) {
                    resolve(blob || file);
                }, 'image/jpeg', quality);
            };
            img.onerror = function () { resolve(file); };
            img.src = URL.createObjectURL(file);
        });
    }

    // =========================================================================
    // PUBLIC API
    // =========================================================================

    window.HeroicosSync = {

        /**
         * Save attendance data offline
         * @param {object} data - { grupo_id, grupo_nombre, profesor_id, fecha,
         *   hora_inicio, hora_fin, observaciones, estudiante_ids, presentes, estudiante_nombres }
         * @returns {Promise<string>} Queue item ID
         */
        saveAttendanceOffline: function (data) {
            // Check for duplicate (same grupo + fecha)
            return HeroicosDB.findInQueue('attendance', function (payload) {
                return payload.grupo_id === data.grupo_id && payload.fecha === data.fecha;
            }).then(function (existing) {
                if (existing) {
                    // Return existing ID to let caller decide whether to replace
                    return { existingId: existing.id, duplicate: true };
                }
                return null;
            }).then(function (dup) {
                if (dup && dup.duplicate) return dup;

                return getUserMeta().then(function (meta) {
                    return HeroicosDB.addToQueue('attendance', data, meta);
                }).then(function (id) {
                    registerSync();
                    return { id: id, duplicate: false };
                });
            });
        },

        /**
         * Replace an existing offline attendance record
         */
        replaceAttendanceOffline: function (existingId, data) {
            return HeroicosDB.deleteQueueItem(existingId).then(function () {
                return getUserMeta();
            }).then(function (meta) {
                return HeroicosDB.addToQueue('attendance', data, meta);
            }).then(function (id) {
                registerSync();
                return id;
            });
        },

        /**
         * Save payment data offline
         * @param {object} data - Payment form data
         * @param {File|null} comprobanteFile - Optional comprobante image
         * @returns {Promise<string>} Queue item ID
         */
        savePaymentOffline: function (data, comprobanteFile) {
            var fileKey = null;

            var filePromise = Promise.resolve();
            if (comprobanteFile) {
                fileKey = 'file_' + Date.now();
                filePromise = compressImage(comprobanteFile).then(function (blob) {
                    return HeroicosDB.storeFile(fileKey, blob, comprobanteFile.name, comprobanteFile.type || 'image/jpeg');
                });
            }

            return filePromise.then(function () {
                data.comprobante_blob_key = fileKey;
                return getUserMeta();
            }).then(function (meta) {
                return HeroicosDB.addToQueue('payment', data, meta);
            }).then(function (id) {
                registerSync();
                return id;
            });
        },

        /**
         * Process the entire pending queue. Called when coming online.
         * @returns {Promise<{synced: number, failed: number, authExpired: boolean}>}
         */
        processQueue: function () {
            if (!HeroicosDB.available || _isSyncing) {
                return Promise.resolve({ synced: 0, failed: 0, authExpired: false });
            }

            _isSyncing = true;
            if (_syncChannel) _syncChannel.postMessage('SYNC_START');
            var result = { synced: 0, failed: 0, skipped: 0, authExpired: false };

            return HeroicosDB.getQueueItems({
                statusIn: ['pending', 'failed'],
                maxAttempts: MAX_ATTEMPTS
            }).then(function (items) {
                if (items.length === 0) {
                    _isSyncing = false;
                    if (_syncChannel) _syncChannel.postMessage('SYNC_END');
                    return result;
                }

                // Filter out items in backoff window
                var now = Date.now();
                var ready = items.filter(function (item) {
                    if (item.status === 'pending') return true;
                    if (!item.lastAttempt) return true;
                    var delay = BACKOFF_BASE_MS * Math.pow(2, Math.min(item.attempts - 1, 4));
                    var eligible = (now - item.lastAttempt) >= delay;
                    if (!eligible) result.skipped++;
                    return eligible;
                });

                if (ready.length === 0) {
                    _isSyncing = false;
                    if (_syncChannel) _syncChannel.postMessage('SYNC_END');
                    return result;
                }

                // Process sequentially
                var chain = Promise.resolve();
                ready.forEach(function (item) {
                    chain = chain.then(function () {
                        if (result.authExpired) return; // stop on auth failure
                        return syncItem(item, result);
                    });
                });

                return chain.then(function () {
                    _isSyncing = false;
                    if (_syncChannel) _syncChannel.postMessage('SYNC_END');
                    // Cleanup old synced records
                    return HeroicosDB.cleanup();
                }).then(function () {
                    return result;
                });

            }).catch(function (err) {
                console.error('[HeroicosSync] processQueue error:', err);
                _isSyncing = false;
                if (_syncChannel) _syncChannel.postMessage('SYNC_END');
                return result;
            });
        },

        /**
         * Check if there are pending items
         */
        hasPendingItems: function () {
            if (!HeroicosDB.available) return Promise.resolve(false);
            return HeroicosDB.getPendingCount().then(function (count) {
                return count > 0;
            });
        },

        /**
         * Get count of pending sync items
         */
        getPendingCount: function () {
            if (!HeroicosDB.available) return Promise.resolve(0);
            return HeroicosDB.getPendingCount();
        },

        /**
         * Prefetch profesor data for offline use
         */
        prefetchProfesorData: function () {
            return HeroicosDB.getUserSession().then(function (session) {
                if (!session || session.rol_nombre !== 'profesor') return false;

                var scope = 'profesor_' + session.profesor_id;

                return fetch('/api/offline/profesor/grupos').then(function (res) {
                    if (!res.ok) throw new Error('HTTP ' + res.status);
                    return res.json();
                }).then(function (data) {
                    // Cache the grupos list
                    return HeroicosDB.cacheData(
                        'profesor_grupos_' + session.profesor_id,
                        'grupos', scope, data.grupos, 24
                    ).then(function () {
                        // For each grupo, prefetch students
                        var promises = data.grupos.map(function (grupo) {
                            return fetch('/api/offline/profesor/grupo/' + grupo.id + '/estudiantes')
                                .then(function (res) {
                                    if (!res.ok) return null;
                                    return res.json();
                                })
                                .then(function (estData) {
                                    if (!estData) return;
                                    return HeroicosDB.cacheData(
                                        'grupo_estudiantes_' + grupo.id,
                                        'estudiantes', scope, estData.estudiantes, 24
                                    );
                                });
                        });
                        return Promise.all(promises);
                    });
                }).then(function () {
                    return true;
                });
            }).catch(function (err) {
                console.warn('[HeroicosSync] Prefetch profesor failed:', err.message);
                return false;
            });
        },

        /**
         * Prefetch acudiente data for offline use
         */
        prefetchAcudienteData: function () {
            return HeroicosDB.getUserSession().then(function (session) {
                if (!session || session.rol_nombre !== 'acudiente') return false;

                var scope = 'acudiente_' + session.acudiente_id;

                return fetch('/api/offline/acudiente/cargos').then(function (res) {
                    if (!res.ok) throw new Error('HTTP ' + res.status);
                    return res.json();
                }).then(function (data) {
                    return HeroicosDB.cacheData(
                        'acudiente_cargos_' + session.acudiente_id,
                        'cargos', scope, data, 12
                    );
                }).then(function () {
                    return fetch('/api/offline/acudiente/dashboard');
                }).then(function (res) {
                    if (!res.ok) return null;
                    return res.json();
                }).then(function (data) {
                    if (!data) return;
                    return HeroicosDB.cacheData(
                        'acudiente_dashboard_' + session.acudiente_id,
                        'dashboard', scope, data, 12
                    );
                }).then(function () {
                    return true;
                });
            }).catch(function (err) {
                console.warn('[HeroicosSync] Prefetch acudiente failed:', err.message);
                return false;
            });
        },

        /**
         * Prefetch admin data for offline use
         */
        prefetchAdminData: function () {
            return HeroicosDB.getUserSession().then(function (session) {
                if (!session || session.rol_nombre !== 'admin') return false;

                var scope = 'admin_' + session.user_id;

                return fetch('/api/offline/admin/dashboard').then(function (res) {
                    if (!res.ok) throw new Error('HTTP ' + res.status);
                    return res.json();
                }).then(function (data) {
                    return HeroicosDB.cacheData(
                        'admin_dashboard_' + session.user_id,
                        'admin_dashboard', scope, data, 12
                    );
                }).then(function () {
                    return true;
                });
            }).catch(function (err) {
                console.warn('[HeroicosSync] Prefetch admin failed:', err.message);
                return false;
            });
        },

        /**
         * Check if syncing is in progress
         */
        isSyncing: function () {
            return _isSyncing;
        }
    };

    // =========================================================================
    // PRIVATE: Sync a single queue item
    // =========================================================================

    function syncItem(item, result) {
        return HeroicosDB.updateQueueItem(item.id, { status: 'syncing' }).then(function () {
            var url, fetchOptions;

            if (item.type === 'attendance') {
                url = '/api/offline/attendance';
                fetchOptions = {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        offline_id: item.id,
                        grupo_id: item.payload.grupo_id,
                        profesor_id: item.payload.profesor_id,
                        fecha: item.payload.fecha,
                        hora_inicio: item.payload.hora_inicio,
                        hora_fin: item.payload.hora_fin,
                        observaciones: item.payload.observaciones || '',
                        estudiante_ids: item.payload.estudiante_ids,
                        presentes: item.payload.presentes
                    })
                };
                return doFetch(url, fetchOptions, item, result);

            } else if (item.type === 'payment') {
                url = '/api/offline/payments';

                // Build FormData (may include file)
                var filePromise = item.payload.comprobante_blob_key
                    ? HeroicosDB.getFile(item.payload.comprobante_blob_key)
                    : Promise.resolve(null);

                return filePromise.then(function (fileData) {
                    var fd = new FormData();
                    fd.append('offline_id', item.id);
                    fd.append('acudiente_id', item.payload.acudiente_id);
                    fd.append('fecha_pago', item.payload.fecha_pago);
                    fd.append('metodo_pago', item.payload.metodo_pago);
                    fd.append('banco', item.payload.banco || '');
                    fd.append('referencia_banco', item.payload.referencia_banco || '');
                    fd.append('observaciones', item.payload.observaciones || '');
                    fd.append('valor_total', item.payload.valor_total);

                    // Append cargos
                    (item.payload.cargos || []).forEach(function (cargo, idx) {
                        fd.append('cargos[' + idx + '][cargo_id]', cargo.cargo_id);
                        fd.append('cargos[' + idx + '][valor_aplicado]', cargo.valor_aplicado);
                    });

                    if (fileData) {
                        fd.append('comprobante', fileData.blob, fileData.fileName);
                    }

                    fetchOptions = { method: 'POST', body: fd };
                    return doFetch(url, fetchOptions, item, result);
                });
            }
        });
    }

    function doFetch(url, fetchOptions, item, result) {
        return fetch(url, fetchOptions).then(function (response) {
            return response.json().then(function (data) {
                return { status: response.status, data: data };
            });
        }).then(function (res) {
            if (res.status >= 200 && res.status < 300 && res.data.success) {
                // Success
                return HeroicosDB.updateQueueItem(item.id, {
                    status: 'synced',
                    serverResponse: res.data
                }).then(function () {
                    // Clean up file if payment
                    if (item.payload.comprobante_blob_key) {
                        return HeroicosDB.deleteFile(item.payload.comprobante_blob_key);
                    }
                }).then(function () {
                    result.synced++;
                });

            } else if (res.status === 401) {
                // Auth expired
                result.authExpired = true;
                return HeroicosDB.updateQueueItem(item.id, { status: 'pending' });

            } else if (res.status === 409) {
                // Conflict
                return HeroicosDB.updateQueueItem(item.id, {
                    status: 'failed',
                    errorMessage: res.data.message || 'Conflicto detectado',
                    conflictData: res.data
                }).then(function () {
                    result.failed++;
                });

            } else {
                // Validation or server error
                return HeroicosDB.updateQueueItem(item.id, {
                    status: 'failed',
                    attempts: item.attempts + 1,
                    lastAttempt: Date.now(),
                    errorMessage: res.data.message || ('Error HTTP ' + res.status)
                }).then(function () {
                    result.failed++;
                });
            }
        }).catch(function (err) {
            // Network error
            return HeroicosDB.updateQueueItem(item.id, {
                status: 'failed',
                attempts: item.attempts + 1,
                lastAttempt: Date.now(),
                errorMessage: 'Error de red: ' + err.message
            }).then(function () {
                result.failed++;
            });
        });
    }

})();
