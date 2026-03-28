<div class="nav-section">Principal</div>
<a href="<?= site_url('admin/dashboard') ?>" class="nav-link <?= ($activePage ?? '') === 'dashboard' ? 'active' : '' ?>">
    <i class="bi bi-speedometer2"></i>
    Dashboard
</a>

<div class="nav-section">Gesti&oacute;n</div>
<a href="<?= site_url('admin/users') ?>" class="nav-link <?= ($activePage ?? '') === 'users' ? 'active' : '' ?>">
    <i class="bi bi-people"></i>
    Usuarios
</a>
<a href="<?= site_url('admin/students') ?>" class="nav-link <?= ($activePage ?? '') === 'students' ? 'active' : '' ?>">
    <i class="bi bi-person-badge"></i>
    Estudiantes
</a>
<a href="<?= site_url('admin/groups') ?>" class="nav-link <?= ($activePage ?? '') === 'groups' ? 'active' : '' ?>">
    <i class="bi bi-collection"></i>
    Grupos
</a>
<a href="<?= site_url('admin/schedules') ?>" class="nav-link <?= ($activePage ?? '') === 'schedules' ? 'active' : '' ?>">
    <i class="bi bi-calendar3"></i>
    Horarios
</a>

<div class="nav-section">Finanzas</div>
<a href="<?= site_url('admin/cartera') ?>" class="nav-link <?= ($activePage ?? '') === 'cartera' ? 'active' : '' ?>">
    <i class="bi bi-clipboard-check"></i>
    Cartera
</a>
<a href="<?= site_url('admin/payments') ?>" class="nav-link <?= ($activePage ?? '') === 'payments' ? 'active' : '' ?>">
    <i class="bi bi-cash-stack"></i>
    Pagos
</a>
<a href="<?= site_url('admin/tarifas') ?>" class="nav-link <?= ($activePage ?? '') === 'tarifas' ? 'active' : '' ?>">
    <i class="bi bi-tags"></i>
    Tarifas
</a>

<div class="nav-section">Actividades</div>
<a href="<?= site_url('admin/attendance') ?>" class="nav-link <?= ($activePage ?? '') === 'attendance' ? 'active' : '' ?>">
    <i class="bi bi-check2-square"></i>
    Asistencia
</a>
<a href="<?= site_url('admin/tournaments') ?>" class="nav-link <?= ($activePage ?? '') === 'tournaments' ? 'active' : '' ?>">
    <i class="bi bi-trophy"></i>
    Torneos
</a>

<div class="nav-section">Sistema</div>
<a href="<?= site_url('admin/auditoria') ?>" class="nav-link <?= ($activePage ?? '') === 'auditoria' ? 'active' : '' ?>">
    <i class="bi bi-shield-check"></i>
    Auditoria
</a>
