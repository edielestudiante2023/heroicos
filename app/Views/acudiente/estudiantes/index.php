<?= $this->extend('layouts/main') ?>

<?= $this->section('sidebar') ?>
<?= $this->include('acudiente/partials/sidebar') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-end mb-3">
    <a href="<?= site_url('acudiente/estudiantes/crear') ?>" class="btn btn-primary">
        <i class="bi bi-person-plus me-1"></i>Agregar Estudiante
    </a>
</div>

<div class="row g-4">
    <?php if (empty($estudiantes)): ?>
        <div class="col-12">
            <div class="table-card">
                <div class="card-body p-5 text-center text-muted">
                    <i class="bi bi-person-plus fs-1 d-block mb-3"></i>
                    <h5>No tiene estudiantes registrados</h5>
                    <p>Haga clic en "Agregar Estudiante" para registrar uno.</p>
                </div>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($estudiantes as $est): ?>
        <div class="col-md-6 col-xl-4">
            <div class="table-card h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <?php if (!empty($est['foto'])): ?>
                            <img src="<?= base_url($est['foto']) ?>" alt="<?= esc($est['nombres']) ?>"
                                 style="width:48px;height:48px;border-radius:50%;object-fit:cover;border:2px solid var(--heroicos-primary);">
                        <?php else: ?>
                            <div class="user-avatar" style="width:48px;height:48px;font-size:1.2rem;">
                                <?= strtoupper(substr($est['nombres'], 0, 1) . substr($est['apellidos'], 0, 1)) ?>
                            </div>
                        <?php endif; ?>
                        <div>
                            <h6 class="mb-0"><?= esc($est['nombres'] . ' ' . $est['apellidos']) ?></h6>
                            <small class="text-muted">Codigo: <?= esc($est['codigo'] ?? 'N/A') ?></small>
                        </div>
                    </div>

                    <div class="mb-2">
                        <small class="text-muted d-block">
                            <i class="bi bi-calendar3 me-1"></i>
                            <?= $est['fecha_nacimiento'] ? date('d/m/Y', strtotime($est['fecha_nacimiento'])) : 'N/A' ?>
                            &middot; <?= $est['sexo'] === 'M' ? 'Masculino' : 'Femenino' ?>
                        </small>
                    </div>

                    <?php if ($est['grupo']): ?>
                        <span class="badge bg-primary mb-2">
                            <i class="bi bi-people me-1"></i><?= esc($est['grupo']) ?>
                        </span>
                    <?php endif; ?>

                    <?php if ($est['categoria']): ?>
                        <span class="badge bg-info text-white mb-2"><?= esc($est['categoria']) ?></span>
                    <?php endif; ?>

                    <span class="badge bg-<?= $est['estado'] === 'activo' ? 'success' : 'secondary' ?> mb-2">
                        <?= ucfirst($est['estado']) ?>
                    </span>

                    <?php if ($est['saldo_pendiente'] > 0): ?>
                        <div class="mt-2 p-2 rounded" style="background: rgba(214,43,35,0.08);">
                            <small class="text-danger fw-semibold">
                                <i class="bi bi-exclamation-circle me-1"></i>
                                Saldo pendiente: $<?= number_format($est['saldo_pendiente'], 0, ',', '.') ?>
                            </small>
                        </div>
                    <?php endif; ?>

                    <div class="mt-3">
                        <a href="<?= site_url('acudiente/estudiantes/' . $est['id']) ?>" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-eye me-1"></i>Ver detalle
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
