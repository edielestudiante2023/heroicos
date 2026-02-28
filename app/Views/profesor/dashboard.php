<?= $this->extend('layouts/main') ?>

<?= $this->section('sidebar') ?>
<?php $activePage = 'dashboard'; ?>
<?= $this->include('partials/profesor_sidebar') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Stats Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-6 col-xl-4">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="value"><?= count($mis_grupos) ?></div>
                    <div class="label">Mis Grupos</div>
                </div>
                <div class="icon bg-primary">
                    <i class="bi bi-collection"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-4">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="value"><?= $total_estudiantes ?></div>
                    <div class="label">Total Estudiantes</div>
                </div>
                <div class="icon bg-info">
                    <i class="bi bi-people"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-4">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="value"><?= count($horarios_hoy) ?></div>
                    <div class="label">Clases Hoy (<?= $dia_actual ?>)</div>
                </div>
                <div class="icon bg-warning">
                    <i class="bi bi-calendar-check"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Content Row -->
<div class="row g-4">
    <!-- Today's Schedule -->
    <div class="col-lg-6">
        <div class="table-card">
            <div class="card-header">
                <i class="bi bi-calendar-event me-2"></i>Mis Clases de Hoy - <?= $dia_actual ?>
            </div>
            <div class="card-body p-0">
                <?php if (empty($horarios_hoy)): ?>
                    <div class="p-4 text-center text-muted">
                        <i class="bi bi-calendar-x fs-1 d-block mb-2"></i>
                        No tiene clases programadas para hoy
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($horarios_hoy as $horario): ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong><?= esc($horario['grupo_nombre']) ?></strong>
                                <div class="small text-muted">
                                    <?= substr($horario['hora_inicio'], 0, 5) ?> -
                                    <?= substr($horario['hora_fin'], 0, 5) ?>
                                    <?php if (!empty($horario['lugar'])): ?>
                                        | <?= esc($horario['lugar']) ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <a href="<?= site_url('profesor/attendance/take/' . $horario['grupo_id']) ?>" class="btn btn-sm btn-success">
                                <i class="bi bi-check2-square me-1"></i>Asistencia
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- My Groups -->
    <div class="col-lg-6">
        <div class="table-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-collection me-2"></i>Mis Grupos</span>
                <a href="<?= site_url('profesor/groups') ?>" class="btn btn-sm btn-outline-primary">Ver todos</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($mis_grupos)): ?>
                    <div class="p-4 text-center text-muted">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        No tiene grupos asignados
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($mis_grupos as $grupo): ?>
                        <a href="<?= site_url('profesor/groups/' . $grupo['id']) ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <strong><?= esc($grupo['nombre']) ?></strong>
                                <div class="small text-muted">
                                    <?= esc($grupo['categoria_nombre']) ?>
                                    <?php if ($grupo['es_titular']): ?>
                                        <span class="badge bg-primary ms-1">Titular</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <span class="badge bg-light text-dark"><?= $grupo['inscritos'] ?> estudiantes</span>
                        </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Offline Readiness Indicator -->
<div class="mt-4" id="offlineStatus" style="display:none;">
    <div class="card border-0" style="background: var(--heroicos-light, #f8e6fc); border-radius: 12px;">
        <div class="card-body d-flex justify-content-between align-items-center py-2 px-3">
            <div>
                <span class="small" id="offlineStatusText">
                    <i class="bi bi-cloud-check me-2" style="color: var(--heroicos-primary, #b720d2);"></i>Datos listos para uso sin conexion
                </span>
            </div>
            <button class="btn btn-sm btn-outline-primary" id="btnPrefetch" onclick="triggerPrefetch()" style="font-size:.8rem;">
                <i class="bi bi-arrow-repeat me-1"></i>Actualizar
            </button>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function() {
    if (typeof HeroicosDB === 'undefined') return;

    var statusEl = document.getElementById('offlineStatus');
    var textEl = document.getElementById('offlineStatusText');
    var btnEl = document.getElementById('btnPrefetch');
    var profesorId = document.body.getAttribute('data-profesor-id');

    HeroicosDB.init().then(function(ok) {
        if (!ok || !profesorId) return;
        return HeroicosDB.getCachedData('profesor_grupos_' + profesorId);
    }).then(function(cached) {
        statusEl.style.display = 'block';
        if (cached && cached.updatedAt) {
            var mins = Math.round((Date.now() - cached.updatedAt) / 60000);
            var label = mins < 1 ? 'hace menos de 1 min' :
                        mins < 60 ? 'hace ' + mins + ' min' :
                        'hace ' + Math.round(mins / 60) + ' h';
            textEl.innerHTML = '<i class="bi bi-cloud-check me-2" style="color:var(--heroicos-primary,#b720d2)"></i>Datos offline listos (actualizado ' + label + ')';
        } else {
            textEl.innerHTML = '<i class="bi bi-cloud-arrow-down me-2 text-muted"></i>Prepara tus datos para usar sin conexion';
            btnEl.innerHTML = '<i class="bi bi-download me-1"></i>Preparar offline';
        }
    });

    window.triggerPrefetch = function() {
        if (typeof HeroicosSync === 'undefined') return;
        btnEl.disabled = true;
        btnEl.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Actualizando...';

        HeroicosSync.prefetchProfesorData().then(function(ok) {
            btnEl.disabled = false;
            btnEl.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i>Actualizar';
            if (ok) {
                textEl.innerHTML = '<i class="bi bi-cloud-check me-2" style="color:var(--heroicos-primary,#b720d2)"></i>Datos offline actualizados ahora';
                if (typeof HeroicosOffline !== 'undefined') HeroicosOffline.showToast('Datos preparados para uso offline.', 'success');
            } else {
                textEl.innerHTML = '<i class="bi bi-exclamation-triangle me-2 text-warning"></i>No se pudieron actualizar los datos offline';
            }
        });
    };
})();
</script>
<?= $this->endSection() ?>
