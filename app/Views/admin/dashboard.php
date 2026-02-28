<?= $this->extend('layouts/main') ?>

<?= $this->section('sidebar') ?>
<?php $activePage = 'dashboard'; ?>
<?= $this->include('partials/admin_sidebar') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Stats Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-6 col-xl-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="value"><?= number_format($stats['total_estudiantes']) ?></div>
                    <div class="label">Estudiantes Activos</div>
                </div>
                <div class="icon bg-primary">
                    <i class="bi bi-person-badge"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="value"><?= number_format($stats['total_profesores']) ?></div>
                    <div class="label">Profesores</div>
                </div>
                <div class="icon bg-info">
                    <i class="bi bi-person-workspace"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="value"><?= number_format($stats['total_grupos']) ?></div>
                    <div class="label">Grupos Activos</div>
                </div>
                <div class="icon bg-warning">
                    <i class="bi bi-collection"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="value"><?= number_format($stats['pagos_pendientes']) ?></div>
                    <div class="label">Pagos Pendientes</div>
                </div>
                <div class="icon bg-danger">
                    <i class="bi bi-exclamation-circle"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cartera Summary -->
<?php if ($stats['total_por_cobrar'] > 0): ?>
<div class="alert alert-warning d-flex align-items-center mb-4">
    <i class="bi bi-exclamation-triangle fs-4 me-3"></i>
    <div>
        <strong>Cartera Pendiente:</strong> $<?= number_format($stats['total_por_cobrar'], 0, ',', '.') ?> por cobrar
        <a href="<?= site_url('admin/cartera') ?>" class="ms-2">Ver detalle</a>
    </div>
</div>
<?php endif; ?>

<!-- Content Row -->
<div class="row g-4">
    <!-- Pagos Pendientes -->
    <div class="col-lg-6">
        <div class="table-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-cash-stack me-2"></i>Pagos Pendientes de Aprobaci&oacute;n</span>
                <a href="<?= site_url('admin/payments?estado=pendiente_revision') ?>" class="btn btn-sm btn-outline-primary">Ver todos</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($pagos_recientes)): ?>
                    <div class="p-4 text-center text-muted">
                        <i class="bi bi-check-circle fs-1 d-block mb-2"></i>
                        No hay pagos pendientes de aprobación
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Estudiante</th>
                                    <th>Monto</th>
                                    <th>Fecha</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pagos_recientes as $pago): ?>
                                <tr>
                                    <td><?= esc(($pago['acudiente_nombres'] ?? '') . ' ' . ($pago['acudiente_apellidos'] ?? '')) ?></td>
                                    <td>$<?= number_format($pago['valor_total'] ?? 0, 0, ',', '.') ?></td>
                                    <td><?= date('d/m/Y', strtotime($pago['created_at'])) ?></td>
                                    <td>
                                        <a href="<?= site_url('admin/payments/' . $pago['id']) ?>" class="btn btn-sm btn-primary">Revisar</a>
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

    <!-- Inscripciones Recientes -->
    <div class="col-lg-6">
        <div class="table-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-person-plus me-2"></i>Inscripciones Recientes</span>
                <a href="<?= site_url('admin/groups') ?>" class="btn btn-sm btn-outline-primary">Ver grupos</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($inscripciones_recientes)): ?>
                    <div class="p-4 text-center text-muted">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        No hay inscripciones recientes
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Estudiante</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($inscripciones_recientes as $inscripcion): ?>
                                <tr>
                                    <td><?= esc($inscripcion['nombres'] . ' ' . $inscripcion['apellidos']) ?></td>
                                    <td>
                                        <?php
                                        $badgeClass = match($inscripcion['estado']) {
                                            'completada' => 'success',
                                            'pendiente' => 'warning',
                                            'cancelada' => 'danger',
                                            default => 'secondary'
                                        };
                                        ?>
                                        <span class="badge bg-<?= $badgeClass ?>"><?= ucfirst($inscripcion['estado']) ?></span>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($inscripcion['created_at'])) ?></td>
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
                    <a href="<?= site_url('admin/students/new') ?>" class="btn btn-primary">
                        <i class="bi bi-person-plus me-2"></i>Nuevo Estudiante
                    </a>
                    <a href="<?= site_url('admin/payments/create') ?>" class="btn btn-success">
                        <i class="bi bi-cash-stack me-2"></i>Registrar Pago
                    </a>
                    <a href="<?= site_url('admin/groups/new') ?>" class="btn btn-info text-white">
                        <i class="bi bi-collection me-2"></i>Nuevo Grupo
                    </a>
                    <a href="<?= site_url('admin/schedules/new') ?>" class="btn btn-warning">
                        <i class="bi bi-calendar3 me-2"></i>Nuevo Horario
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
