<?= $this->extend('layouts/main') ?>

<?= $this->section('sidebar') ?>
<?= $this->include('acudiente/partials/sidebar') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

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

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0">Mis Solicitudes de Clases Particulares</h5>
    <a href="<?= site_url('acudiente/clases-particulares/solicitar') ?>" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-circle me-1"></i>Nueva Solicitud
    </a>
</div>

<?php if (empty($solicitudes)): ?>
    <div class="table-card">
        <div class="card-body text-center p-5">
            <i class="bi bi-person-video3 fs-1 d-block mb-3 text-muted"></i>
            <h6 class="text-muted">No tienes solicitudes de clases particulares</h6>
            <p class="text-muted mb-3">Solicita una clase personalizada con uno de nuestros profesores.</p>
            <a href="<?= site_url('acudiente/clases-particulares/solicitar') ?>" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle me-1"></i>Solicitar Clase
            </a>
        </div>
    </div>
<?php else: ?>
    <div class="table-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Estudiante</th>
                            <th>Profesor</th>
                            <th>Fecha / Hora</th>
                            <th>Duracion</th>
                            <th>Estado</th>
                            <th>Precio</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($solicitudes as $s): ?>
                        <tr>
                            <td><?= esc($s['estudiante_nombres'] . ' ' . $s['estudiante_apellidos']) ?></td>
                            <td><?= esc(($s['profesor_nombres'] ?? '') . ' ' . ($s['profesor_apellidos'] ?? '')) ?></td>
                            <td>
                                <?= $s['fecha_preferida'] ? date('d/m/Y', strtotime($s['fecha_preferida'])) : '-' ?>
                                <div class="small text-muted"><?= $s['hora_preferida'] ? date('h:i A', strtotime($s['hora_preferida'])) : '' ?></div>
                            </td>
                            <td><?= $s['duracion_minutos'] ?> min</td>
                            <td>
                                <?php
                                $badge = match($s['estado']) {
                                    'pendiente' => 'warning',
                                    'aprobada'  => 'info',
                                    'rechazada' => 'danger',
                                    'agendada'  => 'success',
                                    default     => 'secondary',
                                };
                                $label = match($s['estado']) {
                                    'pendiente' => 'Pendiente',
                                    'aprobada'  => 'Precio propuesto',
                                    'rechazada' => 'Rechazada',
                                    'agendada'  => 'Confirmada',
                                    default     => ucfirst($s['estado']),
                                };
                                ?>
                                <span class="badge bg-<?= $badge ?>"><?= $label ?></span>
                                <?php if ($s['estado'] === 'rechazada' && !empty($s['motivo_rechazo'])): ?>
                                    <div class="small text-danger mt-1"><?= esc($s['motivo_rechazo']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($s['precio_propuesto']): ?>
                                    <strong>$<?= number_format($s['precio_propuesto'], 0, ',', '.') ?></strong>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($s['estado'] === 'aprobada' && $s['precio_propuesto']): ?>
                                    <div class="d-flex gap-1">
                                        <form action="<?= site_url('acudiente/clases-particulares/aceptar-precio/' . $s['id']) ?>" method="post" class="d-inline">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('¿Aceptar el precio de $<?= number_format($s['precio_propuesto'], 0, ',', '.') ?>? Se generara un cargo en tu cuenta.')">
                                                <i class="bi bi-check-lg"></i> Aceptar
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#rechazarModal<?= $s['id'] ?>">
                                            <i class="bi bi-x-lg"></i> Rechazar
                                        </button>
                                    </div>

                                    <!-- Modal rechazar precio -->
                                    <div class="modal fade" id="rechazarModal<?= $s['id'] ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form action="<?= site_url('acudiente/clases-particulares/rechazar-precio/' . $s['id']) ?>" method="post">
                                                    <?= csrf_field() ?>
                                                    <div class="modal-header">
                                                        <h6 class="modal-title">Rechazar Precio</h6>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>¿Estas seguro de rechazar el precio de <strong>$<?= number_format($s['precio_propuesto'], 0, ',', '.') ?></strong>?</p>
                                                        <div class="mb-3">
                                                            <label class="form-label">Motivo (opcional)</label>
                                                            <textarea name="motivo_rechazo" class="form-control" rows="2" placeholder="Indica por que rechazas el precio..."></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                                                        <button type="submit" class="btn btn-danger btn-sm">Rechazar Precio</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php elseif ($s['estado'] === 'aprobada' && !empty($s['respuesta_profesor'])): ?>
                                    <div class="small text-muted"><i class="bi bi-chat-quote"></i> <?= esc($s['respuesta_profesor']) ?></div>
                                <?php elseif ($s['estado'] === 'agendada'): ?>
                                    <span class="text-success"><i class="bi bi-calendar-check"></i> Programada</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>
