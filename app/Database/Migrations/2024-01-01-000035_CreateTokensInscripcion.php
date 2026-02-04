<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTokensInscripcion extends Migration
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
            'token' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'nombre_acudiente' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'null'       => true,
            ],
            'profesor_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'usado' => [
                'type'    => 'BOOLEAN',
                'default' => false,
            ],
            'fecha_uso' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'acudiente_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'Se llena cuando se completa el registro',
            ],
            'expira_at' => [
                'type' => 'DATETIME',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('token');
        $this->forge->addKey('email');
        $this->forge->addKey('expira_at');
        $this->forge->addForeignKey('profesor_id', 'profesores', 'id', 'SET NULL', 'SET NULL');
        $this->forge->createTable('tokens_inscripcion');
    }

    public function down()
    {
        $this->forge->dropTable('tokens_inscripcion');
    }
}
