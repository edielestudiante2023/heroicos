<?= $this->extend('layouts/main') ?>

<?= $this->section('sidebar') ?>
<?= $this->include('acudiente/partials/sidebar') ?>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .section-title { font-weight: 600; color: var(--heroicos-primary); border-bottom: 2px solid var(--heroicos-primary); padding-bottom: 0.5rem; margin-bottom: 1rem; }
</style>
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

                    <!-- Datos Personales -->
                    <h6 class="section-title"><i class="bi bi-person me-2"></i>Datos Personales</h6>

                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label small">Nombres *</label>
                            <input type="text" class="form-control form-control-sm" name="nombres"
                                   value="<?= old('nombres') ?>" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small">Apellidos *</label>
                            <input type="text" class="form-control form-control-sm" name="apellidos"
                                   value="<?= old('apellidos') ?>" required>
                        </div>
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label small">Fecha Nac. *</label>
                            <input type="date" class="form-control form-control-sm" name="fecha_nacimiento"
                                   value="<?= old('fecha_nacimiento') ?>" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small">Sexo *</label>
                            <select class="form-select form-select-sm" name="sexo" required>
                                <option value="M" <?= old('sexo') === 'M' ? 'selected' : '' ?>>Masculino</option>
                                <option value="F" <?= old('sexo') === 'F' ? 'selected' : '' ?>>Femenino</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-5">
                            <label class="form-label small">Tipo Doc.</label>
                            <select class="form-select form-select-sm" name="tipo_documento">
                                <option value="TI" <?= old('tipo_documento') === 'TI' ? 'selected' : '' ?>>T.I.</option>
                                <option value="RC" <?= old('tipo_documento') === 'RC' ? 'selected' : '' ?>>R.C.</option>
                                <option value="pasaporte" <?= old('tipo_documento') === 'pasaporte' ? 'selected' : '' ?>>Pasaporte</option>
                                <option value="otro" <?= old('tipo_documento') === 'otro' ? 'selected' : '' ?>>Otro</option>
                            </select>
                        </div>
                        <div class="col-7">
                            <label class="form-label small">Numero Doc.</label>
                            <input type="text" class="form-control form-control-sm" name="numero_documento"
                                   value="<?= old('numero_documento') ?>">
                        </div>
                    </div>

                    <!-- Tallas y Deportivo -->
                    <h6 class="section-title mt-4"><i class="bi bi-dribbble me-2"></i>Datos Deportivos</h6>

                    <div class="row g-2 mb-2">
                        <div class="col-4">
                            <label class="form-label small">Talla Camiseta</label>
                            <input type="text" class="form-control form-control-sm" name="talla_camiseta"
                                   placeholder="S, M, L..." value="<?= old('talla_camiseta') ?>">
                        </div>
                        <div class="col-4">
                            <label class="form-label small">Talla Pantaloneta</label>
                            <input type="text" class="form-control form-control-sm" name="talla_pantaloneta"
                                   placeholder="S, M, L..." value="<?= old('talla_pantaloneta') ?>">
                        </div>
                        <div class="col-4">
                            <label class="form-label small">Talla Medias</label>
                            <input type="text" class="form-control form-control-sm" name="talla_medias"
                                   placeholder="S, M, L..." value="<?= old('talla_medias') ?>">
                        </div>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label small">Posicion</label>
                            <input type="text" class="form-control form-control-sm" name="posicion"
                                   placeholder="Delantero..." value="<?= old('posicion') ?>">
                        </div>
                        <div class="col-6">
                            <label class="form-label small">Pie Dominante</label>
                            <select class="form-select form-select-sm" name="pie_dominante">
                                <option value="derecho" <?= old('pie_dominante') === 'derecho' ? 'selected' : '' ?>>Derecho</option>
                                <option value="izquierdo" <?= old('pie_dominante') === 'izquierdo' ? 'selected' : '' ?>>Izquierdo</option>
                                <option value="ambidiestro" <?= old('pie_dominante') === 'ambidiestro' ? 'selected' : '' ?>>Ambidiestro</option>
                            </select>
                        </div>
                    </div>

                    <!-- Info Medica -->
                    <h6 class="section-title mt-4"><i class="bi bi-heart-pulse me-2"></i>Informacion Medica</h6>

                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label small">EPS</label>
                            <input type="text" class="form-control form-control-sm" name="eps"
                                   placeholder="Nombre de la EPS" value="<?= old('eps') ?>">
                        </div>
                        <div class="col-6">
                            <label class="form-label small">RH</label>
                            <select class="form-select form-select-sm" name="grupo_sanguineo">
                                <option value="">Seleccione...</option>
                                <?php foreach (['O+','O-','A+','A-','B+','B-','AB+','AB-'] as $rh): ?>
                                    <option value="<?= $rh ?>" <?= old('grupo_sanguineo') === $rh ? 'selected' : '' ?>><?= $rh ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Alergias</label>
                        <input type="text" class="form-control form-control-sm" name="alergias"
                               placeholder="Ninguna" value="<?= old('alergias') ?>">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Condiciones medicas</label>
                        <input type="text" class="form-control form-control-sm" name="condiciones_medicas"
                               placeholder="Ninguna" value="<?= old('condiciones_medicas') ?>">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Medicamentos</label>
                        <input type="text" class="form-control form-control-sm" name="medicamentos"
                               placeholder="Ninguno" value="<?= old('medicamentos') ?>">
                    </div>

                    <!-- Contacto Emergencia -->
                    <h6 class="section-title mt-4"><i class="bi bi-telephone me-2"></i>Contacto de Emergencia</h6>

                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label small">Nombre contacto</label>
                            <input type="text" class="form-control form-control-sm" name="contacto_emergencia"
                                   value="<?= old('contacto_emergencia') ?>">
                        </div>
                        <div class="col-6">
                            <label class="form-label small">Telefono emergencia</label>
                            <input type="text" class="form-control form-control-sm" name="telefono_emergencia"
                                   value="<?= old('telefono_emergencia') ?>">
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary w-100 py-2">
                            <i class="bi bi-check-circle me-2"></i>Registrar Estudiante
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
