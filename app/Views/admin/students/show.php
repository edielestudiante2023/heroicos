<?= $this->extend('layouts/main') ?>

<?= $this->section('sidebar') ?>
<?php $activePage = 'students'; ?>
<?= $this->include('partials/admin_sidebar') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= site_url('admin/students') ?>">Estudiantes</a></li>
                <li class="breadcrumb-item active"><?= esc($estudiante['nombres']) ?></li>
            </ol>
        </nav>
        <h4 class="mb-0"><?= esc($estudiante['nombres'] . ' ' . $estudiante['apellidos']) ?></h4>
    </div>
    <div>
        <a href="<?= site_url('admin/students/' . $estudiante['id'] . '/edit') ?>" class="btn btn-primary">
            <i class="bi bi-pencil me-2"></i>Editar
        </a>
        <a href="<?= site_url('admin/students') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Volver
        </a>
    </div>
</div>

<div class="row">
    <!-- Main Info -->
    <div class="col-lg-8">
        <!-- Personal Info -->
        <div class="table-card mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-person me-2"></i>Información Personal</h5>
                <?php
                $estadoBadge = match($estudiante['estado']) {
                    'activo'   => 'success',
                    'inactivo' => 'warning',
                    'retirado' => 'danger',
                    default    => 'secondary'
                };
                ?>
                <span class="badge bg-<?= $estadoBadge ?> fs-6"><?= ucfirst($estudiante['estado']) ?></span>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless mb-0">
                            <tr>
                                <th class="text-muted" style="width:40%">Código:</th>
                                <td><code><?= esc($estudiante['codigo']) ?></code></td>
                            </tr>
                            <tr>
                                <th class="text-muted">Documento:</th>
                                <td><?= esc($estudiante['tipo_documento']) ?>: <?= esc($estudiante['numero_documento']) ?></td>
                            </tr>
                            <tr>
                                <th class="text-muted">Fecha Nacimiento:</th>
                                <td><?= date('d/m/Y', strtotime($estudiante['fecha_nacimiento'])) ?></td>
                            </tr>
                            <tr>
                                <th class="text-muted">Edad:</th>
                                <td><?= $estudiante['edad'] ?> años</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Sexo:</th>
                                <td><?= $estudiante['sexo'] === 'M' ? 'Masculino' : 'Femenino' ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless mb-0">
                            <tr>
                                <th class="text-muted" style="width:40%">Categoría:</th>
                                <td><span class="badge bg-secondary"><?= date('Y', strtotime($estudiante['fecha_nacimiento'])) ?></span></td>
                            </tr>
                            <tr>
                                <th class="text-muted">Teléfono:</th>
                                <td><?= esc($estudiante['telefono']) ?: '-' ?></td>
                            </tr>
                            <tr>
                                <th class="text-muted">Dirección:</th>
                                <td><?= esc($estudiante['direccion']) ?: '-' ?></td>
                            </tr>
                            <tr>
                                <th class="text-muted">Fecha Ingreso:</th>
                                <td><?= $estudiante['fecha_ingreso'] ? date('d/m/Y', strtotime($estudiante['fecha_ingreso'])) : '-' ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Medical Info -->
        <div class="table-card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-heart-pulse me-2"></i>Información Médica</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>EPS:</strong> <?= esc($estudiante['eps']) ?: 'No registrada' ?></p>
                        <p><strong>Grupo Sanguíneo:</strong> <?= esc($estudiante['grupo_sanguineo']) ?: 'No registrado' ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Contacto Emergencia:</strong> <?= esc($estudiante['contacto_emergencia']) ?: '-' ?></p>
                        <p><strong>Teléfono Emergencia:</strong> <?= esc($estudiante['telefono_emergencia']) ?: '-' ?></p>
                    </div>
                </div>

                <?php if ($estudiante['alergias']): ?>
                <div class="alert alert-warning mb-2">
                    <strong><i class="bi bi-exclamation-triangle me-1"></i>Alergias:</strong> <?= esc($estudiante['alergias']) ?>
                </div>
                <?php endif; ?>

                <?php if ($estudiante['condiciones_medicas']): ?>
                <div class="alert alert-info mb-2">
                    <strong><i class="bi bi-info-circle me-1"></i>Condiciones Médicas:</strong> <?= esc($estudiante['condiciones_medicas']) ?>
                </div>
                <?php endif; ?>

                <?php if ($estudiante['medicamentos']): ?>
                <div class="alert alert-secondary mb-0">
                    <strong><i class="bi bi-capsule me-1"></i>Medicamentos:</strong> <?= esc($estudiante['medicamentos']) ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Groups -->
        <div class="table-card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-collection me-2"></i>Grupos Inscritos</h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($grupos)): ?>
                <div class="p-4 text-center text-muted">
                    <i class="bi bi-collection fs-1 d-block mb-2"></i>
                    No está inscrito en ningún grupo
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Grupo</th>
                                <th>Categoría</th>
                                <th>Estado Inscripción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($grupos as $grupo): ?>
                            <tr>
                                <td><?= esc($grupo['nombre']) ?></td>
                                <td><?= esc($grupo['categoria']) ?></td>
                                <td>
                                    <?php
                                    $inscBadge = match($grupo['inscripcion_estado']) {
                                        'completada' => 'success',
                                        'pendiente'  => 'warning',
                                        'cancelada'  => 'danger',
                                        default      => 'secondary'
                                    };
                                    ?>
                                    <span class="badge bg-<?= $inscBadge ?>"><?= ucfirst($grupo['inscripcion_estado']) ?></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <!-- Sports History -->
        <div class="table-card mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-trophy me-2"></i>Historial Deportivo</h5>
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalHistorial">
                    <i class="bi bi-plus-circle me-1"></i>Agregar
                </button>
            </div>
            <div class="card-body p-0">
                <?php if (empty($historial)): ?>
                <div class="p-4 text-center text-muted">
                    <i class="bi bi-trophy fs-1 d-block mb-2"></i>
                    No hay registros de historial deportivo
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Academia Anterior</th>
                                <th>Periodo</th>
                                <th>Posicion</th>
                                <th>Torneos</th>
                                <th>Logros</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($historial as $h): ?>
                            <tr>
                                <td><?= esc($h['academia_anterior'] ?: '-') ?></td>
                                <td><?= esc($h['periodo'] ?: '-') ?></td>
                                <td><?= esc($h['posicion_juego'] ?: '-') ?></td>
                                <td class="small"><?= esc($h['torneos_participados'] ?: '-') ?></td>
                                <td class="small"><?= esc($h['logros'] ?: '-') ?></td>
                                <td>
                                    <form action="<?= site_url('admin/students/' . $estudiante['id'] . '/historial/' . $h['id'] . '/eliminar') ?>" method="post" class="d-inline">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('¿Eliminar este registro?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php if ($h['observaciones']): ?>
                            <tr>
                                <td colspan="6" class="small text-muted border-0 pt-0"><i class="bi bi-chat-quote me-1"></i><?= esc($h['observaciones']) ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Modal Agregar Historial -->
        <div class="modal fade" id="modalHistorial" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="<?= site_url('admin/students/' . $estudiante['id'] . '/historial') ?>" method="post">
                        <?= csrf_field() ?>
                        <div class="modal-header">
                            <h6 class="modal-title"><i class="bi bi-trophy me-2"></i>Agregar Historial Deportivo</h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small">Academia anterior</label>
                                    <input type="text" name="academia_anterior" class="form-control form-control-sm" placeholder="Ej: Club Deportivo XYZ">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small">Periodo</label>
                                    <input type="text" name="periodo" class="form-control form-control-sm" placeholder="Ej: 2023-2024">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small">Posicion de juego</label>
                                    <select name="posicion_juego" class="form-select form-select-sm">
                                        <option value="">Seleccionar...</option>
                                        <option value="Portero">Portero</option>
                                        <option value="Defensa Central">Defensa Central</option>
                                        <option value="Lateral Derecho">Lateral Derecho</option>
                                        <option value="Lateral Izquierdo">Lateral Izquierdo</option>
                                        <option value="Mediocampista Central">Mediocampista Central</option>
                                        <option value="Mediocampista Ofensivo">Mediocampista Ofensivo</option>
                                        <option value="Mediocampista Defensivo">Mediocampista Defensivo</option>
                                        <option value="Extremo Derecho">Extremo Derecho</option>
                                        <option value="Extremo Izquierdo">Extremo Izquierdo</option>
                                        <option value="Delantero Centro">Delantero Centro</option>
                                        <option value="Segundo Delantero">Segundo Delantero</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small">Torneos participados</label>
                                    <textarea name="torneos_participados" class="form-control form-control-sm" rows="2" placeholder="Ej: Copa Navidad 2023, Torneo Interligas 2024..."></textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small">Logros</label>
                                    <textarea name="logros" class="form-control form-control-sm" rows="2" placeholder="Ej: Campeon Sub-12, Mejor jugador..."></textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small">Observaciones</label>
                                    <textarea name="observaciones" class="form-control form-control-sm" rows="2" placeholder="Notas adicionales..."></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-save me-1"></i>Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Foto del estudiante -->
        <div class="table-card mb-4 text-center">
            <div class="card-body py-4">
                <?php if (!empty($estudiante['foto'])): ?>
                    <img src="<?= base_url($estudiante['foto']) ?>" alt="<?= esc($estudiante['nombres']) ?>"
                         class="rounded-circle mb-3"
                         style="width:120px;height:120px;object-fit:cover;border:4px solid var(--heroicos-primary);">
                <?php else: ?>
                    <div class="user-avatar mx-auto mb-3"
                         style="width:120px;height:120px;font-size:3rem;background:<?= $estudiante['sexo'] === 'M' ? 'var(--heroicos-primary)' : 'var(--heroicos-secondary)' ?>;color:<?= $estudiante['sexo'] === 'M' ? 'white' : '#333' ?>">
                        <?= strtoupper(substr($estudiante['nombres'], 0, 1)) ?>
                    </div>
                <?php endif; ?>
                <h5 class="mb-1 fw-bold"><?= esc($estudiante['nombres'] . ' ' . $estudiante['apellidos']) ?></h5>
                <div class="text-muted small mb-2"><code><?= esc($estudiante['codigo']) ?></code></div>
                <a href="<?= site_url('admin/students/' . $estudiante['id'] . '/edit') ?>" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-camera me-1"></i>Cambiar foto
                </a>
            </div>
        </div>

        <!-- Acudiente -->
        <div class="table-card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-person-heart me-2"></i>Acudiente</h5>
            </div>
            <div class="card-body">
                <?php if (isset($estudiante['acudiente'])): ?>
                <div class="d-flex align-items-center mb-3">
                    <div class="user-avatar me-3" style="width:48px;height:48px;">
                        <?= strtoupper(substr($estudiante['acudiente']['nombres'], 0, 1)) ?>
                    </div>
                    <div>
                        <strong><?= esc($estudiante['acudiente']['nombres'] . ' ' . $estudiante['acudiente']['apellidos']) ?></strong>
                        <div class="small text-muted"><?= esc($estudiante['acudiente']['email']) ?></div>
                    </div>
                </div>
                <?php if ($estudiante['acudiente']['telefono']): ?>
                <p class="mb-1"><i class="bi bi-telephone me-2"></i><?= esc($estudiante['acudiente']['telefono']) ?></p>
                <?php endif; ?>
                <?php else: ?>
                <p class="text-muted mb-0">Sin acudiente asignado</p>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($estudiante['estado'] === 'retirado'): ?>
        <!-- Retirement Info -->
        <div class="table-card mb-4 border-danger">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i class="bi bi-person-x me-2"></i>Información de Retiro</h5>
            </div>
            <div class="card-body">
                <p><strong>Fecha Retiro:</strong> <?= $estudiante['fecha_retiro'] ? date('d/m/Y', strtotime($estudiante['fecha_retiro'])) : '-' ?></p>
                <p class="mb-0"><strong>Motivo:</strong> <?= esc($estudiante['motivo_retiro']) ?: 'No especificado' ?></p>
            </div>
        </div>
        <?php endif; ?>

        <!-- Quick Actions -->
        <div class="table-card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-lightning me-2"></i>Acciones</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="<?= site_url('admin/students/' . $estudiante['id'] . '/edit') ?>" class="btn btn-outline-primary">
                        <i class="bi bi-pencil me-2"></i>Editar Información
                    </a>
                    <a href="#" class="btn btn-outline-success">
                        <i class="bi bi-collection me-2"></i>Inscribir a Grupo
                    </a>
                    <a href="#" class="btn btn-outline-info">
                        <i class="bi bi-cash-stack me-2"></i>Ver Estado de Cuenta
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
