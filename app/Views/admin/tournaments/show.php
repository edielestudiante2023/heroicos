<?= $this->extend('layouts/main') ?>

<?= $this->section('sidebar') ?>
<?php $activePage = 'tournaments'; ?>
<?= $this->include('partials/admin_sidebar') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= site_url('admin/tournaments') ?>">Torneos</a></li>
                <li class="breadcrumb-item active"><?= esc($torneo['nombre']) ?></li>
            </ol>
        </nav>
        <h4 class="mb-0"><?= esc($torneo['nombre']) ?></h4>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= site_url('admin/tournaments/' . $torneo['id'] . '/edit') ?>" class="btn btn-outline-primary">
            <i class="bi bi-pencil me-2"></i>Editar
        </a>
        <a href="<?= site_url('admin/tournaments') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Volver
        </a>
    </div>
</div>

<?php if (session()->getFlashdata('success')): ?>
<div class="alert alert-success alert-dismissible fade show">
    <i class="bi bi-check-circle me-2"></i><?= session()->getFlashdata('success') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
<div class="alert alert-danger alert-dismissible fade show">
    <i class="bi bi-exclamation-circle me-2"></i><?= session()->getFlashdata('error') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-8">
        <!-- Tournament Info -->
        <div class="table-card mb-4">
            <div class="card-header bg-white">
                <i class="bi bi-trophy me-2"></i>Informaci&oacute;n del Torneo
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-2"><strong>Fecha:</strong> <?= date('d/m/Y', strtotime($torneo['fecha_evento'])) ?></p>
                        <?php if ($torneo['hora_evento']): ?>
                        <p class="mb-2"><strong>Hora:</strong> <?= substr($torneo['hora_evento'], 0, 5) ?></p>
                        <?php endif; ?>
                        <?php if ($torneo['lugar']): ?>
                        <p class="mb-2"><strong>Lugar:</strong> <?= esc($torneo['lugar']) ?></p>
                        <?php endif; ?>
                        <p class="mb-2"><strong>Categor&iacute;a:</strong>
                            <?= $torneo['categoria_nombre'] ? esc($torneo['categoria_nombre']) : 'Todas' ?>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-2"><strong>Cupos:</strong>
                            <?= $torneo['inscritos'] ?>/<?= $torneo['cupo_maximo'] ?>
                            (<?= $torneo['cupos_disponibles'] ?> disponibles)
                        </p>
                        <p class="mb-2"><strong>Costo:</strong> $<?= number_format($torneo['costo'], 0, ',', '.') ?></p>
                        <p class="mb-2"><strong>Estado:</strong>
                            <?php
                            $estadoBadge = match($torneo['estado']) {
                                'inscripciones_abiertas' => 'success',
                                'en_curso'               => 'warning',
                                'finalizado'             => 'secondary',
                                'cancelado'              => 'danger',
                                default                  => 'info',
                            };
                            ?>
                            <span class="badge bg-<?= $estadoBadge ?> fs-6"><?= str_replace('_', ' ', ucfirst($torneo['estado'])) ?></span>
                        </p>
                    </div>
                </div>
                <?php if ($torneo['descripcion']): ?>
                <hr>
                <p class="mb-0"><?= esc($torneo['descripcion']) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Inscriptions -->
        <div class="table-card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span><i class="bi bi-people me-2"></i>Inscritos (<?= count($torneo['inscripciones']) ?>)</span>
                <?php if (in_array($torneo['estado'], ['programado', 'inscripciones_abiertas']) && $torneo['cupos_disponibles'] > 0): ?>
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#enrollModal">
                    <i class="bi bi-person-plus me-1"></i>Inscribir Estudiante
                </button>
                <?php endif; ?>
            </div>
            <div class="card-body p-0">
                <?php if (empty($torneo['inscripciones'])): ?>
                    <div class="p-4 text-center text-muted">
                        <i class="bi bi-people fs-1 d-block mb-2"></i>
                        No hay estudiantes inscritos
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Estudiante</th>
                                    <th>Acudiente</th>
                                    <th>Fecha</th>
                                    <th>Estado</th>
                                    <th class="text-end">Acci&oacute;n</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($torneo['inscripciones'] as $i => $insc): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td>
                                        <strong><?= esc($insc['nombres'] . ' ' . $insc['apellidos']) ?></strong>
                                        <br><small class="text-muted"><?= esc($insc['estudiante_codigo']) ?></small>
                                    </td>
                                    <td><?= esc($insc['acudiente_nombres'] . ' ' . $insc['acudiente_apellidos']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($insc['fecha_inscripcion'])) ?></td>
                                    <td>
                                        <?php
                                        $insBadge = match($insc['estado']) {
                                            'pagado'         => 'success',
                                            'pendiente_pago' => 'warning',
                                            'cancelado'      => 'danger',
                                            default          => 'secondary',
                                        };
                                        ?>
                                        <span class="badge bg-<?= $insBadge ?>"><?= str_replace('_', ' ', ucfirst($insc['estado'])) ?></span>
                                    </td>
                                    <td class="text-end">
                                        <?php if ($insc['estado'] !== 'cancelado'): ?>
                                        <form action="<?= site_url('admin/tournaments/' . $torneo['id'] . '/unenroll/' . $insc['id']) ?>"
                                              method="post" class="d-inline" onsubmit="return confirm('&iquest;Cancelar esta inscripci&oacute;n?')">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Cancelar">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </form>
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
    </div>

    <div class="col-lg-4">
        <!-- Status Change -->
        <div class="table-card mb-4">
            <div class="card-header bg-white">
                <i class="bi bi-arrow-repeat me-2"></i>Cambiar Estado
            </div>
            <div class="card-body">
                <form action="<?= site_url('admin/tournaments/' . $torneo['id'] . '/status') ?>" method="post">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <select class="form-select" name="estado">
                            <option value="programado" <?= $torneo['estado'] === 'programado' ? 'selected' : '' ?>>Programado</option>
                            <option value="inscripciones_abiertas" <?= $torneo['estado'] === 'inscripciones_abiertas' ? 'selected' : '' ?>>Inscripciones Abiertas</option>
                            <option value="inscripciones_cerradas" <?= $torneo['estado'] === 'inscripciones_cerradas' ? 'selected' : '' ?>>Inscripciones Cerradas</option>
                            <option value="en_curso" <?= $torneo['estado'] === 'en_curso' ? 'selected' : '' ?>>En Curso</option>
                            <option value="finalizado" <?= $torneo['estado'] === 'finalizado' ? 'selected' : '' ?>>Finalizado</option>
                            <option value="cancelado" <?= $torneo['estado'] === 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-outline-primary w-100">
                        <i class="bi bi-arrow-repeat me-1"></i>Actualizar Estado
                    </button>
                </form>
            </div>
        </div>

        <?php if ($torneo['cupos_disponibles'] <= 0): ?>
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>Cupos Agotados</strong>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Enroll Modal -->
<?php if (in_array($torneo['estado'], ['programado', 'inscripciones_abiertas']) && $torneo['cupos_disponibles'] > 0): ?>
<div class="modal fade" id="enrollModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= site_url('admin/tournaments/' . $torneo['id'] . '/enroll') ?>" method="post">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Inscribir Estudiante</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Estudiante <span class="text-danger">*</span></label>
                        <select class="form-select" name="estudiante_id" id="enrollStudentSelect" required>
                            <option value="">Seleccionar estudiante...</option>
                            <?php foreach ($estudiantes as $est): ?>
                            <option value="<?= $est['id'] ?>" data-acudiente="<?= $est['acudiente_id'] ?>">
                                <?= esc($est['nombres'] . ' ' . $est['apellidos']) ?> (<?= esc($est['codigo']) ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <input type="hidden" name="acudiente_id" id="enrollAcudienteId" value="">
                    <?php if ($torneo['costo'] > 0): ?>
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        Se generar&aacute; un cargo de <strong>$<?= number_format($torneo['costo'], 0, ',', '.') ?></strong> al inscribir.
                    </div>
                    <?php endif; ?>
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
<?php endif; ?>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.getElementById('enrollStudentSelect')?.addEventListener('change', function() {
    const selected = this.options[this.selectedIndex];
    document.getElementById('enrollAcudienteId').value = selected.dataset.acudiente || '';
});
</script>
<?= $this->endSection() ?>
