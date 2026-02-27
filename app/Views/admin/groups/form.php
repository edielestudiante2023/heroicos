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
<a href="<?= site_url('admin/groups') ?>" class="nav-link active">
    <i class="bi bi-collection"></i>
    Grupos
</a>
<a href="<?= site_url('admin/schedules') ?>" class="nav-link">
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
                <li class="breadcrumb-item"><a href="<?= site_url('admin/groups') ?>">Grupos</a></li>
                <li class="breadcrumb-item active"><?= $action === 'create' ? 'Nuevo' : 'Editar' ?></li>
            </ol>
        </nav>
        <h4 class="mb-0"><?= $action === 'create' ? 'Nuevo Grupo' : 'Editar Grupo' ?></h4>
    </div>
    <a href="<?= site_url('admin/groups') ?>" class="btn btn-outline-secondary">
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
<form action="<?= $action === 'create' ? site_url('admin/groups') : site_url('admin/groups/' . $grupo['id']) ?>"
      method="post">
    <?= csrf_field() ?>
    <?php if ($action === 'update'): ?>
        <input type="hidden" name="_method" value="PUT">
    <?php endif; ?>

    <div class="row">
        <!-- Left Column - Main Info -->
        <div class="col-lg-8">
            <div class="table-card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-collection me-2"></i>Información del Grupo</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="nombre" class="form-label">Nombre del Grupo <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nombre" name="nombre"
                                   value="<?= old('nombre', $grupo['nombre'] ?? '') ?>"
                                   placeholder="Ej: Sub-10 A, Porteros Avanzados..."
                                   required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="cupo_maximo" class="form-label">Cupo Máximo <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="cupo_maximo" name="cupo_maximo"
                                   value="<?= old('cupo_maximo', $grupo['cupo_maximo'] ?? 20) ?>"
                                   min="1" max="100" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="categoria_id" class="form-label">Categoría <span class="text-danger">*</span></label>
                        <select class="form-select" id="categoria_id" name="categoria_id" required>
                            <option value="">Seleccionar categoría...</option>
                            <?php foreach ($categorias as $cat): ?>
                            <option value="<?= $cat['id'] ?>"
                                    <?= old('categoria_id', $grupo['categoria_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                                <?= esc($cat['nombre']) ?>
                                <?php if (!empty($cat['descripcion'])): ?>
                                    - <?= esc($cat['descripcion']) ?>
                                <?php endif; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Professors -->
            <div class="table-card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-person-workspace me-2"></i>Profesores Asignados</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($profesores)): ?>
                        <p class="text-muted mb-0">No hay profesores disponibles. <a href="<?= site_url('admin/users/new') ?>">Crear profesor</a></p>
                    <?php else: ?>
                        <div class="mb-3">
                            <label class="form-label">Seleccionar Profesores</label>
                            <div class="row">
                                <?php
                                $profesoresAsignados = $profesores_asignados ?? [];
                                foreach ($profesores as $prof):
                                ?>
                                <div class="col-md-6 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input profesor-check" type="checkbox"
                                               name="profesores[]" value="<?= $prof['id'] ?>"
                                               id="prof_<?= $prof['id'] ?>"
                                               <?= in_array($prof['id'], $profesoresAsignados) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="prof_<?= $prof['id'] ?>">
                                            <?= esc($prof['nombres'] . ' ' . $prof['apellidos']) ?>
                                            <small class="text-muted">(<?= esc($prof['email']) ?>)</small>
                                        </label>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="mb-0" id="titularDiv" style="display:none;">
                            <label for="profesor_titular" class="form-label">Profesor Titular</label>
                            <select class="form-select" id="profesor_titular" name="profesor_titular">
                                <option value="">Ninguno</option>
                            </select>
                            <small class="text-muted">El profesor titular es responsable principal del grupo</small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Schedules -->
            <div class="table-card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-calendar3 me-2"></i>Horarios</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($horarios)): ?>
                        <p class="text-muted mb-0">No hay horarios disponibles. <a href="<?= site_url('admin/schedules/new') ?>">Crear horario</a></p>
                    <?php else: ?>
                        <label class="form-label">Seleccionar Horarios</label>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:40px;"></th>
                                        <th>Nombre</th>
                                        <th>Día</th>
                                        <th>Horario</th>
                                        <th>Lugar</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $horariosAsignados = $horarios_asignados ?? [];
                                    $dias = ['', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
                                    foreach ($horarios as $h):
                                    ?>
                                    <tr>
                                        <td>
                                            <input class="form-check-input" type="checkbox"
                                                   name="horarios[]" value="<?= $h['id'] ?>"
                                                   <?= in_array($h['id'], $horariosAsignados) ? 'checked' : '' ?>>
                                        </td>
                                        <td><?= esc($h['nombre']) ?></td>
                                        <td><?= $dias[$h['dia_semana']] ?? '' ?></td>
                                        <td>
                                            <?= substr($h['hora_inicio'], 0, 5) ?> - <?= substr($h['hora_fin'], 0, 5) ?>
                                        </td>
                                        <td><?= esc($h['lugar']) ?: '-' ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Right Column - Status & Actions -->
        <div class="col-lg-4">
            <?php if ($action === 'update'): ?>
            <div class="table-card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Información</h5>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>Inscritos:</strong> <?= $grupo['inscritos'] ?? 0 ?> / <?= $grupo['cupo_maximo'] ?></p>
                    <p class="mb-0"><strong>Creado:</strong> <?= isset($grupo['created_at']) ? date('d/m/Y H:i', strtotime($grupo['created_at'])) : 'N/A' ?></p>
                </div>
            </div>
            <?php endif; ?>

            <div class="table-card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-gear me-2"></i>Estado</h5>
                </div>
                <div class="card-body">
                    <?php if ($action === 'update'): ?>
                    <div class="mb-0">
                        <label for="estado" class="form-label">Estado del Grupo</label>
                        <select class="form-select" id="estado" name="estado">
                            <option value="activo" <?= old('estado', $grupo['estado'] ?? 'activo') === 'activo' ? 'selected' : '' ?>>Activo</option>
                            <option value="inactivo" <?= old('estado', $grupo['estado'] ?? '') === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                        </select>
                    </div>
                    <?php else: ?>
                    <p class="text-muted mb-0">El grupo se creará con estado <span class="badge bg-success">Activo</span></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi bi-check-lg me-2"></i>
                    <?= $action === 'create' ? 'Crear Grupo' : 'Guardar Cambios' ?>
                </button>
                <a href="<?= site_url('admin/groups') ?>" class="btn btn-outline-secondary">
                    Cancelar
                </a>
            </div>
        </div>
    </div>
</form>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
// Update titular dropdown when professors are selected
const profesorChecks = document.querySelectorAll('.profesor-check');
const titularDiv = document.getElementById('titularDiv');
const titularSelect = document.getElementById('profesor_titular');

function updateTitularOptions() {
    const checked = document.querySelectorAll('.profesor-check:checked');
    titularSelect.innerHTML = '<option value="">Ninguno</option>';

    if (checked.length > 0) {
        titularDiv.style.display = 'block';
        checked.forEach(cb => {
            const label = document.querySelector(`label[for="${cb.id}"]`).textContent.split('(')[0].trim();
            const opt = document.createElement('option');
            opt.value = cb.value;
            opt.textContent = label;
            titularSelect.appendChild(opt);
        });
    } else {
        titularDiv.style.display = 'none';
    }
}

profesorChecks.forEach(cb => cb.addEventListener('change', updateTitularOptions));
updateTitularOptions();

<?php if ($action === 'update' && !empty($grupo['profesores'])): ?>
// Pre-select titular
<?php
$titular = null;
foreach ($grupo['profesores'] as $p) {
    if ($p['es_titular']) {
        $titular = $p['id'];
        break;
    }
}
if ($titular):
?>
setTimeout(() => {
    titularSelect.value = '<?= $titular ?>';
}, 100);
<?php endif; ?>
<?php endif; ?>
</script>
<?= $this->endSection() ?>
