<?= $this->extend('layouts/main') ?>

<?= $this->section('sidebar') ?>
<?php $activePage = 'students'; ?>
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
                    <i class="bi bi-person-check"></i>
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
                    <i class="bi bi-person-dash"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="value"><?= $stats['retirados'] ?></div>
                    <div class="label">Retirados</div>
                </div>
                <div class="icon bg-danger">
                    <i class="bi bi-person-x"></i>
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
                    <i class="bi bi-people"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Header with button -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Gestión de Estudiantes</h4>
        <p class="text-muted mb-0">Administra los estudiantes de la academia</p>
    </div>
    <a href="<?= site_url('admin/students/new') ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg me-2"></i>Nuevo Estudiante
    </a>
</div>

<!-- Filters -->
<div class="table-card mb-4">
    <div class="card-body">
        <form action="<?= site_url('admin/students') ?>" method="get" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Buscar</label>
                <input type="text" class="form-control" name="search"
                       value="<?= esc($filters['search'] ?? '') ?>"
                       placeholder="Nombre, apellido o documento...">
            </div>
            <div class="col-md-3">
                <label class="form-label">Categoría (Año)</label>
                <select class="form-select" name="categoria">
                    <option value="">Todas</option>
                    <?php foreach ($categorias as $cat): ?>
                    <option value="<?= $cat ?>" <?= ($filters['categoria'] ?? '') == $cat ? 'selected' : '' ?>>
                        <?= $cat ?>
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
                    <option value="retirado" <?= ($filters['estado'] ?? '') === 'retirado' ? 'selected' : '' ?>>Retirado</option>
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

<!-- Students Table -->
<div class="table-card">
    <div class="card-body p-0">
        <?php if (empty($estudiantes)): ?>
            <div class="p-4 text-center text-muted">
                <i class="bi bi-person-badge fs-1 d-block mb-2"></i>
                No se encontraron estudiantes
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Código</th>
                            <th>Estudiante</th>
                            <th>Documento</th>
                            <th>Edad</th>
                            <th>Categoría</th>
                            <th>Acudiente</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($estudiantes as $est): ?>
                        <tr>
                            <td><code><?= esc($est['codigo']) ?></code></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="user-avatar me-2" style="width:36px;height:36px;font-size:0.875rem;background:<?= $est['sexo'] === 'M' ? '#b720d2' : '#ffd65e' ?>;color:<?= $est['sexo'] === 'M' ? 'white' : '#333' ?>">
                                        <?= strtoupper(substr($est['nombres'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <strong><?= esc($est['nombres'] . ' ' . $est['apellidos']) ?></strong>
                                        <div class="small text-muted"><?= $est['sexo'] === 'M' ? 'Masculino' : 'Femenino' ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <small class="text-muted"><?= esc($est['tipo_documento']) ?>:</small>
                                <?= esc($est['numero_documento']) ?>
                            </td>
                            <td><?= $est['edad'] ?> años</td>
                            <td><span class="badge bg-secondary"><?= $est['categoria'] ?></span></td>
                            <td>
                                <?php if ($est['acudiente_nombres']): ?>
                                    <?= esc($est['acudiente_nombres'] . ' ' . $est['acudiente_apellidos']) ?>
                                <?php else: ?>
                                    <span class="text-muted">Sin asignar</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $estadoBadge = match($est['estado']) {
                                    'activo'   => 'success',
                                    'inactivo' => 'warning',
                                    'retirado' => 'danger',
                                    default    => 'secondary'
                                };
                                ?>
                                <span class="badge bg-<?= $estadoBadge ?>">
                                    <?= ucfirst(esc($est['estado'])) ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= site_url('admin/students/' . $est['id']) ?>"
                                       class="btn btn-outline-info" title="Ver">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="<?= site_url('admin/students/' . $est['id'] . '/edit') ?>"
                                       class="btn btn-outline-primary" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <?php if ($est['estado'] !== 'retirado'): ?>
                                    <button type="button" class="btn btn-outline-danger"
                                            onclick="confirmDelete(<?= $est['id'] ?>, '<?= esc($est['nombres'] . ' ' . $est['apellidos']) ?>')"
                                            title="Retirar">
                                        <i class="bi bi-person-x"></i>
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
        <small class="text-muted">Mostrando <?= count($estudiantes) ?> estudiantes</small>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Retiro</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro de retirar al estudiante <strong id="deleteStudentName"></strong>?</p>
                <p class="text-warning mb-0">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    El estudiante será marcado como retirado.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form id="deleteForm" method="post" style="display:inline;">
                    <?= csrf_field() ?>
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-person-x me-1"></i>Retirar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
function confirmDelete(id, name) {
    document.getElementById('deleteStudentName').textContent = name;
    document.getElementById('deleteForm').action = '<?= site_url('admin/students') ?>/' + id;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
<?= $this->endSection() ?>
