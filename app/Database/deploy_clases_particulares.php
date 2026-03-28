<?php
/**
 * Deploy script: Clases Particulares - DB changes
 *
 * Usage:
 *   php app/Database/deploy_clases_particulares.php --env local
 *   php app/Database/deploy_clases_particulares.php --env production
 */

// Parse arguments
$env = 'local';
foreach ($argv as $i => $arg) {
    if ($arg === '--env' && isset($argv[$i + 1])) {
        $env = $argv[$i + 1];
    }
}

echo "=== Deploy Clases Particulares - Entorno: {$env} ===\n\n";

// Database credentials
if ($env === 'production') {
    // Production credentials via environment variables or CLI args
    // Usage: DB_HOST=... DB_PORT=... DB_USER=... DB_PASS=... DB_NAME=... php deploy_clases_particulares.php --env production
    $config = [
        'host'     => getenv('DB_HOST') ?: 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
        'port'     => (int)(getenv('DB_PORT') ?: 25060),
        'username' => getenv('DB_USER') ?: 'cycloid_userdb',
        'password' => getenv('DB_PASS') ?: '',
        'database' => getenv('DB_NAME') ?: 'heroicos',
        'ssl'      => true,
    ];

    if (empty($config['password'])) {
        echo "[ERROR] DB_PASS es requerido para produccion. Uso: DB_PASS=xxx php deploy_clases_particulares.php --env production\n";
        exit(1);
    }
} else {
    $config = [
        'host'     => 'localhost',
        'port'     => 3306,
        'username' => 'root',
        'password' => '',
        'database' => 'heroicos',
        'ssl'      => false,
    ];
}

// Connect
try {
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];

    if ($config['ssl']) {
        $options[PDO::MYSQL_ATTR_SSL_CA] = true;
        $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
    }

    $pdo = new PDO($dsn, $config['username'], $config['password'], $options);
    echo "[OK] Conexion exitosa a {$config['host']}:{$config['port']}/{$config['database']}\n\n";
} catch (PDOException $e) {
    echo "[ERROR] No se pudo conectar: " . $e->getMessage() . "\n";
    exit(1);
}

// ============================================================================
// Step 1: ALTER TABLE solicitudes_clase_particular
// ============================================================================
echo "--- Paso 1: ALTER TABLE solicitudes_clase_particular ---\n";

$columnsToAdd = [
    'profesor_id'        => "ALTER TABLE solicitudes_clase_particular ADD COLUMN profesor_id INT(11) UNSIGNED NULL AFTER estudiante_id",
    'precio_propuesto'   => "ALTER TABLE solicitudes_clase_particular ADD COLUMN precio_propuesto DECIMAL(12,2) NULL AFTER estado",
    'precio_aceptado'    => "ALTER TABLE solicitudes_clase_particular ADD COLUMN precio_aceptado TINYINT(1) NULL AFTER precio_propuesto",
    'respuesta_profesor' => "ALTER TABLE solicitudes_clase_particular ADD COLUMN respuesta_profesor TEXT NULL AFTER precio_aceptado",
];

foreach ($columnsToAdd as $col => $sql) {
    // Check if column exists
    $check = $pdo->query("SELECT COUNT(*) as cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '{$config['database']}' AND TABLE_NAME = 'solicitudes_clase_particular' AND COLUMN_NAME = '{$col}'")->fetch();
    if ($check['cnt'] > 0) {
        echo "  [SKIP] Columna '{$col}' ya existe\n";
    } else {
        $pdo->exec($sql);
        echo "  [OK] Columna '{$col}' agregada\n";
    }
}

// Add FK and index for profesor_id
try {
    $fkCheck = $pdo->query("SELECT COUNT(*) as cnt FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = '{$config['database']}' AND TABLE_NAME = 'solicitudes_clase_particular' AND CONSTRAINT_NAME = 'fk_solicitud_profesor'")->fetch();
    if ($fkCheck['cnt'] == 0) {
        $pdo->exec("ALTER TABLE solicitudes_clase_particular ADD CONSTRAINT fk_solicitud_profesor FOREIGN KEY (profesor_id) REFERENCES profesores(id) ON DELETE RESTRICT ON UPDATE RESTRICT");
        echo "  [OK] Foreign key 'fk_solicitud_profesor' creada\n";
    } else {
        echo "  [SKIP] Foreign key 'fk_solicitud_profesor' ya existe\n";
    }
} catch (PDOException $e) {
    echo "  [WARN] FK profesor_id: " . $e->getMessage() . "\n";
}

