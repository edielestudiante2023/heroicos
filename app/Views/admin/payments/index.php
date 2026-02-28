<?= $this->extend('layouts/main') ?>

<?= $this->section('sidebar') ?>
<?= $this->include('partials/admin_sidebar') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="value"><?= $stats['pendientes_revision'] ?></div>
                    <div class="label">Pendientes de Revisi&oacute;n</div>
                </div>
                <div class="icon bg-warning">
                    <i class="bi bi-clock-history"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="value"><?= $stats['aprobados_mes'] ?></div>
                    <div class="label">Aprobados este Mes</div>
                </div>
                <div class="icon bg-primary">
                    <i class="bi bi-check-circle"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Gesti&oacute;n de Pagos</h4>
        <p class="text-muted mb-0">Revisa, aprueba o rechaza los pagos recibidos</p>
    </div>
    <a href="<?= site_url('admin/payments/create') ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg me-2"></i>Registrar Pago
    </a>
</div>

<?php if (session()->getFlashdata('success')): ?>
<div class="alert alert-success alert-dismissible fade show">
    <i class="bi bi-check-circle me-2"></i>
    <?= session()->getFlashdata('success') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
<div class="alert alert-danger alert-dismissible fade show">
    <i class="bi bi-exclamation-circle me-2"></i>
    <?= session()->getFlashdata('error') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Filters -->
<div class="table-card mb-4">
    <div class="card-body">
        <form action="<?= site_url('admin/payments') ?>" method="get" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Buscar</label>
                <input type="text" class="form-control" name="search"
                       value="<?= esc($filters['search'] ?? '') ?>"
                       placeholder="Recibo, nombre, referencia...">
            </div>
            <div class="col-md-2">
                <label class="form-label">Estado</label>
                <select class="form-select" name="estado">
                    <option value="">Todos</option>
                    <option value="pendiente_revision" <?= ($filters['estado'] ?? '') === 'pendiente_revision' ? 'selected' : '' ?>>Pendiente</option>
                    <option value="aprobado" <?= ($filters['estado'] ?? '') === 'aprobado' ? 'selected' : '' ?>>Aprobado</option>
                    <option value="rechazado" <?= ($filters['estado'] ?? '') === 'rechazado' ? 'selected' : '' ?>>Rechazado</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">M&eacute;todo</label>
                <select class="form-select" name="metodo_pago">
                    <option value="">Todos</option>
                    <option value="transferencia" <?= ($filters['metodo_pago'] ?? '') === 'transferencia' ? 'selected' : '' ?>>Transferencia</option>
                    <option value="efectivo" <?= ($filters['metodo_pago'] ?? '') === 'efectivo' ? 'selected' : '' ?>>Efectivo</option>
                    <option value="nequi" <?= ($filters['metodo_pago'] ?? '') === 'nequi' ? 'selected' : '' ?>>Nequi</option>
                    <option value="daviplata" <?= ($filters['metodo_pago'] ?? '') === 'daviplata' ? 'selected' : '' ?>>Daviplata</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Desde</label>
                <input type="date" class="form-control" name="fecha_desde" value="<?= esc($filters['fecha_desde'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Hasta</label>
                <input type="date" class="form-control" name="fecha_hasta" value="<?= esc($filters['fecha_hasta'] ?? '') ?>">
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="submit" class="btn btn-outline-primary w-100">
                    <i class="bi bi-search"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Payments Table -->
<div class="table-card">
    <div class="card-body p-0">
        <?php if (empty($pagos)): ?>
            <div class="p-4 text-center text-muted">
                <i class="bi bi-cash-stack fs-1 d-block mb-2"></i>
                No se encontraron pagos
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Recibo</th>
                            <th>Acudiente</th>
                            <th>Fecha Pago</th>
                            <th class="text-end">Valor</th>
                            <th>M&eacute;todo</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pagos as $pago): ?>
                        <tr>
                            <td><strong><?= esc($pago['numero_recibo']) ?></strong></td>
                            <td><?= esc($pago['acudiente_nombres'] . ' ' . $pago['acudiente_apellidos']) ?></td>
                            <td><?= date('d/m/Y', strtotime($pago['fecha_pago'])) ?></td>
                            <td class="text-end fw-bold">$<?= number_format($pago['valor_total'], 0, ',', '.') ?></td>
                            <td>
                                <?php
                                $metodoBadge = match($pago['metodo_pago']) {
                                    'transferencia' => 'primary',
                                    'efectivo'      => 'success',
                                    'nequi'         => 'info',
                                    'daviplata'     => 'danger',
                                    default         => 'secondary',
                                };
                                ?>
                                <span class="badge bg-<?= $metodoBadge ?>"><?= ucfirst($pago['metodo_pago']) ?></span>
                            </td>
                            <td>
                                <?php
                                $estadoBadge = match($pago['estado']) {
                                    'aprobado'           => 'success',
                                    'pendiente_revision' => 'warning',
                                    'rechazado'          => 'danger',
                                    default              => 'secondary',
                                };
                                $estadoLabel = match($pago['estado']) {
                                    'pendiente_revision' => 'Pendiente',
                                    default              => ucfirst($pago['estado']),
                                };
                                ?>
                                <span class="badge bg-<?= $estadoBadge ?>"><?= $estadoLabel ?></span>
                            </td>
                            <td class="text-end">
                                <a href="<?= site_url('admin/payments/' . $pago['id']) ?>"
                                   class="btn btn-sm <?= $pago['estado'] === 'pendiente_revision' ? 'btn-warning' : 'btn-outline-info' ?>">
                                    <?= $pago['estado'] === 'pendiente_revision' ? '<i class="bi bi-search me-1"></i>Revisar' : '<i class="bi bi-eye"></i>' ?>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    <div class="card-footer bg-white border-top">
        <small class="text-muted">Mostrando <?= count($pagos) ?> pagos</small>
    </div>
</div>
<?= $this->endSection() ?>
