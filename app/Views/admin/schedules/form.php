<?= $this->extend('layouts/main') ?>

<?= $this->section('sidebar') ?>
<div class="nav-section">Principal</div>
<a href="<?= site_url('admin/dashboard') ?>" class="nav-link">
    <i class="bi bi-speedometer2"></i>
    Dashboard
</a>

<div class="nav-section">Gestión</div>
<a href="<?= site_url('admin/users') ?>" class="nav-link">
    <i class="bi bi-people"></i>
    Usuarios
</a>
<a href="<?= site_url('admin/students') ?>" class="nav-link">
    <i class="bi bi-person-badge"></i>
    Estudiantes
</a>
<a href="<?= site_url('admin/groups') ?>" class="nav-link">
    <i class="bi bi-collection"></i>
    Grupos
</a>
<a href="<?= site_url('admin/schedules') ?>" class="nav-link active">
    <i class="bi bi-calendar3"></i>
    Horarios
</a>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= site_url('admin/schedules') ?>">Horarios</a></li>
                <li class="breadcrumb-item active"><?= $action === 'create' ? 'Nuevo' : 'Editar' ?></li>
            </ol>
        </nav>
        <h4 class="mb-0"><?= $action === 'create' ? 'Nuevo Horario' : 'Editar Horario' ?></h4>
    </div>
    <a href="<?= site_url('admin/schedules') ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Volver
    </a>
</div>

<?php if (session()->getFlashdata('errors')): ?>
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-circle me-2"></i>
        <strong>Por favor corrija los siguientes errores:</strong>
        <ul class="mb-0 mt-2">
            <?php foreach (session()->getFlashdata('errors') as $error): ?>
                <li><?= esc($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-circle me-2"></i>
        <?= session()->getFlashdata('error') ?>
    </div>
<?php endif; ?>

<!-- Form -->
<form action="<?= $action === 'create' ? site_url('admin/schedules') : site_url('admin/schedules/' . $horario['id']) ?>"
      method="post">
    <?= csrf_field() ?>
    <?php if ($action === 'update'): ?>
        <input type="hidden" name="_method" value="PUT">
    <?php endif; ?>

    <div class="row">
        <!-- Main Form -->
        <div class="col-lg-8">
            <div class="table-card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-calendar3 me-2"></i>Información del Horario</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nombre" name="nombre"
                               value="<?= old('nombre', $horario['nombre'] ?? '') ?>"
                               placeholder="Ej: Entreno Mañana Lunes, Tarde Sábado..."
                               required>
                        <small class="text-muted">Un nombre descriptivo para identificar el horario</small>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="dia_semana" class="form-label">Día de la Semana <span class="text-danger">*</span></label>
                            <select class="form-select" id="dia_semana" name="dia_semana" required>
                                <option value="">Seleccionar...</option>
                                <?php foreach ($dias as $num => $nombre): ?>
                                <option value="<?= $num ?>"
                                        <?= old('dia_semana', $horario['dia_semana'] ?? '') == $num ? 'selected' : '' ?>>
                                    <?= $nombre ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="hora_inicio" class="form-label">Hora Inicio <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" id="hora_inicio" name="hora_inicio"
                                   value="<?= old('hora_inicio', isset($horario['hora_inicio']) ? substr($horario['hora_inicio'], 0, 5) : '') ?>"
                                   required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="hora_fin" class="form-label">Hora Fin <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" id="hora_fin" name="hora_fin"
                                   value="<?= old('hora_fin', isset($horario['hora_fin']) ? substr($horario['hora_fin'], 0, 5) : '') ?>"
                                   required>
                        </div>
                    </div>

                    <div class="mb-0">
                        <label for="lugar" class="form-label">Lugar</label>
                        <input type="text" class="form-control" id="lugar" name="lugar"
                               value="<?= old('lugar', $horario['lugar'] ?? '') ?>"
                               placeholder="Ej: Cancha Principal, Campo Sintético Norte...">
                        <small class="text-muted">Opcional. Ubicación donde se realiza el entrenamiento</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <?php if ($action === 'update'): ?>
            <div class="table-card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Información</h5>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>Creado:</strong> <?= isset($horario['created_at']) ? date('d/m/Y H:i', strtotime($horario['created_at'])) : 'N/A' ?></p>
                    <?php if (isset($horario['updated_at']) && $horario['updated_at']): ?>
                    <p class="mb-0"><strong>Modificado:</strong> <?= date('d/m/Y H:i', strtotime($horario['updated_at'])) ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="table-card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-lightbulb me-2"></i>Consejos</h5>
                </div>
                <div class="card-body">
                    <ul class="mb-0 ps-3">
                        <li class="mb-2">Use nombres descriptivos como "Lunes Mañana Sub-10"</li>
                        <li class="mb-2">Evite sobrelapamientos de horario en el mismo lugar</li>
                        <li>Un horario puede ser asignado a múltiples grupos</li>
                    </ul>
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi bi-check-lg me-2"></i>
                    <?= $action === 'create' ? 'Crear Horario' : 'Guardar Cambios' ?>
                </button>
                <a href="<?= site_url('admin/schedules') ?>" class="btn btn-outline-secondary">
                    Cancelar
                </a>
            </div>
        </div>
    </div>
</form>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
// Validate time range
document.querySelector('form').addEventListener('submit', function(e) {
    const inicio = document.getElementById('hora_inicio').value;
    const fin = document.getElementById('hora_fin').value;

    if (inicio && fin && inicio >= fin) {
        e.preventDefault();
        alert('La hora de fin debe ser posterior a la hora de inicio');
    }
});
</script>
<?= $this->endSection() ?>
