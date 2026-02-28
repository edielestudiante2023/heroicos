<?= $this->extend('layouts/main') ?>

<?= $this->section('sidebar') ?>
<?php $activePage = 'tournaments'; ?>
<?= $this->include('partials/admin_sidebar') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= site_url('admin/tournaments') ?>">Torneos</a></li>
                <li class="breadcrumb-item active"><?= $action === 'create' ? 'Nuevo' : 'Editar' ?></li>
            </ol>
        </nav>
        <h4 class="mb-0"><?= $action === 'create' ? 'Nuevo Torneo' : 'Editar Torneo' ?></h4>
    </div>
    <a href="<?= site_url('admin/tournaments') ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Volver
    </a>
</div>

<?php if (session()->getFlashdata('errors')): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach (session()->getFlashdata('errors') as $error): ?>
                <li><?= esc($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form action="<?= $action === 'create' ? site_url('admin/tournaments') : site_url('admin/tournaments/' . $torneo['id']) ?>"
      method="post">
    <?= csrf_field() ?>
    <?php if ($action === 'update'): ?>
        <input type="hidden" name="_method" value="PUT">
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-8">
            <div class="table-card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-trophy me-2"></i>Informaci&oacute;n del Torneo</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nombre"
                               value="<?= old('nombre', $torneo['nombre'] ?? '') ?>" required
                               placeholder="Nombre del torneo...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripci&oacute;n</label>
                        <textarea class="form-control" name="descripcion" rows="3"
                                  placeholder="Descripci&oacute;n del torneo..."><?= old('descripcion', $torneo['descripcion'] ?? '') ?></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Lugar</label>
                            <input type="text" class="form-control" name="lugar"
                                   value="<?= old('lugar', $torneo['lugar'] ?? '') ?>" placeholder="Ubicaci&oacute;n...">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Fecha del Evento <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="fecha_evento"
                                   value="<?= old('fecha_evento', $torneo['fecha_evento'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Hora</label>
                            <input type="time" class="form-control" name="hora_evento"
                                   value="<?= old('hora_evento', isset($torneo['hora_evento']) ? substr($torneo['hora_evento'], 0, 5) : '') ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Apertura de Inscripciones</label>
                            <input type="datetime-local" class="form-control" name="fecha_apertura_inscripcion"
                                   value="<?= old('fecha_apertura_inscripcion', isset($torneo['fecha_apertura_inscripcion']) ? date('Y-m-d\TH:i', strtotime($torneo['fecha_apertura_inscripcion'])) : '') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Cierre de Inscripciones</label>
                            <input type="datetime-local" class="form-control" name="fecha_cierre_inscripcion"
                                   value="<?= old('fecha_cierre_inscripcion', isset($torneo['fecha_cierre_inscripcion']) ? date('Y-m-d\TH:i', strtotime($torneo['fecha_cierre_inscripcion'])) : '') ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="table-card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-gear me-2"></i>Configuraci&oacute;n</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Categor&iacute;a</label>
                        <select class="form-select" name="categoria_id">
                            <option value="">Todas las categor&iacute;as</option>
                            <?php foreach ($categorias as $cat): ?>
                            <option value="<?= $cat['id'] ?>"
                                    <?= old('categoria_id', $torneo['categoria_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                                <?= esc($cat['nombre']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Dejar vac&iacute;o para todas</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Cupo M&aacute;ximo <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="cupo_maximo"
                               value="<?= old('cupo_maximo', $torneo['cupo_maximo'] ?? 30) ?>" min="1" required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Costo <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" name="costo"
                                   value="<?= old('costo', $torneo['costo'] ?? 0) ?>" min="0" step="100" required>
                        </div>
                        <small class="text-muted">$0 = Gratuito</small>
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi bi-check-lg me-2"></i>
                    <?= $action === 'create' ? 'Crear Torneo' : 'Guardar Cambios' ?>
                </button>
                <a href="<?= site_url('admin/tournaments') ?>" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </div>
    </div>
</form>
<?= $this->endSection() ?>
