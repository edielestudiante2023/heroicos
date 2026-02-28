<?= $this->extend('layouts/main') ?>

<?= $this->section('sidebar') ?>
<?php $activePage = 'users'; ?>
<?= $this->include('partials/admin_sidebar') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Header with button -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Gestión de Usuarios</h4>
        <p class="text-muted mb-0">Administra los usuarios del sistema</p>
    </div>
    <a href="<?= site_url('admin/users/new') ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg me-2"></i>Nuevo Usuario
    </a>
</div>

<!-- Filters -->
<div class="table-card mb-4">
    <div class="card-body">
        <form action="<?= site_url('admin/users') ?>" method="get" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Buscar</label>
                <input type="text" class="form-control" name="search"
                       value="<?= esc($filters['search'] ?? '') ?>"
                       placeholder="Email...">
            </div>
            <div class="col-md-3">
                <label class="form-label">Rol</label>
                <select class="form-select" name="rol">
                    <option value="">Todos los roles</option>
                    <?php foreach ($roles as $rol): ?>
                    <option value="<?= esc($rol['nombre']) ?>"
                            <?= ($filters['rol'] ?? '') === $rol['nombre'] ? 'selected' : '' ?>>
                        <?= ucfirst(esc($rol['nombre'])) ?>
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
                    <option value="pendiente" <?= ($filters['estado'] ?? '') === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
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

<!-- Users Table -->
<div class="table-card">
    <div class="card-body p-0">
        <?php if (empty($usuarios)): ?>
            <div class="p-4 text-center text-muted">
                <i class="bi bi-people fs-1 d-block mb-2"></i>
                No se encontraron usuarios
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Usuario</th>
                            <th>Email</th>
                            <th>Rol</th>
                            <th>Estado</th>
                            <th>Creado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $usuario): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="user-avatar me-2" style="width:36px;height:36px;font-size:0.875rem;">
                                        <?= strtoupper(substr($usuario['nombre'] ?: $usuario['email'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <strong><?= esc($usuario['nombre'] . ' ' . $usuario['apellido']) ?: 'Sin nombre' ?></strong>
                                    </div>
                                </div>
                            </td>
                            <td><?= esc($usuario['email']) ?></td>
                            <td>
                                <?php
                                $rolBadge = match($usuario['rol_nombre']) {
                                    'admin'     => 'danger',
                                    'profesor'  => 'primary',
                                    'acudiente' => 'info',
                                    default     => 'secondary'
                                };
                                ?>
                                <span class="badge bg-<?= $rolBadge ?>">
                                    <?= ucfirst(esc($usuario['rol_nombre'])) ?>
                                </span>
                            </td>
                            <td>
                                <?php
                                $estadoBadge = match($usuario['estado']) {
                                    'activo'    => 'success',
                                    'inactivo'  => 'secondary',
                                    'pendiente' => 'warning',
                                    default     => 'secondary'
                                };
                                ?>
                                <span class="badge bg-<?= $estadoBadge ?>">
                                    <?= ucfirst(esc($usuario['estado'])) ?>
                                </span>
                            </td>
                            <td><?= date('d/m/Y', strtotime($usuario['created_at'])) ?></td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= site_url('admin/users/' . $usuario['id'] . '/edit') ?>"
                                       class="btn btn-outline-primary" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <?php if ($usuario['id'] != session()->get('user_id')): ?>
                                    <button type="button" class="btn btn-outline-danger"
                                            onclick="confirmDelete(<?= $usuario['id'] ?>, '<?= esc($usuario['email']) ?>')"
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
        <small class="text-muted">Total: <?= count($usuarios) ?> usuarios</small>
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
                <p>¿Está seguro de eliminar el usuario <strong id="deleteUserEmail"></strong>?</p>
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
function confirmDelete(id, email) {
    document.getElementById('deleteUserEmail').textContent = email;
    document.getElementById('deleteForm').action = '<?= site_url('admin/users') ?>/' + id;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
<?= $this->endSection() ?>
