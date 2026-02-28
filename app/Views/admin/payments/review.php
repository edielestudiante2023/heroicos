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
                <li class="breadcrumb-item"><a href="<?= site_url('admin/payments') ?>">Pagos</a></li>
                <li class="breadcrumb-item active"><?= esc($pago['numero_recibo']) ?></li>
            </ol>
        </nav>
        <h4 class="mb-0">Revisar Pago <?= esc($pago['numero_recibo']) ?></h4>
    </div>
    <a href="<?= site_url('admin/payments') ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Volver
    </a>
</div>

<?php if (session()->getFlashdata('error')): ?>
<div class="alert alert-danger alert-dismissible fade show">
    <i class="bi bi-exclamation-circle me-2"></i>
    <?= session()->getFlashdata('error') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="row g-4">
    <!-- Payment Info -->
    <div class="col-lg-8">
        <!-- Payment Details -->
        <div class="table-card mb-4">
            <div class="card-header bg-white">
                <i class="bi bi-receipt me-2"></i>Informaci&oacute;n del Pago
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-2"><strong>N&uacute;mero de Recibo:</strong> <?= esc($pago['numero_recibo']) ?></p>
                        <p class="mb-2"><strong>Fecha de Pago:</strong> <?= date('d/m/Y', strtotime($pago['fecha_pago'])) ?></p>
                        <p class="mb-2"><strong>Fecha de Registro:</strong> <?= date('d/m/Y H:i', strtotime($pago['fecha_registro'] ?? $pago['created_at'])) ?></p>
                        <p class="mb-2"><strong>M&eacute;todo:</strong>
                            <span class="badge bg-primary"><?= ucfirst($pago['metodo_pago']) ?></span>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-2"><strong>Valor Total:</strong>
                            <span class="fs-4 fw-bold text-success">$<?= number_format($pago['valor_total'], 0, ',', '.') ?></span>
                        </p>
                        <?php if ($pago['banco']): ?>
                        <p class="mb-2"><strong>Banco:</strong> <?= esc($pago['banco']) ?></p>
                        <?php endif; ?>
                        <?php if ($pago['referencia_banco']): ?>
                        <p class="mb-2"><strong>Referencia:</strong> <?= esc($pago['referencia_banco']) ?></p>
                        <?php endif; ?>
                        <p class="mb-2"><strong>Estado:</strong>
                            <?php
                            $estadoBadge = match($pago['estado']) {
                                'aprobado'           => 'success',
                                'pendiente_revision' => 'warning',
                                'rechazado'          => 'danger',
                                default              => 'secondary',
                            };
                            $estadoLabel = match($pago['estado']) {
                                'pendiente_revision' => 'Pendiente de Revisi&oacute;n',
                                default              => ucfirst($pago['estado']),
                            };
                            ?>
                            <span class="badge bg-<?= $estadoBadge ?> fs-6"><?= $estadoLabel ?></span>
                        </p>
                    </div>
                </div>
                <?php if ($pago['observaciones']): ?>
                <hr>
                <p class="mb-0"><strong>Observaciones:</strong> <?= esc($pago['observaciones']) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Payment Details (cargos) -->
        <div class="table-card mb-4">
            <div class="card-header bg-white">
                <i class="bi bi-list-check me-2"></i>Detalle de Cargos Aplicados
            </div>
            <div class="card-body p-0">
                <?php if (empty($pago['detalles'])): ?>
                    <div class="p-4 text-center text-muted">Sin detalles de cargos</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Estudiante</th>
                                    <th>Concepto</th>
                                    <th class="text-end">Valor Cargo</th>
                                    <th class="text-end">Valor Aplicado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pago['detalles'] as $det): ?>
                                <tr>
                                    <td><?= esc($det['estudiante_nombres'] . ' ' . $det['estudiante_apellidos']) ?></td>
                                    <td><?= esc($det['concepto_nombre']) ?></td>
                                    <td class="text-end">$<?= number_format($det['cargo_valor'], 0, ',', '.') ?></td>
                                    <td class="text-end fw-bold">$<?= number_format($det['valor_aplicado'], 0, ',', '.') ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="3" class="text-end fw-bold">Total:</td>
                                    <td class="text-end fw-bold text-success">$<?= number_format($pago['valor_total'], 0, ',', '.') ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Comprobantes -->
        <?php if (!empty($pago['comprobantes'])): ?>
        <div class="table-card mb-4">
            <div class="card-header bg-white">
                <i class="bi bi-file-earmark-image me-2"></i>Comprobantes
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <?php foreach ($pago['comprobantes'] as $comp): ?>
                    <div class="col-md-6">
                        <div class="border rounded p-3">
                            <p class="mb-1"><strong><?= esc($comp['archivo_original']) ?></strong></p>
                            <p class="mb-2 text-muted small"><?= esc($comp['tipo_archivo']) ?> - <?= number_format($comp['tamano'] / 1024, 1) ?> KB</p>
                            <?php if (str_starts_with($comp['tipo_archivo'], 'image/')): ?>
                                <img src="<?= base_url('writable/uploads/comprobantes/' . $comp['archivo']) ?>"
                                     class="img-fluid rounded" alt="Comprobante" style="max-height: 300px;">
                            <?php else: ?>
                                <a href="<?= base_url('writable/uploads/comprobantes/' . $comp['archivo']) ?>"
                                   class="btn btn-outline-primary btn-sm" target="_blank">
                                    <i class="bi bi-download me-1"></i>Descargar
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Rejection info -->
        <?php if ($pago['estado'] === 'rechazado' && $pago['motivo_rechazo']): ?>
        <div class="alert alert-danger">
            <i class="bi bi-x-circle me-2"></i>
            <strong>Motivo de Rechazo:</strong> <?= esc($pago['motivo_rechazo']) ?>
            <?php if (!empty($pago['revisor'])): ?>
            <br><small>Revisado por: <?= esc($pago['revisor']['nombre'] . ' ' . $pago['revisor']['apellido']) ?> el <?= date('d/m/Y H:i', strtotime($pago['fecha_revision'])) ?></small>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Sidebar Actions -->
    <div class="col-lg-4">
        <!-- Acudiente Info -->
        <div class="table-card mb-4">
            <div class="card-header bg-white">
                <i class="bi bi-person me-2"></i>Acudiente
            </div>
            <div class="card-body">
                <p class="mb-2"><strong><?= esc($pago['acudiente_nombres'] . ' ' . $pago['acudiente_apellidos']) ?></strong></p>
                <p class="mb-2"><i class="bi bi-envelope me-2"></i><?= esc($pago['acudiente_email']) ?></p>
                <?php if ($pago['acudiente_telefono']): ?>
                <p class="mb-0"><i class="bi bi-telephone me-2"></i><?= esc($pago['acudiente_telefono']) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Actions -->
        <?php if ($pago['estado'] === 'pendiente_revision'): ?>
        <div class="table-card mb-4">
            <div class="card-header bg-white">
                <i class="bi bi-check2-square me-2"></i>Acciones
            </div>
            <div class="card-body">
                <form action="<?= site_url('admin/payments/' . $pago['id'] . '/approve') ?>" method="post" class="mb-3">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-success w-100 btn-lg"
                            onclick="return confirm('&iquest;Aprobar este pago? Se actualizar&aacute;n los saldos de los cargos.')">
                        <i class="bi bi-check-lg me-2"></i>Aprobar Pago
                    </button>
                </form>

                <hr>

                <form action="<?= site_url('admin/payments/' . $pago['id'] . '/reject') ?>" method="post">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label">Motivo de Rechazo</label>
                        <textarea class="form-control" name="motivo_rechazo" rows="3"
                                  placeholder="Indique el motivo del rechazo..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-outline-danger w-100"
                            onclick="return confirm('&iquest;Rechazar este pago?')">
                        <i class="bi bi-x-lg me-2"></i>Rechazar Pago
                    </button>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <!-- Review info for already reviewed payments -->
        <?php if ($pago['estado'] !== 'pendiente_revision' && !empty($pago['revisor'])): ?>
        <div class="table-card">
            <div class="card-header bg-white">
                <i class="bi bi-info-circle me-2"></i>Revisi&oacute;n
            </div>
            <div class="card-body">
                <p class="mb-2"><strong>Revisado por:</strong> <?= esc($pago['revisor']['nombre'] . ' ' . $pago['revisor']['apellido']) ?></p>
                <p class="mb-0"><strong>Fecha:</strong> <?= date('d/m/Y H:i', strtotime($pago['fecha_revision'])) ?></p>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>
