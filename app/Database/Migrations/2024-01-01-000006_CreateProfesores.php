<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProfesores extends Migration
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
            'usuario_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'tipo_documento' => [
                'type'       => 'ENUM',
                'constraint' => ['CC', 'CE', 'pasaporte'],
                'default'    => 'CC',
            ],
            'numero_documento' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],
            'nombres' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'apellidos' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'telefono' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'direccion' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'especialidad' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'foto' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'estado' => [
                'type'       => 'ENUM',
                'constraint' => ['activo', 'inactivo'],
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
        $this->forge->addUniqueKey('numero_documento');
        $this->forge->addForeignKey('usuario_id', 'usuarios', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('profesores');
    }

    public function down()
    {
        $this->forge->dropTable('profesores');
    }
}
