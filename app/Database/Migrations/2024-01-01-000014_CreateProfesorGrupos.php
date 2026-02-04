<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProfesorGrupos extends Migration
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
            'profesor_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'grupo_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'es_titular' => [
                'type'    => 'BOOLEAN',
                'default' => false,
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
        $this->forge->addUniqueKey(['profesor_id', 'grupo_id'], 'profesor_grupo_unique');
        $this->forge->addForeignKey('profesor_id', 'profesores', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('grupo_id', 'grupos', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('profesor_grupos');
    }

    public function down()
    {
        $this->forge->dropTable('profesor_grupos');
    }
}
