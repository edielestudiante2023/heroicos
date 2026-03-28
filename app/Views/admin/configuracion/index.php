<?= $this->extend('layouts/main') ?>

<?= $this->section('sidebar') ?>
<?= $this->include('partials/admin_sidebar') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php if (session()->getFlashdata('success')): ?>
<div class="alert alert-success alert-dismissible fade show">
    <i class="bi bi-check-circle me-2"></i>
    <?= session()->getFlashdata('success') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<form action="<?= site_url('admin/configuracion/guardar') ?>" method="post">
    <?= csrf_field() ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="mb-0"><i class="bi bi-gear me-2"></i>Configuracion del Sistema</h5>
        <button type="submit" class="btn btn-primary btn-sm">
            <i class="bi bi-save me-1"></i>Guardar Cambios
        </button>
    </div>

    <?php foreach ($grupos as $grupo => $configs): ?>
    <div class="table-card mb-4">
        <div class="card-header">
            <i class="bi <?= $grupoIcons[$grupo] ?? 'bi-gear' ?> me-2"></i>
            <?= esc($grupoLabels[$grupo] ?? ucfirst($grupo)) ?>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <?php foreach ($configs as $config): ?>
                <div class="<?= in_array($config['tipo'], ['text', 'json']) ? 'col-12' : 'col-md-6' ?>">
                    <?php if ($config['tipo'] === 'bool'): ?>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox"
                                   id="config_<?= $config['id'] ?>"
                                   name="config_<?= $config['id'] ?>"
                                   value="1"
                                   <?= $config['valor'] === '1' ? 'checked' : '' ?>
                                   <?= !$config['editable'] ? 'disabled' : '' ?>>
                            <label class="form-check-label" for="config_<?= $config['id'] ?>">
                                <?= esc($config['descripcion'] ?: $config['clave']) ?>
                            </label>
                        </div>
                    <?php elseif ($config['tipo'] === 'text'): ?>
                        <label class="form-label small fw-semibold"><?= esc($config['descripcion'] ?: $config['clave']) ?></label>
                        <textarea name="config_<?= $config['id'] ?>" class="form-control form-control-sm" rows="4"
                                  <?= !$config['editable'] ? 'disabled' : '' ?>><?= esc($config['valor']) ?></textarea>
                        <div class="form-text"><?= esc($config['clave']) ?></div>
                    <?php elseif ($config['tipo'] === 'json'): ?>
                        <label class="form-label small fw-semibold"><?= esc($config['descripcion'] ?: $config['clave']) ?></label>
                        <textarea name="config_<?= $config['id'] ?>" class="form-control form-control-sm font-monospace" rows="3"
                                  <?= !$config['editable'] ? 'disabled' : '' ?>><?= esc($config['valor']) ?></textarea>
                        <div class="form-text"><?= esc($config['clave']) ?> (formato JSON)</div>
                    <?php elseif ($config['tipo'] === 'int'): ?>
                        <label class="form-label small fw-semibold"><?= esc($config['descripcion'] ?: $config['clave']) ?></label>
                        <input type="number" name="config_<?= $config['id'] ?>" class="form-control form-control-sm"
                               value="<?= esc($config['valor']) ?>"
                               <?= !$config['editable'] ? 'disabled' : '' ?>>
                        <div class="form-text"><?= esc($config['clave']) ?></div>
                    <?php else: ?>
                        <label class="form-label small fw-semibold"><?= esc($config['descripcion'] ?: $config['clave']) ?></label>
                        <input type="text" name="config_<?= $config['id'] ?>" class="form-control form-control-sm"
                               value="<?= esc($config['valor']) ?>"
                               <?= !$config['editable'] ? 'disabled' : '' ?>>
                        <div class="form-text"><?= esc($config['clave']) ?></div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <div class="text-end mb-4">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-save me-1"></i>Guardar Cambios
        </button>
    </div>
</form>

<?= $this->endSection() ?>
