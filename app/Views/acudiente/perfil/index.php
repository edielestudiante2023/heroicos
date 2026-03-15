<?= $this->extend('layouts/main') ?>

<?= $this->section('sidebar') ?>
<?= $this->include('acudiente/partials/sidebar') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row g-4">
    <!-- Profile Info -->
    <div class="col-lg-8">
        <div class="table-card">
            <div class="card-header"><i class="bi bi-person me-2"></i>Datos Personales</div>
            <div class="card-body">
                <form action="<?= site_url('acudiente/perfil/actualizar') ?>" method="post">
                    <?= csrf_field() ?>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nombres</label>
                            <input type="text" class="form-control" value="<?= esc($acudiente['nombres'] ?? '') ?>" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Apellidos</label>
                            <input type="text" class="form-control" value="<?= esc($acudiente['apellidos'] ?? '') ?>" disabled>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tipo Documento</label>
                            <?php if (!empty($acudiente['tipo_documento'])): ?>
                                <input type="text" class="form-control" value="<?= esc($acudiente['tipo_documento']) ?>" disabled>
                            <?php else: ?>
                                <select name="tipo_documento" class="form-select" required>
                                    <option value="">Seleccione...</option>
                                    <option value="CC">Cédula de Ciudadanía</option>
                                    <option value="CE">Cédula de Extranjería</option>
                                    <option value="PA">Pasaporte</option>
                                    <option value="TI">Tarjeta de Identidad</option>
                                </select>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Numero Documento <?= empty($acudiente['numero_documento']) ? '<span class="text-danger">*</span>' : '' ?></label>
                            <?php if (!empty($acudiente['numero_documento'])): ?>
                                <input type="text" class="form-control" value="<?= esc($acudiente['numero_documento']) ?>" disabled>
                            <?php else: ?>
                                <input type="text" name="numero_documento" class="form-control" placeholder="Ingrese su documento" required pattern="[0-9]+" title="Solo números">
                            <?php endif; ?>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" value="<?= esc($email) ?>" disabled>
                        </div>

                        <hr>

                        <div class="col-md-6">
                            <label class="form-label">Telefono <span class="text-danger">*</span></label>
                            <input type="text" name="telefono" class="form-control" value="<?= esc($acudiente['telefono'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Telefono alternativo</label>
                            <input type="text" name="telefono_alt" class="form-control" value="<?= esc($acudiente['telefono_alt'] ?? '') ?>">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Direccion <span class="text-danger">*</span></label>
                            <input type="text" name="direccion" class="form-control" value="<?= esc($acudiente['direccion'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ciudad</label>
                            <input type="text" name="ciudad" class="form-control" value="<?= esc($acudiente['ciudad'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ocupacion</label>
                            <input type="text" name="ocupacion" class="form-control" value="<?= esc($acudiente['ocupacion'] ?? '') ?>">
                        </div>

                        <div class="col-12 mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-1"></i>Guardar Cambios
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Change Password -->
    <div class="col-lg-4">
        <div class="table-card">
            <div class="card-header"><i class="bi bi-shield-lock me-2"></i>Cambiar Contraseña</div>
            <div class="card-body">
                <form action="<?= site_url('acudiente/perfil/cambiar-password') ?>" method="post">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label">Contraseña actual</label>
                        <input type="password" name="password_actual" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nueva contraseña</label>
                        <input type="password" name="password_nuevo" class="form-control" required minlength="6">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirmar contraseña</label>
                        <input type="password" name="password_confirmar" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-warning w-100">
                        <i class="bi bi-key me-1"></i>Cambiar Contraseña
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
