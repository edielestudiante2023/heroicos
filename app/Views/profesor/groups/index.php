<?= $this->extend('layouts/main') ?>

<?= $this->section('sidebar') ?>
<?php $activePage = 'groups'; ?>
<?= $this->include('partials/profesor_sidebar') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Mis Grupos</h4>
        <p class="text-muted mb-0">Grupos de entrenamiento asignados</p>
    </div>
</div>

<?php if (session()->getFlashdata('success')): ?>
<div class="alert alert-success alert-dismissible fade show">
    <i class="bi bi-check-circle me-2"></i>
    <?= session()->getFlashdata('success') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if (empty($grupos)): ?>
    <div class="table-card">
        <div class="card-body text-center text-muted py-5">
            <i class="bi bi-collection fs-1 d-block mb-2"></i>
            No tienes grupos asignados
        </div>
    </div>
<?php else: ?>
    <div class="row g-4">
        <?php foreach ($grupos as $grupo): ?>
        <div class="col-md-6 col-lg-4">
            <div class="table-card h-100">
                <div class="card-body">
                    <h5 class="mb-1"><?= esc($grupo['nombre']) ?></h5>
                    <span class="badge bg-secondary mb-3"><?= esc($grupo['categoria_nombre']) ?></span>
                    <?php if ($grupo['es_titular']): ?>
                        <span class="badge bg-primary mb-3">Titular</span>
                    <?php endif; ?>

                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Inscritos:</span>
                        <strong><?= $grupo['inscritos'] ?> / <?= $grupo['cupo_maximo'] ?></strong>
                    </div>

                    <div class="d-grid gap-2 mt-3">
                        <a href="<?= site_url('profesor/groups/' . $grupo['id']) ?>" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-eye me-1"></i>Ver Grupo
                        </a>
                        <a href="<?= site_url('profesor/attendance/take/' . $grupo['id']) ?>" class="btn btn-success btn-sm">
                            <i class="bi bi-check2-square me-1"></i>Tomar Asistencia
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function() {
    if (typeof HeroicosDB === 'undefined') return;
    var profesorId = document.body.getAttribute('data-profesor-id');
    if (!profesorId) return;

    // Auto-prefetch when visiting groups page (ensures student data is fresh)
    HeroicosDB.init().then(function(ok) {
        if (!ok || !navigator.onLine || typeof HeroicosSync === 'undefined') return;
        HeroicosSync.prefetchProfesorData();
    });
})();
</script>
<?= $this->endSection() ?>
