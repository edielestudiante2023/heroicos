<?= $this->extend('layouts/main') ?>

<?= $this->section('sidebar') ?>
<div class="nav-section">Principal</div>
<a href="<?= site_url('acudiente/dashboard') ?>" class="nav-link active">
    <i class="bi bi-speedometer2"></i>
    Dashboard
</a>

<div class="nav-section">Mis Hijos</div>
<a href="#" class="nav-link">
    <i class="bi bi-people"></i>
    Mis Estudiantes
</a>
<a href="#" class="nav-link">
    <i class="bi bi-calendar3"></i>
    Horarios
</a>

<div class="nav-section">Pagos</div>
<a href="#" class="nav-link">
    <i class="bi bi-wallet2"></i>
    Estado de Cuenta
</a>
<a href="#" class="nav-link">
    <i class="bi bi-cash-stack"></i>
    Registrar Pago
</a>
<a href="#" class="nav-link">
    <i class="bi bi-file-earmark-text"></i>
    Historial
</a>

<div class="nav-section">Documentos</div>
<a href="<?= site_url('acudiente/paz-y-salvo') ?>" class="nav-link">
    <i class="bi bi-file-earmark-check"></i>
    Paz y Salvo
</a>

<div class="nav-section">Actividades</div>
<a href="#" class="nav-link">
    <i class="bi bi-trophy"></i>
    Torneos
</a>
<a href="#" class="nav-link">
    <i class="bi bi-person-video3"></i>
    Solicitar Clase Privada
</a>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Alert for pending balance -->
<?php if ($saldo_total > 0): ?>
<div class="alert alert-warning d-flex align-items-center" role="alert">
    <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
    <div>
        <strong>Saldo pendiente:</strong> $<?= number_format($saldo_total, 0, ',', '.') ?>
        <a href="#" class="alert-link ms-2">Ver detalles</a>
    </div>
</div>
<?php endif; ?>

<!-- Stats Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-6 col-xl-4">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="value"><?= count($mis_estudiantes) ?></div>
                    <div class="label">Mis Estudiantes</div>
                </div>
                <div class="icon bg-primary">
                    <i class="bi bi-people"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-4">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="value">$<?= number_format($saldo_total, 0, ',', '.') ?></div>
                    <div class="label">Saldo Pendiente</div>
                </div>
                <div class="icon <?= $saldo_total > 0 ? 'bg-danger' : 'bg-success' ?>">
                    <i class="bi bi-<?= $saldo_total > 0 ? 'exclamation-circle' : 'check-circle' ?>"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-4">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="value"><?= count($pagos_pendientes) ?></div>
                    <div class="label">Conceptos Pendientes</div>
                </div>
                <div class="icon bg-warning">
                    <i class="bi bi-file-earmark-text"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Content Row -->
<div class="row g-4">
    <!-- Mis Estudiantes -->
    <div class="col-lg-6">
        <div class="table-card">
            <div class="card-header">
                <i class="bi bi-people me-2"></i>Mis Estudiantes
            </div>
            <div class="card-body p-0">
                <?php if (empty($mis_estudiantes)): ?>
                    <div class="p-4 text-center text-muted">
                        <i class="bi bi-person-plus fs-1 d-block mb-2"></i>
                        No tiene estudiantes registrados
                        <div class="mt-3">
                            <a href="#" class="btn btn-primary btn-sm">Inscribir Estudiante</a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($mis_estudiantes as $estudiante): ?>
                        <a href="#" class="list-group-item list-group-item-action">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong><?= esc($estudiante['nombres'] . ' ' . $estudiante['apellidos']) ?></strong>
                                    <div class="small text-muted">
                                        Código: <?= esc($estudiante['codigo'] ?? 'Sin asignar') ?>
                                    </div>
                                </div>
                                <i class="bi bi-chevron-right"></i>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Pagos Recientes -->
    <div class="col-lg-6">
        <div class="table-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-clock-history me-2"></i>Pagos Recientes</span>
                <a href="#" class="btn btn-sm btn-outline-primary">Ver historial</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($pagos_recientes)): ?>
                    <div class="p-4 text-center text-muted">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        No hay pagos registrados
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Estudiante</th>
                                    <th>Monto</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pagos_recientes as $pago): ?>
                                <tr>
                                    <td>
                                        Recibo #<?= esc($pago['numero_recibo'] ?? $pago['id']) ?>
                                        <div class="small text-muted"><?= date('d/m/Y', strtotime($pago['fecha_pago'] ?? $pago['created_at'])) ?></div>
                                    </td>
                                    <td>$<?= number_format($pago['valor_total'], 0, ',', '.') ?></td>
                                    <td>
                                        <?php
                                        $badgeClass = match($pago['estado']) {
                                            'aprobado' => 'success',
                                            'pendiente_revision' => 'warning',
                                            'rechazado' => 'danger',
                                            default => 'secondary'
                                        };
                                        $estadoLabel = match($pago['estado']) {
                                            'aprobado' => 'Aprobado',
                                            'pendiente_revision' => 'Pendiente',
                                            'rechazado' => 'Rechazado',
                                            default => ucfirst($pago['estado'])
                                        };
                                        ?>
                                        <span class="badge bg-<?= $badgeClass ?>"><?= $estadoLabel ?></span>
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

<!-- Quick Actions -->
<div class="row mt-4">
    <div class="col-12">
        <div class="table-card">
            <div class="card-header">
                <i class="bi bi-lightning me-2"></i>Acciones Rápidas
            </div>
            <div class="card-body">
                <div class="d-flex flex-wrap gap-2">
                    <a href="#" class="btn btn-primary">
                        <i class="bi bi-cash-stack me-2"></i>Registrar Pago
                    </a>
                    <a href="#" class="btn btn-success">
                        <i class="bi bi-file-earmark-text me-2"></i>Ver Estado de Cuenta
                    </a>
                    <a href="#" class="btn btn-info text-white">
                        <i class="bi bi-calendar3 me-2"></i>Ver Horarios
                    </a>
                    <a href="#" class="btn btn-warning">
                        <i class="bi bi-person-video3 me-2"></i>Solicitar Clase Privada
                    </a>
                </div>
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
            <button class="btn btn-sm btn-outline-primary" id="btnPrefetch" onclick="triggerAcudientePrefetch()" style="font-size:.8rem;">
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
    var acudienteId = document.body.getAttribute('data-acudiente-id');

    HeroicosDB.init().then(function(ok) {
        if (!ok || !acudienteId) return;
        return HeroicosDB.getCachedData('acudiente_dashboard_' + acudienteId);
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

    window.triggerAcudientePrefetch = function() {
        if (typeof HeroicosSync === 'undefined') return;
        btnEl.disabled = true;
        btnEl.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Actualizando...';

        HeroicosSync.prefetchAcudienteData().then(function(ok) {
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
