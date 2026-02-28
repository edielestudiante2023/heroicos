<?= $this->extend('layouts/main') ?>

<?= $this->section('sidebar') ?>
<?= $this->include('partials/admin_sidebar') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Tarifas</h4>
        <p class="text-muted mb-0">Administra las tarifas por concepto, categor&iacute;a y per&iacute;odo</p>
    </div>
    <a href="<?= site_url('admin/tarifas/new') ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg me-2"></i>Nueva Tarifa
    </a>
</div>

<?php if (session()->getFlashdata('success')): ?>
<div class="alert alert-success alert-dismissible fade show">
    <i class="bi bi-check-circle me-2"></i>
    <?= session()->getFlashdata('success') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Filters -->
<div class="table-card mb-4">
    <div class="card-body">
        <form action="<?= site_url('admin/tarifas') ?>" method="get" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Concepto</label>
                <select class="form-select" name="concepto_id">
                    <option value="">Todos</option>
                    <?php foreach ($conceptos as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= ($filters['concepto_id'] ?? '') == $c['id'] ? 'selected' : '' ?>>
                        <?= esc($c['codigo'] . ' - ' . $c['nombre']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Categor&iacute;a</label>
                <select class="form-select" name="categoria_id">
                    <option value="">Todas</option>
                    <?php foreach ($categorias as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= ($filters['categoria_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                        <?= esc($cat['nombre']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Per&iacute;odo</label>
                <select class="form-select" name="periodo_id">
                    <option value="">Todos</option>
                    <?php foreach ($periodos as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= ($filters['periodo_id'] ?? '') == $p['id'] ? 'selected' : '' ?>>
                        <?= esc($p['nombre']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-outline-primary w-100">
                    <i class="bi bi-search me-1"></i>Filtrar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Tarifas Table -->
<div class="table-card">
    <div class="card-body p-0">
        <?php if (empty($tarifas)): ?>
            <div class="p-4 text-center text-muted">
                <i class="bi bi-tags fs-1 d-block mb-2"></i>
                No se encontraron tarifas
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Concepto</th>
                            <th>Categor&iacute;a</th>
                            <th>Per&iacute;odo</th>
                            <th class="text-end">Valor</th>
                            <th>Vigencia</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tarifas as $tarifa): ?>
                        <tr>
                            <td>
                                <span class="badge bg-secondary"><?= esc($tarifa['concepto_codigo']) ?></span>
                                <?= esc($tarifa['concepto_nombre']) ?>
                            </td>
                            <td><?= $tarifa['categoria_nombre'] ? esc($tarifa['categoria_nombre']) : '<span class="text-muted">Todas</span>' ?></td>
                            <td><?= esc($tarifa['periodo_nombre']) ?></td>
                            <td class="text-end fw-bold">$<?= number_format($tarifa['valor'], 0, ',', '.') ?></td>
                            <td>
                                <?= date('d/m/Y', strtotime($tarifa['vigente_desde'])) ?>
                                <?php if ($tarifa['vigente_hasta']): ?>
                                    - <?= date('d/m/Y', strtotime($tarifa['vigente_hasta'])) ?>
                                <?php else: ?>
                                    <span class="text-muted">- Indefinido</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= site_url('admin/tarifas/' . $tarifa['id'] . '/edit') ?>"
                                       class="btn btn-outline-primary" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-danger"
                                            onclick="confirmDelete(<?= $tarifa['id'] ?>)" title="Eliminar">
                                        <i class="bi bi-trash"></i>
                                    </button>
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
        <small class="text-muted">Mostrando <?= count($tarifas) ?> tarifas</small>
    </div>
</div>

<!-- Delete Form -->
<form id="deleteForm" method="post" style="display:none;">
    <?= csrf_field() ?>
    <input type="hidden" name="_method" value="DELETE">
</form>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
function confirmDelete(id) {
    if (confirm('&iquest;Est&aacute; seguro de eliminar esta tarifa?')) {
        const form = document.getElementById('deleteForm');
        form.action = '<?= site_url('admin/tarifas') ?>/' + id;
        form.submit();
    }
}
</script>
<?= $this->endSection() ?>
