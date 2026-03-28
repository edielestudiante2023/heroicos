<?= $this->extend('layouts/main') ?>

<?= $this->section('sidebar') ?>
<?= $this->include('partials/profesor_sidebar') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php if (session()->getFlashdata('error')): ?>
<div class="alert alert-danger alert-dismissible fade show">
    <i class="bi bi-exclamation-circle me-2"></i>
    <?= session()->getFlashdata('error') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="mb-4">
    <a href="<?= site_url('profesor/clases-particulares') ?>" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Volver
    </a>
</div>

<div class="row g-4">
    <!-- Detalle de la solicitud -->
    <div class="col-md-6">
        <div class="table-card">
            <div class="card-header"><i class="bi bi-info-circle me-2"></i>Detalle de la Solicitud</div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr>
                        <th class="text-muted" style="width:40%">Estudiante</th>
                        <td><?= esc($solicitud['estudiante_nombres'] . ' ' . $solicitud['estudiante_apellidos']) ?></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Codigo</th>
                        <td><?= esc($solicitud['estudiante_codigo'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Acudiente</th>
                        <td>
                            <?= esc($solicitud['acudiente_nombres'] . ' ' . $solicitud['acudiente_apellidos']) ?>
                            <div class="small text-muted"><?= esc($solicitud['acudiente_telefono']) ?></div>
                        </td>
                    </tr>
                    <tr>
                        <th class="text-muted">Fecha preferida</th>
                        <td><?= $solicitud['fecha_preferida'] ? date('d/m/Y', strtotime($solicitud['fecha_preferida'])) : '-' ?></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Hora preferida</th>
                        <td><?= $solicitud['hora_preferida'] ? date('h:i A', strtotime($solicitud['hora_preferida'])) : '-' ?></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Duracion</th>
                        <td><?= $solicitud['duracion_minutos'] ?> minutos</td>
                    </tr>
                    <tr>
                        <th class="text-muted">Observaciones</th>
                        <td><?= esc($solicitud['observaciones'] ?? 'Ninguna') ?></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Estado</th>
                        <td>
                            <?php
                            $badge = match($solicitud['estado']) {
                                'pendiente' => 'warning',
                                'aprobada'  => 'info',
                                'rechazada' => 'danger',
                                'agendada'  => 'success',
                                default     => 'secondary',
                            };
                            ?>
                            <span class="badge bg-<?= $badge ?>"><?= ucfirst($solicitud['estado']) ?></span>
                        </td>
                    </tr>
                    <tr>
                        <th class="text-muted">Solicitado</th>
                        <td><?= date('d/m/Y h:i A', strtotime($solicitud['created_at'])) ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Formulario de respuesta o info de respuesta -->
    <div class="col-md-6">
        <?php if ($solicitud['estado'] === 'pendiente'): ?>
        <div class="table-card">
            <div class="card-header"><i class="bi bi-reply me-2"></i>Responder Solicitud</div>
            <div class="card-body">
                <form action="<?= site_url('profesor/clases-particulares/' . $solicitud['id'] . '/responder') ?>" method="post" id="formResponder">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Accion <span class="text-danger">*</span></label>
                        <div class="d-flex gap-2 flex-wrap">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="accion" id="accionAprobar" value="aprobar" checked>
                                <label class="form-check-label text-success fw-semibold" for="accionAprobar">
                                    <i class="bi bi-check-circle"></i> Aprobar
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="accion" id="accionRechazar" value="rechazar">
                                <label class="form-check-label text-danger fw-semibold" for="accionRechazar">
                                    <i class="bi bi-x-circle"></i> Rechazar
                                </label>
                            </div>
                            <?php if (!empty($otrosProfesores)): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="accion" id="accionReasignar" value="reasignar">
                                <label class="form-check-label text-primary fw-semibold" for="accionReasignar">
                                    <i class="bi bi-arrow-repeat"></i> Sugerir otro profesor
                                </label>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Campos para aprobar -->
                    <div id="camposAprobar">
                        <div class="mb-3">
                            <label class="form-label">Precio propuesto ($) <span class="text-danger">*</span></label>
                            <input type="number" name="precio_propuesto" class="form-control" min="1" step="1000" placeholder="Ej: 50000">
                            <div class="form-text">Indica el valor que cobraras por esta clase particular.</div>
                        </div>
                    </div>

                    <!-- Campos para rechazar -->
                    <div id="camposRechazar" style="display:none">
                        <div class="mb-3">
                            <label class="form-label">Motivo del rechazo <span class="text-danger">*</span></label>
                            <textarea name="motivo_rechazo" class="form-control" rows="3" placeholder="Explica por que no puedes aceptar esta solicitud..."></textarea>
                        </div>
                    </div>

                    <!-- Campos para reasignar -->
                    <div id="camposReasignar" style="display:none">
                        <div class="mb-3">
                            <label class="form-label">Profesor sugerido <span class="text-danger">*</span></label>
                            <select name="nuevo_profesor_id" class="form-select">
                                <option value="">Seleccionar profesor...</option>
                                <?php foreach ($otrosProfesores ?? [] as $op): ?>
                                <option value="<?= $op['id'] ?>">
                                    <?= esc($op['nombres'] . ' ' . $op['apellidos']) ?>
                                    <?= $op['especialidad'] ? ' - ' . esc($op['especialidad']) : '' ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">La solicitud sera reasignada a este profesor y el acudiente sera notificado.</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Mensaje para el acudiente</label>
                        <textarea name="respuesta_profesor" class="form-control" rows="2" placeholder="Mensaje adicional (opcional)..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-send me-1"></i>Enviar Respuesta
                    </button>
                </form>
            </div>
        </div>
        <?php else: ?>
        <div class="table-card">
            <div class="card-header"><i class="bi bi-info-circle me-2"></i>Respuesta Enviada</div>
            <div class="card-body">
                <?php if ($solicitud['precio_propuesto']): ?>
                <div class="mb-3">
                    <strong class="text-muted">Precio propuesto:</strong>
                    <div class="fs-4 fw-bold text-primary">$<?= number_format($solicitud['precio_propuesto'], 0, ',', '.') ?></div>
                </div>
                <?php endif; ?>

                <?php if ($solicitud['respuesta_profesor']): ?>
                <div class="mb-3">
                    <strong class="text-muted">Tu mensaje:</strong>
                    <p><?= esc($solicitud['respuesta_profesor']) ?></p>
                </div>
                <?php endif; ?>

                <?php if ($solicitud['motivo_rechazo']): ?>
                <div class="mb-3">
                    <strong class="text-muted">Motivo de rechazo:</strong>
                    <p class="text-danger"><?= esc($solicitud['motivo_rechazo']) ?></p>
                </div>
                <?php endif; ?>

                <?php if ($solicitud['precio_aceptado'] === '1'): ?>
                    <div class="alert alert-success mb-0">
                        <i class="bi bi-check-circle me-2"></i>El acudiente acepto el precio. La clase esta programada.
                    </div>
                <?php elseif ($solicitud['precio_aceptado'] === '0'): ?>
                    <div class="alert alert-danger mb-0">
                        <i class="bi bi-x-circle me-2"></i>El acudiente rechazo el precio propuesto.
                    </div>
                <?php elseif ($solicitud['estado'] === 'aprobada'): ?>
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-hourglass-split me-2"></i>Esperando respuesta del acudiente sobre el precio.
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($solicitud['estado'] === 'pendiente'): ?>
<script>
document.querySelectorAll('input[name="accion"]').forEach(radio => {
    radio.addEventListener('change', function() {
        document.getElementById('camposAprobar').style.display = this.value === 'aprobar' ? 'block' : 'none';
        document.getElementById('camposRechazar').style.display = this.value === 'rechazar' ? 'block' : 'none';
        const reasignar = document.getElementById('camposReasignar');
        if (reasignar) reasignar.style.display = this.value === 'reasignar' ? 'block' : 'none';
    });
});
</script>
<?php endif; ?>

<?= $this->endSection() ?>
