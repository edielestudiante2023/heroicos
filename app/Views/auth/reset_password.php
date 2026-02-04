<?= $this->extend('layouts/auth') ?>

<?= $this->section('content') ?>

<h2 class="text-center mb-4">Nueva Contraseña</h2>

<p class="text-muted text-center mb-4">
    Ingrese su nueva contraseña. Debe tener al menos 8 caracteres.
</p>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-circle me-2"></i>
        <?= session()->getFlashdata('error') ?>
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

<form action="<?= site_url('reset-password') ?>" method="post">
    <?= csrf_field() ?>
    <input type="hidden" name="token" value="<?= esc($token) ?>">

    <div class="mb-3">
        <label for="password" class="form-label">Nueva Contraseña</label>
        <div class="input-group">
            <span class="input-group-text">
                <i class="bi bi-lock"></i>
            </span>
            <input type="password"
                   class="form-control"
                   id="password"
                   name="password"
                   placeholder="••••••••"
                   minlength="8"
                   required
                   autofocus>
        </div>
    </div>

    <div class="mb-4">
        <label for="password_confirm" class="form-label">Confirmar Contraseña</label>
        <div class="input-group">
            <span class="input-group-text">
                <i class="bi bi-lock-fill"></i>
            </span>
            <input type="password"
                   class="form-control"
                   id="password_confirm"
                   name="password_confirm"
                   placeholder="••••••••"
                   minlength="8"
                   required>
        </div>
    </div>

    <button type="submit" class="btn btn-primary w-100 mb-3">
        <i class="bi bi-check-circle me-2"></i>
        Actualizar Contraseña
    </button>

    <div class="text-center">
        <a href="<?= site_url('login') ?>">
            <i class="bi bi-arrow-left me-1"></i>
            Volver al inicio de sesión
        </a>
    </div>
</form>

<?= $this->endSection() ?>
