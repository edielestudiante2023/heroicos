<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSesionesClase extends Migration
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
            'profesor_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'horario_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'fecha' => [
                'type' => 'DATE',
            ],
            'hora_inicio' => [
                'type' => 'TIME',
                'null' => true,
            ],
            'hora_fin' => [
                'type' => 'TIME',
                'null' => true,
            ],
            'estado' => [
                'type'       => 'ENUM',
                'constraint' => ['programada', 'realizada', 'cancelada'],
                'default'    => 'programada',
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
        $this->forge->addKey('grupo_id');
        $this->forge->addKey('fecha');
        $this->forge->addForeignKey('grupo_id', 'grupos', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('profesor_id', 'profesores', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->addForeignKey('horario_id', 'horarios', 'id', 'SET NULL', 'SET NULL');
        $this->forge->createTable('sesiones_clase');
    }

    public function down()
    {
        $this->forge->dropTable('sesiones_clase');
    }
}
