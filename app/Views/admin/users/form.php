<?= $this->extend('layouts/main') ?>

<?= $this->section('sidebar') ?>
<?php $activePage = 'users'; ?>
<?= $this->include('partials/admin_sidebar') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= site_url('admin/users') ?>">Usuarios</a></li>
                <li class="breadcrumb-item active"><?= $action === 'create' ? 'Nuevo' : 'Editar' ?></li>
            </ol>
        </nav>
        <h4 class="mb-0"><?= $action === 'create' ? 'Nuevo Usuario' : 'Editar Usuario' ?></h4>
    </div>
    <a href="<?= site_url('admin/users') ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Volver
    </a>
</div>

<?php if (session()->getFlashdata('errors')): ?>
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-circle me-2"></i>
        <strong>Por favor corrija los siguientes errores:</strong>
        <ul class="mb-0 mt-2">
            <?php foreach (session()->getFlashdata('errors') as $error): ?>
                <li><?= esc($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-circle me-2"></i>
        <?= session()->getFlashdata('error') ?>
    </div>
<?php endif; ?>

<!-- Form -->
<div class="table-card">
    <div class="card-body">
        <form action="<?= $action === 'create' ? site_url('admin/users') : site_url('admin/users/' . $usuario['id']) ?>"
              method="post">
            <?= csrf_field() ?>
            <?php if ($action === 'update'): ?>
                <input type="hidden" name="_method" value="PUT">
            <?php endif; ?>

            <div class="row">
                <!-- Left Column - Account Info -->
                <div class="col-md-6">
                    <h5 class="mb-3">
                        <i class="bi bi-person-circle me-2"></i>Información de Cuenta
                    </h5>

                    <div class="mb-3">
                        <label for="email" class="form-label">Correo Electrónico <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email"
                               value="<?= old('email', $usuario['email'] ?? '') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">
                            Contraseña
                            <?php if ($action === 'create'): ?>
                                <span class="text-danger">*</span>
                            <?php else: ?>
                                <small class="text-muted">(dejar vacío para mantener actual)</small>
                            <?php endif; ?>
                        </label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="password" name="password"
                                   <?= $action === 'create' ? 'required' : '' ?> minlength="8">
                            <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="password_confirm" class="form-label">Confirmar Contraseña</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="password_confirm" name="password_confirm"
                                   <?= $action === 'create' ? 'required' : '' ?>>
                            <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password_confirm">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="rol_id" class="form-label">Rol <span class="text-danger">*</span></label>
                            <select class="form-select" id="rol_id" name="rol_id" required>
                                <option value="">Seleccionar...</option>
                                <?php foreach ($roles as $rol): ?>
                                <option value="<?= $rol['id'] ?>"
                                        <?= old('rol_id', $usuario['rol_id'] ?? '') == $rol['id'] ? 'selected' : '' ?>>
                                    <?= ucfirst(esc($rol['nombre'])) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="estado" class="form-label">Estado <span class="text-danger">*</span></label>
                            <select class="form-select" id="estado" name="estado" required>
                                <option value="activo" <?= old('estado', $usuario['estado'] ?? 'activo') === 'activo' ? 'selected' : '' ?>>Activo</option>
                                <option value="inactivo" <?= old('estado', $usuario['estado'] ?? '') === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                                <option value="pendiente" <?= old('estado', $usuario['estado'] ?? '') === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Personal Info -->
                <div class="col-md-6">
                    <h5 class="mb-3">
                        <i class="bi bi-person-vcard me-2"></i>Información Personal
                    </h5>

                    <div class="mb-3">
                        <label for="nombres" class="form-label">Nombres <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nombres" name="nombres"
                               value="<?= old('nombres', $usuario['nombre'] ?? $usuario['nombres'] ?? '') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="apellidos" class="form-label">Apellidos <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="apellidos" name="apellidos"
                               value="<?= old('apellidos', $usuario['apellido'] ?? $usuario['apellidos'] ?? '') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="telefono" class="form-label">Teléfono</label>
                        <input type="tel" class="form-control" id="telefono" name="telefono"
                               value="<?= old('telefono', $usuario['telefono'] ?? '') ?>">
                    </div>

                    <!-- Document fields for profesor/acudiente -->
                    <div id="documento-fields" style="display:none;">
                        <div class="row">
                            <div class="col-md-5 mb-3">
                                <label for="tipo_documento" class="form-label">Tipo Documento <span class="text-danger">*</span></label>
                                <select class="form-select" id="tipo_documento" name="tipo_documento">
                                    <option value="CC" <?= old('tipo_documento', $usuario['tipo_documento'] ?? '') === 'CC' ? 'selected' : '' ?>>Cédula</option>
                                    <option value="CE" <?= old('tipo_documento', $usuario['tipo_documento'] ?? '') === 'CE' ? 'selected' : '' ?>>Cédula Extranjería</option>
                                    <option value="pasaporte" <?= old('tipo_documento', $usuario['tipo_documento'] ?? '') === 'pasaporte' ? 'selected' : '' ?>>Pasaporte</option>
                                </select>
                            </div>
                            <div class="col-md-7 mb-3">
                                <label for="numero_documento" class="form-label">Número Documento <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="numero_documento" name="numero_documento"
                                       value="<?= old('numero_documento', $usuario['numero_documento'] ?? '') ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <hr class="my-4">

            <div class="d-flex justify-content-end gap-2">
                <a href="<?= site_url('admin/users') ?>" class="btn btn-outline-secondary">
                    Cancelar
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-2"></i>
                    <?= $action === 'create' ? 'Crear Usuario' : 'Guardar Cambios' ?>
                </button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
// Show/hide document fields based on role
var rolSelect = document.getElementById('rol_id');
var docFields = document.getElementById('documento-fields');
function toggleDocFields() {
    var selected = rolSelect.options[rolSelect.selectedIndex];
    var rolName = selected ? selected.textContent.trim().toLowerCase() : '';
    docFields.style.display = (rolName === 'profesor' || rolName === 'acudiente') ? '' : 'none';
}
rolSelect.addEventListener('change', toggleDocFields);
toggleDocFields();

document.querySelectorAll('.toggle-password').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var input = document.getElementById(this.dataset.target);
        var icon = this.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('bi-eye', 'bi-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('bi-eye-slash', 'bi-eye');
        }
    });
});
</script>
<?= $this->endSection() ?>
