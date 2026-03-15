<?= $this->extend('layouts/main') ?>

<?= $this->section('sidebar') ?>
<?php $activePage = 'schedules'; ?>
<?= $this->include('partials/admin_sidebar') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Header with button -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Gestión de Horarios</h4>
        <p class="text-muted mb-0">Administra los horarios de entrenamiento</p>
    </div>
    <a href="<?= site_url('admin/schedules/new') ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg me-2"></i>Nuevo Horario
    </a>
</div>

<?php if (session()->getFlashdata('success')): ?>
<div class="alert alert-success alert-dismissible fade show">
    <i class="bi bi-check-circle me-2"></i>
    <?= session()->getFlashdata('success') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
<div class="alert alert-danger alert-dismissible fade show">
    <i class="bi bi-exclamation-circle me-2"></i>
    <?= session()->getFlashdata('error') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Filters -->
<div class="table-card mb-4">
    <div class="card-body">
        <form action="<?= site_url('admin/schedules') ?>" method="get" class="row g-3">
            <div class="col-md-5">
                <label class="form-label">Buscar</label>
                <input type="text" class="form-control" name="search"
                       value="<?= esc($filters['search'] ?? '') ?>"
                       placeholder="Nombre o lugar...">
            </div>
            <div class="col-md-5">
                <label class="form-label">Día de la Semana</label>
                <select class="form-select" name="dia_semana">
                    <option value="">Todos</option>
                    <?php foreach ($dias as $num => $nombre): ?>
                    <option value="<?= $num ?>" <?= ($filters['dia_semana'] ?? '') == $num ? 'selected' : '' ?>>
                        <?= $nombre ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-outline-primary w-100">
                    <i class="bi bi-search me-1"></i>Filtrar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Schedules Table -->
<div class="table-card">
    <div class="card-body p-0">
        <?php if (empty($horarios)): ?>
            <div class="p-4 text-center text-muted">
                <i class="bi bi-calendar3 fs-1 d-block mb-2"></i>
                No se encontraron horarios
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table id="dt-schedules" class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nombre</th>
                            <th>Día</th>
                            <th>Horario</th>
                            <th>Lugar</th>
                            <th>Grupos</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($horarios as $h): ?>
                        <tr>
                            <td><strong><?= esc($h['nombre']) ?></strong></td>
                            <td>
                                <span class="badge bg-primary"><?= esc($h['dia_nombre']) ?></span>
                            </td>
                            <td>
                                <i class="bi bi-clock me-1"></i>
                                <?= substr($h['hora_inicio'], 0, 5) ?> - <?= substr($h['hora_fin'], 0, 5) ?>
                            </td>
                            <td>
                                <?php if ($h['lugar']): ?>
                                <i class="bi bi-geo-alt me-1"></i><?= esc($h['lugar']) ?>
                                <?php else: ?>
                                <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($h['grupos_count'] > 0): ?>
                                <span class="badge bg-success"><?= $h['grupos_count'] ?> grupos</span>
                                <?php else: ?>
                                <span class="text-muted">Sin grupos</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= site_url('admin/schedules/' . $h['id']) ?>"
                                       class="btn btn-outline-info" title="Ver">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="<?= site_url('admin/schedules/' . $h['id'] . '/edit') ?>"
                                       class="btn btn-outline-primary" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <?php if ($h['grupos_count'] == 0): ?>
                                    <button type="button" class="btn btn-outline-danger"
                                            onclick="confirmDelete(<?= $h['id'] ?>, '<?= esc($h['nombre']) ?>')"
                                            title="Eliminar">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    <div class="card-footer bg-white border-top">
        <small class="text-muted">Mostrando <?= count($horarios) ?> horarios</small>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro de eliminar el horario <strong id="deleteScheduleName"></strong>?</p>
                <p class="text-danger mb-0">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    Esta acción no se puede deshacer.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form id="deleteForm" method="post" style="display:inline;">
                    <?= csrf_field() ?>
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-1"></i>Eliminar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(function() { initDT('dt-schedules', 'Horarios', { columnDefs: [{ orderable: false, targets: [-1] }] }); });

function confirmDelete(id, name) {
    document.getElementById('deleteScheduleName').textContent = name;
    document.getElementById('deleteForm').action = '<?= site_url('admin/schedules') ?>/' + id;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
<?= $this->endSection() ?>
