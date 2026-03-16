<?= $this->extend('layouts/main') ?>

<?= $this->section('sidebar') ?>
<?php $activePage = 'groups'; ?>
<?= $this->include('partials/admin_sidebar') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= site_url('admin/groups') ?>">Grupos</a></li>
                <li class="breadcrumb-item active"><?= esc($grupo['nombre']) ?></li>
            </ol>
        </nav>
        <h4 class="mb-0"><?= esc($grupo['nombre']) ?></h4>
    </div>
    <div>
        <a href="<?= site_url('admin/groups/' . $grupo['id'] . '/edit') ?>" class="btn btn-primary">
            <i class="bi bi-pencil me-2"></i>Editar
        </a>
        <a href="<?= site_url('admin/groups') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Volver
        </a>
    </div>
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

<div class="row">
    <!-- Main Info -->
    <div class="col-lg-8">
        <!-- Group Info -->
        <div class="table-card mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-collection me-2"></i>Información del Grupo</h5>
                <?php
                $estadoBadge = $grupo['estado'] === 'activo' ? 'success' : 'warning';
                ?>
                <span class="badge bg-<?= $estadoBadge ?> fs-6"><?= ucfirst($grupo['estado']) ?></span>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless mb-0">
                            <tr>
                                <th class="text-muted" style="width:40%">Categoría:</th>
                                <td><span class="badge bg-secondary"><?= esc($grupo['categoria_nombre']) ?></span></td>
                            </tr>
                            <tr>
                                <th class="text-muted">Cupo:</th>
                                <td>
                                    <?php
                                    $porcentaje = $grupo['cupo_maximo'] > 0 ? ($grupo['inscritos'] / $grupo['cupo_maximo']) * 100 : 0;
                                    $colorBarra = $porcentaje >= 90 ? 'danger' : ($porcentaje >= 70 ? 'warning' : 'success');
                                    ?>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height: 10px; max-width: 150px;">
                                            <div class="progress-bar bg-<?= $colorBarra ?>" style="width: <?= $porcentaje ?>%"></div>
                                        </div>
                                        <strong><?= $grupo['inscritos'] ?>/<?= $grupo['cupo_maximo'] ?></strong>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless mb-0">
                            <tr>
                                <th class="text-muted" style="width:40%">Creado:</th>
                                <td><?= date('d/m/Y', strtotime($grupo['created_at'])) ?></td>
                            </tr>
                            <tr>
                                <th class="text-muted">Disponibles:</th>
                                <td>
                                    <?php $disponibles = $grupo['cupo_maximo'] - $grupo['inscritos']; ?>
                                    <span class="badge bg-<?= $disponibles > 0 ? 'success' : 'danger' ?>">
                                        <?= $disponibles ?> cupos
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Professors -->
        <div class="table-card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-person-workspace me-2"></i>Profesores</h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($grupo['profesores'])): ?>
                <div class="p-4 text-center text-muted">
                    <i class="bi bi-person-workspace fs-1 d-block mb-2"></i>
                    No hay profesores asignados
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Profesor</th>
                                <th>Email</th>
                                <th>Rol</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($grupo['profesores'] as $prof): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="user-avatar me-2" style="width:36px;height:36px;font-size:0.875rem;">
                                            <?= strtoupper(substr($prof['nombres'], 0, 1)) ?>
                                        </div>
                                        <strong><?= esc($prof['nombres'] . ' ' . $prof['apellidos']) ?></strong>
                                    </div>
                                </td>
                                <td><?= esc($prof['email']) ?></td>
                                <td>
                                    <?php if ($prof['es_titular']): ?>
                                    <span class="badge bg-primary">Titular</span>
                                    <?php else: ?>
                                    <span class="badge bg-secondary">Asistente</span>
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

        <!-- Schedules -->
        <div class="table-card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-calendar3 me-2"></i>Horarios</h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($grupo['horarios'])): ?>
                <div class="p-4 text-center text-muted">
                    <i class="bi bi-calendar3 fs-1 d-block mb-2"></i>
                    No hay horarios asignados
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Día</th>
                                <th>Horario</th>
                                <th>Lugar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($grupo['horarios'] as $h): ?>
                            <tr>
                                <td><strong><?= esc($h['dia_nombre']) ?></strong></td>
                                <td>
                                    <i class="bi bi-clock me-1"></i>
                                    <?= substr($h['hora_inicio'], 0, 5) ?> - <?= substr($h['hora_fin'], 0, 5) ?>
                                </td>
                                <td>
                                    <?php if ($h['lugar']): ?>
                                    <i class="bi bi-geo-alt me-1"></i><?= esc($h['lugar']) ?>
                                    <?php else: ?>
                                    <span class="text-muted">-</span>
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

        <!-- Enrolled Students -->
        <div class="table-card mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-people me-2"></i>Estudiantes Inscritos (<?= count($inscritos) ?>)</h5>
                <?php if ($grupo['inscritos'] < $grupo['cupo_maximo']): ?>
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#enrollModal">
                    <i class="bi bi-plus-lg me-1"></i>Inscribir
                </button>
                <?php endif; ?>
            </div>
            <div class="card-body p-0">
                <?php if (empty($inscritos)): ?>
                <div class="p-4 text-center text-muted">
                    <i class="bi bi-people fs-1 d-block mb-2"></i>
                    No hay estudiantes inscritos
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Código</th>
                                <th>Estudiante</th>
                                <th>Edad</th>
                                <th>Inscrito</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($inscritos as $est): ?>
                            <tr>
                                <td><code><?= esc($est['codigo']) ?></code></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <?php if (!empty($est['foto'])): ?>
                                            <img src="<?= base_url($est['foto']) ?>" alt="<?= esc($est['nombres']) ?>"
                                                 style="width:32px;height:32px;border-radius:50%;object-fit:cover;">
                                        <?php else: ?>
                                            <div class="user-avatar" style="width:32px;height:32px;font-size:0.8rem;background:<?= ($est['sexo'] ?? 'M') === 'M' ? 'var(--heroicos-primary)' : 'var(--heroicos-secondary)' ?>;color:<?= ($est['sexo'] ?? 'M') === 'M' ? 'white' : '#333' ?>;">
                                                <?= strtoupper(substr($est['nombres'], 0, 1)) ?>
                                            </div>
                                        <?php endif; ?>
                                        <a href="<?= site_url('admin/students/' . $est['estudiante_id']) ?>">
                                            <?= esc($est['nombres'] . ' ' . $est['apellidos']) ?>
                                        </a>
                                    </div>
                                </td>
                                <td><?= $est['edad'] ?> años</td>
                                <td><?= date('d/m/Y', strtotime($est['fecha_inscripcion'])) ?></td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                            onclick="confirmUnenroll(<?= $est['id'] ?>, '<?= esc($est['nombres'] . ' ' . $est['apellidos']) ?>')">
                                        <i class="bi bi-person-dash"></i>
                                    </button>
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

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Quick Stats -->
        <div class="table-card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-graph-up me-2"></i>Estadísticas</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-4">
                        <div class="fs-4 fw-bold text-primary"><?= count($inscritos) ?></div>
                        <small class="text-muted">Inscritos</small>
                    </div>
                    <div class="col-4">
                        <div class="fs-4 fw-bold text-success"><?= count($grupo['horarios']) ?></div>
                        <small class="text-muted">Horarios</small>
                    </div>
                    <div class="col-4">
                        <div class="fs-4 fw-bold text-info"><?= count($grupo['profesores']) ?></div>
                        <small class="text-muted">Profesores</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="table-card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-lightning me-2"></i>Acciones</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="<?= site_url('admin/groups/' . $grupo['id'] . '/edit') ?>" class="btn btn-outline-primary">
                        <i class="bi bi-pencil me-2"></i>Editar Grupo
                    </a>
                    <a href="#" class="btn btn-outline-success">
                        <i class="bi bi-clipboard-check me-2"></i>Tomar Asistencia
                    </a>
                    <a href="#" class="btn btn-outline-info">
                        <i class="bi bi-file-earmark-text me-2"></i>Generar Reporte
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Enroll Modal -->
<div class="modal fade" id="enrollModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Inscribir Estudiante</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= site_url('admin/groups/' . $grupo['id'] . '/enroll') ?>" method="post">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="estudiante_id" class="form-label">Seleccionar Estudiante</label>
                        <select class="form-select" id="estudiante_id" name="estudiante_id" required>
                            <option value="">Buscar estudiante...</option>
                            <!-- Will be populated via AJAX or preloaded -->
                        </select>
                        <small class="text-muted">Solo se muestran estudiantes activos no inscritos en este grupo</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-person-plus me-1"></i>Inscribir
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Unenroll Modal -->
<div class="modal fade" id="unenrollModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Retirar Estudiante</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="unenrollForm" method="post">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <p>¿Está seguro de retirar a <strong id="unenrollStudentName"></strong> del grupo?</p>
                    <div class="mb-3">
                        <label for="motivo" class="form-label">Motivo (opcional)</label>
                        <textarea class="form-control" id="motivo" name="motivo" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-person-dash me-1"></i>Retirar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
function confirmUnenroll(id, name) {
    document.getElementById('unenrollStudentName').textContent = name;
    document.getElementById('unenrollForm').action = '<?= site_url('admin/groups/' . $grupo['id'] . '/unenroll') ?>/' + id;
    new bootstrap.Modal(document.getElementById('unenrollModal')).show();
}

// Load available students for enrollment
document.getElementById('enrollModal')?.addEventListener('show.bs.modal', function() {
    const select = document.getElementById('estudiante_id');
    if (select.options.length <= 1) {
        // Fetch available students
        fetch('<?= site_url('admin/students') ?>?estado=activo&formato=json')
            .then(r => r.json())
            .then(data => {
                data.forEach(est => {
                    const opt = document.createElement('option');
                    opt.value = est.id;
                    opt.textContent = `${est.codigo} - ${est.nombres} ${est.apellidos}`;
                    select.appendChild(opt);
                });
            })
            .catch(() => {
                // Fallback: just show message
                select.innerHTML = '<option value="">Error cargando estudiantes</option>';
            });
    }
});
</script>
<?= $this->endSection() ?>
