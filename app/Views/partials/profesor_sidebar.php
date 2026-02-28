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

<div class="nav-section">Inscripciones</div>
<a href="<?= site_url('profesor/inscripcion') ?>" class="nav-link <?= ($activePage ?? '') === 'inscripcion' ? 'active' : '' ?>">
    <i class="bi bi-link-45deg"></i>
    Enlaces de Inscripcion
</a>
