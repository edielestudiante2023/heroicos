<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Academia Heroicos - Sistema de Gestión">
    <title><?= $title ?? 'Academia Heroicos' ?></title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root {
            --heroicos-primary: #b720d2;
            --heroicos-secondary: #ffd65e;
            --heroicos-dark: #8a189e;
            --heroicos-light: #f8e6fc;
            --heroicos-accent: #d62b23;
            --sidebar-width: 260px;
        }

        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background-color: #f5f7fa;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: linear-gradient(180deg, var(--heroicos-primary) 0%, var(--heroicos-dark) 100%);
            color: white;
            z-index: 1000;
            transition: transform 0.3s ease;
        }

        .sidebar-header {
            padding: 1.5rem;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-header h4 {
            margin: 0;
            font-weight: 700;
        }

        .sidebar-header small {
            opacity: 0.8;
        }

        .sidebar-nav {
            padding: 1rem 0;
        }

        .sidebar-nav .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 0.75rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }

        .sidebar-nav .nav-link:hover {
            background: rgba(255,255,255,0.1);
            color: white;
        }

        .sidebar-nav .nav-link.active {
            background: rgba(255,255,255,0.15);
            color: white;
            border-left-color: var(--heroicos-secondary);
        }

        .sidebar-nav .nav-link i {
            font-size: 1.25rem;
            width: 24px;
            text-align: center;
        }

        .nav-section {
            padding: 0.5rem 1.5rem;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: rgba(255,255,255,0.5);
            margin-top: 1rem;
        }

        /* Main content */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
        }

        /* Top navbar */
        .top-navbar {
            background: white;
            padding: 1rem 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-title {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
            color: #333;
        }

        .user-menu .dropdown-toggle {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            color: #333;
        }

        .user-menu .dropdown-toggle::after {
            display: none;
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--heroicos-primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        /* Content area */
        .content-area {
            padding: 1.5rem;
        }

        /* Cards */
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            transition: transform 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
        }

        .stat-card .icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .stat-card .icon.bg-primary { background: rgba(183, 32, 210, 0.15); color: var(--heroicos-primary); }
        .stat-card .icon.bg-warning { background: rgba(255, 214, 94, 0.15); color: #d4a800; }
        .stat-card .icon.bg-info { background: rgba(13, 202, 240, 0.15); color: #0dcaf0; }
        .stat-card .icon.bg-danger { background: rgba(214, 43, 35, 0.15); color: var(--heroicos-accent); }

        .stat-card .value {
            font-size: 1.75rem;
            font-weight: 700;
            color: #333;
        }

        .stat-card .label {
            color: #6c757d;
            font-size: 0.875rem;
        }

        /* Alerts */
        .alert {
            border: none;
            border-radius: 8px;
        }

        /* Tables */
        .table-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            overflow: hidden;
        }

        .table-card .card-header {
            background: white;
            border-bottom: 1px solid #e9ecef;
            padding: 1rem 1.5rem;
            font-weight: 600;
        }

        /* Mobile responsiveness */
        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .sidebar-toggle {
                display: block !important;
            }
        }

        .sidebar-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #333;
        }

        /* Sidebar overlay for mobile */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 999;
        }

        .sidebar-overlay.show {
            display: block;
        }
    </style>
    <?= $this->renderSection('styles') ?>
</head>
<body>
    <!-- Sidebar Overlay (Mobile) -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="<?= base_url('assets/images/heroicos.png') ?>" alt="Heroicos" style="width:60px;height:60px;object-fit:contain;border-radius:50%;background:white;padding:4px;">
            <h4>Heroicos</h4>
            <small><?= ucfirst(session()->get('rol_nombre') ?? 'Usuario') ?></small>
        </div>

        <nav class="sidebar-nav">
            <?= $this->renderSection('sidebar') ?>
        </nav>

        <!-- User Info at Bottom -->
        <div class="position-absolute bottom-0 w-100 p-3 border-top" style="border-color: rgba(255,255,255,0.1) !important;">
            <div class="d-flex align-items-center gap-2">
                <div class="user-avatar">
                    <?= strtoupper(substr(session()->get('nombre') ?? 'U', 0, 1)) ?>
                </div>
                <div class="flex-grow-1">
                    <div class="fw-semibold small"><?= esc(session()->get('nombre') . ' ' . session()->get('apellido')) ?></div>
                    <div class="small opacity-75"><?= esc(session()->get('email')) ?></div>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Top Navbar -->
        <nav class="top-navbar">
            <div class="d-flex align-items-center gap-3">
                <button class="sidebar-toggle" id="sidebarToggle">
                    <i class="bi bi-list"></i>
                </button>
                <h1 class="page-title"><?= $pageTitle ?? 'Dashboard' ?></h1>
            </div>

            <div class="user-menu dropdown">
                <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown">
                    <span class="d-none d-md-inline"><?= esc(session()->get('nombre')) ?></span>
                    <div class="user-avatar">
                        <?= strtoupper(substr(session()->get('nombre') ?? 'U', 0, 1)) ?>
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i>Mi Perfil</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="<?= site_url('logout') ?>"><i class="bi bi-box-arrow-right me-2"></i>Cerrar Sesión</a></li>
                </ul>
            </div>
        </nav>

        <!-- Content Area -->
        <div class="content-area">
            <?php if (session()->getFlashdata('message')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>
                    <?= session()->getFlashdata('message') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-circle me-2"></i>
                    <?= session()->getFlashdata('error') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?= $this->renderSection('content') ?>
        </div>
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Sidebar toggle for mobile
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        sidebarToggle?.addEventListener('click', () => {
            sidebar.classList.toggle('show');
            sidebarOverlay.classList.toggle('show');
        });

        sidebarOverlay?.addEventListener('click', () => {
            sidebar.classList.remove('show');
            sidebarOverlay.classList.remove('show');
        });
    </script>
    <?= $this->renderSection('scripts') ?>
</body>
</html>
