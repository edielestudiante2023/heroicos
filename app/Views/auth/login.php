<?= $this->extend('layouts/auth') ?>

<?= $this->section('content') ?>

<!-- PWA Install Alert -->
<div id="pwaInstallAlert" class="alert alert-info d-none mb-3" role="alert" style="background: linear-gradient(135deg, #b720d2, #8a189e); border: none; color: white; border-radius: 12px; padding: 1rem;">
    <div class="d-flex align-items-center">
        <i class="bi bi-phone-fill fs-3 me-3"></i>
        <div class="flex-grow-1">
            <strong class="d-block" style="font-size: 0.95rem;">Instala la app de Heroicos</strong>
            <small id="pwaAlertDescription" style="opacity: 0.9;">Acceso directo desde tu pantalla de inicio, sin navegador.</small>
        </div>
        <button id="pwaAlertInstallBtn" class="btn btn-sm btn-light fw-bold ms-2" style="color: #b720d2; border-radius: 20px; white-space: nowrap;">
            <i class="bi bi-download me-1"></i>Instalar
        </button>
    </div>
    <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-2" style="font-size: 0.6rem;" aria-label="Cerrar" onclick="dismissPwaAlert()"></button>
</div>

<h2 class="text-center mb-4">Iniciar Sesión</h2>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-circle me-2"></i>
        <?= session()->getFlashdata('error') ?>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('message')): ?>
    <div class="alert alert-success">
        <i class="bi bi-check-circle me-2"></i>
        <?= session()->getFlashdata('message') ?>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('errors')): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach (session()->getFlashdata('errors') as $error): ?>
                <li><?= esc($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form action="<?= site_url('login') ?>" method="post">
    <?= csrf_field() ?>

    <div class="mb-3">
        <label for="email" class="form-label">Correo Electrónico</label>
        <div class="input-group">
            <span class="input-group-text">
                <i class="bi bi-envelope"></i>
            </span>
            <input type="email"
                   class="form-control"
                   id="email"
                   name="email"
                   value="<?= old('email') ?>"
                   placeholder="correo@ejemplo.com"
                   required
                   autofocus>
        </div>
    </div>

    <div class="mb-3">
        <label for="password" class="form-label">Contraseña</label>
        <div class="input-group">
            <span class="input-group-text">
                <i class="bi bi-lock"></i>
            </span>
            <input type="password"
                   class="form-control"
                   id="password"
                   name="password"
                   placeholder="••••••••"
                   required>
            <button class="btn btn-outline-secondary" type="button" id="togglePassword" tabindex="-1">
                <i class="bi bi-eye" id="togglePasswordIcon"></i>
            </button>
        </div>
    </div>

    <div class="mb-3 form-check">
        <input type="checkbox" class="form-check-input" id="remember" name="remember" value="1">
        <label class="form-check-label" for="remember">Recordarme</label>
    </div>

    <button type="submit" class="btn btn-primary w-100 mb-3">
        <i class="bi bi-box-arrow-in-right me-2"></i>
        Ingresar
    </button>

    <div class="text-center">
        <a href="<?= site_url('forgot-password') ?>">¿Olvidó su contraseña?</a>
    </div>
</form>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.getElementById('togglePassword').addEventListener('click', function() {
    const input = document.getElementById('password');
    const icon = document.getElementById('togglePasswordIcon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('bi-eye', 'bi-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('bi-eye-slash', 'bi-eye');
    }
});

// PWA Install Alert Logic
(function() {
    const alert = document.getElementById('pwaInstallAlert');
    const installBtn = document.getElementById('pwaAlertInstallBtn');
    const description = document.getElementById('pwaAlertDescription');
    if (!alert) return;

    const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone;
    const wasDismissed = sessionStorage.getItem('pwa-alert-dismissed');
    if (isStandalone || wasDismissed) return;

    let deferredPrompt = null;

    // Android/Chrome: capture install prompt
    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt = e;
        alert.classList.remove('d-none');
    });

    // iOS: show manual instructions
    const isIOS = /iphone|ipad|ipod/.test(navigator.userAgent.toLowerCase()) && !window.MSStream;
    if (isIOS) {
        description.innerHTML = 'Toca <i class="bi bi-box-arrow-up"></i> <strong>Compartir</strong> y luego <strong>"Agregar a inicio"</strong>';
        installBtn.style.display = 'none';
        alert.classList.remove('d-none');
    }

    // Install button click
    if (installBtn) {
        installBtn.addEventListener('click', async () => {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                const { outcome } = await deferredPrompt.userChoice;
                if (outcome === 'accepted') {
                    alert.classList.add('d-none');
                }
                deferredPrompt = null;
            }
        });
    }

    window.addEventListener('appinstalled', () => {
        alert.classList.add('d-none');
    });
})();

function dismissPwaAlert() {
    document.getElementById('pwaInstallAlert').classList.add('d-none');
    sessionStorage.setItem('pwa-alert-dismissed', '1');
}
</script>
<?= $this->endSection() ?>
