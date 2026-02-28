<?= $this->extend('layouts/main') ?>

<?= $this->section('sidebar') ?>
<?php $activePage = 'groups'; ?>
<?= $this->include('partials/profesor_sidebar') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= site_url('profesor/groups') ?>">Mis Grupos</a></li>
                <li class="breadcrumb-item active"><?= esc($grupo['nombre']) ?></li>
            </ol>
        </nav>
        <h4 class="mb-0"><?= esc($grupo['nombre']) ?></h4>
        <span class="badge bg-secondary"><?= esc($grupo['categoria_nombre']) ?></span>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= site_url('profesor/attendance/take/' . $grupo['id']) ?>" class="btn btn-success">
            <i class="bi bi-check2-square me-2"></i>Tomar Asistencia
        </a>
        <a href="<?= site_url('profesor/groups') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Volver
        </a>
    </div>
</div>

<?php if (session()->getFlashdata('success')): ?>
<div class="alert alert-success alert-dismissible fade show">
    <i class="bi bi-check-circle me-2"></i>
    <?= session()->getFlashdata('success') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-8">
        <!-- Students List -->
        <div class="table-card">
            <div class="card-header bg-white">
                <i class="bi bi-people me-2"></i>Estudiantes Inscritos (<?= count($inscritos) ?>)
            </div>
            <div class="card-body p-0">
                <?php if (empty($inscritos)): ?>
                    <div class="p-4 text-center text-muted">
                        No hay estudiantes inscritos
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>C&oacute;digo</th>
                                    <th>Estudiante</th>
                                    <th>Edad</th>
                                    <th>Inscripci&oacute;n</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($inscritos as $i => $est): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td><small class="text-muted"><?= esc($est['codigo']) ?></small></td>
                                    <td><strong><?= esc($est['nombres'] . ' ' . $est['apellidos']) ?></strong></td>
                                    <td><?= $est['edad'] ?> a&ntilde;os</td>
                                    <td><?= date('d/m/Y', strtotime($est['fecha_inscripcion'])) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="table-card mb-4">
            <div class="card-header bg-white">Informaci&oacute;n del Grupo</div>
            <div class="card-body">
                <p class="mb-2"><strong>Cupo:</strong> <?= $grupo['inscritos'] ?> / <?= $grupo['cupo_maximo'] ?></p>
                <?php if (!empty($grupo['horarios'])): ?>
                <p class="mb-1"><strong>Horarios:</strong></p>
                <?php foreach ($grupo['horarios'] as $h): ?>
                    <div class="mb-1">
                        <span class="badge bg-light text-dark"><?= esc($h['dia_nombre']) ?></span>
                        <?= substr($h['hora_inicio'], 0, 5) ?> - <?= substr($h['hora_fin'], 0, 5) ?>
                        <?php if ($h['lugar']): ?>
                            <br><small class="text-muted"><?= esc($h['lugar']) ?></small>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function() {
    if (typeof HeroicosDB === 'undefined') return;
    var profesorId = document.body.getAttribute('data-profesor-id');
    if (!profesorId || !navigator.onLine) return;

    // Prefetch this group's students specifically for offline use
    HeroicosDB.init().then(function(ok) {
        if (!ok || typeof HeroicosSync === 'undefined') return;
        HeroicosSync.prefetchProfesorData();
    });
})();
</script>
<?= $this->endSection() ?>
