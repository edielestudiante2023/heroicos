<?= $this->extend('layouts/main') ?>

<?= $this->section('sidebar') ?>
<?= $this->include('acudiente/partials/sidebar') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php if (empty($horariosEstudiantes)): ?>
    <div class="table-card">
        <div class="card-body p-5 text-center text-muted">
            <i class="bi bi-calendar-x fs-1 d-block mb-3"></i>
            <h5>No hay horarios asignados</h5>
            <p>Los horarios se asignan cuando el estudiante es inscrito en un grupo.</p>
        </div>
    </div>
<?php else: ?>
    <div class="row g-4">
        <?php foreach ($horariosEstudiantes as $he): ?>
        <div class="col-lg-6">
            <div class="table-card">
                <div class="card-header">
                    <i class="bi bi-person me-2"></i>
                    <?= esc($he['estudiante']['nombres'] . ' ' . $he['estudiante']['apellidos']) ?>
                    <span class="badge bg-primary ms-2"><?= esc($he['grupo']) ?></span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr><th>Dia</th><th>Horario</th><th>Lugar</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($he['horarios'] as $h): ?>
                                <tr>
                                    <td>
                                        <i class="bi bi-calendar-day me-1"></i>
                                        <?= $dias[$h['dia_semana']] ?? 'Dia ' . $h['dia_semana'] ?>
                                    </td>
                                    <td>
                                        <i class="bi bi-clock me-1"></i>
                                        <?= date('g:i A', strtotime($h['hora_inicio'])) ?> - <?= date('g:i A', strtotime($h['hora_fin'])) ?>
                                    </td>
                                    <td>
                                        <i class="bi bi-geo-alt me-1"></i>
                                        <?= esc($h['lugar'] ?? 'Por definir') ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<?= $this->endSection() ?>
