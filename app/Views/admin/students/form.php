<?= $this->extend('layouts/main') ?>

<?= $this->section('sidebar') ?>
<div class="nav-section">Principal</div>
<a href="<?= site_url('admin/dashboard') ?>" class="nav-link">
    <i class="bi bi-speedometer2"></i>
    Dashboard
</a>

<div class="nav-section">Gestión</div>
<a href="<?= site_url('admin/users') ?>" class="nav-link">
    <i class="bi bi-people"></i>
    Usuarios
</a>
<a href="<?= site_url('admin/students') ?>" class="nav-link active">
    <i class="bi bi-person-badge"></i>
    Estudiantes
</a>
<a href="#" class="nav-link">
    <i class="bi bi-collection"></i>
    Grupos
</a>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= site_url('admin/students') ?>">Estudiantes</a></li>
                <li class="breadcrumb-item active"><?= $action === 'create' ? 'Nuevo' : 'Editar' ?></li>
            </ol>
        </nav>
        <h4 class="mb-0"><?= $action === 'create' ? 'Nuevo Estudiante' : 'Editar Estudiante' ?></h4>
    </div>
    <a href="<?= site_url('admin/students') ?>" class="btn btn-outline-secondary">
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
<form action="<?= $action === 'create' ? site_url('admin/students') : site_url('admin/students/' . $estudiante['id']) ?>"
      method="post">
    <?= csrf_field() ?>
    <?php if ($action === 'update'): ?>
        <input type="hidden" name="_method" value="PUT">
    <?php endif; ?>

    <div class="row">
        <!-- Left Column - Personal Info -->
        <div class="col-lg-8">
            <div class="table-card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-person me-2"></i>Información Personal</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nombres" class="form-label">Nombres <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nombres" name="nombres"
                                   value="<?= old('nombres', $estudiante['nombres'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="apellidos" class="form-label">Apellidos <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="apellidos" name="apellidos"
                                   value="<?= old('apellidos', $estudiante['apellidos'] ?? '') ?>" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="tipo_documento" class="form-label">Tipo Documento <span class="text-danger">*</span></label>
                            <select class="form-select" id="tipo_documento" name="tipo_documento" required>
                                <option value="">Seleccionar...</option>
                                <option value="TI" <?= old('tipo_documento', $estudiante['tipo_documento'] ?? '') === 'TI' ? 'selected' : '' ?>>Tarjeta de Identidad</option>
                                <option value="RC" <?= old('tipo_documento', $estudiante['tipo_documento'] ?? '') === 'RC' ? 'selected' : '' ?>>Registro Civil</option>
                                <option value="pasaporte" <?= old('tipo_documento', $estudiante['tipo_documento'] ?? '') === 'pasaporte' ? 'selected' : '' ?>>Pasaporte</option>
                                <option value="otro" <?= old('tipo_documento', $estudiante['tipo_documento'] ?? '') === 'otro' ? 'selected' : '' ?>>Otro</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="numero_documento" class="form-label">Número Documento <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="numero_documento" name="numero_documento"
                                   value="<?= old('numero_documento', $estudiante['numero_documento'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="sexo" class="form-label">Sexo <span class="text-danger">*</span></label>
                            <select class="form-select" id="sexo" name="sexo" required>
                                <option value="">Seleccionar...</option>
                                <option value="M" <?= old('sexo', $estudiante['sexo'] ?? '') === 'M' ? 'selected' : '' ?>>Masculino</option>
                                <option value="F" <?= old('sexo', $estudiante['sexo'] ?? '') === 'F' ? 'selected' : '' ?>>Femenino</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="fecha_nacimiento" class="form-label">Fecha Nacimiento <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento"
                                   value="<?= old('fecha_nacimiento', $estudiante['fecha_nacimiento'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="telefono" class="form-label">Teléfono</label>
                            <input type="tel" class="form-control" id="telefono" name="telefono"
                                   value="<?= old('telefono', $estudiante['telefono'] ?? '') ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="acudiente_id" class="form-label">Acudiente</label>
                            <select class="form-select" id="acudiente_id" name="acudiente_id">
                                <option value="">Sin asignar</option>
                                <?php foreach ($acudientes as $acu): ?>
                                <option value="<?= $acu['id'] ?>"
                                        <?= old('acudiente_id', $estudiante['acudiente_id'] ?? '') == $acu['id'] ? 'selected' : '' ?>>
                                    <?= esc($acu['nombres'] . ' ' . $acu['apellidos']) ?> (<?= esc($acu['email']) ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="direccion" class="form-label">Dirección</label>
                        <textarea class="form-control" id="direccion" name="direccion" rows="2"><?= old('direccion', $estudiante['direccion'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Medical Info -->
            <div class="table-card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-heart-pulse me-2"></i>Información Médica</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="eps" class="form-label">EPS</label>
                            <input type="text" class="form-control" id="eps" name="eps"
                                   value="<?= old('eps', $estudiante['eps'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="grupo_sanguineo" class="form-label">Grupo Sanguíneo</label>
                            <select class="form-select" id="grupo_sanguineo" name="grupo_sanguineo">
                                <option value="">Seleccionar...</option>
                                <?php foreach (['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $gs): ?>
                                <option value="<?= $gs ?>" <?= old('grupo_sanguineo', $estudiante['grupo_sanguineo'] ?? '') === $gs ? 'selected' : '' ?>><?= $gs ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="alergias" class="form-label">Alergias</label>
                        <textarea class="form-control" id="alergias" name="alergias" rows="2"
                                  placeholder="Describa cualquier alergia conocida"><?= old('alergias', $estudiante['alergias'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="condiciones_medicas" class="form-label">Condiciones Médicas</label>
                        <textarea class="form-control" id="condiciones_medicas" name="condiciones_medicas" rows="2"
                                  placeholder="Describa cualquier condición médica relevante"><?= old('condiciones_medicas', $estudiante['condiciones_medicas'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="medicamentos" class="form-label">Medicamentos</label>
                        <textarea class="form-control" id="medicamentos" name="medicamentos" rows="2"
                                  placeholder="Medicamentos que toma actualmente"><?= old('medicamentos', $estudiante['medicamentos'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Emergency Contact -->
            <div class="table-card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-telephone me-2"></i>Contacto de Emergencia</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="contacto_emergencia" class="form-label">Nombre Contacto</label>
                            <input type="text" class="form-control" id="contacto_emergencia" name="contacto_emergencia"
                                   value="<?= old('contacto_emergencia', $estudiante['contacto_emergencia'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="telefono_emergencia" class="form-label">Teléfono Emergencia</label>
                            <input type="tel" class="form-control" id="telefono_emergencia" name="telefono_emergencia"
                                   value="<?= old('telefono_emergencia', $estudiante['telefono_emergencia'] ?? '') ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Status & Actions -->
        <div class="col-lg-4">
            <?php if ($action === 'update'): ?>
            <div class="table-card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Información</h5>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>Código:</strong> <code><?= esc($estudiante['codigo']) ?></code></p>
                    <p class="mb-2"><strong>Fecha Ingreso:</strong> <?= $estudiante['fecha_ingreso'] ? date('d/m/Y', strtotime($estudiante['fecha_ingreso'])) : 'N/A' ?></p>
                    <p class="mb-0"><strong>Creado:</strong> <?= date('d/m/Y H:i', strtotime($estudiante['created_at'])) ?></p>
                </div>
            </div>
            <?php endif; ?>

            <div class="table-card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-gear me-2"></i>Estado</h5>
                </div>
                <div class="card-body">
                    <?php if ($action === 'update'): ?>
                    <div class="mb-3">
                        <label for="estado" class="form-label">Estado del Estudiante</label>
                        <select class="form-select" id="estado" name="estado">
                            <option value="activo" <?= old('estado', $estudiante['estado'] ?? 'activo') === 'activo' ? 'selected' : '' ?>>Activo</option>
                            <option value="inactivo" <?= old('estado', $estudiante['estado'] ?? '') === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                            <option value="retirado" <?= old('estado', $estudiante['estado'] ?? '') === 'retirado' ? 'selected' : '' ?>>Retirado</option>
                        </select>
                    </div>

                    <div class="mb-3" id="motivoRetiroDiv" style="display:none;">
                        <label for="motivo_retiro" class="form-label">Motivo de Retiro</label>
                        <textarea class="form-control" id="motivo_retiro" name="motivo_retiro" rows="3"><?= old('motivo_retiro', $estudiante['motivo_retiro'] ?? '') ?></textarea>
                    </div>
                    <?php else: ?>
                    <p class="text-muted mb-0">El estudiante se creará con estado <span class="badge bg-success">Activo</span></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi bi-check-lg me-2"></i>
                    <?= $action === 'create' ? 'Crear Estudiante' : 'Guardar Cambios' ?>
                </button>
                <a href="<?= site_url('admin/students') ?>" class="btn btn-outline-secondary">
                    Cancelar
                </a>
            </div>
        </div>
    </div>
</form>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
// Show/hide motivo retiro field
document.getElementById('estado')?.addEventListener('change', function() {
    const motivoDiv = document.getElementById('motivoRetiroDiv');
    if (this.value === 'retirado') {
        motivoDiv.style.display = 'block';
    } else {
        motivoDiv.style.display = 'none';
    }
});

// Trigger on load if editing
if (document.getElementById('estado')?.value === 'retirado') {
    document.getElementById('motivoRetiroDiv').style.display = 'block';
}
</script>
<?= $this->endSection() ?>
