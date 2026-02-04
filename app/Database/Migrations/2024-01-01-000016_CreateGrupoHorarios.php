<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateGrupoHorarios extends Migration
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
            'grupo_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'horario_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'vigente_desde' => [
                'type' => 'DATE',
            ],
            'vigente_hasta' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['grupo_id', 'horario_id']);
        $this->forge->addForeignKey('grupo_id', 'grupos', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('horario_id', 'horarios', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('grupo_horarios');
    }

    public function down()
    {
        $this->forge->dropTable('grupo_horarios');
    }
}
