<?= $this->extend('layouts/main') ?>

<?= $this->section('sidebar') ?>
<?= $this->include('partials/profesor_sidebar') ?>
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

<h5 class="mb-4">Solicitudes de Clases Particulares</h5>

<?php if (empty($solicitudes)): ?>
    <div class="table-card">
        <div class="card-body text-center p-5">
            <i class="bi bi-person-video3 fs-1 d-block mb-3 text-muted"></i>
            <h6 class="text-muted">No tienes solicitudes de clases particulares</h6>
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
                            <th>Acudiente</th>
                            <th>Fecha / Hora</th>
                            <th>Duracion</th>
                            <th>Estado</th>
                            <th>Precio</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($solicitudes as $s): ?>
                        <tr class="<?= $s['estado'] === 'pendiente' ? 'table-warning' : '' ?>">
                            <td><?= esc($s['estudiante_nombres'] . ' ' . $s['estudiante_apellidos']) ?></td>
                            <td>
                                <?= esc($s['acudiente_nombres'] . ' ' . $s['acudiente_apellidos']) ?>
                                <div class="small text-muted"><?= esc($s['acudiente_telefono']) ?></div>
                            </td>
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
                                    'aprobada'  => 'Esperando acudiente',
                                    'rechazada' => 'Rechazada',
                                    'agendada'  => 'Confirmada',
                                    default     => ucfirst($s['estado']),
                                };
                                ?>
                                <span class="badge bg-<?= $badge ?>"><?= $label ?></span>
                            </td>
                            <td>
                                <?php if ($s['precio_propuesto']): ?>
                                    $<?= number_format($s['precio_propuesto'], 0, ',', '.') ?>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?= site_url('profesor/clases-particulares/' . $s['id']) ?>" class="btn btn-sm <?= $s['estado'] === 'pendiente' ? 'btn-primary' : 'btn-outline-secondary' ?>">
                                    <i class="bi bi-eye me-1"></i><?= $s['estado'] === 'pendiente' ? 'Responder' : 'Ver' ?>
                                </a>
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
