<?= $this->extend('layouts/main') ?>

<?= $this->section('sidebar') ?>
<?php
    $rol = $rolNombre ?? session()->get('rol_nombre');
    if ($rol === 'acudiente') {
        echo $this->include('acudiente/partials/sidebar');
    } elseif ($rol === 'profesor') {
        echo $this->include('partials/profesor_sidebar');
    } elseif ($rol === 'admin' || $rol === 'administrador') {
        echo $this->include('partials/admin_sidebar');
    }
?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<h5 class="mb-4"><i class="bi bi-bell me-2"></i>Todas las Notificaciones</h5>

<?php if (empty($notificaciones)): ?>
    <div class="table-card">
        <div class="card-body text-center p-5">
            <i class="bi bi-bell-slash fs-1 d-block mb-3 text-muted"></i>
            <h6 class="text-muted">No tienes notificaciones</h6>
        </div>
    </div>
<?php else: ?>
    <div class="table-card">
        <div class="card-body p-0">
            <?php foreach ($notificaciones as $n): ?>
                <?php
                    $unread = !$n['leida'] || $n['leida'] === '0';
                    $bg = $unread ? 'background:#f0f4ff;' : '';
                    $url = !empty($n['url']) ? site_url($n['url']) : '#';
                    $iconMap = [
                        'solicitud_clase_particular'    => 'bi-person-video3 text-primary',
                        'respuesta_clase_particular'    => 'bi-reply text-info',
                        'reasignacion_clase_particular' => 'bi-arrow-repeat text-warning',
                        'precio_aceptado'               => 'bi-check-circle text-success',
                        'precio_rechazado'              => 'bi-x-circle text-danger',
                    ];
                    $icon = $iconMap[$n['tipo']] ?? 'bi-bell text-secondary';
                ?>
                <a href="<?= $url ?>" class="d-flex align-items-start gap-3 px-3 py-3 text-decoration-none border-bottom" style="<?= $bg ?>">
                    <div class="mt-1">
                        <i class="bi <?= $icon ?>" style="font-size:1.3rem;"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between">
                            <strong class="small text-dark"><?= esc($n['titulo']) ?></strong>
                            <span class="text-muted" style="font-size:0.75rem;"><?= date('d/m/Y h:i A', strtotime($n['created_at'])) ?></span>
                        </div>
                        <div class="text-muted small"><?= esc($n['mensaje']) ?></div>
                    </div>
                    <?php if ($unread): ?>
                        <span class="badge bg-primary rounded-pill align-self-center" style="font-size:0.6rem;">Nueva</span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>
