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
                <li class="breadcrumb-item active">Reporte</li>
            </ol>
        </nav>
        <h4 class="mb-0"><?= esc($estudiante['nombres'] . ' ' . $estudiante['apellidos']) ?></h4>
    </div>
    <a href="<?= site_url('admin/attendance') ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Volver
    </a>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="value"><?= $stats['total_sesiones'] ?></div>
                    <div class="label">Total Sesiones</div>
                </div>
                <div class="icon bg-info"><i class="bi bi-calendar-check"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="value text-success"><?= $stats['presentes'] ?></div>
                    <div class="label">Presentes</div>
                </div>
                <div class="icon bg-primary"><i class="bi bi-check-circle"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="value text-danger"><?= $stats['ausentes'] ?></div>
                    <div class="label">Ausentes</div>
                </div>
                <div class="icon bg-danger"><i class="bi bi-x-circle"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="value"><?= $stats['porcentaje'] ?>%</div>
                    <div class="label">Asistencia</div>
                </div>
                <div class="icon bg-warning"><i class="bi bi-percent"></i></div>
            </div>
        </div>
    </div>
</div>

<!-- History -->
<div class="table-card">
    <div class="card-header bg-white">
        <i class="bi bi-clock-history me-2"></i>Historial de Asistencia
    </div>
    <div class="card-body p-0">
        <?php if (empty($historial)): ?>
            <div class="p-4 text-center text-muted">
                <i class="bi bi-calendar-x fs-1 d-block mb-2"></i>
                No hay registros de asistencia
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Fecha</th>
                            <th>Grupo</th>
                            <th>Horario</th>
                            <th>Asistencia</th>
                            <th>Justificaci&oacute;n</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($historial as $h): ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($h['fecha'])) ?></td>
                            <td><?= esc($h['grupo_nombre']) ?></td>
                            <td><?= substr($h['hora_inicio'], 0, 5) ?> - <?= substr($h['hora_fin'], 0, 5) ?></td>
                            <td>
                                <?php if ($h['presente']): ?>
                                    <span class="badge bg-success"><i class="bi bi-check me-1"></i>Presente</span>
                                <?php else: ?>
                                    <span class="badge bg-danger"><i class="bi bi-x me-1"></i>Ausente</span>
                                <?php endif; ?>
                            </td>
                            <td><?= $h['justificacion'] ? esc($h['justificacion']) : '<span class="text-muted">-</span>' ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>
