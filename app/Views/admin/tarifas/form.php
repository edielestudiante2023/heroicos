<?= $this->extend('layouts/main') ?>

<?= $this->section('sidebar') ?>
<?= $this->include('partials/admin_sidebar') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= site_url('admin/tarifas') ?>">Tarifas</a></li>
                <li class="breadcrumb-item active"><?= $action === 'create' ? 'Nueva' : 'Editar' ?></li>
            </ol>
        </nav>
        <h4 class="mb-0"><?= $action === 'create' ? 'Nueva Tarifa' : 'Editar Tarifa' ?></h4>
    </div>
    <a href="<?= site_url('admin/tarifas') ?>" class="btn btn-outline-secondary">
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

<form action="<?= $action === 'create' ? site_url('admin/tarifas') : site_url('admin/tarifas/' . $tarifa['id']) ?>"
      method="post">
    <?= csrf_field() ?>
    <?php if ($action === 'update'): ?>
        <input type="hidden" name="_method" value="PUT">
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-8">
            <div class="table-card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-tags me-2"></i>Informaci&oacute;n de la Tarifa</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Concepto <span class="text-danger">*</span></label>
                            <select class="form-select" name="concepto_id" required>
                                <option value="">Seleccionar...</option>
                                <?php foreach ($conceptos as $c): ?>
                                <option value="<?= $c['id'] ?>"
                                        <?= old('concepto_id', $tarifa['concepto_id'] ?? '') == $c['id'] ? 'selected' : '' ?>>
                                    <?= esc($c['codigo'] . ' - ' . $c['nombre']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Categor&iacute;a</label>
                            <select class="form-select" name="categoria_id">
                                <option value="">Todas las categor&iacute;as</option>
                                <?php foreach ($categorias as $cat): ?>
                                <option value="<?= $cat['id'] ?>"
                                        <?= old('categoria_id', $tarifa['categoria_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                                    <?= esc($cat['nombre']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">Dejar en blanco aplica a todas las categor&iacute;as</small>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Per&iacute;odo <span class="text-danger">*</span></label>
                            <select class="form-select" name="periodo_id" required>
                                <option value="">Seleccionar...</option>
                                <?php foreach ($periodos as $p): ?>
                                <option value="<?= $p['id'] ?>"
                                        <?= old('periodo_id', $tarifa['periodo_id'] ?? '') == $p['id'] ? 'selected' : '' ?>>
                                    <?= esc($p['nombre']) ?> <?= $p['estado'] === 'activo' ? '(Activo)' : '' ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Valor <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" name="valor"
                                       value="<?= old('valor', $tarifa['valor'] ?? '') ?>"
                                       min="0" step="100" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Vigente Desde <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="vigente_desde"
                                   value="<?= old('vigente_desde', $tarifa['vigente_desde'] ?? date('Y-m-d')) ?>" required>
                        </div>
                        <div class="col-md-6 mb-0">
                            <label class="form-label">Vigente Hasta</label>
                            <input type="date" class="form-control" name="vigente_hasta"
                                   value="<?= old('vigente_hasta', $tarifa['vigente_hasta'] ?? '') ?>">
                            <small class="text-muted">Dejar en blanco para vigencia indefinida</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi bi-check-lg me-2"></i>
                    <?= $action === 'create' ? 'Crear Tarifa' : 'Guardar Cambios' ?>
                </button>
                <a href="<?= site_url('admin/tarifas') ?>" class="btn btn-outline-secondary">
                    Cancelar
                </a>
            </div>
        </div>
    </div>
</form>
<?= $this->endSection() ?>
