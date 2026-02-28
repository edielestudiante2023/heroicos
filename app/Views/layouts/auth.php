<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Academia Heroicos - Escuela de Fútbol">
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
            --heroicos-accent: #d62b23;
        }

        body {
            background: linear-gradient(135deg, var(--heroicos-primary) 0%, var(--heroicos-dark) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', system-ui, sans-serif;
        }

        .auth-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            overflow: hidden;
            max-width: 420px;
            width: 100%;
        }

        .auth-header {
            background: var(--heroicos-primary);
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .auth-header h1 {
            font-size: 1.75rem;
            font-weight: 700;
            margin: 0;
        }

        .auth-header p {
            margin: 0.5rem 0 0;
            opacity: 0.9;
        }

        .auth-body {
            padding: 2rem;
        }

        .form-control {
            border-radius: 8px;
            padding: 0.75rem 1rem;
            border: 2px solid #e9ecef;
            transition: border-color 0.2s;
        }

        .form-control:focus {
            border-color: var(--heroicos-primary);
            box-shadow: 0 0 0 0.2rem rgba(183, 32, 210, 0.15);
        }

        .btn-primary {
            background: var(--heroicos-primary);
            border: none;
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: all 0.2s;
        }

        .btn-primary:hover {
            background: var(--heroicos-dark);
            transform: translateY(-1px);
        }

        .form-label {
            font-weight: 500;
            color: #495057;
        }

        .input-group-text {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-right: none;
        }

        .input-group .form-control {
            border-left: none;
        }

        .alert {
            border-radius: 8px;
            border: none;
        }

        .logo-img {
            width: 120px;
            height: 120px;
            object-fit: contain;
            margin-bottom: 0.5rem;
            border-radius: 50%;
            background: white;
            padding: 8px;
        }

        a {
            color: var(--heroicos-primary);
        }

        a:hover {
            color: var(--heroicos-dark);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="auth-card mx-auto">
                    <div class="auth-header">
                        <img src="<?= base_url('assets/images/heroicos.png') ?>" alt="Academia Heroicos" class="logo-img">
                        <h1>Academia Heroicos</h1>
                        <p>Escuela de Fútbol</p>
                    </div>
                    <div class="auth-body">
                        <?= $this->renderSection('content') ?>
                    </div>
                </div>
                <p class="text-center mt-3 text-white-50 small">
                    &copy; <?= date('Y') ?> Academia Heroicos. Todos los derechos reservados.
                </p>
            </div>
        </div>
    </div>

    <!-- PWA Install Hint Banner (Auth) -->
    <div id="pwaInstallHintAuth" style="display:none;position:fixed;bottom:0;left:0;right:0;background:rgba(138,24,158,0.95);color:white;padding:10px 16px;text-align:center;font-size:0.875rem;z-index:9999;backdrop-filter:blur(10px);">
        <span id="pwaInstallHintText">
            <i class="bi bi-download me-1"></i>
            Instala la app para un acceso más rápido
            <button id="pwaInstallHintBtn" style="background:white;color:#b720d2;border:none;border-radius:20px;padding:4px 16px;margin-left:10px;font-weight:600;font-size:0.8rem;cursor:pointer;">Instalar</button>
        </span>
        <button onclick="closePwaHintAuth()" style="background:none;border:none;color:rgba(255,255,255,0.7);font-size:1.2rem;position:absolute;right:8px;top:50%;transform:translateY(-50%);cursor:pointer;">&times;</button>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Service Worker Registration -->
    <script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js', { scope: '/' })
                .then(reg => console.log('SW registered:', reg.scope))
                .catch(err => console.log('SW registration failed:', err));
        });
    }
    </script>

    <!-- PWA Install Logic (Auth) -->
    <script>
    (function() {
        let deferredPrompt = null;
        const hintBanner = document.getElementById('pwaInstallHintAuth');
        const hintBtn = document.getElementById('pwaInstallHintBtn');
        const hintText = document.getElementById('pwaInstallHintText');

        // Don't show if already installed or dismissed
        const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone;
        const wasDismissed = localStorage.getItem('pwa-hint-auth-dismissed');

        if (isStandalone || wasDismissed) return;

        // Android/Chrome: intercept beforeinstallprompt
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            hintBanner.style.display = 'block';
        });

        // iOS detection
        const isIOS = /iphone|ipad|ipod/.test(navigator.userAgent.toLowerCase()) && !window.MSStream;
        if (isIOS) {
            hintText.innerHTML = '<i class="bi bi-phone me-1"></i> Para instalar: toca <strong>Compartir</strong> <i class="bi bi-box-arrow-up"></i> y luego <strong>"Agregar a pantalla de inicio"</strong>';
            hintBanner.style.display = 'block';
        }

        if (hintBtn) {
            hintBtn.addEventListener('click', async () => {
                if (deferredPrompt) {
                    deferredPrompt.prompt();
                    const { outcome } = await deferredPrompt.userChoice;
                    console.log('PWA install outcome:', outcome);
                    deferredPrompt = null;
                    hintBanner.style.display = 'none';
                }
            });
        }

        window.addEventListener('appinstalled', () => {
            hintBanner.style.display = 'none';
            localStorage.setItem('pwa-hint-auth-dismissed', '1');
        });
    })();

    function closePwaHintAuth() {
        document.getElementById('pwaInstallHintAuth').style.display = 'none';
        localStorage.setItem('pwa-hint-auth-dismissed', '1');
    }
    </script>
    <?= $this->renderSection('scripts') ?>
</body>
</html>
