<?= $this->extend('layouts/main') ?>

<?= $this->section('sidebar') ?>
<div class="nav-section">Principal</div>
<a href="<?= site_url('acudiente/dashboard') ?>" class="nav-link">
    <i class="bi bi-speedometer2"></i>
    Dashboard
</a>

<div class="nav-section">Mis Hijos</div>
<a href="#" class="nav-link">
    <i class="bi bi-people"></i>
    Mis Estudiantes
</a>

<div class="nav-section">Pagos</div>
<a href="#" class="nav-link">
    <i class="bi bi-wallet2"></i>
    Estado de Cuenta
</a>

<div class="nav-section">Documentos</div>
<a href="<?= site_url('acudiente/paz-y-salvo') ?>" class="nav-link active">
    <i class="bi bi-file-earmark-check"></i>
    Paz y Salvo
</a>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>
        <?= session()->getFlashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Info Card -->
<div class="stat-card mb-4">
    <div class="d-flex align-items-start gap-3">
        <div class="icon bg-primary">
            <i class="bi bi-file-earmark-check"></i>
        </div>
        <div>
            <h6 class="mb-1">Paz y Salvo</h6>
            <p class="text-muted small mb-0">
                El paz y salvo certifica que no tienes deudas pendientes con la academia para el estudiante seleccionado.
                Solo se puede solicitar cuando el saldo es $0. Al generarlo, recibiras una copia por email con el PDF adjunto.
            </p>
        </div>
    </div>
</div>

<!-- Students Table -->
<div class="table-card">
    <div class="card-header">
        <i class="bi bi-people me-2"></i>Mis Estudiantes
    </div>
    <div class="card-body p-0">
        <?php if (empty($estudiantes)): ?>
            <div class="p-4 text-center text-muted">
                <i class="bi bi-person-x fs-1 d-block mb-2"></i>
                No tienes estudiantes activos registrados
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Estudiante</th>
                            <th>Codigo</th>
                            <th>Saldo Pendiente</th>
                            <th>Estado</th>
                            <th>Accion</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($estudiantes as $est): ?>
                        <tr>
                            <td>
                                <strong><?= esc($est['nombres'] . ' ' . $est['apellidos']) ?></strong>
                            </td>
                            <td><code><?= esc($est['codigo']) ?></code></td>
                            <td>
                                <?php if ($est['saldo_pendiente'] > 0): ?>
                                    <span class="text-danger fw-bold">$<?= number_format($est['saldo_pendiente'], 0, ',', '.') ?></span>
                                <?php else: ?>
                                    <span class="text-success fw-bold">$0</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($est['saldo_pendiente'] <= 0): ?>
                                    <span class="badge bg-success">Al dia</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">$<?= number_format($est['saldo_pendiente'], 0, ',', '.') ?> pendiente</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($est['paz_y_salvo'])): ?>
                                    <a href="<?= site_url('acudiente/paz-y-salvo/descargar/' . $est['paz_y_salvo']['id']) ?>"
                                       class="btn btn-sm btn-info text-white">
                                        <i class="bi bi-download me-1"></i>Descargar PDF
                                    </a>
                                    <small class="text-muted d-block mt-1">
                                        Emitido: <?= date('d/m/Y', strtotime($est['paz_y_salvo']['fecha_generacion'])) ?>
                                    </small>
                                <?php elseif ($est['saldo_pendiente'] <= 0): ?>
                                    <form action="<?= site_url('acudiente/paz-y-salvo/solicitar') ?>" method="POST" class="d-inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="estudiante_id" value="<?= $est['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="bi bi-file-earmark-check me-1"></i>Solicitar Paz y Salvo
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-secondary" disabled>
                                        <i class="bi bi-x-circle me-1"></i>Tiene saldo pendiente
                                    </button>
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
<?= $this->endSection() ?>
