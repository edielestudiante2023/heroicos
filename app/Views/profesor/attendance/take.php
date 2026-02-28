<?= $this->extend('layouts/main') ?>

<?= $this->section('sidebar') ?>
<?php $activePage = 'attendance'; ?>
<?= $this->include('partials/profesor_sidebar') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= site_url('profesor/groups') ?>">Mis Grupos</a></li>
                <li class="breadcrumb-item active">Tomar Asistencia</li>
            </ol>
        </nav>
        <h4 class="mb-0">Tomar Asistencia - <?= esc($grupo['nombre']) ?></h4>
    </div>
    <a href="<?= site_url('profesor/groups/' . $grupo['id']) ?>" class="btn btn-outline-secondary">
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

<?php if (empty($estudiantes)): ?>
    <div class="table-card">
        <div class="card-body text-center text-muted py-5">
            <i class="bi bi-people fs-1 d-block mb-2"></i>
            No hay estudiantes inscritos en este grupo
        </div>
    </div>
<?php else: ?>
<form action="<?= site_url('profesor/attendance/save') ?>" method="post">
    <?= csrf_field() ?>
    <input type="hidden" name="grupo_id" value="<?= $grupo['id'] ?>">
    <input type="hidden" name="profesor_id" value="<?= $profesorId ?>">

    <div class="row g-4">
        <div class="col-lg-8">
            <!-- Student List -->
            <div class="table-card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-people me-2"></i>Estudiantes (<?= count($estudiantes) ?>)</span>
                    <div>
                        <button type="button" class="btn btn-sm btn-outline-success" id="btnTodosPresente">
                            <i class="bi bi-check-all me-1"></i>Todos Presente
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger" id="btnTodosAusente">
                            <i class="bi bi-x-lg me-1"></i>Todos Ausente
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:50px">Presente</th>
                                    <th>C&oacute;digo</th>
                                    <th>Estudiante</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($estudiantes as $est): ?>
                                <tr>
                                    <td class="text-center">
                                        <input type="hidden" name="estudiante_ids[]" value="<?= $est['estudiante_id'] ?>">
                                        <input class="form-check-input estudiante-check" type="checkbox"
                                               name="presentes[]" value="<?= $est['estudiante_id'] ?>"
                                               checked style="width: 1.5em; height: 1.5em;">
                                    </td>
                                    <td><small class="text-muted"><?= esc($est['codigo']) ?></small></td>
                                    <td><strong><?= esc($est['nombres'] . ' ' . $est['apellidos']) ?></strong></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="table-card mb-4">
                <div class="card-header bg-white">
                    <i class="bi bi-gear me-2"></i>Detalles de la Sesi&oacute;n
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Fecha</label>
                        <input type="date" class="form-control" name="fecha" value="<?= $fecha ?>">
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">Hora Inicio</label>
                            <input type="time" class="form-control" name="hora_inicio" value="<?= date('H:i') ?>">
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Hora Fin</label>
                            <input type="time" class="form-control" name="hora_fin" value="<?= date('H:i', strtotime('+1 hour')) ?>">
                        </div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Observaciones</label>
                        <textarea class="form-control" name="observaciones" rows="3" placeholder="Notas de la clase..."></textarea>
                    </div>
                </div>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-success btn-lg">
                    <i class="bi bi-check-lg me-2"></i>Guardar Asistencia
                </button>
            </div>

            <div class="text-center mt-3" id="resumen">
                <span class="text-success fw-bold" id="countPresentes"><?= count($estudiantes) ?></span> presentes /
                <span class="text-danger fw-bold" id="countAusentes">0</span> ausentes
            </div>
        </div>
    </div>
</form>
<?php endif; ?>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
const checks = document.querySelectorAll('.estudiante-check');
const countPresentes = document.getElementById('countPresentes');
const countAusentes = document.getElementById('countAusentes');

function updateCount() {
    const presentes = document.querySelectorAll('.estudiante-check:checked').length;
    const ausentes = checks.length - presentes;
    countPresentes.textContent = presentes;
    countAusentes.textContent = ausentes;
}

checks.forEach(cb => cb.addEventListener('change', updateCount));

document.getElementById('btnTodosPresente')?.addEventListener('click', () => {
    checks.forEach(cb => cb.checked = true);
    updateCount();
});

document.getElementById('btnTodosAusente')?.addEventListener('click', () => {
    checks.forEach(cb => cb.checked = false);
    updateCount();
});
</script>
<?= $this->endSection() ?>
