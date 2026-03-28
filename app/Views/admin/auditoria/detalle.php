<?= $this->extend('layouts/main') ?>

<?= $this->section('sidebar') ?>
<?= $this->include('partials/admin_sidebar') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="mb-4">
    <a href="<?= site_url('admin/auditoria') ?>" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Volver
    </a>
</div>

<div class="row g-4">
    <div class="col-md-6">
        <div class="table-card">
            <div class="card-header"><i class="bi bi-info-circle me-2"></i>Informacion del Registro</div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr>
                        <th class="text-muted" style="width:35%">ID</th>
                        <td><?= $log['id'] ?></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Fecha</th>
                        <td><?= date('d/m/Y h:i:s A', strtotime($log['created_at'])) ?></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Usuario</th>
                        <td><?= esc($log['usuario_email'] ?? 'Sistema') ?></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Accion</th>
                        <td><span class="badge bg-secondary"><?= esc($log['accion']) ?></span></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Tabla</th>
                        <td><?= esc($log['tabla'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Registro ID</th>
                        <td><?= $log['registro_id'] ?? '-' ?></td>
                    </tr>
                    <tr>
                        <th class="text-muted">IP</th>
                        <td><code><?= esc($log['ip'] ?? '-') ?></code></td>
                    </tr>
                    <tr>
                        <th class="text-muted">User Agent</th>
                        <td class="small"><?= esc($log['user_agent'] ?? '-') ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <?php if ($log['datos_anteriores']): ?>
        <div class="table-card mb-4">
            <div class="card-header"><i class="bi bi-file-earmark-minus me-2 text-danger"></i>Datos Anteriores</div>
            <div class="card-body">
                <pre class="mb-0 small" style="max-height:300px;overflow:auto;background:#f8f9fa;padding:1rem;border-radius:8px;"><?= esc(json_encode(json_decode($log['datos_anteriores']), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($log['datos_nuevos']): ?>
        <div class="table-card">
            <div class="card-header"><i class="bi bi-file-earmark-plus me-2 text-success"></i>Datos Nuevos</div>
            <div class="card-body">
                <pre class="mb-0 small" style="max-height:300px;overflow:auto;background:#f8f9fa;padding:1rem;border-radius:8px;"><?= esc(json_encode(json_decode($log['datos_nuevos']), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!$log['datos_anteriores'] && !$log['datos_nuevos']): ?>
        <div class="table-card">
            <div class="card-body text-center p-4 text-muted">
                <i class="bi bi-file-earmark fs-3 d-block mb-2"></i>
                No hay datos de cambio registrados para esta accion.
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>
