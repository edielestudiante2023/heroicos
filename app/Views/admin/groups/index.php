<?= $this->extend('layouts/main') ?>

<?= $this->section('sidebar') ?>
<?php $activePage = 'groups'; ?>
<?= $this->include('partials/admin_sidebar') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="value"><?= $stats['activos'] ?></div>
                    <div class="label">Activos</div>
                </div>
                <div class="icon bg-primary">
                    <i class="bi bi-collection"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="value"><?= $stats['inactivos'] ?></div>
                    <div class="label">Inactivos</div>
                </div>
                <div class="icon bg-warning">
                    <i class="bi bi-collection"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="value"><?= $stats['con_horario'] ?></div>
                    <div class="label">Con Horario</div>
                </div>
                <div class="icon bg-success">
                    <i class="bi bi-calendar-check"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="value"><?= $stats['total'] ?></div>
                    <div class="label">Total</div>
                </div>
                <div class="icon bg-info">
                    <i class="bi bi-collection-fill"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Header with button -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Gestión de Grupos</h4>
        <p class="text-muted mb-0">Administra los grupos de entrenamiento</p>
    </div>
    <a href="<?= site_url('admin/groups/new') ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg me-2"></i>Nuevo Grupo
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
        <form action="<?= site_url('admin/groups') ?>" method="get" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Buscar</label>
                <input type="text" class="form-control" name="search"
                       value="<?= esc($filters['search'] ?? '') ?>"
                       placeholder="Nombre del grupo...">
            </div>
            <div class="col-md-3">
                <label class="form-label">Categoría</label>
                <select class="form-select" name="categoria_id">
                    <option value="">Todas</option>
                    <?php foreach ($categorias as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= ($filters['categoria_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                        <?= esc($cat['nombre']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Estado</label>
                <select class="form-select" name="estado">
                    <option value="">Todos</option>
                    <option value="activo" <?= ($filters['estado'] ?? '') === 'activo' ? 'selected' : '' ?>>Activo</option>
                    <option value="inactivo" <?= ($filters['estado'] ?? '') === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
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

<!-- Groups Table -->
<div class="table-card">
    <div class="card-body p-0">
        <?php if (empty($grupos)): ?>
            <div class="p-4 text-center text-muted">
                <i class="bi bi-collection fs-1 d-block mb-2"></i>
                No se encontraron grupos
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table id="dt-groups" class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Grupo</th>
                            <th>Categoría</th>
                            <th>Profesores</th>
                            <th>Cupos</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($grupos as $grupo): ?>
                        <tr>
                            <td>
                                <strong><?= esc($grupo['nombre']) ?></strong>
                            </td>
                            <td>
                                <span class="badge bg-secondary"><?= esc($grupo['categoria_nombre']) ?></span>
                            </td>
                            <td>
                                <?php if (!empty($grupo['profesores'])): ?>
                                    <?php foreach ($grupo['profesores'] as $prof): ?>
                                        <span class="badge <?= $prof['es_titular'] ? 'bg-primary' : 'bg-light text-dark' ?>">
                                            <?= esc($prof['nombres'] . ' ' . $prof['apellidos']) ?>
                                            <?= $prof['es_titular'] ? '(T)' : '' ?>
                                        </span>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <span class="text-muted">Sin asignar</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $porcentaje = $grupo['cupo_maximo'] > 0 ? ($grupo['inscritos'] / $grupo['cupo_maximo']) * 100 : 0;
                                $colorBarra = $porcentaje >= 90 ? 'danger' : ($porcentaje >= 70 ? 'warning' : 'success');
                                ?>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height: 8px; width: 80px;">
                                        <div class="progress-bar bg-<?= $colorBarra ?>" style="width: <?= $porcentaje ?>%"></div>
                                    </div>
                                    <small><?= $grupo['inscritos'] ?>/<?= $grupo['cupo_maximo'] ?></small>
                                </div>
                            </td>
                            <td>
                                <?php
                                $estadoBadge = $grupo['estado'] === 'activo' ? 'success' : 'warning';
                                ?>
                                <span class="badge bg-<?= $estadoBadge ?>">
                                    <?= ucfirst(esc($grupo['estado'])) ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= site_url('admin/groups/' . $grupo['id']) ?>"
                                       class="btn btn-outline-info" title="Ver">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="<?= site_url('admin/groups/' . $grupo['id'] . '/edit') ?>"
                                       class="btn btn-outline-primary" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <?php if ($grupo['estado'] === 'activo' && $grupo['inscritos'] == 0): ?>
                                    <button type="button" class="btn btn-outline-danger"
                                            onclick="confirmDelete(<?= $grupo['id'] ?>, '<?= esc($grupo['nombre']) ?>')"
                                            title="Desactivar">
                                        <i class="bi bi-x-lg"></i>
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
        <small class="text-muted">Mostrando <?= count($grupos) ?> grupos</small>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Desactivación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro de desactivar el grupo <strong id="deleteGroupName"></strong>?</p>
                <p class="text-warning mb-0">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    El grupo será marcado como inactivo.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form id="deleteForm" method="post" style="display:inline;">
                    <?= csrf_field() ?>
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-x-lg me-1"></i>Desactivar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(function() { initDT('dt-groups', 'Grupos', { columnDefs: [{ orderable: false, targets: [2, 3, -1] }] }); });

function confirmDelete(id, name) {
    document.getElementById('deleteGroupName').textContent = name;
    document.getElementById('deleteForm').action = '<?= site_url('admin/groups') ?>/' + id;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
<?= $this->endSection() ?>
