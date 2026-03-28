<div class="nav-section">Principal</div>
<a href="<?= site_url('profesor/dashboard') ?>" class="nav-link <?= ($activePage ?? '') === 'dashboard' ? 'active' : '' ?>">
    <i class="bi bi-speedometer2"></i>
    Dashboard
</a>

<div class="nav-section">Clases</div>
<a href="<?= site_url('profesor/groups') ?>" class="nav-link <?= ($activePage ?? '') === 'groups' ? 'active' : '' ?>">
    <i class="bi bi-collection"></i>
    Mis Grupos
</a>
<a href="<?= site_url('profesor/attendance') ?>" class="nav-link <?= ($activePage ?? '') === 'attendance' ? 'active' : '' ?>">
    <i class="bi bi-check2-square"></i>
    Asistencia
</a>
<?php
    // Count pending private class requests
    $__profesorUserId = session()->get('user_id');
    $__profesorRow = \Config\Database::connect()->table('profesores')->where('usuario_id', $__profesorUserId)->get()->getRowArray();
    $__pendingCount = 0;
    if ($__profesorRow) {
        $__pendingCount = \Config\Database::connect()->table('solicitudes_clase_particular')
            ->where('profesor_id', $__profesorRow['id'])
            ->where('estado', 'pendiente')
            ->countAllResults();
    }
?>
<a href="<?= site_url('profesor/clases-particulares') ?>" class="nav-link <?= ($activePage ?? '') === 'clases_particulares' ? 'active' : '' ?>">
    <i class="bi bi-person-video3"></i>
    Clases Particulares
    <?php if ($__pendingCount > 0): ?>
        <span class="badge bg-danger ms-auto"><?= $__pendingCount ?></span>
    <?php endif; ?>
</a>

<div class="nav-section">Inscripciones</div>
<a href="<?= site_url('profesor/inscripcion') ?>" class="nav-link <?= ($activePage ?? '') === 'inscripcion' ? 'active' : '' ?>">
    <i class="bi bi-link-45deg"></i>
    Enlaces de Inscripcion
</a>
