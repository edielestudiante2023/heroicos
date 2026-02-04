<?= $this->extend('layouts/auth') ?>

<?= $this->section('content') ?>

<h2 class="text-center mb-4">Recuperar Contraseña</h2>

<p class="text-muted text-center mb-4">
    Ingrese su correo electrónico y le enviaremos un enlace para restablecer su contraseña.
</p>

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

<form action="<?= site_url('forgot-password') ?>" method="post">
    <?= csrf_field() ?>

    <div class="mb-4">
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

    <button type="submit" class="btn btn-primary w-100 mb-3">
        <i class="bi bi-send me-2"></i>
        Enviar Enlace
    </button>

    <div class="text-center">
        <a href="<?= site_url('login') ?>">
            <i class="bi bi-arrow-left me-1"></i>
            Volver al inicio de sesión
        </a>
    </div>
</form>

<?= $this->endSection() ?>
