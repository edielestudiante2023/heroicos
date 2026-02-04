<?= $this->extend('layouts/main') ?>

<?= $this->section('sidebar') ?>
<div class="nav-section">Principal</div>
<a href="<?= site_url('admin/dashboard') ?>" class="nav-link active">
    <i class="bi bi-speedometer2"></i>
    Dashboard
</a>

<div class="nav-section">Gestión</div>
<a href="<?= site_url('admin/users') ?>" class="nav-link">
    <i class="bi bi-people"></i>
    Usuarios
</a>
<a href="#" class="nav-link">
    <i class="bi bi-person-badge"></i>
    Estudiantes
</a>
<a href="#" class="nav-link">
    <i class="bi bi-collection"></i>
    Grupos
</a>
<a href="#" class="nav-link">
    <i class="bi bi-calendar3"></i>
    Horarios
</a>

<div class="nav-section">Finanzas</div>
<a href="#" class="nav-link">
    <i class="bi bi-cash-stack"></i>
    Pagos
</a>
<a href="#" class="nav-link">
    <i class="bi bi-file-earmark-text"></i>
    Conceptos
</a>
<a href="#" class="nav-link">
    <i class="bi bi-clipboard-check"></i>
    Cartera
</a>

<div class="nav-section">Actividades</div>
<a href="#" class="nav-link">
    <i class="bi bi-trophy"></i>
    Torneos
</a>
<a href="#" class="nav-link">
    <i class="bi bi-person-video3"></i>
    Clases Privadas
</a>

<div class="nav-section">Reportes</div>
<a href="#" class="nav-link">
    <i class="bi bi-graph-up"></i>
    Estadísticas
</a>
<a href="#" class="nav-link">
    <i class="bi bi-file-earmark-pdf"></i>
    Paz y Salvo
</a>
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

<!-- Content Row -->
<div class="row g-4">
    <!-- Pagos Pendientes -->
    <div class="col-lg-6">
        <div class="table-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-cash-stack me-2"></i>Pagos Pendientes de Aprobación</span>
                <a href="#" class="btn btn-sm btn-outline-primary">Ver todos</a>
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
                                    <td><?= esc($pago['estudiante_nombre'] . ' ' . $pago['estudiante_apellido']) ?></td>
                                    <td>$<?= number_format($pago['monto'], 0, ',', '.') ?></td>
                                    <td><?= date('d/m/Y', strtotime($pago['created_at'])) ?></td>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-primary">Revisar</a>
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
                <a href="#" class="btn btn-sm btn-outline-primary">Ver todos</a>
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
                                    <td><?= esc($inscripcion['nombre'] . ' ' . $inscripcion['apellido']) ?></td>
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
                    <a href="#" class="btn btn-primary">
                        <i class="bi bi-person-plus me-2"></i>Nuevo Estudiante
                    </a>
                    <a href="#" class="btn btn-success">
                        <i class="bi bi-cash-stack me-2"></i>Registrar Pago
                    </a>
                    <a href="#" class="btn btn-info text-white">
                        <i class="bi bi-collection me-2"></i>Nuevo Grupo
                    </a>
                    <a href="#" class="btn btn-warning">
                        <i class="bi bi-trophy me-2"></i>Crear Torneo
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
