<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Academia Heroicos - Escuela de Fútbol">
    <title><?= $title ?? 'Academia Heroicos' ?></title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root {
            --heroicos-primary: #1a5f2a;
            --heroicos-secondary: #ffc107;
            --heroicos-dark: #0d3015;
        }

        body {
            background: linear-gradient(135deg, var(--heroicos-primary) 0%, var(--heroicos-dark) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', system-ui, sans-serif;
        }

        .auth-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            overflow: hidden;
            max-width: 420px;
            width: 100%;
        }

        .auth-header {
            background: var(--heroicos-primary);
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .auth-header h1 {
            font-size: 1.75rem;
            font-weight: 700;
            margin: 0;
        }

        .auth-header p {
            margin: 0.5rem 0 0;
            opacity: 0.9;
        }

        .auth-body {
            padding: 2rem;
        }

        .form-control {
            border-radius: 8px;
            padding: 0.75rem 1rem;
            border: 2px solid #e9ecef;
            transition: border-color 0.2s;
        }

        .form-control:focus {
            border-color: var(--heroicos-primary);
            box-shadow: 0 0 0 0.2rem rgba(26, 95, 42, 0.15);
        }

        .btn-primary {
            background: var(--heroicos-primary);
            border: none;
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: all 0.2s;
        }

        .btn-primary:hover {
            background: var(--heroicos-dark);
            transform: translateY(-1px);
        }

        .form-label {
            font-weight: 500;
            color: #495057;
        }

        .input-group-text {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-right: none;
        }

        .input-group .form-control {
            border-left: none;
        }

        .alert {
            border-radius: 8px;
            border: none;
        }

        .logo-img {
            width: 120px;
            height: 120px;
            object-fit: contain;
            margin-bottom: 0.5rem;
            border-radius: 50%;
            background: white;
            padding: 8px;
        }

        a {
            color: var(--heroicos-primary);
        }

        a:hover {
            color: var(--heroicos-dark);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="auth-card mx-auto">
                    <div class="auth-header">
                        <img src="<?= base_url('assets/images/heroicos.png') ?>" alt="Academia Heroicos" class="logo-img">
                        <h1>Academia Heroicos</h1>
                        <p>Escuela de Fútbol</p>
                    </div>
                    <div class="auth-body">
                        <?= $this->renderSection('content') ?>
                    </div>
                </div>
                <p class="text-center mt-3 text-white-50 small">
                    &copy; <?= date('Y') ?> Academia Heroicos. Todos los derechos reservados.
                </p>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
