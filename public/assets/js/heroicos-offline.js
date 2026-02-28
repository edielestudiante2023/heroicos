/**
 * HeroicosOffline - UI Manager for Offline Support
 * Academia Heroicos PWA
 *
 * Handles: offline banner, sync badge, toasts, auto-sync on reconnect.
 * Depends on: heroicos-db.js, heroicos-sync.js
 */
(function () {
    'use strict';

    var _initialized = false;
    var _syncCheckInterval = null;

    // =========================================================================
    // TOAST SYSTEM
    // =========================================================================

    function createToastContainer() {
        var existing = document.getElementById('heroicos-toast-container');
        if (existing) return existing;

        var container = document.createElement('div');
        container.id = 'heroicos-toast-container';
        container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        container.style.zIndex = '11000';
        document.body.appendChild(container);
        return container;
    }

    function showToast(message, type) {
        var container = createToastContainer();
        var icon, bgClass;

        switch (type) {
            case 'success':
                icon = 'bi-check-circle-fill';
                bgClass = 'text-bg-success';
                break;
            case 'warning':
                icon = 'bi-exclamation-triangle-fill';
                bgClass = 'text-bg-warning';
                break;
            case 'danger':
                icon = 'bi-x-circle-fill';
                bgClass = 'text-bg-danger';
                break;
            default:
                icon = 'bi-info-circle-fill';
                bgClass = 'text-bg-primary';
        }

        var toastEl = document.createElement('div');
        toastEl.className = 'toast align-items-center border-0 ' + bgClass;
        toastEl.setAttribute('role', 'alert');
        toastEl.innerHTML =
            '<div class="d-flex">' +
            '  <div class="toast-body">' +
            '    <i class="bi ' + icon + ' me-2"></i>' + message +
            '  </div>' +
            '  <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>' +
            '</div>';

        container.appendChild(toastEl);

        var toast = new bootstrap.Toast(toastEl, { delay: 5000 });
        toast.show();

        toastEl.addEventListener('hidden.bs.toast', function () {
            toastEl.remove();
        });
    }

    // =========================================================================
    // OFFLINE BANNER
    // =========================================================================

    function createOfflineBanner() {
        var existing = document.getElementById('heroicos-offline-banner');
        if (existing) return existing;

        var contentArea = document.querySelector('.content-area');
        if (!contentArea) return null;

        var banner = document.createElement('div');
        banner.id = 'heroicos-offline-banner';
        banner.className = 'alert alert-warning d-flex align-items-center mb-3';
        banner.style.display = 'none';
        banner.style.borderLeft = '4px solid #ffc107';
        banner.style.background = '#fff8e1';
        banner.innerHTML =
            '<i class="bi bi-wifi-off me-2 fs-5"></i>' +
            '<div class="flex-grow-1">' +
            '  <strong>Sin conexion.</strong> Los cambios se guardaran localmente.' +
            '</div>' +
            '<span class="badge bg-warning text-dark" id="heroicos-pending-badge" style="display:none;">0 pendientes</span>';

        contentArea.insertBefore(banner, contentArea.firstChild);
        return banner;
    }

    function showOfflineBanner() {
        var banner = document.getElementById('heroicos-offline-banner') || createOfflineBanner();
        if (banner) banner.style.display = 'flex';
        updatePendingBadge();
    }

    function hideOfflineBanner() {
        var banner = document.getElementById('heroicos-offline-banner');
        if (banner) banner.style.display = 'none';
    }

    // =========================================================================
    // SYNC BADGE (in navbar)
    // =========================================================================

    function createSyncBadge() {
        var existing = document.getElementById('heroicos-sync-indicator');
        if (existing) return existing;

        var userMenu = document.querySelector('.user-menu');
        if (!userMenu) return null;

        var indicator = document.createElement('span');
        indicator.id = 'heroicos-sync-indicator';
        indicator.className = 'position-relative me-3';
        indicator.style.display = 'none';
        indicator.style.cursor = 'pointer';
        indicator.title = 'Datos pendientes de sincronizar';
        indicator.innerHTML =
            '<i class="bi bi-cloud-arrow-up" style="font-size:1.3rem;color:var(--heroicos-primary);"></i>' +
            '<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:0.6rem;">' +
            '  <span id="heroicos-sync-count">0</span>' +
            '</span>';

        indicator.addEventListener('click', function () {
            if (navigator.onLine && !HeroicosSync.isSyncing()) {
                showToast('Sincronizando...', 'info');
                HeroicosSync.processQueue().then(handleSyncResult);
            } else if (!navigator.onLine) {
                showToast('Sin conexion. Se sincronizara automaticamente.', 'warning');
            } else {
                showToast('Sincronizacion en progreso...', 'info');
            }
        });

        userMenu.parentNode.insertBefore(indicator, userMenu);
        return indicator;
    }

    function updatePendingBadge() {
        if (!HeroicosDB.available) return;

        HeroicosDB.getPendingCount().then(function (count) {
            // Update navbar badge
            var indicator = document.getElementById('heroicos-sync-indicator') || createSyncBadge();
            var countEl = document.getElementById('heroicos-sync-count');
            if (indicator) {
                indicator.style.display = count > 0 ? 'inline-block' : 'none';
            }
            if (countEl) {
                countEl.textContent = count;
            }

            // Update offline banner badge
            var pendingBadge = document.getElementById('heroicos-pending-badge');
            if (pendingBadge) {
                pendingBadge.style.display = count > 0 ? 'inline-block' : 'none';
                pendingBadge.textContent = count + ' pendiente' + (count !== 1 ? 's' : '');
            }
        });
    }

    // =========================================================================
    // SYNC RESULT HANDLING
    // =========================================================================

    function handleSyncResult(result) {
        if (!result) return;

        if (result.authExpired) {
            showToast('Sesion expirada. Inicie sesion para sincronizar.', 'danger');
            return;
        }

        if (result.synced > 0 && result.failed === 0) {
            showToast(result.synced + ' registro' + (result.synced > 1 ? 's' : '') + ' sincronizado' + (result.synced > 1 ? 's' : '') + ' exitosamente.', 'success');
        } else if (result.synced > 0 && result.failed > 0) {
            showToast(result.synced + ' sincronizado' + (result.synced > 1 ? 's' : '') + ', ' + result.failed + ' con conflicto.', 'warning');
        } else if (result.failed > 0) {
            showToast(result.failed + ' registro' + (result.failed > 1 ? 's' : '') + ' con error. Revise los detalles.', 'danger');
        }

        updatePendingBadge();
    }

    // =========================================================================
    // USER SESSION CACHING
    // =========================================================================

    function cacheUserSession() {
        if (!HeroicosDB.available) return;

        var body = document.body;
        var userId = body.getAttribute('data-user-id');
        if (!userId) return;

        HeroicosDB.saveUserSession({
            user_id: parseInt(userId),
            email: body.getAttribute('data-user-email') || '',
            nombre: body.getAttribute('data-user-nombre') || '',
            apellido: body.getAttribute('data-user-apellido') || '',
            rol_nombre: body.getAttribute('data-user-rol') || '',
            profesor_id: parseInt(body.getAttribute('data-profesor-id')) || null,
            acudiente_id: parseInt(body.getAttribute('data-acudiente-id')) || null
        });
    }

    // =========================================================================
    // AUTO-PREFETCH
    // =========================================================================

    function autoPrefetch() {
        if (!HeroicosDB.available || !navigator.onLine) return;

        var body = document.body;
        var rol = body.getAttribute('data-user-rol');

        if (rol === 'profesor') {
            HeroicosSync.prefetchProfesorData().then(function (ok) {
                if (ok) console.log('[HeroicosOffline] Profesor data prefetched');
            });
        } else if (rol === 'acudiente') {
            HeroicosSync.prefetchAcudienteData().then(function (ok) {
                if (ok) console.log('[HeroicosOffline] Acudiente data prefetched');
            });
        }
    }

    // =========================================================================
    // REAL CONNECTIVITY CHECK
    // =========================================================================

    /**
     * navigator.onLine is unreliable on mobile. This does a real fetch
     * to the session-check endpoint to verify actual server reachability.
     * Falls back to navigator.onLine if fetch fails with a non-network error.
     */
    function checkRealConnectivity() {
        // Quick bail: if browser says offline, trust it
        if (!navigator.onLine) return Promise.resolve(false);

        return fetch('/api/offline/session-check', {
            method: 'GET',
            cache: 'no-store',
            signal: AbortSignal.timeout ? AbortSignal.timeout(5000) : undefined
        }).then(function (res) {
            // Any HTTP response means server is reachable
            return true;
        }).catch(function () {
            // Network error = truly offline
            return false;
        });
    }

    // =========================================================================
    // STORAGE QUOTA
    // =========================================================================

    function checkStorageQuota() {
        if (!navigator.storage || !navigator.storage.estimate) return;

        navigator.storage.estimate().then(function (estimate) {
            var usedMB = Math.round((estimate.usage || 0) / (1024 * 1024));
            var quotaMB = Math.round((estimate.quota || 0) / (1024 * 1024));
            var pct = quotaMB > 0 ? Math.round((usedMB / quotaMB) * 100) : 0;

            console.log('[HeroicosOffline] Storage: ' + usedMB + 'MB / ' + quotaMB + 'MB (' + pct + '%)');

            if (pct > 80) {
                showToast('Almacenamiento local al ' + pct + '%. Considere sincronizar o liberar espacio.', 'warning');
            }
        }).catch(function () { /* ignore */ });
    }

    // =========================================================================
    // INITIALIZATION
    // =========================================================================

    window.HeroicosOffline = {
        /**
         * Initialize the offline system. Called on DOMContentLoaded.
         */
        init: function () {
            if (_initialized) return;
            _initialized = true;

            HeroicosDB.init().then(function (available) {
                if (!available) {
                    console.warn('[HeroicosOffline] IndexedDB not available, offline features disabled');
                    return;
                }

                // Cache user session from page data attributes
                cacheUserSession();

                // Create UI elements
                createOfflineBanner();
                createSyncBadge();

                // Set initial state using real connectivity check
                checkRealConnectivity().then(function (isOnline) {
                    if (!isOnline) {
                        showOfflineBanner();
                    }
                });
                updatePendingBadge();

                // Online/Offline listeners
                window.addEventListener('online', function () {
                    // Verify with real ping before hiding banner
                    checkRealConnectivity().then(function (isOnline) {
                        if (isOnline) {
                            hideOfflineBanner();
                            showToast('Conexion restaurada.', 'success');
                            // Auto-sync
                            setTimeout(function () {
                                HeroicosSync.processQueue().then(handleSyncResult);
                            }, 1000);
                        }
                    });
                });

                window.addEventListener('offline', function () {
                    showOfflineBanner();
                });

                // Listen for SW sync messages
                if (navigator.serviceWorker) {
                    navigator.serviceWorker.addEventListener('message', function (event) {
                        if (event.data && event.data.type === 'SYNC_REQUESTED') {
                            HeroicosSync.processQueue().then(handleSyncResult);
                        }
                    });
                }

                // Periodic sync check when online (every 60s)
                _syncCheckInterval = setInterval(function () {
                    if (navigator.onLine && !HeroicosSync.isSyncing()) {
                        HeroicosSync.hasPendingItems().then(function (has) {
                            if (has) {
                                HeroicosSync.processQueue().then(handleSyncResult);
                            }
                        });
                    }
                    updatePendingBadge();
                }, 60000);

                // Auto-prefetch data for offline use
                autoPrefetch();

                // Cleanup old records
                HeroicosDB.cleanup();

                // Check storage quota
                checkStorageQuota();
            });
        },

        showToast: showToast,
        updatePendingBadge: updatePendingBadge,
        showOfflineBanner: showOfflineBanner,
        hideOfflineBanner: hideOfflineBanner,

        isOnline: function () {
            return navigator.onLine;
        }
    };

    // Auto-init on DOMContentLoaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            window.HeroicosOffline.init();
        });
    } else {
        window.HeroicosOffline.init();
    }

})();
