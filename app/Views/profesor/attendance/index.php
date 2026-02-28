<?= $this->extend('layouts/main') ?>

<?= $this->section('sidebar') ?>
<?php $activePage = 'attendance'; ?>
<?= $this->include('partials/profesor_sidebar') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Mi Asistencia</h4>
        <p class="text-muted mb-0">Historial de sesiones de clase</p>
    </div>
</div>

<div class="table-card">
    <div class="card-body p-0">
        <?php if (empty($sesiones)): ?>
            <div class="p-4 text-center text-muted">
                <i class="bi bi-calendar-x fs-1 d-block mb-2"></i>
                No hay sesiones registradas. Toma asistencia desde <a href="<?= site_url('profesor/groups') ?>">Mis Grupos</a>.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Fecha</th>
                            <th>Grupo</th>
                            <th>Horario</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sesiones as $s): ?>
                        <tr>
                            <td><strong><?= date('d/m/Y', strtotime($s['fecha'])) ?></strong></td>
                            <td><?= esc($s['grupo_nombre']) ?></td>
                            <td><?= substr($s['hora_inicio'], 0, 5) ?> - <?= substr($s['hora_fin'], 0, 5) ?></td>
                            <td>
                                <span class="badge bg-<?= $s['estado'] === 'realizada' ? 'success' : 'secondary' ?>">
                                    <?= ucfirst($s['estado']) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>
