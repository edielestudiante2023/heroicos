<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Academia Heroicos - Sistema de Gestión">
    <title><?= $title ?? 'Academia Heroicos' ?></title>

    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#b720d2">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Heroicos">
    <meta name="application-name" content="Heroicos">
    <meta name="msapplication-TileColor" content="#b720d2">
    <meta name="msapplication-TileImage" content="<?= base_url('assets/icons/icon-144x144.png') ?>">

    <!-- PWA Manifest -->
    <link rel="manifest" href="<?= base_url('manifest.json') ?>">

    <!-- Apple Touch Icons -->
    <link rel="apple-touch-icon" href="<?= base_url('assets/icons/apple-touch-icon.png') ?>">
    <link rel="apple-touch-icon" sizes="152x152" href="<?= base_url('assets/icons/icon-152x152.png') ?>">
    <link rel="apple-touch-icon" sizes="192x192" href="<?= base_url('assets/icons/icon-192x192.png') ?>">

    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="<?= base_url('assets/icons/icon-96x96.png') ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= base_url('assets/icons/icon-72x72.png') ?>">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root {
            --heroicos-primary: #b720d2;
            --heroicos-secondary: #ffd65e;
            --heroicos-dark: #8a189e;
            --heroicos-light: #f8e6fc;
            --heroicos-accent: #d62b23;
            --sidebar-width: 260px;
        }

        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background-color: #f5f7fa;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: linear-gradient(180deg, var(--heroicos-primary) 0%, var(--heroicos-dark) 100%);
            color: white;
            z-index: 1000;
            transition: transform 0.3s ease;
        }

        .sidebar-header {
            padding: 1.5rem;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-header h4 {
            margin: 0;
            font-weight: 700;
        }

        .sidebar-header small {
            opacity: 0.8;
        }

        .sidebar-nav {
            padding: 1rem 0;
        }

        .sidebar-nav .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 0.75rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }

        .sidebar-nav .nav-link:hover {
            background: rgba(255,255,255,0.1);
            color: white;
        }

        .sidebar-nav .nav-link.active {
            background: rgba(255,255,255,0.15);
            color: white;
            border-left-color: var(--heroicos-secondary);
        }

        .sidebar-nav .nav-link i {
            font-size: 1.25rem;
            width: 24px;
            text-align: center;
        }

        .nav-section {
            padding: 0.5rem 1.5rem;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: rgba(255,255,255,0.5);
            margin-top: 1rem;
        }

        /* Main content */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
        }

        /* Top navbar */
        .top-navbar {
            background: white;
            padding: 1rem 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-title {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
            color: #333;
        }

        .user-menu .dropdown-toggle {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            color: #333;
        }

        .user-menu .dropdown-toggle::after {
            display: none;
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--heroicos-primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        /* Content area */
        .content-area {
            padding: 1.5rem;
        }

        /* Cards */
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            transition: transform 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
        }

        .stat-card .icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .stat-card .icon.bg-primary { background: rgba(183, 32, 210, 0.15); color: var(--heroicos-primary); }
        .stat-card .icon.bg-warning { background: rgba(255, 214, 94, 0.15); color: #d4a800; }
        .stat-card .icon.bg-info { background: rgba(13, 202, 240, 0.15); color: #0dcaf0; }
        .stat-card .icon.bg-danger { background: rgba(214, 43, 35, 0.15); color: var(--heroicos-accent); }

        .stat-card .value {
            font-size: 1.75rem;
            font-weight: 700;
            color: #333;
        }

        .stat-card .label {
            color: #6c757d;
            font-size: 0.875rem;
        }

        /* Alerts */
        .alert {
            border: none;
            border-radius: 8px;
        }

        /* Tables */
        .table-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            overflow: hidden;
        }

        .table-card .card-header {
            background: white;
            border-bottom: 1px solid #e9ecef;
            padding: 1rem 1.5rem;
            font-weight: 600;
        }

        /* Mobile responsiveness */
        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .sidebar-toggle {
                display: block !important;
            }
        }

        .sidebar-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #333;
        }

        /* Sidebar overlay for mobile */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 999;
        }

        .sidebar-overlay.show {
            display: block;
        }
    </style>
    <?= $this->renderSection('styles') ?>
