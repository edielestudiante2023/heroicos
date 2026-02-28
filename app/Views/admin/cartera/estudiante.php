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
                <li class="breadcrumb-item"><a href="<?= site_url('admin/cartera') ?>">Cartera</a></li>
                <li class="breadcrumb-item active">Cuenta Estudiante</li>
            </ol>
        </nav>
        <h4 class="mb-0"><?= esc($estudiante['nombres'] . ' ' . $estudiante['apellidos']) ?></h4>
        <small class="text-muted">C&oacute;digo: <?= esc($estudiante['codigo']) ?></small>
    </div>
    <a href="<?= site_url('admin/cartera') ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Volver
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

<!-- Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="value">$<?= number_format($cuenta['total_cargos'], 0, ',', '.') ?></div>
                    <div class="label">Total Cargos</div>
                </div>
                <div class="icon bg-info">
                    <i class="bi bi-receipt"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="value text-success">$<?= number_format($cuenta['total_pagado'], 0, ',', '.') ?></div>
                    <div class="label">Total Pagado</div>
                </div>
                <div class="icon bg-primary">
                    <i class="bi bi-check-circle"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="value <?= $cuenta['total_pendiente'] > 0 ? 'text-danger' : 'text-success' ?>">$<?= number_format($cuenta['total_pendiente'], 0, ',', '.') ?></div>
                    <div class="label">Saldo Pendiente</div>
                </div>
                <div class="icon bg-danger">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Cargos -->
    <div class="col-lg-8">
        <div class="table-card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span><i class="bi bi-receipt me-2"></i>Cargos</span>
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#newCargoModal">
                    <i class="bi bi-plus-lg me-1"></i>Nuevo Cargo
                </button>
            </div>
            <div class="card-body p-0">
                <?php if (empty($cuenta['cargos'])): ?>
                    <div class="p-4 text-center text-muted">
                        <i class="bi bi-receipt fs-1 d-block mb-2"></i>
                        No hay cargos registrados
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Concepto</th>
                                    <th>Descripci&oacute;n</th>
                                    <th class="text-end">Valor</th>
                                    <th class="text-end">Pagado</th>
                                    <th class="text-end">Saldo</th>
                                    <th>Estado</th>
                                    <th class="text-end">Acci&oacute;n</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cuenta['cargos'] as $cargo): ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-secondary"><?= esc($cargo['concepto_codigo']) ?></span>
                                        <br><small><?= esc($cargo['concepto_nombre']) ?></small>
                                    </td>
                                    <td>
                                        <?= esc($cargo['descripcion']) ?>
                                        <?php if ($cargo['mes']): ?>
                                            <br><small class="text-muted">Mes: <?= $cargo['mes'] ?>/<?= $cargo['anio'] ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">$<?= number_format($cargo['valor_original'], 0, ',', '.') ?></td>
                                    <td class="text-end text-success">$<?= number_format($cargo['valor_pagado'], 0, ',', '.') ?></td>
                                    <td class="text-end fw-bold <?= $cargo['saldo_pendiente'] > 0 ? 'text-danger' : 'text-success' ?>">
                                        $<?= number_format($cargo['saldo_pendiente'], 0, ',', '.') ?>
                                    </td>
                                    <td>
                                        <?php
                                        $badgeClass = match($cargo['estado']) {
                                            'pagado'  => 'success',
                                            'parcial' => 'warning',
                                            'anulado' => 'secondary',
                                            default   => 'danger',
                                        };
                                        ?>
                                        <span class="badge bg-<?= $badgeClass ?>"><?= ucfirst($cargo['estado']) ?></span>
                                    </td>
                                    <td class="text-end">
                                        <?php if ($cargo['estado'] === 'pendiente' && $cargo['valor_pagado'] == 0): ?>
                                        <form action="<?= site_url('admin/cartera/anular/' . $cargo['id']) ?>" method="post" class="d-inline"
                                              onsubmit="return confirm('&iquest;Anular este cargo?')">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Anular">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php if (!empty($cargo['pagos'])): ?>
                                <tr>
                                    <td colspan="7" class="bg-light">
                                        <small class="fw-bold">Historial de pagos:</small>
                                        <?php foreach ($cargo['pagos'] as $pago): ?>
                                            <span class="badge bg-<?= $pago['pago_estado'] === 'aprobado' ? 'success' : 'warning' ?> ms-2">
                                                <?= esc($pago['numero_recibo']) ?> - $<?= number_format($pago['valor_aplicado'], 0, ',', '.') ?>
                                                (<?= date('d/m/Y', strtotime($pago['fecha_pago'])) ?>)
                                            </span>
                                        <?php endforeach; ?>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Student Info -->
    <div class="col-lg-4">
        <div class="table-card mb-4">
            <div class="card-header bg-white">
                <i class="bi bi-person me-2"></i>Informaci&oacute;n del Estudiante
            </div>
            <div class="card-body">
                <p class="mb-2"><strong>Nombre:</strong> <?= esc($estudiante['nombres'] . ' ' . $estudiante['apellidos']) ?></p>
                <p class="mb-2"><strong>C&oacute;digo:</strong> <?= esc($estudiante['codigo']) ?></p>
                <?php if (!empty($estudiante['acudiente'])): ?>
                <p class="mb-2"><strong>Acudiente:</strong> <?= esc($estudiante['acudiente']['nombres'] . ' ' . $estudiante['acudiente']['apellidos']) ?></p>
                <p class="mb-0"><strong>Email:</strong> <?= esc($estudiante['acudiente']['email']) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="d-grid gap-2">
            <a href="<?= site_url('admin/payments/create') ?>" class="btn btn-success">
                <i class="bi bi-cash-stack me-2"></i>Registrar Pago
            </a>
            <a href="<?= site_url('admin/students/' . $estudiante['id']) ?>" class="btn btn-outline-primary">
                <i class="bi bi-person me-2"></i>Ver Estudiante
            </a>
        </div>
    </div>
</div>

<!-- New Cargo Modal -->
<div class="modal fade" id="newCargoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= site_url('admin/cartera/generar-cargo') ?>" method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="estudiante_id" value="<?= $estudiante['id'] ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Nuevo Cargo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Concepto <span class="text-danger">*</span></label>
                        <select class="form-select" name="concepto_id" required>
                            <option value="">Seleccionar...</option>
                            <?php foreach ($conceptos as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= esc($c['codigo'] . ' - ' . $c['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Per&iacute;odo <span class="text-danger">*</span></label>
                        <select class="form-select" name="periodo_id" required>
                            <?php foreach ($periodos as $p): ?>
                            <option value="<?= $p['id'] ?>" <?= $p['estado'] === 'activo' ? 'selected' : '' ?>>
                                <?= esc($p['nombre']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripci&oacute;n <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="descripcion" required
                               placeholder="Ej: Mensualidad Marzo 2026">
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Valor <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="valor" required
                                   min="0" step="100" placeholder="0">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Mes</label>
                            <select class="form-select" name="mes">
                                <option value="">-</option>
                                <?php for ($m = 1; $m <= 12; $m++): ?>
                                <option value="<?= $m ?>"><?= $m ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">A&ntilde;o</label>
                            <input type="number" class="form-control" name="anio" value="<?= date('Y') ?>" min="2020" max="2030">
                        </div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Fecha Vencimiento</label>
                        <input type="date" class="form-control" name="fecha_vencimiento">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-1"></i>Generar Cargo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
