<div class="nav-section">Principal</div>
<a href="<?= site_url('acudiente/dashboard') ?>" class="nav-link <?= uri_string() === 'acudiente/dashboard' ? 'active' : '' ?>">
    <i class="bi bi-speedometer2"></i>
    Dashboard
</a>

<div class="nav-section">Mis Hijos</div>
<a href="<?= site_url('acudiente/estudiantes') ?>" class="nav-link <?= str_starts_with(uri_string(), 'acudiente/estudiantes') ? 'active' : '' ?>">
    <i class="bi bi-people"></i>
    Mis Estudiantes
</a>
<a href="<?= site_url('acudiente/horarios') ?>" class="nav-link <?= uri_string() === 'acudiente/horarios' ? 'active' : '' ?>">
    <i class="bi bi-calendar3"></i>
    Horarios
</a>

<div class="nav-section">Clases</div>
<a href="<?= site_url('acudiente/clases-particulares') ?>" class="nav-link <?= str_starts_with(uri_string(), 'acudiente/clases-particulares') ? 'active' : '' ?>">
    <i class="bi bi-person-video3"></i>
    Clases Particulares
</a>

<div class="nav-section">Pagos</div>
<a href="<?= site_url('acudiente/pagos') ?>" class="nav-link <?= uri_string() === 'acudiente/pagos' ? 'active' : '' ?>">
    <i class="bi bi-wallet2"></i>
    Estado de Cuenta
</a>
<a href="<?= site_url('acudiente/pagos/subir') ?>" class="nav-link <?= uri_string() === 'acudiente/pagos/subir' ? 'active' : '' ?>">
    <i class="bi bi-cash-stack"></i>
    Registrar Pago
</a>

<div class="nav-section">Documentos</div>
<a href="<?= site_url('acudiente/paz-y-salvo') ?>" class="nav-link <?= uri_string() === 'acudiente/paz-y-salvo' ? 'active' : '' ?>">
    <i class="bi bi-file-earmark-check"></i>
    Paz y Salvo
</a>

<div class="nav-section">Mi Cuenta</div>
<a href="<?= site_url('acudiente/perfil') ?>" class="nav-link <?= uri_string() === 'acudiente/perfil' ? 'active' : '' ?>">
    <i class="bi bi-person-gear"></i>
    Mi Perfil
</a>
