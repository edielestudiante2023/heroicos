<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePeriodos extends Migration
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
            'fecha_inicio' => [
                'type' => 'DATE',
            ],
            'fecha_fin' => [
                'type' => 'DATE',
            ],
            'estado' => [
                'type'       => 'ENUM',
                'constraint' => ['activo', 'cerrado'],
                'default'    => 'activo',
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
        $this->forge->createTable('periodos');

        // Crear periodo actual
        $this->db->table('periodos')->insert([
            'nombre' => date('Y'),
            'fecha_inicio' => date('Y') . '-01-01',
            'fecha_fin' => date('Y') . '-12-31',
            'estado' => 'activo',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('periodos');
    }
}
