<?= $this->extend('layouts/main') ?>

<?= $this->section('sidebar') ?>
<?php $activePage = 'attendance'; ?>
<?= $this->include('partials/admin_sidebar') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= site_url('admin/attendance') ?>">Asistencia</a></li>
                <li class="breadcrumb-item active">Sesi&oacute;n</li>
            </ol>
        </nav>
        <h4 class="mb-0"><?= esc($sesion['grupo_nombre']) ?> - <?= date('d/m/Y', strtotime($sesion['fecha'])) ?></h4>
    </div>
    <a href="<?= site_url('admin/attendance') ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Volver
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="table-card mb-4">
            <div class="card-header bg-white">Informaci&oacute;n de la Sesi&oacute;n</div>
            <div class="card-body">
                <p class="mb-2"><strong>Grupo:</strong> <?= esc($sesion['grupo_nombre']) ?></p>
                <p class="mb-2"><strong>Profesor:</strong> <?= esc($sesion['profesor_nombres'] . ' ' . $sesion['profesor_apellidos']) ?></p>
                <p class="mb-2"><strong>Fecha:</strong> <?= date('d/m/Y', strtotime($sesion['fecha'])) ?></p>
                <p class="mb-2"><strong>Horario:</strong> <?= substr($sesion['hora_inicio'], 0, 5) ?> - <?= substr($sesion['hora_fin'], 0, 5) ?></p>
                <p class="mb-0"><strong>Estado:</strong>
                    <span class="badge bg-<?= $sesion['estado'] === 'realizada' ? 'success' : 'secondary' ?>">
                        <?= ucfirst($sesion['estado']) ?>
                    </span>
                </p>
            </div>
        </div>

        <div class="table-card">
            <div class="card-header bg-white">Resumen</div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>Total:</span>
                    <strong><?= $sesion['total_estudiantes'] ?></strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-success">Presentes:</span>
                    <strong class="text-success"><?= $sesion['total_presentes'] ?></strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-danger">Ausentes:</span>
                    <strong class="text-danger"><?= $sesion['total_ausentes'] ?></strong>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="table-card">
            <div class="card-header bg-white">
                <i class="bi bi-people me-2"></i>Lista de Asistencia
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="dt-session" class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>C&oacute;digo</th>
                                <th>Estudiante</th>
                                <th>Asistencia</th>
                                <th>Justificaci&oacute;n</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sesion['asistencias'] as $i => $a): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><small class="text-muted"><?= esc($a['codigo']) ?></small></td>
                                <td><?= esc($a['nombres'] . ' ' . $a['apellidos']) ?></td>
                                <td>
                                    <?php if ($a['presente']): ?>
                                        <span class="badge bg-success"><i class="bi bi-check"></i> Presente</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger"><i class="bi bi-x"></i> Ausente</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $a['justificacion'] ? esc($a['justificacion']) : '-' ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($sesion['observaciones']): ?>
<div class="table-card mt-4">
    <div class="card-header bg-white">Observaciones</div>
    <div class="card-body"><?= esc($sesion['observaciones']) ?></div>
</div>
<?php endif; ?>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(function() { initDT('dt-session', 'Lista Asistencia', { columnDefs: [{ orderable: false, targets: [3] }], paging: false }); });
</script>
<?= $this->endSection() ?>
