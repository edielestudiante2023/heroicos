<?= $this->extend('layouts/main') ?>

<?= $this->section('sidebar') ?>
<?php $activePage = 'inscripcion'; ?>
<?= $this->include('partials/profesor_sidebar') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>
        <?= session()->getFlashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Generate Link Form -->
<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="table-card">
            <div class="card-header">
                <i class="bi bi-link-45deg me-2"></i>Generar Enlace de Inscripcion
            </div>
            <div class="card-body p-4">
                <form action="<?= site_url('profesor/inscripcion/generar') ?>" method="POST">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre del Acudiente</label>
                        <input type="text" class="form-control" id="nombre" name="nombre"
                               value="<?= old('nombre') ?>" required placeholder="Nombre completo del acudiente">
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email del Acudiente</label>
                        <input type="email" class="form-control" id="email" name="email"
                               value="<?= old('email') ?>" required placeholder="correo@ejemplo.com">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-send me-2"></i>Generar y Enviar Enlace
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="table-card">
            <div class="card-header">
                <i class="bi bi-info-circle me-2"></i>Como Funciona
            </div>
            <div class="card-body p-4">
                <ol class="mb-0">
                    <li class="mb-2">Ingresa el nombre y email del acudiente nuevo</li>
                    <li class="mb-2">Se le enviara un enlace de registro por email</li>
                    <li class="mb-2">El acudiente completa el formulario con sus datos y los de su(s) hijo(s)</li>
                    <li class="mb-2">Se crean automaticamente las cuentas de acudiente y estudiante(s)</li>
                    <li class="mb-0">Recibiras una notificacion cuando se complete el registro</li>
                </ol>
                <div class="alert alert-warning mt-3 mb-0">
                    <i class="bi bi-clock me-2"></i>
                    El enlace expira en <strong>48 horas</strong> despues de generarse.
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Previous Links Table -->
<div class="table-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-list-ul me-2"></i>Enlaces Generados</span>
        <span class="badge bg-primary"><?= count($tokens) ?> enlaces</span>
    </div>
    <div class="card-body p-0">
        <?php if (empty($tokens)): ?>
            <div class="p-4 text-center text-muted">
                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                No has generado enlaces de inscripcion aun
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Fecha</th>
                            <th>Nombre Acudiente</th>
                            <th>Email</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tokens as $tk): ?>
                        <tr>
                            <td><?= date('d/m/Y H:i', strtotime($tk['created_at'])) ?></td>
                            <td><?= esc($tk['nombre_acudiente']) ?></td>
                            <td><?= esc($tk['email']) ?></td>
                            <td>
                                <?php if ($tk['usado']): ?>
                                    <span class="badge bg-success">Usado</span>
                                    <small class="text-muted d-block"><?= date('d/m/Y', strtotime($tk['fecha_uso'])) ?></small>
                                <?php elseif (strtotime($tk['expira_at']) < time()): ?>
                                    <span class="badge bg-secondary">Expirado</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark">Pendiente</span>
                                    <small class="text-muted d-block">Expira: <?= date('d/m/Y H:i', strtotime($tk['expira_at'])) ?></small>
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
<?= $this->endSection() ?>
