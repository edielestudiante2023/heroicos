<?= $this->extend('layouts/main') ?>

<?= $this->section('sidebar') ?>
<?php $activePage = 'schedules'; ?>
<?= $this->include('partials/admin_sidebar') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= site_url('admin/schedules') ?>">Horarios</a></li>
                <li class="breadcrumb-item active"><?= esc($horario['nombre']) ?></li>
            </ol>
        </nav>
        <h4 class="mb-0"><?= esc($horario['nombre']) ?></h4>
    </div>
    <div>
        <a href="<?= site_url('admin/schedules/' . $horario['id'] . '/edit') ?>" class="btn btn-primary">
            <i class="bi bi-pencil me-2"></i>Editar
        </a>
        <a href="<?= site_url('admin/schedules') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Volver
        </a>
    </div>
</div>

<div class="row">
    <!-- Main Info -->
    <div class="col-lg-8">
        <!-- Schedule Info -->
        <div class="table-card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-calendar3 me-2"></i>Información del Horario</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless mb-0">
                            <tr>
                                <th class="text-muted" style="width:40%">Día:</th>
                                <td><span class="badge bg-primary fs-6"><?= esc($horario['dia_nombre']) ?></span></td>
                            </tr>
                            <tr>
                                <th class="text-muted">Horario:</th>
                                <td>
                                    <i class="bi bi-clock me-1"></i>
                                    <strong><?= substr($horario['hora_inicio'], 0, 5) ?> - <?= substr($horario['hora_fin'], 0, 5) ?></strong>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless mb-0">
                            <tr>
                                <th class="text-muted" style="width:40%">Lugar:</th>
                                <td>
                                    <?php if ($horario['lugar']): ?>
                                    <i class="bi bi-geo-alt me-1"></i><?= esc($horario['lugar']) ?>
                                    <?php else: ?>
                                    <span class="text-muted">No especificado</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th class="text-muted">Duración:</th>
                                <td>
                                    <?php
                                    $inicio = strtotime($horario['hora_inicio']);
                                    $fin = strtotime($horario['hora_fin']);
                                    $duracion = ($fin - $inicio) / 60;
                                    ?>
                                    <?= $duracion ?> minutos
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Groups using this schedule -->
        <div class="table-card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-collection me-2"></i>Grupos con este Horario (<?= count($horario['grupos']) ?>)</h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($horario['grupos'])): ?>
                <div class="p-4 text-center text-muted">
                    <i class="bi bi-collection fs-1 d-block mb-2"></i>
                    Ningún grupo tiene asignado este horario
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Grupo</th>
                                <th>Categoría</th>
                                <th>Vigencia</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($horario['grupos'] as $g): ?>
                            <tr>
                                <td><strong><?= esc($g['nombre']) ?></strong></td>
                                <td><span class="badge bg-secondary"><?= esc($g['categoria_nombre']) ?></span></td>
                                <td>
                                    <small>
                                        Desde <?= date('d/m/Y', strtotime($g['vigente_desde'])) ?>
                                        <?php if ($g['vigente_hasta']): ?>
                                        hasta <?= date('d/m/Y', strtotime($g['vigente_hasta'])) ?>
                                        <?php endif; ?>
                                    </small>
                                </td>
                                <td class="text-end">
                                    <a href="<?= site_url('admin/groups/' . $g['id']) ?>"
                                       class="btn btn-sm btn-outline-info">
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
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Stats -->
        <div class="table-card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-graph-up me-2"></i>Resumen</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="fs-3 fw-bold text-primary"><?= count($horario['grupos']) ?></div>
                        <small class="text-muted">Grupos</small>
                    </div>
                    <div class="col-6">
                        <div class="fs-3 fw-bold text-success"><?= $duracion ?></div>
                        <small class="text-muted">Minutos</small>
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
                    <a href="<?= site_url('admin/schedules/' . $horario['id'] . '/edit') ?>" class="btn btn-outline-primary">
                        <i class="bi bi-pencil me-2"></i>Editar Horario
                    </a>
                    <?php if (empty($horario['grupos'])): ?>
                    <button type="button" class="btn btn-outline-danger"
                            onclick="confirmDelete(<?= $horario['id'] ?>, '<?= esc($horario['nombre']) ?>')">
                        <i class="bi bi-trash me-2"></i>Eliminar Horario
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro de eliminar el horario <strong id="deleteScheduleName"></strong>?</p>
                <p class="text-danger mb-0">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    Esta acción no se puede deshacer.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form id="deleteForm" method="post" style="display:inline;">
                    <?= csrf_field() ?>
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-1"></i>Eliminar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
function confirmDelete(id, name) {
    document.getElementById('deleteScheduleName').textContent = name;
    document.getElementById('deleteForm').action = '<?= site_url('admin/schedules') ?>/' + id;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
<?= $this->endSection() ?>