</head>
<body
    data-user-id="<?= esc(session()->get('user_id')) ?>"
    data-user-email="<?= esc(session()->get('email')) ?>"
    data-user-nombre="<?= esc(session()->get('nombre')) ?>"
    data-user-apellido="<?= esc(session()->get('apellido')) ?>"
    data-user-rol="<?= esc(session()->get('rol_nombre')) ?>"
    data-profesor-id="<?= esc(session()->get('profesor_id') ?? '') ?>"
    data-acudiente-id="<?= esc(session()->get('acudiente_id') ?? '') ?>"
>
    <!-- Sidebar Overlay (Mobile) -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="<?= base_url('assets/images/heroicos.png') ?>" alt="Heroicos" style="width:60px;height:60px;object-fit:contain;border-radius:50%;background:white;padding:4px;">
            <h4>Heroicos</h4>
            <small><?= ucfirst(session()->get('rol_nombre') ?? 'Usuario') ?></small>
        </div>

        <nav class="sidebar-nav">
            <?= $this->renderSection('sidebar') ?>
        </nav>

        <!-- User Info at Bottom -->
        <div class="position-absolute bottom-0 w-100 p-3 border-top" style="border-color: rgba(255,255,255,0.1) !important;">
            <div class="d-flex align-items-center gap-2">
                <div class="user-avatar">
                    <?= strtoupper(substr(session()->get('nombre') ?? 'U', 0, 1)) ?>
                </div>
                <div class="flex-grow-1">
                    <div class="fw-semibold small"><?= esc(session()->get('nombre') . ' ' . session()->get('apellido')) ?></div>
                    <div class="small opacity-75"><?= esc(session()->get('email')) ?></div>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Top Navbar -->
        <nav class="top-navbar">
            <div class="d-flex align-items-center gap-3">
                <button class="sidebar-toggle" id="sidebarToggle">
                    <i class="bi bi-list"></i>
                </button>
                <h1 class="page-title"><?= $pageTitle ?? 'Dashboard' ?></h1>
            </div>

            <div class="user-menu dropdown">
                <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown">
                    <span class="d-none d-md-inline"><?= esc(session()->get('nombre')) ?></span>
                    <div class="user-avatar">
                        <?= strtoupper(substr(session()->get('nombre') ?? 'U', 0, 1)) ?>
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i>Mi Perfil</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="<?= site_url('logout') ?>"><i class="bi bi-box-arrow-right me-2"></i>Cerrar Sesión</a></li>
                </ul>
            </div>
        </nav>

        <!-- Content Area -->
        <div class="content-area">
            <?php if (session()->getFlashdata('message')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>
                    <?= session()->getFlashdata('message') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-circle me-2"></i>
                    <?= session()->getFlashdata('error') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?= $this->renderSection('content') ?>
        </div>
    </main>

    <!-- PWA Install Banner -->
    <div id="pwaInstallBanner" style="display:none;position:fixed;bottom:0;left:0;right:0;z-index:10000;">
        <div style="background:white;margin:12px;border-radius:16px;box-shadow:0 -2px 20px rgba(0,0,0,0.15);padding:20px;max-width:420px;margin-left:auto;margin-right:auto;">
            <div style="display:flex;align-items:center;gap:14px;">
                <img src="<?= base_url('assets/images/heroicos.png') ?>" alt="Heroicos" style="width:56px;height:56px;border-radius:12px;object-fit:contain;background:#f8e6fc;padding:4px;">
                <div style="flex:1;">
                    <div style="font-weight:700;font-size:1rem;color:#333;">Instala Heroicos en tu dispositivo</div>
                    <div style="font-size:0.8rem;color:#6c757d;margin-top:2px;">Accede más rápido sin abrir el navegador</div>
                </div>
                <button id="pwaInstallClose" style="background:none;border:none;font-size:1.4rem;color:#adb5bd;cursor:pointer;padding:0;line-height:1;">&times;</button>
            </div>
            <div style="display:flex;gap:10px;margin-top:16px;">
                <button id="pwaInstallLater" style="flex:1;background:none;border:none;color:#6c757d;font-size:0.875rem;cursor:pointer;padding:10px;">Ahora no</button>
                <button id="pwaInstallBtn" style="flex:2;background:linear-gradient(135deg,#b720d2,#8a189e);color:white;border:none;border-radius:10px;padding:10px 20px;font-weight:600;font-size:0.95rem;cursor:pointer;transition:all 0.2s;">
                    <i class="bi bi-download me-1"></i> Instalar
                </button>
            </div>
        </div>
    </div>

    <!-- PWA iOS Install Instructions -->
    <div id="pwaIOSBanner" style="display:none;position:fixed;bottom:0;left:0;right:0;z-index:10000;">
        <div style="background:white;margin:12px;border-radius:16px;box-shadow:0 -2px 20px rgba(0,0,0,0.15);padding:20px;max-width:420px;margin-left:auto;margin-right:auto;">
            <div style="display:flex;align-items:flex-start;gap:14px;">
                <img src="<?= base_url('assets/images/heroicos.png') ?>" alt="Heroicos" style="width:48px;height:48px;border-radius:12px;object-fit:contain;background:#f8e6fc;padding:4px;">
                <div style="flex:1;">
                    <div style="font-weight:700;font-size:1rem;color:#333;">Instala Heroicos</div>
                    <div style="font-size:0.85rem;color:#6c757d;margin-top:6px;line-height:1.5;">
                        1. Toca el botón <strong>Compartir</strong>
                        <svg style="width:16px;height:16px;vertical-align:middle;margin:0 2px;" viewBox="0 0 24 24" fill="none" stroke="#007AFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12v8a2 2 0 002 2h12a2 2 0 002-2v-8"/><polyline points="16 6 12 2 8 6"/><line x1="12" y1="2" x2="12" y2="15"/></svg>
                        <br>
                        2. Selecciona <strong>"Agregar a pantalla de inicio"</strong>
                    </div>
                </div>
                <button id="pwaIOSClose" style="background:none;border:none;font-size:1.4rem;color:#adb5bd;cursor:pointer;padding:0;line-height:1;">&times;</button>
            </div>
            <div style="text-align:center;margin-top:8px;">
                <svg style="width:20px;height:20px;opacity:0.3;" viewBox="0 0 24 24" fill="currentColor"><path d="M12 16l-6-6h12z"/></svg>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Sidebar toggle for mobile
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        sidebarToggle?.addEventListener('click', () => {
            sidebar.classList.toggle('show');
            sidebarOverlay.classList.toggle('show');
        });

        sidebarOverlay?.addEventListener('click', () => {
            sidebar.classList.remove('show');
            sidebarOverlay.classList.remove('show');
        });
    </script>

    <!-- Service Worker Registration + Auto-Update -->
    <script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
            navigator.serviceWorker.register('/sw.js', { scope: '/' }).then(function (reg) {
                console.log('[SW] Registered:', reg.scope);

                // Check for updates immediately, then every 60 seconds
                reg.update().catch(function () {});
                setInterval(function () { reg.update().catch(function () {}); }, 60000);

                // Detect when a new SW is found and waiting
                reg.addEventListener('updatefound', function () {
                    var newWorker = reg.installing;
                    if (!newWorker) return;

                    newWorker.addEventListener('statechange', function () {
                        if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                            // New SW ready - tell it to activate immediately
                            console.log('[SW] New version found, activating...');
                            newWorker.postMessage({ type: 'SKIP_WAITING' });
                        }
                    });
                });
            }).catch(function (err) {
                console.log('[SW] Registration failed:', err);
            });

            // When the new SW takes control, reload the page to use new assets
            var refreshing = false;
            navigator.serviceWorker.addEventListener('controllerchange', function () {
                if (refreshing) return;
                refreshing = true;
                console.log('[SW] New version active, reloading...');
                window.location.reload();
            });
        });
    }
    </script>

    <!-- PWA Install Logic (Main) -->
    <script>
    (function() {
        let deferredPrompt = null;
        const installBanner = document.getElementById('pwaInstallBanner');
        const installBtn = document.getElementById('pwaInstallBtn');
        const installLater = document.getElementById('pwaInstallLater');
        const installClose = document.getElementById('pwaInstallClose');
        const iosBanner = document.getElementById('pwaIOSBanner');
        const iosClose = document.getElementById('pwaIOSClose');

        // Don't show if already installed or dismissed
        const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone;
        const wasDismissed = localStorage.getItem('pwa-install-dismissed');

        if (isStandalone || wasDismissed) return;

        // Android/Chrome: intercept beforeinstallprompt
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            installBanner.style.display = 'block';
        });

        // iOS detection
        const isIOS = /iphone|ipad|ipod/.test(navigator.userAgent.toLowerCase()) && !window.MSStream;
        if (isIOS) {
            iosBanner.style.display = 'block';
        }

        // Install button click
        if (installBtn) {
            installBtn.addEventListener('click', async () => {
                if (deferredPrompt) {
                    deferredPrompt.prompt();
                    const { outcome } = await deferredPrompt.userChoice;
                    console.log('PWA install outcome:', outcome);
                    deferredPrompt = null;
                    installBanner.style.display = 'none';
                    if (outcome === 'accepted') {
                        localStorage.setItem('pwa-install-dismissed', '1');
                    }
                }
            });
        }

        // Close / Later buttons
        function dismissBanner() {
            installBanner.style.display = 'none';
            iosBanner.style.display = 'none';
            localStorage.setItem('pwa-install-dismissed', '1');
        }

        installLater?.addEventListener('click', dismissBanner);
        installClose?.addEventListener('click', dismissBanner);
        iosClose?.addEventListener('click', dismissBanner);

        window.addEventListener('appinstalled', () => {
            installBanner.style.display = 'none';
            localStorage.setItem('pwa-install-dismissed', '1');
            console.log('PWA installed successfully');
        });
    })();
    </script>

    <!-- Offline Support -->
    <script src="<?= base_url('assets/js/heroicos-db.js') ?>"></script>
    <script src="<?= base_url('assets/js/heroicos-sync.js') ?>"></script>
    <script src="<?= base_url('assets/js/heroicos-offline.js') ?>"></script>

    <!-- Inline connectivity override (bypasses stale SW cache) -->
    <script>
    (function () {
        // Force clear old SW caches so new JS files are served
        if ('caches' in window) {
            caches.keys().then(function (names) {
                names.forEach(function (name) {
                    if (name !== 'heroicos-static-v5') {
                        caches.delete(name);
                        console.log('[Inline] Deleted old cache:', name);
                    }
                });
            });
        }

        // Real connectivity check - hide banner if server is reachable
        setTimeout(function () {
            var banner = document.getElementById('heroicos-offline-banner');
            if (!banner || banner.style.display === 'none') return;

            console.log('[Inline] Banner visible, verifying connectivity...');
            fetch('/api/offline/session-check', { method: 'GET', cache: 'no-store' })
                .then(function (res) {
                    console.log('[Inline] Server reachable (HTTP ' + res.status + '), hiding banner');
                    banner.style.display = 'none';
                })
                .catch(function (err) {
                    console.log('[Inline] Server unreachable:', err.message);
                });
        }, 1500);
    })();
    </script>

    <?= $this->renderSection('scripts') ?>
</body>
</html>
