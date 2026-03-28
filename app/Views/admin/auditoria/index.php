<?= $this->extend('layouts/main') ?>

<?= $this->section('sidebar') ?>
<?= $this->include('partials/admin_sidebar') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="mb-4">
    <h5 class="mb-3"><i class="bi bi-shield-check me-2"></i>Auditoria del Sistema</h5>

    <!-- Filters -->
    <div class="table-card mb-4">
        <div class="card-body">
            <form method="get" class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label small">Accion</label>
                    <select name="accion" class="form-select form-select-sm">
                        <option value="">Todas</option>
                        <?php foreach ($acciones as $a): ?>
                        <option value="<?= esc($a) ?>" <?= ($filters['accion'] ?? '') === $a ? 'selected' : '' ?>><?= esc($a) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Tabla</label>
                    <select name="tabla" class="form-select form-select-sm">
                        <option value="">Todas</option>
                        <?php foreach ($tablas as $t): ?>
                        <option value="<?= esc($t) ?>" <?= ($filters['tabla'] ?? '') === $t ? 'selected' : '' ?>><?= esc($t) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Usuario</label>
                    <input type="text" name="usuario" class="form-control form-control-sm" placeholder="Email..." value="<?= esc($filters['usuario'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Desde</label>
                    <input type="date" name="fecha_desde" class="form-control form-control-sm" value="<?= esc($filters['fecha_desde'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Hasta</label>
                    <input type="date" name="fecha_hasta" class="form-control form-control-sm" value="<?= esc($filters['fecha_hasta'] ?? '') ?>">
                </div>
                <div class="col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm flex-grow-1"><i class="bi bi-search me-1"></i>Filtrar</button>
                    <a href="<?= site_url('admin/auditoria') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-lg"></i></a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php if (empty($logs)): ?>
    <div class="table-card">
        <div class="card-body text-center p-5">
            <i class="bi bi-shield-check fs-1 d-block mb-3 text-muted"></i>
            <h6 class="text-muted">No se encontraron registros de auditoria</h6>
        </div>
    </div>
<?php else: ?>
    <div class="table-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="dt-auditoria" class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Fecha</th>
                            <th>Usuario</th>
                            <th>Accion</th>
                            <th>Tabla</th>
                            <th>Registro</th>
                            <th>IP</th>
                            <th>Detalle</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                        <tr>
                            <td class="small"><?= date('d/m/Y H:i:s', strtotime($log['created_at'])) ?></td>
                            <td class="small"><?= esc($log['usuario_email'] ?? 'Sistema') ?></td>
                            <td>
                                <?php
                                $badgeColor = match(true) {
                                    str_contains($log['accion'], 'LOGIN_SUCCESS') => 'success',
                                    str_contains($log['accion'], 'LOGIN_FAILED')  => 'danger',
                                    str_contains($log['accion'], 'CREATE')        => 'primary',
                                    str_contains($log['accion'], 'UPDATE')        => 'warning',
                                    str_contains($log['accion'], 'DELETE')        => 'danger',
                                    default => 'secondary',
                                };
                                ?>
                                <span class="badge bg-<?= $badgeColor ?>"><?= esc($log['accion']) ?></span>
                            </td>
                            <td class="small"><?= esc($log['tabla'] ?? '-') ?></td>
                            <td class="small"><?= $log['registro_id'] ?? '-' ?></td>
                            <td class="small text-muted"><?= esc($log['ip'] ?? '-') ?></td>
                            <td>
                                <?php if ($log['datos_anteriores'] || $log['datos_nuevos']): ?>
                                <a href="<?= site_url('admin/auditoria/' . $log['id']) ?>" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(function() {
    initDT('dt-auditoria', 'Auditoria', {
        order: [[0, 'desc']],
        columnDefs: [{ orderable: false, targets: [-1] }]
    });
});
</script>
<?= $this->endSection() ?>
