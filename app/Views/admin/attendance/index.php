<?= $this->extend('layouts/main') ?>

<?= $this->section('sidebar') ?>
<?php $activePage = 'attendance'; ?>
<?= $this->include('partials/admin_sidebar') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Historial de Asistencia</h4>
        <p class="text-muted mb-0">Consulta la asistencia por grupo y fecha</p>
    </div>
</div>

<!-- Filters -->
<div class="table-card mb-4">
    <div class="card-body">
        <form action="<?= site_url('admin/attendance') ?>" method="get" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Grupo <span class="text-danger">*</span></label>
                <select class="form-select" name="grupo_id" required>
                    <option value="">Seleccionar grupo...</option>
                    <?php foreach ($grupos as $g): ?>
                    <option value="<?= $g['id'] ?>" <?= ($grupo_id ?? '') == $g['id'] ? 'selected' : '' ?>>
                        <?= esc($g['nombre']) ?> - <?= esc($g['categoria_nombre']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Desde</label>
                <input type="date" class="form-control" name="fecha_desde" value="<?= esc($filters['fecha_desde'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Hasta</label>
                <input type="date" class="form-control" name="fecha_hasta" value="<?= esc($filters['fecha_hasta'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Estado</label>
                <select class="form-select" name="estado">
                    <option value="">Todos</option>
                    <option value="realizada" <?= ($filters['estado'] ?? '') === 'realizada' ? 'selected' : '' ?>>Realizada</option>
                    <option value="programada" <?= ($filters['estado'] ?? '') === 'programada' ? 'selected' : '' ?>>Programada</option>
                    <option value="cancelada" <?= ($filters['estado'] ?? '') === 'cancelada' ? 'selected' : '' ?>>Cancelada</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-outline-primary w-100">
                    <i class="bi bi-search me-1"></i>Consultar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Sessions Table -->
<?php if ($grupo_id): ?>
<div class="table-card">
    <div class="card-body p-0">
        <?php if (empty($sesiones)): ?>
            <div class="p-4 text-center text-muted">
                <i class="bi bi-calendar-x fs-1 d-block mb-2"></i>
                No se encontraron sesiones de clase para este grupo
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Fecha</th>
                            <th>Horario</th>
                            <th>Profesor</th>
                            <th>Presentes</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sesiones as $s): ?>
                        <tr>
                            <td><strong><?= date('d/m/Y', strtotime($s['fecha'])) ?></strong></td>
                            <td><?= substr($s['hora_inicio'], 0, 5) ?> - <?= substr($s['hora_fin'], 0, 5) ?></td>
                            <td><?= esc($s['profesor_nombres'] . ' ' . $s['profesor_apellidos']) ?></td>
                            <td>
                                <?php if ($s['estado'] === 'realizada'): ?>
                                    <?php $pct = $s['total_estudiantes'] > 0 ? ($s['total_presentes'] / $s['total_estudiantes']) * 100 : 0; ?>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height: 8px; width: 60px;">
                                            <div class="progress-bar bg-<?= $pct >= 80 ? 'success' : ($pct >= 60 ? 'warning' : 'danger') ?>"
                                                 style="width: <?= $pct ?>%"></div>
                                        </div>
                                        <small><?= $s['total_presentes'] ?>/<?= $s['total_estudiantes'] ?></small>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $badge = match($s['estado']) {
                                    'realizada'  => 'success',
                                    'programada' => 'info',
                                    'cancelada'  => 'danger',
                                    default      => 'secondary',
                                };
                                ?>
                                <span class="badge bg-<?= $badge ?>"><?= ucfirst($s['estado']) ?></span>
                            </td>
                            <td class="text-end">
                                <a href="<?= site_url('admin/attendance/session/' . $s['id']) ?>"
                                   class="btn btn-sm btn-outline-info">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>
<?= $this->endSection() ?>
