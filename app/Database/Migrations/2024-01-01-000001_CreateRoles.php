<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRoles extends Migration
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
            'nombre' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'descripcion' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
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
        $this->forge->addUniqueKey('nombre');
        $this->forge->createTable('roles');

        // Insertar roles por defecto
        $this->db->table('roles')->insertBatch([
            ['id' => 1, 'nombre' => 'admin', 'descripcion' => 'Administrador de la plataforma', 'created_at' => date('Y-m-d H:i:s')],
            ['id' => 2, 'nombre' => 'profesor', 'descripcion' => 'Profesor de la academia', 'created_at' => date('Y-m-d H:i:s')],
            ['id' => 3, 'nombre' => 'acudiente', 'descripcion' => 'Padre o acudiente de estudiante', 'created_at' => date('Y-m-d H:i:s')],
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('roles');
    }
}
