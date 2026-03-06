<?= $this->extend('layouts/main') ?>

<?= $this->section('sidebar') ?>
<?= $this->include('acudiente/partials/sidebar') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Resumen -->
<div class="row g-4 mb-4">
    <div class="col-md-6 col-xl-4">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="value">$<?= number_format($saldo_total, 0, ',', '.') ?></div>
                    <div class="label">Saldo Pendiente Total</div>
                </div>
                <div class="icon <?= $saldo_total > 0 ? 'bg-danger' : 'bg-success' ?>">
                    <i class="bi bi-<?= $saldo_total > 0 ? 'exclamation-circle' : 'check-circle' ?>"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-4">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="value"><?= count($cargos_pendientes) ?></div>
                    <div class="label">Cargos Pendientes</div>
                </div>
                <div class="icon bg-warning">
                    <i class="bi bi-file-earmark-text"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-4">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="value"><?= count($pagos) ?></div>
                    <div class="label">Pagos Realizados</div>
                </div>
                <div class="icon bg-primary">
                    <i class="bi bi-receipt"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Cargos Pendientes -->
    <div class="col-12">
        <div class="table-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-exclamation-triangle me-2"></i>Cargos Pendientes</span>
                <?php if (!empty($cargos_pendientes)): ?>
                    <a href="<?= site_url('acudiente/pagos/subir') ?>" class="btn btn-sm btn-primary">
                        <i class="bi bi-upload me-1"></i>Registrar Pago
                    </a>
                <?php endif; ?>
            </div>
            <div class="card-body p-0">
                <?php if (empty($cargos_pendientes)): ?>
                    <div class="p-4 text-center text-muted">
                        <i class="bi bi-check-circle fs-1 d-block mb-2 text-success"></i>
                        <h6>No tiene cargos pendientes</h6>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Estudiante</th>
                                    <th>Concepto</th>
                                    <th>Valor</th>
                                    <th>Pagado</th>
                                    <th>Saldo</th>
                                    <th>Vence</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cargos_pendientes as $c): ?>
                                <tr>
                                    <td><?= esc($c['estudiante_nombre']) ?></td>
                                    <td>
                                        <?= esc($c['concepto_nombre']) ?>
                                        <?php if (!empty($c['descripcion'])): ?>
                                            <div class="small text-muted"><?= esc($c['descripcion']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>$<?= number_format($c['valor_original'], 0, ',', '.') ?></td>
                                    <td>$<?= number_format($c['valor_pagado'], 0, ',', '.') ?></td>
                                    <td class="text-danger fw-semibold">$<?= number_format($c['saldo_pendiente'], 0, ',', '.') ?></td>
                                    <td>
                                        <?php if (!empty($c['fecha_vencimiento'])): ?>
                                            <?php
                                            $vencido = strtotime($c['fecha_vencimiento']) < time();
                                            ?>
                                            <span class="<?= $vencido ? 'text-danger fw-semibold' : '' ?>">
                                                <?= date('d/m/Y', strtotime($c['fecha_vencimiento'])) ?>
                                            </span>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $c['estado'] === 'parcial' ? 'warning' : 'danger' ?>">
                                            <?= ucfirst($c['estado']) ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Historial de Pagos -->
    <div class="col-12">
        <div class="table-card">
            <div class="card-header"><i class="bi bi-clock-history me-2"></i>Historial de Pagos</div>
            <div class="card-body p-0">
                <?php if (empty($pagos)): ?>
                    <div class="p-4 text-center text-muted">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        No hay pagos registrados
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Recibo</th>
                                    <th>Fecha Pago</th>
                                    <th>Valor</th>
                                    <th>Metodo</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pagos as $p): ?>
                                <tr>
                                    <td><?= esc($p['numero_recibo']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($p['fecha_pago'])) ?></td>
                                    <td>$<?= number_format($p['valor_total'], 0, ',', '.') ?></td>
                                    <td><?= ucfirst($p['metodo_pago'] ?? '-') ?></td>
                                    <td>
                                        <?php
                                        $badge = match($p['estado']) {
                                            'aprobado' => 'success',
                                            'pendiente_revision' => 'warning',
                                            'rechazado' => 'danger',
                                            default => 'secondary',
                                        };
                                        $label = match($p['estado']) {
                                            'aprobado' => 'Aprobado',
                                            'pendiente_revision' => 'En revision',
                                            'rechazado' => 'Rechazado',
                                            default => ucfirst($p['estado']),
                                        };
                                        ?>
                                        <span class="badge bg-<?= $badge ?>"><?= $label ?></span>
                                        <?php if ($p['estado'] === 'rechazado' && !empty($p['motivo_rechazo'])): ?>
                                            <div class="small text-danger mt-1"><?= esc($p['motivo_rechazo']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
