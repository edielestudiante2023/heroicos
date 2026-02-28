<?= $this->extend('layouts/main') ?>

<?= $this->section('sidebar') ?>
<?php $activePage = 'tournaments'; ?>
<?= $this->include('partials/admin_sidebar') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="value"><?= $stats['abiertos'] ?></div>
                    <div class="label">Inscripciones Abiertas</div>
                </div>
                <div class="icon bg-primary"><i class="bi bi-door-open"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="value"><?= $stats['en_curso'] ?></div>
                    <div class="label">En Curso</div>
                </div>
                <div class="icon bg-warning"><i class="bi bi-play-circle"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="value"><?= $stats['programados'] ?></div>
                    <div class="label">Programados</div>
                </div>
                <div class="icon bg-info"><i class="bi bi-calendar-event"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="value"><?= $stats['finalizados'] ?></div>
                    <div class="label">Finalizados</div>
                </div>
                <div class="icon bg-danger"><i class="bi bi-flag"></i></div>
            </div>
        </div>
    </div>
</div>

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Torneos</h4>
        <p class="text-muted mb-0">Administra los torneos y competencias</p>
    </div>
    <a href="<?= site_url('admin/tournaments/new') ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg me-2"></i>Nuevo Torneo
    </a>
</div>

<?php if (session()->getFlashdata('success')): ?>
<div class="alert alert-success alert-dismissible fade show">
    <i class="bi bi-check-circle me-2"></i><?= session()->getFlashdata('success') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Filters -->
<div class="table-card mb-4">
    <div class="card-body">
        <form action="<?= site_url('admin/tournaments') ?>" method="get" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Buscar</label>
                <input type="text" class="form-control" name="search"
                       value="<?= esc($filters['search'] ?? '') ?>" placeholder="Nombre o lugar...">
            </div>
            <div class="col-md-3">
                <label class="form-label">Categor&iacute;a</label>
                <select class="form-select" name="categoria_id">
                    <option value="">Todas</option>
                    <?php foreach ($categorias as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= ($filters['categoria_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                        <?= esc($cat['nombre']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Estado</label>
                <select class="form-select" name="estado">
                    <option value="">Todos</option>
                    <option value="programado" <?= ($filters['estado'] ?? '') === 'programado' ? 'selected' : '' ?>>Programado</option>
                    <option value="inscripciones_abiertas" <?= ($filters['estado'] ?? '') === 'inscripciones_abiertas' ? 'selected' : '' ?>>Inscripciones Abiertas</option>
                    <option value="inscripciones_cerradas" <?= ($filters['estado'] ?? '') === 'inscripciones_cerradas' ? 'selected' : '' ?>>Inscripciones Cerradas</option>
                    <option value="en_curso" <?= ($filters['estado'] ?? '') === 'en_curso' ? 'selected' : '' ?>>En Curso</option>
                    <option value="finalizado" <?= ($filters['estado'] ?? '') === 'finalizado' ? 'selected' : '' ?>>Finalizado</option>
                    <option value="cancelado" <?= ($filters['estado'] ?? '') === 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-outline-primary w-100">
                    <i class="bi bi-search me-1"></i>Filtrar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Tournaments List -->
<?php if (empty($torneos)): ?>
    <div class="table-card">
        <div class="card-body text-center text-muted py-5">
            <i class="bi bi-trophy fs-1 d-block mb-2"></i>
            No se encontraron torneos
        </div>
    </div>
<?php else: ?>
    <div class="row g-4">
        <?php foreach ($torneos as $torneo): ?>
        <div class="col-md-6 col-lg-4">
            <div class="table-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h5 class="mb-0"><?= esc($torneo['nombre']) ?></h5>
                        <?php
                        $estadoBadge = match($torneo['estado']) {
                            'inscripciones_abiertas' => 'success',
                            'en_curso'               => 'warning',
                            'finalizado'             => 'secondary',
                            'cancelado'              => 'danger',
                            default                  => 'info',
                        };
                        ?>
                        <span class="badge bg-<?= $estadoBadge ?>"><?= str_replace('_', ' ', ucfirst($torneo['estado'])) ?></span>
                    </div>

                    <?php if ($torneo['categoria_nombre']): ?>
                        <span class="badge bg-secondary mb-2"><?= esc($torneo['categoria_nombre']) ?></span>
                    <?php else: ?>
                        <span class="badge bg-light text-dark mb-2">Todas las categor&iacute;as</span>
                    <?php endif; ?>

                    <div class="mb-2">
                        <i class="bi bi-calendar-event me-1"></i>
                        <?= date('d/m/Y', strtotime($torneo['fecha_evento'])) ?>
                        <?php if ($torneo['hora_evento']): ?>
                            - <?= substr($torneo['hora_evento'], 0, 5) ?>
                        <?php endif; ?>
                    </div>

                    <?php if ($torneo['lugar']): ?>
                    <div class="mb-2">
                        <i class="bi bi-geo-alt me-1"></i><?= esc($torneo['lugar']) ?>
                    </div>
                    <?php endif; ?>

                    <div class="d-flex justify-content-between mb-2">
                        <span>Cupos:</span>
                        <strong>
                            <?= $torneo['inscritos'] ?>/<?= $torneo['cupo_maximo'] ?>
                            <?php if ($torneo['cupos_disponibles'] <= 0): ?>
                                <span class="text-danger">(Agotados)</span>
                            <?php endif; ?>
                        </strong>
                    </div>

                    <div class="d-flex justify-content-between mb-3">
                        <span>Costo:</span>
                        <strong>$<?= number_format($torneo['costo'], 0, ',', '.') ?></strong>
                    </div>

                    <div class="d-grid">
                        <a href="<?= site_url('admin/tournaments/' . $torneo['id']) ?>" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-eye me-1"></i>Ver Detalle
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<?= $this->endSection() ?>
