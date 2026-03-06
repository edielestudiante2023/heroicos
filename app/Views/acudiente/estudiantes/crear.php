<?= $this->extend('layouts/main') ?>

<?= $this->section('sidebar') ?>
<?= $this->include('acudiente/partials/sidebar') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<a href="<?= site_url('acudiente/estudiantes') ?>" class="btn btn-sm btn-outline-secondary mb-3">
    <i class="bi bi-arrow-left me-1"></i>Volver
</a>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="table-card">
            <div class="card-header"><i class="bi bi-person-plus me-2"></i>Registrar Nuevo Estudiante</div>
            <div class="card-body">
                <form action="<?= site_url('acudiente/estudiantes/guardar') ?>" method="post">
                    <?= csrf_field() ?>

                    <h6 class="text-muted mb-3">Datos personales</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nombres <span class="text-danger">*</span></label>
                            <input type="text" name="nombres" class="form-control" required value="<?= old('nombres') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Apellidos <span class="text-danger">*</span></label>
                            <input type="text" name="apellidos" class="form-control" required value="<?= old('apellidos') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tipo documento</label>
                            <select name="tipo_documento" class="form-select">
                                <option value="TI" <?= old('tipo_documento') === 'TI' ? 'selected' : '' ?>>Tarjeta de Identidad</option>
                                <option value="RC" <?= old('tipo_documento') === 'RC' ? 'selected' : '' ?>>Registro Civil</option>
                                <option value="pasaporte" <?= old('tipo_documento') === 'pasaporte' ? 'selected' : '' ?>>Pasaporte</option>
                                <option value="otro" <?= old('tipo_documento') === 'otro' ? 'selected' : '' ?>>Otro</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Numero documento</label>
                            <input type="text" name="numero_documento" class="form-control" value="<?= old('numero_documento') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Fecha nacimiento <span class="text-danger">*</span></label>
                            <input type="date" name="fecha_nacimiento" class="form-control" required value="<?= old('fecha_nacimiento') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Sexo <span class="text-danger">*</span></label>
                            <select name="sexo" class="form-select" required>
                                <option value="M" <?= old('sexo') === 'M' ? 'selected' : '' ?>>Masculino</option>
                                <option value="F" <?= old('sexo') === 'F' ? 'selected' : '' ?>>Femenino</option>
                            </select>
                        </div>
                    </div>

                    <hr class="my-4">
                    <h6 class="text-muted mb-3">Informacion medica</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">EPS</label>
                            <input type="text" name="eps" class="form-control" value="<?= old('eps') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Grupo sanguineo</label>
                            <select name="grupo_sanguineo" class="form-select">
                                <option value="">Seleccione...</option>
                                <?php foreach (['O+','O-','A+','A-','B+','B-','AB+','AB-'] as $rh): ?>
                                    <option value="<?= $rh ?>" <?= old('grupo_sanguineo') === $rh ? 'selected' : '' ?>><?= $rh ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Alergias</label>
                            <input type="text" name="alergias" class="form-control" placeholder="Ninguna" value="<?= old('alergias') ?>">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Condiciones medicas</label>
                            <input type="text" name="condiciones_medicas" class="form-control" placeholder="Ninguna" value="<?= old('condiciones_medicas') ?>">
                        </div>
                    </div>

                    <hr class="my-4">
                    <h6 class="text-muted mb-3">Contacto de emergencia</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nombre contacto emergencia</label>
                            <input type="text" name="contacto_emergencia" class="form-control" value="<?= old('contacto_emergencia') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Telefono emergencia</label>
                            <input type="text" name="telefono_emergencia" class="form-control" value="<?= old('telefono_emergencia') ?>">
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-person-plus me-1"></i>Registrar Estudiante
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
