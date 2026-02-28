<?= $this->extend('layouts/auth') ?>

<?= $this->section('content') ?>
<div class="text-center">
    <i class="bi bi-exclamation-triangle text-warning" style="font-size: 3rem;"></i>
    <h5 class="mt-3">Enlace Invalido o Expirado</h5>
    <p class="text-muted">
        El enlace de inscripcion que intentas usar ya no es valido. Puede que haya expirado o ya fue utilizado.
    </p>
    <p class="text-muted small">
        Si necesitas un nuevo enlace, contacta al profesor que te lo envio.
    </p>
    <a href="<?= site_url('login') ?>" class="btn btn-primary mt-2">
        <i class="bi bi-box-arrow-in-right me-2"></i>Ir al Login
    </a>
</div>
<?= $this->endSection() ?>
