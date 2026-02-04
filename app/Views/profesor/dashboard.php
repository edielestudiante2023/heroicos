<?= $this->extend('layouts/main') ?>

<?= $this->section('sidebar') ?>
<div class="nav-section">Principal</div>
<a href="<?= site_url('profesor/dashboard') ?>" class="nav-link active">
    <i class="bi bi-speedometer2"></i>
    Dashboard
</a>

<div class="nav-section">Mis Clases</div>
<a href="#" class="nav-link">
    <i class="bi bi-collection"></i>
    Mis Grupos
</a>
<a href="#" class="nav-link">
    <i class="bi bi-calendar3"></i>
    Mi Horario
</a>
<a href="#" class="nav-link">
    <i class="bi bi-clipboard-check"></i>
    Asistencia
</a>

<div class="nav-section">Actividades</div>
<a href="#" class="nav-link">
    <i class="bi bi-person-video3"></i>
    Clases Privadas
</a>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Stats Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-6 col-xl-4">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="value"><?= count($mis_grupos) ?></div>
                    <div class="label">Mis Grupos</div>
                </div>
                <div class="icon bg-primary">
                    <i class="bi bi-collection"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-4">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="value"><?= $total_estudiantes ?></div>
                    <div class="label">Total Estudiantes</div>
                </div>
                <div class="icon bg-info">
                    <i class="bi bi-people"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-4">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="value"><?= count($horarios_hoy) ?></div>
                    <div class="label">Clases Hoy (<?= $dia_actual ?>)</div>
                </div>
                <div class="icon bg-warning">
                    <i class="bi bi-calendar-check"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Horario de Hoy -->
<div class="row g-4">
    <div class="col-lg-6">
        <div class="table-card">
            <div class="card-header">
                <i class="bi bi-calendar-event me-2"></i>Mis Clases de Hoy - <?= $dia_actual ?>
            </div>
            <div class="card-body p-0">
                <?php if (empty($horarios_hoy)): ?>
                    <div class="p-4 text-center text-muted">
                        <i class="bi bi-calendar-x fs-1 d-block mb-2"></i>
                        No tiene clases programadas para hoy
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($horarios_hoy as $horario): ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong><?= esc($horario['grupo_nombre']) ?></strong>
                                <div class="small text-muted">
                                    <?= date('h:i A', strtotime($horario['hora_inicio'])) ?> -
                                    <?= date('h:i A', strtotime($horario['hora_fin'])) ?>
                                </div>
                            </div>
                            <a href="#" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-clipboard-check"></i> Asistencia
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Mis Grupos -->
    <div class="col-lg-6">
        <div class="table-card">
            <div class="card-header">
                <i class="bi bi-collection me-2"></i>Mis Grupos
            </div>
            <div class="card-body p-0">
                <?php if (empty($mis_grupos)): ?>
                    <div class="p-4 text-center text-muted">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        No tiene grupos asignados
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($mis_grupos as $grupo): ?>
                        <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <strong><?= esc($grupo['nombre']) ?></strong>
                                <div class="small text-muted">Categoría: <?= esc($grupo['categoria']) ?></div>
                            </div>
                            <i class="bi bi-chevron-right"></i>
                        </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
