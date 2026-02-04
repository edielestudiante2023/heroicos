<?= $this->extend('layouts/main') ?>

<?= $this->section('sidebar') ?>
<div class="nav-section">Principal</div>
<a href="<?= site_url('acudiente/dashboard') ?>" class="nav-link active">
    <i class="bi bi-speedometer2"></i>
    Dashboard
</a>

<div class="nav-section">Mis Hijos</div>
<a href="#" class="nav-link">
    <i class="bi bi-people"></i>
    Mis Estudiantes
</a>
<a href="#" class="nav-link">
    <i class="bi bi-calendar3"></i>
    Horarios
</a>

<div class="nav-section">Pagos</div>
<a href="#" class="nav-link">
    <i class="bi bi-wallet2"></i>
    Estado de Cuenta
</a>
<a href="#" class="nav-link">
    <i class="bi bi-cash-stack"></i>
    Registrar Pago
</a>
<a href="#" class="nav-link">
    <i class="bi bi-file-earmark-text"></i>
    Historial
</a>

<div class="nav-section">Actividades</div>
<a href="#" class="nav-link">
    <i class="bi bi-trophy"></i>
    Torneos
</a>
<a href="#" class="nav-link">
    <i class="bi bi-person-video3"></i>
    Solicitar Clase Privada
</a>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Alert for pending balance -->
<?php if ($saldo_total > 0): ?>
<div class="alert alert-warning d-flex align-items-center" role="alert">
    <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
    <div>
        <strong>Saldo pendiente:</strong> $<?= number_format($saldo_total, 0, ',', '.') ?>
        <a href="#" class="alert-link ms-2">Ver detalles</a>
    </div>
</div>
<?php endif; ?>

<!-- Stats Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-6 col-xl-4">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="value"><?= count($mis_estudiantes) ?></div>
                    <div class="label">Mis Estudiantes</div>
                </div>
                <div class="icon bg-primary">
                    <i class="bi bi-people"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-4">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="value">$<?= number_format($saldo_total, 0, ',', '.') ?></div>
                    <div class="label">Saldo Pendiente</div>
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
                    <div class="value"><?= count($pagos_pendientes) ?></div>
                    <div class="label">Conceptos Pendientes</div>
                </div>
                <div class="icon bg-warning">
                    <i class="bi bi-file-earmark-text"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Content Row -->
<div class="row g-4">
    <!-- Mis Estudiantes -->
    <div class="col-lg-6">
        <div class="table-card">
            <div class="card-header">
                <i class="bi bi-people me-2"></i>Mis Estudiantes
            </div>
            <div class="card-body p-0">
                <?php if (empty($mis_estudiantes)): ?>
                    <div class="p-4 text-center text-muted">
                        <i class="bi bi-person-plus fs-1 d-block mb-2"></i>
                        No tiene estudiantes registrados
                        <div class="mt-3">
                            <a href="#" class="btn btn-primary btn-sm">Inscribir Estudiante</a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($mis_estudiantes as $estudiante): ?>
                        <a href="#" class="list-group-item list-group-item-action">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong><?= esc($estudiante['nombre'] . ' ' . $estudiante['apellido']) ?></strong>
                                    <div class="small text-muted">
                                        Categoría: <?= esc($estudiante['categoria'] ?? 'Sin asignar') ?>
                                    </div>
                                </div>
                                <i class="bi bi-chevron-right"></i>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Pagos Recientes -->
    <div class="col-lg-6">
        <div class="table-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-clock-history me-2"></i>Pagos Recientes</span>
                <a href="#" class="btn btn-sm btn-outline-primary">Ver historial</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($pagos_recientes)): ?>
                    <div class="p-4 text-center text-muted">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        No hay pagos registrados
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Estudiante</th>
                                    <th>Monto</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pagos_recientes as $pago): ?>
                                <tr>
                                    <td>
                                        <?= esc($pago['nombre'] . ' ' . $pago['apellido']) ?>
                                        <div class="small text-muted"><?= date('d/m/Y', strtotime($pago['created_at'])) ?></div>
                                    </td>
                                    <td>$<?= number_format($pago['monto'], 0, ',', '.') ?></td>
                                    <td>
                                        <?php
                                        $badgeClass = match($pago['estado']) {
                                            'aprobado' => 'success',
                                            'pendiente' => 'warning',
                                            'rechazado' => 'danger',
                                            default => 'secondary'
                                        };
                                        ?>
                                        <span class="badge bg-<?= $badgeClass ?>"><?= ucfirst($pago['estado']) ?></span>
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

<!-- Quick Actions -->
<div class="row mt-4">
    <div class="col-12">
        <div class="table-card">
            <div class="card-header">
                <i class="bi bi-lightning me-2"></i>Acciones Rápidas
            </div>
            <div class="card-body">
                <div class="d-flex flex-wrap gap-2">
                    <a href="#" class="btn btn-primary">
                        <i class="bi bi-cash-stack me-2"></i>Registrar Pago
                    </a>
                    <a href="#" class="btn btn-success">
                        <i class="bi bi-file-earmark-text me-2"></i>Ver Estado de Cuenta
                    </a>
                    <a href="#" class="btn btn-info text-white">
                        <i class="bi bi-calendar3 me-2"></i>Ver Horarios
                    </a>
                    <a href="#" class="btn btn-warning">
                        <i class="bi bi-person-video3 me-2"></i>Solicitar Clase Privada
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
