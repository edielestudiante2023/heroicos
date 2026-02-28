<?= $this->extend('layouts/main') ?>

<?= $this->section('sidebar') ?>
<?= $this->include('partials/admin_sidebar') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="value">$<?= number_format($stats['total_por_cobrar'], 0, ',', '.') ?></div>
                    <div class="label">Por Cobrar</div>
                </div>
                <div class="icon bg-danger">
                    <i class="bi bi-exclamation-circle"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="value">$<?= number_format($stats['recaudo_mes'], 0, ',', '.') ?></div>
                    <div class="label">Recaudo del Mes</div>
                </div>
                <div class="icon bg-primary">
                    <i class="bi bi-graph-up-arrow"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="value"><?= $stats['morosos'] ?></div>
                    <div class="label">Morosos</div>
                </div>
                <div class="icon bg-warning">
                    <i class="bi bi-person-exclamation"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="value">$<?= number_format($stats['total_pagado'], 0, ',', '.') ?></div>
                    <div class="label">Total Recaudado</div>
                </div>
                <div class="icon bg-info">
                    <i class="bi bi-cash-coin"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Morosos -->
    <?php if (!empty($morosos)): ?>
    <div class="col-lg-4">
        <div class="table-card">
            <div class="card-header bg-white">
                <i class="bi bi-person-exclamation me-2 text-danger"></i>Estudiantes Morosos
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Estudiante</th>
                                <th class="text-end">Deuda</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($morosos, 0, 10) as $moroso): ?>
                            <tr>
                                <td>
                                    <a href="<?= site_url('admin/cartera/estudiante/' . $moroso['id']) ?>" class="text-decoration-none">
                                        <?= esc($moroso['nombres'] . ' ' . $moroso['apellidos']) ?>
                                    </a>
                                    <br><small class="text-muted"><?= $moroso['cargos_vencidos'] ?> cargo(s) vencido(s)</small>
                                </td>
                                <td class="text-end text-danger fw-bold">
                                    $<?= number_format($moroso['total_deuda'], 0, ',', '.') ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Cartera General -->
    <div class="<?= !empty($morosos) ? 'col-lg-8' : 'col-12' ?>">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Cartera General</h4>
            <form action="<?= site_url('admin/cartera/recordatorios') ?>" method="POST" class="d-inline">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-warning"
                        onclick="return confirm('Se enviaran recordatorios a todos los acudientes con cargos vencidos. ¿Continuar?')">
                    <i class="bi bi-envelope me-1"></i>Enviar Recordatorios
                </button>
            </form>
        </div>

        <!-- Filters -->
        <div class="table-card mb-4">
            <div class="card-body">
                <form action="<?= site_url('admin/cartera') ?>" method="get" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Buscar</label>
                        <input type="text" class="form-control" name="search"
                               value="<?= esc($filters['search'] ?? '') ?>"
                               placeholder="Nombre o c&oacute;digo...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Concepto</label>
                        <select class="form-select" name="concepto_id">
                            <option value="">Todos</option>
                            <?php foreach ($conceptos as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= ($filters['concepto_id'] ?? '') == $c['id'] ? 'selected' : '' ?>>
                                <?= esc($c['nombre']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Estado</label>
                        <select class="form-select" name="estado">
                            <option value="">Todos</option>
                            <option value="pendiente" <?= ($filters['estado'] ?? '') === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                            <option value="parcial" <?= ($filters['estado'] ?? '') === 'parcial' ? 'selected' : '' ?>>Parcial</option>
                            <option value="pagado" <?= ($filters['estado'] ?? '') === 'pagado' ? 'selected' : '' ?>>Pagado</option>
                            <option value="anulado" <?= ($filters['estado'] ?? '') === 'anulado' ? 'selected' : '' ?>>Anulado</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-outline-primary w-100">
                            <i class="bi bi-search me-1"></i>Filtrar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Cargos Table -->
        <div class="table-card">
            <div class="card-body p-0">
                <?php if (empty($cargos)): ?>
                    <div class="p-4 text-center text-muted">
                        <i class="bi bi-clipboard-check fs-1 d-block mb-2"></i>
                        No se encontraron cargos
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Estudiante</th>
                                    <th>Concepto</th>
                                    <th class="text-end">Valor</th>
                                    <th class="text-end">Pagado</th>
                                    <th class="text-end">Saldo</th>
                                    <th>Vencimiento</th>
                                    <th>Estado</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cargos as $cargo): ?>
                                <tr>
                                    <td>
                                        <a href="<?= site_url('admin/cartera/estudiante/' . $cargo['estudiante_id']) ?>" class="text-decoration-none">
                                            <strong><?= esc($cargo['nombres'] . ' ' . $cargo['apellidos']) ?></strong>
                                        </a>
                                        <br><small class="text-muted"><?= esc($cargo['estudiante_codigo']) ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary"><?= esc($cargo['concepto_codigo']) ?></span>
                                        <?= esc($cargo['concepto_nombre']) ?>
                                    </td>
                                    <td class="text-end">$<?= number_format($cargo['valor_original'], 0, ',', '.') ?></td>
                                    <td class="text-end text-success">$<?= number_format($cargo['valor_pagado'], 0, ',', '.') ?></td>
                                    <td class="text-end fw-bold <?= $cargo['saldo_pendiente'] > 0 ? 'text-danger' : 'text-success' ?>">
                                        $<?= number_format($cargo['saldo_pendiente'], 0, ',', '.') ?>
                                    </td>
                                    <td>
                                        <?php if ($cargo['fecha_vencimiento']): ?>
                                            <?php
                                            $vencido = strtotime($cargo['fecha_vencimiento']) < strtotime('today');
                                            ?>
                                            <span class="<?= $vencido && $cargo['estado'] !== 'pagado' ? 'text-danger fw-bold' : '' ?>">
                                                <?= date('d/m/Y', strtotime($cargo['fecha_vencimiento'])) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
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
                                        <a href="<?= site_url('admin/cartera/estudiante/' . $cargo['estudiante_id']) ?>"
                                           class="btn btn-sm btn-outline-info" title="Ver cuenta">
                                            <i class="bi bi-eye"></i>
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
                <small class="text-muted">Mostrando <?= count($cargos) ?> cargos</small>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
