<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEstudianteHistorialDeportivo extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'estudiante_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'academia_anterior' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'null'       => true,
            ],
            'periodo' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'torneos_participados' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'logros' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'posicion_juego' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'observaciones' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('estudiante_id');
        $this->forge->addForeignKey('estudiante_id', 'estudiantes', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('estudiante_historial_deportivo');
    }

    public function down()
    {
        $this->forge->dropTable('estudiante_historial_deportivo');
    }
}
