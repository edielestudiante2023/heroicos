<?= $this->extend('layouts/main') ?>

<?= $this->section('sidebar') ?>
<?= $this->include('acudiente/partials/sidebar') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php if (session()->getFlashdata('error')): ?>
<div class="alert alert-danger alert-dismissible fade show">
    <i class="bi bi-exclamation-circle me-2"></i>
    <?= session()->getFlashdata('error') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="mb-4">
    <a href="<?= site_url('acudiente/clases-particulares') ?>" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Volver
    </a>
</div>

<div class="table-card">
    <div class="card-header">
        <i class="bi bi-person-video3 me-2"></i>Solicitar Clase Particular
    </div>
    <div class="card-body">
        <form action="<?= site_url('acudiente/clases-particulares/guardar') ?>" method="post">
            <?= csrf_field() ?>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Profesor <span class="text-danger">*</span></label>
                    <select name="profesor_id" class="form-select" required>
                        <option value="">Seleccionar profesor...</option>
                        <?php foreach ($profesores as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= old('profesor_id') == $p['id'] ? 'selected' : '' ?>>
                            <?= esc($p['nombres'] . ' ' . $p['apellidos']) ?>
                            <?= $p['especialidad'] ? ' - ' . esc($p['especialidad']) : '' ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Estudiante <span class="text-danger">*</span></label>
                    <select name="estudiante_id" class="form-select" required>
                        <option value="">Seleccionar estudiante...</option>
                        <?php foreach ($estudiantes as $e): ?>
                        <option value="<?= $e['id'] ?>" <?= old('estudiante_id') == $e['id'] ? 'selected' : '' ?>>
                            <?= esc($e['nombres'] . ' ' . $e['apellidos']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Fecha preferida <span class="text-danger">*</span></label>
                    <input type="date" name="fecha_preferida" class="form-control" required
                           min="<?= date('Y-m-d') ?>" value="<?= old('fecha_preferida') ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Hora preferida <span class="text-danger">*</span></label>
                    <input type="time" name="hora_preferida" class="form-control" required
                           value="<?= old('hora_preferida') ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Duracion <span class="text-danger">*</span></label>
                    <select name="duracion_minutos" class="form-select" required>
                        <option value="30" <?= old('duracion_minutos') == '30' ? 'selected' : '' ?>>30 minutos</option>
                        <option value="60" <?= old('duracion_minutos', '60') == '60' ? 'selected' : '' ?>>60 minutos</option>
                        <option value="90" <?= old('duracion_minutos') == '90' ? 'selected' : '' ?>>90 minutos</option>
                        <option value="120" <?= old('duracion_minutos') == '120' ? 'selected' : '' ?>>120 minutos</option>
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label">Observaciones</label>
                    <textarea name="observaciones" class="form-control" rows="3"
                              placeholder="Indica que aspectos quieres trabajar, necesidades especiales, etc."><?= old('observaciones') ?></textarea>
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-send me-1"></i>Enviar Solicitud
                </button>
                <a href="<?= site_url('acudiente/clases-particulares') ?>" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>
