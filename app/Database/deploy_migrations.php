<?php
/**
 * Script de Despliegue de Migraciones
 * Ejecuta las migraciones en LOCAL y PRODUCCIÓN simultáneamente
 *
 * USO: php app/Database/deploy_migrations.php
 */

// Cargar el framework
require_once __DIR__ . '/../../vendor/autoload.php';

// Inicializar CodeIgniter
$paths = new Config\Paths();
$app = require APPPATH . 'Config/Boot/development.php';

use CodeIgniter\Database\Config;
use CodeIgniter\Database\Exceptions\DatabaseException;

class MigrationDeployer
{
    private $localDb;
    private $productionDb;
    private $results = [];

    public function __construct()
    {
        echo "\n";
        echo "╔══════════════════════════════════════════════════════════════╗\n";
        echo "║     ACADEMIA HEROICOS - DESPLIEGUE DE BASE DE DATOS         ║\n";
        echo "╚══════════════════════════════════════════════════════════════╝\n\n";
    }

    public function run()
    {
        // Conectar a LOCAL
        echo "🔌 Conectando a bases de datos...\n\n";

        $this->connectLocal();
        $this->connectProduction();

        // Ejecutar migraciones en ambos entornos
        echo "\n📦 Ejecutando migraciones...\n";
        echo str_repeat("─", 60) . "\n\n";

        $this->runMigrations('default', 'LOCAL');
        $this->runMigrations('production', 'PRODUCCIÓN');

        // Mostrar resumen
        $this->showSummary();
    }

    private function connectLocal()
    {
        try {
            $this->localDb = \Config\Database::connect('default');
            $this->localDb->initialize();
            echo "✅ LOCAL: Conexión exitosa\n";
            $this->results['local']['connected'] = true;
        } catch (\Exception $e) {
            echo "❌ LOCAL: Error de conexión - " . $e->getMessage() . "\n";
            $this->results['local']['connected'] = false;
            $this->results['local']['error'] = $e->getMessage();
        }
    }

    private function connectProduction()
    {
        try {
            $this->productionDb = \Config\Database::connect('production');
            $this->productionDb->initialize();
            echo "✅ PRODUCCIÓN: Conexión exitosa (DigitalOcean)\n";
            $this->results['production']['connected'] = true;
        } catch (\Exception $e) {
            echo "❌ PRODUCCIÓN: Error de conexión - " . $e->getMessage() . "\n";
            $this->results['production']['connected'] = false;
            $this->results['production']['error'] = $e->getMessage();
        }
    }

    private function runMigrations($group, $label)
    {
        echo "▶ Ejecutando en {$label}...\n";

        if (!$this->results[strtolower($label === 'LOCAL' ? 'local' : 'production')]['connected'] ?? false) {
            echo "   ⚠️  Saltando - No hay conexión\n\n";
            return;
        }

        try {
            $migrate = \Config\Services::migrations();
            $migrate->setGroup($group);

            if ($migrate->latest()) {
                $history = $migrate->getHistory();
                $count = count($history);
                echo "   ✅ {$count} migraciones ejecutadas exitosamente\n";
                $this->results[strtolower($label === 'LOCAL' ? 'local' : 'production')]['migrations'] = $count;
                $this->results[strtolower($label === 'LOCAL' ? 'local' : 'production')]['success'] = true;
            } else {
                echo "   ℹ️  No hay migraciones pendientes\n";
                $this->results[strtolower($label === 'LOCAL' ? 'local' : 'production')]['migrations'] = 0;
                $this->results[strtolower($label === 'LOCAL' ? 'local' : 'production')]['success'] = true;
            }
        } catch (\Exception $e) {
            echo "   ❌ Error: " . $e->getMessage() . "\n";
            $this->results[strtolower($label === 'LOCAL' ? 'local' : 'production')]['success'] = false;
            $this->results[strtolower($label === 'LOCAL' ? 'local' : 'production')]['error'] = $e->getMessage();
        }

        echo "\n";
    }

    private function showSummary()
    {
        echo str_repeat("═", 60) . "\n";
        echo "                      RESUMEN                              \n";
        echo str_repeat("═", 60) . "\n\n";

        echo "┌─────────────────┬─────────────┬─────────────────────────┐\n";
        echo "│ Entorno         │ Estado      │ Migraciones             │\n";
        echo "├─────────────────┼─────────────┼─────────────────────────┤\n";

        // Local
        $localStatus = ($this->results['local']['success'] ?? false) ? '✅ OK' : '❌ Error';
        $localMigrations = $this->results['local']['migrations'] ?? 'N/A';
        printf("│ %-15s │ %-11s │ %-23s │\n", 'LOCAL', $localStatus, $localMigrations . ' tablas');

        // Producción
        $prodStatus = ($this->results['production']['success'] ?? false) ? '✅ OK' : '❌ Error';
        $prodMigrations = $this->results['production']['migrations'] ?? 'N/A';
        printf("│ %-15s │ %-11s │ %-23s │\n", 'PRODUCCIÓN', $prodStatus, $prodMigrations . ' tablas');

        echo "└─────────────────┴─────────────┴─────────────────────────┘\n\n";

        // Credenciales del admin
        echo "🔑 CREDENCIALES DE ACCESO INICIAL:\n";
        echo "   Email: admin@heroicos.com\n";
        echo "   Pass:  Admin123*\n\n";

        echo "¡Despliegue completado!\n\n";
    }
}

// Ejecutar
$deployer = new MigrationDeployer();
$deployer->run();