try {
    $idxCheck = $pdo->query("SHOW INDEX FROM solicitudes_clase_particular WHERE Key_name = 'idx_profesor_id'")->fetchAll();
    if (empty($idxCheck)) {
        $pdo->exec("ALTER TABLE solicitudes_clase_particular ADD INDEX idx_profesor_id (profesor_id)");
        echo "  [OK] Index 'idx_profesor_id' creado\n";
    } else {
        echo "  [SKIP] Index 'idx_profesor_id' ya existe\n";
    }
} catch (PDOException $e) {
    echo "  [WARN] Index profesor_id: " . $e->getMessage() . "\n";
}

echo "\n";

// ============================================================================
// Step 2: INSERT email templates
// ============================================================================
echo "--- Paso 2: Plantillas de email ---\n";

$plantillas = [
    [
        'codigo'     => 'solicitud_clase_particular',
        'nombre'     => 'Solicitud de clase particular al profesor',
        'asunto'     => 'Nueva solicitud de clase particular - Academia Heroicos',
        'cuerpo_html' => '<h2>Nueva Solicitud de Clase Particular</h2><p>Hola <strong>{{nombre_profesor}}</strong>,</p><p>El acudiente <strong>{{nombre_acudiente}}</strong> ha solicitado una clase particular para el estudiante <strong>{{nombre_estudiante}}</strong>.</p><p><strong>Fecha preferida:</strong> {{fecha_preferida}}<br><strong>Hora preferida:</strong> {{hora_preferida}}<br><strong>Duracion:</strong> {{duracion}}<br><strong>Observaciones:</strong> {{observaciones}}</p><p>Ingresa a la plataforma para aceptar o rechazar esta solicitud y proponer el precio:</p><p><a href="{{url_responder}}" style="background-color:#28a745;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;">Ver Solicitud</a></p>',
        'variables'  => '["nombre_profesor", "nombre_acudiente", "nombre_estudiante", "fecha_preferida", "hora_preferida", "duracion", "observaciones", "url_responder"]',
        'estado'     => 'activa',
    ],
    [
        'codigo'     => 'respuesta_clase_particular',
        'nombre'     => 'Respuesta del profesor a solicitud de clase particular',
        'asunto'     => 'Respuesta a tu solicitud de clase particular - Academia Heroicos',
        'cuerpo_html' => '<h2>Respuesta a Solicitud de Clase Particular</h2><p>Hola <strong>{{nombre_acudiente}}</strong>,</p><p>El profesor <strong>{{nombre_profesor}}</strong> ha respondido a tu solicitud de clase particular para <strong>{{nombre_estudiante}}</strong>.</p><p><strong>Estado:</strong> {{estado}}</p>{{seccion_precio}}<p><strong>Mensaje del profesor:</strong> {{respuesta_profesor}}</p><p>Ingresa a la plataforma para ver los detalles:</p><p><a href="{{url_ver}}" style="background-color:#007bff;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;">Ver Detalles</a></p>',
        'variables'  => '["nombre_acudiente", "nombre_profesor", "nombre_estudiante", "estado", "seccion_precio", "respuesta_profesor", "url_ver"]',
        'estado'     => 'activa',
    ],
    [
        'codigo'     => 'precio_aceptado_clase',
        'nombre'     => 'Precio aceptado - clase particular confirmada',
        'asunto'     => 'Clase particular confirmada - Academia Heroicos',
        'cuerpo_html' => '<h2>Clase Particular Confirmada</h2><p>Hola <strong>{{nombre_profesor}}</strong>,</p><p>El acudiente <strong>{{nombre_acudiente}}</strong> ha aceptado el precio propuesto para la clase particular del estudiante <strong>{{nombre_estudiante}}</strong>.</p><p><strong>Fecha:</strong> {{fecha}}<br><strong>Hora:</strong> {{hora}}<br><strong>Precio acordado:</strong> ${{precio}}</p><p>La clase ha sido programada exitosamente.</p>',
        'variables'  => '["nombre_profesor", "nombre_acudiente", "nombre_estudiante", "fecha", "hora", "precio"]',
        'estado'     => 'activa',
    ],
];

$now = date('Y-m-d H:i:s');

foreach ($plantillas as $p) {
    $check = $pdo->prepare("SELECT COUNT(*) as cnt FROM plantillas_email WHERE codigo = ?");
    $check->execute([$p['codigo']]);
    $exists = $check->fetch();

    if ($exists['cnt'] > 0) {
        echo "  [SKIP] Plantilla '{$p['codigo']}' ya existe\n";
    } else {
        $stmt = $pdo->prepare("INSERT INTO plantillas_email (codigo, nombre, asunto, cuerpo_html, variables, estado, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$p['codigo'], $p['nombre'], $p['asunto'], $p['cuerpo_html'], $p['variables'], $p['estado'], $now]);
        echo "  [OK] Plantilla '{$p['codigo']}' insertada\n";
    }
}

echo "\n=== Deploy completado exitosamente ===\n";
