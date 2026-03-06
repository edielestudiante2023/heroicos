<?= $this->extend('layouts/main') ?>

<?= $this->section('sidebar') ?>
<?= $this->include('acudiente/partials/sidebar') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<a href="<?= site_url('acudiente/estudiantes') ?>" class="btn btn-sm btn-outline-secondary mb-3">
    <i class="bi bi-arrow-left me-1"></i>Volver
</a>

<div class="row g-4">
    <!-- Student Info -->
    <div class="col-lg-6">
        <div class="table-card">
            <div class="card-header"><i class="bi bi-person me-2"></i>Datos del Estudiante</div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr><th class="text-muted" style="width:40%">Nombre</th><td><?= esc($estudiante['nombres'] . ' ' . $estudiante['apellidos']) ?></td></tr>
                    <tr><th class="text-muted">Codigo</th><td><?= esc($estudiante['codigo'] ?? 'N/A') ?></td></tr>
                    <tr><th class="text-muted">Documento</th><td><?= esc(($estudiante['tipo_documento'] ?? '') . ' ' . ($estudiante['numero_documento'] ?? '')) ?></td></tr>
                    <tr><th class="text-muted">Fecha Nacimiento</th><td><?= $estudiante['fecha_nacimiento'] ? date('d/m/Y', strtotime($estudiante['fecha_nacimiento'])) : 'N/A' ?></td></tr>
                    <tr><th class="text-muted">Sexo</th><td><?= $estudiante['sexo'] === 'M' ? 'Masculino' : 'Femenino' ?></td></tr>
                    <tr><th class="text-muted">EPS</th><td><?= esc($estudiante['eps'] ?? 'N/A') ?></td></tr>
                    <tr><th class="text-muted">Grupo Sanguineo</th><td><?= esc($estudiante['grupo_sanguineo'] ?? 'N/A') ?></td></tr>
                    <tr><th class="text-muted">Estado</th><td><span class="badge bg-<?= $estudiante['estado'] === 'activo' ? 'success' : 'secondary' ?>"><?= ucfirst($estudiante['estado']) ?></span></td></tr>
                    <?php if ($estudiante['grupo']): ?>
                    <tr><th class="text-muted">Grupo</th><td><?= esc($estudiante['grupo']) ?></td></tr>
                    <?php endif; ?>
                    <?php if ($estudiante['categoria']): ?>
                    <tr><th class="text-muted">Categoria</th><td><?= esc($estudiante['categoria']) ?></td></tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>

    <!-- Medical Info -->
    <div class="col-lg-6">
        <div class="table-card">
            <div class="card-header"><i class="bi bi-heart-pulse me-2"></i>Info Medica y Emergencia</div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr><th class="text-muted" style="width:40%">Alergias</th><td><?= esc($estudiante['alergias'] ?? 'Ninguna') ?></td></tr>
                    <tr><th class="text-muted">Condiciones</th><td><?= esc($estudiante['condiciones_medicas'] ?? 'Ninguna') ?></td></tr>
                    <tr><th class="text-muted">Medicamentos</th><td><?= esc($estudiante['medicamentos'] ?? 'Ninguno') ?></td></tr>
                    <tr><th class="text-muted">Contacto Emergencia</th><td><?= esc($estudiante['contacto_emergencia'] ?? 'N/A') ?></td></tr>
                    <tr><th class="text-muted">Tel. Emergencia</th><td><?= esc($estudiante['telefono_emergencia'] ?? 'N/A') ?></td></tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Horarios -->
    <?php if (!empty($horarios)): ?>
    <div class="col-lg-6">
        <div class="table-card">
            <div class="card-header"><i class="bi bi-calendar3 me-2"></i>Horarios</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr><th>Dia</th><th>Hora</th><th>Lugar</th></tr>
                        </thead>
                        <tbody>
                            <?php
                            $dias = ['', 'Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sab', 'Dom'];
                            foreach ($horarios as $h): ?>
                            <tr>
                                <td><?= $dias[$h['dia_semana']] ?? $h['dia_semana'] ?></td>
                                <td><?= date('g:i A', strtotime($h['hora_inicio'])) ?> - <?= date('g:i A', strtotime($h['hora_fin'])) ?></td>
                                <td><?= esc($h['lugar'] ?? 'Por definir') ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Cargos -->
    <div class="col-12">
        <div class="table-card">
            <div class="card-header"><i class="bi bi-receipt me-2"></i>Cargos</div>
            <div class="card-body p-0">
                <?php if (empty($cargos)): ?>
                    <div class="p-4 text-center text-muted">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        No hay cargos registrados
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Concepto</th>
                                    <th>Valor</th>
                                    <th>Pagado</th>
                                    <th>Saldo</th>
                                    <th>Vencimiento</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cargos as $c): ?>
                                <tr>
                                    <td>
                                        <?= esc($c['concepto_nombre']) ?>
                                        <?php if ($c['descripcion']): ?>
                                            <div class="small text-muted"><?= esc($c['descripcion']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>$<?= number_format($c['valor_original'], 0, ',', '.') ?></td>
                                    <td>$<?= number_format($c['valor_pagado'], 0, ',', '.') ?></td>
                                    <td class="<?= $c['saldo_pendiente'] > 0 ? 'text-danger fw-semibold' : 'text-success' ?>">
                                        $<?= number_format($c['saldo_pendiente'], 0, ',', '.') ?>
                                    </td>
                                    <td><?= $c['fecha_vencimiento'] ? date('d/m/Y', strtotime($c['fecha_vencimiento'])) : '-' ?></td>
                                    <td>
                                        <?php
                                        $badge = match($c['estado']) {
                                            'pagado' => 'success',
                                            'parcial' => 'warning',
                                            'pendiente' => 'danger',
                                            'anulado' => 'secondary',
                                            default => 'secondary',
                                        };
                                        ?>
                                        <span class="badge bg-<?= $badge ?>"><?= ucfirst($c['estado']) ?></span>
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
</div>
<?= $this->endSection() ?>
