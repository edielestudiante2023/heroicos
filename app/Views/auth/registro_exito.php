<?= $this->extend('layouts/auth') ?>

<?= $this->section('content') ?>
<div class="text-center">
    <i class="bi bi-check-circle text-success" style="font-size: 3rem;"></i>
    <h5 class="mt-3">Registro Completado</h5>
    <p class="text-muted">
        Tu registro en la <strong>Academia Heroicos</strong> se ha completado exitosamente.
    </p>
    <div class="alert alert-info text-start">
        <i class="bi bi-envelope me-2"></i>
        <strong>Revisa tu correo electronico.</strong><br>
        Te hemos enviado tus credenciales de acceso al email que proporcionaste.
    </div>
    <a href="<?= site_url('login') ?>" class="btn btn-primary w-100 mt-2">
        <i class="bi bi-box-arrow-in-right me-2"></i>Iniciar Sesion
    </a>
</div>
<?= $this->endSection() ?>
